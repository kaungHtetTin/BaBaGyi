<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MidnightLotteryResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Carbon::setTestNow(Carbon::parse('2026-11-08 17:31:00', 'UTC'));
        DB::table('lottery_types')->insert([
            ['id' => 1, 'type' => 'BTC 2D', 'release_mode' => 0, 'open' => 0],
            ['id' => 2, 'type' => 'Thai 2D', 'release_mode' => 0, 'open' => 0],
            ['id' => 3, 'type' => 'Thai 3D', 'release_mode' => 1, 'open' => 1],
            ['id' => 8, 'type' => 'Additional lottery', 'release_mode' => 0, 'open' => 0],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_all_lotteries_return_to_auto_and_only_thai_2d_availability_changes(): void
    {
        $this->artisan('command:runat0001am')->assertExitCode(0);
        $this->assertSame(0, DB::table('lottery_types')->where('release_mode', '!=', 1)->count());
        $this->assertSame(4, DB::table('lottery_types')->count());
        $this->assertSame([1 => 0, 2 => 1, 3 => 1, 8 => 0], DB::table('lottery_types')
            ->orderBy('id')->pluck('open', 'id')->all());
        $this->artisan('command:runat0001am')->assertExitCode(0);
        $this->assertSame(4, DB::table('lottery_types')->where('release_mode', 1)->count());
    }

    public function test_holiday_uses_the_myanmar_date_and_keeps_thai_2d_closed_in_auto_mode(): void
    {
        // 17:31 UTC on November 8 is 00:01 Myanmar time on November 9.
        DB::table('holidays')->insert(['title' => 'Test holiday', 'month' => 11, 'day' => 9]);
        DB::table('lottery_types')->where('id', 2)->update(['open' => 1]);
        $this->artisan('command:runat0001am')->assertExitCode(0);
        $this->assertDatabaseHas('lottery_types', ['id' => 2, 'open' => 0, 'release_mode' => 1]);
        $this->assertSame(4, DB::table('lottery_types')->where('release_mode', 1)->count());
    }

    public function test_reset_still_handles_lotteries_when_thai_2d_is_not_configured(): void
    {
        DB::table('lottery_types')->where('id', 2)->delete();
        $this->artisan('command:runat0001am')->assertExitCode(0);
        $this->assertSame(3, DB::table('lottery_types')->where('release_mode', 1)->count());
    }

    public function test_reset_is_scheduled_daily_at_0001_myanmar_time(): void
    {
        $events = collect($this->app->make(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command, 'command:runat0001am'));
        $this->assertCount(1, $events);
        $this->assertSame('1 0 * * *', $events->first()->expression);
        $this->assertSame('Asia/Yangon', $events->first()->timezone);
    }
}
