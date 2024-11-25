<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    public function payment_method(Request $req){
        $req->validate([
            'banking_id' => 'required',
        ]);
        $payment_method = PaymentMethod::with('banking:id,bank,icon_url')
        ->where('banking_id',$req->banking_id)
        ->orderBy(DB::raw("rand()"))
        ->limit(1)->get();
        
        $payment_method = $payment_method[0];
        return $payment_method;
    }

}
