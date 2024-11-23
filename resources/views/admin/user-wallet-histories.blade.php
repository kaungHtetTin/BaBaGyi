@extends('admin.master')
@section('content')
    <style>
        .my-btn{
            margin:5px;
        }
        table tr td{
            font-size: 14px;
        }
    </style>
    <div class="container-fluid">
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
                        <td>Status</td>
                        <td>
                            @if ($user->disable)
                                <span style="color: red">Disable</span>
                            @else 
                                <span style="color:green">Active</span>
                            @endif
                        </td>
                    </tr>
                </table>
                  
            </div>
        </div>

        <a href="{{route('admin.users.transactions',$user->id)}}" class="btn btn-secondary my-btn">Transactions</a>
        <a href="{{route('admin.users.withdraws',$user->id)}}" class="btn btn-secondary my-btn">Withdraws</a>
        <a href="{{route('admin.users.vouchers',$user->id)}}" class="btn btn-secondary my-btn">Vouchers</a>
        <a href="{{route('admin.users.wallet-histories',$user->id)}}" class="btn btn-primary my-btn">Wallet History</a>
        <a href="{{route('admin.users.setting',$user->id)}}" class="btn btn-secondary my-btn">Setting</a>
        <br><br>
        <h5>Wallet History</h5>
        @if (count($wallet_histories)>0)
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Amount</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($wallet_histories as $history)
                            <tr>
                                <td>{{$history->created_at->diffForHumans()}}</td>
                                <td>{{$history->title}}</td>
                                <td>
                                    @if ($history->income == 0)
                                        <span style="color: red">{{$history->amount}}</span>
                                    @else 
                                        <span style="color:green">{{$history->amount}}</span>
                                    @endif
                                    
                                </td>
                            </tr> 
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{$wallet_histories->links()}}
        @else
            <div style="padding: 20px; text-align:center">
                <br><br><br><br>
                <h6>No wallet history yet</h6>
            </div>
        @endif

    </div>
@endsection