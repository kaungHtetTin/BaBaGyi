@php
    $lottery_type = $report->lottery_type;
    $clock = $report->clock;
    $lottery_hour = $clock->hour < 10 ? '0' . $clock->hour : $clock->hour;
    $lottery_minute = $clock->minute < 10 ? '0' . $clock->minute : $clock->minute;
    $drawLabel = $lottery_type->type . ' ' . $lottery_hour . ':' . $lottery_minute . ' ' . ($clock->morning == 1 ? 'AM' : 'PM');
    $total_amount = 0;
@endphp

<!DOCTYPE html>
<html lang="en">
@include('admin.components.head')
<body>
    <div class="app-root" data-theme="light">
        <main class="print-page">
            <div class="admin-page-heading">
                <div>
                    <p class="eyebrow">SAVED REPORT</p>
                    <h1>{{ $drawLabel }}</h1>
                    <p class="panel-subtitle">Reported on {{ $report->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <button id="btn_print" class="btn primary" type="button">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>

            <section class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">REPORT DETAILS</p>
                        <h2>Reported amounts</h2>
                        <p class="panel-subtitle">{{ number_format($report->report_details->count()) }} numbers in this report</p>
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
                        <tbody>
                            @forelse ($report->report_details as $detail)
                                @php
                                    $total_amount += $detail->amount;
                                @endphp
                                <tr>
                                    <td><span class="lottery-number">{{ $detail->number->number }}</span></td>
                                    <td><span class="money-cell">{{ number_format($detail->amount) }} MMK</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2"><span class="muted">No report detail found.</span></td>
                                </tr>
                            @endforelse
                            <tr class="report-total-row">
                                <td><strong>Total</strong></td>
                                <td><strong>{{ number_format($total_amount) }} MMK</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        $(document).ready(function () {
            $('#btn_print').click(function () {
                window.print();
            });
        });
    </script>
</body>
</html>
