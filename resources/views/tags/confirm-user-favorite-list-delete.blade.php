@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
	
	<h1>@LANG('ui.Delete') {{$record->name}}</h1>

	<form method="POST" action="/{{$prefix}}/delete/{{$record->id}}">
			   			
		@if ($count > 0)
			
			<h3 class="red">@LANG('content.This list has') {{$count}} @LANG('content.entries')</h3>
			<div class="large-text mb-2">@LANG('content.Are you sure you want to delete it?')</div>

		@endif
		
		<div class="submit-button">
			<button type="submit" class="btn btn-primary">@LANG('ui.Confirm Delete')</button>
		</div>

		{{ csrf_field() }}
		{{$referrer}}
	</form>
</div>
@endsection
