<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Models\Voucher;
class LayoutController extends Controller
{
    public function index(Request $req){

        if(isset($req->year)) $year = $req->year;
        else $year = date('Y');

        $total_user = User::count();
        $total_balance = User::sum('balance');
        $total_transaction = Transaction::sum('amount');
        $total_withdraw = Withdraw::sum('amount');
        $transaction_req = Transaction::where('verified',0)->count();
        $withdraw_req = Withdraw::where('verified',0)->count();

        $transactionOfYear = DB::table('transactions')
        ->selectRaw(DB::raw("sum(amount) as amount, MONTH(created_at) as month"))
        ->where(DB::raw("YEAR(created_at)"),$year)
        ->groupBy("month")
        ->get();

        $withdrawOfYear = DB::table('withdraws')
        ->selectRaw(DB::raw("sum(amount) as amount, MONTH(created_at) as month"))
        ->where(DB::raw("YEAR(created_at)"),$year)
        ->groupBy("month")
        ->get();

        $balanceOfYear = DB::table('users')
        ->selectRaw(DB::raw("sum(balance) as amount, MONTH(created_at) as month"))
        ->where(DB::raw("YEAR(created_at)"),$year)
        ->groupBy("month")
        ->get();

        $unexpected_2d_1201_numbers = DB::table('vouchers')
        ->selectRaw(DB::raw("sum(amount) as amount, number"))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),date('d'))
        ->where('lottery_type_id',2)
        ->where('clock_id',2)
        ->groupBy("number")
        ->orderBy('amount','desc')
        ->limit(3)
        ->get();

        $unexpected_2d_1630_numbers = DB::table('vouchers')
        ->selectRaw(DB::raw("sum(amount) as amount, number"))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),date('d'))
        ->where('lottery_type_id',2)
        ->where('clock_id',4)
        ->groupBy("number")
        ->orderBy('amount','desc')
        ->limit(3)
        ->get();

        $unexpected_3d_1_numbers = DB::table('vouchers')
        ->selectRaw(DB::raw("sum(amount) as amount, number"))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),1)
        ->where('lottery_type_id',3)
        ->groupBy("number")
        ->orderBy('amount','desc')
        ->limit(3)
        ->get();

        $unexpected_3d_16_numbers = DB::table('vouchers')
        ->selectRaw(DB::raw("sum(amount) as amount, number"))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),16)
        ->where('lottery_type_id',3)
        ->groupBy("number")
        ->orderBy('amount','desc')
        ->limit(3)
        ->get();

        $earning_2d_1201 = Voucher::where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),date('d'))
        ->where('lottery_type_id',2)
        ->where('clock_id',2)
        ->sum('amount');

      

        $earning_2d_1630 = Voucher::where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),date('d'))
        ->where('lottery_type_id',2)
        ->where('clock_id',4)
        ->sum('amount');

        $earning_3d_1 = Voucher::where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),1)
        ->where('lottery_type_id',3)
        ->sum('amount');

        $earning_3d_16 = Voucher::where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),16)
        ->where('lottery_type_id',3)
        ->sum('amount');

     
        return view('admin.index',[
            'page_name'=>'Dashboard',
            'total_user'=> $total_user,
            'total_balance'=>$total_balance,
            'total_transaction'=>$total_transaction,
            'total_withdraw'=>$total_withdraw,
            'transaction_req'=>$transaction_req,
            'withdraw_req'=>$withdraw_req,
            'transactionOfYear'=>$transactionOfYear,
            'withdrawOfYear'=>$withdrawOfYear,
            'balanceOfYear'=>$balanceOfYear,
            'unexpected_2d_1201_numbers'=>$unexpected_2d_1201_numbers,
            'unexpected_2d_1630_numbers'=>$unexpected_2d_1630_numbers,
            'unexpected_3d_1_numbers'=>$unexpected_3d_1_numbers,
            'unexpected_3d_16_numbers'=>$unexpected_3d_16_numbers,
            'earning_2d_1201'=>$earning_2d_1201,
            'earning_2d_1630'=>$earning_2d_1630,
            'earning_3d_1'=>$earning_3d_1,
            'earning_3d_16'=>$earning_3d_16

        ]);
    }
}
