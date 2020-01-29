@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin, 'parent_id' => $record->parent_id])@endcomponent

	<div style="margin-top:30px;">

		<h3>
			{{$record->title}}<span style="vertical-align: middle; background-color: LightGray; color: gray; margin-left: 7px; font-size:12px; padding:3px 3px; font-weight:bold;" class="badge">{{$record->view_count}}</span>
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
	@elseif (isset($records) && $records->count() > 0)
		@component('components.data-badge-list', ['edit' => '/words/view/', 'records' => $records, 'title' => 'Vocabulary'])@endcomponent
	@endif
	</div>

</div>

@endsection

