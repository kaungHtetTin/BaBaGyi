<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ads;
use App\Models\Notice;
use App\Models\LotteryType;
use App\Models\LotteryClock;
use App\Models\MobileVersion;

class NoticeController extends Controller
{
    public function index(Request $req){
        $ads = Ads::all();
        $notices = Notice::all();
        $lottery_types = LotteryType::all();

        $mobile_version = MobileVersion::orderBy('id','desc')->first();

        return response()->json([
            'ads'=>$ads,
            'notices'=>$notices,
            'user' => $req->user(),
            'lottery_types' => $lottery_types,
            'mobile_version'=>$mobile_version,
        ]);
    }
}
