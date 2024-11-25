<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ads;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function index(Request $req){
        $ads = Ads::all();
        $notices = Notice::all();

        return response()->json([
            'ads'=>$ads,
            'notices'=>$notices,
            'user' => $req->user(),
        ]);
    }
}
