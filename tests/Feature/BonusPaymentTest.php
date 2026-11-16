<?php

namespace Tests\Feature;

use App\Models\BonusPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BonusPaymentTest extends TestCase
{
    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->admin = User::forceCreate(['name' => 'Bonus Admin', 'email' => 'bonus-admin@example.com',
            'phone' => '09999999999', 'password' => bcrypt('secret123'), 'admin' => true]);
        $this->customer = User::forceCreate(['name' => 'Bonus Customer', 'email' => 'bonus-user@example.com',
            'phone' => '09123456789', 'password' => bcrypt('secret123'), 'balance' => 1000]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['bonus_amount' => 5000, 'bonus_note' => 'Welcome bonus',
            'bonus_request_id' => (string) Str::uuid()], $overrides);
    }

    private function endpoint(): string
    {
        return '/admin/users/'.$this->customer->id.'/bonus';
    }

    public function test_admin_can_pay_bonus_with_auditable_wallet_history_visible_to_the_customer(): void
    {
        $this->actingAs($this->admin)->post($this->endpoint(), $this->payload())->assertSessionHasNoErrors()
            ->assertRedirect('/admin/users/'.$this->customer->id.'/setting')->assertSessionHas('msg', 'Bonus of 5,000 MMK paid successfully.');
        $this->assertSame(6000, $this->customer->fresh()->balance);
        $this->assertDatabaseHas('wallet_histories', ['user_id' => $this->customer->id, 'title' => 'Bonus', 'income' => 1, 'amount' => 5000]);
        $this->assertDatabaseHas('bonus_payments', ['user_id' => $this->customer->id, 'admin_id' => $this->admin->id, 'note' => 'Welcome bonus', 'amount' => 5000]);
        $this->get('/admin/users/'.$this->customer->id.'/wallet-histories')->assertOk()->assertSee('Paid by Bonus Admin')->assertSee('Welcome bonus')->assertSee('+5,000 MMK');
        $this->actingAs($this->customer, 'sanctum')->getJson('/api/user/wallet-histories')->assertOk()
            ->assertJsonPath('wallet_histories.data.0.title', 'Bonus')->assertJsonPath('wallet_histories.data.0.amount', 5000);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('telegram_alerts', 0);
    }

    public function test_retry_or_double_submit_credits_once_and_conflicting_payload_is_rejected(): void
    {
        $this->actingAs($this->admin);
        $payload = $this->payload();
        $this->post($this->endpoint(), $payload)->assertSessionHasNoErrors();
        $this->post($this->endpoint(), $payload)->assertSessionHasNoErrors()->assertSessionHas('msg', 'This bonus was already paid. No additional amount was credited.');
        $this->post($this->endpoint(), array_merge($payload, ['bonus_amount' => 6000]))->assertStatus(409);
        $this->assertSame(6000, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('wallet_histories', 1);
        $this->assertDatabaseCount('bonus_payments', 1);
    }

    public function test_each_new_reference_is_a_separate_bonus_and_note_is_optional(): void
    {
        $this->actingAs($this->admin);
        $this->post($this->endpoint(), $this->payload(['bonus_note' => '']))->assertSessionHasNoErrors();
        $this->post($this->endpoint(), $this->payload(['bonus_note' => null]))->assertSessionHasNoErrors();
        $this->assertSame(11000, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('bonus_payments', 2);
    }

    public function test_invalid_amounts_notes_and_references_cannot_change_the_wallet(): void
    {
        $this->actingAs($this->admin);
        foreach ([0, -1, '1.5', 'invalid', 2147483648, null] as $amount) {
            $this->post($this->endpoint(), $this->payload(['bonus_amount' => $amount]))->assertSessionHasErrors('bonus_amount');
        }
        $this->post($this->endpoint(), $this->payload(['bonus_note' => str_repeat('a', 256)]))->assertSessionHasErrors('bonus_note');
        $this->post($this->endpoint(), $this->payload(['bonus_request_id' => 'invalid']))->assertSessionHasErrors('bonus_request_id');
        $this->assertSame(1000, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('bonus_payments', 0);
        $this->assertDatabaseCount('wallet_histories', 0);
    }

    public function test_balance_overflow_is_rejected(): void
    {
        $this->customer->forceFill(['balance' => 2147483640])->save();
        $this->actingAs($this->admin)->post($this->endpoint(), $this->payload())->assertSessionHasErrors('bonus_amount');
        $this->assertSame(2147483640, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('wallet_histories', 0);
    }

    public function test_guests_and_customers_cannot_pay_a_bonus(): void
    {
        $this->post($this->endpoint(), $this->payload())->assertRedirect();
        $this->actingAs($this->customer)->post($this->endpoint(), $this->payload())->assertForbidden();
        $this->assertSame(1000, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('bonus_payments', 0);
    }

    public function test_missing_user_is_not_found(): void
    {
        $this->actingAs($this->admin)->post('/admin/users/9999/bonus', $this->payload())->assertNotFound();
        $this->assertDatabaseCount('bonus_payments', 0);
    }

    public function test_failed_audit_insert_rolls_back_wallet_history_and_balance(): void
    {
        BonusPayment::creating(fn () => throw new \RuntimeException('Simulated audit failure'));
        $this->withoutExceptionHandling()->actingAs($this->admin);
        try {
            $this->post($this->endpoint(), $this->payload());
            $this->fail('Expected the audit insert to fail.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated audit failure', $e->getMessage());
        } finally {
            BonusPayment::flushEventListeners();
        }
        $this->assertSame(1000, $this->customer->fresh()->balance);
        $this->assertDatabaseCount('wallet_histories', 0);
        $this->assertDatabaseCount('bonus_payments', 0);
    }

    public function test_settings_render_bonus_form_and_a_fresh_reference_after_payment(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/admin/users/'.$this->customer->id.'/setting')->assertOk()->assertSee('Pay bonus')->assertSee('Confirm bonus payment');
        if (! is_dir(storage_path('app/previews'))) {
            mkdir(storage_path('app/previews'), 0775, true);
        }
        file_put_contents(storage_path('app/previews/admin-user-bonus.html'), $response->getContent());
        preg_match('/name="bonus_request_id" value="([^"]+)"/', $response->getContent(), $match);
        $this->assertTrue(Str::isUuid($match[1]));
        $this->post($this->endpoint(), $this->payload(['bonus_request_id' => $match[1]]))->assertRedirect();
        $this->get('/admin/users/'.$this->customer->id.'/setting')->assertOk()->assertDontSee('value="'.$match[1].'"', false)->assertSee('6,000 MMK');
    }
}
