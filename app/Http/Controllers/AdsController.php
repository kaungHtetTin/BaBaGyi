<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use App\Models\Ads;
use Illuminate\Support\Facades\Storage;

class AdsController extends Controller
{
    public function store(Request $req){
       
        $req->validate([
            'image_file'=>'required',
        ]);

        $ad = new Ads();
       
        if($req->hasFile('image_file')){
            $image = $req->file('image_file');
            $path = $image->store('images', 'public');
            $ad->url = $path;
            $ad->save();
            return back()->with('msg', 'Your ad photo was successfully uploaded');
        }

        return "hello";

    }

    public function destroy($id){
        $ad = Ads::find($id);
        $image_url = $ad->url;
        if ($image_url) {
            Storage::disk('public')->delete($image_url); // Delete old image
        }
        $ad->delete();
        return back()->with('msg', 'The ad photo was successfully deleted');
    }
}
