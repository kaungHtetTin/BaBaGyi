<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Lottery;
use App\Models\Voucher;
use App\Models\Number;
use App\Models\HotNumber;
use Illuminate\Support\Facades\DB;

class RunTaskAt430PM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:runat430pm';

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
        $clock_id = 4;
     
        $url = "https://api.thaistock2d.com/live";

        $headers = [
            "Content-Type: application/json",
        ];

        $options = [
            "http" => [
                "method"  => "GET",
                "header"  => implode("\r\n", $headers)
            ]
        ];
        $context = stream_context_create($options);

        $response = file_get_contents($url, false, $context);

        $result = json_decode($response,true);
        
        $win_num = $result['live']['twod'];

    
        $lottery = new Lottery();
        $lottery->lottery_type_id = $lottery_type_id ;
        $lottery->clock_id = $clock_id;        
        $lottery->number = $win_num;         
        $lottery->save();

        Voucher::where(DB::raw("DAY(created_at)"),date('d'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',$lottery_type_id )
        ->where('clock_id',$clock_id)
        ->where('number',$win_num)
        ->update(['win'=>1]);

        Voucher::where(DB::raw("DAY(created_at)"),date('d'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',$lottery_type_id )
        ->where('clock_id',$clock_id)
        ->where('number','!=',$win_num)
        ->update(['win'=>0,'verified'=>1]);

        Number::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->update(['demand'=>0,'disable'=>0]);
        HotNumber::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->delete();
        return;
    }
}
