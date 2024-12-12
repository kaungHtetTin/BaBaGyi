<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LotteryType;

class LotteryTypeController extends Controller
{
    public function index(){
        $lottery_types = LotteryType::where('id','>',1)->get();
        return view('admin.lottery-types',[
            'page_name' => 'Lottery Setting',
            'lottery_types' => $lottery_types,
        ]);
    }

    public function edit($id){
        $lottery_type = LotteryType::find($id);

        return view('admin.lottery-types-edit',[
            'page_name'=>'Lottery Setting',
            'lottery_type' => $lottery_type,
        ]);
    }

    public function update(Request $req, $id){
        $lottery_type = LotteryType::find($id);
        $req->validate([
            'multiplication'=>'required',
        ]);

        $lottery_type->coefficient = $req->multiplication;
        $lottery_type->save();

        return back()->with('msg','The multification was successfully updated');
    }
}
