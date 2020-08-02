@extends('layouts.app')

@section('content')

<div class="container">

	@component('entries.menu-submenu')@endcomponent	

	<h1 style="font-size:1.3em;">@LANG('ui.Articles') ({{ count($records) }})</h1>

	<div>
		@foreach($records as $record)			
		<div class="drop-box-articles mb-4" style="padding:10px 10px 20px 15px;">
			<div style="font-size:1.3em; font-weight:normal;">
				<a href="/entries/{{$record->permalink}}">{{$record->title}}</a>
			</div>

			@if (true)
			<div style="padding-bottom:10px; font-size:.8em; font-weight:10;">
				<div style="float:left; margin-right:15px;">{{$record->display_date}}</div>
				<div style="float:left;">
					<div style="margin-right:15px; float:left;">{{$record->view_count}} @LANG('content.views')</div>
					<div style="margin-right:0px; float:left;">{{str_word_count($record->description)}} @LANG('content.words')</div>
				</div>
			</div>
			@endif
		</div>
		@endforeach
	</div>

</div>

@endsection
