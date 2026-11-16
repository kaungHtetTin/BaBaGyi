<?php

namespace App\Services;

use App\Models\TelegramAlert;
use App\Models\TelegramSetting;
use App\Models\Transaction;
use App\Models\Withdraw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function record(Transaction|Withdraw $request): void
    {
        $settings = TelegramSetting::current();
        $topup = $request instanceof Transaction;
        if (! $settings->enabled || ! $settings->bot_token || ! $settings->admin_chat_id ||
            ! ($topup ? $settings->notify_topups : $settings->notify_withdrawals)) {
            return;
        }

        $user = $request->user;
        $bank = $topup ? $request->payment_method->banking->bank : $request->banking->bank;
        $lines = [
            $topup ? '📥 ငွေသွင်းတောင်းဆိုမှု အသစ် ရောက်ရှိသည်!' : '📤 ငွေထုတ်တောင်းဆိုမှု အသစ် ရောက်ရှိသည်!',
            '',
            '👤 အမည်: '.$user->name,
            '📱 ဖုန်း: '.$user->phone,
            '💰 ပမာဏ: '.number_format($request->amount).' ကျပ် ('.$bank.')',
        ];
        if ($topup) {
            $lines[] = '🔢 လုပ်ဆောင်မှုနံပါတ်: '.str_pad((string) $request->bank_transaction_id, 6, '0', STR_PAD_LEFT);
        } else {
            $lines[] = '🏦 အကောင့်အမည်: '.$request->account_name;
            $lines[] = '💳 အကောင့်နံပါတ်: '.$request->method;
        }
        $lines[] = '🧾 Request ID: '.($topup ? 'TOPUP-' : 'WITHDRAW-').$request->id;
        $lines[] = '🕒 '.$request->created_at->timezone('Asia/Yangon')->format('Y-m-d h:i A');
        $lines[] = '⏳ အတည်ပြုရန် စောင့်ဆိုင်းနေသည်';

        // Persist in the same transaction as the financial request, including the web bridge's outer transaction.
        $alert = TelegramAlert::firstOrCreate(['type' => $topup ? 'topup' : 'withdrawal', 'request_id' => $request->id], [
            'chat_id' => $settings->admin_chat_id, 'message' => implode("\n", $lines), 'next_attempt_at' => now(),
        ]);
        $id = $alert->id;
        app()->terminating(function () use ($id) {
            try {
                $this->deliver($id);
            } catch (\Throwable $e) {
                // Never log an HTTP exception: its URL may contain the bot token.
                Log::warning('Telegram delivery deferred to scheduler.', ['alert_id' => $id]);
            }
        });
    }

    public function send(TelegramSetting $settings, string $chatId, string $message): ?string
    {
        try {
            $response = Http::connectTimeout(3)->timeout(8)->post(
                'https://api.telegram.org/bot'.$settings->bot_token.'/sendMessage',
                ['chat_id' => $chatId, 'text' => mb_substr($message, 0, 4000)]
            );
            if ($response->successful() && $response->json('ok') === true) {
                return null;
            }
            // Fixed messages avoid exposing credentials or user data from Telegram responses.
            $code = (int) ($response->json('error_code') ?? $response->status());
            return match ($code) {
                400 => 'Telegram rejected the chat or message. Check the chat ID and press Start in the bot.',
                401 => 'Invalid bot token. Update the token in Telegram settings.',
                403 => 'The bot is blocked. Unblock it and press Start in the admin chat.',
                429 => 'Telegram rate limit reached. Delivery will be retried.',
                default => 'Telegram service error. Delivery will be retried.',
            };
        } catch (\Throwable $e) {
            return 'Unable to connect to Telegram. Check server internet access; delivery will be retried.';
        }
    }

    public function deliver(int $id): void
    {
        if (DB::transactionLevel() > 0) {
            return;
        }
        DB::transaction(function () use ($id) {
            $alert = TelegramAlert::whereKey($id)->lockForUpdate()->first();
            if (! $alert || $alert->sent_at || $alert->attempts >= 5 || ! $alert->next_attempt_at || $alert->next_attempt_at->isFuture()) {
                return;
            }
            $settings = TelegramSetting::current();
            if (! $settings->enabled || ! $settings->bot_token || ! $settings->admin_chat_id) {
                return;
            }
            // Never silently send old financial details to a newly configured recipient.
            if ($alert->chat_id !== $settings->admin_chat_id) {
                $alert->update(['last_error' => 'Admin chat changed. This alert was held for the original recipient.', 'next_attempt_at' => null]);
                return;
            }
            $error = $this->send($settings, $alert->chat_id, $alert->message);
            $attempts = $alert->attempts + 1;
            $alert->update([
                'attempts' => $attempts, 'last_error' => $error,
                'sent_at' => $error ? null : now(),
                'next_attempt_at' => $error && $attempts < 5 ? now()->addMinutes(2 ** ($attempts - 1)) : null,
            ]);
        });
    }

    public function flush(): void
    {
        TelegramAlert::whereNull('sent_at')->where('attempts', '<', 5)
            ->where('next_attempt_at', '<=', now())->orderBy('id')->limit(20)->pluck('id')
            ->each(fn ($id) => $this->deliver($id));
    }
}
