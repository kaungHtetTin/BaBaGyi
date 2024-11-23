<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Lottery;

class LotteryController extends Controller
{
    public function records(Request $req){
   
        if(isset($req->type)) $type = $req->type;
        else $type = 2;

        $records = Lottery::with('clock:id,hour,minute,second,morning')
        ->where('lottery_type_id',$type)
        ->orderBy('id','desc')
        ->paginate(100);
        
        return view('admin.calendar-2d',[
            'page_name'=>'Calendar',
            'records'=>$records,
            'type'=>$type,
        ]);

    }
}
