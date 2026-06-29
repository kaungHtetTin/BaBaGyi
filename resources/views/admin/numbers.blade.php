@php
    $lottery_hour = $clock->hour < 9 ? '0' . $clock->hour : $clock->hour;
    $lottery_minute = $clock->minute < 9 ? '0' . $clock->minute : $clock->minute;
    $drawLabel = "{$lottery_type->type} {$lottery_hour}:{$lottery_minute} " . ($clock->morning == 1 ? 'AM' : 'PM');
    $isDraw = fn ($lotteryTypeId, $clockId) => (int) $lottery_type->id === (int) $lotteryTypeId && (int) $clock->id === (int) $clockId;
    $numberDrawLabel = function ($draw) {
        $type = str_replace('Thai', 'MM', $draw->lottery_type->type);

        if ((int) $draw->lottery_type_id === 3) {
            return $type;
        }

        $hour = $draw->clock->hour < 10 ? '0' . $draw->clock->hour : $draw->clock->hour;
        $minute = $draw->clock->minute < 10 ? '0' . $draw->clock->minute : $draw->clock->minute;
        $period = $draw->clock->morning == 1 ? 'AM' : 'PM';

        return "{$type} {$hour}:{$minute} {$period}";
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
                <p class="eyebrow">NUMBER CONTROL</p>
                <h1>{{ $drawLabel }}</h1>
            </div>
        </div>

        <nav class="report-draw-tabs" aria-label="Number draw navigation">
            @foreach ($number_draws as $draw)
                <a class="{{ $isDraw($draw->lottery_type_id, $draw->clock_id) ? 'active' : '' }}"
                    href="{{ route('admin.numbers', ['lottery_type_id' => $draw->lottery_type_id, 'clock_id' => $draw->clock_id]) }}">
                    <i class="fas {{ $draw->lottery_type_id == 3 ? 'fa-dice-three' : 'fa-clock' }}"></i>
                    {{ $numberDrawLabel($draw) }}
                </a>
            @endforeach
        </nav>

        <section class="panel glass number-grid-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">NUMBER GRID</p>
                    <h2>Sell limits and exposure</h2>
                    <p class="panel-subtitle">{{ number_format(count($numbers)) }} numbers for {{ $drawLabel }}</p>
                </div>
            </div>

            <div class="number-table-toolbar">
                <div class="number-tab-shell">
                    <nav class="number-toolbar-tabs" role="tablist" aria-label="Number bulk tools">
                        <button class="is-active" id="availability-tab" type="button" data-number-tab="availability-panel"
                            role="tab" aria-controls="availability-panel" aria-selected="true">
                            <i class="fas fa-toggle-on"></i>
                            <span>Availability</span>
                        </button>
                        <button id="sell-limits-tab" type="button" data-number-tab="sell-limits-panel" role="tab"
                            aria-controls="sell-limits-panel" aria-selected="false">
                            <i class="fas fa-coins"></i>
                            <span>Sell Limits</span>
                        </button>
                        <button id="hot-numbers-tab" type="button" data-number-tab="hot-numbers-panel" role="tab"
                            aria-controls="hot-numbers-panel" aria-selected="false">
                            <i class="fas fa-fire"></i>
                            <span>Hot Numbers</span>
                        </button>
                    </nav>

                    <div class="number-tab-content">
                        <section class="number-tab-panel is-active" id="availability-panel" role="tabpanel"
                            aria-labelledby="availability-tab">
                            <div class="number-command-heading">
                                <div>
                                    <h3>Availability</h3>
                                    <p>Block selected numbers, or toggle availability for the whole draw.</p>
                                </div>
                            </div>

                            <div class="number-tab-columns compact">
                                <form class="toolbar-form" action="{{ route('admin.numbers.disable-by-group') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="lottery_type_id" value="{{ $lottery_type->id }}">
                                    <input type="hidden" name="clock_id" value="{{ $clock->id }}">
                                    <label class="form-field mb-0">
                                        <span>Selected numbers</span>
                                        <input type="text" name="numbers" placeholder="11,12,13">
                                    </label>
                                    <button class="btn danger" type="submit">Disable</button>
                                </form>

                                <div class="toolbar-actions number-bulk-actions">
                                    <form action="{{ route('admin.numbers.disable-all') }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="lottery_type_id" value="{{ $lottery_type->id }}">
                                        <input type="hidden" name="clock_id" value="{{ $clock->id }}">
                                        <input type="hidden" name="disable" value="1">
                                        <button class="btn secondary" type="submit">Disable all</button>
                                    </form>
                                    <form action="{{ route('admin.numbers.disable-all') }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="lottery_type_id" value="{{ $lottery_type->id }}">
                                        <input type="hidden" name="clock_id" value="{{ $clock->id }}">
                                        <input type="hidden" name="disable" value="0">
                                        <button class="btn primary" type="submit">Activate all</button>
                                    </form>
                                </div>
                            </div>
                        </section>

                        <section class="number-tab-panel" id="sell-limits-panel" role="tabpanel"
                            aria-labelledby="sell-limits-tab" hidden>
                            <div class="number-command-heading">
                                <div>
                                    <h3>Sell Limits</h3>
                                    <p>Set one limit for every number, or apply a limit to a selected group.</p>
                                </div>
                            </div>

                            <div class="number-tab-columns">
                                <form class="toolbar-form" action="{{ route('admin.numbers.reset-sell') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="lottery_type_id" value="{{ $lottery_type->id }}">
                                    <input type="hidden" name="clock_id" value="{{ $clock->id }}">
                                    <label class="form-field mb-0">
                                        <span>All numbers amount</span>
                                        <input type="text" name="sell" placeholder="100000">
                                    </label>
                                    <button class="btn primary" type="submit">Apply all</button>
                                </form>

                                <form class="toolbar-form three" action="{{ route('admin.numbers.reset-sell-by-group') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="lottery_type_id" value="{{ $lottery_type->id }}">
                                    <input type="hidden" name="clock_id" value="{{ $clock->id }}">
                                    <label class="form-field mb-0">
                                        <span>Group amount</span>
                                        <input type="text" name="sell" placeholder="100000">
                                    </label>
                                    <label class="form-field mb-0">
                                        <span>Numbers</span>
                                        <input type="text" name="numbers" placeholder="11,12,13">
                                    </label>
                                    <button class="btn secondary" type="submit">Apply</button>
                                </form>
                            </div>
                        </section>

                        <section class="number-tab-panel" id="hot-numbers-panel" role="tabpanel"
                            aria-labelledby="hot-numbers-tab" hidden>
                            <div class="number-command-heading">
                                <div>
                                    <h3>Hot Numbers</h3>
                                    <p>Mark numbers that need special attention for this draw.</p>
                                </div>
                            </div>

                            @if ($lottery_type->id != 3)
                                <div class="toolbar-split-actions number-hot-actions">
                                    <form class="toolbar-form" action="{{ route('admins.hot-numbers.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="lottery_type_id" value="{{ $lottery_type->id }}">
                                        <input type="hidden" name="clock_id" value="{{ $clock->id }}">
                                        <label class="form-field mb-0">
                                            <span>Numbers</span>
                                            <input type="text" name="numbers" placeholder="1,2,3">
                                        </label>
                                        <button class="btn primary" type="submit">Add</button>
                                    </form>
                                    <form action="{{ route('admins.hot-numbers.destroy') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="lottery_type_id" value="{{ $lottery_type->id }}">
                                        <input type="hidden" name="clock_id" value="{{ $clock->id }}">
                                        <button class="btn danger" type="submit">Clear</button>
                                    </form>
                                </div>
                            @else
                                <p class="number-tab-note">Hot-number tools are only available for MM 2D draws.</p>
                            @endif
                        </section>
                    </div>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Sell Amt</th>
                            <th>Demand Amt</th>
                            <th>Report Amt</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($numbers as $number)
                            <tr>
                                <td><span class="lottery-number">{{ $number->number }}</span></td>
                                <td><span class="money-cell">{{ number_format($number->sell) }} MMK</span></td>
                                <td><span class="money-cell">{{ number_format($number->demand) }} MMK</span></td>
                                <td><span class="money-cell">{{ number_format($number->report) }} MMK</span></td>
                                <td>
                                    @if ($number->disable == 1)
                                        <span class="status status-danger"><span class="status-dot"></span>Disabled</span>
                                    @else
                                        <span class="status status-success"><span class="status-dot"></span>Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-actions dense-actions" aria-label="Number {{ $number->number }} actions">
                                        @if ($number->disable == 1)
                                            <a class="icon-btn small success" href="#" data-toggle="modal" data-target="#activate-modal-{{ $number->id }}"
                                                aria-label="Activate number {{ $number->number }}" title="Activate">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        @else
                                            <a class="icon-btn small danger" href="#" data-toggle="modal" data-target="#disable-modal-{{ $number->id }}"
                                                aria-label="Disable number {{ $number->number }}" title="Disable">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        @endif
                                        <a class="icon-btn small" href="{{ route('admin.numbers.edit', $number->id) }}"
                                            aria-label="Edit number {{ $number->number }}" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @foreach ($numbers as $number)
        <div class="modal fade" id="activate-modal-{{ $number->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Activate Lottery Number</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Activate number <strong>{{ $number->number }}</strong> for this draw?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.numbers.activate', $number->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button class="btn primary">Activate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="disable-modal-{{ $number->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Disable Lottery Number</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Disable number <strong>{{ $number->number }}</strong> for this draw?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.numbers.disable', $number->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button class="btn danger">Disable</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toolbar = document.querySelector('.number-table-toolbar');

            if (!toolbar) {
                return;
            }

            var tabs = toolbar.querySelectorAll('[data-number-tab]');
            var panels = toolbar.querySelectorAll('.number-tab-panel');

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var targetId = tab.getAttribute('data-number-tab');

                    tabs.forEach(function (item) {
                        var isActive = item === tab;
                        item.classList.toggle('is-active', isActive);
                        item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    });

                    panels.forEach(function (panel) {
                        var isTarget = panel.id === targetId;
                        panel.classList.toggle('is-active', isTarget);
                        panel.hidden = !isTarget;
                    });
                });
            });
        });
    </script>
@endsection
