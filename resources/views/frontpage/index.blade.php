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

<div class="container page-normal">

    <!-- SHOW ARTICLES -->
    <h3>@LANG('content.Latest Articles')</h3>
    <div style=" margin-bottom:50px;">
		@if (isset($articles) && count($articles) > 0)
			@foreach($articles as $record)
			<div class=""><h4>{{$record->title}}</h4></div>
			<div class="">
				<p class="">
					<p>{{$record->description_short}}</p>
				</p>
			</div>
			@endforeach
		@else
			<div class=""><h4>@LANG('content.No articles')</h4></div>
		@endif
	</div>
    <!-- END OF ARTICLES -->

    <!-- SHOW WORD OF THE DAY -->
    @if (isset($wod))
        <h3>@LANG('content.Word of the Day')</h3>

    <div style="max-width:600px;">
		<div class="card text-white bg-primary mb-3">
			<div class="card-header"><h4>{{$wod->title}}</h4></div>
			<div class="card-body">
			    <p class="card-text">
                    <p>{{$wod->description}}</p>
                    @if (isset($wod->examples))
                        @foreach($wod->examples as $example)
                            <p><i>{{$example}}</i></p>
                        @endforeach
                    @endif
			    </p>
			</div>
		</div>
	</div>

    @endif
    <!-- END OF WORD OF THE DAY -->

    <!-- SHOW VOCAB LISTS -->
	<h3>@LANG('content.Vocabulary') ({{count($vocabLists)}})</h3>

	<div class="row row-course">
		@foreach($vocabLists as $record)
		<div class="col-sm-4 col-course"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
			<div class="card card-vocab-list truncate">
			<a href="/vocab-lists/view/{{$record->id}}">
				<div class="card-header">{{$record->title}}</div>
				<div class="card-body"><p class="card-text">Word Count: {{$record->words->count()}}</p></div>
			</a>
			</div>
		</div>
		@endforeach
	</div>
    <!-- END OF VOCAB LISTS -->

	@if (false)
    <!-- SHOW COURSES -->
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
    <!-- END OF COURSES -->
	@endif

</div>

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
