@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin, 'parent_id' => $record->parent_id])@endcomponent
	
	<div style="margin-top:30px;">
		<h3>{{$record->title}}</h3>
	</div>

	<div class="">
		<p style="font-size:1.2em;">{{$record->description}}</p>
		<p style="font-size:1.1em;"><i>{{$record->examples}}</i></p>
	<div>
	
	<div style="margin-top:50px;">
		@if (isset($next))
		<a href="/words/view/{{$next->id}}" class="btn btn-outline-primary btn-sm" style="font-size:16px;" role="button">{{$next->title}}</a>
		@else
		<a href="/words/view/{{$records[0]->id}}" class="btn btn-outline-primary btn-sm" style="font-size:14px;" role="button">{{$records[0]->title}}</a>
		@endif
	</div>

	<div style="margin-top:50px;">
	@if (isset($record->parent_id))
		@component('components.data-course-words', ['edit' => $lesson ? '/words/edit/' : '/words/view/', 'words' => $words])@endcomponent																				
	@else
		@component('components.data-badge-list', ['edit' => '/words/view/', 'records' => $records, 'title' => 'Vocabulary'])@endcomponent																			
	@endif
	</div>
	
</div>

@endsection

