<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(){
        return view('admin.chatrooms',[
            'page_name' => 'Message',
        ]);
    }
}
