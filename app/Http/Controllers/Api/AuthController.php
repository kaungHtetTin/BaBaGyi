<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Models\Voucher;
use App\Models\WalletHistory;

class AuthController extends Controller
{
    public function register(Request $req){
        $req->validate([
            'name' => 'required',
            'phone' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone',$req->phone)->first();
        if($user){
            $response['status']= "fail";
            $response['error'] = "သင့်ဖုန်းနံပါတ်ဖြင့် မှတ်ပုံတင်ထားပြီးဖြစ်၍ အကောင့်ပြုလုပ်၍မရနိုင်ပါ";
            return response()->json($response,200);
        }

        $user = new User();
        $user->email = $req->phone;
        $user->name = $req->name;
        $user->phone = $req->phone;
        $user->password = Hash::make($req->password);
        $user->save();

        $response['_token'] = $user->createToken("superbrain")->plainTextToken;
        $response['user']= User::find($user->id);
        $response['status']= "success";
        return response()->json($response);
    }

    public function checkUser(Request $req){
        $user = User::where('phone',$req->phone)->first();
        if($user){
            $response['user_exist']= true;
        }else{
            $response['user_exist']= false;
        }
        return response()->json($response,200);
    }

    public function login(Request $request){
        
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user){
            $response['status']= "fail";
            $response['error'] = "လက်ရှိဖုန်းဖုန်းနံပါတ်ဖြင့် မှတ်ပုံတင်ထားခြင်းမရှိပါ";
            return response()->json($response);
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            $response['status']= "fail";
            $response['error'] = "လျှို့ဝှက်နံပါတ်မှားနေပါတယ်";
            return response()->json($response);
        }

        $response['_token'] = $user->createToken("superbrain")->plainTextToken;
        $response['user']= $user;
        $response['status']= "success";
        return response()->json($response);
    }

    public function resetPassword(Request $req){
        $user = $req->user();
        $req->validate([
            'current_password'=>'required',
            'new_password'=>'required',
        ]);

        $user = User::find($user->id);

        if (! $user || ! Hash::check($req->current_password, $user->password)) {
            $response['status']= "fail";
            $response['error'] = "လျှို့ဝှက်နံပါတ်မှားနေပါတယ်";
            return response()->json($response);
        }

        $user->password =  Hash::make($req->new_password);
        $user->save();

        return response()->json(['status'=>'success']);
       
    }

    public function deleteAccount(Request $req){
        $user = $req->user();
        $req->validate([
            'password'=>'required',
        ]);

        $user = User::find($user->id);
        if(! $user){
            $response['status']= "fail";
            $response['error'] = "အကောင့်မရှိပါ";
            return response()->json($response);
        }
        if (! Hash::check($req->password, $user->password)) {
            $response['status']= "fail";
            $response['error'] = "လျှို့ဝှက်နံပါတ်မှားနေပါတယ်";
            return response()->json($response);
        }

        Transaction::where('user_id',$user->id)->update(['user_id'=>2]);
        Withdraw::where('user_id',$user->id)->update(['user_id'=>2]);
        Voucher::where('user_id',$user->id)->update(['user_id'=>2]);
        WalletHistory::where('user_id',$user->id)->update(['user_id'=>2]);

        $user->delete();
        return response()->json(['status'=>'success']);
    }
}
