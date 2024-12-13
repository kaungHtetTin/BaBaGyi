<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Holiday;
use App\Models\LotteryType;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $holidays = Holiday::where('day',date('d'))
        ->where('month',date('m'))
        ->get();

        $lottery = LotteryType::find(2);
        if(count($holidays)>0){
            $lottery->open = 0;
        }else{
            $lottery->open = 1;
        }

        $lottery->save();

        return Command::SUCCESS;
    }
}
