<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Models\Voucher;
use App\Models\WalletHistory;
use App\Models\PaymentMethod;


class UserController extends Controller
{
    public function user(Request $req){
        $user = $req->user();
        return response()->json([
            'status'=>"success",
            'user'=>$user,
        ]);
    }
    public function transactions(Request $req){
        $user = $req->user();
        $transactions = Transaction::where('user_id',$user->id)->orderBy('id','desc')->paginate(100);
        return response()->json($transactions);
    }

    public function updateRecoveryHint(Request $req){
        $req->validate([
            'recovery_hint'=>'required',
        ]);
        $user = User::find($req->user()->id);
        $user->recovery_hint = $req->recovery_hint;
        $user->save();
        return response()->json([
            'status'=>"success",
            'user'=>$user,
        ]);
    }

    public function unverified_transactions(Request $req){
        $user = $req->user();
        $transactions = Transaction::where('user_id',$user->id)
        ->where('verified',0)
        ->orderBy('id','desc')
        ->paginate(100);
        return response()->json($transactions);
    }

    public function withdraws(Request $req){
        $user = $req->user();
        $withdraws = Withdraw::where('user_id',$user->id)->orderBy('id','desc')->paginate(100);
        return response()->json($withdraws);
    }

    public function vouchers(Request $req){
        $user = $req->user();
        $req->validate([
            'lottery_type_id'=>'required',
        ]);

        $status = 1;
        if($req->has('status')){
            $status = $req->status;
        }

        if($status == 1){   // all
            $vouchers = Voucher::with('clock:id,hour,minute,morning')
            ->where('user_id',$user->id)
            ->where('lottery_type_id',$req->lottery_type_id)
            ->orderBy('id','desc')->paginate(100);
        }

        if($status == 2){ // soon
            $vouchers = Voucher::with('clock:id,hour,minute,morning')
            ->where('user_id',$user->id)
            ->where('lottery_type_id',$req->lottery_type_id)
            ->where('win',0)
            ->where('verified',0)
            ->orderBy('id','desc')->paginate(100);
        }

        if($status == 3){ // win
            $vouchers = Voucher::with('clock:id,hour,minute,morning')
            ->where('user_id',$user->id)
            ->where('lottery_type_id',$req->lottery_type_id)
            ->where('win',1)
            ->orderBy('id','desc')->paginate(100);
        }

        if($status == 4){ // loose
            $vouchers = Voucher::with('clock:id,hour,minute,morning')
            ->where('user_id',$user->id)
            ->where('lottery_type_id',$req->lottery_type_id)
            ->where('win',0)
            ->where('verified',1)
            ->orderBy('id','desc')->paginate(100);
        }

        
        return response()->json($vouchers);

    }

    public function walletHistories(Request $req){
        $user = $req->user();
        $unverified_transactions = Transaction::where('user_id',$user->id)
        ->where('verified',0)
        ->get();
        $unverified_withdraws = Withdraw::where('user_id',$user->id)
        ->where('verified',0)
        ->get();
        $wallet_histories["unverified_transactions"] =$unverified_transactions;
        $wallet_histories["unverified_withdraws"] =$unverified_withdraws;
        $wallet_histories["wallet_histories"] = WalletHistory::where('user_id',$user->id)->orderBy('id','desc')->paginate(100);
        
        return response()->json($wallet_histories);
    }

    public function update(Request $req){
        $req->validate([
            'name' => 'required',
            'avatar_url' => 'required',
        ]);

        $user = User::find($req->user()->id);
        $user->name = $req->name;
        $user->avatar_url = $req->avatar_url;
        $user->save();

        return response()->json(['status'=>'success'],200);

     }
}
