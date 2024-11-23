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
                        @if (Auth::user()->id == 1)
                            <div style="flex:1">
                                <a href="{{route('admin.register')}}" style="float: right;" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-plus fa-sm text-white-50"></i>Add New</a>
                            </div>
                        @endif
                   </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Action </th>
                                    
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Action </th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach ($admins as $admin)
                                    <tr>
                                        <td><img src="" alt="" style="width: 20px;border-radius:5px;"></td>
                                        <td>{{$admin->name}}</td>
                                        <td>{{$admin->email}}</td>
                                        <td>{{$admin->phone}}</td>
                                        <td>
                                            @if ($admin->disable == 0)
                                                <span class="text text-success" style="font-size:12px; font-weight:bold">Active</span>
                                            @else
                                               <span class="text text-danger" style="font-size:12px; font-weight:bold">Disable</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($admin->id != 1)
                                                <a class="btn btn-danger action-button"href="#" data-toggle="modal" data-target="#disable-modal-{{$admin->id}}"><i class="fas fa-trash fa-fw"></i> Disable</a>
                                            @endif
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="disable-modal-{{$admin->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="">Disable Admin Account</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                                <div class="alert alert-warning">
                                                    <strong>Warning: </strong>
                                                    This action will not completely remove the account. But this user will loose the admin full access.
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                    <form action="{{route('admin.admins.disable',$admin->id)}}" method="POST">
                                                        @csrf
                                                        <button class="btn btn-danger" href="{{route('logout')}}">Disable</button>
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
            </div>

        </div>
        <!-- /.container-fluid -->
@endsection