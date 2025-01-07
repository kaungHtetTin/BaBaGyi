@extends('admin.master')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit </h1>
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
                        </tr>
                    </thead>
                    <tbody>
                       
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
                                        @if ($voucher->win_amount > 0)
                                            {{$voucher->win_amount}}
                                        @else
                                            {{$voucher->amount * $voucher->lottery_type->coefficient}}
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($voucher->verified == 1)
                                        @if ($voucher->win == 1)
                                            <span style="color:green;font-weight:bold">Win</span>
                                        @else
                                            @if ($voucher->bonus_win == 1)
                                                <span style="color:red;font-weight:bold">Fail</span>
                                            @else
                                                 <span style="color:yellow;font-weight:bold">Fail</span>
                                            @endif
                                           
                                        @endif
                                    @else
                                        @if ($voucher->win == 1)
                                            <span style="color:green;font-weight:bold">Win</span>
                                        @else
                                            <span style="color:gray;font-weight:bold">Waiting</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        
                    </tbody>
                </table>
            </div>
            <br><br>
            @if ($voucher->win == 0)
                <div class="alert alert-warning">
                    <strong style="color: red">Important: </strong>This action cannot be undone
                </div>
                <form style="width: 70%" class="" action="{{route('admin.vouchers.update',$voucher->id)}}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="amount">Win Amount (Default x 10)</label>
                        <input id="input_amount" type="text" class="form-control" name="amount" value="{{$voucher->amount*10}}">
                        <p style="color: red;font-size:14px;">{{$errors->first('amount')}}</p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            @endif
             
        </div>

        

    </div>
@endsection