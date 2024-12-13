
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
            <h1 class="h3 mb-0 text-gray-800">{{$title}}</h1>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="alert alert-info">
                    Earning  - <strong>{{$earning_today}}</strong>
                </div>
            </div>

            <div class="col-6">
                <div class="alert alert-warning">
                    Give Back  - <strong>{{$give_Back_today}}</strong>
                </div>
            </div>

        </div>
        
        <div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Clock</th>
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
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Clock</th>
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
                                <td>{{$voucher->created_at->format('Y-m-d')}}</td>
                                <td>{{$voucher->user->name}}</td>
                                <td>{{$voucher->user->phone}}</td>
                                <td>
                                    {{$voucher->clock->hour>9 ? $voucher->clock->hour :"0".$voucher->clock->hour}}:
                                    {{$voucher->clock->minute>9 ?$voucher->clock->minute :"0".$voucher->clock->minute}}
                                    {{ $voucher->clock->morning == 1 ? "AM":"PM"}}
                                </td>
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
                                    @if ($voucher->verified == 0 )
                                        @if ($voucher->win == 1)
                                            <a class="btn btn-primary action-button"href="#" data-toggle="modal" data-target="#approve-modal-{{$voucher->id}}"> Paid</a>
                                        @else
                                            <a class="btn btn-warning action-button"href="#" data-toggle="modal" data-target="#restore-modal-{{$voucher->id}}"> Restore</a>
                                        @endif
                                        
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

                                 <div class="modal fade" id="restore-modal-{{$voucher->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="">Restore Voucher</h5>
                                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="alert alert-warning">
                                                Do you really want to restore voucher and refund to user's balance?
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                <form action="{{route('admin.vouchers.delete',$voucher->id)}}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-primary">Restore</button>
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
        </div>
       

    </div>
@endsection