@php
    $lottery_type = $report->lottery_type;
    $clock = $report->clock;
    $lottery_hour = $clock->hour<9 ? "0".$clock->hour: $clock->hour;
    $lottery_minute = $clock->minute<9 ? "0".$clock->minute: $clock->minute;
    $total_amount = 0;
@endphp

<!DOCTYPE html>
<html lang="en">
@include('admin.components.head')
<body>
    <style>
        thead{
            background:#4e73df;
            color:white;
        }
    </style>
    <div class="container">
        <br><br>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{$lottery_type->type}} {{"$lottery_hour:$lottery_minute"}} {{$clock->morning==1?"AM":"PM"}}</h1>
            <a id="btn_print" href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                    class="fas fa-download fa-sm text-white-50"></i> Print Now</a>
        </div>
        <h4 class="h4">
            Reported On {{$report->created_at->format('Y M, d H:i:s')}}
        </h4>


        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <th>Number</th>
                    <th>Amount</th>
                </thead>
                <tfoot>
                    <tr>
                        <th style=" background:rgb(13, 183, 87);color:white">Total</th>
                        <th style=" background:rgb(13, 183, 87);color:white" id="total"></th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($report->report_details as $detail)
                        @php
                            $total_amount += $detail->amount;
                        @endphp
                        <tr>
                            <td>{{$detail->number->number}}</td>
                            <td>{{$detail->amount}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        $(document).ready(()=>{
            $('#btn_print').click(()=>{
                window.print();
            });

            $('#total').html({{$total_amount}})

        });
    </script>
</body>
</html>