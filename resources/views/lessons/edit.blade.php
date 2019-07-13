@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" action="/{{$prefix}}/update/{{$record->id}}">

		<label for="title" class="control-label">@LANG('gen.Title'):</label>
		<input type="text" name="title" class="form-control" value="{{$record->title}}"></input>	
					
		<div style="margin-top: 20px;" class="form-group">
		
			<label for="lesson_number" class="control-label">@LANG('content.Lesson'):</label>
			<select name="lesson_number" id="lesson_number" class="">
				@foreach ($record->getLessonNumbers() as $key => $value)
					<option value="{{$key}}" {{ $key == $record->lesson_number ? 'selected' : ''}}>{{$value}}</option>
				@endforeach
			</select>			
			&nbsp;
			<label for="section_number" class="control-label">@LANG('content.Section'):</label>
			<select name="section_number" id="section_number" class="">
				@foreach ($record->getSectionNumbers() as $key => $value)
					<option value="{{$key}}" {{ $key == $record->section_number ? 'selected' : ''}}>{{$value}}</option>
				@endforeach
			</select>			
			&nbsp;
			<input type="checkbox" name="renumber_flag" id="renumber_flag" class="" />
			<label for="blocked_flag" class="checkbox-big-label">@LANG('content.Renumber All')</label>
			
		</div>
					
		<label for="description" class="control-label">@LANG('gen.Description'):</label>
		<textarea name="description" class="form-control">{{$record->description}}</textarea>

		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Update')</button>
		</div>
		
		<label for="text" class="control-label">@LANG('gen.Text'):</label>
		<textarea style="height:500px" name="text" class="form-control big-text">{{$record->text}}</textarea>
		
		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Update')</button>
		</div>

		{{ csrf_field() }}
		
	</form>

</div>

@stop
