<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banking;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index(){
        $payment_methods = PaymentMethod::with('banking:id,bank,icon_url')->paginate(50);
        
        return view('admin.payment-methods',[
            'page_name'=>'Setting',
            'payment_methods'=>$payment_methods,
        ]);
    }

    public function create(){
        $bankings = Banking::all();
        return view('admin.payment-method-create',[
            'page_name'=>'Setting',
            'bankings' => $bankings,
        ]);
    }

    public function store(Request $req){
        $req->validate([
            'phone' => 'required',
            'account_name' => 'required',
        ]);
 
        $bankings = Banking::all();
        $banking_checked = false;
        foreach($bankings as $banking){
            $id = $banking->id;
            $key = "banking_$id";
            if(isset($req->$key)){
                if($req->$key == 'on'){
                    $payment_method = PaymentMethod::where('method',$req->phone)->where('banking_id',$id)->first();
                    if(!$payment_method){
                        $payment_method = new PaymentMethod();
                        $payment_method->banking_id = $id;
                        $payment_method->method = $req->phone;
                        $payment_method->account_name = $req->account_name;
                        $payment_method->save();
                        $banking_checked = true;
                    }
                }
            }
        }

        if($banking_checked){
            return back()->with('msg','New payment was added successfully');
        }else{
            return back()->with('banking_error','Please select the banking.');
        }
    }

    public function disable(Request $req){
        $id = $req->id;
        $payment_method = PaymentMethod::find($id);
        $payment_method->disable = 1;
        $payment_method->save();
        return back()->with('msg','New payment was disable successfully');
    }

    public function enable(Request $req){
        $id = $req->id;
        $payment_method = PaymentMethod::find($id);
        $payment_method->disable = 0;
        $payment_method->save();
        return back()->with('msg','New payment was activated successfully');
    }
}
