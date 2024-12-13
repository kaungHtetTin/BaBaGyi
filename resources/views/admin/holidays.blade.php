@php
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
@endphp
@extends('admin.master')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Holidays</h1>
             
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Day</th>
                    </tr>
                </thead>
    
                <tbody>
                    @foreach ($holidays as $holiday)
                        <tr>
                            <td> {{$holiday->title}} </td>
                            <td>{{$months[$holiday->month-1].', '.$holiday->day}}</td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection