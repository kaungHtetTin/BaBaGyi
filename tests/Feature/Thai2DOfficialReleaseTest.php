<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class Thai2DOfficialReleaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        foreach (['YEAR' => 'Y', 'MONTH' => 'm', 'DAY' => 'd'] as $function => $format) {
            DB::connection()->getPdo()->sqliteCreateFunction($function,
                fn ($value) => $value ? date($format, strtotime($value)) : null);
        }
        // Match PHP's date() used by the existing voucher settlement queries.
        $now = CarbonImmutable::parse(date('Y-m-d').' 16:30:00', 'Asia/Yangon');
        CarbonImmutable::setTestNow($now);
        Carbon::setTestNow($now);
        DB::table('lottery_types')->insert(['id' => 2, 'type' => 'Thai 2D', 'release_mode' => 1]);
        DB::table('clocks')->insert([
            ['id' => 2, 'hour' => 12, 'minute' => 1, 'morning' => 0],
            ['id' => 4, 'hour' => 4, 'minute' => 30, 'morning' => 0],
        ]);
        DB::table('users')->insert(['id' => 1, 'name' => 'Test', 'phone' => '123',
            'email' => 'release@example.com', 'password' => 'unused', 'balance' => 10000]);
        foreach ([2, 4] as $clock) {
            foreach (['07', '00', '99'] as $number) {
                DB::table('vouchers')->insert(['user_id' => 1, 'lottery_type_id' => 2,
                    'clock_id' => $clock, 'number' => $number, 'amount' => 100,
                    'created_at' => date('Y-m-d').' 10:00:00']);
            }
            DB::table('numbers')->insert(['lottery_type_id' => 2, 'clock_id' => $clock,
                'number' => '07', 'sell' => 1000, 'demand' => 100, 'report' => 20, 'disable' => 1]);
            DB::table('hot_numbers')->insert(['lottery_type_id' => 2, 'clock_id' => $clock, 'number' => '07']);
        }
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function official(string $time, string $number): array
    {
        return ['open_time' => $time, 'twod' => $number,
            'stock_date' => CarbonImmutable::now('Asia/Yangon')->toDateString(), 'history_id' => '12345'];
    }

    public function test_both_commands_use_matching_official_results_and_preserve_settlement_behavior(): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'live' => ['twod' => '99'],
            // Deliberately out of order: select by time, never by array index.
            'result' => [$this->official('16:30:00', '00'), $this->official('15:00:00', '88'),
                $this->official('12:01:00', '07'), $this->official('11:00:00', '55')],
        ])]);

        foreach (['command:runat1201pm' => [2, '07'], 'command:runat430pm' => [4, '00']] as $command => [$clock, $winner]) {
            $this->artisan($command)->assertExitCode(0);
            $this->assertDatabaseHas('lotteries', ['clock_id' => $clock, 'number' => $winner]);
            $this->assertDatabaseHas('vouchers', ['clock_id' => $clock, 'number' => $winner, 'win' => 1]);
            $this->assertDatabaseHas('vouchers', ['clock_id' => $clock, 'number' => '99', 'win' => 0, 'verified' => 1]);
            $this->assertDatabaseHas('numbers', ['clock_id' => $clock, 'demand' => 0, 'disable' => 0]);
            $this->assertDatabaseMissing('hot_numbers', ['clock_id' => $clock]);
        }
        $this->assertDatabaseCount('lotteries', 2);
        $this->assertDatabaseHas('users', ['id' => 1, 'balance' => 10000]);
    }

    /** @dataProvider invalidResults */
    public function test_invalid_official_results_leave_all_records_and_settlement_untouched(string $scenario): void
    {
        $tables = ['lotteries', 'vouchers', 'numbers', 'hot_numbers', 'users'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = DB::table($table)->get()->toJson();
        }
        foreach (['command:runat1201pm' => '12:01:00', 'command:runat430pm' => '16:30:00'] as $command => $time) {
            $result = $this->official($time, '07');
            switch ($scenario) {
                case 'placeholder': $result['twod'] = '--'; break;
                case 'one digit': $result['twod'] = '7'; break;
                case 'numeric': $result['twod'] = 7; break;
                case 'old date': $result['stock_date'] = CarbonImmutable::now('Asia/Yangon')->subDay()->toDateString(); break;
                case 'wrong time': $result['open_time'] = '11:00:00'; break;
                case 'unfinished': $result['history_id'] = null; break;
            }
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::preventStrayRequests();
            Http::fake(['api.thaistock2d.com/live' => match ($scenario) {
                'http error' => Http::response([], 503),
                'malformed' => Http::response('invalid json'),
                'missing result' => Http::response(['live' => ['twod' => '99']]),
                default => Http::response(['live' => ['twod' => '99'], 'result' => [$result]]),
            }]);
            $this->artisan($command)->assertExitCode(1);
        }
        foreach ($tables as $table) {
            $this->assertSame($before[$table], DB::table($table)->get()->toJson(), $table);
        }
    }

    public static function invalidResults(): array
    {
        return array_map(fn ($scenario) => [$scenario], ['placeholder', 'one digit', 'numeric',
            'old date', 'wrong time', 'unfinished', 'http error', 'malformed', 'missing result']);
    }

    public function test_manual_mode_does_not_fetch_or_release(): void
    {
        DB::table('lottery_types')->where('id', 2)->update(['release_mode' => 0]);
        Http::fake();
        $this->artisan('command:runat1201pm')->assertExitCode(0);
        $this->artisan('command:runat430pm')->assertExitCode(0);
        Http::assertNothingSent();
        $this->assertDatabaseCount('lotteries', 0);
    }

    /** @dataProvider releaseSlots */
    public function test_delayed_results_are_retried_and_successful_releases_are_not_repeated(string $command, int $clock, string $time): void
    {
        $start = CarbonImmutable::now('Asia/Yangon')->setTimeFromTimeString($time);
        CarbonImmutable::setTestNow($start);
        Carbon::setTestNow($start);
        Http::fake(['api.thaistock2d.com/live' => Http::sequence()
            ->push(['result' => []])
            ->push(['result' => [$this->official($time, '07')]])]);

        $this->artisan($command, ['--scheduled' => true])->assertExitCode(1);
        $this->assertDatabaseCount('lotteries', 0);
        $this->assertDatabaseHas('numbers', ['clock_id' => $clock, 'demand' => 100, 'disable' => 1]);

        CarbonImmutable::setTestNow($start->addMinutes(2));
        Carbon::setTestNow($start->addMinutes(2));
        $this->artisan($command, ['--scheduled' => true])->assertExitCode(0);
        $this->assertDatabaseHas('lotteries', ['clock_id' => $clock, 'number' => '07',
            'created_at' => $start->toDateTimeString()]);
        $this->assertDatabaseHas('vouchers', ['clock_id' => $clock, 'number' => '07', 'win' => 1]);

        // New demand must survive subsequent scheduler ticks.
        DB::table('numbers')->where('clock_id', $clock)->update(['demand' => 500]);
        $this->artisan($command, ['--scheduled' => true])->assertExitCode(0);
        $this->artisan($command)->assertExitCode(0);
        Http::assertSentCount(2);
        $this->assertDatabaseCount('lotteries', 1);
        $this->assertDatabaseHas('numbers', ['clock_id' => $clock, 'demand' => 500]);
    }

    public static function releaseSlots(): array
    {
        return [['command:runat1201pm', 2, '12:01:00'], ['command:runat430pm', 4, '16:30:00']];
    }

    /** @dataProvider releaseSlots */
    public function test_retry_window_has_a_final_warning_and_stops_after_deadline(string $command, int $clock, string $time): void
    {
        $start = CarbonImmutable::now('Asia/Yangon')->setTimeFromTimeString($time);
        Log::spy();
        Http::fake(['api.thaistock2d.com/live' => Http::response(['result' => []])]);
        foreach ([$start->subMinute(), $start->addMinutes(16)] as $outside) {
            CarbonImmutable::setTestNow($outside);
            $this->artisan($command, ['--scheduled' => true])->assertExitCode(0);
        }
        Http::assertNothingSent();

        CarbonImmutable::setTestNow($start->addMinutes(14));
        $this->artisan($command, ['--scheduled' => true])->assertExitCode(1);
        Log::shouldHaveReceived('log')->with('info', \Mockery::any(), \Mockery::any())->once();
        CarbonImmutable::setTestNow($start->addMinutes(15));
        $this->artisan($command, ['--scheduled' => true])->assertExitCode(1);
        Log::shouldHaveReceived('log')->with('warning', 'Thai 2D release failed; admin review required.',
            \Mockery::on(fn ($context) => $context['clock_id'] === $clock))->once();
        $this->assertDatabaseCount('lotteries', 0);
    }

    public function test_release_rolls_back_all_changes_on_database_failure_and_can_retry(): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'result' => [$this->official('12:01:00', '07')],
        ])]);
        DB::unprepared("CREATE TRIGGER fail_reset BEFORE UPDATE ON numbers BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        $before = DB::table('vouchers')->get()->toJson();
        $this->artisan('command:runat1201pm')->assertExitCode(1);
        $this->assertDatabaseCount('lotteries', 0);
        $this->assertSame($before, DB::table('vouchers')->get()->toJson());
        $this->assertDatabaseHas('hot_numbers', ['clock_id' => 2]);

        DB::unprepared('DROP TRIGGER fail_reset');
        $this->artisan('command:runat1201pm')->assertExitCode(0);
        $this->assertDatabaseCount('lotteries', 1);
    }

    public function test_result_created_during_fetch_is_preserved_without_settlement(): void
    {
        Http::fake(function () {
            DB::table('lotteries')->insert(['lottery_type_id' => 2, 'clock_id' => 2,
                'number' => '55', 'created_at' => CarbonImmutable::now('Asia/Yangon')->toDateTimeString()]);

            return Http::response(['result' => [$this->official('12:01:00', '07')]]);
        });
        $before = DB::table('vouchers')->get()->toJson();
        $this->artisan('command:runat1201pm')->assertExitCode(0);
        $this->assertDatabaseCount('lotteries', 1);
        $this->assertDatabaseHas('lotteries', ['clock_id' => 2, 'number' => '55']);
        $this->assertSame($before, DB::table('vouchers')->get()->toJson());
        $this->assertDatabaseHas('numbers', ['clock_id' => 2, 'demand' => 100]);
    }

    public function test_noon_result_must_match_1201_instead_of_1200(): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'result' => [$this->official('12:00:00', '99')],
        ])]);
        $this->artisan('command:runat1201pm')->assertExitCode(1);
        $this->assertDatabaseCount('lotteries', 0);
    }

    /** @dataProvider releaseSlots */
    public function test_schedule_runs_each_retry_minute_in_myanmar_time(string $command, int $clock, string $time): void
    {
        $events = collect($this->app->make(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command ?? '', $command));
        $this->assertCount(1, $events);
        $event = $events->first();
        $this->assertSame('Asia/Yangon', $event->timezone);
        $this->assertTrue($event->withoutOverlapping);
        $this->assertStringContainsString('--scheduled', $event->command);

        $start = CarbonImmutable::now('Asia/Yangon')->setTimeFromTimeString($time);
        foreach (range(-1, 16) as $offset) {
            // Supply UTC to ensure the schedule actually converts to Myanmar time.
            Carbon::setTestNow($start->addMinutes($offset)->utc());
            $this->assertSame($offset >= 0 && $offset <= 15, $event->isDue($this->app));
        }
    }

    public function test_previous_day_does_not_block_release_and_other_dates_vouchers_are_untouched(): void
    {
        $yesterday = CarbonImmutable::now('Asia/Yangon')->subDay()->toDateString();
        DB::table('lotteries')->insert(['lottery_type_id' => 2, 'clock_id' => 2,
            'number' => '99', 'created_at' => $yesterday.' 12:01:00']);
        $id = DB::table('vouchers')->insertGetId(['user_id' => 1, 'lottery_type_id' => 2,
            'clock_id' => 2, 'number' => '07', 'amount' => 100, 'created_at' => $yesterday.' 10:00:00']);
        $before = DB::table('vouchers')->find($id);
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'result' => [$this->official('12:01:00', '07')],
        ])]);
        $this->artisan('command:runat1201pm')->assertExitCode(0);
        $this->assertDatabaseCount('lotteries', 2);
        $this->assertEquals($before, DB::table('vouchers')->find($id));
    }

    public function test_existing_history_is_preserved_without_fetching_or_settling(): void
    {
        DB::table('lotteries')->insert(['lottery_type_id' => 2, 'clock_id' => 2,
            'number' => '55', 'created_at' => CarbonImmutable::now('Asia/Yangon')->toDateTimeString()]);
        Http::fake();
        $before = DB::table('vouchers')->get()->toJson();
        $this->artisan('command:runat1201pm')->assertExitCode(0);
        Http::assertNothingSent();
        $this->assertSame($before, DB::table('vouchers')->get()->toJson());
        $this->assertDatabaseHas('lotteries', ['clock_id' => 2, 'number' => '55']);
    }
}
