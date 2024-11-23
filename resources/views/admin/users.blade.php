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
            <h1 class="h3 mb-0 text-gray-800">Users</h1>
        </div>

         <div>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name </th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th><a href="{{route('admin.users')}}?sort=balance">Balance</a></th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Balance</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($users as $user)
                            @if ($user->id != 2)
                                <tr>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->phone}}</td>
                                    <td>{{$user->balance}}</td>
                                    <td>{{$user->created_at->diffForHumans()}}</td>
                                    <th>
                                        <a class="btn btn-primary action-button" href="{{route('admin.users.transactions',$user->id)}}"> View</a>
                                    </th>
                                </tr>
                            @endif
                                 
                        @endforeach
                    </tbody>
                </table>
            </div>

             {{$users->links()}}
        </div>
       

    </div>
@endsection