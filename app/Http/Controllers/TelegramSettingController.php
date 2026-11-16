<?php

namespace App\Http\Controllers;

use App\Models\TelegramAlert;
use App\Models\TelegramSetting;
use App\Services\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TelegramSettingController extends Controller
{
    public function index()
    {
        return response()->view('admin.telegram', [
            'page_name' => 'Telegram', 'settings' => TelegramSetting::current(),
            'alerts' => TelegramAlert::latest()->paginate(20),
        ])->header('Cache-Control', 'no-store');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'bot_token' => ['nullable', 'string', 'max:255', 'regex:/^[0-9]+:[A-Za-z0-9_-]{30,}$/'],
            'admin_chat_id' => ['nullable', 'string', 'regex:/^[1-9][0-9]{0,19}$/'],
            'enabled' => 'sometimes|boolean', 'notify_topups' => 'sometimes|boolean', 'notify_withdrawals' => 'sometimes|boolean',
        ]);
        $settings = TelegramSetting::current();
        if (! empty($data['bot_token'])) {
            $settings->bot_token = $data['bot_token'];
        }
        $settings->admin_chat_id = $data['admin_chat_id'] ?? null;
        $settings->enabled = $request->boolean('enabled');
        $settings->notify_topups = $request->boolean('notify_topups');
        $settings->notify_withdrawals = $request->boolean('notify_withdrawals');
        if ($settings->enabled && (! $settings->bot_token || ! $settings->admin_chat_id)) {
            throw ValidationException::withMessages(['enabled' => 'Enter a bot token and admin chat ID before enabling notifications.']);
        }
        $settings->save();
        return redirect()->route('admin.telegram')->with('msg', 'Telegram settings saved.');
    }

    public function test(TelegramNotifier $notifier)
    {
        $settings = TelegramSetting::current();
        if (! $settings->bot_token || ! $settings->admin_chat_id) {
            return back()->withErrors(['telegram' => 'Save the bot token and admin chat ID first.']);
        }
        $error = $notifier->send($settings, $settings->admin_chat_id, '✅ Telegram ချိတ်ဆက်မှု အောင်မြင်ပါသည်။ ငွေသွင်း/ငွေထုတ် အသိပေးချက်များကို ဤ admin chat သို့ ပို့ပါမည်။');
        return $error ? back()->withErrors(['telegram' => $error]) : back()->with('msg', 'Test message sent to the admin chat.');
    }

    public function retry(int $id)
    {
        $settings = TelegramSetting::current();
        $alert = TelegramAlert::findOrFail($id);
        if (! $settings->enabled || $alert->chat_id !== $settings->admin_chat_id) {
            return back()->withErrors(['telegram' => 'Enable notifications for the original admin chat before retrying this alert.']);
        }
        if (! $alert->sent_at) {
            $alert->update(['attempts' => 0, 'next_attempt_at' => now(), 'last_error' => null]);
            app()->terminating(fn () => app(TelegramNotifier::class)->deliver($id));
        }
        return back()->with('msg', 'Notification scheduled for retry.');
    }
}
