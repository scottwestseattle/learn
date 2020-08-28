@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component('entries.menu-submenu', ['index' => $index, 'isIndex' => $isIndex])@endcomponent	

	<h1>@LANG('ui.' . $page_title) ({{count($records)}})</h1>

	<div>
		@if (isset($records))
		@foreach($records as $record)			
		<div class="drop-box-articles mb-4" style="padding:10px 10px 20px 15px;">
			<div style="font-size:1.3em; font-weight:normal;">
				<a href="/entries/{{$record->permalink}}">{{$record->title}}</a>
			</div>

			<div style="padding-bottom:10px; font-size:.8em; font-weight:10;">
				@if (false)
				<div style="float:left; margin-right:15px;">{{$record->display_date}}</div>
				@endif
				<div style="float:left;">
					<div style="margin-right:15px; float:left;">{{$record->view_count}} @LANG('content.views')</div>
					<div style="margin-right:15px; float:left;"><a href="/entries/stats/{{$record->id}}">{{str_word_count($record->description)}} @LANG('content.words')</a></div>
					@if (App\User::isAdmin())
						<div style="margin-right:15px; float:left;">
							@component('components.control-button-publish', ['record' => $record, 'prefix' => 'entries', 'showPublic' => true])@endcomponent					
						</div>
					@endif
				</div>
				<div style="float:left;">
					<div style="margin-right:5px; float:left;"><a href='/entries/read/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-volume-up"></span></a></div>
					@if (App\User::isAdmin())
					<div style="margin-right:5px; float:left;"><a href='/entries/edit/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-edit"></span></a></div>
					<div style="margin-right:0px; float:left;"><a href='/entries/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-trash"></span></a></div>
					@endif				
				</div>
				<div style="clear:both;"></div>
				@if (App\User::isSuperAdmin())
				<div class="mt-1">
					@if (App\User::isAdmin())
						<span><a target="_blank" href="https://{{App\Tools::getSiteName($record->site_id)}}">{{App\Tools::getSiteName($record->site_id)}}</a></span>
					@endif				
				</div>
				@endif
			</div>
		</div>
		@endforeach
		@endif
	</div>

</div>

@endsection
