@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('content.' . $title)</h1>

	<form method="POST" action="/{{$prefix}}/create">

		<div class="form-group">
			<label for="parent_id" class="control-label">@LANG('content.Course'):</label>
			<select name="parent_id" class="form-control">
				<option value="0">(@LANG('content.Select Course'))</option>
				@foreach ($courses as $record)
					<option value="{{$record->id}}" {{ isset($course->id) && $record->id == $course->id ? 'selected' : ''}}>{{$record->title}}</option>
				@endforeach
			</select>
		</div>

		<div class="form-group">
			<label for="title" class="control-label">@LANG('gen.Title'):</label>
			<input type="text" name="title" class="form-control" />
		</div>

		<div class="form-group">
			<label for="lesson_number" class="control-label">@LANG('content.Chapter'):</label>
			<input type="number"  min="1" max="1000" step="1" name="lesson_number" class="form-control form-control-100" value="{{$chapter}}" />
		</div>

		<div class="form-group">
			<label for="section_number" class="control-label">@LANG('content.Section'):</label>
			<input type="number"  min="0" max="1000" step="1" name="section_number" class="form-control form-control-100" value="{{$section}}" />
		</div>

		<div class="form-group">
			<label for="description" class="control-label">@LANG('gen.Description'):</label>
			<textarea name="description" class="form-control"></textarea>
		<div>

		<div class="form-group">
			<label for="title_chapter" class="control-label">@LANG('gen.Chapter Title'):</label>
			<input type="text" name="title_chapter" class="form-control" />
		<div>

		<div class="form-group">
			<label for="main_photo" class="control-label">@LANG('gen.Main Photo'):</label>
			<input type="text" name="main_photo" class="form-control" />
		<div>

		<div class="form-group">
			<label for="seconds" class="control-label">@LANG('gen.Seconds'):</label>
			<input type="number" name="seconds" class="form-control" />
		<div>

		<div class="form-group">
			<label for="break_seconds" class="control-label">@LANG('gen.Break Seconds'):</label>
			<input type="number" name="break_seconds" class="form-control" />
		<div>

		<div class="form-group">
			<label for="reps" class="control-label">@LANG('gen.Reps'):</label>
			<input type="number" name="reps" class="form-control" />
		<div>

		<div class="form-group">
			<div class="submit-button">
				<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Add')</button>
			</div>
		</div>

		{{ csrf_field() }}

	</form>

	@component('lessons.comp-lesson-list', ['records' => $lessons])@endcomponent

</div>

@endsection
