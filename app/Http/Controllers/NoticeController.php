<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function store(Request $req){
        $req->validate([
            'content'=>'required',
        ]);

        $content = $req->content;
        $notice = new Notice();
        $notice->content = $content;
        $notice->save();
        return back()->with('msg','The notice was successfully uploaded');
    }

    public function delete($id){
        Notice::find($id)->delete();
        return back()->with('msg','The notice was successfully uploaded');
    }
}
