<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8">		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, shrink-to-fit=9">
		<meta name="description" content="Gambolthemes">
		<meta name="author" content="Gambolthemes">
		<title>EasyStudy | Forgot Password</title>
		
		<!-- Favicon Icon -->
		<link rel="icon" type="image/png" href="images/fav.png">
		
		<!-- Stylesheets -->
		<link href='http://fonts.googleapis.com/css?family=Roboto:400,700,500' rel='stylesheet'>
		<link href='vendor/unicons-2.0.1/css/unicons.css' rel='stylesheet'>
		<link href="css/vertical-responsive-menu.min.css" rel="stylesheet">
		<link href="css/style.css" rel="stylesheet">
		<link href="css/responsive.css" rel="stylesheet">
		<link href="css/night-mode.css" rel="stylesheet">
		
		<!-- Vendor Stylesheets -->
		<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
		<link href="vendor/OwlCarousel/assets/owl.carousel.css" rel="stylesheet">
		<link href="vendor/OwlCarousel/assets/owl.theme.default.min.css" rel="stylesheet">
		<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="vendor/semantic/semantic.min.css">	
		
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
                        <a href="{{route('index')}}"><img src="images/logo.svg" alt=""></a>
                        <a href="{{route('index')}}"><img class="logo-inverse" src="images/ct_logo.svg" alt=""></a>
                    </div>
                </div>
            
                <div class="col-lg-6 col-md-8">
                    <div class="sign_form">
                        <h2>Request a Password Reset</h2>
                         <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="ui search focus mt-50">
                                <div class="ui left icon input swdh95">
                                    <input class="prompt srch_explore" type="email" name="email" value="" id="id_email" required="" maxlength="64" placeholder="Email Address">															
                                    <i class="uil uil-envelope icon icon2"></i>
                                </div>
                                <div class="error">{{$errors->first('email')}}</div>
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

        <script src="js/jquery-3.3.1.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/OwlCarousel/owl.carousel.js"></script>
        <script src="vendor/semantic/semantic.min.js"></script>
        <script src="js/custom.js"></script>	
        <script src="js/night-mode.js"></script>	
        
    </body>
</html>
