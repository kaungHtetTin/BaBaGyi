@extends('admin.master')
@section('content')
    <style>
        .action-button{
            padding:3px;
            font-size: 12px;
            margin:3px;
        }
    </style>
     <!-- Content Wrapper -->
        <!-- Begin Page Content -->
        <div class="container-fluid">
            <!-- DataTales Example -->
             @if (session('msg'))
                <div class="alert alert-success">
                    {{session('msg')}}
                </div>
            @endif
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                   <div style="display: flex">
                        <h6 style="flex:1" class="m-0 font-weight-bold text-primary">Payment Methods</h6>
                        <div style="flex:1">
                            <a href="{{route('admin.payment-methods.create')}}" style="float: right;" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                            class="fas fa-plus fa-sm text-white-50"></i>Add New</a>
                        </div>
                   </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Account Name</th>
                                    <th>Phone</th>
                                    <th>Banking</th>
                                    <th>Status</th>
                                    <th>Action </th>
                                    
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th>Account Name</th>
                                    <th>Phone</th>
                                    <th>Banking</th>
                                    <th>Status</th>
                                    <th>Action </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach ($payment_methods as $method)
                                    <tr>
                                        <td><img src="{{asset($method->banking->icon_url)}}" alt="" style="width: 20px;border-radius:5px;"></td>
                                        <td>{{$method->account_name}}</td>
                                        <td>{{$method->method}}</td>
                                        <td>{{$method->banking->bank}}</td>
                                        <td>
                                            @if ($method->disable == 0)
                                                <span class="text text-success" style="font-size:12px; font-weight:bold">Active</span>
                                            @else
                                               <span class="text text-danger" style="font-size:12px; font-weight:bold">Disable</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-primary action-button" href=""><i class="fas fa-bell fa-fw"></i> View </a>
                                            @if ($method->disable == 0)
                                                <a class="btn btn-danger action-button"href="#" data-toggle="modal" data-target="#disable-modal-{{$method->id}}"><i class="fas fa-trash fa-fw"></i> Disable</a>
                                            @else
                                                 <a class="btn btn-success action-button"href="#" data-toggle="modal" data-target="#activate-modal-{{$method->id}}"><i class="fas fa-check-circle fa-fw"></i> Activate</a>
                                            @endif
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="disable-modal-{{$method->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="">Disable Payment Methods</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                                <div class="alert alert-warning">
                                                    <strong>Warning: </strong>
                                                    This action will not completely remove the payment method. But users will no longer see this method.
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                    <form action="{{route('admin.payment-methods.disable')}}" method="POST">
                                                        @csrf
                                                         <input type="hidden" name="id" value="{{$method->id}}">
                                                        <button class="btn btn-danger" href="{{route('logout')}}">Disable</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="activate-modal-{{$method->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="">Activate Payment Methods</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                                <div class="alert alert-success">
                                                    Do you really want to activate this payment method now?
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                    <form action="{{route('admin.payment-methods.enable')}}" method="POST">
                                                        @csrf
                                                         <input type="hidden" name="id" value="{{$method->id}}">
                                                        <button class="btn btn-success"">Activate Now</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                         {{$payment_methods->links()}}
                    </div>
                </div>
            </div>

        </div>
        <!-- /.container-fluid -->
@endsection