<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    public function payment_method(Request $req){
        $req->validate([
            'banking_id' => 'required',
            'amount' => 'required',
        ]);
        $payment_method = PaymentMethod::with('banking:id,bank,icon_url')
        ->where('banking_id',$req->banking_id)
        ->orderBy(DB::raw("rand()"))
        ->limit(1)->get();

        $payment_method = $payment_method[0];
        $amount = $req->amount;
        $amount = $amount + rand(0,300);

        $user = $req->user();
 
        $transaction = new Transaction();
        $transaction->payment_method_id = $payment_method->id;
        $transaction->user_id = $user->id;
        $transaction->amount = $amount;
        $transaction->save();

        $payment_method->new_payment_count = $payment_method->new_payment_count + 1;
        $payment_method->save();

        return response()->json([
            'payment_method'=>$payment_method,
            'amount'=>$amount,
        ],200);
    }

}
