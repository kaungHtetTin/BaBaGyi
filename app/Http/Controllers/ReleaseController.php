<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lottery;
use App\Models\LotteryType;
use App\Models\LotteryClock;
use App\Models\Voucher;
use App\Models\Number;
use App\Models\HotNumber;
use Illuminate\Support\Facades\DB;

class ReleaseController extends Controller
{
    public function manualRelease($type){
        if($type == "2d"){
            $lottery_clocks = LotteryClock::where('lottery_type_id',2)->get();
            return view('admin.release-2d',[
                'page_name' => 'Release 2D',
                'lottery_clocks'=>$lottery_clocks,
            ]);
        }else{
            return view('admin.release-3d',[
                'page_name' => 'Release 3D',
            ]);
        }
    }
    
    public function release2D(Request $req){
        $req->validate([
            'lottery_type_id' => 'required',
            'clock_id' => 'required',
            'lottery_number' => 'required|numeric',
        ]);

        $lottery_type_id = $req->lottery_type_id;
        $clock_id = $req->clock_id;
        $win_num =  $req->lottery_number;


        $lottery = new Lottery();
        $lottery->lottery_type_id = $lottery_type_id ;
        $lottery->clock_id = $clock_id;        
        $lottery->number = $win_num;         
        $lottery->save();

        Voucher::where(DB::raw("DAY(created_at)"),date('d'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',$lottery_type_id )
        ->where('clock_id',$clock_id)
        ->where('number',$win_num)
        ->update(['win'=>1]);

        Voucher::where(DB::raw("DAY(created_at)"),date('d'))
        ->where(DB::raw("MONTH(created_at)"),date('m'))
        ->where(DB::raw("YEAR(created_at)"),date('Y'))
        ->where('lottery_type_id',$lottery_type_id )
        ->where('clock_id',$clock_id)
        ->where('number','!=',$win_num)
        ->update(['win'=>0,'verified'=>1]);

        Number::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->update(['demand'=>0,'disable'=>0]);
        HotNumber::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->delete();

        return redirect()->route('admin.index');
    }

    public function release3D(Request $req){
        $req->validate([
            'lottery_number' => 'required|numeric'
        ]);

        $lottery_type_id = 3;
        $clock_id = 5;
        $win_num = $req->lottery_number;

        $lottery = new Lottery();
        $lottery->lottery_type_id = 3;
        $lottery->clock_id = 5;        
        $lottery->number = $win_num;         
        $lottery->save();

        // lottery win
        Voucher::where('lottery_type_id',3)
        ->where('clock_id',5)
        ->where('number',$win_num)
        ->where('verified',0)
        ->update(['win'=>1]);

        // bonus win တွတ်ဂဏန်း
        $last_digit = $win_num[2];
        $second_digit = $win_num[1];
        $up_number = $last_digit+1;
        $up_second = $second_digit;
        if($up_number>9){
            $up_number = 0;
            $up_second = $second_digit+1;
        }
        $down_number = $last_digit-1;
        $down_second = $second_digit;
        if($down_number<0){
            $down_number = 9;
            $down_second = $second_digit-1;
        }

        $upper_win = $win_num[0].$up_second.$up_number;
        $down_win = $win_num[0].$down_second.$down_number;

        Voucher::where('lottery_type_id',3)
        ->where('clock_id',5)
        ->where('number',$upper_win)
        ->where('verified',0)
        ->update(['bonus_win'=>1]);

        Voucher::where('lottery_type_id',3)
        ->where('clock_id',5)
        ->where('number',$down_win)
        ->where('verified',0)
        ->update(['bonus_win'=>1]);

        // fail

        Voucher::where('lottery_type_id',3)
        ->where('clock_id',5)
        ->where('number','!=',$win_num)
        ->where('verified',0)
        ->where('win',0)
        ->update(['win'=>0,'verified'=>1]);

        

        Number::where('clock_id',$clock_id)->where('lottery_type_id',$lottery_type_id)->update(['demand'=>0,'disable'=>0]);
        return redirect()->route('admin.index');
    }
}
