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
            <h1 class="h3 mb-0 text-gray-800">Transactions</h1>
        </div>

        <div>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
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
                            <th>Name</th>
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
                                <td><a href="{{route('admin.users.transactions',$transaction->user_id)}}" style="text-decoration: none">{{$transaction->user->name}}</a></td>
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
                                        <a class="btn btn-danger action-button"href="#" data-toggle="modal" data-target="#delete-modal-{{$transaction->id}}"> Delete</a>
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
                                <div class="modal fade" id="delete-modal-{{$transaction->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="">Delete Transaction</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="alert alert-warning">
                                                <strong>Warning: </strong>This action cannot be undo. Make sure the user sent you the wrong transactions. Do you really want to remove it?
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                <form action="{{route('admin.transactions.remove',$transaction->id)}}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger">Delete</button>
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
        </div>
       

    </div>
@endsection