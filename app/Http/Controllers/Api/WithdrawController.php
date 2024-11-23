<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banking;
use App\Models\Withdraw;
use App\Models\User;

class WithdrawController extends Controller
{
    public function store(Request $req){
        $user = User::find($req->user()->id);
        $req->validate([
            'banking_id'=>'required',
            'method'=>'required',
            'account_name'=>'required',
            'amount'=>'required',
        ]);

        $banking = Banking::find($req->banking_id);
        if(!$banking){
            $res = [
                'status'=>'fail',
                'msg'=>'Unsupported banking',      
            ];
            return response()->json($res);
        }
    
      
        if($user->balance < $req->amount){
            $res = [
                'status'=>'fail',
                'msg'=>'လက်ကျန်ငွေမလုံလောက်ပါ',      
            ];
            return response()->json($res);
        }

        $withdraw = new Withdraw();
        $withdraw->user_id = $user->id;
        $withdraw->banking_id = $req->banking_id;
        $withdraw->method = $req->method;
        $withdraw->account_name = $req->account_name;
        $withdraw->amount = $req->amount;
        $withdraw->save();

        $user->balance = $user->balance - $req->amount;
        $user->save();

        return response()->json(['status'=>'success']);
    }
}
