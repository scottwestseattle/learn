@extends('layouts.app')

@section('content')

<!--------------------------------------------------------------------------------------->
<!-- Front Page -->
<!--------------------------------------------------------------------------------------->

@if (Auth::check() && count($lesson) > 0)
@else
<!-- Main jumbotron for a primary marketing message or call to action -->
<div class="fpheader app-color-primary fpBannerImage">
	<div class="container text-center" >
		@if (isset($jumboTitle))
			<h1 class="">@LANG('fp.' . $jumboTitle)</h1>
			<p>@LANG('fp.' . $jumboSlug)</p>
		@else
			<h1 class="">@LANG('fp.Frontpage Header Title')</h1>
			<p>@LANG('fp.Frontpage Header Body')</p>			
		@endif
		@if (App\Tools::siteUses(LOG_MODEL_COURSES))
			<p><a class="btn btn-primary btn-lg" href="/start" role="button">@LANG('fp.Start') &raquo;</a></p>
		@endif
	</div>
</div>
@endif

<div class="container page-normal">

	<!-- SHOW VOCAB LISTS -->
	@if (isset($vocabLists) && count($vocabLists) > 0)
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
	@endif
	<!-- END OF VOCAB LISTS -->
	
	<!-- SHOW COURSES -->
	@if (App\Tools::siteUses(LOG_MODEL_COURSES))
		@if (Auth::check())
			
		<h3>@LANG('content.Courses in Progress')</h3>
		<div class="row row-course m-1">

			@if (isset($lesson['course']))
				<div class="alert alert-primary" role="alert">
					<h3 class="alert-heading mt-0">{{$lesson['course']->title}}</h3>
					@if (isset($lesson['lesson']))
						<hr>
						<h4>@LANG('content.Chapter') {{$lesson['lesson']->getFullName()}}</h4>
						<p>@LANG('content.Last viewed on') {{$lesson['date']}}</p>
						<p><a class="btn btn-primary btn-lg" href="/lessons/view/{{$lesson['lesson']->id}}" role="button">@LANG('content.Continue Lesson') &raquo;</a></p>
					@endif
				</div>
			@else
				<div class="mb-3">
					<h4>@LANG('content.No lessons started').<h4>
				</div>
			@endif
					
		</div>	

		@else
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
		@endif
	@endif
	<!-- END OF COURSES -->		

    <!-- SHOW ARTICLES -->
	@if (App\Tools::siteUses(LOG_MODEL_ARTICLES))
		<h3>@LANG('content.Latest Articles')</h3>
		<div class="text-center mt-3">		
			<div style="display: inline-block; width: 95%;">
				<table>
				<?php $count = 0; ?>
				@foreach($articles as $record)
				
				<tr class="drop-box-ghost" style="vertical-align:middle;">
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
								<div style="float:left;">
									@component('components.icon-read', ['href' => "/entries/read/$record->id"])@endcomponent						
									<div style="margin-right:15px; float:left;">{{$record->view_count}} @LANG('content.views')</div>
									<div style="margin-right:15px; margin-bottom:5px; float:left;"><a href="/entries/stats/{{$record->id}}">{{str_word_count($record->description)}} @LANG('content.words')</a></div>
									
									@if (App\User::isAdmin())
										<div style="margin-right:15px; float:left;">
											@component('components.control-button-publish', ['record' => $record, 'btnStyle' => 'btn-xxs', 'prefix' => 'entries', 'showPublic' => true])@endcomponent					
										</div>
									@endif
																	
								</div>
								<div style="float:left;">
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
		</div>	
		<!-- END OF ARTICLES -->
	@endif

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

</div>

<!-- PRE-FOOTER SECTION -->
<div class="mars-sky">
	<div class="container marketing text-center">
		<div class="pb-4 pt-3">
			<img src="/img/image5.png" width="100%" style="max-width: 300px;" />
			@if (isset($randomWord))
				@component('components.random-word', ['record' => $randomWord])@endcomponent						
			@else
				<h2 class="section-heading mt-0 mb-4">@LANG('fp.Frontpage Subfooter Title')</h2>
				<h4 style="font-size: 20px; font-weight: 400;">@LANG('fp.Frontpage Subfooter Body')</h4>
			@endif
		</div>
	</div>
</div>

@endsection
