<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LotteryType;

class LotteryTypeController extends Controller
{
    public function clocks($id){
        $lottery_type = LotteryType::find($id);
        $clocks = DB::table('lottery_clock')
        ->selectRaw("clocks.*,lottery_clock.close_before")
        ->join('clocks','clocks.id','=','lottery_clock.clock_id')
        ->where('lottery_type_id',$id)
        ->get();
        return $clocks;
    }
}
