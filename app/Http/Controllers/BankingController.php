<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banking;

class BankingController extends Controller
{
    public function index(){
        $bankings = Banking::all();
        return view('admin.bankings',[
            'page_name' => 'Setting',
            'bankings' => $bankings,
        ]);
    }
}
