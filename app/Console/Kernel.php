<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->call(function () {
        //     Log::channel('single')->info('Scheduler heartbeat: schedule:run is running.', [
        //         'time' => now('Asia/Yangon')->toDateTimeString(),
        //         'timezone' => 'Asia/Yangon',
        //     ]);
        // })->everyMinute()->name('scheduler-heartbeat');

        $schedule->command('inspire')->hourly();

        // History only: these commands do not settle vouchers or reset numbers.
        // Arm capture one minute early; the API server clock selects the exact second.
        foreach (['09:30' => '09:29', '14:00' => '13:59'] as $time => $startTime) {
            $schedule->command('lottery:record-thai-2d', [$time])
                ->timezone('Asia/Yangon')
                ->weekdays()
                ->dailyAt($startTime)
                ->withoutOverlapping(5);
        }
        
        $schedule->command('command:runat1201pm')->dailyAt('12:01');
        $schedule->command('command:runat430pm')->dailyAt('16:30');
        $schedule->command('command:runtaskfor3d')->dailyAt('15:30');
        $schedule->command('command:runat0001am')->timezone('Asia/Yangon')->dailyAt('00:01');
      
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
