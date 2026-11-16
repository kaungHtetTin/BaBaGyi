@extends('admin.master')

@section('content')
<div class="container-fluid telegram-page">
    @if(session('msg'))<div class="alert alert-success" role="status">{{ session('msg') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="admin-page-heading"><div><p class="eyebrow">NOTIFICATIONS</p><h1>Telegram admin alerts</h1></div></div>
    <section class="panel telegram-panel">
        <div class="panel-heading"><div><h2>Bot settings</h2><p class="panel-subtitle">Send new top-up and withdrawal requests to one private admin chat.</p></div>
            <span class="status-badge">{{ $settings->enabled ? 'Enabled' : 'Disabled' }}</span>
        </div>
        <div class="telegram-settings-grid">
            <form class="settings-form" method="POST" action="{{ route('admin.telegram.update') }}" autocomplete="off">
                @csrf @method('PUT')
                <label class="form-field"><span>Bot token</span>
                    <input type="password" name="bot_token" aria-label="Bot token" aria-describedby="telegram-token-help" maxlength="255" autocomplete="new-password" placeholder="{{ $settings->bot_token ? 'Token saved — leave blank to keep it' : 'Paste the token from BotFather' }}">
                    <small class="muted" id="telegram-token-help">{{ $settings->bot_token ? 'A token is saved securely. Enter a new token only to replace it.' : 'The token is encrypted and never displayed after saving.' }}</small>
                </label>
                <label class="form-field"><span>Admin chat ID</span>
                    <input name="admin_chat_id" aria-label="Admin chat ID" aria-describedby="telegram-chat-help" value="{{ old('admin_chat_id', $settings->admin_chat_id) }}" inputmode="numeric" pattern="[1-9][0-9]{0,19}" maxlength="20" placeholder="e.g. 123456789">
                    <small class="muted" id="telegram-chat-help">Use the admin account’s numeric Telegram ID. Only private chats are supported.</small>
                </label>
                <label class="telegram-check"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', session()->hasOldInput() ? false : $settings->enabled))> Enable Telegram notifications</label>
                <label class="telegram-check"><input type="checkbox" name="notify_topups" value="1" @checked(old('notify_topups', session()->hasOldInput() ? false : $settings->notify_topups))> Notify on new top-up requests</label>
                <label class="telegram-check"><input type="checkbox" name="notify_withdrawals" value="1" @checked(old('notify_withdrawals', session()->hasOldInput() ? false : $settings->notify_withdrawals))> Notify on new withdrawal requests</label>
                <div class="settings-form-actions"><button class="btn primary" type="submit">Save settings</button></div>
            </form>
            <aside>
                <h3>Connect your admin account</h3>
                <ol class="telegram-steps">
                    <li>Open <a href="https://t.me/BotFather" target="_blank" rel="noopener noreferrer">@BotFather</a> in Telegram, send /newbot and copy the bot token.</li>
                    <li>Open your new bot from the admin account and press <strong>Start</strong>.</li>
                    <li>Get your numeric Telegram account ID, then save it above as the admin chat ID.</li>
                    <li>Send a test message, then enable notifications and save.</li>
                </ol>
                <p class="muted">Messages use Burmese labels and include the user, phone, amount, payment provider and request reference. Withdrawals also include the recipient account details.</p>
                <form method="POST" action="{{ route('admin.telegram.test') }}">@csrf
                    <button class="btn secondary" type="submit" @disabled(!$settings->bot_token || !$settings->admin_chat_id)>Send test message</button>
                </form>
                <p class="muted telegram-test-help">The test uses your saved settings and works while notifications are disabled.</p>
            </aside>
        </div>
    </section>
    <section class="panel telegram-panel">
        <div class="panel-heading"><div><h2>Recent notifications</h2><p class="panel-subtitle">Failed deliveries retry up to five times. Refresh to see the latest status.</p></div>
            <a class="btn secondary" href="{{ route('admin.telegram') }}">Refresh status</a>
        </div>
        <div class="table-wrap"><table class="table table-bordered"><thead><tr><th>Request</th><th>Created</th><th>Delivery</th><th>Attempts</th><th>Details</th><th>Action</th></tr></thead><tbody>
            @forelse($alerts as $alert)
                <tr>
                    <td><strong>{{ $alert->type === 'topup' ? 'TOPUP' : 'WITHDRAW' }}-{{ $alert->request_id }}</strong></td>
                    <td>{{ $alert->created_at->format('M d, Y h:i A') }}</td>
                    <td>{{ $alert->sent_at ? 'Sent' : ($alert->next_attempt_at ? 'Pending retry' : 'Held / Failed') }}</td>
                    <td>{{ $alert->attempts }}</td><td class="telegram-delivery-detail">{{ $alert->last_error ?? ($alert->sent_at ? 'Delivered '.$alert->sent_at->format('M d, h:i A') : 'Waiting for delivery') }}</td>
                    <td>@if(!$alert->sent_at)<form method="POST" action="{{ route('admin.telegram.retry', $alert->id) }}">@csrf<button class="btn secondary" type="submit" @disabled(!$settings->enabled || $alert->chat_id !== $settings->admin_chat_id)>Retry</button></form>@else<span class="muted">Complete</span>@endif</td>
                </tr>
            @empty<tr><td colspan="6"><span class="muted">No notifications yet. Enable alerts to receive new requests.</span></td></tr>@endforelse
        </tbody></table></div>
        {{ $alerts->links() }}
    </section>
</div>
<style>
    .admin-main:has(.telegram-page) { background: var(--color-bg) !important; }
    .telegram-page .eyebrow { color: var(--color-muted); }
    .telegram-panel { margin-bottom: 14px; background: var(--color-surface); }
    .telegram-settings-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 24px; padding: 10px 0; }
    .telegram-settings-grid aside { font-size: 13px; line-height: 1.6; }
    .telegram-settings-grid h3 { font-size: 15px; font-weight: 700; line-height: 1.4; }
    .telegram-settings-grid a { color: var(--color-text); text-decoration: underline; }
    .telegram-check { display: flex; align-items: center; gap: 9px; min-height: 34px; font-size: 13px; }
    .telegram-check input { width: 16px; height: 16px; flex: 0 0 16px; }
    .telegram-steps { padding-left: 20px; line-height: 1.7; }
    .telegram-steps li { margin-bottom: 8px; }
    .telegram-test-help { margin-top: 10px; }
    .telegram-delivery-detail { min-width: 200px; max-width: 380px; white-space: normal; }
    @media(max-width: 760px) { .telegram-settings-grid { grid-template-columns: minmax(0, 1fr); gap: 20px; } .telegram-check { min-height: 44px; } .telegram-panel .btn { min-height: 44px; } }
</style>
@endsection
