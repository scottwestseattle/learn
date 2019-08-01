@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">

		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word'):</label>
			<input type="text" name="title" class="form-control" value="{{$record->title}}"></input>	
		</div>
				
		<label for="description" class="control-label">@LANG('content.Translation'):</label>
		<input type="text" name="description" class="form-control" value="{{$record->description}}"</input>

		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
		</div>

		{{ csrf_field() }}
		
	</form>
	
</div>

@endsection

