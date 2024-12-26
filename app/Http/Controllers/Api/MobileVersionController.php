<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileVersion;

class MobileVersionController extends Controller
{
    public function get_latest_version(){
        $mobile_version = MobileVersion::orderBy('id','desc')->first();

        return $mobile_version;
    }
}
