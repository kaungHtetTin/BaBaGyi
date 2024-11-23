<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Withdraw;
use App\Models\WalletHistory;

class WithdrawController extends Controller
{
    public function index(){
        $withdraws = Withdraw::with('user:id,name,balance')
        ->with('banking:id,bank,icon_url')
        ->orderBy('verified','asc')
        ->paginate(100);

        return view('admin.withdraws',[
            'page_name'=>'Financial',
            'withdraws'=>$withdraws,
        ]);
         
    }

    public function approve($id){
        $withdraw = Withdraw::find($id);

        $withdraw->verified_by = Auth::user()->id;
        $withdraw->verified = 1;
        $withdraw->save();

        $wallet = new WalletHistory();
        $wallet->user_id = $withdraw->user_id;
        $wallet->title = "Withdraw";
        $wallet->amount = $withdraw->amount;
        $wallet->income = 0;
        $wallet->save();

        return back()->with('msg','The withdraw was successfully approved');
    }

}
