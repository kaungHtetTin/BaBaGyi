<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Models\LotteryType;
use App\Models\Voucher;
use App\Models\Number;
use App\Models\HotNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Thai2DOfficialResult;
use Throwable;

class RunTaskAt1201PM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:runat1201pm';

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
        $lottery_type_id = 2;
        $clock_id = 2;

        $lottery_type = LotteryType::find($lottery_type_id);
        if($lottery_type->release_mode == 0) return;
        
        try {
            $result = app(Thai2DOfficialResult::class)->fetch('12:00:00');
        } catch (Throwable $exception) {
            Log::warning('Thai 2D release was skipped.', [
                'clock_id' => $clock_id, 'open_time' => '12:00:00', 'reason' => $exception->getMessage(),
            ]);
            $this->error($exception->getMessage());

            return Command::FAILURE;
        }
        
        $win_num = $result['twod'];
    
        $lottery = new Lottery();
        $lottery->lottery_type_id = $lottery_type_id;
        $lottery->clock_id = $clock_id;        
        $lottery->number = $win_num;         
        $lottery->save();

        Log::channel('single')->info('Lottery winning digit recorded.', [
            'lottery_type_id' => $lottery_type_id,
            'clock_id' => $clock_id,
            'draw_time' => '12:01',
            'win_digit' => (string) $win_num,
            'source' => 'result',
            'open_time' => $result['open_time'],
            'stock_date' => $result['stock_date'],
            'history_id' => $result['history_id'],
            'recorded_at' => now('Asia/Yangon')->toDateTimeString(),
            'timezone' => 'Asia/Yangon',
        ]);

        Voucher::where(DB::raw("DAY(created_at)"),date('d'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',$lottery_type_id)
        ->where('clock_id',$clock_id)
        ->where('number',$win_num)
        ->update(['win'=>1]);

        Voucher::where(DB::raw("DAY(created_at)"),date('d'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',$lottery_type_id)
        ->where('clock_id',$clock_id)
        ->where('number','!=',$win_num)
        ->update(['win'=>0,'verified'=>1]);

        Number::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->update(['demand'=>0,'disable'=>0]);
        HotNumber::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->delete();
    }
}
