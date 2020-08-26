@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('content.' . $title)</h1>
               
	@component('components.control-accent-chars-esp', ['visible' => true, 'flat' => true])@endcomponent																					   
			   
	<form method="POST" action="/{{$prefix}}/create">
							
		<label for="title" class="control-label">@LANG('gen.Title'):</label>
		<input type="text" id="title" name="title" class="form-control" onfocus="setFocus($(this))" />
		
		<div class="form-group">
			<label for="description" class="control-label">@LANG('gen.Description'):</label>
			<textarea id="description" name="description" class="form-control" onfocus="setFocus($(this))" ></textarea>
		<div>
		
		<div class="form-group">
			<div class="submit-button">
				<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Add')</button>
			</div>
		</div>
		
		{{ csrf_field() }}

	</form>

</div>

@endsection
