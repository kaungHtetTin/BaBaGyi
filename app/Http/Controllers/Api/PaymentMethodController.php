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
        ->where('disable', false)
        ->inRandomOrder()->first();

        abort_unless($payment_method, 404, 'No payment account is currently available for this banking.');
        return $payment_method;
    }

}
