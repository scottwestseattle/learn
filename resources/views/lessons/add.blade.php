@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('content.' . $title)</h1>
               
	<form method="POST" action="/{{$prefix}}/create">
							
		<label for="title" class="control-label">@LANG('gen.Title'):</label>
		<input type="text" name="title" class="form-control" />

		<div style="margin-top: 20px;" class="form-group">
			<label for="lesson_number" class="control-label">@LANG('content.Lesson'):</label>
			<select name="lesson_number" id="lesson_number" class="">
				@foreach ($lessonNumbers as $key => $value)
					<option value="{{$key}}">{{$value}}</option>
				@endforeach
			</select>			
		</div>

		<div style="margin-top: 20px;" class="form-group">
			<label for="section_number" class="control-label">@LANG('content.Section'):</label>
			<select name="section_number" id="section_number" class="">
				@foreach ($sectionNumbers as $key => $value)
					<option value="{{$key}}">{{$value}}</option>
				@endforeach
			</select>			
		</div>	
		
		<div class="form-group">
			<label for="description" class="control-label">@LANG('gen.Description'):</label>
			<textarea name="description" class="form-control"></textarea>
		<div>

		<div class="form-group">
			<label for="text" class="control-label">@LANG('gen.Text'):</label>
			<textarea style="height:500px" name="text" class="form-control"></textarea>
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
