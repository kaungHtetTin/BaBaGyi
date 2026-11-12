@php
    $voucherRows = $vouchers->getCollection();
    $saleToday = $summary->sales;
    $paybackToday = $summary->payout;
    $pendingCount = $summary->pending;
    $winCount = $summary->winners;
    $advancedOpen = $errors->any() || !empty($filters['admin_id']) || isset($filters['amount_min']) || isset($filters['amount_max']) || $filters['sort'] !== 'queue' || $filters['per_page'] !== 50;
    $today = \Carbon\CarbonImmutable::now('Asia/Yangon')->toDateString();
    $datePresets = [
        'Today' => [$today, $today],
        'Yesterday' => [\Carbon\CarbonImmutable::now('Asia/Yangon')->subDay()->toDateString(), \Carbon\CarbonImmutable::now('Asia/Yangon')->subDay()->toDateString()],
        'Last 7 days' => [\Carbon\CarbonImmutable::now('Asia/Yangon')->subDays(6)->toDateString(), $today],
        'All dates' => [null, null],
    ];
    $chips = [];
    foreach (['q' => 'Search', 'clock_id' => 'Draw', 'status' => 'Status', 'date_from' => 'From', 'date_to' => 'To', 'number' => 'Number', 'admin_id' => 'Admin', 'amount_min' => 'Min amount', 'amount_max' => 'Max amount'] as $key => $label) {
        if (!isset($filters[$key]) || $filters[$key] === '') continue;
        $value = $filters[$key];
        if ($key === 'status') $value = $statuses[$value];
        if ($key === 'clock_id') {
            $selectedClock = $clocks->firstWhere('id', $value);
            $value = sprintf('%02d:%02d %s', $selectedClock->hour, $selectedClock->minute, $selectedClock->morning ? 'AM' : 'PM');
        }
        if ($key === 'admin_id') $value = $value === 'unassigned' ? 'Unassigned' : $admins->firstWhere('id', $value)?->name;
        if (in_array($key, ['amount_min', 'amount_max'])) $value = number_format($value).' MMK';
        $chips[$key] = $label.': '.$value;
    }
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

        return $voucher->payoutAmount();
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
            return $voucher->lottery_type_id == 3 && $voucher->bonus_win == 1 ? 'Bonus win' : 'Win';
        }

        if ($voucher->verified == 1) {
            return $voucher->bonus_win == 1 ? 'Bonus fail' : 'Fail';
        }

        return 'Waiting';
    };
@endphp

@extends('admin.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-vouchers.css') }}?v={{ filemtime(public_path('css/admin-vouchers.css')) }}">
@endpush

