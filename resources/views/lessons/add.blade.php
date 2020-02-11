@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('content.' . $title)</h1>

	<form method="POST" action="/{{$prefix}}/create">

        @if (false && $course->isTimedSlides())
            <input type="hidden" name="parent_id" id="parent_id" value="{{$course->id}}" />
        @else
            <div class="form-group">
                <label for="parent_id" class="control-label">@LANG('content.Course'):</label>
                <select name="parent_id" class="form-control">
                    <option value="0">(@LANG('content.Select Course'))</option>
                    @foreach ($courses as $record)
                        <option value="{{$record->id}}" {{ isset($course->id) && $record->id == $course->id ? 'selected' : ''}}>{{$record->title}}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!--------------------------------------------------------------------------->
        <!-- Lesson Type Dropdown -->
        <!--------------------------------------------------------------------------->

        @if ($course->isTimedSlides())
            <input type="hidden" name="type_flag" id="type_flag" value="{{LESSONTYPE_TIMED_SLIDES}}" />
        @else
        <div class="form-group">
        @component('components.control-dropdown-menu', ['record' => $course, 'prefix' => $prefix,
            'isAdmin' => $isAdmin,
            'prompt' => 'Lesson Type: ',
            'empty' => 'Select Lesson Type',
            'options' => App\Lesson::getTypes(),
            'selected_option' => null,
            'field_name' => 'type_flag',
            'prompt_div' => true,
            'select_class' => 'form-control',
        ])@endcomponent
        </div>
        @endif

        <!--------------------------------------------------------------------------->
        <!-- Main Photo -->
        <!--------------------------------------------------------------------------->

        <div class="form-group">
        @component('components.control-dropdown-photos', [
            'record' => $record,
            'prefix' => $prefix,
            'prompt' => 'Main Photo: ',
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
            <img id="photo" width="200" src="" />
            <input id="main_photo" name="main_photo" type="hidden" value="default" />
        </div>

		<div class="form-group">
			<label for="title" class="control-label">@LANG('gen.Title'):</label>
			<input type="text" name="title" id="title" class="form-control" />
		</div>

		<div class="form-group">
			<label for="seconds" class="control-label">@LANG('gen.Seconds'):</label>
			<input type="number" name="seconds" class="form-control" />
		<div>

		<div class="form-group">
			<label for="break_seconds" class="control-label">@LANG('gen.Break Seconds'):</label>
			<input type="number" name="break_seconds" class="form-control" />
		<div>

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

<script>

function showMainPhoto(id)
{
    setLessonMainPhoto(id, "{{$photoPath}}", "photo", "photo-div", "main_photo", "title");
}

</script>
