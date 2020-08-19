@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin, 'parent_id' => $record->parent_id])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-top" role="button" href="/{{$prefix}}/">
		    @LANG('content.Back to Dictionary')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	</div>
	
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-sm btn-nav-lesson {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{isset($prev) ? $prev->id : 0}}">
			<span class="glyphicon glyphicon-button-prev"></span>
			@LANG('ui.Prev')
		</a>
		<a class="btn btn-primary btn-sm btn-nav-lesson {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{isset($next) ? $next->id : 0}}">
			@LANG('ui.Next')
			<span class="glyphicon glyphicon-button-next"></span>
		</a>
	</div>

	<div style="margin-top:30px;">
		<h3>
			{{$record->title}}<span style="vertical-align: middle; background-color: LightGray; color: gray; margin-left: 7px; font-size:12px; padding:3px 3px; font-weight:bold;" class="badge">{{$record->view_count}}</span>
		</h3>
	</div>

	<div class="">
		@if (isset($record->definition))
			<p style="font-size:1.2em;">{{$record->definition}}</p>
		@endif
		@if (isset($record->translation_en))
			<p style="font-size:1.2em;">{{$record->translation_en}}</p>
		@endif
		@if (isset($record->translation_es))
			<p style="font-size:1.2em;">{{$record->translation_es}}</p>
		@endif
		@if (isset($record->examples))
		@foreach($record->examples as $example)
			<p><i>{{$example}}</i></p>
		@endforeach
		@endif
	<div>

	<div class="page-nav-buttons">
		@if (isset($prev))
		<a class="btn btn-primary btn-sm btn-nav-lesson" role="button" href="/{{$prefix}}/view/{{$prev->id}}">
			<span class="glyphicon glyphicon-button-prev"></span>
			{{$prev->title}}
		</a>
		@endif
		@if (isset($next))
		<a class="btn btn-primary btn-sm btn-nav-lesson" role="button" href="/{{$prefix}}/view/{{$next->id}}">
			{{$next->title}}
			<span class="glyphicon glyphicon-button-next"></span>
		</a>
		@endif
	</div>

	@if (isset($records) && count($records) > 0)
		<div style="margin-top:50px;">
			@component('components.data-badge-list', ['edit' => '/words/view/', 'records' => $records, 'title' => 'Vocabulary'])@endcomponent
		</div>
	@endif

</div>

@endsection

