<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ClientAppController;
use App\Http\Controllers\ClientApiController;

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

Route::get('/app', [ClientAppController::class, 'index'])->name('client.index');
Route::get('/app/manifest.webmanifest', [ClientAppController::class, 'manifest'])->name('client.manifest');
Route::get('/app/sw.js', [ClientAppController::class, 'worker']);
Route::get('/app/session', [ClientApiController::class, 'session']);
Route::match(['GET', 'POST'], '/app/api/{endpoint}', [ClientApiController::class, 'handle'])
    ->where('endpoint', '[a-zA-Z0-9/-]+')->middleware('throttle:120,1');


require __DIR__.'/auth.php';

Route::get('/login',function(){
    return redirect()->route('admin.login');
})->name('login');
