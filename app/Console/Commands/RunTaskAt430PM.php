<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Lottery;
use App\Models\Voucher;
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
        
        // URL you want to access
        $url = "https://api.thaistock2d.com/live";

        // Set custom headers
        $headers = [
            "Content-Type: application/json",
        ];

        // Create a stream context with the headers
        $options = [
            "http" => [
                "method"  => "GET",
                "header"  => implode("\r\n", $headers)
            ]
        ];
        $context = stream_context_create($options);

        // Fetch the content from the URL with custom headers
        $response = file_get_contents($url, false, $context);

        $result = json_decode($response,true);
        
        $win_num = $result['live']['twod'];
    
        $lottery = new Lottery();
        $lottery->lottery_type_id = 2;
        $lottery->clock_id = 4;        
        $lottery->number = $win_num;         
        $lottery->save();

        Voucher::where(DB::raw("DAY(created_at)"),date('d'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',2)
        ->where('clock_id',4)
        ->where('number',$win_num)
        ->update(['win'=>1]);

        Voucher::where(DB::raw("DAY(created_at)"),date('d'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',2)
        ->where('clock_id',4)
        ->where('number','!=',$win_num)
        ->update(['win'=>0,'verified'=>1]);

        return;
    }
}
