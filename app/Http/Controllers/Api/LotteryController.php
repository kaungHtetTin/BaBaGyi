<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Lottery;

class LotteryController extends Controller
{
    public function records(Request $req){
        if(isset($req->year)) $year = $req->year;
        else $year = date('Y');

        if(isset($req->month)) $month = $req->month;
        else $month = date('m');

        
        if(isset($req->type)) $type = $req->type;
        else $type = 2;

        $records = Lottery::selectRaw("*, DAY(created_at) as day")
        ->where(DB::raw("YEAR(created_at)"),$year)
        ->where(DB::raw("MONTH(created_at)"),$month)
        ->where('lottery_type_id',$type)
        ->get();

        $result = [];
        foreach($records as $record){
            $day = $record->day;
            $timestamp = mktime(0,0,0,$month,$day,$year);
            $d = date('D',$timestamp);
            $record->d = $d;
            if($type == 2){
                if($d !== "Sat" && $d !== "Sun"){
                    $result [] = $record;
                }
            }else{
              $result [] = $record;
            }
        }

        return $result;

    }

    public function _3d_records(Request $req){
        if(isset($req->year)) $year = $req->year;
        else $year = date('Y');
 
        $records = Lottery::selectRaw("*, Month(created_at) as month")
        ->where(DB::raw("YEAR(created_at)"),$year)
        ->where('lottery_type_id',3)
        ->orderBy('created_at','asc')
        ->get();

        return $records;

    }
}
