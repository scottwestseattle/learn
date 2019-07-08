@extends('layouts.app')

@section('content')

<!--------------------------------------------------------------------------------------->
<!-- Front Page -->
<!--------------------------------------------------------------------------------------->

	<!-- Main jumbotron for a primary marketing message or call to action -->
	<div class="fpheader">
		<div class="container text-center">
		  <h1 class="">@LANG('content.Frontpage Header Title')</h1>
		  <p>@LANG('content.Frontpage Header Body')</p>
		  <p><a class="btn btn-primary btn-lg" href="/signup" role="button">@LANG('ui.Learn More') &raquo;</a></p>
		</div>
	</div>

    <!-- START THE FEATURETTES -->

	<div class="container marketing">	
		<div class="row featurette">
			<div class="col-md-7">
				<h2 class="featurette-heading">@LANG('content.Frontpage Section 1 Title')</h2>
				<p class="lead">@LANG('content.Frontpage Section 1 Body')</p>
			</div>
			<div class="col-md-5">
				<img src="/img/image1.png" width="100%" style="max-width: 350px;" />
			</div>
		</div>
	</div>
	
    <hr class="featurette-divider">

	<div class="container marketing">	
		<div class="row featurette">
			<div class="col-md-7 order-md-2">
				<h2 class="featurette-heading">@LANG('content.Frontpage Section 2 Title')</h2>
				<p class="lead">@LANG('content.Frontpage Section 2 Body')</p>
			</div>
			<div class="col-md-5 order-md-1">
				<img src="/img/image2.png" width="100%" style="max-width: 350px;" />
			</div>
		</div>
	</div>
	
    <hr class="featurette-divider">

	<div class="container marketing">	
		<div class="row featurette">
		  <div class="col-md-7">
			<h2 class="featurette-heading">@LANG('content.Frontpage Section 3 Title')</h2>
			<p class="lead">@LANG('content.Frontpage Section 3 Body')</p>
		  </div>
		  <div class="col-md-5">
				<img src="/img/image3.png" width="100%" style="max-width: 350px;" />
		  </div>
		</div>
	</div>
	
    <div style="padding:50px;"><!-- SPACER ONLY --></div>

    <!-- /END THE FEATURETTES -->

	<!-- PRE-FOOTER SECTION -->
	<div class="grassy-green">
		<div class="container marketing text-center">
			<div style="padding:50px;">	
				<img src="/img/globe.png" width="100%" style="max-width: 350px;" /> 	
				<h2 class="featurette-heading">@LANG('content.Frontpage Subfooter Title')</h2>
				<p class="lead">@LANG('content.Frontpage Subfooter Body')</p>
			</div>						
		</div>
	</div>	

@endsection
	