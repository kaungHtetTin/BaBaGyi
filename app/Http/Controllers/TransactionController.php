<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\WalletHistory;

class TransactionController extends Controller
{

    public function index(){

        $transactions = Transaction::with('user:id,name,phone')
        ->with('payment_method:id,banking_id,method,account_name')
        ->orderBy('verified','asc')
        ->paginate(100);

        return view('admin.transactions',[
            'page_name' => 'Financial',
            'transactions'=>$transactions,
        ]);
    }

    public function approve($id){
        $transaction = Transaction::find($id);
        $user = $transaction->user;
        $user->balance = $user->balance + $transaction->amount;
        $user->save();

        $transaction->verified_by = Auth::user()->id;
        $transaction->verified = 1;
        $transaction->save();

        $wallet = new WalletHistory();
        $wallet->user_id = $user->id;
        $wallet->title = "ငွေသွင်း";
        $wallet->amount = $transaction->amount;
        $wallet->income = 1;
        $wallet->save();

        return back()->with('msg','The transaction was successfully approved');
    }

    public function destroy($id){
        Transaction::find($id)->delete();
        return back()->with('msg','The transaction was successfully deleted');
    }
}
