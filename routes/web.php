<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------

| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth')->group(function () {
    
    //user profile
    
   
});

Route::get('/guide/top-up',function(){
    return view('top-up');
});

Route::get('/guide/withdraw',function(){
    return view('withdraw');
});


Route::get('/',function(){
   return redirect()->route('admin.index');
})->name('index');


require __DIR__.'/auth.php';

Route::get('/login',function(){
    return redirect()->route('admin.login');
})->name('login');
