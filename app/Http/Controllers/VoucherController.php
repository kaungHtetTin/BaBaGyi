<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Voucher;
use App\Models\LotteryHistory;
use App\Models\LotteryType;
use App\Models\WalletHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class VoucherController extends Controller
{
    public function index(){

    }

    public function voucher_btc(){

        $vouchers = Voucher::with('user:id,name,email,phone,balance')
        ->with('clock:id,hour,minute,morning')
        ->where('user_id','!=',2)
        ->where('lottery_type_id',1)
        ->orderBy('created_at','desc')
        ->orderBy('verified','asc')
        ->paginate(100);

        return view('admin.vouchers',[
            'page_name'=>'Vouchers',
            'title'=>'BTC 2D Vouchers',
            'vouchers'=>$vouchers,
        ]);
    }

    public function voucher_2d(){
        $lottery_type_id = 2;
        $vouchers = Voucher::with('user:id,name,email,phone,balance')
         ->where('user_id','!=',2)
        ->where('lottery_type_id',$lottery_type_id)
        ->orderBy('verified','asc')
        ->orderBy('created_at','desc')
        ->paginate(100);

        $earning_today = Voucher::where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),date('d'))
        ->where('lottery_type_id',$lottery_type_id)
        ->sum('amount');

        $give_Back_today = Voucher::where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("DAY(created_at)"),date('d'))
        ->where('lottery_type_id',$lottery_type_id)
        ->where('win',1)
        ->sum('amount');

        $give_Back_today = $give_Back_today * LotteryType::find($lottery_type_id)->coefficient;

        return view('admin.vouchers',[
            'page_name'=>'Vouchers',
            'title'=>'Thai 2D Vouchers',
            'vouchers'=>$vouchers,
            'earning_today'=>$earning_today,
            'give_Back_today'=>$give_Back_today,
        ]);
    }

    public function voucher_3d(){
         $lottery_type_id = 3;

        $vouchers = Voucher::with('user:id,name,email,phone,balance')
        ->where('user_id','!=',2)
        ->where('lottery_type_id',3)
        ->orderBy('verified','asc')
        ->paginate(100);

        $day = date('d');
        $year = date('Y');
        $month = date('m');
        if($day<=16){
            $day = 16;
        }else{
            $day = 1;
            $month++;
            if($month>12){
                $month = 1;
                $year++;
            }
        }

        $earning_today = Voucher::where(DB::raw("YEAR(created_at)"),$year)
        ->where(DB::raw("MONTH(created_at)"),$month)
        ->where(DB::raw("DAY(created_at)"),$day)
        ->where('lottery_type_id',$lottery_type_id)
        ->sum('amount');

        $give_Back_today = Voucher::where(DB::raw("YEAR(created_at)"),$year)
        ->where(DB::raw("MONTH(created_at)"),$month)
        ->where(DB::raw("DAY(created_at)"),$day)
        ->where('lottery_type_id',$lottery_type_id)
        ->where('win',1)
        ->sum('amount');

        $give_Back_today = $give_Back_today * LotteryType::find($lottery_type_id)->coefficient;

        return view('admin.vouchers',[
            'page_name'=>'Vouchers',
            'title'=>'Thai 3D Vouchers',
            'vouchers'=>$vouchers,
            'earning_today'=>$earning_today,
            'give_Back_today'=>$give_Back_today,
        ]);
    }

    public function approve($id){
        $voucher = Voucher::find($id);
        $user = $voucher->user;
        $lottery_type = $voucher->lottery_type;

        $coefficient = $lottery_type->coefficient;

        
        $user->balance = $user->balance + $voucher->amount * $coefficient;
        $user->save();

        $wallet_history = new WalletHistory();
        $wallet_history->user_id = $user->id;
        $wallet_history->title = "ထီပေါက်";
        $wallet_history->amount = $voucher->amount * $coefficient;
        $wallet_history->income = 1;
        $wallet_history->save();

        $voucher->verified = 1;
        $voucher->verified_by = Auth::user()->id;
        $voucher->save();
    
        return back()->with('msg','The voucher was successfully approved');
    }

    public function delete($id){
        $voucher = Voucher::find($id);
        $amount = $voucher->amount;
        $user = User::find($voucher->user_id);

        $user->balance = $user->balance + $amount;
        $user->save();

        $wallet_history = new WalletHistory();
        $wallet_history->user_id = $user->id;
        $wallet_history->title = "ထီအားလပ်ရက်အတွက် ငွေပြန်အမ်းခြင်း";
        $wallet_history->amount = $amount;
        $wallet_history->income = 1;
        $wallet_history->save();

        $voucher->delete();

        return back()->with('msg','The voucher was successfully restored');
    }

}
