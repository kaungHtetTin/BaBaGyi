@extends('admin.master')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">3D Release </h1>
        </div>
        <div>
        <div class="alert alert-warning">
            <strong style="color: red">Important warning: </strong> This action cannot be undone. Please carefully fill the submit form. 
        </div>
        <form style="width: 70%;margin:auto" class="" action="{{route('admins.release-3d')}}" method="POST" >
            @csrf
            <div class="mb-3">
                <label for="lottery_number">3D Lottery Number</label>
                <input id="input_lottery_number" type="text" class="form-control" name="lottery_number">
                <p style="color: red;font-size:14px;">{{$errors->first('lottery_number')}}</p>
            </div>
              
            <button type="submit" class="btn btn-primary">Release Now</button>
        </form>
        </div>

    </div>
    <script>
        $(document).ready(()=>{
            $('#input_lottery_number').on('input',()=>{
                var number = $('#input_lottery_number').val();
                if(number.length > 3){
                    $('#input_lottery_number').val(number.substring(0, $('#input_lottery_number').val().length - 1));
                }
            })
        })
    </script>
@endsection