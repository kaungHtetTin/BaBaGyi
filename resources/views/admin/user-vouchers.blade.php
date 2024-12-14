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

        <a href="{{route('admin.users.transactions',$user->id)}}" class="btn btn-secondary my-btn">Top Up</a>
        <a href="{{route('admin.users.withdraws',$user->id)}}" class="btn btn-secondary my-btn">Withdraws</a>
        <a href="{{route('admin.users.vouchers',$user->id)}}" class="btn btn-primary my-btn">Vouchers</a>
        <a href="{{route('admin.users.wallet-histories',$user->id)}}" class="btn btn-secondary my-btn">Wallet Histories</a>
        <a href="{{route('admin.users.setting',$user->id)}}" class="btn btn-secondary my-btn">Setting</a>
        <br><br>
        <h5>Vouchers</h5>
        @if (count($vouchers)>0)
            <div class="table-responsive ">
                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Lottery</th>
                            <th>Amount</th>
                            <th>Pay</th>
                            <th>Status</th>
                            <th>Admin</th>
                            <th>Action</th>
                            
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Date</th>
                            <th>Lottery</th>
                            <th>Amount</th>
                            <th>Pay</th>
                            <th>Status</th>
                            <th>Admin</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($vouchers as $voucher)
                            <tr>
                                <td>{{$voucher->created_at->diffForHumans()}}</td>
                                <td>{{$voucher->number}}</td>
                                <td>{{$voucher->amount}}</td>
                                <td>
                                    @if ($voucher->win == 1)
                                            {{$voucher->amount * $voucher->lottery_type->coefficient}}
                                    @endif
                                </td>
                                <td>
                                    @if ($voucher->verified == 1)
                                        @if ($voucher->win == 1)
                                            <span style="color:green;font-weight:bold">Win</span>
                                        @else
                                            <span style="color:red;font-weight:bold">Fail</span>
                                        @endif
                                    @else
                                        @if ($voucher->win == 1)
                                            <span style="color:green;font-weight:bold">Win</span>
                                        @else
                                            <span style="color:gray;font-weight:bold">Waiting</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($voucher->verified_by != 0)
                                            {{$voucher->verified_by($voucher->verified_by)->name}}
                                    @endif
                                </td>
                                <td>
                                    @if ($voucher->verified == 0 && $voucher->win == 1)
                                        <a class="btn btn-primary action-button"href="#" data-toggle="modal" data-target="#approve-modal-{{$voucher->id}}"> Paid</a>
                                    @endif

                                    @if ($voucher->verified_by != 0)
                                        <span style="color:green;"><i class="fas fa-check-circle fa-fw"></i> Paid</span>
                                    @endif
                                </td>

                                <div class="modal fade" id="approve-modal-{{$voucher->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="">Approve Voucher</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="alert alert-success">
                                                Do you really want to approve?
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                <form action="{{route('admin.vouchers.approve',$voucher->id)}}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button class="btn btn-primary">Paid</button>
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
            {{$vouchers->links()}}
        @else
            <div style="padding: 20px; text-align:center">
                <br><br><br><br>
                <h6>No voucher yet</h6>
            </div>
        @endif

    </div>
@endsection