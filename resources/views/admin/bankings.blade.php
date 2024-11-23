@extends('admin.master')
@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Supported Banking</h1>
           
        </div>
        <div class="row">
            @foreach ($bankings as $banking)
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Mobile Banking
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{$banking->bank}}</div>
                                </div>
                                <div class="col-auto">
                                    <img src="{{asset($banking->icon_url)}}" alt="" style="width: 40px; border-radius:5px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
       

    </div>
@endsection