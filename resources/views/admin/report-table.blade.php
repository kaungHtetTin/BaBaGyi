@php
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
                    <p class="eyebrow">LIVE DEMAND REPORT</p>
                    <h1>{{ $drawLabel }}</h1>
                </div>
                <button id="btn_print" class="btn primary" type="button">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>

            <section class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">NUMBER DEMAND</p>
                        <h2>Current demand amounts</h2>
                        <p class="panel-subtitle">{{ number_format($numbers->count()) }} numbers with demand</p>
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
                            @forelse ($numbers as $number)
                                @php
                                    $total_amount += $number->demand;
                                @endphp
                                <tr>
                                    <td><span class="lottery-number">{{ $number->number }}</span></td>
                                    <td><span class="money-cell">{{ number_format($number->demand) }} MMK</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2"><span class="muted">No demand found.</span></td>
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
