@php
    $lottery_hour = $clock->hour<9 ? "0".$clock->hour: $clock->hour;
    $lottery_minute = $clock->minute<9 ? "0".$clock->minute: $clock->minute;
    $total_amount = 0;
@endphp

@extends('admin.master')
@section('content')
    <style>
        .report a{
            color:#666;
            margin:0px;
        }

        .report:hover{
            background: #cdd8f6;
            color:white
        }

        
        li{
            padding:0px;
        }
    </style>
    <div class="container-fluid">
        @if (session('msg'))
            <div class="alert alert-success">
                {{session('msg')}}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{session('error')}}
            </div>
        @endif
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{$lottery_type->type}} {{"$lottery_hour:$lottery_minute"}} {{$clock->morning==1?"AM":"PM"}}</h1>
            @if (count($numbers)>0)
                <a href="#" id="btn_report" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                    class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
            @endif
            
        </div>

        <div>
            <form id="form_report" action="{{route('admin.reports.save')}}" method="post">
                @csrf
                <input type="hidden" name="lottery_type_id" value="{{$lottery_type->id}}">
                <input type="hidden" name="clock_id" value="{{$clock->id}}">
            </form>
        </div>

        <div class="row">
        @if (count($numbers)>0)
            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-6">
        @else
            <div class="col-12">
        @endif
                <h4>Report History</h4>
                @if (count($reports)<=0)
                    <div style="text-align: center">
                        <br><br><br>
                        No report yet!
                        <br><br><br>
                    </div>
                @endif
                <ul class="list-group navbar-nav ">
                    @foreach ($reports as $report)
                        <li class="list-group-item nav-item report">
                            <a class="nav-link " href="{{route('admin.reports.detail',$report->id)}}" style="text-decoration: none">
                                Id - {{$report->id}} <br>
                                Reported on {{$report->created_at->format('Y M, d H:i:s')}}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @if (count($numbers)>0)
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                    <h4>Report Now</h4>
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
                                @foreach ($numbers as $number)
                                    @php
                                        $total_amount += $number->report;
                                    @endphp
                                    <tr>
                                        <td>{{$number->number}}</td>
                                        <td>{{$number->report}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
             @endif
        </div>

    </div>

    <script>
        $(document).ready(()=>{

            $('#total').html({{$total_amount}})

            $('#btn_report').click(()=>{
                $('#form_report').submit();
            });

        });
    </script>
@endsection