@extends('admin.master')
@section('content')
<style>
    .my-error{
        color:red;
        margin-left:10px;
        font-size: 12px;
    }
</style>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Add New Payment Methods</h1>
        </div>
        @if (session('msg'))
            <div class="alert alert-success">
                {{session('msg')}}
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <form class="user" action="{{route('admin.payment-methods.store')}}" method="POST" >
                    @csrf
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user"
                            id="" aria-describedby="emailHelp"
                            placeholder="Enter Mobile Banking Phone" name="phone">
                        <p class="my-error"> {{$errors->first('phone')}}</p>
                    </div>

                    <div class="form-group">
                        <input type="text" class="form-control form-control-user"
                            id="" aria-describedby="emailHelp"
                            placeholder="Enter Mobile Banking Account Name" name="account_name">
                            <p class="my-error"> {{$errors->first('account_name')}}</p>
                    </div>
                    
                    @foreach ($bankings as $banking)
                        <div class="form-group">
                            <div class="custom-control custom-checkbox small">
                                <input type="checkbox" class="custom-control-input" id="banking_{{$banking->id}}" name="banking_{{$banking->id}}">
                                <label class="custom-control-label" for="banking_{{$banking->id}}">{{$banking->bank}}</label>
                            </div>
                        </div>
                    @endforeach
                    @if (session('banking_error'))
                        <p class="my-error"> {{session('banking_error')}}</p>
                    @endif
                 
                    <button class="btn btn-primary btn-user btn-block" style="width:100px;">
                        Add Now
                    </button>
                </form>
            </div>
        </div>

    </div>
@endsection