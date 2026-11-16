<?php

namespace Tests\Feature;

use App\Models\TelegramAlert;
use App\Models\TelegramSetting;
use App\Models\User;
use App\Services\TelegramNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class TelegramNotificationsTest extends TestCase
{
    private string $token = '123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijk';
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);
        $this->customer = User::forceCreate(['name' => 'Test <User>', 'email' => 'telegram-user@example.com',
            'phone' => '09123456789', 'password' => bcrypt('secret123'), 'balance' => 10000]);
        DB::table('bankings')->insert(['id' => 1, 'bank' => 'KPay']);
        DB::table('payment_methods')->insert(['id' => 1, 'banking_id' => 1, 'method' => '09987654321', 'account_name' => 'Company']);
    }

    private function configure(array $overrides = []): TelegramSetting
    {
        return TelegramSetting::create(array_merge(['id' => 1, 'bot_token' => $this->token, 'admin_chat_id' => '123456789',
            'enabled' => true, 'notify_topups' => true, 'notify_withdrawals' => true], $overrides));
    }

    private function fakeTelegram($callback): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake($callback);
    }

    private function authenticateClient(): void
    {
        $this->withSession(['client_token' => $this->customer->createToken('test')->plainTextToken]);
    }

    private function topup(): array
    {
        return ['payment_method_id' => 1, 'amount' => 5000, 'bank_transaction_id' => '001234', 'request_id' => (string) Str::uuid()];
    }

    private function admin(): User
    {
        return User::forceCreate(['name' => 'Telegram Admin', 'email' => 'telegram-admin@example.com',
            'phone' => '09999999999', 'password' => bcrypt('secret123'), 'admin' => 1]);
    }

    public function test_admin_can_save_encrypted_settings_keep_token_and_send_test_while_disabled(): void
    {
        $this->actingAs($this->admin())->put('/admin/telegram', ['bot_token' => $this->token, 'admin_chat_id' => '123456789',
            'notify_topups' => '1', 'notify_withdrawals' => '1'])->assertSessionHasNoErrors()->assertRedirect('/admin/telegram');
        $this->assertNotSame($this->token, DB::table('telegram_settings')->value('bot_token'));
        $this->assertSame($this->token, TelegramSetting::current()->bot_token);
        $this->assertArrayNotHasKey('bot_token', TelegramSetting::current()->toArray());
        $this->get('/admin/telegram')->assertOk()->assertSee('Telegram admin alerts')->assertDontSee($this->token)->assertHeader('Cache-Control', 'no-store, private');
        $this->put('/admin/telegram', ['bot_token' => '', 'admin_chat_id' => '123456789', 'enabled' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame($this->token, TelegramSetting::current()->bot_token);
        TelegramSetting::current()->update(['enabled' => false]);
        $this->from('/admin/telegram')->post('/admin/telegram/test')->assertSessionHasNoErrors()->assertSessionHas('msg');
        Http::assertSent(fn ($request) => $request['chat_id'] === '123456789' && str_contains($request->url(), '/sendMessage'));
    }

    public function test_settings_validate_private_chat_credentials_and_never_flash_the_token(): void
    {
        $this->actingAs($this->admin())->from('/admin/telegram')->put('/admin/telegram', [
            'bot_token' => $this->token, 'admin_chat_id' => '-100123456', 'enabled' => '1',
        ])->assertSessionHasErrors('admin_chat_id')->assertSessionMissing('_old_input.bot_token');
        $this->put('/admin/telegram', ['enabled' => '1', 'admin_chat_id' => '123456789'])->assertSessionHasErrors('enabled');
        $this->assertDatabaseCount('telegram_settings', 0);
    }

    public function test_guests_and_customers_cannot_read_or_change_bot_settings(): void
    {
        $this->configure();
        $this->get('/admin/telegram')->assertRedirect();
        $this->post('/admin/telegram/test')->assertRedirect();
        $this->actingAs($this->customer)->get('/admin/telegram')->assertForbidden()->assertDontSee('Bot settings');
        $this->put('/admin/telegram', ['admin_chat_id' => '999'])->assertForbidden();
        $this->post('/admin/telegram/test')->assertForbidden();
        $this->assertSame('123456789', TelegramSetting::current()->admin_chat_id);
        Http::assertNothingSent();
    }

    public function test_topup_alert_contains_reference_and_repeated_web_request_sends_once(): void
    {
        $this->configure(); $this->authenticateClient();
        $payload = $this->topup();
        $this->postJson('/app/api/top-up', $payload)->assertOk();
        $this->postJson('/app/api/top-up', $payload)->assertOk();
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('telegram_alerts', 1);
        $alert = TelegramAlert::firstOrFail();
        foreach (['Test <User>', '09123456789', '5,000', 'KPay', '001234', 'TOPUP-1'] as $value) {
            $this->assertStringContainsString($value, $alert->message);
        }
        app(TelegramNotifier::class)->flush();
        app(TelegramNotifier::class)->flush();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['chat_id'] === '123456789' && ! isset($request['parse_mode']));
        $this->assertNotNull($alert->fresh()->sent_at);
        $this->assertSame(10000, $this->customer->fresh()->balance);
    }

    public function test_native_api_withdrawal_contains_account_and_does_not_alert_for_insufficient_balance(): void
    {
        $this->configure();
        $this->actingAs($this->customer, 'sanctum');
        $payload = ['banking_id' => 1, 'amount' => 3000, 'method' => '09888777666', 'account_name' => 'Recipient'];
        $this->postJson('/api/withdraw', $payload)->assertOk()->assertJsonPath('status', 'success');
        $this->postJson('/api/withdraw', array_merge($payload, ['amount' => 20000]))->assertOk()->assertJsonPath('status', 'fail');
        $this->assertDatabaseCount('telegram_alerts', 1);
        $message = TelegramAlert::firstOrFail()->message;
        foreach (['WITHDRAW-1', 'Recipient', '09888777666', '3,000', 'KPay', '09123456789'] as $value) {
            $this->assertStringContainsString($value, $message);
        }
        $this->assertSame(7000, $this->customer->fresh()->balance);
    }

    public function test_disabled_notifications_and_disabled_types_do_not_record_alerts(): void
    {
        $settings = $this->configure(['enabled' => false]); $this->authenticateClient();
        $this->postJson('/app/api/top-up', $this->topup())->assertOk();
        $settings->update(['enabled' => true, 'notify_topups' => false]);
        $this->postJson('/app/api/top-up', $this->topup())->assertOk();
        $this->assertDatabaseCount('telegram_alerts', 0);
        Http::assertNothingSent();
    }

    public function test_rolled_back_financial_request_leaves_no_notification(): void
    {
        $this->configure(); $this->authenticateClient();
        DB::beginTransaction();
        $this->postJson('/app/api/top-up', $this->topup())->assertOk();
        $this->assertDatabaseCount('telegram_alerts', 1);
        DB::rollBack();
        $this->artisan('telegram:send-pending')->assertExitCode(0);
        $this->assertDatabaseCount('telegram_alerts', 0);
        $this->assertDatabaseCount('transactions', 0);
        Http::assertNothingSent();
    }

    public function test_telegram_failure_does_not_fail_payment_and_retries_are_bounded(): void
    {
        $this->configure(); $this->authenticateClient();
        $this->fakeTelegram(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 401, 'description' => $this->token], 401)]);
        $this->postJson('/app/api/top-up', $this->topup())->assertOk();
        $notifier = app(TelegramNotifier::class);
        for ($i = 0; $i < 5; $i++) {
            $notifier->flush();
            $this->travel(20)->minutes();
        }
        $notifier->flush();
        $alert = TelegramAlert::firstOrFail();
        $this->assertSame(5, $alert->attempts);
        $this->assertNull($alert->next_attempt_at);
        $this->assertNull($alert->sent_at);
        $this->assertStringNotContainsString($this->token, $alert->last_error);
        Http::assertSentCount(5);
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_network_exception_is_redacted_and_retry_respects_delay_then_succeeds(): void
    {
        $this->configure(); $this->authenticateClient();
        $this->fakeTelegram(fn () => throw new \Illuminate\Http\Client\ConnectionException($this->token));
        $this->postJson('/app/api/top-up', $this->topup())->assertOk();
        $notifier = app(TelegramNotifier::class);
        $notifier->flush();
        $alert = TelegramAlert::firstOrFail();
        $this->assertSame(1, $alert->attempts);
        $this->assertStringNotContainsString($this->token, $alert->last_error);
        $notifier->flush();
        $this->assertSame(1, $alert->fresh()->attempts);
        $this->fakeTelegram(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $this->travel(2)->minutes();
        $this->artisan('telegram:send-pending')->assertExitCode(0);
        $this->assertNotNull($alert->fresh()->sent_at);
    }

    public function test_pending_alerts_pause_when_disabled_and_hold_when_recipient_changes(): void
    {
        $settings = $this->configure(); $this->authenticateClient();
        $this->fakeTelegram(['api.telegram.org/*' => Http::response(['ok' => false], 500)]);
        $this->postJson('/app/api/top-up', $this->topup())->assertOk();
        $settings->update(['enabled' => false]);
        app(TelegramNotifier::class)->flush();
        $this->assertSame(1, TelegramAlert::first()->attempts);
        $settings->update(['enabled' => true, 'admin_chat_id' => '987654321']);
        $this->travel(2)->minutes();
        app(TelegramNotifier::class)->flush();
        $this->assertNull(TelegramAlert::first()->next_attempt_at);
        Http::assertSentCount(1);
    }

    public function test_admin_can_retry_failed_alerts_and_render_delivery_history(): void
    {
        $this->configure();
        $alert = TelegramAlert::create(['type' => 'withdrawal', 'request_id' => 42, 'message' => 'Test financial request',
            'chat_id' => '123456789', 'attempts' => 5, 'last_error' => 'Unable to connect to Telegram.']);
        $this->actingAs($this->admin());
        $response = $this->get('/admin/telegram')->assertOk()->assertSee('WITHDRAW-42')->assertSee('Held / Failed');
        if (! is_dir(storage_path('app/previews'))) {
            mkdir(storage_path('app/previews'), 0775, true);
        }
        file_put_contents(storage_path('app/previews/admin-telegram.html'), $response->getContent());
        $this->from('/admin/telegram')->post('/admin/telegram/alerts/'.$alert->id.'/retry')
            ->assertSessionHasNoErrors()->assertRedirect('/admin/telegram');
        $this->assertNotNull($alert->fresh()->sent_at);
        Http::assertSentCount(1);
        $this->post('/admin/telegram/alerts/'.$alert->id.'/retry')->assertRedirect();
        Http::assertSentCount(1);
    }
}
