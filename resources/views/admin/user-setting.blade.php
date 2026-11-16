@extends('admin.master')

@section('content')
    <div class="container-fluid user-setting-page">
        @if (session('msg'))
            <div class="alert alert-success">
                {{ session('msg') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">USER OPERATIONS</p>
                <h1>User detail</h1>
            </div>
            <a class="btn secondary" href="{{ route('admin.users') }}">
                <i class="fas fa-arrow-left"></i>
                Users
            </a>
        </div>

        <section class="panel glass user-profile-panel">
            <div class="user-profile-heading">
                <div class="user-identity">
                    <span class="user-avatar large">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <div>
                        <p class="eyebrow">ACCOUNT PROFILE</p>
                        <h2>{{ $user->name }}</h2>
                        <p>{{ $user->email ?: 'No email recorded' }}</p>
                    </div>
                </div>
                @if ($user->disable)
                    <span class="status status-danger"><span class="status-dot"></span>Disabled</span>
                @else
                    <span class="status status-success"><span class="status-dot"></span>Active</span>
                @endif
            </div>

            <div class="user-detail-grid">
                <div>
                    <span>Phone</span>
                    <strong>{{ $user->phone ?: '-' }}</strong>
                </div>
                <div>
                    <span>Balance</span>
                    <strong>{{ number_format($user->balance) }} MMK</strong>
                </div>
                <div>
                    <span>Recovery hint</span>
                    <strong>{{ $user->recovery_hint ?: '-' }}</strong>
                </div>
                <div>
                    <span>Joined</span>
                    <strong>{{ $user->created_at->format('M d, Y') }}</strong>
                </div>
            </div>

            <nav class="user-detail-tabs" aria-label="User detail sections">
                <a href="{{ route('admin.users.transactions', $user->id) }}"><i class="fas fa-coins"></i> Top Up</a>
                <a href="{{ route('admin.users.withdraws', $user->id) }}"><i class="fas fa-arrow-right"></i> Withdraws</a>
                <a href="{{ route('admin.users.vouchers', $user->id) }}"><i class="fas fa-ticket-alt"></i> Vouchers</a>
                <a href="{{ route('admin.users.wallet-histories', $user->id) }}"><i class="fas fa-wallet"></i> Wallet</a>
                <a class="active" href="{{ route('admin.users.setting', $user->id) }}"><i class="fas fa-user-cog"></i> Setting</a>
            </nav>
        </section>

        <section class="panel glass user-bonus-panel" aria-labelledby="bonus-heading">
            <div class="panel-heading">
                <div><p class="eyebrow">WALLET CREDIT</p><h2 id="bonus-heading">Pay bonus</h2>
                    <p class="panel-subtitle">Credit a bonus directly to this user’s wallet.</p></div>
                <a class="btn secondary" href="{{ route('admin.users.wallet-histories', $user->id) }}">View wallet history</a>
            </div>
            <form id="pay-bonus-form" class="settings-form" action="{{ route('admin.users.bonus.store', $user->id) }}" method="POST">
                @csrf
                <input type="hidden" name="bonus_request_id" value="{{ old('bonus_request_id', (string) \Illuminate\Support\Str::uuid()) }}">
                <div class="settings-form-grid compact">
                    <label class="form-field"><span>Bonus amount (MMK)</span>
                        <input id="bonus-amount" name="bonus_amount" type="number" min="1" max="2147483647" step="1" inputmode="numeric" value="{{ old('bonus_amount') }}" placeholder="e.g. 5000" required>
                    </label>
                    <label class="form-field"><span>Bonus note (optional)</span>
                        <input id="bonus-note" name="bonus_note" maxlength="255" value="{{ old('bonus_note') }}" placeholder="e.g. Welcome bonus">
                    </label>
                </div>
                <p class="muted">The amount is added immediately and recorded as Bonus in wallet history.</p>
                <div class="settings-form-actions"><button id="review-bonus" class="btn primary" type="submit"><i class="fas fa-gift" aria-hidden="true"></i> Pay bonus</button></div>
            </form>
        </section>

        <section class="admin-grid">
            <article class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">SECURITY</p>
                        <h2>Password reset</h2>
                        <p class="panel-subtitle">Set a new password for this user account.</p>
                    </div>
                </div>

                <form class="settings-form" action="{{ route('admin.users.password-reset', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="settings-form-grid compact">
                        <label class="form-field">
                            <span>New password</span>
                            <input type="text" placeholder="Enter new password" name="password">
                            @error('password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </label>
                    </div>

                    <div class="settings-form-actions">
                        <button class="btn primary" type="submit">
                            <i class="fas fa-key"></i>
                            Reset password
                        </button>
                    </div>
                </form>
            </article>

            <article class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">ACCOUNT ACCESS</p>
                        <h2>{{ $user->disable ? 'Activate user' : 'Disable user' }}</h2>
                        <p class="panel-subtitle">
                            {{ $user->disable ? 'Restore access for this account.' : 'Temporarily block this user from signing in.' }}
                        </p>
                    </div>
                </div>

                @if ($user->disable == 0)
                    <div class="info-block warning">
                        <h3>Disable account access</h3>
                        <p>The user will not be able to access the account until an admin activates it again.</p>
                    </div>
                    <form class="settings-form" action="{{ route('admin.users.disable', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="settings-form-actions">
                            <button class="btn danger" type="submit">
                                <i class="fas fa-ban"></i>
                                Disable now
                            </button>
                        </div>
                    </form>
                @else
                    <div class="info-block danger">
                        <h3>Account is disabled</h3>
                        <p>Activate this user when account access should be restored.</p>
                    </div>
                    <form class="settings-form" action="{{ route('admin.users.activate', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="settings-form-actions">
                            <button class="btn primary" type="submit">
                                <i class="fas fa-check"></i>
                                Activate user
                            </button>
                        </div>
                    </form>
                @endif
            </article>
        </section>
    </div>

    <div class="modal fade" id="pay-bonus-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="pay-bonus-title" aria-describedby="pay-bonus-description">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="pay-bonus-title">Confirm bonus payment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">&times;</button></div>
            <div class="modal-body">
                <p id="pay-bonus-description">Pay <strong id="bonus-review-amount"></strong> MMK to <strong>{{ $user->name }}</strong> ({{ $user->phone }})?</p>
                <p id="bonus-review-note" class="muted"></p>
                <p class="mb-0">This will immediately increase the user’s wallet balance.</p>
            </div>
            <div class="modal-footer"><button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                <button id="confirm-bonus" class="btn primary" type="button">Confirm payment</button></div>
        </div></div>
    </div>
    <style>
        .admin-main:has(.user-setting-page) { background: var(--color-bg) !important; }
        .user-bonus-panel { margin-bottom: 14px; background: var(--color-surface); }
        #pay-bonus-modal .close { color: var(--color-text); text-shadow: none; }
        .user-bonus-panel .settings-form-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        @media (max-width: 760px) { .user-bonus-panel .settings-form-grid { grid-template-columns: minmax(0, 1fr); } .user-bonus-panel .btn, #pay-bonus-modal .btn { min-height: 44px; } }
    </style>
@endsection

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('pay-bonus-form');
        var confirmed = false;
        var sending = false;
        form.addEventListener('submit', function (event) {
            if (sending) { event.preventDefault(); return; }
            if (!confirmed) {
                event.preventDefault();
                document.getElementById('bonus-review-amount').textContent = Number(document.getElementById('bonus-amount').value).toLocaleString('en-US');
                var note = document.getElementById('bonus-note').value.trim();
                document.getElementById('bonus-review-note').textContent = note ? 'Note: ' + note : '';
                $('#pay-bonus-modal').modal('show');
                return;
            }
            sending = true;
            document.getElementById('review-bonus').disabled = true;
            document.getElementById('confirm-bonus').disabled = true;
            document.getElementById('confirm-bonus').textContent = 'Paying…';
        });
        document.getElementById('confirm-bonus').addEventListener('click', function () {
            confirmed = true;
            form.requestSubmit();
        });
        $('#pay-bonus-modal').on('hidden.bs.modal', function () { confirmed = false; });
        window.addEventListener('pageshow', function (event) { if (event.persisted) window.location.reload(); });
    })();
</script>
@endpush
