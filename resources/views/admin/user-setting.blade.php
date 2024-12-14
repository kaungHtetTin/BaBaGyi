@extends('admin.master')
@section('content')
    <style>
        .my-btn{
            margin:5px;
        }
        .my-error{
            color:red;
            margin-left:10px;
            font-size: 12px;
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
            <h1 class="h3 mb-0 text-gray-800">User Detail</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <h4>{{$user->name}}</h4>
                <table class="table" width="80%">
                    <tr>
                        <td>Email</td>
                        <td>{{$user->email}}</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>{{$user->phone}}</td>
                    </tr>
                    <tr>
                        <td>Balance</td>
                        <td>{{$user->balance}}</td>
                    </tr>
                    <tr>
                        <td>Password Recovery Hint</td>
                        <td>{{$user->recovery_hint}}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            @if ($user->disable)
                                <span style="color: red">Disable</span>
                            @else 
                                <span style="color:green"><b>Active</b></span>
                            @endif
                        </td>
                    </tr>
                </table>
                  
            </div>
        </div>

        <a href="{{route('admin.users.transactions',$user->id)}}" class="btn btn-secondary my-btn">Top Up</a>
        <a href="{{route('admin.users.withdraws',$user->id)}}" class="btn btn-secondary my-btn">Withdraws</a>
        <a href="{{route('admin.users.vouchers',$user->id)}}" class="btn btn-secondary my-btn">Vouchers</a>
        <a href="{{route('admin.users.wallet-histories',$user->id)}}" class="btn btn-secondary my-btn">Wallet Histories</a>
        <a href="{{route('admin.users.setting',$user->id)}}" class="btn btn-primary my-btn">Setting</a>
        <br><br>
        <h5>Setting</h5>
      
        <br>
        <h6>Password Reset</h6>
        <form class="user" action="{{route('admin.users.password-reset',$user->id)}}" method="POST" >
            @csrf
            @method("PUT")
            <div class="form-group">
                <input type="text" class="form-control form-control-user"
                    id="" aria-describedby="emailHelp"
                    placeholder="Enter new password" name="password">
                <p class="my-error"> {{$errors->first('password')}}</p>
            </div>

            <button class="btn btn-primary btn-user btn-block" style="width:100px;">
                Reset Now
            </button>
        </form>

        <br><hr><br>
        @if ($user->disable == 0)
            <h6>Disable User</h6>
            <div class="alert alert-warning">
                <strong>Warning: </strong> By disabling, the user will have no longer access to his account until the admin reactive it.
            </div>
            <form class="user" action="{{route('admin.users.disable',$user->id)}}" method="POST" >
                @csrf
                @method('PUT')
                <button class="btn btn-primary btn-user btn-block" style="width:150px;">
                    Disable Now
                </button>
            </form>
        @else
            <h6>Activate user</h6>
            <div class="alert alert-danger">
                This account was disable and do you want to reactivate the user now.
            </div>
            <form class="user" action="{{route('admin.users.activate',$user->id)}}" method="POST" >
                @csrf
                @method('PUT')
                <button class="btn btn-primary btn-user btn-block" style="width:150px;">
                    Activate
                </button>
            </form>
        @endif
    </div>
@endsection