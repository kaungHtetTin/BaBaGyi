@extends('admin.master')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">2D Release </h1>
        </div>
        <div>
        <div class="alert alert-warning">
            <strong style="color: red">Important warning: </strong> This action cannot be undone. Please carefully fill the submit form. 
        </div>
        <form style="width: 70%;margin:auto" class="" action="{{route('admins.release-2d')}}" method="POST" >
            @csrf
            <div class="mb-3">
                <label for="lottery_number">2D Lottery Number</label>
                <input id="input_lottery_number" type="text" class="form-control" name="lottery_number">
                <p style="color: red;font-size:14px;">{{$errors->first('lottery_number')}}</p>
            </div>
            <div class="mb-3">
                <label for="clock_id">Lottery Time</label>

                <select name="clock_id" class="form-control">
                    @foreach ($lottery_clocks as $lottery_clock)
                        @php
                            $clock = $lottery_clock->clock;
                            $lottery_hour = $clock->hour<9 ? "0".$clock->hour: $clock->hour;
                            $lottery_minute = $clock->minute<9 ? "0".$clock->minute: $clock->minute;
                        @endphp 
                        <option value="{{$clock->id}}">{{"$lottery_hour:$lottery_minute"}} {{$clock->morning==1?"AM":"PM"}}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" class="form-control" name="lottery_type_id" value="2">
            <button type="submit" class="btn btn-primary">Release Now</button>
        </form>
        </div>

    </div>

    <script>
        $(document).ready(()=>{
            $('#input_lottery_number').on('input',()=>{
                var number = $('#input_lottery_number').val();
                if(number.length > 2){
                    $('#input_lottery_number').val(number.substring(0, $('#input_lottery_number').val().length - 1));
                }
            })
        })
    </script>
@endsection