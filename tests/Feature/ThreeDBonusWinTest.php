<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use App\Services\ThreeDWinRules;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ThreeDBonusWinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Carbon::setTestNow(Carbon::parse('2026-11-16 15:30:00', 'Asia/Yangon'));
        $this->actingAs(User::forceCreate(['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com',
            'password' => 'unused', 'phone' => '091', 'admin' => 1]));
        User::forceCreate(['id' => 10, 'name' => 'Player', 'email' => 'player@example.com',
            'password' => 'unused', 'phone' => '092', 'balance' => 0]);
        DB::table('lottery_types')->insert(['id' => 3, 'type' => 'Thai 3D', 'coefficient' => 500, 'release_mode' => 1]);
        DB::table('clocks')->insert(['id' => 5, 'hour' => 3, 'minute' => 30, 'morning' => 0]);
        DB::table('lottery_clock')->insert(['id' => 3, 'lottery_type_id' => 3, 'clock_id' => 5]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function voucher(string $number, array $overrides = []): Voucher
    {
        return Voucher::forceCreate(array_replace(['user_id' => 10, 'lottery_type_id' => 3, 'clock_id' => 5,
            'number' => $number, 'amount' => 100, 'created_at' => '2026-11-16 00:00:00'], $overrides));
    }

    public function test_manual_release_marks_main_adjacent_and_all_arrangements_as_payable(): void
    {
        foreach (['123', '122', '124', '132', '213', '231', '312', '321', '999'] as $number) {
            $this->voucher($number);
        }
        $paid = $this->voucher('132', ['win' => 1, 'bonus_win' => 1, 'verified' => 1, 'win_amount' => 800]);
        $this->post('/admin/release-3d', ['lottery_number' => '123'])->assertRedirect();
        $this->assertDatabaseHas('vouchers', ['number' => '123', 'win' => 1, 'bonus_win' => 0, 'verified' => 0]);
        foreach (['122', '124', '132', '213', '231', '312', '321'] as $number) {
            $this->assertDatabaseHas('vouchers', ['number' => $number, 'win' => 1, 'bonus_win' => 1, 'verified' => 0]);
        }
        $this->assertDatabaseHas('vouchers', ['number' => '999', 'win' => 0, 'bonus_win' => 0, 'verified' => 1]);
        $this->assertEquals(800, $paid->fresh()->win_amount);
        $response = $this->get('/admin/vouchers/3d?status=unpaid_win')->assertOk()->assertSee('Bonus win');
        $this->assertEquals(8, $response->viewData('summary')->winners);
        $this->assertEquals(57000, $response->viewData('summary')->payout);
    }

    public function test_repeated_digits_and_leading_zeroes_do_not_duplicate_bonus_or_replace_main(): void
    {
        $rules = new ThreeDWinRules();
        $this->assertEqualsCanonicalizing(['006', '008', '070', '700'], $rules->bonusNumbers('007'));
        $this->assertEqualsCanonicalizing(['110', '112'], $rules->bonusNumbers('111'));
        // Keep existing adjacent behavior, including its hundreds-boundary handling.
        $this->assertContains('5-19', $rules->bonusNumbers('500'));
        $this->assertContains('5100', $rules->bonusNumbers('599'));
        $main = $this->voucher('007');
        $bonus = $this->voucher('070');
        $this->post('/admin/release-3d', ['lottery_number' => '007'])->assertRedirect();
        $this->assertEquals(0, $main->fresh()->bonus_win);
        $this->assertEquals(1, $bonus->fresh()->bonus_win);
        $this->get('/admin/vouchers/'.$bonus->id.'/edit')->assertOk()->assertSee('Bonus win')->assertSee('1,000 MMK');
        $this->get('/admin/users/10/vouchers')->assertOk()->assertSee('Bonus win')->assertSee('1,000')->assertSee('50,000');
    }

    public function test_bonus_and_main_approval_credit_their_own_multiplier_once(): void
    {
        DB::table('lottery_types')->where('id', 3)->update(['bonus_coefficient' => 15]);
        $main = $this->voucher('123');
        $bonus = $this->voucher('321');
        $loser = $this->voucher('999');
        $this->post('/admin/release-3d', ['lottery_number' => '123'])->assertRedirect();
        $this->put('/admin/vouchers/'.$bonus->id.'/approve')->assertRedirect();
        $this->assertDatabaseHas('vouchers', ['id' => $bonus->id, 'win_amount' => 1500, 'verified' => 1, 'verified_by' => 1]);
        $this->assertDatabaseHas('wallet_histories', ['user_id' => 10, 'amount' => 1500, 'income' => 1]);
        $this->put('/admin/vouchers/'.$main->id.'/approve')->assertRedirect();
        $this->assertEquals(51500, User::find(10)->balance);
        $this->putJson('/admin/vouchers/'.$bonus->id.'/approve')->assertUnprocessable();
        $this->putJson('/admin/vouchers/'.$loser->id.'/approve')->assertUnprocessable();
        $this->assertEquals(51500, User::find(10)->balance);
        $this->assertSame(2, DB::table('wallet_histories')->count());
        DB::table('lottery_types')->where('id', 3)->update(['bonus_coefficient' => 20]);
        $response = $this->get('/admin/vouchers/3d?status=paid')->assertOk();
        $this->assertEquals(51500, $response->viewData('summary')->payout);
    }

    public function test_automatic_release_uses_same_bonus_rules_and_only_current_draw_date(): void
    {
        Http::fake(['www.calamuseducation.com/api-3d.php' => Http::response(['number' => '007'])]);
        $main = $this->voucher('007');
        $bonus = $this->voucher('070');
        $adjacent = $this->voucher('008');
        $loser = $this->voucher('999');
        $future = $this->voucher('700', ['created_at' => '2026-12-01 00:00:00']);
        $this->artisan('command:runtaskfor3d')->assertExitCode(0);
        foreach ([$main, $bonus, $adjacent] as $winner) {
            $this->assertEquals(1, $winner->fresh()->win);
            $this->assertEquals(0, $winner->fresh()->verified);
        }
        $this->assertEquals(1, $bonus->fresh()->bonus_win);
        $this->assertEquals(1, $adjacent->fresh()->bonus_win);
        $this->assertEquals(1, $loser->fresh()->verified);
        $this->assertEquals(0, $future->fresh()->win);
        $this->assertEquals(0, $future->fresh()->verified);
    }

    public function test_bonus_multiplier_defaults_to_ten_and_can_be_configured(): void
    {
        $this->assertDatabaseHas('lottery_types', ['id' => 3, 'bonus_coefficient' => 10]);
        $this->get(route('admin.lottery-types.edit', 3))->assertOk()->assertSee('Bonus multiplication');
        $this->put(route('admin.lottery-types.update', 3), ['multiplication' => 600,
            'bonus_multiplication' => 25, 'close_before' => 15])->assertRedirect();
        $this->assertDatabaseHas('lottery_types', ['id' => 3, 'coefficient' => 600, 'bonus_coefficient' => 25]);
        foreach ([0, -1, 'abc', 1.5] as $invalid) {
            $this->putJson(route('admin.lottery-types.update', 3), ['multiplication' => 600,
                'bonus_multiplication' => $invalid, 'close_before' => 15])->assertUnprocessable();
        }
    }

    public function test_invalid_results_are_rejected_before_releasing_vouchers(): void
    {
        foreach (['7', '1234', 'abc'] as $number) {
            $this->postJson('/admin/release-3d', ['lottery_number' => $number])->assertUnprocessable();
        }
        $this->assertSame(0, DB::table('lotteries')->count());
    }

    public function test_initial_data_seeds_the_3d_bonus_multiplier(): void
    {
        // This test uses only the in-memory database configured in setUp.
        $this->artisan('migrate:fresh', ['--force' => true])->assertExitCode(0);
        $this->seed();
        $this->assertDatabaseHas('lottery_types', ['id' => 3, 'type' => 'Thai 3D',
            'coefficient' => 500, 'bonus_coefficient' => 10]);
    }
}
