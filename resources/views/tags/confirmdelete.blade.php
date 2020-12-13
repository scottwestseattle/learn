@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
	
	<h1>@LANG('ui.Delete') @LANG('ui.' . $title)</h1>

	<form method="POST" action="/{{$prefix}}/delete/{{ $record->id }}">
			   
		<h3 name="title" class="">{{$record->name}}</h3>
	
		<div class="large-text mb-2">@LANG('ui.Type'): {{$record->getTypeFlagName()}}</div>
		<div class="large-text">@LANG('ui.User'): {{$record->user_id}}</div>
		
		@if ($cantDelete)
			
			<h3 class="red">@LANG('content.This Tag is in use and cannot be deleted')</h3>
			@if ($countDefinitions > 0)
				<div class="large-text mb-2">@LANG('content.Definition Tags'): {{$countDefinitions}}</div>
			@endif
			@if ($countEntries > 0)
				<div class="large-text mb-2">@LANG('content.Entry Tags'): {{$countEntries}}</div>
			@endif				

		@else
			
			<h3>@LANG('content.This Tag is not in use')</h3>			
		
		@endif
		

		<div class="submit-button">
			<button type="submit" class="btn btn-{{$cantDelete ? 'secondary' : 'primary'}}" {{$cantDelete ? 'disabled' : ''}}>@LANG('ui.Confirm Delete')</button>
		</div>

		
		{{ csrf_field() }}
		{{$referrer}}
	
	</form>
</div>
@endsection
