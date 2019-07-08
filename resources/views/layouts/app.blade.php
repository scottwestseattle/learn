<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="Learn Systems">
	<meta name="generator" content="Jekyll v3.8.5">
	<title>Learn - Remote Virtual Training</title>

	<!-- Bootstrap core CSS -->
	<link href="/css/bootstrap.min.css" rel="stylesheet">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
	
    <!-- Custom styles for this template -->
    <link href="/css/project.css" rel="stylesheet">
</head>
  
<body>
  
	<header>
		<nav style="" class="navbar navbar-expand-md navbar-dark fixed-top power-purple">
		
			<a class="navbar-brand" href="/"><img height="35" src="/img/logo.png" /></a>

			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarCollapse">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item"><a class="nav-link" href="/">@LANG('ui.Home')<span class="sr-only">(current)</span></a></li>
					<li class="nav-item"><a class="nav-link" href="/contact">@LANG('ui.Contact')</a></li>
					<li class="nav-item"><a class="nav-link" href="/about">@LANG('ui.About')</a></li>
				</ul>
			</div>
			
		</nav>
	</header>
	
<main role="main">
	<div class="page-layout">
		@yield('content')
	</div>
</main>
	
	<!-- FOOTER -->
	<footer class="footer backin-black">
		<div class="container marketing text-center" style="padding:50px;">
			<img src="/img/logo.png" height="60" /> 			
			<p style="font-size:2em;" class="featurette-heading">Learn English Online</p>
			<p>&copy; 2019 Learn&nbsp;&middot;&nbsp;<a href="/privacy">Privacy Policy</a>&nbsp;&middot;&nbsp;<a href="/terms">Terms of Use</a>&nbsp;&middot;&nbsp;<a href="/">Back to top</a></p>
		</div>

	</footer>
	
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script>window.jQuery || document.write('<script src="https://getbootstrap.com/docs/4.3/assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
	<script src="https://getbootstrap.com/docs/4.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o" crossorigin="anonymous"></script>

</body>
</html>
