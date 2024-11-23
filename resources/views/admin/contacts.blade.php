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
            <h1 class="h3 mb-0 text-gray-800">Contacts</h1>
        </div>

        <div class="row">
            <div class="col-lg-7 col-md-6">
                <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Contact</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Service</th>
                                    <th>Contact</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
            </div>
            <div class="col-lg-5 col-md-6">
                <h4>Add new contact</h4>
                <form class="user" action="" method="POST" >
                    @csrf
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user"
                            id="" aria-describedby="emailHelp"
                            placeholder="Enter Service (eg. Viber, Telegram, etc.)" name="service">
                        <p class="my-error"> {{$errors->first('service')}}</p>
                    </div>

                    <div class="form-group">
                        <input type="text" class="form-control form-control-user"
                            id="" aria-describedby="emailHelp"
                            placeholder="Enter phone no or link" name="contact">
                        <p class="my-error">{{$errors->first('contact')}}</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-user btn-block" style="width:100px;">
                        Add Now
                    </button>
                </form>
            </div>
        </div>
       

    </div>
@endsection