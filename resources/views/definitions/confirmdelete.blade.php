@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
	
	<h1>@LANG('ui.Delete') @LANG('content.' . $title)</h1>

	<form method="POST" action="/{{$prefix}}/delete/{{$record->id}}">
			   	
		<h3>{{$record->title }}</h3>
		<p>{{$record->definition}}</p>
		<p>{{$record->forms}}</p>
		<p>{{$record->translation_en}}</p>
		<p>{{$record->examples}}</p>
		
		@if (count($record->entries) > 0)
			<p>
			<div>Word is used in {{count($record->entries)}} entry/entries and will be removed</div>
			@foreach($record->entries as $r)
				<div><a target="_blank" href="/entries/{{$r->permalink}}">{{$r->title}}</a></div>
			@endforeach	
			</p>
		@endif
		
		@if (count($record->tags) > 0)
			<p>
			<div>Word is used in {{count($record->tags)}} favorite list(s) and will be removed</div>
			@foreach($record->tags as $r)
				<div><a target="_blank" href="/definitions/list/{{$r->id}}">{{$r->name}}</a></div>
			@endforeach	
			</p>
		@endif
		
		<div class="submit-button">
			<button type="submit" class="btn btn-primary">@LANG('ui.Confirm Delete')</button>
		</div>
		
	{{ csrf_field() }}
	</form>
</div>
@endsection
