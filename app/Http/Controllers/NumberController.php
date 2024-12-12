<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Number;
use App\Models\LotteryType;
use App\Models\Clock;

class NumberController extends Controller
{
    public function index(Request $req){
        $lottery_type_id = $req->lottery_type_id;
        $clock_id = $req->clock_id;
        $lottery_type = LotteryType::find($lottery_type_id);
        $clock = Clock::find($clock_id);

        $numbers = Number::where('lottery_type_id',$lottery_type_id)
        ->where('clock_id',$clock_id)
        ->get();

        return view('admin.numbers',[
            'numbers' => $numbers,
            'lottery_type'=>$lottery_type,
            'clock'=>$clock,
            'page_name'  => 'Number Setting',
        ]);

    }

    public function report(Request $req){
        $lottery_type_id = $req->lottery_type_id;
        $clock_id = $req->clock_id;
        $lottery_type = LotteryType::find($lottery_type_id);
        $clock = Clock::find($clock_id);

        $numbers = Number::where('lottery_type_id',$lottery_type_id)
        ->where('clock_id',$clock_id)
        ->where('demand','>',0)
        ->get();

        return view('admin.reports',[
            'numbers' => $numbers,
            'lottery_type'=>$lottery_type,
            'clock'=>$clock,
            'page_name'  => 'Reports',
        ]);
    }

    public function edit($id){
        $number = Number::find($id);

        return view('admin.number-edit',[
            'number' => $number,
            'lottery_type'=>$number->lottery_type,
            'clock'=>$number->clock,
            'page_name'  => 'Number Setting',
        ]);

    }

    public function update(Request $req,$id){
        $req->validate([
            'sell'=>'required',
        ]);
        $number = Number::find($id);
        $number->sell = $req->sell;
        $number->save();

        return back()->with('msg','The sell amount was successfully updated.');
    }

    public function disable($id){
        $number = Number::find($id);
        $number->disable = 1;
        $number->save();

        return back()->with('msg','The number was successfully disable.');
    }

    public function activate($id){
        $number = Number::find($id);
        $number->disable = 0;
        $number->save();

        return back()->with('msg','The number was successfully activated.');
    }

    public function disableAll(Request $req){
        $req->validate([
            'lottery_type_id'=>'required',
            'clock_id'=>'required',
            'disable'=>'required',
        ]);

        Number::where('lottery_type_id',$req->lottery_type_id)
        ->where('clock_id',$req->clock_id)
        ->update(['disable'=>$req->disable]);

        if($req->disable == 1){
            return back()->with('msg','All number was successfully disable.');
        }else{
            return back()->with('msg','All number was successfully activated.');
        }
        
    }

    public function resetSellAmount(Request $req){
        $req->validate([
            'lottery_type_id'=>'required',
            'clock_id'=>'required',
            'sell'=>'required',
        ]);

        Number::where('lottery_type_id',$req->lottery_type_id)
        ->where('clock_id',$req->clock_id)
        ->update(['sell'=>$req->sell]);

        return back()->with('msg','The sell amount for all numbers was successfully updated');
    }
}
