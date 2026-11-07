<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Database\Seeders\Thai2DDemoSeeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class Thai2DDemoSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('lottery_types')->insert(['id' => 2, 'type' => 'Thai 2D']);
        DB::table('clocks')->insert([
            ['id' => 11, 'hour' => 9, 'minute' => 30, 'morning' => 1],
            ['id' => 12, 'hour' => 12, 'minute' => 1, 'morning' => 0],
            ['id' => 13, 'hour' => 2, 'minute' => 0, 'morning' => 0],
            ['id' => 14, 'hour' => 4, 'minute' => 30, 'morning' => 0],
        ]);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-08 12:00:00', 'Asia/Yangon'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_october_and_november_demo_history_is_available_in_both_apis(): void
    {
        $this->seed(Thai2DDemoSeeder::class);
        $this->assertDatabaseCount('lotteries', 108);
        $rows = DB::table('lotteries')->orderBy('created_at')->get();
        $this->assertSame('2026-10-01 09:30:00', $rows->first()->created_at);
        $this->assertSame('2026-11-06 16:30:00', $rows->last()->created_at);
        foreach ($rows as $row) {
            $this->assertMatchesRegularExpression('/\A[0-9]{2}\z/', $row->number);
            $this->assertFalse(CarbonImmutable::parse($row->created_at)->isWeekend());
        }
        $this->assertTrue($rows->contains(fn ($row) => str_starts_with($row->number, '0')));
        $this->getJson('/api/lotteries/records?type=2&year=2026&month=10')->assertOk()->assertJsonCount(88);
        $this->getJson('/api/lotteries/records?type=2&year=2026&month=11')->assertOk()->assertJsonCount(20);
        $calendar = $this->getJson('/api/lotteries/calendar?year=2026&month=11')->assertOk()->json('days');
        $day = collect($calendar)->firstWhere('date', '2026-11-06');
        $this->assertSame(['09:30', '12:01', '14:00', '16:30'], array_keys($day['results']));
        foreach ($day['results'] as $result) {
            $this->assertNotNull($result);
        }
        foreach (['vouchers', 'numbers', 'hot_numbers', 'users', 'wallet_histories', 'lottery_clock'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertDatabaseCount('clocks', 4);
        $this->assertDatabaseCount('lottery_types', 1);
    }

    public function test_reruns_preserve_existing_results_and_do_not_add_duplicates(): void
    {
        $id = DB::table('lotteries')->insertGetId(['lottery_type_id' => 2, 'clock_id' => 11,
            'number' => '00', 'created_at' => '2026-10-01 09:31:00', 'updated_at' => '2026-10-01 09:31:00']);
        $this->seed(Thai2DDemoSeeder::class);
        $before = DB::table('lotteries')->orderBy('id')->get()->toJson();
        $this->seed(Thai2DDemoSeeder::class);
        $this->assertSame($before, DB::table('lotteries')->orderBy('id')->get()->toJson());
        $this->assertDatabaseCount('lotteries', 108);
        $this->assertDatabaseHas('lotteries', ['id' => $id, 'number' => '00', 'updated_at' => '2026-10-01 09:31:00']);
    }

    public function test_only_elapsed_draws_are_seeded_today(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-09 10:00:00', 'Asia/Yangon'));
        $this->seed(Thai2DDemoSeeder::class);
        $this->assertDatabaseCount('lotteries', 109);
        $this->assertSame(1, DB::table('lotteries')->whereDate('created_at', '2026-11-09')->count());
        $this->assertDatabaseMissing('lotteries', ['created_at' => '2026-11-09 12:01:00']);
    }

    public function test_missing_configuration_does_not_leave_partial_history(): void
    {
        DB::table('clocks')->where('id', 13)->delete();
        try {
            $this->seed(Thai2DDemoSeeder::class);
            $this->fail('Missing clocks should be reported.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('14:00 clock', $exception->getMessage());
            $this->assertDatabaseCount('lotteries', 0);
        }
    }

    public function test_the_range_stops_at_november_even_if_run_in_december(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-03 10:00:00', 'Asia/Yangon'));
        $this->seed(Thai2DDemoSeeder::class);
        $this->assertDatabaseCount('lotteries', 172);
        $this->assertSame('2026-11-30 16:30:00', DB::table('lotteries')->max('created_at'));
    }
}
