<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ads;
use App\Models\Notice;
use App\Models\LotteryType;

class NoticeController extends Controller
{
    public function index(Request $req){
        $ads = Ads::all();
        $notices = Notice::all();
        $lottery_types = LotteryType::all();

        return response()->json([
            'ads'=>$ads,
            'notices'=>$notices,
            'user' => $req->user(),
            'lottery_types' => $lottery_types,
            
        ]);
    }
}
