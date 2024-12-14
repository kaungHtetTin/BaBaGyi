@extends('admin.master')
@section('content')
    <style>
        .action-button{
            padding:3px;
            font-size: 12px;
            margin:3px;
        }
        .my-error{
            color:red;
            margin-left:10px;
            font-size: 12px;
        }
        .ad-img{
            width : 300px;
            height : 150px;
            border-radius: 10px;
            cursor: pointer;
        }
    </style>
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
                                @foreach ($contacts as $contact)
                                    <tr>
                                        <td>{{$contact->service}}</td>
                                        <td>{{$contact->contact}}</td>
                                        <td>
                                            <a class="btn btn-danger action-button"href="#" data-toggle="modal" data-target="#delete-modal-{{$contact->id}}"> Delete</a>
                                        </td>

                                        <div class="modal fade" id="delete-modal-{{$contact->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="">Delete Contact</h5>
                                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="alert alert-warning">
                                                        Do you really want to delete it?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                        <form action="{{route('admin.contacts.remove',$contact->id)}}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
            </div>
            <div class="col-lg-5 col-md-6">
                <h5>Add new contact</h5>
                <form class="user" action="{{route('admin.contacts.store')}}" method="POST" >
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
       
        <br><br>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ad Photo</h1>
        </div>
    
        <div class="row">
            <div class="col-lg-7 col-md-6">
                @if (count($ads)>0)
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Content</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Content</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach ($ads as $ad)
                                    <tr>
                                        <td>
                                            <img style="width: 80px; height:45px; margin:10px; border-radius:10px;" 
                                            src="{{ Storage::url('app/public/'.$ad->url) }}" alt="">
                                        </td>
                                        <td>
                                            <a class="btn btn-danger action-button"href="#" data-toggle="modal" data-target="#delete-modal-adphoto-{{$ad->id}}"> Delete</a>
                                        </td>

                                        <div class="modal fade" id="delete-modal-adphoto-{{$ad->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="">Delete Photo</h5>
                                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="alert alert-warning">
                                                        Do you really want to delete it?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                        <form action="{{route('admin.ad-photo.destroy',$ad->id)}}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div>No Ad Photo is added</div>
                @endif
                
            </div>
            <div class="col-lg-5 col-md-6">
                <h5>Add new ad photo</h5>
                <img id="img_preview" class="ad-img" src="{{asset('img/thumbnail-demo.jpg')}}" alt="">
                <form  class="user" action="{{route('admin.ad-photo.save')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input id="input_image" type="file" name="image_file" style="display: none" accept="image/*">
                    <p class="my-error">{{$errors->first('image_file')}}</p>
                    <button type="submit" class="btn btn-primary btn-user btn-block" style="width: 300px; margin-top:10px;">Upload Now</button>
                </form>
            </div>
        </div>

        <br><br>
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Notice</h1>
        </div>

        <div class="row">
            <div class="col-lg-7 col-md-6">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Content</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Content</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach ($notices as $notice)
                                <tr>
                                    <td>{{$notice->content}}</td>
                                    <td>
                                        <a class="btn btn-danger action-button"href="#" data-toggle="modal" data-target="#delete-modal-notice-{{$notice->id}}"> Delete</a>
                                    </td>

                                    <div class="modal fade" id="delete-modal-notice-{{$notice->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="">Delete Notice</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                                <div class="alert alert-warning">
                                                    Do you really want to delete it?
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                    <form action="{{route('admin.notices.remove',$notice->id)}}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-5 col-md-6">
                <h5>Add new notice</h5>
                <form class="user" action="{{route('admin.notices.save')}}" method="POST" >
                    @csrf
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user"
                            id="" aria-describedby="emailHelp"
                            placeholder="Promotion plan or something like that" name="content">
                            <p class="my-error">{{$errors->first('content')}}</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-user btn-block" style="width:100px;">
                        Add Now
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(()=>{
            $('#img_preview').click(()=>{
                $('#input_image').click();
            })

            $('#input_image').change(()=>{
                let files = $('#input_image').prop('files');
                let file = files[0];
                let reader = new FileReader();
                reader.onload = (e)=>{
                    let image_src = e.target.result;
                    $('#img_preview').attr('src',image_src);
                }

                reader.readAsDataURL(file)
            })
        })
    </script>
@endsection