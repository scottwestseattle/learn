@extends('layouts.app')

@section('content')

<div class="container page-normal">

@if (false)
	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
@endif

	<h1>@LANG('ui.Delete') History Record</h1>
	
	<p>{{$historyTitle}}</p>
	
	<form method="POST" action="/{{$prefix}}/delete/{{ $record->id }}">
			   		
		<div class="submit-button">
			<button type="submit" class="btn btn-primary">@LANG('ui.Confirm Delete')</button>
		</div>
		
	{{ csrf_field() }}
	</form>
</div>
@endsection
