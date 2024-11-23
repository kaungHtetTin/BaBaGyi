<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banking;

class BankingController extends Controller
{
    public function index(){
        $bankings = Banking::all();
        return response()->json($bankings);
    }
}
