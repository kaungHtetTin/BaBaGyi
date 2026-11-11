@php
    use App\Models\LotteryType;

    $interest = $total_transaction - $total_withdraw - $total_balance;
    $base = max($total_transaction, $total_withdraw, $total_balance, abs($interest), 1);
    $per_trx = $total_transaction * 100 / $base;
    $per_wdr = $total_withdraw * 100 / $base;
    $per_bal = $total_balance * 100 / $base;
    $per_intr = abs($interest) * 100 / $base;
@endphp

@extends('admin.master')

@section('content')
    <div class="container-fluid">
        <div class="admin-page-heading">
            <div>
                <p class="eyebrow">{{ date('D, M d Y') }}</p>
                <h1>Dashboard</h1>
            </div>
            <a href="{{ route('admin.reports') }}?lottery_type_id=2&clock_id=2" class="btn primary">
                <i class="fas fa-chart-line fa-sm"></i> View reports
            </a>
        </div>

        <section class="metrics-grid" aria-label="Dashboard metrics">
            <a class="metric-card glass" href="{{ route('admin.transactions') }}">
                <span><i class="fas fa-arrow-down"></i></span>
                <small>Top up requests</small>
                <strong id="trx_count">{{ $transaction_req }}</strong>
                <p>Pending verification</p>
            </a>

            <a class="metric-card glass" href="{{ route('admin.withdraws') }}">
                <span><i class="fas fa-arrow-right"></i></span>
                <small>Withdraw requests</small>
                <strong id="withdraw_count">{{ $withdraw_req }}</strong>
                <p>Pending payout</p>
            </a>

            <a class="metric-card glass" href="{{ route('admin.users') }}">
                <span><i class="fas fa-user"></i></span>
                <small>Total users</small>
                <strong>{{ number_format($total_user) }}</strong>
                <p>Registered accounts</p>
            </a>

            <a class="metric-card glass" href="{{ route('admin.users') }}?sort=balance">
                <span><i class="fas fa-wallet"></i></span>
                <small>Total balance</small>
                <strong>{{ number_format($total_balance) }}</strong>
                <p>MMK in wallets</p>
            </a>

            <article class="metric-card glass">
                <span><i class="fas fa-chart-line"></i></span>
                <small>Total earning</small>
                <strong>{{ number_format($interest) }}</strong>
                <p>MMK net position</p>
            </article>
        </section>

        @if (session('history_status'))
            <div class="alert alert-success" role="status">{{ session('history_status') }}</div>
        @endif
        @include('admin.components.history-edit-modal')

        <section class="admin-grid">
            <article class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">LIVE LOTTERY</p>
                        <h2>MM 2D</h2>
                    </div>
                    @if ($mm2d->release_mode == 0)
                        <a href="{{ route('admins.manual-release', '2d') }}" class="btn primary">Release</a>
                    @else
                        <span class="status status-success"><span class="status-dot"></span>Auto release</span>
                    @endif
                </div>

                <div class="panel-body-grid">
                    <div class="live-number">
                        <div>
                            <small id="2d_status" class="muted">--</small>
                            <strong id="2d_live_num">--</strong>
                        </div>
                        <span class="status {{ $mm2d->release_mode == 1 ? 'status-success' : 'status-danger' }}">
                            <span class="status-dot"></span>
                            {{ $mm2d->release_mode == 1 ? 'Auto' : 'Manual' }}
                        </span>
                    </div>

                    <div class="info-block warning">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h3 class="mb-0">Results <small class="muted">{{ $historyDate->format('d M Y') }}</small></h3>
                            <button type="button" class="icon-btn small" data-toggle="modal" data-target="#history-edit-modal"
                                data-history-url="{{ route('admin.lotteries.edit-day', array_filter([
                                    'type' => 2, 'date' => $historyDate->toDateString(), 'source' => 'dashboard', 'year' => request('year'),
                                ])) }}" aria-label="Edit MM 2D history" title="Edit history">
                                <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                            </button>
                        </div>
                        <table class="mini-table" aria-label="Thai 2D results">
                            <tr>
                                <td>09:30 AM</td>
                                <td><strong id="2d_result_num_0930">{{ $dailyHistory[2]['09:30']['number'] ?? '--' }}</strong></td>
                            </tr>
                            <tr>
                                <td>12:01 PM</td>
                                <td><strong id="2d_result_num_1">{{ $dailyHistory[2]['12:01']['number'] ?? '--' }}</strong></td>
                            </tr>
                            <tr>
                                <td>02:00 PM</td>
                                <td><strong id="2d_result_num_1400">{{ $dailyHistory[2]['14:00']['number'] ?? '--' }}</strong></td>
                            </tr>
                            <tr>
                                <td>04:30 PM</td>
                                <td><strong id="2d_result_num_2">{{ $dailyHistory[2]['16:30']['number'] ?? '--' }}</strong></td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-block success">
                        <h3>Lottery sale</h3>
                        <table class="mini-table">
                            <tr>
                                <td>12:01 PM</td>
                                <td><strong>{{ number_format($earning_2d_1201) }} MMK</strong></td>
                            </tr>
                            <tr>
                                <td>04:30 PM</td>
                                <td><strong>{{ number_format($earning_2d_1630) }} MMK</strong></td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-block danger">
                        <h3>Unexpected exposure</h3>
                        <div class="split-list">
                            <table class="mini-table">
                                <thead>
                                    <tr>
                                        <th>12:01 PM</th>
                                        <th>Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($unexpected_2d_1201_numbers as $number)
                                        <tr>
                                            <td>{{ $number->number }}</td>
                                            <td>{{ $number->amount }} x {{ LotteryType::find(2)->coefficient }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="muted">No exposure</td></tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <table class="mini-table">
                                <thead>
                                    <tr>
                                        <th>04:30 PM</th>
                                        <th>Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($unexpected_2d_1630_numbers as $number)
                                        <tr>
                                            <td>{{ $number->number }}</td>
                                            <td>{{ $number->amount }} x {{ LotteryType::find(2)->coefficient }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="muted">No exposure</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </article>

            <article class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">LIVE LOTTERY</p>
                        <h2>MM 3D</h2>
                    </div>
                    @if ($mm3d->release_mode == 0)
                        <a href="{{ route('admins.manual-release', '3d') }}" class="btn primary">Release</a>
                    @else
                        <span class="status status-success"><span class="status-dot"></span>Auto release</span>
                    @endif
                </div>

                <div class="panel-body-grid">
                    <div class="live-number">
                        <div>
                            <small id="3d_status" class="muted">On going</small>
                            <strong id="3d_live_num">--</strong>
                        </div>
                        <span class="status {{ $mm3d->release_mode == 1 ? 'status-success' : 'status-danger' }}">
                            <span class="status-dot"></span>
                            {{ $mm3d->release_mode == 1 ? 'Auto' : 'Manual' }}
                        </span>
                    </div>

                    <div class="info-block warning">
                        <h3>Results</h3>
                        <table class="mini-table">
                            @foreach ($monthly3DHistory as $dayNumber => $result)
                                @php($drawDate = $historyDate->startOfMonth()->day($dayNumber))
                                <tr>
                                    <td>{{ $drawDate->format('M j') }}, 03:30 PM</td>
                                    <td><strong id="3d_result_num_{{ $dayNumber === 1 ? 1 : 2 }}">{{ $result['number'] ?? '--' }}</strong></td>
                                    <td>
                                        <button type="button" class="icon-btn small" data-toggle="modal" data-target="#history-edit-modal"
                                            data-history-url="{{ route('admin.lotteries.edit-day', ['type' => 3, 'date' => $drawDate->toDateString(), 'source' => 'dashboard']) }}"
                                            aria-label="Edit MM 3D history for {{ $drawDate->format('d M Y') }}" title="Edit history"
                                            @disabled(! $result['due'])>
                                            <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>

                    <div class="info-block success">
                        <h3>Lottery sale</h3>
                        <table class="mini-table">
                            <tr>
                                <td>{{ date('M') }} 1</td>
                                <td><strong>{{ number_format($earning_3d_1) }} MMK</strong></td>
                            </tr>
                            <tr>
                                <td>{{ date('M') }} 16</td>
                                <td><strong>{{ number_format($earning_3d_16) }} MMK</strong></td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-block danger">
                        <h3>Unexpected exposure</h3>
                        <div class="split-list">
                            <table class="mini-table">
                                <thead>
                                    <tr>
                                        <th>{{ date('M') }} 1</th>
                                        <th>Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($unexpected_3d_1_numbers as $number)
                                        <tr>
                                            <td>{{ $number->number }}</td>
                                            <td>{{ $number->amount }} x {{ LotteryType::find(3)->coefficient }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="muted">No exposure</td></tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <table class="mini-table">
                                <thead>
                                    <tr>
                                        <th>{{ date('M') }} 16</th>
                                        <th>Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($unexpected_3d_16_numbers as $number)
                                        <tr>
                                            <td>{{ $number->number }}</td>
                                            <td>{{ $number->amount }} x {{ LotteryType::find(3)->coefficient }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="muted">No exposure</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </article>

            <article class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">REPORTING</p>
                        <h2>Monthly profit</h2>
                    </div>
                    <div class="dropdown no-arrow">
                        <button class="icon-btn small" type="button" id="yearMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="yearMenu">
                            <div class="dropdown-header">Select year</div>
                            @for ($i = date('Y'); $i >= 2024; $i--)
                                <a class="dropdown-item" href="?year={{ $i }}">{{ $i }}</a>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </article>

            <article class="panel glass">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">FINANCE</p>
                        <h2>Overview</h2>
                    </div>
                </div>

                <h4 class="small font-weight-bold">Total top up <span class="float-right">{{ number_format($total_transaction) }} MMK</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $per_trx }}%" aria-valuenow="{{ $per_trx }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <h4 class="small font-weight-bold">Total remaining balance <span class="float-right">{{ number_format($total_balance) }} MMK</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $per_bal }}%" aria-valuenow="{{ $per_bal }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <h4 class="small font-weight-bold">Total withdraw <span class="float-right">{{ number_format($total_withdraw) }} MMK</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar" role="progressbar" style="width: {{ $per_wdr }}%" aria-valuenow="{{ $per_wdr }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <h4 class="small font-weight-bold">Total earning <span class="float-right">{{ number_format($interest) }} MMK</span></h4>
                <div class="progress">
                    <div class="progress-bar bg-{{ $interest < 0 ? 'danger' : 'success' }}" role="progressbar" style="width: {{ $per_intr }}%" aria-valuenow="{{ $per_intr }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </article>
        </section>
    </div>

    <script>
        let thai2DHistoryDate = '';
        let thai2DHistoryLoading = false;

        $(document).ready(() => {
            fetchThai2DNumber();
            fetchThai2DSavedResults();
            fetchThai3DSavedResults();
            fetchThai3DNumber();
            setInterval(() => {
                fetchThai2DNumber();
                fetchThai2DSavedResults();
                fetchThai3DSavedResults();
                fetchThai3DNumber();
                notify();
            }, 5000);
        });

        function fetchThai2DSavedResults() {
            const date = @json($historyDate->toDateString());
            const parts = { year: date.slice(0, 4), month: date.slice(5, 7) };
            const targets = { '09:30': '#2d_result_num_0930', '12:01': '#2d_result_num_1', '14:00': '#2d_result_num_1400', '16:30': '#2d_result_num_2' };

            if (thai2DHistoryDate !== date) {
                thai2DHistoryDate = date;
                Object.values(targets).forEach(target => $(target).text('--'));
            }
            if (thai2DHistoryLoading) return;
            thai2DHistoryLoading = true;

            $.ajax({
                url: @json(url('/api/lotteries/records')),
                type: 'GET',
                dataType: 'json',
                timeout: 10000,
                data: { type: 2, year: parts.year, month: parts.month },
                success: function(records) {
                    if (thai2DHistoryDate !== date || !Array.isArray(records)) return;
                    Object.values(targets).forEach(target => $(target).text('--'));
                    records.forEach(record => {
                        if (record.date === date && targets[record.draw_time]
                            && typeof record.number === 'string' && /^[0-9]{2}$/.test(record.number)) {
                            $(targets[record.draw_time]).text(record.number);
                        }
                    });
                },
                error: function() {
                    console.error('Unable to refresh Thai 2D saved results.');
                },
                complete: function() {
                    thai2DHistoryLoading = false;
                }
            });
        }

        let thai3DHistoryLoading = false;
        function fetchThai3DSavedResults() {
            if (thai3DHistoryLoading) return;
            thai3DHistoryLoading = true;
            $.ajax({
                url: @json(url('/api/lotteries/records')),
                dataType: 'json', timeout: 10000,
                data: { type: 3, year: {{ $historyDate->year }}, month: {{ $historyDate->month }} },
                success: function(records) {
                    if (!Array.isArray(records)) return;
                    $('#3d_result_num_1, #3d_result_num_2').text('--');
                    records.forEach(record => {
                        const day = Number(record.date?.slice(-2));
                        if ([1, 16].includes(day) && record.draw_time === '15:30'
                            && typeof record.number === 'string' && /^[0-9]{3}$/.test(record.number)) {
                            $(day === 1 ? '#3d_result_num_1' : '#3d_result_num_2').text(record.number);
                        }
                    });
                },
                complete: function() { thai3DHistoryLoading = false; }
            });
        }

        function notify() {
            $.ajax({
                url: `{{ asset('') }}api/admin-notify`,
                type: 'GET',
                success: function(res) {
                    $('#withdraw_count').html(res.withdraw_req);
                    $('#trx_count').html(res.transaction_req);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                }
            });
        }

        function fetchThai2DNumber() {
            $.ajax({
                url: `{{ asset('') }}api/remote/thai-2d`,
                type: 'GET',
                success: function(res) {
                    res = JSON.parse(res);

                    if (res.live) {
                        let live = res.live;
                        $('#2d_status').html('Live');
                        $('#2d_live_num').html(live.twod);
                        $('#2d_status').css({'color': '#168255'});
                    } else {
                        $('#2d_status').html('Close');
                        $('#2d_status').css({'color': '#ce4444'});
                    }

                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                }
            });
        }

        function fetchThai3DNumber() {
            $.ajax({
                url: `{{ asset('') }}api/remote/thai-3d`,
                type: 'GET',
                success: function(res) {
                    $('#3d_live_num').html(res.number);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                }
            });
        }
    </script>

    <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>

    <script>
        let saleOfYear = @json($saleOfYear);
        let lossOfYear = @json($lossOfYear);

        Chart.defaults.global.defaultFontFamily = 'Inter, "Noto Sans Myanmar", system-ui, sans-serif';
        Chart.defaults.global.defaultFontColor = '#69768a';

        var dataSale = [];
        var dataLoss = [];
        var dataEar = [];

        for (var i = 0; i < 12; i++) {
            var month = i + 1;
            var trx = saleOfYear.filter(element => element.month == month);
            dataSale[i] = trx.length > 0 ? trx[0].amount : 0;

            var wdr = lossOfYear.filter(element => element.month == month);
            dataLoss[i] = wdr.length > 0 ? wdr[0].amount : 0;

            dataEar[i] = dataSale[i] - dataLoss[i];
        }

        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        var ctx = document.getElementById("myAreaChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [
                    {
                        label: "Sale",
                        lineTension: 0.3,
                        backgroundColor: "#2874bc10",
                        borderColor: "#2874bc",
                        pointRadius: 3,
                        pointBackgroundColor: "#2874bc",
                        pointBorderColor: "#2874bc",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "#2874bc",
                        pointHoverBorderColor: "#2874bc",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: dataSale,
                    },
                    {
                        label: "Win",
                        lineTension: 0.3,
                        backgroundColor: "#ce444410",
                        borderColor: "#ce4444",
                        pointRadius: 3,
                        pointBackgroundColor: "#ce4444",
                        pointBorderColor: "#ce4444",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "#ce4444",
                        pointHoverBorderColor: "#ce4444",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: dataLoss,
                    },
                    {
                        label: "Earning",
                        lineTension: 0.3,
                        backgroundColor: "#16825510",
                        borderColor: "#168255",
                        pointRadius: 3,
                        pointBackgroundColor: "#168255",
                        pointBorderColor: "#168255",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "#168255",
                        pointHoverBorderColor: "#168255",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: dataEar,
                    }
                ],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 18,
                        top: 18,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value) {
                                return 'MMK ' + number_format(value);
                            }
                        },
                        gridLines: {
                            color: "rgb(105 118 138 / 16%)",
                            zeroLineColor: "rgb(105 118 138 / 16%)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#172033",
                    titleMarginBottom: 10,
                    titleFontColor: '#69768a',
                    titleFontSize: 12,
                    borderColor: 'rgb(15 23 42 / 10%)',
                    borderWidth: 1,
                    xPadding: 12,
                    yPadding: 12,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': MMK ' + number_format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });
    </script>
@endsection
