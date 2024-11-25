<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    
    public function store(Request $req){
        $req->validate([
            'payment_method_id' => 'required',
            'amount' => 'required',
            'bank_transaction_id'=>'required',
        ]);

        $user = $req->user();
 
        $transaction = new Transaction();
        $transaction->payment_method_id = $req->payment_method_id;
        $transaction->user_id = $user->id;
        $transaction->amount = $req->amount;
        $transaction->bank_transaction_id = $req->bank_transaction_id;
        $transaction->save();

        $payment_method = PaymentMethod::find($req->payment_method_id);
        $payment_method->new_payment_count = $payment_method->new_payment_count + 1;
        $payment_method->save();

        return response()->json([
            'payment_method'=>$payment_method,
            'amount'=>$req->amount,
        ],200);
        
    }
}
