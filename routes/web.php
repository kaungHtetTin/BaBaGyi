<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;

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


Route::get('/', [WelcomeController::class, 'index'])->name('index');
Route::get('/download', [WelcomeController::class, 'download'])->name('app.download');


require __DIR__.'/auth.php';

Route::get('/login',function(){
    return redirect()->route('admin.login');
})->name('login');
