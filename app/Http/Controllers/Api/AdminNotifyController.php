<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Withdraw;

class AdminNotifyController extends Controller
{
    public function notify(){
        $transaction_req = Transaction::where('verified',0)->count();
        $withdraw_req = Withdraw::where('verified',0)->count();

        return response()->json([
            'transaction_req'=>$transaction_req,
            'withdraw_req'=>$withdraw_req,
        ]);
    }
}
