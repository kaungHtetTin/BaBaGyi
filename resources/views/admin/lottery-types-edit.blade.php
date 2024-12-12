
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
                {{$lottery_type->type}}
                <span style="font-size: 18px;"> Edit</span>
           </h1>
        </div>

        <h1 class="h1" style="text-align: center;font-size:70px;">
            {{$lottery_type->coefficient}} 
            
        </h1>
        <div style="font-size: 18px;text-align:center"> Multiplication</div>

        <form style="width: 70%;margin:auto" class="user mb-3" action="{{route('admin.lottery-types.update',$lottery_type->id)}}" method="POST" >
            @csrf
            @method("PUT")
            <div style="display: flex">
                <input type="text" class="form-control" placeholder="Enter Multiplication" name="multiplication" style="display:inline" value="">
                <button class="btn btn-primary" style="margin-left:5px;">Update</button>
            </div>
            <p style="color: red;font-size:14px;">{{$errors->first('multiplication')}}</p>
        </form>
    </div>
@endsection