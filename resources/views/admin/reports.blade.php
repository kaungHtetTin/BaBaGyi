@php
    $lottery_hour = $clock->hour < 9 ? '0' . $clock->hour : $clock->hour;
    $lottery_minute = $clock->minute < 9 ? '0' . $clock->minute : $clock->minute;
    $drawLabel = "{$lottery_type->type} {$lottery_hour}:{$lottery_minute} " . ($clock->morning == 1 ? 'AM' : 'PM');
    $total_amount = $numbers->sum('report');
    $reportCount = method_exists($reports, 'total') ? $reports->total() : count($reports);
    $isDraw = fn ($lotteryTypeId, $clockId) => (int) $lottery_type->id === (int) $lotteryTypeId && (int) $clock->id === (int) $clockId;
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
@endphp

@extends('admin.master')

@section('content')
    <div class="container-fluid">
        @if (session('msg'))
            <div class="alert alert-success">
                {{ session('msg') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">LOTTERY REPORTING</p>
                <h1>{{ $drawLabel }}</h1>
            </div>
            @if (count($numbers) > 0)
                <button id="btn_report" type="button" class="btn primary">
                    <i class="fas fa-download fa-sm"></i> Generate report
                </button>
            @endif
        </div>

        <nav class="report-draw-tabs" aria-label="Report draw navigation">
            @foreach ($report_draws as $draw)
                <a class="{{ $isDraw($draw->lottery_type_id, $draw->clock_id) ? 'active' : '' }}"
                    href="{{ route('admin.reports', ['lottery_type_id' => $draw->lottery_type_id, 'clock_id' => $draw->clock_id]) }}">
                    <i class="fas {{ $draw->lottery_type_id == 3 ? 'fa-dice-three' : 'fa-clock' }}"></i>
                    {{ $reportDrawLabel($draw) }}
                </a>
            @endforeach
        </nav>

        <form id="form_report" action="{{ route('admin.reports.save') }}" method="post">
            @csrf
            <input type="hidden" name="lottery_type_id" value="{{ $lottery_type->id }}">
            <input type="hidden" name="clock_id" value="{{ $clock->id }}">
        </form>

        <section class="report-metrics" aria-label="Report metrics">
            <article class="metric-card glass">
                <span><i class="fas fa-hashtag"></i></span>
                <small>Report numbers</small>
                <strong>{{ number_format(count($numbers)) }}</strong>
                <p>Ready for current draw</p>
            </article>
            <article class="metric-card glass">
                <span><i class="fas fa-coins"></i></span>
                <small>Report amount</small>
                <strong>{{ number_format($total_amount) }}</strong>
                <p>MMK current total</p>
            </article>
            <article class="metric-card glass">
                <span><i class="fas fa-history"></i></span>
                <small>Report history</small>
                <strong>{{ number_format($reportCount) }}</strong>
                <p>Saved reports</p>
            </article>
        </section>

        <section class="admin-grid">
            <article class="panel glass {{ count($numbers) > 0 ? '' : 'wide' }}">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">REPORT ARCHIVE</p>
                        <h2>Report history</h2>
                        <p class="panel-subtitle">{{ number_format($reportCount) }} generated reports</p>
                    </div>
                </div>

                @if (count($reports) <= 0)
                    <div class="info-block">
                        <span class="muted">No report yet.</span>
                    </div>
                @else
                    <div class="report-history-list">
                        @foreach ($reports as $report)
                            <a class="report-history-item" href="{{ route('admin.reports.detail', $report->id) }}">
                                <span>
                                    <strong>Report #{{ $report->id }}</strong>
                                    <small>{{ $report->created_at->format('Y M, d H:i:s') }}</small>
                                </span>
                                <i class="fas fa-chevron-right muted"></i>
                            </a>
                        @endforeach
                    </div>

                    {{ $reports->links() }}
                @endif
            </article>

            @if (count($numbers) > 0)
                <article class="panel glass">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">READY TO GENERATE</p>
                            <h2>Report now</h2>
                            <p class="panel-subtitle">{{ number_format(count($numbers)) }} numbers with demand</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="report-total-row">
                                    <th>Total</th>
                                    <th>{{ number_format($total_amount) }}</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach ($numbers as $number)
                                    <tr>
                                        <td><span class="lottery-number">{{ $number->number }}</span></td>
                                        <td><span class="money-cell">{{ number_format($number->report) }} MMK</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif
        </section>
    </div>

    <script>
        $(document).ready(() => {
            $('#btn_report').click(() => {
                $('#form_report').submit();
            });
        });
    </script>
@endsection
