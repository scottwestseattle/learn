@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('content.Vocabulary')</h1>
               
	<form method="POST" action="/{{$prefix}}/create">
	
		@if (isset($parent_id))
		<input type="hidden" name="parent_id" value={{$parent_id}} />
		@endif
		
		<label for="title" class="control-label">@LANG('content.Word or Phrase'):</label>
		<input type="text" name="title" class="form-control" />
		
		<div class="form-group">
			<label for="description" class="control-label">@LANG('content.Translation, Definition, or Hint'):</label>
			<textarea name="description" class="form-control"></textarea>
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
