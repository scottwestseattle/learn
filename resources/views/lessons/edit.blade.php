@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title) - {{$record->title}}</h1>

	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">

    @if ($record->isText())
		<ul class="nav nav-tabs">
			<li class="nav-item">
				<a id="nav-link-text" class="nav-link active" href="#" onclick="setTab(event, 1);">@LANG('gen.Text')</a>
			</li>
			<li class="nav-item">
				<a id="nav-link-title" class="nav-link" href="#" onclick="setTab(event, 2);"><span class="glyphCustom glyphicon glyphicon-cog"></span><!-- @LANG('ui.Title')--></a>
			</li>
			<li class="nav-item">
				<button type="submit" name="update" style="margin-top:5px; margin-left:5px;" class="btn btn-sm btn-primary">@LANG('ui.Save')</button>
			</li>
			<li class="nav-item">
				<a class="nav-link" href='/{{$prefix}}/edit2/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-pencil"></span></a>
			</li>
			<li class="nav-item">
				@component('components.control-accent-chars-esp', ['target' => 'text', 'visible' => true, 'tinymce' => true, 'flat' => true])@endcomponent
			</li>
		</ul>
	@else
		<div class="submit-button mb-3">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
		</div>
	@endif

		<div style="{{$record->isText() ? 'display:none;' : ''}}" id="tab-title">

            @if ($record->isTimedSlides())
			<!--------------------------------------------------------------------------->
			<!-- Main Photo -->
			<!--------------------------------------------------------------------------->

			<div class="form-group">
			@component('components.control-dropdown-photos', [
			    'record' => $record,
			    'prefix' => $prefix,
				'prompt' => 'Select Exercise',
				'empty' => 'Select Main Photo',
				'options' => App\Tools::getPhotos($photoPath),
				'selected_option' => $record->main_photo,
				'field_name' => 'main_photo',
				'prompt_div' => true,
				'select_class' => 'form-control',
				'onchange' => 'showMainPhoto',
				'noSelection' => 'none.png',
			])@endcomponent
			</div>

			<!-- Photo Preview -->
			<div id="photo-div" class="form-group" style="">
                <img id="photo" width="200" src="{{$photoPath}}{{$record->main_photo}}" />
                <input id="main_photo" name="main_photo" type="hidden" value="{{$record->main_photo}}" />
            </div>

			<div class="form-group">
				<label for="title" class="control-label">@LANG('gen.Title'):</label>
				<input type="text" id="title" name="title" class="form-control" value="{{$record->title}}"></input>
			</div>

			<!-- Seconds -->
			<div class="form-group">
                <label for="seconds" class="control-label">@LANG('gen.Seconds'):</label>
                <input type="number" min="0" step="5" name="seconds" id="seconds" class="form-control form-control-100"  value="{{$record->seconds}}" />
				@component('components.control-numinc', ['id' => 'seconds', 'multiple' => 5])@endcomponent
            </div>

			<!-- Break Seconds -->
			<div class="form-group">
				<label for="break_seconds" class="control-label">@LANG('gen.Break Seconds'):</label>
				<input type="number" min="0" step="5" name="break_seconds" id="break_seconds" class="form-control form-control-100"  value="{{$record->break_seconds}}" />
				@component('components.control-numinc', ['id' => 'break_seconds', 'multiple' => 5])@endcomponent
            </div>

            @if (false)
			<!-- Reps -->
			<div class="form-group">
                <label for="reps" class="control-label">@LANG('gen.Reps'):</label>
                <input type="number" name="reps" class="form-control" value="{{$record->reps}}" />
            </div>
            @endif

            <div class="submit-button mb-3">
                <button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
            </div>

            @else

			<div class="form-group">
				<label for="parent_id" class="control-label">@LANG('content.Course'):</label>
				<select name="parent_id" class="form-control">
					<option value="0">(@LANG('content.Select Course'))</option>
					@foreach ($courses as $course)
						<option value="{{$course->id}}" {{ $course->id == $record->parent_id ? 'selected' : ''}}>{{$course->title}}</option>
					@endforeach
				</select>
			</div>

			<div class="form-group">
				<label for="title" class="control-label">@LANG('gen.Title'):</label>
				<input type="text" id="title" name="title" class="form-control" value="{{$record->title}}"></input>
			</div>

            @endif

			<!--------------------------------------------------------------------------->
			<!-- Chapter / Section -->
			<!--------------------------------------------------------------------------->

			<div class="form-group">
				<label for="lesson_number" class="control-label">@LANG('content.Chapter'):</label>
				<input type="number" min="1" name="lesson_number" id="lesson_number" class="form-control form-control-100" value="{{$record->lesson_number}}" />
				@component('components.control-numinc', ['id' => 'lesson_number', 'multiple' => 1])@endcomponent
			</div>

			<div class="form-group">
				<label for="section_number" class="control-label">@LANG('content.Section'):</label>
				<input type="number" min="0" name="section_number" id="section_number" class="form-control form-control-100" value="{{$record->section_number}}" />
				@component('components.control-numinc', ['id' => 'section_number', 'multiple' => 1])@endcomponent
			</div>

			<div class="form-group">
				<input type="checkbox" name="renumber_flag" id="renumber_flag" class="" />
				<label for="renumber_flag" class="checkbox-big-label">@LANG('content.Renumber All')</label>
				&nbsp;
				<input type="hidden" name="format_flag" value="{{$record->format_flag}}" />
				<input type="checkbox" name="autoformat" id="autoformat" {{$record->format_flag == LESSON_FORMAT_AUTO ? 'checked' : ''}} />
				<label for="autoformat" class="checkbox-big-label">@LANG('content.Auto-format')</label>
			</div>

			<!--------------------------------------------------------------------------->
			<!-- Chapter Title - only used for the first lesson in a chapter -->
			<!--------------------------------------------------------------------------->
			<div class="form-group">
    			<label for="title_chapter" class="control-label">@LANG('gen.Chapter Title'):</label>
	    		<input type="text" name="title_chapter" class="form-control" value="{{$record->title_chapter}}" />
            </div>

			<!--------------------------------------------------------------------------->
			<!-- Description -->
			<!--------------------------------------------------------------------------->
			<div class="form-group">
		    	<label for="description" class="control-label">@LANG('gen.Description'):</label>
			    <textarea name="description" class="form-control">{{$record->description}}</textarea>
            </div>

			<!--------------------------------------------------------------------------->
			<!-- Options -->
			<!--------------------------------------------------------------------------->
			<div class="form-group">
    			<label for="options" class="control-label">@LANG('content.Options'):</label>
	    		<input type="text" name="options" class="form-control" value="{{$record->options}}" />
            </div>


            @if ($record->isTimedSlides())

			<div class="form-group">
				<label for="parent_id" class="control-label">@LANG('content.Course'):</label>
				<select name="parent_id" class="form-control">
					<option value="0">(@LANG('content.Select Course'))</option>
					@foreach ($courses as $course)
						<option value="{{$course->id}}" {{ $course->id == $record->parent_id ? 'selected' : ''}}>{{$course->title}}</option>
					@endforeach
				</select>
			</div>

            @endif

			<!--------------------------------------------------------------------------->
			<!-- Lesson Type Dropdown -->
			<!--------------------------------------------------------------------------->

			<div class="form-group">
			@component('components.control-dropdown-menu', ['record' => $record, 'prefix' => $prefix,
				'isAdmin' => $isAdmin,
				'prompt' => 'Lesson Type: ',
				'empty' => 'Select Lesson Type',
				'options' => App\Lesson::getTypes(),
				'selected_option' => $record->type_flag,
				'field_name' => 'type_flag',
				'prompt_div' => true,
				'select_class' => 'form-control',
			])@endcomponent
			</div>

		</div>

    @if ($record->isText())
		<div id="tab-text">

			<div id ="rich" style="clear:both;display:default;">
				<textarea style="height:500px" name="text" id="text" class="form-control big-text">{{$record->text}}</textarea>
			</div>

		</div>
    @endif

		@if (false)
			<button onclick="event.preventDefault(); saveAndStay();" name="update" class="btn btn-success">Save and Stay</button>
		@endif

		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
		</div>

		{{ csrf_field() }}

		<div id ="preview" style="display:none;">
		</div>

	</form>

</div>

@endsection

<script>

function showMainPhoto(id)
{
    setLessonMainPhoto(id, "{{$photoPath}}", "photo", "photo-div", "main_photo", "title");
}

</script>
