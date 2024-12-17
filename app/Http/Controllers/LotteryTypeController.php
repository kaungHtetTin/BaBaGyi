<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LotteryType;
use App\Models\LotteryClock;
use App\Models\Clock;

class LotteryTypeController extends Controller
{
    public function index(){

        $lotteries = LotteryClock::where('lottery_type_id','>',1)->get();

        return view('admin.lottery-types',[
            'page_name' => 'Lottery Setting',
            'lotteries' => $lotteries,
        ]);
    }

    public function edit($id){
        $lottery_clock = LotteryClock::find($id);
        $lottery_type = LotteryType::find($lottery_clock->lottery_type_id);
        $clock = Clock::find($lottery_clock->clock_id);

        return view('admin.lottery-types-edit',[
            'page_name'=>'Lottery Setting',
            'lottery_type' => $lottery_type,
            'clock'=>$clock,
            'lottery_clock'=>$lottery_clock,
        ]);
    }

    public function update(Request $req, $id){
        $lottery_clock = LotteryClock::find($id);
        $lottery_type = LotteryType::find($lottery_clock->lottery_type_id);
     
        $req->validate([
            'multiplication'=>'required',
            'close_before'=>'required',
        ]);

        $lottery_type->coefficient = $req->multiplication;
        $lottery_type->save();

        $lottery_clock->close_before = $req->close_before;
        $lottery_clock->save();

        return back()->with('msg','The lottery was successfully updated');
    }

    public function changeStatus(Request $req, $id){
        $lottery_type = LotteryType::find($id);
        $req->validate([
            'open'=>'required',
        ]);

        $lottery_type->open = $req->open;
        $lottery_type->save();

        return back()->with('msg','The lottery was successfully updated');

    }
}
