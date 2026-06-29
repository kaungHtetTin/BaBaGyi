@php
    $activeTab = $activeTab ?? 'transactions';
@endphp

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
            <span>Email</span>
            <strong>{{ $user->email ?: '-' }}</strong>
        </div>
        <div>
            <span>Joined</span>
            <strong>{{ $user->created_at->format('M d, Y') }}</strong>
        </div>
    </div>

    <nav class="user-detail-tabs" aria-label="User detail sections">
        <a class="{{ $activeTab === 'transactions' ? 'active' : '' }}" href="{{ route('admin.users.transactions', $user->id) }}">
            <i class="fas fa-coins"></i> Top Up
        </a>
        <a class="{{ $activeTab === 'withdraws' ? 'active' : '' }}" href="{{ route('admin.users.withdraws', $user->id) }}">
            <i class="fas fa-arrow-right"></i> Withdraws
        </a>
        <a class="{{ $activeTab === 'vouchers' ? 'active' : '' }}" href="{{ route('admin.users.vouchers', $user->id) }}">
            <i class="fas fa-ticket-alt"></i> Vouchers
        </a>
        <a class="{{ $activeTab === 'wallet' ? 'active' : '' }}" href="{{ route('admin.users.wallet-histories', $user->id) }}">
            <i class="fas fa-wallet"></i> Wallet
        </a>
        <a class="{{ $activeTab === 'setting' ? 'active' : '' }}" href="{{ route('admin.users.setting', $user->id) }}">
            <i class="fas fa-user-cog"></i> Setting
        </a>
    </nav>
</section>
