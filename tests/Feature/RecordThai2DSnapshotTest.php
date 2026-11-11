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
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'server_time' => CarbonImmutable::now('Asia/Yangon')->toDateTimeString(),
            'live' => array_merge([
            'twod' => '07', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:00',
        ], $overrides)])]);
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

    public function test_capture_waits_for_exact_server_second_for_both_slots(): void
    {
        foreach (['09:30' => 11, '14:00' => 13] as $time => $clockId) {
            $target = CarbonImmutable::parse('2026-11-09 '.$time.':00', 'Asia/Yangon');
            // Simulate the scheduler starting eight seconds into the previous minute.
            $this->freezeAt($target->subSeconds(52)->toDateTimeString());
            Http::swap(new \Illuminate\Http\Client\Factory());
            Http::preventStrayRequests();
            Http::fake(['api.thaistock2d.com/live' => Http::sequence()
                ->push(['server_time' => $target->subSecond()->toDateTimeString(),
                    'live' => ['twod' => '99']])
                ->push(['server_time' => $target->toDateTimeString(), 'live' => [
                    'twod' => '07', 'date' => '2026-11-09',
                    'time' => $target->subSecond()->toDateTimeString(),
                ]])]);

            $this->artisan('lottery:record-thai-2d', ['time' => $time])->assertExitCode(0);

            Http::assertSentCount(2);
            $this->assertDatabaseHas('lotteries', ['clock_id' => $clockId, 'number' => '07',
                'created_at' => $target->toDateTimeString()]);
        }
        $this->assertDatabaseCount('lotteries', 2);
    }

    public function test_late_server_second_is_logged_without_saving_a_later_digit(): void
    {
        $this->freezeAt('2026-11-09 09:30:08');
        Log::spy();
        $this->fakeLive(['twod' => '99', 'time' => '2026-11-09 09:30:08']);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);

        $this->assertDatabaseCount('lotteries', 0);
        Log::shouldHaveReceived('warning')->once()->withArgs(fn ($message, $context) =>
            $context['target_time'] === '2026-11-09 09:30:00'
            && $context['server_time'] === '2026-11-09 09:30:08');
    }

    public function test_api_clock_selects_the_digit_even_when_local_clock_is_ahead(): void
    {
        $this->freezeAt('2026-11-09 09:30:08');
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'server_time' => '2026-11-09 09:30:00',
            'live' => ['twod' => '00', 'date' => '2026-11-09', 'time' => '2026-11-09 09:29:59'],
        ])]);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(0);

        $this->assertDatabaseHas('lotteries', ['clock_id' => 11, 'number' => '00',
            'created_at' => '2026-11-09 09:30:00']);
    }

    public function test_polling_that_skips_the_exact_second_does_not_save(): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::sequence()
            ->push(['server_time' => '2026-11-09 09:29:59'])
            ->push(['server_time' => '2026-11-09 09:30:01',
                'live' => ['twod' => '99', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:01']])]);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);

        $this->assertDatabaseCount('lotteries', 0);
        Http::assertSentCount(2);
    }

    /** @dataProvider invalidServerTimes */
    public function test_missing_or_invalid_server_time_cannot_select_a_digit($serverTime): void
    {
        Http::fake(['api.thaistock2d.com/live' => Http::response([
            'server_time' => $serverTime,
            'live' => ['twod' => '07', 'date' => '2026-11-09', 'time' => '2026-11-09 09:30:00'],
        ])]);

        $this->artisan('lottery:record-thai-2d', ['time' => '09:30'])->assertExitCode(1);
        $this->assertDatabaseCount('lotteries', 0);
    }

    public static function invalidServerTimes(): array
    {
        return [[null], ['invalid'], ['2026-11-08 09:30:00'], ['2026-11-10 09:30:00'],
            ['2026-11-09 09:30:00 trailing'], ['2026-11-09 09:30:60']];
    }
}
