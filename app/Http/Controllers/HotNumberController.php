<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HotNumber;

class HotNumberController extends Controller
{
    public function store(Request $req){
        $req->validate([
            'lottery_type_id'=>'required',
            'clock_id'=>'required',
            'numbers'=>'required',
        ]);

        $numberStr = $req->numbers;
        $numbers = explode(",",$numberStr);

        foreach($numbers as $number){
            if(strlen($number)==1){
                $hot_number = new HotNumber();
                $hot_number->lottery_type_id = $req->lottery_type_id;
                $hot_number->clock_id = $req->clock_id;
                $hot_number->number = $number;
                $hot_number->save();
            }
        }

        return back()->with('msg','Hot numbers were successfully added');

    }

    public function destroyAll(Request $req){
        $req->validate([
            'lottery_type_id'=>'required',
            'clock_id'=>'required',
        ]);

      
        HotNumber::where('lottery_type_id',$req->lottery_type_id)->where('clock_id',$req->clock_id)->delete();
        return back()->with('msg','Hot numbers were successfully deleted');
    }
}
