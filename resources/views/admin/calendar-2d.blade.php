@extends('admin.master')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Thai {{$type == 3 ? "3D":"2D"}} Calendar</h1>
        </div>

        <div>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Hour</th>
                            <th>Number</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Date</th>
                            <th>Hour</th>
                            <th>Number</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>{{$record->created_at->format('Y M, d')}}</td>
                                <td>
                                    {{$record->clock->hour<10 ?"0".$record->clock->hour:$record->clock->hour }} :
                                    {{$record->clock->minute<10 ?"0".$record->clock->minute:$record->clock->minute }}
                                    {{$record->clock->morning == 1 ? "AM":"PM"}}
                                </td>
                                <td>{{$record->number}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

             {{$records->links()}}
        </div>
    </div>
@endsection