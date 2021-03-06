@extends('layouts.app')

@section('content')

<?php 
	$directLink = false;
?>

<div class="container page-normal">
               
	<div class="">
	
	@component('entries.menu-submenu', ['record' => $record, 'index' => $index, 'prefix' => $prefix])@endcomponent		
			
		<!------------------------------------>
		<!-- Top Navigation Buttons -->
		<!------------------------------------>
		
		@if (false)
			
		@if (isset($prev) || isset($record->parent_id) || isset($next))
		<div style="margin-top: 10px;">
			@if (isset($prev))
				<a href="/entries/{{$prev->permalink}}"><button type="button" class="btn btn-blog-nav"><span style="margin-right:5px;" class="glyphicon glyphicon-circle-arrow-left"></span>@LANG('ui.Prev')</button></button></a>
			@endif
			
			<a href="{{$backLink}}"><button type="button" class="btn btn-blog-nav">@LANG($backLinkText)<span style="margin-left:5px;" class="glyphicon glyphicon-circle-arrow-up"></span></button></a>
			
			@if (isset($next))
				<a href="/entries/{{$next->permalink}}"><button type="button" class="btn btn-blog-nav">@LANG('ui.Next')<span style="margin-left:5px;" class="glyphicon glyphicon-circle-arrow-right"></span></button></a>
			@endif	

		</div>
		@elseif (isset($backLink) && isset($backLinkText) && !((Auth::user() && (Auth::user()->user_type >= 1000))))
		<div style="margin-top: 10px;">
			<a href="{{$backLink}}">
				<button type="button" class="btn btn-blog-nav">{{$backLinkText}}
					<span style="margin-left:5px;" class="glyphicon glyphicon-circle-arrow-up"></span>
				</button>
			</a>			
		</div>
		@endif
		
		@endif

		<!------------------------------------>
		<!-- The Entry						-->
		<!------------------------------------>

		<div>
			<!-- Stats -->
			<div style="" class="vertical-align">
				@component('components.icon-read', ['href' => "/entries/read/$record->id"])@endcomponent						
				<div class="article-source">
					<div style="margin-right:15px; float:left;">{{$record->view_count}} @LANG('content.views')</div>
					<div style="margin-right:15px; float:left;"><a href="/entries/stats/{{$record->id}}">{{$wordCount}} @LANG('content.words')</a></div>
					<span style="margin-left:10px;">
						@component('components.control-button-publish', ['record' => $record, 'prefix' => $prefix, 'showPublic' => true])@endcomponent					
					</span>
					@if (isset($record->definitions) && count($record->definitions) > 0)
						<div class="mr-2 float-left">
							<a href="/entries/vocabulary/{{$record->id}}" class="btn btn-xs btn-primary" role="button">
								<div class="middle mr-0" style="margin-bottom:2px;">Vocabulary</div>
								<div class="badge badge-small badge-white middle ml-0">{{count($record->definitions)}}</div>
							</a>
						</div>
					@endif
				</div>
			</div>
			
			<!-- Title -->
			<h1 name="title" class="">{{$record->title}}</h1>
			
			<!-- Summary -->
			@if (strlen(trim($record->description_short)) > 0)
				<div class="entry" style="margin-bottom:20px; font-size:1.3em;">
					<div><i>{{$record->description_short}}</i></div>
				</div>
			@endif

			<div class="entry-div" style="margin-top:20px; width:100%; font-size:1.1em;">
				<div class="entry" style="width:100%;">
					<span name="description" class="">{!! $record->description !!}</span>		
				</div>
			</div>

			@if (strlen(trim($record->source_credit)) > 0)
				<p class="article-source">{{$record->source_credit}}</p>
			@endif
			
			@if (strlen(trim($record->source_link)) > 0)
				<p class="article-source"><a target="_blank" href="{{$record->source_link}}">{{$record->source_link}}</a></p>
			@endif
		</div>

	<!------------------------------------>
	<!-- Bottom Navigation Buttons -->
	<!------------------------------------>

	@if (false)
		
	<div class="trim-text" style="max-width:100%; margin-top: 30px;">
		@if (isset($prev))
			<div class="" style="float:left; margin: 0 5px 5px 0;" >
				<a href="/entries/{{$prev->permalink}}"><button type="button" class="btn btn-nav-bottom"><span class="glyph-nav-bottom glyphicon glyphicon-circle-arrow-left"></span>{{$prev->title}}</button></a>
			</div>
		@endif
		@if (isset($next))
			<div style="float:left;">
				<a href="/entries/{{$next->permalink}}"><button type="button" class="btn btn-nav-bottom"><span class="glyph-nav-bottom glyphicon glyphicon-circle-arrow-right"></span>{{$next->title}}</button></a>
			</div>
		@endif			
	</div>
	
	@endif
	
	</div>
	
</div>

@endsection
