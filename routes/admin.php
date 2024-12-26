<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BankingController;
use App\Http\Controllers\LotteryTypeController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\LotteryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\NumberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\MobileVersionController;

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


    Route::middleware('admin')->group(function (){
  
        Route::get('/',[LayoutController::class,'index'])->name('admin.index');

        Route::get('/register',function(){
        return view('admin.register',[

            ]);
        })->name('admin.register');
        Route::post('register', [RegisteredUserController::class, 'storeAdmin'])->name('admin.register');
        
        
        Route::get('/payment-methods',[PaymentMethodController::class,'index'])->name('admin.payment-methods');
        Route::get('/payment-methods/create',[PaymentMethodController::class,'create'])->name('admin.payment-methods.create');
        Route::post('/payment-methods/create',[PaymentMethodController::class,'store'])->name('admin.payment-methods.store');
        Route::post('/payment-methods/disable',[PaymentMethodController::class,'disable'])->name('admin.payment-methods.disable');
        Route::post('/payment-methods/enable',[PaymentMethodController::class,'enable'])->name('admin.payment-methods.enable');

        Route::get('/admins',[AdminController::class,'index'])->name('admin.admins');
        Route::post('/admins/{id}/disable',[AdminController::class,'disable'])->name('admin.admins.disable');

        Route::get('/bankings',[BankingController::class,'index'])->name('admin.bankings');
        Route::get('/lottery-types',[LotteryTypeController::class,'index'])->name('admin.lottery-types');
        Route::get('/lottery-types/{id}/edit',[LotteryTypeController::class,'edit'])->name('admin.lottery-types.edit');
        Route::PUT('/lottery-types/{id}',[LotteryTypeController::class,'update'])->name('admin.lottery-types.update');
        Route::PUT('/lottery-types/{id}/change-status',[LotteryTypeController::class,'changeStatus'])->name('admin.lottery-types.change-status');

        Route::get('/chatrooms',[ConversationController::class,'index'])->name('admin.chatrooms');

        Route::get('/transactions',[TransactionController::class,'index'])->name('admin.transactions');
        Route::put('/transactions/{id}',[TransactionController::class,'approve'])->name('admin.transactions.approve');
        Route::delete('/transactions/{id}',[TransactionController::class,'destroy'])->name('admin.transactions.remove');

        Route::get('/withdraws',[WithdrawController::class,'index'])->name('admin.withdraws');
        Route::put('/withdraws/{id}',[WithdrawController::class,'approve'])->name('admin.withdraws.approve');
        Route::delete('/withdraws/{id}',[WithdrawController::class,'destroy'])->name('admin.withdraws.remove');

        Route::get('/users',[UserController::class,'index'])->name('admin.users');
        Route::get('/users/search',[UserController::class,'search'])->name('admin.users.search');
        Route::get('/users/{id}/transactions',[UserController::class,'transactions'])->name('admin.users.transactions');
        Route::get('/users/{id}/withdraws',[UserController::class,'withdraws'])->name('admin.users.withdraws');
        Route::get('/users/{id}/vouchers',[UserController::class,'vouchers'])->name('admin.users.vouchers');
        Route::get('/users/{id}/wallet-histories',[UserController::class,'wallet_histories'])->name('admin.users.wallet-histories');
        Route::get('/users/{id}/setting',[UserController::class,'setting'])->name('admin.users.setting');
        Route::put('/users/{id}/disable',[UserController::class,'disable'])->name('admin.users.disable');
        Route::put('/users/{id}/activate',[UserController::class,'activate'])->name('admin.users.activate');
        Route::put('/users/{id}/password-reset',[UserController::class,'resetPassword'])->name('admin.users.password-reset');

        Route::get('/vouchers/btc-2d',[VoucherController::class,'voucher_btc'])->name('admin.vouchers.btc-2d');
        Route::get('/vouchers/thai-2d',[VoucherController::class,'voucher_2d'])->name('admin.vouchers.thai-2d');
        Route::get('/vouchers/thai-3d',[VoucherController::class,'voucher_3d'])->name('admin.vouchers.thai-3d');
        Route::put('/vouchers/{id}/approve',[VoucherController::class,'approve'])->name('admin.vouchers.approve');
        Route::delete('/vouchers/{id}/delete',[VoucherController::class,'delete'])->name('admin.vouchers.delete');

        Route::get('/lotteries/records',[LotteryController::class,'records'])->name('admin.lotteries.records');

        Route::get('/profile',[ProfileController::class,'index'])->name('admin.profile');
        Route::post('/change-avatar',[ProfileController::class,'changeAvatar'])->name('admin.avatar.modify');
        Route::put('/change-password',[ProfileController::class,'changePassword'])->name('admin.password.modify');

        Route::get('/contacts',[ContactController::class,'index'])->name('admin.contacts');
        Route::post('/contacts',[ContactController::class,'store'])->name('admin.contacts.store');
        Route::delete('/contacts/{id}',[ContactController::class,'delete'])->name('admin.contacts.remove');

        Route::put('/numbers/disable-all',[NumberController::class,'disableAll'])->name('admin.numbers.disable-all');
        Route::put('/numbers/disable-by-group',[NumberController::class,'disabeByGroup'])->name('admin.numbers.disable-by-group');
        Route::put('/numbers/reset-sell',[NumberController::class,'resetSellAmount'])->name('admin.numbers.reset-sell');
        Route::get('/numbers',[NumberController::class,'index'])->name('admin.numbers');
        Route::get('/numbers/reports',[NumberController::class,'report'])->name('admin.numbers.report');
        Route::put('/numbers/{id}/disble',[NumberController::class,'disable'])->name('admin.numbers.disable');
        Route::put('/numbers/{id}/activate',[NumberController::class,'activate'])->name('admin.numbers.activate');
        Route::get('/numbers/{id}/edit',[NumberController::class,'edit'])->name('admin.numbers.edit');
        Route::put('/numbers/{id}',[NumberController::class,'update'])->name('admin.numbers.modify');
        
        Route::get('/reports',[ReportController::class,'index'])->name('admin.reports');
        Route::post('/reports',[ReportController::class,'store'])->name('admin.reports.save');
        Route::get('/reports/{id}',[ReportController::class,'show'])->name('admin.reports.detail');


        Route::post('/ad-photos',[AdsController::class, 'store'])->name('admin.ad-photo.save');
        Route::delete('/ad-photos/{id}',[AdsController::class, 'destroy'])->name('admin.ad-photo.destroy');

        Route::post('/notices',[NoticeController::class,'store'])->name('admin.notices.save');
        Route::delete('/notices/{id}',[NoticeController::class,'delete'])->name('admin.notices.remove');

        Route::get('/holidays',[HolidayController::class,'index'])->name('admins.holidays');

        Route::get('/mobile-versions',[MobileVersionController::class,'index'])->name('admins.mobile-versions');
        Route::get('/mobile-versions/add',[MobileVersionController::class,'add'])->name('admins.mobile-versions.add');
        Route::post('/mobile-versions/add',[MobileVersionController::class,'store'])->name('admins.mobile-versions.add');
    });
});


Route::get('/login',function(){
    return view('admin.login');
})->name('admin.login');
Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('admin.login');

