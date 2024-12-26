<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobileVersion;

class MobileVersionController extends Controller
{
    public function index(){
        $mobile_versions = MobileVersion::all();
        return view('admin.mobile-versions',[
            'page_name'=>'Setting',
            'mobile_versions'=>$mobile_versions
        ]);
    }

    public function add(){
        return view('admin.mobile-versions-add',[
            'page_name'=>'Setting'
        ]);
    }

    public function store(Request $req){

        $req->validate([
            'version_code'=>'required|integer',
            'version_name'=>'required',
            'min_android_version'=>'required|integer',
            'anroid_apk_file'=>'required',
        ]);

        $file=$_FILES['anroid_apk_file']['name'];
        $file_loc=$_FILES['anroid_apk_file']['tmp_name'];
        $folder="../storage/app/public/apks/";
        if(move_uploaded_file($file_loc,$folder."babagyi-".$req->version_name.".apk")){
            $mobile_version = new MobileVersion();
            $mobile_version->version_code = $req->version_code;
            $mobile_version->version_name = $req->version_name;
            $mobile_version->min_android_version = $req->min_android_version;
            $mobile_version->url = "apks/babagyi-".$req->version_name.".apk";
            $mobile_version->save();

            return back()->with('msg', 'The latest version was successfully published.');

        }else{
            $response['status']="fail";
            return $response;
        }

       
        
    }
}
