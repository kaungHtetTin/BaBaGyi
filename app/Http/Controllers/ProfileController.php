<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Avatar;

class ProfileController extends Controller
{
    public function index(){
        $avatars = Avatar::all();
        $user = Auth::user();
        return view('admin.profile',[
            'page_name' => 'Edit Profile',
            'avatars'=>$avatars,
            'user'=>$user,
        ]);
    }

    public function changeAvatar(Request $req){
        $req->validate([
            'avatar_url'=>'required',
        ]);

        $user = Auth::user();
        $user->avatar_url = $req->avatar_url;
        $user->save();

        return back()->with('msg','The profile avatar was successfully updated');
    }

    public function changePassword(Request $req){
        $req->validate([
            'new_password'=>'required',
            'current_password'=>'required',
        ]);

        $current_password = $req->current_password;
        $new_password = $req->new_password;

        $user = Auth::user();
        
    }
}
