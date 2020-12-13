<!doctype html>
<?php
$domainName = isset($domainName) ? $domainName : App\Tools::getDomainName();
$siteTitle = isset($siteTitle) ? $siteTitle : App\Tools::getSiteTitle();
$siteTitleLite = isset($siteTitleLite) ? $siteTitleLite : App\Tools::getSiteTitle(false);
?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="Learn Systems">
	<meta name="generator" content="Jekyll v3.8.5">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<title>{{$siteTitle}}</title>

    <!-- Scripts -->
	<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
	<script src="https://getbootstrap.com/docs/4.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o" crossorigin="anonymous"></script>

    <script src="{{ asset('/js/project.js') }}"></script>

@if (isset($tinymce))
	<script src="/js/tinymce/tinymce.min.js"></script>
	<script src="/js/loadTinyMce.js"></script>
@endif

	<!-- Bootstrap core CSS -->
	<link href="/css/bootstrap.min.css" rel="stylesheet">

	<!-- laravel css -->
    <!-- link href="/css/app.css" rel="stylesheet" --><!-- doesn't seeme to be needed-->

    <!-- Custom styles for this template -->
    <link href="/css/project.css" rel="stylesheet">
    <link href="/css/glyphicons.css" rel="stylesheet">
    <link href="/css/glyphicons-short.css" rel="stylesheet">

</head>

<body>
	<header>
		<nav style="" class="navbar navbar-expand-md navbar-dark fixed-top app-color-primary">

			<a class="navbar-brand" href="/">
				<!-- img height="35" src="/img/logo.png" / -->
				<div class="brand logo middle">
					<svg class="mb-1" width="32" height="32" fill="currentColor" >
						<use xlink:href="/img/bootstrap-icons.svg#brightness-high" />
					</svg>
				</div>
			</a>

			<div class="mr-auto navbar-icon-shortcuts">
				@auth
					@if ($isAdmin)
						<a class="navbar-item" href="/admin"><span class="glyphicon glyphicon-user gold"></span></a>
					@else
						<a class="navbar-item" href="/home"><span class="glyphicon glyphicon-user white"></span></a>
					@endif
				@endauth
				<a class="navbar-item mr-3" href="/search"><span style="color:white;" class="glyphicon glyphicon-search"></span></a>
			</div>

			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse"
				aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarCollapse">
				<ul class="navbar-nav mr-auto">
					@if (defined('LOG_MODEL_ARTICLES') && App\Tools::siteUses(LOG_MODEL_ARTICLES))
						<li class="nav-item"><a class="nav-link" href="/articles">@LANG('content.Articles')</a></li>
					@endif
					@if (defined('LOG_MODEL_DEFINITIONS') && App\Tools::siteUses(LOG_MODEL_DEFINITIONS))
						<li class="nav-item"><a class="nav-link" href="/definitions">@LANG('content.Dictionary')</a></li>
					@endif
					@if (defined('LOG_MODEL_WORDS') && App\Tools::siteUses(LOG_MODEL_WORDS))
						<li class="nav-item"><a class="nav-link" href="/vocabulary">@LANG('content.Lists')</a></li>
					@endif
					@if (defined('LOG_MODEL_COURSES') && App\Tools::siteUses(LOG_MODEL_COURSES))
						<li class="nav-item"><a class="nav-link" href="/courses">@LANG('content.Courses')</a></li>
					@endif
					@if ($isAdmin && defined('LOG_MODEL_BOOKS') && App\Tools::siteUses(LOG_MODEL_BOOKS))
						<li class="nav-item"><a class="nav-link" href="/books">@LANG('content.Books')</a></li>
					@endif

                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">@LANG('ui.Login')</a>
                        </li>
                        @if (App\Tools::isAdmin() && Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">@LANG('ui.Register')</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}<span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

								@if (Auth::user()->isAdmin())
									<a class="dropdown-item" href="/home"><span class="glyphicon glyphicon-home mr-2"></span></a>
									<div class="dropdown-divider"></div>
									<a class="dropdown-item" href="/admin">@LANG('ui.Admin')</a>
									<a class="dropdown-item" href="/events">@LANG('ui.Events')</a>
									<a class="dropdown-item" href="/tags">@LANG('ui.Tags')</a>
									<a class="dropdown-item" href="/translations">@LANG('ui.Translations')</a>
									<a class="dropdown-item" href="/users">@LANG('ui.Users')</a>
									<a class="dropdown-item" href="/visitors">@LANG('ui.Visitors')</a>

								<div class="dropdown-divider"></div>
								@endif

                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    <button style="color: red; font-size:1em;" class="btn btn-warning"><span style="color:red; margin-left:0;" class="glyphCustom-sm glyphicon glyphicon-log-out"></span>@LANG('ui.Logout')</button>
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>

                            </div>
                        </li>
                    @endguest

				</ul>
			</div>

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

		</nav>
	</header>

<main role="main">
	<div class="page-layout">

		@if(session()->has('message.level'))
			<div style="" class="alert alert-{{ session('message.level') }}">
				{{App\Tools::getFlashMessage(session('message.content'))}}
			</div>
		@endif

		@if (isset($showPrivacyNotice) && $showPrivacyNotice)
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
		<div class="container marketing text-center p-5">

			<a class="" href="/">
				<div class="brand logo middle">
					<svg class="bi app-color-primary-reverse" width="55" height="55" >
						<use xlink:href="/img/bootstrap-icons.svg#brightness-high" />
					</svg>
				</div>
			</a>

			<p style="font-size:2em;" class="footer-heading">{{$domainName}}</p>
			<p style="font-size:1.2em;" class="">{{$siteTitleLite}}</p>
			<p>&copy; {{date("Y")}} {{$domainName}} - @LANG('ui.All Rights Reserved')</p>
			<span class="footer-links">
				<a href="#">@LANG('ui.Back to Top')</a>&bull;
				<a href="/privacy">@LANG('ui.Privacy Policy')</a>&bull;
				<a href="/terms">@LANG('ui.Terms of Use')</a>&bull;
				<a href="/contact">@LANG('ui.Contact')</a>&bull;
				<a href="/about">@LANG('ui.About')</a>
			</span>
		</div>
	</footer>

</body>
</html>
