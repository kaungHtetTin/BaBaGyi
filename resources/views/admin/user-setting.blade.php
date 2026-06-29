@extends('admin.master')

@section('content')
    <div class="container-fluid">
        @if (session('msg'))
            <div class="alert alert-success">
                {{ session('msg') }}
            </div>
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
@endsection
