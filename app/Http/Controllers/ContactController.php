<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index(){
        return view('admin.contacts',[
            'page_name'=>'Setting',
        ]);

    }

    public function store(Request $req){
        $req->validate([
            'service'=>'required',
            'contact'=>'required',
        ]);

        $service = $req->service;
        $contact = $req->contact;

        $Contact = new Contact();
        $Contact->service = $service;
        $Contact->contact = $contact;
        $Contact->save();
        return back()->with('msg','New contact was successfully added');
    }
}
