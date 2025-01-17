@php
    use App\Models\LotteryType;
@endphp
@extends('admin.master')
@section('content')
    <style>
        .tbl-unexpected tr{
            font-size: 14px;
        }
    </style>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Transaction Requests -->
            <div class="col-xl-6 col-md-6 mb-4">
                <a href="{{route('admin.transactions')}}" style="text-decoration: none">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Top Up Requests</div>
                                    <div id="trx_count" class="h5 mb-0 font-weight-bold text-danger">{{$transaction_req}}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Withdraw Request -->
            <div class="col-xl-6 col-md-6 mb-4">
                <a href="{{route('admin.withdraws')}}" style="text-decoration: none">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Withdraw Requests</div>
                                    <div id="withdraw_count" class="h5 mb-0 font-weight-bold text-danger">{{$withdraw_req}}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-arrow-right fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- 2D Dashboard -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card shadow h-100 py-2">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <span style="font-weight: bold;font-size:20px;">
                                    MM 2D
                                </span>
                                @if ($mm2d->release_mode == 1)
                                    <span style="font-weight: bold;color:green;font-size:14px;">
                                        Auto Release
                                    </span>
                                @else
                                    <span style="font-weight: bold;color:red;font-size:14px;">
                                        Manual Release
                                    </span>
                                @endif
                            </div>
                            <div class="col-6">
                                @if ($mm2d->release_mode == 0)
                                    <a href="{{route('admins.manual-release','2d')}}" class="btn btn-primary" style="float: right">Release</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div id="2d_status" class="text-xs font-weight-bold text-uppercase mb-1">--</div>
                                <div id="2d_live_num" class="h1 mb-0 font-weight-bold text-gray-800">--</div>
                            </div>
                            <div class="col-auto">
                                {{date('D').", ".date('M')." ".date('d')}} <br>
                                {{date('Y')}}
                            </div>
                        </div>
                      
                        <div style="padding:10px; background:rgb(255, 255, 229);border-radius:7px;margin-top:10px;">
                            <table width="100%">
                                <tr>
                                    <td>12:01 PM</td>
                                    <td><h6 id="2d_result_num_1" class="font-weight-bold">--</h6></td>
                                </tr>
                                <tr>
                                    <td>04:30 PM</td>
                                    <td><h6 id="2d_result_num_2" class="font-weight-bold">--</h6></td>
                                </tr>
                            </table>
                        </div>

                        <div style="padding:10px; background:rgb(229, 255, 231);border-radius:7px;margin-top:10px;">
                            <h6>Lottery Sale</h6>
                            <hr>
                            <table width="100%">
                                <tr>
                                    <td>12:01 PM</td>
                                    <td><h6 class="font-weight-bold">{{$earning_2d_1201}} mmk</h6></td>
                                </tr>
                                <tr>
                                    <td>04:30 PM</td>
                                    <td><h6 class="font-weight-bold">{{$earning_2d_1630}} mmk</h6></td>
                                </tr>
                            </table>
                        </div>
                      
                        <div style="padding:10px; background:rgb(255, 229, 229);border-radius:7px;margin-top:10px;">
                            <h6>Unexpected</h6>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <table class="tbl-unexpected" width="100%">
                                        <tr>
                                            <th>12:01 PM</th>
                                            <th>Pay</th>
                                        </tr>
                                        @foreach ($unexpected_2d_1201_numbers as $number)
                                            <tr>
                                                <td>{{$number->number}}</td>
                                                <td>{{$number->amount}} x {{LotteryType::find(2)->coefficient}}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                                <div class="col-6">
                                    <table class="tbl-unexpected" width="100%">
                                        <tr>
                                            <th>04:30 PM</th>
                                            <th>Pay</th>
                                        </tr>
                                        @foreach ($unexpected_2d_1630_numbers as $number)
                                            <tr>
                                                <td>{{$number->number}}</td>
                                                <td>{{$number->amount}} x {{LotteryType::find(2)->coefficient}}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                      
                        
                    </div>
                </div>
            </div>

            <!-- 3D Dashboard -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card shadow h-100 py-2">
                    <div class="card-header">
                       <div class="row">
                            <div class="col-6">
                                <span style="font-weight: bold;font-size:20px;">
                                    MM 3D
                                </span>
                                @if ($mm3d->release_mode == 1)
                                    <span style="font-weight: bold;color:green;font-size:14px;">
                                        Auto Release
                                    </span>
                                @else
                                    <span style="font-weight: bold;color:red;font-size:14px;">
                                        Manual Release
                                    </span>
                                @endif
                            </div>
                            <div class="col-6">
                                @if ($mm3d->release_mode == 0)
                                    <a href="{{route('admins.manual-release','3d')}}" class="btn btn-primary" style="float: right">Release</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div id="3d_status" style="color: green" class="text-xs font-weight-bold text-uppercase mb-1"> On Going</div>
                                <div id="3d_live_num" class="h1 mb-0 font-weight-bold text-gray-800"></div>
                            </div>
                            <div class="col-auto">
                                {{date('D').", ".date('M')." ".date('d')}} <br>
                                {{date('Y')}}
                            </div>
                        </div>
                      
                        <div style="padding:10px; background:rgb(255, 255, 229);border-radius:7px;margin-top:10px;">
                            <table width="100%">
                                <tr>
                                    <td>{{date('M')}} 1, 04:30 PM</td>
                                    <td><h6 id="3d_result_num_1" class="font-weight-bold">Comming soon</h6></td>
                                </tr>
                                <tr>
                                    <td>{{date('M')}} 16, 04:30 PM</td>
                                    <td><h6 id="3d_result_num_2" class="font-weight-bold">Comming soon</h6></td>
                                </tr>
                            </table>
                        </div>

                        <div style="padding:10px; background:rgb(229, 255, 231);border-radius:7px;margin-top:10px;">
                            <h6>Lottery Sale</h6>
                            <hr>
                            <table width="100%">
                                <tr>
                                    <td>{{date('M')}} 1</td>
                                    <td><h6 class="font-weight-bold">{{$earning_3d_1}} mmk</h6></td>
                                </tr>
                                <tr>
                                    <td>{{date('M')}} 16</td>
                                    <td><h6 class="font-weight-bold">{{$earning_3d_16}} mmk</h6></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style="padding:10px; background:rgb(255, 229, 229);border-radius:7px;margin-top:10px;">
                            <h6>Unexpected</h6>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <table class="tbl-unexpected" width="100%">
                                        <tr>
                                            <th>{{date('M')}} 1</th>
                                            <th>Pay</th>
                                        </tr>
                                        @foreach ($unexpected_3d_1_numbers as $number)
                                            <tr>
                                                <td>{{$number->number}}</td>
                                                <td>{{$number->amount}} x {{LotteryType::find(3)->coefficient}}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                                <div class="col-6">
                                    <table class="tbl-unexpected" width="100%">
                                        <tr>
                                            <th>{{date('M')}} 16</th>
                                            <th>Pay</th>
                                        </tr>
                                        @foreach ($unexpected_3d_16_numbers as $number)
                                            <tr>
                                                <td>{{$number->number}}</td>
                                                <td>{{$number->amount}} x {{LotteryType::find(3)->coefficient}}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>

            <!-- Total User -->
            <div class="col-xl-6 col-md-6 mb-4">
                <a href="{{route('admin.users')}}" style="text-decoration: none">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total User</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{$total_user}}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Balance -->
            <div class="col-xl-6 col-md-6 mb-4">
                <a href="{{route('admin.users')}}?sort=balance" style="text-decoration: none">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Balance</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{$total_balance}} mmk</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Content Row -->

        <div class="row">
            <!-- Area Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div
                        class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Monthly Profit</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Select year:</div>
                                @php
                                    $year = date('Y');
                                @endphp
                                @for ($i = $year; $i >=2024; $i--)
                                    <a class="dropdown-item" href="?year={{$i}}">{{$i}}</a>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="myAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Financial Overview</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $interest = $total_transaction - $total_withdraw - $total_balance;
                            $base = $total_transaction > $total_balance ? $total_transaction : $total_balance;
                            $base = $base > $total_withdraw ? $base : $total_withdraw;
                            $base = $base > $interest ? $base : $interest;

                            if($base>0){
                                $per_trx = $total_transaction*100/$base; 
                                $per_wdr = $total_withdraw*100/$base;
                                $per_bal = $total_balance*100/$base;
                                $per_intr = $interest*100/$base;
                                if($per_intr<0) $per_intr = $per_intr*(-1);
                            }else{
                                $per_trx = $per_wdr = $per_bal = $per_intr = 0;
                            }

                        @endphp
                        <h4 class="small font-weight-bold">Total Top Up <span
                                class="float-right">{{$total_transaction}} mmk</span></h4>
                        <div class="progress mb-4">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{$per_trx}}%"
                                aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <h4 class="small font-weight-bold">Total Remaining Balance <span
                                class="float-right">{{$total_balance}} mmk</span></h4>
                        <div class="progress mb-4">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{$per_bal}}%"
                                aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <h4 class="small font-weight-bold">Total Withdraw <span
                                class="float-right">{{$total_withdraw}} mmk</span></h4>
                        <div class="progress mb-4">
                            <div class="progress-bar" role="progressbar" style="width: {{$per_wdr}}%"
                                aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                         
                        <h4 class="small font-weight-bold">Total Earning <span
                                class="float-right">{{$interest}} mmk</span></h4>
                        <div class="progress">
                            <div class="progress-bar bg-{{$interest<0?'danger':'success'}}" role="progressbar" style="width: {{$per_intr}}%"
                                aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(()=>{
            fetchThai2DNumber();
            fetchThai3DNumber();
            setInterval(() => {
                fetchThai2DNumber();
                fetchThai3DNumber();
                notify();
            }, 5000);
        });

        function notify(){
            $.ajax({
                url: `{{asset('')}}api/admin-notify`,
                type: 'GET',
                success: function(res) {
                    console.log('res', res);
                    $('#withdraw_count').html(res.withdraw_req);
                    $('#trx_count').html(res.transaction_req);

                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                }
            })
        }

        function fetchThai2DNumber(){
            $.ajax({
                url: `{{asset('')}}api/remote/thai-2d`,
                type: 'GET',
                success: function(res) {
                   
                    res = JSON.parse(res);
                  
                    if(res.live){
                        let live = res.live;
                        $('#2d_status').html('Live');
                        $('#2d_live_num').html(live.twod);
                        $('#2d_status').css({'color':'green'});
                    }else{
                        $('#2d_status').html('Close');
                        $('#2d_status').css({'color':'red'});
                    }

                    if(res.result){
                        let result = res.result;
                        result.map((history)=>{
                            if(history.open_time=="12:01:00"){
                                $('#2d_result_num_1').html(history.twod);
                            }
                            if(history.open_time=="16:30:00"){
                                $('#2d_result_num_2').html(history.twod)
                            }
                        });
                    }

                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                }
            })
        }

        function fetchThai3DNumber(){
            $.ajax({
                url: `{{asset('')}}api/remote/thai-3d`,
                type: 'GET',
                success: function(res) {
                    $('#3d_live_num').html(res.number);
                    if(res.history){
                        let history = res.history;
                     
                        if(history.first){
                            $('#3d_result_num_1').html(history.first.number)
                        }
                        if(history.second){
                            $('#3d_result_num_2').html(history.second.number)
                        }
                    }

                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                }
            })
        }

    </script>

    <script src="{{asset('vendor/chart.js/Chart.min.js')}}"></script>

    <script>

        let saleOfYear = @json($saleOfYear);
        let lossOfYear = @json($lossOfYear);
        // Set new default font family and font color to mimic Bootstrap's default styling
        Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        var dataSale=[];
        var dataLoss=[];
        var dataEar=[];

        for(var i=0;i<12;i++){
            var month=i+1;
            var trx=saleOfYear.filter(element => element.month==month);
            if(trx.length>0){
                dataSale[i]=trx[0].amount;
            }else{
                dataSale[i]=0;
            }
            
            var wdr=lossOfYear.filter(element => element.month==month);
            if(wdr.length>0){
                dataLoss[i]=wdr[0].amount;
            }else{
                dataLoss[i]=0;
            }

            dataEar[i] = dataSale[i]  - dataLoss[i];
            
        }

        function number_format(number, decimals, dec_point, thousands_sep) {
            // *     example: number_format(1234.56, 2, ',', ' ');
            // *     return: '1 234,56'
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
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
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

            // Area Chart Example
            var ctx = document.getElementById("myAreaChart");
            var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [
                    {
                        label: "Sale",
                        lineTension: 0.3,
                        backgroundColor: "#4e73df10",
                        borderColor: "#4e73df",
                        pointRadius: 3,
                        pointBackgroundColor: "#4e73df",
                        pointBorderColor: "#4e73df",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "#4e73df",
                        pointHoverBorderColor: "#4e73df",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: dataSale,
                    },
                    {
                        label: "Win",
                        lineTension: 0.3,
                        backgroundColor: "#e74a3b10",
                        borderColor: "#e74a3b",
                        pointRadius: 3,
                        pointBackgroundColor: "#e74a3b",
                        pointBorderColor: "#e74a3b",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "#e74a3b",
                        pointHoverBorderColor: "#e74a3b",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: dataLoss,
                    },
                    {
                        label: "Earning",
                        lineTension: 0.3,
                        backgroundColor: "#1cc88a10",
                        borderColor: "#1cc88a",
                        pointRadius: 3,
                        pointBackgroundColor: "#1cc88a",
                        pointBorderColor: "#1cc88a",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "#1cc88a",
                        pointHoverBorderColor: "#1cc88a",
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
                    right: 25,
                    top: 25,
                    bottom: 0
                }
                },
                scales: {
                xAxes: [{
                    time: {
                    unit: 'date'
                    },
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
                    // Include a dollar sign in the ticks
                    callback: function(value, index, values) {
                        return 'mmk ' + number_format(value);
                    }
                    },
                    gridLines: {
                    color: "rgb(234, 236, 244)",
                    zeroLineColor: "rgb(234, 236, 244)",
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
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                    var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                    return datasetLabel + ': mmk ' + number_format(tooltipItem.yLabel);
                    }
                }
                }
            }
        });

    </script>

@endsection