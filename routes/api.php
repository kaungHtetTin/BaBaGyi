<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\LotteryTypeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WithdrawController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\BankingController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\RemoteNumberController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\AdminNotifyController;
use App\Http\Controllers\Api\NumberController;

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

 Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', [UserController::class,'user']);
    Route::get('/user/transactions', [UserController::class,'transactions']);
    Route::get('/user/unverified-transactions', [UserController::class,'unverified_transactions']);
    Route::get('/user/withdraws', [UserController::class,'withdraws']);
    Route::get('/user/vouchers', [UserController::class,'vouchers']);
    Route::get('/user/wallet-histories', [UserController::class,'walletHistories']);
    Route::post('/user/update', [UserController::class,'update']);
    Route::post('/user/update-password-recovery-hint', [UserController::class,'updateRecoveryHint']);

    Route::get('/payment-method',[PaymentMethodController::class,'payment_method']);
    Route::post('/top-up', [TransactionController::class,'store']);

    Route::post('/withdraw', [WithdrawController::class,'store']);

    Route::post('/password-reset', [AuthController::class,'resetPassword']);
    Route::post('/delete-account', [AuthController::class,'deleteAccount']);

    Route::post('/vouchers/store',[VoucherController::class,'store']);
    Route::get('/notices',[NoticeController::class,'index']);
    
});

Route::post('/login',[AuthController::class,'login']);
Route::post('/register',[AuthController::class,'register']);
Route::post('/check-user',[AuthController::class,'checkUser']);

Route::get('/lotteries/records',[LotteryController::class,'records']);
Route::get('/lotteries/3d-records',[LotteryController::class,'_3d_records']);
Route::get('/lottery-types/{id}/clocks',[LotteryTypeController::class,'clocks']);

Route::get('/contacts',[ContactController::class,'index']);

Route::get('/bankings',[BankingController::class,'index']);

Route::get('/avatars',[AvatarController::class,'index']);

Route::get('/remote/thai-2d',[RemoteNumberController::class,'get2DNumber']);
Route::get('/remote/thai-3d',[RemoteNumberController::class,'get3DNumber']);

Route::get('/admin-notify',[AdminNotifyController::class,'notify']);

Route::get('/numbers',[NumberController::class,'index']);

Route::get('/time-zone',function(){
    $now = now();
    return date("Y-m-d, H:i:s");
});