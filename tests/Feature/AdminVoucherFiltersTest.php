<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminVoucherFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array',
            'session.driver' => 'array', 'lottery.thai_2d_extra_draws_enabled' => true]);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        foreach ([1 => 'Office Admin', 2 => 'Excluded Customer', 10 => 'Alice', 11 => 'Bob'] as $id => $name) {
            $user = User::forceCreate(['id' => $id, 'name' => $name, 'email' => strtolower(str_replace(' ', '', $name)).'@example.com',
                'phone' => '091234567'.$id, 'password' => 'unused', 'admin' => $id === 1 ? 1 : 0]);
            if ($id === 1) {
                $this->actingAs($user);
            }
        }
        DB::table('lottery_types')->insert([
            ['id' => 2, 'type' => 'Thai 2D', 'coefficient' => 85],
            ['id' => 3, 'type' => 'Thai 3D', 'coefficient' => 600],
        ]);
        DB::table('clocks')->insert([
            ['id' => 11, 'hour' => 9, 'minute' => 30, 'morning' => 1],
            ['id' => 12, 'hour' => 4, 'minute' => 30, 'morning' => 0],
            ['id' => 15, 'hour' => 3, 'minute' => 30, 'morning' => 0],
        ]);
        foreach ([11, 12] as $clock) {
            DB::table('lottery_clock')->insert(['lottery_type_id' => 2, 'clock_id' => $clock]);
        }
        DB::table('lottery_clock')->insert(['lottery_type_id' => 3, 'clock_id' => 15]);
        $this->voucher(1001, ['user_id' => 10, 'clock_id' => 11, 'number' => '07', 'amount' => 100, 'verified_by' => 0]);
        $this->voucher(1002, ['user_id' => 10, 'number' => '07', 'amount' => 200, 'win' => 1]);
        $this->voucher(1003, ['number' => '89', 'amount' => 300, 'win' => 1, 'verified' => 1,
            'verified_by' => 1, 'win_amount' => 25000, 'created_at' => '2026-11-10 12:00:00']);
        $this->voucher(1004, ['clock_id' => 11, 'number' => '00', 'amount' => 400,
            'verified' => 1, 'created_at' => '2026-11-11 23:59:59']);
        $this->voucher(1005, ['lottery_type_id' => 3, 'clock_id' => 15, 'number' => '007', 'amount' => 500,
            'bonus_win' => 1, 'verified' => 1]);
        $this->voucher(1006, ['user_id' => 10, 'lottery_type_id' => 3, 'clock_id' => 15,
            'number' => '007', 'amount' => 600, 'win' => 1]);
        $this->voucher(1007, ['user_id' => 2, 'amount' => 999999]);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-11 17:00:00', 'Asia/Yangon'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function voucher(int $id, array $overrides = []): void
    {
        DB::table('vouchers')->insert(array_replace(['id' => $id, 'user_id' => 11, 'lottery_type_id' => 2,
            'clock_id' => 12, 'number' => '12', 'amount' => 100, 'created_at' => '2026-11-11 12:00:00',
            'updated_at' => '2026-11-11 12:00:00'], $overrides));
    }

    public function test_defaults_show_all_history_with_consistent_totals(): void
    {
        $response = $this->get('/admin/vouchers/2d')->assertOk()->assertSee('Voucher filters')
            ->assertSee('Purchased from')->assertSee('More filters')->assertSee('42,000');
        $this->assertSame(4, $response->viewData('vouchers')->total());
        $summary = $response->viewData('summary');
        $this->assertEquals(1000, $summary->sales);
        $this->assertEquals(42000, $summary->payout);
        $this->assertEquals(2, $summary->pending);
        $this->assertEquals(2, $summary->winners);
        // Saved only under storage for browser visual QA, never exposed as a public route.
        if (! is_dir(storage_path('app/previews'))) {
            mkdir(storage_path('app/previews'), 0775, true);
        }
        file_put_contents(storage_path('app/previews/admin-voucher-filters.html'), $response->getContent());
    }

    public function test_filters_combine_and_keep_leading_zeroes(): void
    {
        $response = $this->get('/admin/vouchers/2d?q=Alice&clock_id=12&number=07&status=unpaid_win&date_from=2026-11-11&date_to=2026-11-11')
            ->assertOk()->assertSee('Number: 07')->assertSee('Unpaid winner');
        $this->assertSame([1002], $response->viewData('vouchers')->pluck('id')->all());
        $this->assertEquals(200, $response->viewData('summary')->sales);
        $this->assertEquals(17000, $response->viewData('summary')->payout);
        $this->assertEquals(1, $response->viewData('summary')->pending);
    }

    public function test_search_finds_email_phone_and_voucher_id_without_leaking_other_types_or_excluded_users(): void
    {
        foreach (['Alice', 'alice@example.com', '09123456710'] as $search) {
            $response = $this->get('/admin/vouchers/2d?q='.urlencode($search))->assertOk();
            $this->assertSame([1002, 1001], $response->viewData('vouchers')->pluck('id')->all());
        }
        $this->assertSame([1003], $this->get('/admin/vouchers/2d?q=1003')->viewData('vouchers')->pluck('id')->all());
        $this->assertSame(0, $this->get('/admin/vouchers/2d?q=Excluded')->viewData('vouchers')->total());
    }

    public function test_status_filters_and_unassigned_admin_include_null_reviewers(): void
    {
        foreach (['waiting' => [1001], 'unpaid_win' => [1002], 'paid' => [1003], 'failed' => [1004]] as $status => $ids) {
            $this->assertSame($ids, $this->get('/admin/vouchers/2d?status='.$status)->assertOk()->viewData('vouchers')->pluck('id')->all());
        }
        $this->assertSame([1003], $this->get('/admin/vouchers/2d?admin_id=1')->viewData('vouchers')->pluck('id')->all());
        $this->assertSame(3, $this->get('/admin/vouchers/2d?admin_id=unassigned')->viewData('vouchers')->total());
        $this->assertSame([1005], $this->get('/admin/vouchers/3d?status=bonus_failed')->assertOk()->viewData('vouchers')->pluck('id')->all());
    }

    public function test_purchase_dates_include_end_of_day_and_sorting_is_applied(): void
    {
        $response = $this->get('/admin/vouchers/2d?date_from=2026-11-11&date_to=2026-11-11&sort=amount_desc')->assertOk();
        $this->assertSame([1004, 1002, 1001], $response->viewData('vouchers')->pluck('id')->all());
        $this->assertEquals(700, $response->viewData('summary')->sales);
        $this->assertSame([1003], $this->get('/admin/vouchers/2d?date_to=2026-11-10')->viewData('vouchers')->pluck('id')->all());
        $this->assertSame([1005, 1006], $this->get('/admin/vouchers/3d?number=007&sort=amount_asc')->assertOk()->viewData('vouchers')->pluck('id')->all());
    }

    public function test_invalid_filters_are_rejected(): void
    {
        foreach (['number=7', 'number=007', 'clock_id=15', 'status=bonus_failed', 'status=unknown',
            'date_from=2026-11-12&date_to=2026-11-11', 'date_from=invalid', 'admin_id=10', 'sort=invalid', 'per_page=100000', 'q[]=Alice',
            'amount_min=-1', 'amount_max=invalid', 'amount_min=300&amount_max=100'] as $query) {
            $this->getJson('/admin/vouchers/2d?'.$query)->assertUnprocessable();
        }
    }

    public function test_amount_range_is_inclusive_and_updates_matching_totals(): void
    {
        $response = $this->get('/admin/vouchers/2d?amount_min=200&amount_max=300')->assertOk()
            ->assertSee('Min amount: 200 MMK')->assertSee('Max amount: 300 MMK');
        $this->assertSame([1002, 1003], $response->viewData('vouchers')->pluck('id')->all());
        $this->assertEquals(500, $response->viewData('summary')->sales);
        $this->assertSame(1, $this->get('/admin/vouchers/2d?amount_min=400')->viewData('vouchers')->total());
        $this->assertSame(1, $this->get('/admin/vouchers/2d?amount_max=100')->viewData('vouchers')->total());
        $this->assertSame(4, $this->get('/admin/vouchers/2d?amount_min=0')->viewData('vouchers')->total());
        $this->assertSame([1005], $this->get('/admin/vouchers/3d?amount_min=500&amount_max=500')->viewData('vouchers')->pluck('id')->all());
    }

    public function test_pagination_preserves_filters_and_summary_covers_all_pages(): void
    {
        for ($id = 1100; $id < 1130; $id++) {
            $this->voucher($id, ['user_id' => 10, 'number' => '07', 'amount' => 100]);
        }
        $response = $this->get('/admin/vouchers/2d?q=Alice&number=07&sort=oldest&per_page=25')->assertOk();
        $this->assertSame(32, $response->viewData('vouchers')->total());
        $this->assertCount(25, $response->viewData('vouchers')->items());
        $this->assertStringContainsString('number=07', $response->viewData('vouchers')->nextPageUrl());
        $this->assertStringContainsString('q=Alice', $response->viewData('vouchers')->nextPageUrl());
        $this->assertEquals(3300, $response->viewData('summary')->sales);
        $page2 = $this->get('/admin/vouchers/2d?q=Alice&number=07&sort=oldest&per_page=25&page=2')->assertOk();
        $this->assertCount(7, $page2->viewData('vouchers')->items());
        $this->assertEquals(3300, $page2->viewData('summary')->sales);
    }

    public function test_empty_results_have_zero_totals_and_a_clear_action(): void
    {
        $response = $this->get('/admin/vouchers/2d?number=99')->assertOk()
            ->assertSee('No vouchers match these filters')->assertSee('Clear all filters');
        $this->assertSame(0, $response->viewData('vouchers')->total());
        $this->assertEquals(0, $response->viewData('summary')->sales);
        $this->assertEquals(0, $response->viewData('summary')->pending);
    }

    public function test_disabled_extra_draws_stay_hidden_from_results_and_draw_selector(): void
    {
        config(['lottery.thai_2d_extra_draws_enabled' => false]);
        $response = $this->get('/admin/vouchers/2d')->assertOk()->assertDontSee('09:30 AM');
        $this->assertSame([1002, 1003], $response->viewData('vouchers')->pluck('id')->all());
        $this->assertEquals(500, $response->viewData('summary')->sales);
        $this->getJson('/admin/vouchers/2d?clock_id=11')->assertUnprocessable();
    }
}
