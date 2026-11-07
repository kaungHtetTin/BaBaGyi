<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use App\Models\LotteryType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunTaskAt0001AM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:runat0001am';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all lotteries to automatic release and update Thai 2D holiday availability';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now('Asia/Yangon');

        DB::transaction(function () use ($today) {
            LotteryType::query()->update(['release_mode' => 1]);

            $isHoliday = Holiday::where('day', $today->day)
                ->where('month', $today->month)->exists();

            LotteryType::whereKey(2)->update(['open' => ! $isHoliday]);
        });

        return Command::SUCCESS;
    }
}
