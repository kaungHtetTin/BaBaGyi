<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\User;
use App\Models\Clock;
use App\Models\WalletHistory;
use App\Models\Number;
use App\Models\LotteryType;
use App\Models\LotteryClock;

class VoucherController extends Controller
{
    public function store(Request $req){
        return \Illuminate\Support\Facades\DB::transaction(function () use ($req) {
            $user = User::whereKey($req->user()->id)->lockForUpdate()->firstOrFail();
            $req->validate([
                'numberJSON'=>'required|json',
                'lottery_type_id'=>'required',
                'clock_id'=>'required',
            ]);


            $numberJSON = $req->numberJSON;
            $lottery_type_id = $req->lottery_type_id;
            $clock_id = $req->clock_id;

            $lottery_clock = LotteryClock::where('lottery_type_id',$lottery_type_id)->where('clock_id',$clock_id)->first();

            $lottery_numbers = json_decode($numberJSON,true);
            validator(['numbers' => $lottery_numbers], [
                'numbers' => 'required|array|min:1|max:1000',
                'numbers.*.id' => 'required|integer',
                'numbers.*.number' => 'required|regex:/^[0-9]{2,3}$/',
                'numbers.*.amount' => 'required|integer|min:1',
            ])->validate();
            // Lock in a consistent order for concurrent native and web clients.
            Number::whereIn('id', array_column($lottery_numbers, 'id'))->orderBy('id')->lockForUpdate()->get();
            $total_amount = 0;

            foreach($lottery_numbers as $number){
                $total_amount+= $number['amount'];
            }


            if($total_amount> $user->balance){
                 return response()->json(['status'=>'fail','error'=>'Insufficient Balance']);
            }

            $Clock = Clock::find($clock_id);
            $lottery_type = LotteryType::find($lottery_type_id);

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

            $error_numbers = [];
           // if($currentTimestamp < $selectedTimestamp - 60*$lottery_clock->close_before){

            if(true){
                foreach($lottery_numbers as $number){
                    $lottery_num_id = $number['id'];
                    $lotteryNum = $number['number'];
                    $amount = $number['amount'];

                    $Number = Number::whereKey($lottery_num_id)->lockForUpdate()->firstOrFail();
                    abort_unless((int) $Number->lottery_type_id === (int) $lottery_type_id
                        && (int) $Number->clock_id === (int) $clock_id
                        && (string) $Number->number === (string) $lotteryNum, 422, 'Invalid number selection.');
                    $allowed_amount = $Number->sell - $Number->demand;
                    if($allowed_amount >= $amount && $Number->disable == 0){
                        $voucher = new Voucher();
                        $voucher->user_id = $user->id;
                        $voucher->lottery_type_id = $lottery_type_id;
                        $voucher->clock_id = $clock_id;
                        $voucher->number = $lotteryNum;
                        $voucher->amount = $amount;
                        $voucher->win = 0;
                        $voucher->bonus_win = 0;
                        $voucher->win_amount = 0;
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
                        $Number->report = $Number->report + $amount;
                        $Number->save();

                    }else{
                        $error_numbers [] =  [
                            'id'=>$Number->id,
                            'number'=>$Number->number,
                            'amount'=>$amount,
                            'allowed_amount'=>$allowed_amount,
                        ];
                    }
                }

                $res['status'] = 'success';
                if(count( $error_numbers) > 0) $res['error_numbers'] = $error_numbers;

                return response()->json($res);
            }else{
                return response()->json(['status'=>'fail','error'=>'Lottery closed']);
            }
        });
    }
}
