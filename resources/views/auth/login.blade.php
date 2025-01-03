<!DOCTYPE html>
<html lang="en">

	<head>
		@include('student.components.head') 
	</head> 

<body>

	<style>
		.error{
			color: red;
			font-family: 'Roboto', sans-serif;
		}
	</style>
	<!-- Signup Start -->
	<div class="sign_in_up_bg">
		<div class="container">  
			<div class="row justify-content-lg-center justify-content-md-center">
				<div class="col-lg-12">
					<div class="main_logo25" id="logo">
						<a href="index.html"><img src="images/logo.svg" alt=""></a>
						<a href="index.html"><img class="logo-inverse" src="images/ct_logo.svg" alt=""></a>
					</div>
				</div> 
				<div class="col-lg-6 col-md-8">
					<div class="sign_form">
						<h2>Welcome Back</h2>
						<p>Log In to Your EasyStudy Account!</p>
						<button class="social_lnk_btn color_btn_fb"><i class="uil uil-facebook-f"></i>Continue with Facebook</button>
						<button class="social_lnk_btn mt-15 color_btn_tw"><i class="uil uil-twitter"></i>Continue with Twitter</button>
						<button class="social_lnk_btn mt-15 color_btn_go"><i class="uil uil-google"></i>Continue with Google</button>
						<form method="POST" action="{{ route('login') }}">
							@csrf
							<div class="ui search focus mt-15">
								<div class="ui left icon input swdh95">
									<input class="prompt srch_explore" type="email" name="email" value="" id="id_email" required="" maxlength="64" placeholder="Email Address">															
									<i class="uil uil-envelope icon icon2"></i>
								</div>
							</div>
							<div class="ui search focus mt-15">
								<div class="ui left icon input swdh95">
									<input class="prompt srch_explore" type="password" name="password" value="" id="id_password" required="" maxlength="64" placeholder="Password">
									<i class="uil uil-key-skeleton-alt icon icon2"></i>
								</div>
							</div>
							<div class="ui form mt-30 checkbox_sign">
								<div class="inline field">
									<div class="ui checkbox mncheck">
										<input type="checkbox" tabindex="0" class="hidden" name="remember">
										<label>Remember Me</label>
									</div>
								</div>
							</div>
							<div class="error" style="margin-top: 7px;">
								{{$errors->first('email')}}
							</div>
							<button class="login-btn" type="submit">Sign In</button>
						</form>
						<p class="sgntrm145">Or <a href="{{route('password.request')}}">Forgot Password</a>.</p>
						
						<p class="mb-0 mt-30 hvsng145">Don't have an account? <a href="{{route('register')}}">Sign Up</a></p>
					</div>
					<div class="sign_footer"><img src="images/sign_logo.png" alt="">© 2020 <strong>Cursus</strong>. All Rights Reserved.</div>
				</div>				
			</div>				
		</div>				
	</div>
	<!-- Signup End -->	


	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="vendor/OwlCarousel/owl.carousel.js"></script>
	<script src="vendor/semantic/semantic.min.js"></script>
	<script src="js/custom.js"></script>
	<script src="js/my.js"></script>	
	<script src="js/night-mode.js"></script>	
	
</body>
</html>