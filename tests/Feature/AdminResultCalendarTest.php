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
}
