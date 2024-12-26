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
            <h1 class="h3 mb-0 text-gray-800">Add New Version Update</h1>
        </div>

        @if (session('msg'))
            <div class="alert alert-success">
                {{session('msg')}}
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <form class="user" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user"
                            id="" aria-describedby="emailHelp"
                            placeholder="Version Code" name="version_code">
                        <p class="my-error"> {{$errors->first('version_code')}}</p>
                    </div>

                    <div class="form-group">
                        <input type="text" class="form-control form-control-user"
                            id="" aria-describedby="emailHelp"
                            placeholder="Version Name" name="version_name">
                            <p class="my-error"> {{$errors->first('version_name')}}</p>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user"
                            id="" aria-describedby="emailHelp"
                            placeholder="Minimun Android Version" name="min_android_version">
                            <p class="my-error"> {{$errors->first('min_android_version')}}</p>
                    </div>

                    <br>
                    <input type="file" name="anroid_apk_file" id="" accept=".apk">
                    <p class="my-error"> {{$errors->first('anroid_apk_file')}}</p>

                    <br><br>

                 
                    <button class="btn btn-primary btn-user btn-block" style="width:100px;">
                        Add Now
                    </button>
                </form>
            </div>
        </div>
       


    </div>
@endsection