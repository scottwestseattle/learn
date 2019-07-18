@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('content.' . $title)</h1>
               
	<form method="POST" action="/{{$prefix}}/create">
					
		<div class="form-group">
			<label for="parent_id" class="control-label">@LANG('content.Course'):</label>
			<select name="parent_id" class="form-control">
				@foreach ($courses as $course)
					<option value="{{$course->id}}" {{ isset($record) && $course->id == $record->parent_id ? 'selected' : ''}}>{{$course->title}}</option>
				@endforeach
			</select>
		</div>
		
		<div class="form-group">
			<label for="title" class="control-label">@LANG('gen.Title'):</label>
			<input type="text" name="title" class="form-control" />
		</div>

		<div class="form-group">
			<label for="lesson_number" class="control-label">@LANG('content.Chapter'):</label>
			<input type="number"  min="1" max="1000" step="1" name="lesson_number" class="form-control form-control-100" value="1" />
		</div>	

		<div class="form-group">		
			<label for="section_number" class="control-label">@LANG('content.Section'):</label>
			<input type="number"  min="1" max="1000" step="1" name="section_number" class="form-control form-control-100" value="1" />
		</div>	
		
		<div class="form-group">
			<label for="description" class="control-label">@LANG('gen.Description'):</label>
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
