<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Number;
use App\Models\HotNumber;

class NumberController extends Controller
{
    public function index(Request $req){

        $req->validate([
            'clock_id' => 'required',
            'lottery_type_id' => 'required',
        ]);

        $numbers = Number::where('lottery_type_id',$req->lottery_type_id)
        ->where('clock_id',$req->clock_id)
        ->get();

        $hot_numbers = HotNumber::where('lottery_type_id',$req->lottery_type_id)
        ->where('clock_id',$req->clock_id)
        ->get();

        return response()->json(['numbers'=>$numbers, 'hot_numbers'=>$hot_numbers]);
    }
}
