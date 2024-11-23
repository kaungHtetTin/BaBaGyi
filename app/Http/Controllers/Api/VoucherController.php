<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\User;
use App\Models\WalletHistory;

class VoucherController extends Controller
{
    public function store(Request $req){
        $user = User::find($req->user()->id);
        $req->validate([
            'number'=>'required',
            'amount'=>'required',
            'lottery_type_id'=>'required',
            'year'=>'required',
            'month'=>'required',
            'day'=>'required',
            'hour'=>'required',
            'minute'=>'required',
            'clock_id'=>'required',

        ]);

        $number = $req->number;
        $amount = $req->amount;
        $lottery_type_id = $req->lottery_type_id;
        $year = $req->year;
        $month = $req->month;
        $day = $req->day;
        $hour = $req->hour;
        $minute = $req->minute;
        $clock_id = $req->clock_id;

        $currentYear = date('Y');
        $currentMonth = date('m');
        $currentDay = date('d');
        $currentHour = date('H');
        $currentMinute = date('i');

        if($year<$currentYear){
            return response()->json(['status'=>'fail','error'=>'Cannot select the passed date']);
        }else{
            if($year == $currentYear){
                if($month<$currentMonth){
                    return response()->json(['status'=>'fail','error'=>'Cannot select the passed date']);
                }else{
                    if($month == $currentMonth){
                        if($day<$currentDay){
                            return response()->json(['status'=>'fail','error'=>'Cannot select the passed date']);
                        }
                    }
                }
            }
        }

        if($amount> $user->balance){
             return response()->json(['status'=>'fail','error'=>'Insufficient Balance']);
        }

        $currentTimestamp = time();
        $selectedTimestamp = mktime($hour,$minute,0,$month,$day,$year);

        if($currentTimestamp < $selectedTimestamp - 60*30){

            $voucher = new Voucher();
            $voucher->user_id = $user->id;
            $voucher->lottery_type_id = $lottery_type_id;
            $voucher->clock_id = $clock_id;
            $voucher->number = $number;
            $voucher->amount = $amount;
            $voucher->win = 0;
            $voucher->verified = 0;
            $voucher->verified_by = 0;
            $voucher->created_at = "$year-$month-$day";
            $voucher->save();

            $user->balance = $user->balance - $amount;
            $user->save();

            $wallet = new WalletHistory();
            $wallet->user_id = $user->id;
            $wallet->title = "Buy Lottery";
            $wallet->amount = $amount;
            $wallet->income = 0;
            $wallet->save();

            return response()->json([
                'status' => 'success',
                'voucher' => $voucher,
            ]);
        }else{
            return response()->json(['status'=>'fail','error'=>'Lottery closed']);
        }

        
    }
}
