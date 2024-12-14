<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Withdraw;
use App\Models\WalletHistory;

class WithdrawController extends Controller
{
    public function index(){
        $withdraws = Withdraw::with('user:id,name,balance')
        ->with('banking:id,bank,icon_url')
        ->orderBy('verified','asc')
        ->paginate(100);

        $amount_today = Withdraw::where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),date('d'))
        ->sum('amount');

        return view('admin.withdraws',[
            'page_name'=>'Financial',
            'withdraws'=>$withdraws,
            'amount_today'=>$amount_today,
        ]);
         
    }

    public function approve($id){
        $withdraw = Withdraw::find($id);

        $withdraw->verified_by = Auth::user()->id;
        $withdraw->verified = 1;
        $withdraw->save();

        $wallet = new WalletHistory();
        $wallet->user_id = $withdraw->user_id;
        $wallet->title = "ငွေထုတ်";
        $wallet->amount = $withdraw->amount;
        $wallet->income = 0;
        $wallet->save();

        return back()->with('msg','The withdraw was successfully approved');
    }

}