@section('content')
    <div class="container-fluid voucher-page">
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
                <small>Matching sales</small>
                <strong>{{ number_format($saleToday) }}</strong>
                <p>MMK across all matching vouchers</p>
            </article>
            <article class="metric-card glass">
                <span><i class="fas fa-coins"></i></span>
                <small>Winning payout</small>
                <strong>{{ number_format($paybackToday) }}</strong>
                <p>MMK paid + unpaid winning vouchers</p>
            </article>
            <article class="metric-card glass">
                <span><i class="fas fa-clock"></i></span>
                <small>Awaiting action</small>
                <strong>{{ number_format($pendingCount) }}</strong>
                <p>Unpaid wins and waiting vouchers</p>
            </article>
        </section>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">VOUCHER QUEUE</p>
                    <h2>{{ $pageTitle }}</h2>
                    <p class="panel-subtitle">{{ number_format($vouchers->total()) }} matching vouchers · {{ number_format($winCount) }} winners across all results</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Check your filters.</strong>
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form class="voucher-filters" method="GET" action="{{ request()->url() }}" aria-label="Voucher filters">
                <div class="voucher-filter-heading">
                    <strong><i class="fas fa-sliders-h" aria-hidden="true"></i> Filter vouchers</strong>
                    <span class="muted">Find a customer, number or draw</span>
                </div>
                <div class="voucher-filter-main">
                    <div class="voucher-filter-scroll" tabindex="0" aria-label="Scroll voucher filter fields">
                        <div class="voucher-filter-fields">
                            <label class="form-field voucher-filter-search" for="voucher-search">
                                <span>Customer / voucher</span>
                                <input id="voucher-search" type="search" name="q" value="{{ old('q', $filters['q']) }}" placeholder="Name, phone, email or voucher ID" maxlength="120">
                            </label>
                            <label class="form-field voucher-filter-number" for="voucher-number">
                                <span>Exact number</span>
                                <input id="voucher-number" name="number" inputmode="numeric" pattern="[0-9]{{ '{' }}{{ $type === 3 ? 3 : 2 }}{{ '}' }}" maxlength="{{ $type === 3 ? 3 : 2 }}" value="{{ old('number', $filters['number'] ?? '') }}" placeholder="{{ $type === 3 ? 'e.g. 007' : 'e.g. 07' }}">
                            </label>
                            <label class="form-field voucher-filter-select" for="voucher-clock">
                                <span>Draw time</span>
                                <select id="voucher-clock" name="clock_id">
                                    <option value="">All draws</option>
                                    @foreach ($clocks as $clock)
                                        <option value="{{ $clock->id }}" @selected((string) old('clock_id', $filters['clock_id'] ?? '') === (string) $clock->id)>{{ sprintf('%02d:%02d %s', $clock->hour, $clock->minute, $clock->morning ? 'AM' : 'PM') }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="form-field voucher-filter-status" for="voucher-status">
                                <span>Status</span>
                                <select id="voucher-status" name="status">
                                    <option value="">All statuses</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $filters['status'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="form-field voucher-filter-date" for="voucher-from">
                                <span>Purchased from</span>
                                <input id="voucher-from" type="date" name="date_from" value="{{ old('date_from', $filters['date_from'] ?? '') }}">
                            </label>
                            <label class="form-field voucher-filter-date" for="voucher-to">
                                <span>Purchased to</span>
                                <input id="voucher-to" type="date" name="date_to" value="{{ old('date_to', $filters['date_to'] ?? '') }}">
                            </label>
                        </div>
                    </div>
                    <div class="voucher-filter-actions">
                        <button class="btn primary" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Apply</button>
                        <a class="btn secondary" href="{{ request()->url() }}">Reset</a>
                    </div>
                </div>
                <div class="voucher-filter-tools">
                    <nav class="voucher-date-presets" aria-label="Purchase date shortcuts">
                        @foreach ($datePresets as $label => [$from, $to])
                            @php($selected = ($filters['date_from'] ?? null) === $from && ($filters['date_to'] ?? null) === $to)
                            <a class="{{ $selected ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['date_from' => $from, 'date_to' => $to, 'page' => null]) }}" @if($selected) aria-current="true" @endif>{{ $label }}</a>
                        @endforeach
                    </nav>
                    <span class="muted voucher-timezone">Purchase dates · Myanmar time</span>
                </div>
                <details class="voucher-more" @if($advancedOpen) open @endif>
                    <summary>More filters <span class="muted">Amount, admin &amp; sorting</span></summary>
                    <div class="voucher-advanced-fields">
                        <label class="form-field" for="voucher-amount-min">
                            <span>Min amount (MMK)</span>
                            <input id="voucher-amount-min" type="number" name="amount_min" min="0" step="any" inputmode="decimal" value="{{ old('amount_min', $filters['amount_min'] ?? '') }}" placeholder="No minimum">
                        </label>
                        <label class="form-field" for="voucher-amount-max">
                            <span>Max amount (MMK)</span>
                            <input id="voucher-amount-max" type="number" name="amount_max" min="0" step="any" inputmode="decimal" value="{{ old('amount_max', $filters['amount_max'] ?? '') }}" placeholder="No maximum">
                        </label>
                        <label class="form-field" for="voucher-admin">
                            <span>Reviewing admin</span>
                            <select id="voucher-admin" name="admin_id">
                                <option value="">All admins</option>
                                <option value="unassigned" @selected(old('admin_id', $filters['admin_id'] ?? '') === 'unassigned')>Unassigned</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" @selected((string) old('admin_id', $filters['admin_id'] ?? '') === (string) $admin->id)>{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field" for="voucher-sort">
                            <span>Sort by</span>
                            <select id="voucher-sort" name="sort">
                                @foreach(['queue' => 'Action queue', 'newest' => 'Newest first', 'oldest' => 'Oldest first', 'amount_desc' => 'Amount: high to low', 'amount_asc' => 'Amount: low to high'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('sort', $filters['sort']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field" for="voucher-page-size">
                            <span>Rows per page</span>
                            <select id="voucher-page-size" name="per_page">
                                @foreach([25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected((int) old('per_page', $filters['per_page']) === $size)>{{ $size }} rows</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </details>
            </form>

            <div class="voucher-result-bar" aria-label="Applied voucher filters">
                <p class="muted">Showing <strong>{{ $vouchers->firstItem() ?? 0 }}–{{ $vouchers->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($vouchers->total()) }}</strong></p>
                <div class="voucher-filter-chips">
                    @foreach($chips as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery([$key => null, 'page' => null]) }}" aria-label="Remove {{ $label }}">{{ $label }} <span aria-hidden="true">×</span></a>
                    @endforeach
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
                                        <strong>{{ $voucher->reviewer?->name ?? 'Former admin' }}</strong>
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
                                <td colspan="9" class="voucher-empty">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                    <strong>No vouchers match these filters</strong>
                                    <span class="muted">Try another customer, draw time or purchase date.</span>
                                    <a class="btn secondary" href="{{ request()->url() }}">Clear all filters</a>
                                </td>
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
