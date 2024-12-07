<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\User;
use App\Models\Clock;
use App\Models\WalletHistory;
use App\Models\Number;

class VoucherController extends Controller
{
    public function store(Request $req){
        $user = User::find($req->user()->id);
        $req->validate([
            'numberJSON'=>'required',
            'lottery_type_id'=>'required',
            'clock_id'=>'required',
        ]);

        $numberJSON = $req->numberJSON;
        $lottery_type_id = $req->lottery_type_id;
        $clock_id = $req->clock_id;

        $lottery_numbers = json_decode($numberJSON,true);
        $total_amount = 0;

        foreach($lottery_numbers as $number){
            $total_amount+= $number['amount'];
        }
        

        if($total_amount> $user->balance){
             return response()->json(['status'=>'fail','error'=>'Insufficient Balance']);
        }

        $Clock = Clock::find($clock_id);

        $currentYear = date('Y');
        $currentMonth = date('m');
        $currentDay = date('d');
        $currentHour = date('H');
        $currentMinute = date('i');


        $hour = $Clock->hour;
        if($hour<12 && $Clock->morning == 0){
            $hour = $hour+12;
        }
        if($lottery_type_id == 3){
            $hour = 13;
            if($currentDay > 16){
                $currentDay = 1;
                $currentMonth++;
                if($currentMonth>12){
                    $currentYear++;
                    $currentMonth  = 1;
                }
            }else{
                $currentDay = 16;
            }
        } 
        $minute = $Clock->minute;

        $selectedTimestamp = mktime($hour,$minute,0,$currentMonth,$currentDay,$currentYear);
        $currentTimestamp = time();

        //if($currentTimestamp < $selectedTimestamp - 60*30){
        $error_numbers = [];
        if(true){
            foreach($lottery_numbers as $number){
                $lottery_num_id = $number['id'];
                $lotteryNum = $number['number'];
                $amount = $number['amount'];

                $Number = Number::find($lottery_num_id);
                $allowed_amount = $Number->sell - $Number->demand;
                if($allowed_amount > $amount && $Number->disable == 0){
                    $voucher = new Voucher();
                    $voucher->user_id = $user->id;
                    $voucher->lottery_type_id = $lottery_type_id;
                    $voucher->clock_id = $clock_id;
                    $voucher->number = $lotteryNum;
                    $voucher->amount = $amount;
                    $voucher->win = 0;
                    $voucher->verified = 0;
                    $voucher->verified_by = 0;
                    if($lottery_type_id == 3) $voucher->created_at = "$currentYear-$currentMonth-$currentDay";
                    $voucher->save();

                    $user->balance = $user->balance - $amount;
                    $user->save();

                    $wallet = new WalletHistory();
                    $wallet->user_id = $user->id;
                    $wallet->title = "ထီထိုး";
                    $wallet->amount = $amount;
                    $wallet->income = 0;
                    $wallet->save();

                    $Number->demand = $Number->demand + $amount;
                    $Number->save();

                }else{
                    $error_numbers [] =  $number;
                }
            }

            $res['status'] = 'success';
            if(count( $error_numbers) > 0) $res['error_numbers'] = $error_numbers;

            return response()->json($res);
        }else{
            return response()->json(['status'=>'fail','error'=>'Lottery closed']);
        }
        
    }
}
