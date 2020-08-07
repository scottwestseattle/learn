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
	  <p><a class="btn btn-primary btn-lg" href="/articles" role="button">@LANG('fp.Start') &raquo;</a></p>
	</div>
</div>

<div class="container xpage-normal">

    <!-- SHOW ARTICLES -->
	<h3 class="mt-0">@LANG('content.Latest Articles')</h3>
	<div class="text-center mt-3">		
		@if (isset($articles) && count($articles) > 0)		
			<div style="display: inline-block; width: 95%;">
				<table>
				<?php $count = 0; ?>
				@foreach($articles as $record)
				
				<tr class="drop-box-articles" style="vertical-align:middle;">
					<td style="min-width:75px; font-size: 1.5em; padding:10px; color: white; background-color: #74b567; margin-bottom:10px;" >
						<div style="margin:0; padding:0; line-height:100%;">
							<div style="font-family:impact; font-size:1.7em; margin:10px 0 10px 0;">{{++$count}}</div>
						</div>
					</td>
					<td style="color:default; padding: 0 10px; text-align:left; padding:15px;">
						<table>
						<tbody>
							<tr><td style="padding-bottom:10px; font-size:1.3em; font-weight:normal;"><a href="/entries/{{$record->permalink}}">{{$record->title}}</a></td></tr>
							<tr><td style="padding-bottom:10px; font-size:.8em; font-weight:10;">
								@if (false)
								<div style="float:left; margin-right:15px;">{{$record->display_date}}</div>
								@endif
								<div style="float:left;">
									<div style="margin-right:15px; float:left;">{{$record->view_count}} @LANG('content.views')</div>
									<div style="margin-right:15px; margin-bottom:5px; float:left;">{{str_word_count($record->description)}} @LANG('content.words')</div>
								</div>
								<div style="float:left;">
									<div style="margin-right:5px; float:left;"><a href='/entries/read/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-volume-up"></span></a></div>
									@if (App\User::isAdmin())
									<div style="margin-right:5px; float:left;"><a href='/entries/edit/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-edit"></span></a></div>
									<div style="margin-right:0px; float:left;"><a href='/entries/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-trash"></span></a></div>
									@endif
								</div>
							</td></tr>
						</tbody>
						</table>
					</td>
				</tr>
				
				<tr><td>&nbsp;</td><td></td></tr>
				
				@endforeach
				</table>
			</div>
			<div class="mb-4"><a href="/articles">@LANG('content.Show All Articles')</a></div>
		@else
			<div class="text-left"><h4>@LANG('content.No articles')</h4></div>
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

	@if (isset($vocabLists) && count($vocabLists) > 0)
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
	@endif
	
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
