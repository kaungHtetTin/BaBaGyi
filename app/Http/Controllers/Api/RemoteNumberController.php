<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lottery;
use Illuminate\Support\Facades\DB;

class RemoteNumberController extends Controller
{
    public function get2DNumber(){
        /*
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

        */

        $demo = '{"server_time":"2024-11-21 16:05:13","live":{"set":"1,441.11","value":"38,177.16","time":"2024-11-21 16:05:12","twod":"17","date":"2024-11-21"},"result":[{"set":"1,444.12","value":"19,306.60","open_time":"11:00:00","twod":"26","stock_date":"2024-11-21","stock_datetime":"2024-11-21 11:00:01","history_id":"1746953"},{"set":"1,443.79","value":"24,221.65","open_time":"12:01:00","twod":"91","stock_date":"2024-11-21","stock_datetime":"2024-11-21 12:01:00","history_id":"1747369"},{"set":"1,439.86","value":"31,494.42","open_time":"15:00:00","twod":"64","stock_date":"2024-11-21","stock_datetime":"2024-11-21 15:00:02","history_id":"1748268"},{"set":"--","value":"--","open_time":"16:30:00","twod":"--","stock_date":"2024-11-21","stock_datetime":"2024-11-21 16:05:13","history_id":null}],"holiday":{"status":"2","date":"2024-11-21","name":"NULL"}}';
        $demo = json_decode($demo,true);
        return $demo;
    }

    public function get3DNumber(){

        $day = date('d');

        if($day == 1 || $day == 16){
            
            $response = file_get_contents("https://www.calamuseducation.com/api-3d.php");
            $result = json_decode($response,true);
            $win_num = $result['number'];

            $response['number'] = $win_num;
            $response['status'] = "open";

        }else{
            $response['number'] = "Soon";
            $response['status'] = "closed";
        }

        $lottery = Lottery::where(DB::raw("DAY(created_at)"),1)
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',3)
        ->get();

        if(count($lottery)>0){
            $lottery = $lottery[0];
            $response['history']['first'] = $lottery;
        }
    
        $lottery = Lottery::where(DB::raw("DAY(created_at)"),16)
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',3)
        ->get();

        if(count($lottery)>0){
            $lottery = $lottery[0];
            $response['history']['second'] = $lottery;
        }

        return $response;
    }
}
