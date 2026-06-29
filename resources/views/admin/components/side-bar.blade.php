@php
    $isActive = fn (...$patterns) => request()->routeIs(...$patterns);
    $activeClass = fn (...$patterns) => $isActive(...$patterns) ? 'active' : '';
    $activeCurrent = fn (...$patterns) => $isActive(...$patterns) ? 'page' : null;
    $reportLotteryType = (string) request('lottery_type_id', '');
    $reportClock = (string) request('clock_id', '');
    $isReportParam = fn ($lotteryTypeId, $clockId) => $isActive('admin.reports') && $reportLotteryType === (string) $lotteryTypeId && $reportClock === (string) $clockId;
    $isNumberParam = fn ($lotteryTypeId, $clockId) => $isActive('admin.numbers') && $reportLotteryType === (string) $lotteryTypeId && $reportClock === (string) $clockId;
    $calendarType = (string) request('type', '2');
    $isCalendarParam = fn ($type) => $isActive('admin.lotteries.records') && $calendarType === (string) $type;
    $isVoucherGroup = $isActive('admin.vouchers.*');
    $isReportGroup = $isActive('admin.reports', 'admin.reports.*');
    $isNumberGroup = $isActive('admin.numbers', 'admin.numbers.*');
    $isCalendarGroup = $isActive('admin.lotteries.records');
    $lotteryDraws = \App\Models\LotteryClock::with(['lottery_type', 'clock'])
        ->whereIn('lottery_type_id', [2, 3])
        ->orderBy('lottery_type_id')
        ->orderBy('id')
        ->get();
    $defaultReportDraw = $lotteryDraws->first();
    $defaultReportUrl = $defaultReportDraw
        ? route('admin.reports', ['lottery_type_id' => $defaultReportDraw->lottery_type_id, 'clock_id' => $defaultReportDraw->clock_id])
        : route('admin.index');
    $defaultNumberUrl = $defaultReportDraw
        ? route('admin.numbers', ['lottery_type_id' => $defaultReportDraw->lottery_type_id, 'clock_id' => $defaultReportDraw->clock_id])
        : route('admin.index');
    $reportDrawLabel = function ($draw) {
        $type = str_replace('Thai', 'MM', $draw->lottery_type->type);

        if ((int) $draw->lottery_type_id === 3) {
            return $type;
        }

        $hour = $draw->clock->hour < 10 ? '0' . $draw->clock->hour : $draw->clock->hour;
        $minute = $draw->clock->minute < 10 ? '0' . $draw->clock->minute : $draw->clock->minute;
        $period = $draw->clock->morning == 1 ? 'AM' : 'PM';

        return "{$type} {$hour}:{$minute} {$period}";
    };

    $user = Auth::user();
    $nameParts = preg_split('/\s+/', trim($user->name ?? 'Admin'));
    $initials = strtoupper(substr($nameParts[0] ?? 'A', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
@endphp

<aside class="admin-sidebar glass" id="accordionSidebar">
    <a class="admin-brand" href="{{ route('admin.index') }}">
        <img src="{{ asset('img/ic_launcher.png') }}" alt="Ba Ba Gyi">
        <div>
            <strong>Ba Ba Gyi</strong>
            <small>Office admin</small>
        </div>
    </a>

    <nav aria-label="Admin navigation">
        <a class="nav-link {{ $activeClass('admin.index') }}" href="{{ route('admin.index') }}" @if($activeCurrent('admin.index')) aria-current="page" @endif>
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <p class="admin-nav-section">Financial</p>
        <a class="nav-link {{ $activeClass('admin.transactions', 'admin.transactions.*') }}" href="{{ route('admin.transactions') }}" @if($activeCurrent('admin.transactions', 'admin.transactions.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-arrow-down"></i>
            <span>Top Up</span>
        </a>
        <a class="nav-link {{ $activeClass('admin.withdraws', 'admin.withdraws.*') }}" href="{{ route('admin.withdraws') }}" @if($activeCurrent('admin.withdraws', 'admin.withdraws.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-arrow-right"></i>
            <span>Withdraw</span>
        </a>

        <p class="admin-nav-section">Lottery</p>
        <div class="admin-nav-group {{ $isVoucherGroup ? 'is-open' : '' }}" data-nav-group>
            <button class="nav-link nav-toggle" type="button" aria-expanded="{{ $isVoucherGroup ? 'true' : 'false' }}" aria-controls="voucher-nav">
                <i class="fas fa-fw fa-ticket-alt"></i>
                <span>Vouchers</span>
                <i class="fas fa-chevron-down nav-chevron"></i>
            </button>
            <div class="admin-subnav" id="voucher-nav" aria-label="Voucher shortcuts">
                <a class="{{ $isActive('admin.vouchers.thai-2d') ? 'active' : '' }}" href="{{ route('admin.vouchers.thai-2d') }}">
                    MM 2D Vouchers
                </a>
                <a class="{{ $isActive('admin.vouchers.thai-3d') ? 'active' : '' }}" href="{{ route('admin.vouchers.thai-3d') }}">
                    MM 3D Vouchers
                </a>
            </div>
        </div>

        <div class="admin-nav-group {{ $isReportGroup ? 'is-open' : '' }}" data-nav-group>
            <button class="nav-link nav-toggle" type="button" aria-expanded="{{ $isReportGroup ? 'true' : 'false' }}" aria-controls="report-nav">
                <i class="fas fa-fw fa-chart-line"></i>
                <span>Reports</span>
                <i class="fas fa-chevron-down nav-chevron"></i>
            </button>
            <div class="admin-subnav" id="report-nav" aria-label="Report shortcuts">
                @foreach ($lotteryDraws as $draw)
                    <a class="{{ $isReportParam($draw->lottery_type_id, $draw->clock_id) ? 'active' : '' }}"
                        href="{{ route('admin.reports', ['lottery_type_id' => $draw->lottery_type_id, 'clock_id' => $draw->clock_id]) }}">
                        {{ $reportDrawLabel($draw) }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="admin-nav-group {{ $isNumberGroup ? 'is-open' : '' }}" data-nav-group>
            <button class="nav-link nav-toggle" type="button" aria-expanded="{{ $isNumberGroup ? 'true' : 'false' }}" aria-controls="number-nav">
                <i class="fas fa-fw fa-sliders-h"></i>
                <span>Number Setting</span>
                <i class="fas fa-chevron-down nav-chevron"></i>
            </button>
            <div class="admin-subnav" id="number-nav" aria-label="Number setting shortcuts">
                @foreach ($lotteryDraws as $draw)
                    <a class="{{ $isNumberParam($draw->lottery_type_id, $draw->clock_id) ? 'active' : '' }}"
                        href="{{ route('admin.numbers', ['lottery_type_id' => $draw->lottery_type_id, 'clock_id' => $draw->clock_id]) }}">
                        {{ $reportDrawLabel($draw) }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="admin-nav-group {{ $isCalendarGroup ? 'is-open' : '' }}" data-nav-group>
            <button class="nav-link nav-toggle" type="button" aria-expanded="{{ $isCalendarGroup ? 'true' : 'false' }}" aria-controls="calendar-nav">
                <i class="fas fa-fw fa-calendar-alt"></i>
                <span>Calendar</span>
                <i class="fas fa-chevron-down nav-chevron"></i>
            </button>
            <div class="admin-subnav" id="calendar-nav" aria-label="Calendar shortcuts">
                <a class="{{ $isCalendarParam(2) ? 'active' : '' }}" href="{{ route('admin.lotteries.records', ['type' => 2]) }}">
                    MM 2D
                </a>
                <a class="{{ $isCalendarParam(3) ? 'active' : '' }}" href="{{ route('admin.lotteries.records', ['type' => 3]) }}">
                    MM 3D
                </a>
            </div>
        </div>
        <a class="nav-link {{ $activeClass('admin.lottery-types', 'admin.lottery-types.*') }}" href="{{ route('admin.lottery-types') }}" @if($activeCurrent('admin.lottery-types', 'admin.lottery-types.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-cog"></i>
            <span>Lottery Setting</span>
        </a>

        <p class="admin-nav-section">People</p>
        <a class="nav-link {{ $activeClass('admin.users', 'admin.users.*') }}" href="{{ route('admin.users') }}" @if($activeCurrent('admin.users', 'admin.users.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-user"></i>
            <span>Users</span>
        </a>
        <a class="nav-link {{ $activeClass('admin.admins', 'admin.admins.*') }}" href="{{ route('admin.admins') }}" @if($activeCurrent('admin.admins', 'admin.admins.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-user-shield"></i>
            <span>Admins</span>
        </a>

        <p class="admin-nav-section">Settings</p>
        <a class="nav-link {{ $activeClass('admin.payment-methods', 'admin.payment-methods.*') }}" href="{{ route('admin.payment-methods') }}" @if($activeCurrent('admin.payment-methods', 'admin.payment-methods.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-wallet"></i>
            <span>Payment Methods</span>
        </a>
        <a class="nav-link {{ $activeClass('admins.mobile-versions', 'admins.mobile-versions.*') }}" href="{{ route('admins.mobile-versions') }}" @if($activeCurrent('admins.mobile-versions', 'admins.mobile-versions.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-mobile-alt"></i>
            <span>Mobile Versions</span>
        </a>
        <a class="nav-link {{ $activeClass('admin.contacts', 'admin.contacts.*', 'admin.notices.*', 'admin.ad-photo.*') }}" href="{{ route('admin.contacts') }}" @if($activeCurrent('admin.contacts', 'admin.contacts.*', 'admin.notices.*', 'admin.ad-photo.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-tools"></i>
            <span>Misc Setting</span>
        </a>
        <a class="nav-link {{ $activeClass('admins.holidays') }}" href="{{ route('admins.holidays') }}" @if($activeCurrent('admins.holidays')) aria-current="page" @endif>
            <i class="fas fa-fw fa-calendar-check"></i>
            <span>Holiday</span>
        </a>
    </nav>

    <div class="admin-profile">
        @if (!empty($user->avatar_url))
            <img src="{{ asset($user->avatar_url) }}" alt="{{ $user->name }}">
        @else
            <span>{{ $initials ?: 'A' }}</span>
        @endif
        <div>
            <strong>{{ $user->name }}</strong>
            <small>Office admin</small>
        </div>
    </div>

    <a class="nav-link mt-2" href="{{ route('logout') }}">
        <i class="fas fa-fw fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-nav-group]').forEach(function (group) {
            var toggle = group.querySelector('.nav-toggle');
            var subnav = group.querySelector('.admin-subnav');

            if (!toggle || !subnav) {
                return;
            }

            subnav.hidden = !group.classList.contains('is-open');

            toggle.addEventListener('click', function () {
                var isOpen = group.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                subnav.hidden = !isOpen;
            });
        });
    });
</script>
