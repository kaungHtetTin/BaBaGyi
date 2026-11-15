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
        $data = $this->validatedContact($req);

        $Contact = new Contact();
        $Contact->service = $data['service'];
        $Contact->contact = $data['contact'];
        $Contact->save();
        return back()->with('msg','New contact was successfully added');
    }

    public function delete($id){
        Contact::findOrFail($id)->delete();
        return back()->with('msg','The contact was successfully deleted');
    }

    public function update(Request $req, $id)
    {
        $contact = Contact::findOrFail($id);
        $data = $this->validatedContact($req);
        $contact->service = $data['service'];
        $contact->contact = $data['contact'];
        $contact->save();
        return back()->with('msg', 'The contact was successfully updated');
    }

    private function validatedContact(Request $req): array
    {
        $data = $req->validate([
            'service' => 'required|string|in:Telegram,Viber',
            'contact' => 'required|string|max:255',
        ]);
        $value = \App\Support\ContactLink::normalize($data['service'], $data['contact']);
        if ($value === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'contact' => 'Use a Telegram username, t.me link or international phone number; for Viber use an international phone number (+959...), invite.viber.com/vb.me link or Viber chat link.',
            ]);
        }
        $data['contact'] = $value;
        return $data;
    }
}
