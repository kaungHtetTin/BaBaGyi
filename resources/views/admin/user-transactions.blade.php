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
                                 <span style="color:green"><b>Active</b></span>
                            @endif
                        </td>
                    </tr>
                </table>
                  
            </div>
        </div>

        <a href="{{route('admin.users.transactions',$user->id)}}" class="btn btn-primary my-btn">Transactions</a>
        <a href="{{route('admin.users.withdraws',$user->id)}}" class="btn btn-secondary my-btn">Withdraws</a>
        <a href="{{route('admin.users.vouchers',$user->id)}}" class="btn btn-secondary my-btn">Vouchers</a>
        <a href="{{route('admin.users.wallet-histories',$user->id)}}" class="btn btn-secondary my-btn">Wallet Histories</a>
        <a href="{{route('admin.users.setting',$user->id)}}" class="btn btn-secondary my-btn">Setting</a>
        <br><br>
        <h5>Transactions</h5>
        @if (count($transactions)>0)
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th style="background: rgb(249, 255, 239)">Banking</th>
                            <th style="background: rgb(249, 255, 239)">Account Name</th>
                            <th style="background: rgb(249, 255, 239)">Phone</th>
                            <th style="background: rgb(249, 255, 239)">Trx Id</th>
                            <th>Admin</th>
                            <th>Status</th>
                            
                            
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th style="background: rgb(249, 255, 239)">Banking</th>
                            <th style="background: rgb(249, 255, 239)">Account Name</th>
                            <th style="background: rgb(249, 255, 239)">Phone</th>
                            <th style="background: rgb(249, 255, 239)">Trx Id</th>
                            <th>Admin</th>
                            <th>Status</th>
                                
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{$transaction->created_at->diffForHumans()}}</td>
                                <td>{{$transaction->amount}}</td>
                                <td  style="background: rgb(249, 255, 239);text-align:center">
                                    <img src="{{asset($transaction->payment_method->banking->icon_url)}}" alt="" style="width: 20px;border-radius:5px;">
                                    <div>{{$transaction->payment_method->banking->bank}}</div>
                                </td>
                                <td  style="background: rgb(249, 255, 239)">{{$transaction->payment_method->account_name}}</td>
                                <td  style="background: rgb(249, 255, 239)">{{$transaction->payment_method->method}}</td>
                                <td  style="background: rgb(249, 255, 239)">{{$transaction->bank_transaction_id}}</td>
                                <td>
                                    @if ($transaction->verified_by != 0)
                                        {{$transaction->verified_by($transaction->verified_by)->name}}
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->verified==1)
                                        <span style="color:green;"><i class="fas fa-check-circle fa-fw"></i> Verified</span>
                                    @else
                                        <a class="btn btn-primary action-button"href="#" data-toggle="modal" data-target="#approve-modal-{{$transaction->id}}"> Approve</a>
                                    @endif

                                </td>

                                <div class="modal fade" id="approve-modal-{{$transaction->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="">Approve Transaction</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="alert alert-success">
                                                Do you really want to approve?
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                <form action="{{route('admin.transactions.approve',$transaction->id)}}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button class="btn btn-primary">Approve</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{$transactions->links()}}
        @else
            <div style="padding: 20px; text-align:center">
                <br><br><br><br>
                <h6>No transaction</h6>
            </div>
        @endif

    </div>
@endsection