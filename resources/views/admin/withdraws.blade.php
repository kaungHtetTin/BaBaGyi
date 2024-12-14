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
            <h1 class="h3 mb-0 text-gray-800">Withdraw Request</h1>
        </div>

        <div class="alert alert-info">
            Amount today - <strong>{{$amount_today}}</strong>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Balance</th>
                        <th>Withdraw</th>
                        <th>Banking</th>
                        <th>Phone</th>
                        <th>Account Name</th>
                        <th>Admin</th>
                        <th>Approved On</th>
                        <th>Status</th>
                        
                        
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Balance</th>
                        <th>Withdraw</th>
                        <th>Banking</th>
                        <th>Phone</th>
                        <th>Account Name</th>
                        <th>Admin</th>
                        <th>Approved On</th>
                        <th>Status</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($withdraws as $withdraw)
                            <tr>
                            <td>{{$withdraw->created_at->diffForHumans()}}</td>
                            <td><a href="{{route('admin.users.withdraws',$withdraw->user_id)}}" style="text-decoration: none">{{$withdraw->user->name}}</a></td>
                            <td>{{$withdraw->user->balance}}</td>
                            <td>{{$withdraw->amount}}</td>
                            <td style="text-align: center">
                                <img src="{{asset($withdraw->banking->icon_url)}}" alt="" style="width: 20px;border-radius:5px;">
                                <div>{{$withdraw->banking->bank}}</div>
                            </td>
                            <td>{{$withdraw->method}}</td>
                            <td>{{$withdraw->account_name}}</td>
                            <td>
                                @if ($withdraw->verified_by != 0)
                                    {{$withdraw->verified_by($withdraw->verified_by)->name}}
                                @endif
                            </td>
                            <td>
                                @if ($withdraw->verified_by != 0)
                                    {{$withdraw->updated_at->diffForHumans()}}
                                @endif
                            </td>
                            <td>
                                @if ($withdraw->verified==1)
                                    <span style="color:green;"><i class="fas fa-check-circle fa-fw"></i> Sent</span>
                                @else
                                    <a class="btn btn-primary action-button"href="#" data-toggle="modal" data-target="#approve-modal-{{$withdraw->id}}"> Sent</a>
                                @endif
                            </td>

                            <div class="modal fade" id="approve-modal-{{$withdraw->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="">Approve Withdraw</h5>
                                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <div class="alert alert-success">
                                            Do you really want to approve?
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                            <form action="{{route('admin.withdraws.approve',$withdraw->id)}}" method="POST">
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
        {{$withdraws->links()}}
       

    </div>
@endsection