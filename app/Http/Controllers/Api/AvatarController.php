<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Avatar;

class AvatarController extends Controller
{
    public function index(){
        $avatars = Avatar::all();
        return response()->json($avatars);
    }
}
