<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RecordThai2DSnapshotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['lottery.thai_2d_extra_draws_enabled' => true]);

        // Never migrate or write to the project's configured MySQL database.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('lottery_types')->insert(['id' => 2, 'type' => 'Thai 2D', 'release_mode' => 0, 'open' => 0]);
        DB::table('clocks')->insert([
            ['id' => 11, 'hour' => 9, 'minute' => 30, 'morning' => 1],
            ['id' => 13, 'hour' => 2, 'minute' => 0, 'morning' => 0],
        ]);
        $this->freezeAt('2026-11-09 09:30:00');
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function freezeAt(string $time): void
    {
        $now = CarbonImmutable::parse($time, 'Asia/Yangon');
        CarbonImmutable::setTestNow($now);
        Carbon::setTestNow($now);
    }

    private function fakeLive(array $overrides = []): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['api.thaistock2d.com/live' => Http::sequence()->push([
            'server_time' => CarbonImmutable::now('Asia/Yangon')->toDateTimeString(),
            'live' => array_merge([
            'twod' => '07', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:00',
        ], $overrides)])->push([
            'server_time' => CarbonImmutable::now('Asia/Yangon')->setSecond(4)->toDateTimeString(),
            'live' => ['twod' => '99', 'date' => '2026-11-09',
                'time' => CarbonImmutable::now('Asia/Yangon')->setSecond(4)->toDateTimeString()],
        ])]);
    }

    public function test_disabled_draws_do_not_fetch_save_or_register_snapshot_schedules(): void
    {
        config(['lottery.thai_2d_extra_draws_enabled' => false]);
        Http::fake();
        foreach (['09:30', '14:00'] as $time) {
            $this->freezeAt('2026-11-09 '.$time.':00');
            $this->artisan('lottery:record-thai-2d', ['time' => $time])->assertExitCode(0);
        }
        Http::assertNothingSent();
        $this->assertDatabaseCount('lotteries', 0);
        $events = collect($this->app->make(Schedule::class)->events());
        $this->assertFalse($events->contains(fn ($event) => str_contains($event->command, 'lottery:record-thai-2d')));
        $this->assertTrue($events->contains(fn ($event) => str_contains($event->command, 'command:runat1201pm')));
    }

    public function test_both_times_save_history_only_even_in_manual_mode(): void
    {
        DB::table('users')->insert(['id' => 1, 'name' => 'Test', 'email' => 'test@example.com',
            'phone' => '123', 'password' => 'unused', 'balance' => 10000]);
        foreach ([11, 13] as $clockId) {
            DB::table('vouchers')->insert(['user_id' => 1, 'lottery_type_id' => 2,
                'clock_id' => $clockId, 'number' => '07', 'amount' => 100, 'created_at' => now()]);
            DB::table('numbers')->insert(['lottery_type_id' => 2, 'clock_id' => $clockId,
                'number' => '07', 'sell' => 10000, 'demand' => 100, 'report' => 50, 'disable' => 1]);
            DB::table('hot_numbers')->insert(['lottery_type_id' => 2, 'clock_id' => $clockId, 'number' => '07']);
        }
        $tables = ['users', 'vouchers', 'numbers', 'hot_numbers', 'wallet_histories', 'lottery_clock', 'lottery_types'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = DB::table($table)->get()->toJson();
        }

        foreach (['09:30' => 11, '14:00' => 13] as $time => $clockId) {
            $this->freezeAt('2026-11-09 '.$time.':00');
            $this->fakeLive(['time' => '2026-11-09 '.$time.':00']);
            $this->artisan('lottery:record-thai-2d', ['time' => $time])->assertExitCode(0);
            $this->assertDatabaseHas('lotteries', ['lottery_type_id' => 2, 'clock_id' => $clockId,
                'number' => '07', 'created_at' => '2026-11-09 '.$time.':00']);
        }

        $this->assertDatabaseCount('lotteries', 2);
        foreach ($tables as $table) {
            $this->assertSame($before[$table], DB::table($table)->get()->toJson(), $table.' must remain unchanged');
        }
        $this->getJson('/api/lotteries/records?type=2&year=2026&month=11')
            ->assertOk()->assertJsonCount(2)->assertJsonFragment(['number' => '07']);
    }

    public function test_repeated_run_preserves_the_first_record_without_fetching_again(): void
    {
        $this->fakeLive();
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(0);
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(0);
        $this->assertDatabaseCount('lotteries', 1);
        Http::assertSentCount(1);

        $this->freezeAt('2026-11-10 09:30:00');
        $this->fakeLive(['date' => '2026-11-10', 'time' => '2026-11-10 09:30:00', 'twod' => '00']);
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(0);
        $this->assertDatabaseCount('lotteries', 2);
    }

    /** @dataProvider invalidLiveValues */
    public function test_invalid_or_stale_source_is_not_saved(array $live): void
    {
        $this->fakeLive($live);
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);
        $this->assertDatabaseCount('lotteries', 0);
    }

    public static function invalidLiveValues(): array
    {
        return [
            'placeholder' => [['twod' => '--']],
            'one digit' => [['twod' => '7']],
            'three digits' => [['twod' => '107']],
            'non-numeric' => [['twod' => 'ab']],
            'missing number' => [['twod' => null]],
            'old date' => [['date' => '2026-11-06']],
            'old timestamp' => [['time' => '2026-11-09 09:24:59']],
            'yesterday timestamp' => [['time' => '2026-11-06 09:30:00']],
            'future timestamp' => [['time' => '2026-11-09 09:31:01']],
            'malformed timestamp' => [['time' => 'invalid']],
            'missing timestamp' => [['time' => null]],
            'timestamp with trailing text' => [['time' => '2026-11-09 09:30:00 trailing']],
            'invalid seconds' => [['time' => '2026-11-09 09:30:60']],
        ];
    }

    public function test_http_failure_and_malformed_json_are_not_saved(): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::sequence()->push([], 503)->push('not json')]);
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);
        $this->assertDatabaseCount('lotteries', 0);
    }

    public function test_timeout_is_logged_and_does_not_save(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('API timed out');
        });
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);
        $this->assertDatabaseCount('lotteries', 0);
    }

    public function test_wrong_time_unsupported_slot_and_weekend_do_not_fetch(): void
    {
        Http::fake();
        $this->artisan('lottery:record-thai-2d', ['time' => '14:00'])->assertExitCode(1);
        $this->artisan('lottery:record-thai-2d', ['time' => '12:01'])->assertExitCode(1);
        $this->freezeAt('2026-11-08 09:30:00');
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(0);
        Http::assertNothingSent();
        $this->assertDatabaseCount('lotteries', 0);
    }

    public function test_missing_clock_fails_without_creating_a_betting_draw(): void
    {
        DB::table('clocks')->where('id', 11)->delete();
        Http::fake();
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);
        Http::assertNothingSent();
        $this->assertDatabaseCount('lottery_clock', 0);
        $this->assertDatabaseCount('lotteries', 0);
    }

    public function test_scheduler_registers_both_myanmar_times_and_keeps_existing_releases(): void
    {
        $events = collect($this->app->make(Schedule::class)->events());
        $snapshots = $events->filter(fn ($event) => str_contains($event->command, 'lottery:record-thai-2d'))->values();
        $this->assertCount(2, $snapshots);
        $this->assertSame(['29 9 * * 1-5', '59 13 * * 1-5'], $snapshots->pluck('expression')->all());
        foreach ($snapshots as $event) {
            $this->assertSame('Asia/Yangon', $event->timezone);
            $this->assertTrue($event->withoutOverlapping);
        }
        $this->assertTrue($events->contains(fn ($event) => str_contains($event->command, 'command:runat1201pm')
            && $event->expression === '1-16 12 * * *'));
        $this->assertTrue($events->contains(fn ($event) => str_contains($event->command, 'command:runat430pm')
            && $event->expression === '30-45 16 * * *'));
    }

    public function test_capture_saves_first_valid_digit_in_window_for_both_slots(): void
    {
        foreach (['09:30' => 11, '14:00' => 13] as $time => $clockId) {
            $target = CarbonImmutable::parse('2026-11-09 '.$time.':00', 'Asia/Yangon');
            // Simulate the scheduler starting eight seconds into the previous minute.
            $this->freezeAt($target->subSeconds(52)->toDateTimeString());
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::preventStrayRequests();
            Http::fake(['api.thaistock2d.com/live' => Http::sequence()
                ->push(['server_time' => $target->subSecond()->toDateTimeString(),
                    'live' => ['twod' => '99', 'date' => '2026-11-09',
                        'time' => $target->subSecond()->toDateTimeString()]])
                ->push(['server_time' => $target->toDateTimeString(), 'live' => [
                    'twod' => '07', 'date' => '2026-11-09',
                    'time' => $target->subSecond()->toDateTimeString(),
                ]])
                ->push(['server_time' => $target->addSecond()->toDateTimeString(), 'live' => [
                    'twod' => '89', 'date' => '2026-11-09',
                    'time' => $target->addSecond()->toDateTimeString(),
                ]])]);

            $this->artisan('lottery:record-thai-2d', ['time' => $time])->assertExitCode(0);

            Http::assertSentCount(3);
            $this->assertDatabaseHas('lotteries', ['clock_id' => $clockId, 'number' => '89',
                'created_at' => $target->toDateTimeString()]);
        }
        $this->assertDatabaseCount('lotteries', 2);
    }

    public function test_late_live_second_is_logged_without_saving_a_later_digit(): void
    {
        $this->freezeAt('2026-11-09 09:30:08');
        Log::spy();
        $this->fakeLive(['twod' => '99', 'time' => '2026-11-09 09:30:08']);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);

        $this->assertDatabaseCount('lotteries', 0);
        Log::shouldHaveReceived('warning')->once()->withArgs(fn ($message, $context) =>
            $context['target_time'] === '2026-11-09 09:30:00'
            && $context['live_time'] === '2026-11-09 09:30:08');
    }

    public function test_live_time_selects_the_digit_even_when_server_and_local_clocks_are_ahead(): void
    {
        $this->freezeAt('2026-11-09 09:30:08');
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'server_time' => '2026-11-09 09:30:08',
            'live' => ['twod' => '00', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:00'],
        ])]);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(0);

        $this->assertDatabaseHas('lotteries', ['clock_id' => 11, 'number' => '00',
            'created_at' => '2026-11-09 09:30:00']);
    }

    /** @dataProvider captureWindowSeconds */
    public function test_polling_accepts_first_valid_digit_at_either_later_second_for_both_slots(int $second): void
    {
        foreach (['09:30' => 11, '14:00' => 13] as $time => $clockId) {
            $target = CarbonImmutable::parse('2026-11-09 '.$time.':00', 'Asia/Yangon');
            // Local recording time does not select the digit.
            $this->freezeAt($target->addSeconds(8)->toDateTimeString());
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::preventStrayRequests();
            Http::fake(['api.thaistock2d.com/live' => Http::sequence()
                ->push(['server_time' => $target->addSeconds(8)->toDateTimeString(),
                    'live' => ['twod' => '07', 'date' => '2026-11-09',
                        'time' => $target->subSecond()->toDateTimeString()]])
                ->push(['server_time' => $target->addSeconds(9)->toDateTimeString(),
                    'live' => ['twod' => '99', 'date' => '2026-11-09',
                        'time' => $target->addSeconds($second)->toDateTimeString()]])]);

            $this->artisan('lottery:record-thai-2d', ['time' => $time])->assertExitCode(0);
            $this->assertDatabaseHas('lotteries', ['clock_id' => $clockId, 'number' => '99',
                'created_at' => $target->toDateTimeString()]);
            Http::assertSentCount(2);
        }
    }

    public static function captureWindowSeconds(): array
    {
        return [[1], [2], [3]];
    }

    public function test_invalid_digit_in_window_is_skipped_until_first_valid_digit(): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::sequence()
            ->push(['server_time' => '2026-11-09 09:30:00',
                'live' => ['twod' => '--', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:00']])
            ->push(['server_time' => '2026-11-09 09:30:01',
                'live' => ['twod' => '89', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:00']])]);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(0);
        $this->assertDatabaseHas('lotteries', ['number' => '89']);
        Http::assertSentCount(2);
    }

    public function test_digit_at_second_four_is_outside_the_window(): void
    {
        $this->freezeAt('2026-11-09 09:30:04');
        $this->fakeLive(['time' => '2026-11-09 09:30:04']);
        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);
        $this->assertDatabaseCount('lotteries', 0);
        Http::assertSentCount(1);
    }

    public function test_live_time_controls_both_slots_when_server_time_is_behind(): void
    {
        foreach (['09:30' => 11, '14:00' => 13] as $time => $clockId) {
            $target = CarbonImmutable::parse('2026-11-09 '.$time.':00', 'Asia/Yangon');
            $this->freezeAt($target->toDateTimeString());
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::preventStrayRequests();
            Http::fake(['api.thaistock2d.com/live' => Http::sequence()
                ->push(['server_time' => $target->subSecond()->toDateTimeString(),
                    'live' => ['twod' => '00', 'date' => '2026-11-09',
                        'time' => $target->toDateTimeString()]])]);

            $this->artisan('lottery:record-thai-2d', ['time' => $time])->assertExitCode(0);

            $this->assertDatabaseHas('lotteries', ['clock_id' => $clockId, 'number' => '00']);
            Http::assertSentCount(1);
        }
    }

    public function test_live_time_after_window_is_rejected_even_when_server_time_is_at_target(): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'server_time' => '2026-11-09 09:30:00',
            'live' => ['twod' => '99', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:04'],
        ])]);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);

        $this->assertDatabaseCount('lotteries', 0);
        Http::assertSentCount(1);
    }

    /** @dataProvider invalidServerTimes */
    public function test_server_time_does_not_control_live_digit_selection($serverTime): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'server_time' => $serverTime,
            'live' => ['twod' => '07', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:00'],
        ])]);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(0);
        $this->assertDatabaseHas('lotteries', ['clock_id' => 11, 'number' => '07']);
    }

    public static function invalidServerTimes(): array
    {
        return [[null], ['invalid'], ['2026-11-08 09:30:00'], ['2026-11-10 09:30:00'],
            ['2026-11-09 09:30:00 trailing'], ['2026-11-09 09:30:60']];
    }
}
