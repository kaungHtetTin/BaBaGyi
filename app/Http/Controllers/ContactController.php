<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Ads;
use App\Models\Notice;

class ContactController extends Controller
{
    public function index(){
       
        $contacts = Contact::all();
        $ads = Ads::all();
        $notices = Notice::all();
        return view('admin.contacts',[
            'page_name'=>'Setting',
            'contacts'=> $contacts,
            'ads'=>$ads,
            'notices' => $notices,
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

    public function delete($id){
        Contact::find($id)->delete();
        return back()->with('msg','The contact was successfully deleted');
    }
}
