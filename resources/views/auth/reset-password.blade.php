<!DOCTYPE html>
<html lang="en">

	<head>
	<head>
		@include('student.components.head') 
	</head> 
		
	</head> 

    <body class="sign_in_up_bg">
        <style>
            .error{
                color: red;
                font-family: 'Roboto', sans-serif;
                margin-top:7px;
            }
        </style>
        
        <!-- Signup Start -->
        <div class="container">
            @if (session('status')!==null)
                <div>
                    {{session('status')}}
                </div>
            @endif
            <div class="row justify-content-lg-center justify-content-md-center">
                <div class="col-lg-12">
                    <div class="main_logo25" id="logo">
                        <a href="{{route('index')}}"><img src="{{asset('images/logo.svg')}}" alt=""></a>
                        <a href="{{route('index')}}"><img class="logo-inverse" src="{{asset('images/ct_logo.svg')}}" alt=""></a>
                    </div>
                </div>
            
                <div class="col-lg-6 col-md-8">
                    <div class="sign_form">
                        <h2>Reset Password Now</h2>
                        <form method="POST" action="{{ route('password.store') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{$request->route('token')}}">
                            <div class="ui search focus mt-50">
                                <div class="ui left icon input swdh95">
                                    <input class="prompt srch_explore" type="email" name="email" value="" id="id_email" required="" maxlength="64" placeholder="Email Address">															
                                    <i class="uil uil-envelope icon icon2"></i>
                                </div>
                                <div class="error">{{$errors->first('email')}}</div>
                            </div>
                            <div class="ui search focus mt-15">
                                <div class="ui left icon input swdh95">
                                    <input class="prompt srch_explore" type="password" name="password" value="" id="id_password" required="" maxlength="64" placeholder="Password">															
                                    <i class="uil uil-lock icon icon2"></i>
                                </div>
                                <div class="error">{{$errors->first('password')}}</div>
                            </div>
                            <div class="ui search focus mt-15">
                                <div class="ui left icon input swdh95">
                                    <input class="prompt srch_explore" type="password" name="password_confirmation" value="" id="id_password_confirmation" required="" maxlength="64" placeholder="Confirm Password">															
                                    <i class="uil uil-lock icon icon2"></i>
                                </div>
                                <div class="error">{{$errors->first('password_confirmation')}}</div>
                            </div>
                            <button class="login-btn" type="submit">Request</button>
                        </form>
                        <p class="mb-0 mt-30">Go Back <a href="{{route('login')}}">Sign In</a></p>
                    </div>
                    <div class="sign_footer"><img src="images/sign_logo.png" alt="">© 2024 <strong>calamuseducation</strong>. All Rights Reserved.</div>
                </div>				
            </div>				
        </div>				
        <!-- Signup End -->	

        <script src="{{asset('js/jquery-3.3.1.min.js')}}"></script>
        <script src="{{asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('vendor/OwlCarousel/owl.carousel.js')}}"></script>
        <script src="{{asset('vendor/semantic/semantic.min.js')}}"></script>
        <script src="{{asset('js/custom.js')}}"></script>	
        <script src="{{asset('js/night-mode.js')}}"></script>	
        
    </body>
</html>

