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
                        <th>Close Before(Minute)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Lottery Type</th>
                        <th>Multiplication</th>
                        <th>Close Before(Minute)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($lottery_types as $type)
                        <tr>
                            <td> {{$type->type}} </td>
                            <td>{{$type->coefficient}}</td>
                            <td>{{$type->close_before}}</td>
                            <td>
                                @if ($type->open == 1)
                                    <strong class="text-success">Open</strong>
                                @else
                                    <strong class="text-danger">Close</strong>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-primary action-button"href="{{route('admin.lottery-types.edit',$type->id)}}"> Edit</a>
                                @if ($type->open == 1)
                                    <a class="btn btn-danger action-button"href="#" data-toggle="modal" data-target="#close-modal-{{$type->id}}"> Close</a>
                                @else
                                    <a class="btn btn-success action-button"href="#" data-toggle="modal" data-target="#open-modal-{{$type->id}}"> Open</a>
                                @endif
                            </td>
                        </tr>

                        <div class="modal fade" id="open-modal-{{$type->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                                        <form action="{{route('admin.lottery-types.change-status',$type->id)}}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="open" value="1">
                                            <button class="btn btn-primary">Open Now</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="close-modal-{{$type->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                                        <form action="{{route('admin.lottery-types.change-status',$type->id)}}" method="POST">
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
@endsection