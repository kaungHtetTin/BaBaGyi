@extends('admin.master')
@section('content')

    <style>
       .action-button{
            padding:3px;
            font-size: 12px;
            margin:3px;
        }
        table tr td{
            font-size: 14px;
        }
    </style>

    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Mobile App Version History</h1>
            <a href="{{route('admins.mobile-versions.add')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                    class="fas fa-plus fa-sm text-white-50"></i> Add new</a>
        </div>

        <div class="table-responsive ">
            <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Version Code</th>
                        <th>Version Name</th>
                        <th>Min Android Version</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Version Code</th>
                        <th>Version Name</th>
                        <th>Min Android Version</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($mobile_versions as $version)
                        <tr>
                            <td> {{$version->version_code}} </td>
                            <td> {{$version->version_name}}  </td>
                            <td> {{$version->min_android_version}}</td>
                            <td>  
                                <a class="btn btn-primary action-button" href="{{ Storage::url('app/public/'.$version->url) }}"> Get </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection