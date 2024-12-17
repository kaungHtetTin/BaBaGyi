@php
    $lottery_hour = $clock->hour<9 ? "0".$clock->hour: $clock->hour;
    $lottery_minute = $clock->minute<9 ? "0".$clock->minute: $clock->minute;
@endphp
@extends('admin.master')
@section('content')
    <div class="container-fluid">
        @if (session('msg'))
            <div class="alert alert-success">
                {{session('msg')}}
            </div>
        @endif

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
           <h1 class="h3 mb-0 text-gray-800">
                Edit
           </h1>
        </div>

        <h1 class="h1" style="text-align: center;font-size:60px;">
            {{$lottery_type->type}}  
            {{"$lottery_hour:$lottery_minute"}} {{$clock->morning==1?"AM":"PM"}}
        </h1>
       

        <form style="width: 70%;margin:auto" class="user mb-3" action="{{route('admin.lottery-types.update',$lottery_clock->id)}}" method="POST" >
            @csrf
            @method("PUT")
            <div class="form-group">
                <span>Multiplication</span>
                <input type="text" class="form-control" placeholder="Enter Multiplication" name="multiplication" style="display:inline" value="{{$lottery_type->coefficient}}">
                <p style="color: red;font-size:14px;">{{$errors->first('multiplication')}}</p>
            </div>

            <div class="form-group">
                <span>Close Before (Minute)</span>
                <input type="text" class="form-control" placeholder="Enter in minute" name="close_before" style="display:inline" value="{{$lottery_clock->close_before}}">
                <p style="color: red;font-size:14px;">{{$errors->first('close_before')}}</p>
            </div>
           
            <button class="btn btn-primary" style="width:100%">Update</button>
            
        </form>
    </div>
@endsection