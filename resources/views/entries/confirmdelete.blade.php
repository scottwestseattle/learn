@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component('entries.menu-submenu', ['record' => $entry])@endcomponent	

	<h1>Delete</h1>

	<form method="POST" action="/entries/delete/{{ $entry->id }}">

		@if (isset($referer))<input type="hidden" name="referer" value="{{$referer}}">@endif

		<div class="form-group">
			<button type="submit" class="btn btn-primary">Confirm Delete</button>
		</div>
		
		<div class="form-group">
			<h3 name="title" class="">{{$entry->title}}</h3>
		</div>
		
		@if (strlen(trim($entry->source_credit)) > 0)
		<div class="form-group">
			<i><p class="article-source">{{$entry->source_credit}}</p></i>
		</div>
		@endif
		
		@if (strlen(trim($entry->source_link)) > 0)
		<div class="form-group">
			<p class="article-source"><a target="_blank" href="{{$entry->source_link}}">{{$entry->source_link}}</a></p>			
		</div>
		@endif
			
		<div class="form-group">
			<button type="submit" class="btn btn-primary">Confirm Delete</button>
		</div>
	{{ csrf_field() }}
	</form>
</div>
@endsection
