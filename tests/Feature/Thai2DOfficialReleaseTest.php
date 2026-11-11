<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
                $this->official('12:00:00', '07'), $this->official('11:00:00', '55')],
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
        foreach (['command:runat1201pm' => '12:00:00', 'command:runat430pm' => '16:30:00'] as $command => $time) {
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
}
