<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Voucher;
use App\Models\WalletHistory;
use App\Models\User;
use App\Services\AdminVoucherListing;
use Illuminate\Support\Facades\DB;


class VoucherController extends Controller
{
    public function index(){

    }

    public function voucher_btc(Request $request, AdminVoucherListing $listing)
    {
        return view('admin.vouchers', $listing->data($request, 1, 'BTC 2D Vouchers'));
    }

    public function voucher_2d(Request $request, AdminVoucherListing $listing)
    {
        return view('admin.vouchers', $listing->data($request, 2, '2D Vouchers'));
    }

    public function voucher_3d(Request $request, AdminVoucherListing $listing)
    {
        return view('admin.vouchers', $listing->data($request, 3, '3D Vouchers'));
    }

    public function approve($id){
        DB::transaction(function () use ($id) {
            $voucher = Voucher::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($voucher->win == 1 && $voucher->verified == 0, 422, 'This voucher is not an unpaid winner.');
            $user = User::whereKey($voucher->user_id)->lockForUpdate()->firstOrFail();
            $payout = $voucher->payoutAmount();

            $user->balance = $user->balance + $payout;
            $user->save();

            $wallet_history = new WalletHistory();
            $wallet_history->user_id = $user->id;
            $wallet_history->title = "ထီပေါက်";
            $wallet_history->amount = $payout;
            $wallet_history->income = 1;
            $wallet_history->save();

            $voucher->verified = 1;
            $voucher->verified_by = Auth::user()->id;
            $voucher->win_amount = $payout;
            $voucher->save();
        });
    
        return back()->with('msg','The voucher was successfully approved');
    }

    public function update(Request $req, $id){
        $req->validate([
            'amount'=>'required|numeric',
        ]);

        $voucher = Voucher::find($id);
        $user = $voucher->user;
  
        $win_amount = $req->amount;

        $user->balance = $user->balance + $win_amount;
        $user->save();

        $wallet_history = new WalletHistory();
        $wallet_history->user_id = $user->id;
        $wallet_history->title = "ထီပေါက်";
        $wallet_history->amount = $win_amount;
        $wallet_history->income = 1;
        $wallet_history->save();

        $voucher->verified = 1;
        $voucher->verified_by = Auth::user()->id;
        $voucher->win_amount = $win_amount;
        $voucher->win = 1;
        $voucher->save();
    
        return back()->with('msg','The voucher was successfully updated');
    }

    public function edit($id){
        $voucher = Voucher::find($id);
        return view('admin.voucher-detail',[
            'voucher'=>$voucher,
            'page_name'=>'Vouchers',
        ]);
    }

    public function delete($id){
        $voucher = Voucher::find($id);
        $amount = $voucher->amount;
        $user = User::find($voucher->user_id);

        $user->balance = $user->balance + $amount;
        $user->save();

        $wallet_history = new WalletHistory();
        $wallet_history->user_id = $user->id;
        $wallet_history->title = "ထီထိုးငွေ ပြန်အမ်းခြင်း";
        $wallet_history->amount = $amount;
        $wallet_history->income = 1;
        $wallet_history->save();

        $voucher->delete();

        return back()->with('msg','The voucher was successfully restored');
    }

}
