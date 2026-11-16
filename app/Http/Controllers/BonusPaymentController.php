<?php

namespace App\Http\Controllers;

use App\Models\BonusPayment;
use App\Models\User;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BonusPaymentController extends Controller
{
    public function store(Request $request, int $id)
    {
        $data = $request->validate([
            'bonus_amount' => 'required|integer|min:1|max:2147483647',
            'bonus_note' => 'nullable|string|max:255',
            'bonus_request_id' => 'required|uuid',
        ], [], ['bonus_amount' => 'bonus amount', 'bonus_note' => 'bonus note', 'bonus_request_id' => 'payment reference']);

        $created = DB::transaction(function () use ($request, $id, $data) {
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            $previous = BonusPayment::where('user_id', $id)->where('request_id', $data['bonus_request_id'])->first();
            $note = $data['bonus_note'] ?? null;
            if ($previous) {
                abort_unless((int) $previous->amount === (int) $data['bonus_amount'] &&
                    $previous->note === $note && (int) $previous->admin_id === (int) $request->user()->id,
                    409, 'This payment reference has already been used. Reload the settings page before making a new payment.');
                return false;
            }
            if ((int) $user->balance + (int) $data['bonus_amount'] > 2147483647) {
                throw ValidationException::withMessages(['bonus_amount' => 'This bonus would exceed the maximum wallet balance.']);
            }

            $history = new WalletHistory();
            $history->user_id = $user->id;
            $history->title = 'Bonus';
            $history->amount = $data['bonus_amount'];
            $history->income = true;
            $history->save();

            BonusPayment::create([
                'user_id' => $user->id, 'admin_id' => $request->user()->id,
                'wallet_history_id' => $history->id, 'request_id' => $data['bonus_request_id'],
                'amount' => $data['bonus_amount'], 'note' => $note,
            ]);
            $user->balance = (int) $user->balance + (int) $data['bonus_amount'];
            $user->save();
            return true;
        });

        return redirect()->route('admin.users.setting', $id)->with('msg', $created
            ? 'Bonus of '.number_format($data['bonus_amount']).' MMK paid successfully.'
            : 'This bonus was already paid. No additional amount was credited.');
    }
}
