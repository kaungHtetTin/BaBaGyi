@php
    $voucherRows = $vouchers->getCollection();
    $saleToday = $earning_today ?? 0;
    $paybackToday = $give_Back_today ?? 0;
    $pendingCount = $voucherRows->where('verified', 0)->count();
    $winCount = $voucherRows->where('win', 1)->count();
    $pageTitle = match ($title) {
        '2D Vouchers' => 'MM 2D Vouchers',
        '3D Vouchers' => 'MM 3D Vouchers',
        default => $title,
    };

    $drawTime = function ($voucher) {
        if (!$voucher->clock) {
            return 'No clock';
        }

        $hour = $voucher->clock->hour > 9 ? $voucher->clock->hour : '0' . $voucher->clock->hour;
        $minute = $voucher->clock->minute > 9 ? $voucher->clock->minute : '0' . $voucher->clock->minute;
        $period = $voucher->clock->morning == 1 ? 'AM' : 'PM';

        return "{$hour}:{$minute} {$period}";
    };

    $payAmount = function ($voucher) {
        if ($voucher->win != 1) {
            return null;
        }

        if ($voucher->win_amount > 0) {
            return $voucher->win_amount;
        }

        return $voucher->amount * $voucher->lottery_type->coefficient;
    };

    $statusClass = function ($voucher) {
        if ($voucher->win == 1) {
            return 'status-success';
        }

        if ($voucher->verified == 1) {
            return $voucher->bonus_win == 1 ? 'status-warning' : 'status-danger';
        }

        return 'status-neutral';
    };

    $statusLabel = function ($voucher) {
        if ($voucher->win == 1) {
            return 'Win';
        }

        if ($voucher->verified == 1) {
            return $voucher->bonus_win == 1 ? 'Bonus fail' : 'Fail';
        }

        return 'Waiting';
    };
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
                <p class="eyebrow">LOTTERY OPERATIONS</p>
                <h1>{{ $pageTitle }}</h1>
            </div>
        </div>

        <section class="report-metrics" aria-label="Voucher metrics">
            <article class="metric-card glass">
                <span><i class="fas fa-ticket-alt"></i></span>
                <small>Lottery sale</small>
                <strong>{{ number_format($saleToday) }}</strong>
                <p>MMK for current draw</p>
            </article>
            <article class="metric-card glass">
                <span><i class="fas fa-coins"></i></span>
                <small>Lottery win</small>
                <strong>{{ number_format($paybackToday) }}</strong>
                <p>MMK payout exposure</p>
            </article>
            <article class="metric-card glass">
                <span><i class="fas fa-clock"></i></span>
                <small>Waiting</small>
                <strong>{{ number_format($pendingCount) }}</strong>
                <p>On this page</p>
            </article>
        </section>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">VOUCHER QUEUE</p>
                    <h2>{{ $pageTitle }}</h2>
                    <p class="panel-subtitle">{{ number_format($vouchers->total()) }} total vouchers · {{ number_format($winCount) }} winners on this page</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Clock</th>
                            <th>Number</th>
                            <th>Amount</th>
                            <th>Pay</th>
                            <th>Status</th>
                            <th>Admin</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vouchers as $voucher)
                            <tr>
                                <td>
                                    <strong class="table-primary-line">{{ $voucher->created_at->format('M d, Y') }}</strong>
                                    <small class="table-secondary-line">{{ $voucher->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <strong class="table-primary-line">{{ $voucher->user->name }}</strong>
                                    <small class="table-secondary-line">{{ $voucher->user->phone ?? 'No phone' }}</small>
                                </td>
                                <td><span class="clock-pill">{{ $drawTime($voucher) }}</span></td>
                                <td><span class="lottery-number">{{ $voucher->number }}</span></td>
                                <td><span class="money-cell">{{ number_format($voucher->amount) }} MMK</span></td>
                                <td>
                                    @if ($payAmount($voucher))
                                        <span class="money-cell">{{ number_format($payAmount($voucher)) }} MMK</span>
                                    @else
                                        <span class="muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status {{ $statusClass($voucher) }}">
                                        <span class="status-dot"></span>{{ $statusLabel($voucher) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($voucher->verified_by != 0)
                                        <strong>{{ $voucher->verified_by($voucher->verified_by)->name }}</strong>
                                    @else
                                        <span class="muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-actions">
                                        @if ($voucher->verified == 0)
                                            @if ($voucher->win == 1)
                                                <a class="btn primary" href="#" data-toggle="modal" data-target="#approve-modal-{{ $voucher->id }}">Paid</a>
                                            @else
                                                <a class="btn secondary" href="#" data-toggle="modal" data-target="#restore-modal-{{ $voucher->id }}">Restore</a>
                                            @endif
                                        @endif

                                        @if ($voucher->verified_by != 0)
                                            <span class="status status-success"><span class="status-dot"></span>Paid</span>
                                        @endif

                                        @if ($voucher->win == 0 && $voucher->verified == 1)
                                            <a class="btn secondary" href="{{ route('admin.vouchers.edit', $voucher->id) }}">Edit</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9"><span class="muted">No vouchers found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $vouchers->links() }}
        </section>
    </div>

    @foreach ($vouchers as $voucher)
        <div class="modal fade" id="approve-modal-{{ $voucher->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mark Voucher Paid</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Pay winning voucher <strong>{{ $voucher->number }}</strong> for <strong>{{ $voucher->user->name }}</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.vouchers.approve', $voucher->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button class="btn primary">Paid</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="restore-modal-{{ $voucher->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Restore Voucher</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Restore voucher <strong>{{ $voucher->number }}</strong> and refund <strong>{{ number_format($voucher->amount) }} MMK</strong> to the user's balance?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.vouchers.delete', $voucher->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn primary">Restore</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
