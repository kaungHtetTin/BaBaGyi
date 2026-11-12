<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientAppTest extends TestCase
{
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->customer = User::forceCreate(['name' => 'Client Test', 'email' => 'client@example.com',
            'phone' => '09123456789', 'password' => Hash::make('secret123'), 'balance' => 10000]);
    }

    public function test_shell_is_public_and_separate_from_the_welcome_and_admin_pages(): void
    {
        $this->get('/app')->assertOk()->assertViewIs('client')->assertSee('/build/assets/')
            ->assertSee('android-resources.json?v=')
            ->assertDontSee('secret123')->assertDontSee('client_token');
        $this->get('/')->assertOk()->assertViewIs('welcome')->assertSee(url('/app').'/');
        $this->get('/admin/login')->assertOk();
    }

    public function test_manifest_and_worker_are_scoped_to_client_and_cache_no_api_data(): void
    {
        $this->get('/app/manifest.webmanifest')->assertOk()->assertJsonPath('name', 'MasterGyi')
            ->assertJsonPath('start_url', url('/app'))->assertJsonPath('scope', url('/app'))
            ->assertJsonPath('display', 'standalone')->assertJsonCount(3, 'icons');
        $worker = $this->get('/app/sw.js')->assertOk()->assertHeader('Service-Worker-Allowed', '/app')->getContent();
        $this->assertStringNotContainsString('__SHELL_URLS__', $worker);
        $this->assertStringContainsString('logic.js', $worker);
        $this->assertStringNotContainsString('/app/api/', $worker);
        $this->assertStringNotContainsString('/admin', $worker);
        $this->assertStringNotContainsString('csrf', $worker);
    }

    public function test_login_stores_token_only_in_server_session_and_logout_revokes_it(): void
    {
        $login = $this->postJson('/app/api/login', ['phone' => $this->customer->phone, 'password' => 'secret123'])
            ->assertOk()->assertJsonPath('status', 'success')->assertJsonMissingPath('_token')->assertSessionHas('client_token');
        $this->assertStringContainsString('no-store', $login->headers->get('Cache-Control'));
        $this->getJson('/app/api/user')->assertOk()->assertJsonPath('user.id', $this->customer->id);
        $this->postJson('/app/api/logout')->assertOk()->assertSessionMissing('client_token');
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->getJson('/app/api/user')->assertUnauthorized();
    }

    public function test_an_admin_cookie_does_not_authenticate_the_client_account(): void
    {
        $this->actingAs($this->customer)->getJson('/app/api/user')->assertUnauthorized();
    }

    public function test_persistent_sign_in_cookie_is_http_only_and_scoped_to_the_app(): void
    {
        $login = $this->postJson('/app/api/login', ['phone' => $this->customer->phone, 'password' => 'secret123'])->assertOk();
        $cookie = collect($login->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'mastergyi_client');
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('/app', $cookie->getPath());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertStringNotContainsString('|', $cookie->getValue());
    }

    public function test_a_valid_remember_cookie_restores_a_new_client_session(): void
    {
        $token = $this->customer->createToken('remember-test')->plainTextToken;
        $this->withCredentials()->withCookie('mastergyi_client', $token)->getJson('/app/api/user')->assertOk()->assertJsonPath('user.id', $this->customer->id);
    }

    public function test_guest_cannot_make_payments_and_unlisted_endpoints_are_rejected(): void
    {
        $this->postJson('/app/api/withdraw', ['amount' => 100])->assertUnauthorized();
        $this->getJson('/app/api/admin-notify')->assertNotFound();
        $this->getJson('/app/api/logout')->assertNotFound();
    }

    public function test_client_posts_require_csrf_in_a_non_test_runtime(): void
    {
        $this->app['env'] = 'local';
        $this->postJson('/app/api/login', ['phone' => $this->customer->phone, 'password' => 'secret123'])->assertStatus(419);
        $this->app['env'] = 'testing';
    }

    public function test_public_controller_collections_are_normalized_to_json(): void
    {
        DB::table('contacts')->insert(['service' => 'Test support', 'contact' => '@test']);
        $this->getJson('/app/api/contacts')->assertOk()->assertJsonPath('0.contact', '@test');
    }

    public function test_disabled_customers_can_read_their_status_but_cannot_make_payments(): void
    {
        $this->customer->forceFill(['disable' => 1])->save();
        $this->authenticate();
        $this->getJson('/app/api/user')->assertOk()->assertJsonPath('user.disable', 1);
        $this->postJson('/app/api/withdraw', [])->assertForbidden()->assertJsonPath('disabled', true);
    }

    public function test_topup_retry_creates_one_request_and_keeps_the_same_response(): void
    {
        $this->seedBank(); $this->authenticate();
        $payload = ['payment_method_id' => 1, 'amount' => 1000, 'bank_transaction_id' => '001234', 'request_id' => (string) Str::uuid()];
        $first = $this->postJson('/app/api/top-up', $payload)->assertOk()->json();
        $this->postJson('/app/api/top-up', $payload)->assertOk()->assertExactJson($first);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('payment_methods', ['id' => 1, 'new_payment_count' => 1]);
        $this->assertDatabaseCount('client_requests', 1);
    }

    public function test_reusing_request_id_with_a_different_payment_is_rejected(): void
    {
        $this->seedBank(); $this->authenticate();
        $payload = ['payment_method_id' => 1, 'amount' => 1000, 'bank_transaction_id' => '001234', 'request_id' => (string) Str::uuid()];
        $this->postJson('/app/api/top-up', $payload)->assertOk();
        $payload['amount'] = 2000;
        $this->postJson('/app/api/top-up', $payload)->assertStatus(409);
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_withdraw_retry_debits_the_wallet_once_and_rejects_negative_amounts(): void
    {
        $this->seedBank(); $this->authenticate();
        $payload = ['banking_id' => 1, 'amount' => 1000, 'method' => '09123456789', 'account_name' => 'Test account', 'request_id' => (string) Str::uuid()];
        $this->postJson('/app/api/withdraw', $payload)->assertOk()->assertJsonPath('status', 'success');
        $this->postJson('/app/api/withdraw', $payload)->assertOk();
        $this->assertSame(9000, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('withdraws', 1);
        $payload['amount'] = -1000; $payload['request_id'] = (string) Str::uuid();
        $this->postJson('/app/api/withdraw', $payload)->assertUnprocessable();
        $this->assertSame(9000, $this->customer->fresh()->balance);
    }

    public function test_purchase_retry_is_atomic_and_returns_original_success_after_closing(): void
    {
        $this->seedDraw(); $this->authenticate();
        $payload = $this->purchase();
        $this->postJson('/app/api/vouchers/store', $payload)->assertOk()->assertJsonPath('status', 'success');
        $this->travelTo(now('Asia/Yangon')->setTime(17, 0));
        $this->postJson('/app/api/vouchers/store', $payload)->assertOk()->assertJsonPath('status', 'success');
        $this->assertSame(9900, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('vouchers', 1);
        $this->assertDatabaseCount('wallet_histories', 1);
        $this->assertDatabaseHas('numbers', ['id' => 1, 'demand' => 100]);
    }

    public function test_closed_or_forged_purchases_do_not_charge_the_wallet(): void
    {
        $this->seedDraw(); $this->authenticate();
        $payload = $this->purchase();
        $payload['numberJSON'] = json_encode([['id' => 1, 'number' => '99', 'amount' => 100]]);
        $this->postJson('/app/api/vouchers/store', $payload)->assertUnprocessable();
        $this->travelTo(now('Asia/Yangon')->setTime(12, 0));
        $this->postJson('/app/api/vouchers/store', $this->purchase())->assertUnprocessable();
        $this->assertSame(10000, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('vouchers', 0);
    }

    public function test_partial_purchase_charges_only_accepted_numbers(): void
    {
        $this->seedDraw(); $this->authenticate();
        DB::table('numbers')->insert(['id' => 2, 'lottery_type_id' => 2, 'clock_id' => 2,
            'number' => '08', 'sell' => 50, 'demand' => 0, 'report' => 0]);
        $payload = $this->purchase();
        $payload['numberJSON'] = json_encode([['id' => 1, 'number' => '07', 'amount' => 100], ['id' => 2, 'number' => '08', 'amount' => 100]]);
        $this->postJson('/app/api/vouchers/store', $payload)->assertOk()->assertJsonPath('error_numbers.0.number', '08');
        $this->assertSame(9900, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('vouchers', 1);
    }

    public function test_hot_number_pair_rows_remain_separate_native_style_vouchers(): void
    {
        $this->seedDraw(); $this->authenticate();
        $payload = $this->purchase();
        $payload['numberJSON'] = json_encode([['id' => 1, 'number' => '07', 'amount' => 100], ['id' => 1, 'number' => '07', 'amount' => 100]]);
        $this->postJson('/app/api/vouchers/store', $payload)->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseCount('vouchers', 2);
        $this->assertSame(9800, $this->customer->fresh()->balance);
    }

    public function test_shared_native_api_rolls_back_all_rows_if_a_later_number_is_forged(): void
    {
        $this->seedDraw();
        DB::table('numbers')->insert(['id' => 2, 'lottery_type_id' => 2, 'clock_id' => 2, 'number' => '08', 'sell' => 5000, 'demand' => 0, 'report' => 0]);
        $payload = $this->purchase();
        $payload['numberJSON'] = json_encode([['id' => 1, 'number' => '07', 'amount' => 100], ['id' => 2, 'number' => '99', 'amount' => 100]]);
        $this->withToken($this->customer->createToken('native-test')->plainTextToken)->postJson('/api/vouchers/store', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('vouchers', 0);
        $this->assertSame(10000, $this->customer->fresh()->balance);
        $this->assertDatabaseHas('numbers', ['id' => 1, 'demand' => 0]);
    }

    public function test_missing_payment_accounts_return_a_useful_error(): void
    {
        $this->seedBank(); $this->authenticate();
        DB::table('payment_methods')->update(['disable' => 1]);
        $this->getJson('/app/api/payment-method?banking_id=1')->assertNotFound();
    }

    public function test_3d_live_results_work_on_draw_days(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-16 15:30:00', 'Asia/Yangon'));
        Http::preventStrayRequests();
        Http::fake(['www.calamuseducation.com/api-3d.php' => Http::response(['number' => '007'])]);
        $this->getJson('/app/api/remote/thai-3d')->assertOk()->assertJsonPath('number', '007')->assertJsonPath('status', 'open');
    }

    private function authenticate(): void
    {
        $this->withSession(['client_token' => $this->customer->createToken('pwa-test')->plainTextToken]);
    }

    private function seedBank(): void
    {
        DB::table('bankings')->insert(['id' => 1, 'bank' => 'Test Bank']);
        DB::table('payment_methods')->insert(['id' => 1, 'banking_id' => 1, 'method' => '09123456789', 'account_name' => 'Test account']);
    }

    private function seedDraw(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-09 09:30:00', 'Asia/Yangon'));
        DB::table('lottery_types')->insert(['id' => 2, 'type' => 'Thai 2D', 'open' => 1]);
        DB::table('clocks')->insert(['id' => 2, 'hour' => 12, 'minute' => 1, 'morning' => 0]);
        DB::table('lottery_clock')->insert(['lottery_type_id' => 2, 'clock_id' => 2, 'close_before' => 30]);
        DB::table('numbers')->insert(['id' => 1, 'lottery_type_id' => 2, 'clock_id' => 2, 'number' => '07', 'sell' => 5000, 'demand' => 0, 'report' => 0]);
    }

    private function purchase(): array
    {
        return ['lottery_type_id' => 2, 'clock_id' => 2, 'numberJSON' => json_encode([['id' => 1, 'number' => '07', 'amount' => 100]]), 'request_id' => (string) Str::uuid()];
    }
}
