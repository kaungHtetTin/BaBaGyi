@extends('admin.master')
@section('content')
    <style>
        .avatar-img{
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid white;
            margin-right: 10px;
            cursor: pointer;
        }

        .avatar-img:hover{
             border: 2px solid #4e73df;
        }

        .my-error{
            color:red;
            margin-left:10px;
            font-size: 12px;
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
            <h1 class="h3 mb-0 text-gray-800">Edit Profile</h1>
        </div>

        <img src="{{asset($user->avatar_url)}}" alt="" style="width: 100px; height:100px; border-radius:50%">
        <br><br>
        <h5>Select an avatar</h5>
        <div>
            @foreach ($avatars as $avatar)
                <img onclick="updateAvatar({{$avatar->id}})" class="avatar-img" src="{{asset($avatar->url)}}" alt="">     
                <form id="form-update-avatar-{{$avatar->id}}" action="{{route('admin.avatar.modify')}}" method="post" style="display:inline">
                    @csrf
                    <input type="hidden" name="avatar_url" value="{{$avatar->url}}">
                </form>           
            @endforeach
        </div>

        <br><br>
        <h5>Change Password</h5>
        <form class="user" action="{{route('password.update')}}" method="POST" >
            @csrf
            @method("PUT")
            <div class="form-group">
                <input type="password" class="form-control form-control-user"
                    id="" aria-describedby="emailHelp"
                    placeholder="Enter current password" name="current_password">
                <p class="my-error"> {{$errors->updatePassword->first('current_password')}}</p>
            </div>

            <div class="form-group">
                <input type="password" class="form-control form-control-user"
                    id="" aria-describedby="emailHelp"
                    placeholder="Enter new password" name="password">
                <p class="my-error">{{$errors->updatePassword->first('password')}}</p>
            </div>

            <button class="btn btn-primary btn-user btn-block" style="width:100px;">
                Reset Now
            </button>
        </form>
    </div>

    <script>
        
        function updateAvatar(id){
            $(`#form-update-avatar-${id}`).submit();
        }
    </script>
@endsection