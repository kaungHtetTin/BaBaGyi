<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminResultCalendarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['lottery.thai_2d_extra_draws_enabled' => true]);
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->actingAs(User::forceCreate(['name' => 'Calendar Admin', 'email' => 'calendar@example.com',
            'phone' => '12345', 'password' => 'unused', 'admin' => 1]));
        DB::table('lottery_types')->insert([['id' => 2, 'type' => 'Thai 2D'], ['id' => 3, 'type' => 'Thai 3D']]);
        DB::table('clocks')->insert([
            ['id' => 11, 'hour' => 9, 'minute' => 30, 'morning' => 1],
            ['id' => 12, 'hour' => 12, 'minute' => 1, 'morning' => 0],
            ['id' => 13, 'hour' => 2, 'minute' => 0, 'morning' => 0],
            ['id' => 14, 'hour' => 4, 'minute' => 30, 'morning' => 0],
            ['id' => 15, 'hour' => 3, 'minute' => 30, 'morning' => 0],
        ]);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-08 12:00:00', 'Asia/Yangon'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_disabled_extra_draws_are_hidden_and_cannot_be_manually_saved(): void
    {
        $this->result(11, '07', '2026-11-06 09:30:00');
        $this->result(13, '89', '2026-11-06 14:00:00');
        config(['lottery.thai_2d_extra_draws_enabled' => false]);
        $response = $this->get('/admin/lotteries/records?type=2&year=2026&month=11')->assertOk()
            ->assertDontSee('09:30 AM')->assertDontSee('02:00 PM');
        $this->assertSame(0, $response->viewData('recordCount'));
        $this->getJson(route('admin.lotteries.edit-day', ['type' => 2, 'date' => '2026-11-06']))
            ->assertOk()->assertJsonMissingPath('draws.09:30')->assertJsonMissingPath('draws.14:00');
        foreach (['09:30', '14:00'] as $time) {
            $this->putJson(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
                'numbers' => [$time => '00']])->assertUnprocessable();
        }
        foreach ([11, 13] as $clockId) {
            $this->postJson(route('admins.release-2d'), ['lottery_type_id' => 2,
                'clock_id' => $clockId, 'lottery_number' => '00'])->assertUnprocessable();
        }
        foreach (['YEAR' => 'Y', 'MONTH' => 'm', 'DAY' => 'd'] as $function => $format) {
            DB::connection()->getPdo()->sqliteCreateFunction($function, fn ($value) => $value ? (int) date($format, strtotime($value)) : null);
        }
        $this->get(route('admin.index', ['date' => '2026-11-06']))->assertOk()
            ->assertDontSee('id="2d_result_num_0930"', false)->assertDontSee('id="2d_result_num_1400"', false);
        $this->putJson(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'numbers' => ['12:01' => '12']])->assertOk();
        $this->assertDatabaseHas('lotteries', ['clock_id' => 11, 'number' => '07']);
        $this->assertDatabaseHas('lotteries', ['clock_id' => 13, 'number' => '89']);
        $this->assertDatabaseHas('lotteries', ['clock_id' => 12, 'number' => '12']);
    }

    private function result(int $clock, string $number, string $date, int $type = 2): void
    {
        DB::table('lotteries')->insert(['lottery_type_id' => $type, 'clock_id' => $clock,
            'number' => $number, 'created_at' => $date, 'updated_at' => $date]);
    }

    public function test_calendar_renders_a_full_monday_to_sunday_grid_with_all_four_times(): void
    {
        foreach ([11 => '07', 12 => '12', 13 => '42', 14 => '00'] as $clock => $number) {
            $this->result($clock, $number, '2026-11-09 16:30:00');
        }
        $this->result(11, '99', '2026-10-31 09:30:00');
        $this->result(15, '777', '2026-11-09 15:30:00', 3);
        $response = $this->get('/admin/lotteries/records?type=2&year=2026&month=11')->assertOk()
            ->assertSee('November 2026')->assertSee('09:30 AM')->assertSee('02:00 PM')
            ->assertSee('No scheduled draw')->assertSee('Monthly results calendar');
        $weeks = $response->viewData('calendarWeeks');
        $days = $weeks->collapse();
        $this->assertCount(6, $weeks);
        $this->assertCount(42, $days);
        $this->assertCount(30, $days->where('in_month', true));
        $this->assertSame('2026-10-26', $days->first()['date']->toDateString());
        $this->assertSame('2026-12-06', $days->last()['date']->toDateString());
        $day = $days->first(fn ($day) => $day['date']->toDateString() === '2026-11-09');
        $this->assertSame(['09:30', '12:01', '14:00', '16:30'], array_keys($day['results']));
        $this->assertSame(['07', '12', '42', '00'], array_column($day['results'], 'number'));
        $outside = $days->first(fn ($day) => $day['date']->toDateString() === '2026-10-31');
        $this->assertSame([], $outside['results']);
        $this->assertSame(4, $response->viewData('recordCount'));
        $this->assertSame(1, $response->viewData('resultDayCount'));
        $this->assertTrue($days->first(fn ($day) => $day['date']->toDateString() === '2026-11-08')['is_today']);
    }

    public function test_missing_results_keep_the_calendar_and_empty_slots_visible(): void
    {
        $response = $this->get('/admin/lotteries/records?year=2026&month=11')->assertOk()
            ->assertSee('No results saved for this month.');
        $day = $response->viewData('calendarWeeks')->collapse()
            ->first(fn ($day) => $day['date']->toDateString() === '2026-11-02');
        $this->assertCount(4, $day['results']);
        $this->assertSame([null, null, null, null], array_column($day['results'], 'number'));
    }

    public function test_month_navigation_crosses_years_and_type_tabs_preserve_the_month(): void
    {
        $response = $this->get('/admin/lotteries/records?type=3&year=2026&month=12')->assertOk()
            ->assertSee('Previous month: November 2026')->assertSee('Next month: January 2027');
        $this->assertSame('2026-11', $response->viewData('previousMonth')->format('Y-m'));
        $this->assertSame('2027-01', $response->viewData('nextMonth')->format('Y-m'));
        $response->assertSee(route('admin.lotteries.records', ['type' => 2, 'year' => 2026, 'month' => 12]));
        $response->assertSee(route('admin.lotteries.records', ['type' => 3, 'year' => 2027, 'month' => 1]));
    }

    public function test_leap_year_and_default_myanmar_month_are_correct(): void
    {
        $response = $this->get('/admin/lotteries/records?year=2024&month=2')->assertOk();
        $days = $response->viewData('calendarWeeks')->collapse()->where('in_month', true);
        $this->assertCount(29, $days);
        $this->assertSame(29, $days->last()['date']->day);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-31 18:00:00', 'UTC'));
        $response = $this->get('/admin/lotteries/records')->assertOk();
        $this->assertSame('2026-11', $response->viewData('selectedMonth')->format('Y-m'));
    }

    public function test_3d_results_preserve_three_digits_and_weekend_records(): void
    {
        $this->result(15, '007', '2026-11-01 15:30:00', 3);
        $response = $this->get('/admin/lotteries/records?type=3&year=2026&month=11')->assertOk()
            ->assertSee('MM 3D Calendar')->assertSee('03:30 PM')->assertSee('007');
        $day = $response->viewData('calendarWeeks')->collapse()
            ->first(fn ($day) => $day['date']->toDateString() === '2026-11-01');
        $this->assertSame('007', $day['results']['15:30']['number']);
        $this->assertCount(1, $day['results']);
    }

    public function test_full_month_is_not_truncated_at_one_hundred_records(): void
    {
        for ($day = 1; $day <= 31; $day++) {
            foreach ([11, 12, 13, 14] as $clock) {
                $this->result($clock, '07', sprintf('2026-10-%02d 16:30:00', $day));
            }
        }
        $response = $this->get('/admin/lotteries/records?year=2026&month=10')->assertOk();
        $this->assertSame(124, $response->viewData('recordCount'));
        $day = $response->viewData('calendarWeeks')->collapse()
            ->first(fn ($day) => $day['date']->toDateString() === '2026-10-31');
        $this->assertCount(4, $day['results']);
    }

    public function test_latest_saved_result_is_shown_when_a_draw_has_duplicates(): void
    {
        $this->result(11, '07', '2026-11-09 09:30:00');
        $this->result(11, '08', '2026-11-09 09:31:00');
        $response = $this->get('/admin/lotteries/records?year=2026&month=11')->assertOk();
        $day = $response->viewData('calendarWeeks')->collapse()
            ->first(fn ($day) => $day['date']->toDateString() === '2026-11-09');
        $this->assertSame('08', $day['results']['09:30']['number']);
    }

    public function test_invalid_month_filters_are_rejected(): void
    {
        $this->getJson('/admin/lotteries/records?month=13&year=bad&type=4')->assertUnprocessable()
            ->assertJsonValidationErrors(['month', 'year', 'type']);
    }

    public function test_admin_can_correct_history_and_fill_missing_draws_without_changing_operational_tables(): void
    {
        $this->result(11, '99', '2026-11-06 09:30:00');
        $this->result(12, '88', '2026-11-06 12:01:00');
        $this->result(14, '77', '2026-11-06 16:30:00');
        DB::table('vouchers')->insert(['user_id' => auth()->id(), 'lottery_type_id' => 2,
            'clock_id' => 12, 'number' => '07', 'amount' => 100, 'win' => 0, 'verified' => 1,
            'created_at' => '2026-11-06 11:00:00']);
        DB::table('numbers')->insert(['lottery_type_id' => 2, 'clock_id' => 12,
            'number' => '07', 'sell' => 1000, 'demand' => 100, 'report' => 20, 'disable' => 1]);
        DB::table('hot_numbers')->insert(['lottery_type_id' => 2, 'clock_id' => 12, 'number' => '07']);
        $tables = ['users', 'vouchers', 'numbers', 'hot_numbers', 'wallet_histories', 'lottery_types', 'lottery_clock'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = DB::table($table)->get()->toJson();
        }

        $this->put(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'source' => 'calendar', 'numbers' => ['09:30' => '07', '12:01' => '00', '14:00' => '42', '16:30' => '12']])
            ->assertRedirect(route('admin.lotteries.records', ['type' => 2, 'year' => 2026, 'month' => 11]))
            ->assertSessionHas('history_status');
        foreach ([11 => '07', 12 => '00', 13 => '42', 14 => '12'] as $clock => $number) {
            $this->assertDatabaseHas('lotteries', ['clock_id' => $clock, 'number' => $number]);
        }
        $this->assertDatabaseCount('lotteries', 4);
        $this->assertDatabaseHas('lotteries', ['clock_id' => 13, 'created_at' => '2026-11-06 14:00:00']);
        $this->getJson('/api/lotteries/records?type=2&year=2026&month=11')
            ->assertOk()->assertJsonCount(4)->assertJsonFragment(['number' => '07'])
            ->assertJsonFragment(['number' => '00']);
        foreach ($tables as $table) {
            $this->assertSame($before[$table], DB::table($table)->get()->toJson(), $table.' must remain unchanged');
        }
    }

    public function test_history_editor_renders_saved_values_and_preserves_dashboard_context(): void
    {
        $this->result(11, '07', '2026-11-06 09:30:00');
        $this->get(route('admin.lotteries.edit-day', ['type' => 2, 'date' => '2026-11-06',
            'source' => 'dashboard', 'year' => 2025]))->assertOk()
            ->assertSee('History correction only')->assertSee('value="07"', false)
            ->assertSee(route('admin.index', ['date' => '2026-11-06', 'year' => 2025]));
        $this->put(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'source' => 'dashboard', 'year' => 2025, 'numbers' => ['09:30' => '08']])
            ->assertRedirect(route('admin.index', ['date' => '2026-11-06', 'year' => 2025]));
    }

    public function test_history_edits_target_latest_duplicate_and_blank_does_not_delete(): void
    {
        $this->result(11, '07', '2026-11-06 09:30:00');
        $this->result(11, '08', '2026-11-06 09:31:00');
        $this->result(12, '99', '2026-11-06 12:01:00');
        $this->put(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'numbers' => ['09:30' => '00', '12:01' => '']])->assertRedirect();
        $this->assertDatabaseHas('lotteries', ['clock_id' => 11, 'number' => '07', 'created_at' => '2026-11-06 09:30:00']);
        $this->assertDatabaseHas('lotteries', ['clock_id' => 11, 'number' => '00', 'created_at' => '2026-11-06 09:31:00']);
        $this->assertDatabaseHas('lotteries', ['clock_id' => 12, 'number' => '99']);
        $this->assertDatabaseCount('lotteries', 3);
    }

    public function test_three_digit_history_can_be_filled_on_a_weekend_draw_date(): void
    {
        $this->put(route('admin.lotteries.update-day'), ['type' => 3, 'date' => '2026-11-01',
            'numbers' => ['15:30' => '007']])->assertRedirect();
        $this->assertDatabaseHas('lotteries', ['lottery_type_id' => 3, 'clock_id' => 15,
            'number' => '007', 'created_at' => '2026-11-01 15:30:00']);
    }

    public function test_invalid_numbers_dates_and_draws_cannot_write_history(): void
    {
        foreach ([
            ['numbers' => ['09:30' => '7']], ['numbers' => ['09:30' => '007']],
            ['numbers' => ['09:30' => 'ab']], ['numbers' => ['10:00' => '07']],
            ['date' => '2026-11-09'], ['date' => '2026-02-30'], ['type' => 1],
            ['date' => '2026-11-08', 'numbers' => ['16:30' => '07']],
        ] as $overrides) {
            $this->putJson(route('admin.lotteries.update-day'), array_replace([
                'type' => 2, 'date' => '2026-11-06', 'numbers' => ['09:30' => '07'],
            ], $overrides))->assertUnprocessable();
        }
        DB::table('clocks')->where('id', 11)->delete();
        $this->putJson(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'numbers' => ['09:30' => '07']])->assertUnprocessable();
        $this->assertDatabaseCount('lotteries', 0);
    }

    public function test_non_admin_and_guest_cannot_edit_history(): void
    {
        auth()->user()->forceFill(['admin' => 0])->save();
        $this->put(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'numbers' => ['09:30' => '07']])->assertSee('Access Denied');
        auth()->logout();
        $this->put(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'numbers' => ['09:30' => '07']])->assertRedirect(route('login'));
        $this->assertDatabaseCount('lotteries', 0);
    }

    public function test_dashboard_shows_saved_results_for_selected_day_and_edit_links(): void
    {
        // The dashboard's existing report queries use MySQL date functions.
        $pdo = DB::connection()->getPdo();
        foreach (['YEAR' => 'Y', 'MONTH' => 'm', 'DAY' => 'd'] as $function => $format) {
            $pdo->sqliteCreateFunction($function, fn ($value) => $value ? (int) date($format, strtotime($value)) : null);
        }
        $this->result(11, '07', '2026-11-06 09:30:00');
        $response = $this->get(route('admin.index', ['date' => '2026-11-06']))->assertOk()
            ->assertDontSee('Saved results')->assertSee('Edit MM 2D history')
            ->assertSee('Edit MM 3D history')->assertSee('data-target="#history-edit-modal"', false)
            ->assertSee('id="2d_result_num_0930">07</strong>', false)
            ->assertSee(route('admin.lotteries.edit-day', ['type' => 2, 'date' => '2026-11-06', 'source' => 'dashboard']));
        $this->assertSame('07', $response->viewData('dailyHistory')[2]['09:30']['number']);
        $this->assertNull($response->viewData('dailyHistory')[2]['14:00']['number']);
    }

    public function test_modal_loads_the_selected_days_values_and_disables_future_draws(): void
    {
        $this->result(11, '07', '2026-11-08 09:30:00');
        $this->getJson(route('admin.lotteries.edit-day', ['type' => 2, 'date' => '2026-11-08']))
            ->assertOk()->assertJsonPath('type', 2)->assertJsonPath('date', '2026-11-08')
            ->assertJsonPath('draws.09:30.number', '07')->assertJsonPath('draws.09:30.due', true)
            ->assertJsonPath('draws.16:30.due', false);
        $this->get('/admin/lotteries/records?year=2026&month=11')->assertOk()
            ->assertSee('data-target="#history-edit-modal"', false)
            ->assertSee('id="history-edit-modal"', false)->assertSee('admin-history-modal.js');
    }

    public function test_modal_save_returns_json_and_validation_retains_existing_record(): void
    {
        $this->result(11, '07', '2026-11-06 09:30:00');
        $this->putJson(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'numbers' => ['09:30' => 'bad']])->assertUnprocessable()->assertJsonValidationErrors('numbers.09:30');
        $this->assertDatabaseHas('lotteries', ['number' => '07']);
        $this->putJson(route('admin.lotteries.update-day'), ['type' => 2, 'date' => '2026-11-06',
            'numbers' => ['09:30' => '00']])->assertOk()->assertJsonStructure(['message'])
            ->assertSessionHas('history_status');
        $this->assertDatabaseHas('lotteries', ['number' => '00']);
        $this->assertDatabaseCount('lotteries', 1);
    }
}
