<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AdminController extends Controller
{
    public function index(){
        $admins = User::where('admin',1)->get();
        return view('admin.admins',[
            'page_name'=>'Setting',
            'admins' => $admins,
        ]);
    }

    public function disable($id){
        $admin = User::find($id);
        $admin->admin = 0;
        $admin->save();

        return back()->with('msg','The admin was disable successfully');
    }
}
