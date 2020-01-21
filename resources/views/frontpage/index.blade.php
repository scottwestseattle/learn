@extends('layouts.app')

@section('content')

<!--------------------------------------------------------------------------------------->
<!-- Front Page -->
<!--------------------------------------------------------------------------------------->

<!-- Main jumbotron for a primary marketing message or call to action -->
<div class="fpheader">
	<div class="container text-center">
	  <h1 class="">@LANG('fp.Frontpage Header Title')</h1>
	  <p>@LANG('fp.Frontpage Header Body')</p>
	  <p><a class="btn btn-primary btn-lg" href="/courses" role="button">@LANG('fp.Start Learning') &raquo;</a></p>
	</div>
</div>

<!-- SHOW COURSES -->
<div class="container page-normal">
	
	<h3>@LANG('content.Courses') ({{count($courses)}})</h3>
	
	<div class="row row-course">
		@foreach($courses as $record)
		<div class="col-sm-4 col-course"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
			<div class="card card-course {{$record->getCardColor()}} truncate">
			<a href="/courses/view/{{$record->id}}">
				<div class="card-header">{{$record->title}}</div>
				<div class="card-body"><p class="card-text">{{$record->description}}</p></div>
			</a>
			</div>
		</div>
		@endforeach
	</div>

</div>
<!-- END OF COURSES -->

<!-- PRE-FOOTER SECTION -->
<div class="grassy-green">
	<div class="container marketing text-center">
		<div style="padding:50px;">	
			<img src="/img/image5.png" width="100%" style="max-width: 350px;" /> 	
			<h2 class="section-heading">@LANG('fp.Frontpage Subfooter Title')</h2>
			<p class="lead">@LANG('fp.Frontpage Subfooter Body')</p>
		</div>						
	</div>
</div>	

@endsection
