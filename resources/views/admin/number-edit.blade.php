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
                {{$lottery_type->type}} {{"$lottery_hour:$lottery_minute"}} {{$clock->morning==1?"AM":"PM"}}
                <span style="font-size: 18px;"> Edit</span>
           </h1>
        </div>

        <h1 class="h1" style="text-align: center;font-size:70px;">
            {{$number->number}}
        </h1>

        <br><br>
        <form style="width: 70%;margin:auto" class="user mb-3" action="{{route('admin.numbers.modify',$number->id)}}" method="POST" >
            @csrf
            @method("PUT")
            <div style="display: flex">
                <input type="text" class="form-control" placeholder="Sell amount" name="sell" style="display:inline" value="{{$number->sell}}">
                <button class="btn btn-primary" style="margin-left:5px;">Update</button>
            </div>
            <p style="color: red;font-size:14px;">{{$errors->first('sell')}}</p>
        </form>
    </div>
@endsection