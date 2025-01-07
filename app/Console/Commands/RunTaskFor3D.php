<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Models\Voucher;
use App\Models\LotteryType;
use App\Models\Number;
use Illuminate\Support\Facades\DB;

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
        $day = date('d');
        $lottery_type_id = 3;
        $clock_id = 5;

        $lottery_type = LotteryType::find($lottery_type_id);
        if($lottery_type->release_mode == 0) return;

        if($day == 1 || $day == 16){
            
            $response = file_get_contents("https://www.calamuseducation.com/api-3d.php");
            $result = json_decode($response,true);

            $win_num = $result['number'];

            $lottery = new Lottery();
            $lottery->lottery_type_id = 3;
            $lottery->clock_id = 5;        
            $lottery->number = $win_num;         
            $lottery->save();

            Voucher::where(DB::raw("DAY(created_at)"),date('d'))
            ->where(DB::raw("MONTH(created_at)"),date('m'))
            ->where(DB::raw("YEAR(created_at)"),date('Y'))
            ->where('lottery_type_id',3)
            ->where('clock_id',5)
            ->where('number',$win_num)
            ->update(['win'=>1]);

            // bonus win တွတ်ဂဏန်း
            $last_digit = $win_num[2];
            $second_digit = $win_num[1];
            $up_number = $last_digit+1;
            $up_second = $second_digit;
            if($up_number>9){
                $up_number = 0;
                $up_second = $second_digit+1;
            }
            $down_number = $last_digit-1;
            $down_second = $second_digit;
            if($down_number<0){
                $down_number = 9;
                $down_second = $second_digit-1;
            }

            $upper_win = $win_num[0].$up_second.$up_number;
            $down_win = $win_num[0].$down_second.$down_number;

            Voucher::where(DB::raw("DAY(created_at)"),date('d'))
            ->where(DB::raw("MONTH(created_at)"),date('m'))
            ->where(DB::raw("YEAR(created_at)"),date('Y'))
            ->where('lottery_type_id',3)
            ->where('clock_id',5)
            ->where('number',$upper_win)
            ->update(['bonus_win'=>1]);

            Voucher::where(DB::raw("DAY(created_at)"),date('d'))
            ->where(DB::raw("MONTH(created_at)"),date('m'))
            ->where(DB::raw("YEAR(created_at)"),date('Y'))
            ->where('lottery_type_id',3)
            ->where('clock_id',5)
            ->where('number',$down_win)
            ->update(['bonus_win'=>1]);

            Voucher::where(DB::raw("DAY(created_at)"),date('d'))
            ->where(DB::raw("MONTH(created_at)"),date('m'))
            ->where(DB::raw("YEAR(created_at)"),date('Y'))
            ->where('lottery_type_id',3)
            ->where('clock_id',5)
            ->where('number','!=',$win_num)
            ->update(['win'=>0,'verified'=>1]);

            Number::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->update(['demand'=>0,'disable'=>0]);
        }

        return Command::SUCCESS;
    }
}
