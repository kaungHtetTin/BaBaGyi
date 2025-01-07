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
        .release-mode{
            color:#777;
            cursor: pointer;
            padding:3px;
            text-decoration: none;
        }

        .release-mode-active{
            color:#1cc88a;
            cursor: pointer;
            padding:3px;
            text-decoration: none;
        }

        .release-mode-active:hover{
            background: #1cc88a;
            border-radius:7px;
            color: white;
            text-decoration: none;
        }
        
        .release-mode:hover{
            background: #1cc88a;
            border-radius:7px;
            color: white;
            text-decoration: none;
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
            <h1 class="h3 mb-0 text-gray-800">Lottery Types</h1>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Lottery Type</th>
                        <th>Multiplication</th>
                        <th>Clock</th>
                        <th>Close Before (Minute)</th>
                        <th>Release Mode</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Lottery Type</th>
                        <th>Multiplication</th>
                        <th>Clock</th>
                        <th>Close Before (Minute)</th>
                        <th>Release Mode</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($lotteries as $lottery)
                        @php
                            $clock = $lottery->clock;
                            $lottery_hour = $clock->hour<9 ? "0".$clock->hour: $clock->hour;
                            $lottery_minute = $clock->minute<9 ? "0".$clock->minute: $clock->minute;
                        @endphp
                        <tr>
                            <td> {{$lottery->lottery_type->type}} </td>
                            <td>{{$lottery->lottery_type->coefficient}}</td>
                            <td>
                                {{"$lottery_hour:$lottery_minute"}} {{$clock->morning==1?"AM":"PM"}}
                            </td>
                            <td>{{$lottery->close_before}}</td>
                            <td>
                                @if ($lottery->lottery_type->release_mode == 1)
                                    <a href="{{route('admin.lottery-types.release_auto',$lottery->lottery_type->id)}}" class="release-mode-active"><strong>Auto</strong></a> 
                                    | 
                                    <a href="{{route('admin.lottery-types.release_manual',$lottery->lottery_type->id)}}" class="release-mode"><strong>Manual</strong></a>
                                @else
                                    <a href="{{route('admin.lottery-types.release_auto',$lottery->lottery_type->id)}}" class="release-mode"><strong>Auto</strong></a> 
                                    | 
                                    <a href="{{route('admin.lottery-types.release_manual',$lottery->lottery_type->id)}}" class="release-mode-active"><strong>Manual</strong></a>
                                @endif
                                
                            </td>
                            <td>
                                @if ($lottery->lottery_type->open == 1)
                                    <strong class="text-success">Open</strong>
                                @else
                                    <strong class="text-danger">Close</strong>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-primary action-button"href="{{route('admin.lottery-types.edit',$lottery->id)}}"> Edit</a>
                                @if ($lottery->lottery_type->open == 1)
                                    <a class="btn btn-danger action-button"href="#" data-toggle="modal" data-target="#close-modal-{{$lottery->lottery_type->id}}"> Close</a>
                                @else
                                    <a class="btn btn-success action-button"href="#" data-toggle="modal" data-target="#open-modal-{{$lottery->lottery_type->id}}"> Open</a>
                                @endif
                            </td>
                        </tr>

                        <div class="modal fade" id="open-modal-{{$lottery->lottery_type->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="">Open Lottery</h5>
                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="alert alert-warning">
                                        Do you really want to open this lottery?
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                        <form action="{{route('admin.lottery-types.change-status',$lottery->lottery_type->id)}}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="open" value="1">
                                            <button class="btn btn-primary">Open Now</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="close-modal-{{$lottery->lottery_type->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="">Close Lottery</h5>
                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="alert alert-warning">
                                        Do you really want to close this lottery?
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                        <form action="{{route('admin.lottery-types.change-status',$lottery->lottery_type->id)}}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="open" value="0">
                                            <button class="btn btn-danger">Close Now</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $(document).ready(()=>{
            $('#auto_mode').click(()=>{
                alert('auto')
            })
            $('#manual_mode').click(()=>{
                alert('manual')
            })
        })
    </script>
@endsection