<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Models\Voucher;
use App\Models\WalletHistory;

class UserController extends Controller
{
    public function index(Request $req){

        if(isset($req->sort)) $sort = $req->sort;
        else $sort = 'created_at';

        $users = User::orderBy($sort,'desc')->paginate(100);
        return view('admin.users',[
            'page_name'=>'Users',
            'users' => $users,
        ]);
    }

    public function transactions($id){
        $user = User::find($id);
        $transactions = Transaction::with('payment_method:id,banking_id,method,account_name')
        ->where('user_id',$user->id)->orderBy('id','desc')->paginate(100);
        return view('admin.user-transactions',[
            'page_name'=> "Users",
            'user' => $user,
            'transactions'=> $transactions,
        ]);
        
    }

    public function withdraws($id){
        $user = User::find($id);
        $withdraws = Withdraw::where('user_id',$user->id)->orderBy('id','desc')->paginate(100);
        return view('admin.user-withdraws',[
            'page_name'=> "Users",
            'user' => $user,
            'withdraws'=> $withdraws,
        ]);
    }

    public function vouchers($id){
        $user = User::find($id);
        $vouchers = Voucher::with('lottery_type:id,type')->where('user_id',$user->id)->orderBy('id','desc')->paginate(100);
        return view('admin.user-vouchers',[
            'page_name'=> "Users",
            'user' => $user,
            'vouchers'=> $vouchers,
        ]);
    }

    public function wallet_histories($id){
        $user = User::find($id);
        $wallet_histories = WalletHistory::where('user_id',$user->id)->orderBy('id','desc')->paginate(100);
        return view('admin.user-wallet-histories',[
            'page_name'=> "Users",
            'user' => $user,
            'wallet_histories'=> $wallet_histories,
        ]);
    }

    public function setting($id){
        $user = User::find($id);
        return view('admin.user-setting',[
            'page_name'=> "Users",
            'user' => $user,
        ]);

    }

    public function disable($id){
        $user = User::find($id);
        $user->disable = 1;
        $user->save();
        return back()->with('msg','The use was successfully disable.');
    }

    public function activate($id){
        $user = User::find($id);
        $user->disable = 0;
        $user->save();
        return back()->with('msg','The use was successfully activated.');
    }

    public function resetPassword(Request $req,$id){
        $req->validate([
            'password' => 'required',
        ]);
        $user = User::find($id);
        $password = Hash::make($req->password);
        $user->password = $password;
        $user->save();
        return back()->with('msg','The password was successfully reset');
    }

    public function search(Request $req){
        $search = $req->search;
        $users = User::where('name','like',"%$search%")
        ->orWhere('email','like',"%$search%")
        ->orWhere('phone',$search)
        ->paginate(100);

        return view('admin.user-search',[
            'page_name'=>'Users',
            'users' => $users,
        ]);
    }
}
