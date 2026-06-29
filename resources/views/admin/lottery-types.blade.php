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
                <h1>Lottery Types</h1>
            </div>
        </div>

        <section class="panel glass">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">CONFIGURATION</p>
                    <h2>Draw schedule and release rules</h2>
                    <p class="panel-subtitle">{{ number_format($lotteries->count()) }} configured schedules</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Lottery</th>
                            <th>Multiplier</th>
                            <th>Clock</th>
                            <th>Close Before</th>
                            <th>Release Mode</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lotteries as $lottery)
                            @php
                                $clock = $lottery->clock;
                                $lotteryHour = $clock->hour < 10 ? '0' . $clock->hour : $clock->hour;
                                $lotteryMinute = $clock->minute < 10 ? '0' . $clock->minute : $clock->minute;
                                $isOpen = $lottery->lottery_type->open == 1;
                                $isAuto = $lottery->lottery_type->release_mode == 1;
                            @endphp
                            <tr>
                                <td>
                                    <strong class="table-primary-line">{{ $lottery->lottery_type->type }}</strong>
                                    <small class="table-secondary-line">Lottery type</small>
                                </td>
                                <td><span class="money-cell">{{ $lottery->lottery_type->coefficient }}x</span></td>
                                <td>
                                    <span class="clock-pill">{{ $lotteryHour }}:{{ $lotteryMinute }} {{ $clock->morning == 1 ? 'AM' : 'PM' }}</span>
                                </td>
                                <td>{{ number_format($lottery->close_before) }} min</td>
                                <td>
                                    <div class="release-toggle {{ $isAuto ? 'is-auto' : 'is-manual' }}" aria-label="{{ $lottery->lottery_type->type }} release mode">
                                        <a class="{{ $isAuto ? 'active' : '' }}" href="{{ route('admin.lottery-types.release_auto', $lottery->lottery_type->id) }}"
                                            aria-current="{{ $isAuto ? 'true' : 'false' }}">
                                            <i class="fas fa-sync-alt"></i>
                                            Auto
                                        </a>
                                        <a class="{{ !$isAuto ? 'active' : '' }}" href="{{ route('admin.lottery-types.release_manual', $lottery->lottery_type->id) }}"
                                            aria-current="{{ !$isAuto ? 'true' : 'false' }}">
                                            <i class="fas fa-hand-paper"></i>
                                            Manual
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    @if ($isOpen)
                                        <span class="status status-success"><span class="status-dot"></span>Open</span>
                                    @else
                                        <span class="status status-danger"><span class="status-dot"></span>Closed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-actions dense-actions" aria-label="{{ $lottery->lottery_type->type }} actions">
                                        <a class="icon-btn small" href="{{ route('admin.lottery-types.edit', $lottery->id) }}"
                                            aria-label="Edit {{ $lottery->lottery_type->type }}" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        @if ($isOpen)
                                            <a class="icon-btn small danger" href="#" data-toggle="modal" data-target="#close-modal-{{ $lottery->lottery_type->id }}"
                                                aria-label="Close {{ $lottery->lottery_type->type }}" title="Close">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                        @else
                                            <a class="icon-btn small success" href="#" data-toggle="modal" data-target="#open-modal-{{ $lottery->lottery_type->id }}"
                                                aria-label="Open {{ $lottery->lottery_type->type }}" title="Open">
                                                <i class="fas fa-lock-open"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"><span class="muted">No lottery type schedules found.</span></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @foreach ($lotteries as $lottery)
        <div class="modal fade" id="open-modal-{{ $lottery->lottery_type->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Open Lottery</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Open <strong>{{ $lottery->lottery_type->type }}</strong> for users?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.lottery-types.change-status', $lottery->lottery_type->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="open" value="1">
                            <button class="btn primary">Open Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="close-modal-{{ $lottery->lottery_type->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Close Lottery</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Close <strong>{{ $lottery->lottery_type->type }}</strong> for users?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn secondary" type="button" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.lottery-types.change-status', $lottery->lottery_type->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="open" value="0">
                            <button class="btn danger">Close Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
