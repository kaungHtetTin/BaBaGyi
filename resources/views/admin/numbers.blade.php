@php
    $lottery_hour = $clock->hour<9 ? "0".$clock->hour: $clock->hour;
    $lottery_minute = $clock->minute<9 ? "0".$clock->minute: $clock->minute;
@endphp
@extends('admin.master')
@section('content')
    <style>
        .action-button{
            padding:3px;
            font-size: 12px;
            margin:3px;
        }
        table tr td{
            font-size: 14px;
        }
    </style>
    <div class="container-fluid">
        @if (session('msg'))
            <div class="alert alert-success">
                {{session('msg')}}
            </div>
        @endif
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{$lottery_type->type}} {{"$lottery_hour:$lottery_minute"}} {{$clock->morning==1?"AM":"PM"}}</h1>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div>
                    <form class="user mb-3" action="{{route('admin.numbers.disable-by-group')}}" method="POST" >
                        @csrf
                        @method("PUT")
                        <div style="display: flex">
                            <input type="hidden" name="lottery_type_id" value="{{$lottery_type->id}}">
                            <input type="hidden" name="clock_id" value="{{$clock->id}}">
                            <input type="text" class="form-control" placeholder="Enter separated by comma (Eg. 11,12,13)" name="numbers" style="display:inline">
                            <button class="btn btn-danger" style="margin-left:5px;">Disable</button>
                        </div>
                    </form>
                </div>
                <div style="display:flex">
                    <div>
                        <form class="user mb-3" action="{{route('admin.numbers.disable-all')}}" method="POST" >
                            @csrf
                            @method("PUT")
                            <input type="hidden" name="lottery_type_id" value="{{$lottery_type->id}}">
                            <input type="hidden" name="clock_id" value="{{$clock->id}}">
                            <input type="hidden" name="disable" value="1">
                            <button class="btn btn-danger action-button">Disable All</button>
                        </form>
                    </div>

                    <div>
                        <form class="user mb-3" action="{{route('admin.numbers.disable-all')}}" method="POST" >
                            @csrf
                            @method("PUT")
                            <input type="hidden" name="lottery_type_id" value="{{$lottery_type->id}}">
                            <input type="hidden" name="clock_id" value="{{$clock->id}}">
                            <input type="hidden" name="disable" value="0">
                            <button class="btn btn-success action-button"  style="margin-left: 15px;">Activate All</button>
                        </form>
                    </div>

                </div>   
            </div>
            <div class="col-lg-6 col-md-6">
                <form class="user mb-3" action="{{route('admin.numbers.reset-sell')}}" method="POST" >
                    @csrf
                    @method("PUT")
                    <div style="display: flex">
                        <input type="hidden" name="lottery_type_id" value="{{$lottery_type->id}}">
                        <input type="hidden" name="clock_id" value="{{$clock->id}}">
                        <input type="text" class="form-control" placeholder="Sell amount" name="sell" style="display:inline">
                        <button class="btn btn-primary" style="margin-left:5px;width:150px;">Reset All</button>
                    </div>
                </form>

                <form class="user mb-3" action="{{route('admin.numbers.reset-sell-by-group')}}" method="POST" >
                    @csrf
                    @method("PUT")
                    <div style="display: flex">
                        <input type="hidden" name="lottery_type_id" value="{{$lottery_type->id}}">
                        <input type="hidden" name="clock_id" value="{{$clock->id}}">
                        <input type="text" class="form-control" placeholder="Sell amount" name="sell" style="display:inline;margin-right:7px;">
                        <input type="text" class="form-control" placeholder="Enter 11,12,13" name="numbers" style="display:inline">
                        <button class="btn btn-primary" style="margin-left:5px;">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div>
                    <h4 class="h4">Hot Numbers: 
                        @foreach ($hot_numbers as $number)
                            {{$number->number}}, 
                        @endforeach
                    </h4>
                </div>
                <div style="display: flex">
                    <form class="user mb-3" action="{{route('admins.hot-numbers.store')}}" method="POST" style="flex: 1">
                        @csrf
                        <div style="display: flex">
                            <input type="hidden" name="lottery_type_id" value="{{$lottery_type->id}}">
                            <input type="hidden" name="clock_id" value="{{$clock->id}}">
                            <input type="text" class="form-control" placeholder="Enter 1,2,3" name="numbers" style="display:inline">
                            <button class="btn btn-primary" style="margin-left:5px;">Add</button>
                        </div>
                    </form>
                    <form class="user mb-3" action="{{route('admins.hot-numbers.destroy')}}" method="POST" >
                        @csrf
                        @method('DELETE')
                        <div style="display: flex">
                            <input type="hidden" name="lottery_type_id" value="{{$lottery_type->id}}">
                            <input type="hidden" name="clock_id" value="{{$clock->id}}">
                            <button class="btn btn-danger" style="margin-left:5px;">Clear</button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
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
                <tfoot>
                    <tr>
                        <th>Number</th>
                        <th>Sell Amt</th>
                        <th>Demand Amt</th>
                        <th>Report Amt</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($numbers as $number)
                        <tr>
                            <td>{{$number->number}}</td>
                            <td>{{$number->sell}}</td>
                            <td>{{$number->demand}}</td>
                            <td>{{$number->report}}</td>
                            <td>
                                @if ($number->disable == 1)
                                    <span style="color:red">Disable</span>
                                @else
                                    <span style="color:green">Active</span>
                                @endif
                            </td>
                            <td>
                                @if ($number->disable == 1)
                                        <a class="btn btn-success action-button"href="#" data-toggle="modal" data-target="#activate-modal-{{$number->id}}"> Activate</a>
                                @else
                                    <a class="btn btn-warning action-button"href="#" data-toggle="modal" data-target="#disable-modal-{{$number->id}}"> Disable</a>
                                @endif

                                <a class="btn btn-primary action-button"href="{{route('admin.numbers.edit',$number->id)}}"> Edit</a>

                                <div class="modal fade" id="activate-modal-{{$number->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="">Activate Lottery Number</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="alert alert-warning">
                                                Do you really want to activate this number?
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                <form action="{{route('admin.numbers.activate',$number->id)}}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button class="btn btn-primary">Activate</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="disable-modal-{{$number->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="">Disable Lottery Number</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="alert alert-warning">
                                                Do you really want to disable this number?
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                <form action="{{route('admin.numbers.disable',$number->id)}}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button class="btn btn-danger">Disable</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection