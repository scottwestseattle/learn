@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="form-group">
		<a href="/lessons/view/{{$record->parent_id}}"><button class="btn btn-success">Back to Lesson</button></a>
	</div>
	
	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">
		
		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word'):</label>
			<input type="text" name="title" class="form-control" value="{{$record->title}}"></input>	
		</div>

		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
		</div>

		{{ csrf_field() }}
		
	</form>
	
</div>

@endsection

