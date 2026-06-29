@php
    $drawHour = $voucher->clock->hour > 9 ? $voucher->clock->hour : '0' . $voucher->clock->hour;
    $drawMinute = $voucher->clock->minute > 9 ? $voucher->clock->minute : '0' . $voucher->clock->minute;
    $drawLabel = $drawHour . ':' . $drawMinute . ' ' . ($voucher->clock->morning == 1 ? 'AM' : 'PM');

    $payAmount = null;
    if ($voucher->win == 1) {
        $payAmount = $voucher->win_amount > 0 ? $voucher->win_amount : $voucher->amount * $voucher->lottery_type->coefficient;
    }

    if ($voucher->win == 1) {
        $statusClass = 'status-success';
        $statusLabel = 'Win';
    } elseif ($voucher->verified == 1) {
        $statusClass = $voucher->bonus_win == 1 ? 'status-warning' : 'status-danger';
        $statusLabel = $voucher->bonus_win == 1 ? 'Bonus fail' : 'Fail';
    } else {
        $statusClass = 'status-neutral';
        $statusLabel = 'Waiting';
    }
@endphp

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
                <p class="eyebrow">VOUCHER OPERATIONS</p>
                <h1>Voucher detail</h1>
            </div>
            <a class="btn secondary" href="{{ url()->previous() }}">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">VOUCHER</p>
                    <h2><span class="lottery-number">{{ $voucher->number }}</span> <span class="clock-pill">{{ $drawLabel }}</span></h2>
                    <p class="panel-subtitle">{{ $voucher->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <span class="status {{ $statusClass }}"><span class="status-dot"></span>{{ $statusLabel }}</span>
            </div>

            <div class="user-detail-grid">
                <div>
                    <span>User</span>
                    <strong>{{ $voucher->user->name }}</strong>
                </div>
                <div>
                    <span>Phone</span>
                    <strong>{{ $voucher->user->phone ?: '-' }}</strong>
                </div>
                <div>
                    <span>Lottery type</span>
                    <strong>{{ $voucher->lottery_type->type }}</strong>
                </div>
                <div>
                    <span>Bet amount</span>
                    <strong>{{ number_format($voucher->amount) }} MMK</strong>
                </div>
                <div>
                    <span>Pay amount</span>
                    <strong>{{ $payAmount ? number_format($payAmount) . ' MMK' : '-' }}</strong>
                </div>
                <div>
                    <span>Verified by</span>
                    <strong>
                        @if ($voucher->verified_by != 0)
                            {{ $voucher->verified_by($voucher->verified_by)->name }}
                        @else
                            -
                        @endif
                    </strong>
                </div>
            </div>
        </section>

        @if ($voucher->win == 0)
            <section class="panel glass release-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">MANUAL WIN</p>
                        <h2>Confirm winning payout</h2>
                        <p class="panel-subtitle">Use this only when this voucher should be marked as a winner.</p>
                    </div>
                </div>

                <div class="release-warning">
                    <span><i class="fas fa-exclamation-triangle"></i></span>
                    <div>
                        <strong>This action cannot be undone</strong>
                        <p>The payout will be added to the user's wallet and this voucher will be marked as paid.</p>
                    </div>
                </div>

                <form class="settings-form release-form" action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="settings-form-grid compact">
                        <label class="form-field">
                            <span>Win amount</span>
                            <input id="input_amount" type="text" name="amount" inputmode="numeric"
                                value="{{ old('amount', $voucher->amount * 10) }}">
                            @error('amount')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </label>
                    </div>

                    <div class="settings-form-actions">
                        <button class="btn danger" type="submit">
                            <i class="fas fa-check"></i>
                            Confirm payout
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>
@endsection
