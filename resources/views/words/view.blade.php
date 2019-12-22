@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin, 'parent_id' => $record->parent_id])@endcomponent
	
	<div class="">
		<h3>{{$record->title}}</h3>
	</div>

	<div class="">
		<p style="font-size:1.2em;">{{$record->description}}</p>
	<div>

	<div style="font-size:.8em; margin-top:80px;">
	@if (isset($record->parent_id))
		@component('components.data-course-words', ['edit' => $lesson ? '/words/edit/' : '/words/view/', 'words' => $words])@endcomponent																				
	@else
		@component('components.data-badge-list', ['edit' => $lesson ? '/words/edit/' : '/words/view/', 'records' => $records])@endcomponent																			
	@endif
	</div>
	
</div>

@endsection

