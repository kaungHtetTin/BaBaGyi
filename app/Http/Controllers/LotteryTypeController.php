<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LotteryType;

class LotteryTypeController extends Controller
{
    public function index(){
        $lottery_types = LotteryType::all();
        return view('admin.lottery-types',[
            'page_name' => 'Setting',
            'lottery_types' => $lottery_types,
        ]);
    }
}
