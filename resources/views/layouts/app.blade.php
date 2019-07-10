<?php
$domainName = isset($domainName) ? $domainName : '';
?>
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
			
			<!-- Language Selector Dropdown -->			
			<div style="margin-left: 5px; margin-right:10px;" class="dropdown">
				<a href="#" class="" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
					<img width="25" src="/img/theme1/language-{{App::getLocale()}}.png" />
				</a>
				<ul style="float: left; background-color:transparent; border:0;"  class="dropdown-menu">
					<li><a href="/language/en"><img src="/img/theme1/language-en.png" /></a></li>
					<li><a href="/language/es"><img src="/img/theme1/language-es.png" /></a></li>
					<li><a href="/language/zh"><img src="/img/theme1/language-zh.png" /></a></li>
				</ul>
			</div>			
			
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarCollapse">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item"><a class="nav-link" href="/">@LANG('ui.Home')<span class="sr-only">(current)</span></a></li>
					<li class="nav-item"><a class="nav-link" href="/contact">@LANG('ui.Contact')</a></li>
					<li class="nav-item"><a class="nav-link" href="/about">@LANG('ui.About')</a></li>
					@if (isset($domainName) && $domainName == 'localhost')
						<li class="nav-item"><a class="nav-link" href="/translations">@LANG('ui.Translations')</a></li>
						<li class="nav-item"><a class="nav-link" href="/eunoticereset">@LANG('ui.Privacy Notice')</a></li>
						<li class="nav-item"><a class="nav-link" href="/login">@LANG('ui.Login')</a></li>
						<li class="nav-item"><a class="nav-link" href="/register">@LANG('ui.Register')</a></li>
					@endif
					<li class="nav-item"><a class="nav-link" href="/visitors">@LANG('ui.Visitors')</a></li>
				</ul>
			</div>
			
		</nav>
	</header>
	
<main role="main">
	<div class="page-layout">
	
		@if(session()->has('message.level'))
			<div style="" class="alert alert-{{ session('message.level') }}"> 
				{!! session('message.content') !!}
			</div>
		@endif

		@if (isset($euNoticeAccepted) && !$euNoticeAccepted)
			<div style="margin:0; padding: 5px 5px 5px 20px;" id="euNoticeAccepted" class="alert alert-success"> 
				<span>@LANG('ui.European Union Privacy Notice')</span>
				<button type="submit" onclick="event.preventDefault(); ajaxexec('/eunoticeaccept'); $('#euNoticeAccepted').hide();" class="btn btn-primary" style="padding:1px 4px; margin:5px;">@LANG('ui.Accept')</button>
			</div>
		@endif
	
		@yield('content')
	</div>
</main>
	
	<!-- FOOTER -->
	<footer class="footer backin-black">
		<div class="container marketing text-center" style="padding:50px;">
			<a href="/"><img src="/img/logo.png" height="60" /></a>
			<p style="font-size:2em;" class="footer-heading">{{$domainName}}</p>
			<p style="font-size:1.2em;" class="">@LANG('content.Site Title')</p>
			<p>&copy; 2019 {{$domainName}} - @LANG('ui.All Rights Reserved')</p>
			<span class="footer-links">
				<a href="/privacy">@LANG('ui.Privacy Policy')</a>&bull;
				<a href="/terms">@LANG('ui.Terms of Use')</a>&bull;
				<a href="#">@LANG('ui.Back to Top')</a></p>
			</span>
		</div>
	</footer>
	
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script>window.jQuery || document.write('<script src="https://getbootstrap.com/docs/4.3/assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
	<script src="https://getbootstrap.com/docs/4.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o" crossorigin="anonymous"></script>

	<script>
function ajaxexec(url)
{	
	var xhttp = new XMLHttpRequest();
	
	xhttp.onreadystatechange = function() 
	{
		//alert(this.status);
		
		if (this.status == 200)
		{
			//alert(this.responseText);
		}
		else if (this.status == 404)
		{
			alert(this.responseText);
		}
					
		if (this.readyState == 4 && this.status == 200) 
		{	
			//
			// results
			//
			//alert(this.requestText);
		}
	};
	
	xhttp.open("GET", url, true);
	xhttp.send();
}	
	</script>
</body>
</html>
