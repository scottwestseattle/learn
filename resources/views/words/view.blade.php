@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin, 'parent_id' => $record->parent_id])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-top" role="button" href="/vocab-lists/view/{{$record->vocab_list_id}}/">
		    @LANG('content.Back to List')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	</div>
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-sm btn-nav-lesson {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}">
			<span class="glyphicon glyphicon-button-prev"></span>
			@LANG('ui.Prev')
		</a>
		<a class="btn btn-primary btn-sm btn-nav-lesson {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">
			@LANG('ui.Next')
			<span class="glyphicon glyphicon-button-next"></span>
		</a>
	</div>

	<div style="margin-top:30px;">

		<h3>
			{{$record->title}}@component('components.badge', ['text' => $record->view_count])@endcomponent
		</h3>
	</div>

	<div class="">
		<p style="font-size:1.2em;">{{$record->description}}</p>
		@if (isset($record->examples))
		@foreach($record->examples as $example)
			<p><i>{{$example}}</i></p>
		@endforeach
		@endif
	<div>

	<div style="margin-top:50px;">
		@if (isset($nextWod))
		<a href="/words/view/{{$nextWod->id}}" class="btn btn-outline-primary btn-sm" style="font-size:16px;" role="button">{{$nextWod->title}}</a>
		@endif
		@if (isset($firstWod))
		<a href="/words/view/{{$firstWod->id}}" class="btn btn-outline-primary btn-sm" style="font-size:14px;" role="button">{{$firstWod->title}}</a>
		@endif
	</div>

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

	<div style="margin-top:50px;">
	@if (isset($record->parent_id) && isset($words))
		@component('components.data-course-words', ['edit' => $lesson ? '/words/edit/' : '/words/view/', 'words' => $words])@endcomponent
	@elseif (isset($records) && count($records) > 0)
		@component('components.data-badge-list', ['edit' => '/words/view/', 'records' => $records, 'title' => 'Vocabulary'])@endcomponent
	@endif
	</div>

</div>

@endsection

