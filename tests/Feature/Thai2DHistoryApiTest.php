<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Thai2DHistoryApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('lottery_types')->insert([['id' => 2, 'type' => 'Thai 2D'], ['id' => 3, 'type' => 'Thai 3D']]);
        DB::table('clocks')->insert([
            ['id' => 11, 'hour' => 9, 'minute' => 30, 'morning' => 1],
            ['id' => 12, 'hour' => 12, 'minute' => 1, 'morning' => 0],
            ['id' => 13, 'hour' => 2, 'minute' => 0, 'morning' => 0],
            ['id' => 14, 'hour' => 4, 'minute' => 30, 'morning' => 0],
        ]);
    }

    private function result(int $clock, string $number, string $date, int $type = 2): int
    {
        return DB::table('lotteries')->insertGetId(['lottery_type_id' => $type, 'clock_id' => $clock,
            'number' => $number, 'created_at' => $date, 'updated_at' => $date]);
    }

    public function test_records_keep_old_fields_and_identify_all_four_times(): void
    {
        $afternoon = $this->result(13, '90', '2026-11-09 14:00:00');
        $noon = $this->result(12, '12', '2026-11-09 12:01:00');
        $morning = $this->result(11, '07', '2026-11-09 09:30:00');
        $evening = $this->result(14, '00', '2026-11-09 16:30:00');
        $this->result(11, '99', '2026-11-08 09:30:00');
        $this->result(11, '88', '2026-10-09 09:30:00');
        $this->result(11, '123', '2026-11-09 09:30:00', 3);

        $response = $this->getJson('/api/lotteries/records?type=2&year=2026&month=11')
            ->assertOk()->assertJsonCount(4)
            ->assertJsonStructure([['id', 'lottery_type_id', 'clock_id', 'number', 'created_at', 'updated_at',
                'day', 'd', 'date', 'draw_time', 'draw_label', 'record_only', 'clock']]);
        $records = $response->json();
        $this->assertSame([$morning, $noon, $afternoon, $evening], array_column($records, 'id'));
        $this->assertSame(['09:30', '12:01', '14:00', '16:30'], array_column($records, 'draw_time'));
        $this->assertSame(['09:30 AM', '12:01 PM', '02:00 PM', '04:30 PM'], array_column($records, 'draw_label'));
        $this->assertSame([true, false, true, false], array_column($records, 'record_only'));
        $this->assertSame('07', $records[0]['number']);
        $this->assertSame('00', $records[3]['number']);
        $this->assertSame('2026-11-09', $records[0]['date']);
        $this->assertSame(9, $records[0]['day']);
        $this->assertSame('Mon', $records[0]['d']);
    }

    public function test_calendar_keeps_missing_results_null_and_aligns_all_weekdays(): void
    {
        $this->result(11, '07', '2026-11-09 09:30:00');
        $this->result(13, '42', '2026-11-09 14:00:00');
        $this->result(11, '99', '2026-11-08 09:30:00');
        $response = $this->getJson('/api/lotteries/calendar?year=2026&month=11')->assertOk()
            ->assertJsonPath('timezone', 'Asia/Yangon')->assertJsonPath('type', 2)
            ->assertJsonCount(4, 'draws')->assertJsonCount(21, 'days');
        $this->assertSame([11, 12, 13, 14], array_column($response->json('draws'), 'clock_id'));
        $days = collect($response->json('days'));
        $day = $days->firstWhere('date', '2026-11-09');
        $this->assertSame(['09:30', '12:01', '14:00', '16:30'], array_keys($day['results']));
        $this->assertSame('07', $day['results']['09:30']['number']);
        $this->assertSame('42', $day['results']['14:00']['number']);
        $this->assertNull($day['results']['12:01']);
        $this->assertNull($day['results']['16:30']);
        $this->assertSame('2026-11-02', $days->first()['date']);
        $this->assertSame('2026-11-30', $days->last()['date']);
        foreach ($days as $entry) {
            $this->assertNotContains($entry['d'], ['Sat', 'Sun']);
        }
    }

    public function test_calendar_returns_an_empty_month_with_placeholders(): void
    {
        $this->getJson('/api/lotteries/records?year=2026&month=11')->assertOk()->assertExactJson([]);
        $response = $this->getJson('/api/lotteries/calendar?year=2026&month=11')->assertOk();
        foreach ($response->json('days') as $day) {
            $this->assertSame(array_fill_keys(['09:30', '12:01', '14:00', '16:30'], null), $day['results']);
        }
    }

    public function test_calendar_handles_leap_years_and_a_missing_clock(): void
    {
        DB::table('clocks')->where('id', 11)->delete();
        $response = $this->getJson('/api/lotteries/calendar?year=2024&month=2')->assertOk()
            ->assertJsonCount(21, 'days')->assertJsonPath('draws.0.clock_id', null);
        $this->assertSame('2024-02-29', collect($response->json('days'))->last()['date']);
    }

    public function test_latest_duplicate_release_is_used_in_the_calendar(): void
    {
        $this->result(12, '10', '2026-11-09 12:01:00');
        $this->result(12, '20', '2026-11-09 12:02:00');
        $response = $this->getJson('/api/lotteries/calendar?year=2026&month=11')->assertOk();
        $day = collect($response->json('days'))->firstWhere('date', '2026-11-09');
        $this->assertSame('20', $day['results']['12:01']['number']);
    }

    public function test_other_lottery_types_keep_weekend_results(): void
    {
        $this->result(12, '007', '2026-11-08 12:01:00', 3);
        $this->getJson('/api/lotteries/records?type=3&year=2026&month=11')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.number', '007')
            ->assertJsonPath('0.record_only', false)->assertJsonPath('0.d', 'Sun');
    }

    public function test_default_month_uses_myanmar_time(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-31 18:30:00', 'UTC'));
        try {
            $this->getJson('/api/lotteries/calendar')->assertOk()->assertJsonPath('year', 2026)
                ->assertJsonPath('month', 11);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_invalid_filters_return_validation_errors(): void
    {
        $this->getJson('/api/lotteries/records?month=13&year=bad&type=-1')->assertUnprocessable()
            ->assertJsonValidationErrors(['month', 'year', 'type']);
        $this->getJson('/api/lotteries/calendar?type=3')->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }
}
