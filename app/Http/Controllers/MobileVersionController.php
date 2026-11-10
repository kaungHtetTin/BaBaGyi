<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobileVersion;
use Illuminate\Validation\ValidationException;

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
            'version_name'=>['required', 'string', 'max:80', 'regex:/\A[a-zA-Z0-9][a-zA-Z0-9._-]*\z/'],
            'min_android_version'=>'required|integer',
            'anroid_apk_file'=>['bail', 'required', 'file', function ($attribute, $value, $fail) {
                if (strtolower($value->getClientOriginalExtension()) !== 'apk') {
                    $fail('Please upload an Android APK file.');
                }
            }],
        ]);

        // The public disk resolves an absolute path and creates missing directories.
        $path = $req->file('anroid_apk_file')->storeAs(
            'apks', 'mastergyi-'.$req->version_name.'.apk', 'public'
        );
        if ($path !== false) {
            $mobile_version = new MobileVersion();
            $mobile_version->version_code = $req->version_code;
            $mobile_version->version_name = $req->version_name;
            $mobile_version->min_android_version = $req->min_android_version;
            $mobile_version->url = $path;
            $mobile_version->save();

            return back()->with('msg', 'The latest version was successfully published.');

        }else{
            throw ValidationException::withMessages([
                'anroid_apk_file' => 'The APK could not be saved. Check the server storage permissions and available disk space.',
            ]);
        }

       
        
    }
}
