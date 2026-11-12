<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Models\Voucher;
use App\Models\LotteryType;
use App\Models\Number;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RunTaskFor3D extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:runtaskfor3d';

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
        $drawDate = now('Asia/Yangon');
        $day = $drawDate->day;
        $lottery_type_id = 3;
        $clock_id = 5;

        $lottery_type = LotteryType::find($lottery_type_id);
        if($lottery_type->release_mode == 0) return;

        if($day == 1 || $day == 16){
            
            $result = Http::get("https://www.calamuseducation.com/api-3d.php")->throw()->json();

            $win_num = (string) $result['number'];
            validator(['number' => $win_num], ['number' => ['required', 'regex:/\A[0-9]{3}\z/']])->validate();

            $lottery = new Lottery();
            $lottery->lottery_type_id = 3;
            $lottery->clock_id = 5;        
            $lottery->number = $win_num;         
            $lottery->save();

            Log::channel('single')->info('Lottery winning digit recorded.', [
                'lottery_type_id' => $lottery_type_id,
                'clock_id' => $clock_id,
                'draw_time' => '15:30',
                'win_digit' => (string) $win_num,
                'recorded_at' => now('Asia/Yangon')->toDateTimeString(),
                'timezone' => 'Asia/Yangon',
            ]);

            app(\App\Services\ThreeDWinRules::class)->apply(
                Voucher::where('lottery_type_id', 3)->where('clock_id', 5)
                    ->whereDate('created_at', $drawDate->toDateString()),
                $win_num
            );

            Number::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->update(['demand'=>0,'disable'=>0]);
        }

        return Command::SUCCESS;
    }
}
