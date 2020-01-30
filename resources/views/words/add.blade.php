@extends('layouts.app')

@section('content')

@component('components.ready-set-focus', ['controlId' => 'title'])@endcomponent

<div class="container page-normal">


	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin, 'parent_id' => $lesson_id != null ? $lesson_id : $vocab_list_id])@endcomponent

    @if ($type_flag == WORDTYPE_LESSONLIST)
        <h1>@LANG('ui.Add') @LANG('content.Lesson Vocabulary')</h1>
    @elseif ($type_flag == WORDTYPE_LESSONLIST_USERCOPY)
        <h1>@LANG('ui.Add') @LANG('content.Vocabulary')</h1>
    @elseif ($type_flag == WORDTYPE_USERLIST)
        <h1>@LANG('ui.Add') @LANG('content.Vocabulary')</h1>
    @elseif ($type_flag == WORDTYPE_VOCABLIST)
        <h1>@LANG('ui.Add') @LANG('content.Vocabulary List Word')</h1>
    @else
        <h1>Unknown Word Type</h1>
    @endif

    @if (isset($parent_title))
        <h4>{{$parent_title}}</h4>
    @endif

	@component('components.control-accent-chars-esp', ['visible' => true, 'target' => null, 'flat' => true])@endcomponent

	<form method="POST" action="{{$postAction}}">

		<input type="hidden" name="lesson_id" value={{$lesson_id}} />
		<input type="hidden" name="vocab_list_id" value={{$vocab_list_id}} />
		<input type="hidden" name="type_flag" value={{$type_flag}} />

		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word or Phrase'):</label>
			<input type="text" id="title" name="title" class="form-control" autocomplete="off" autofocus />
		<div>

		@if (!isset($lesson) || !$lesson)
		<div class="form-group">
			<label for="description" class="control-label">@LANG('content.Translation, Definition, or Hint'):</label>
			<textarea rows="3" name="description" id="description" class="form-control" autocomplete="off" onfocus="setFocus($(this))" ></textarea>
			<label for="examples" class="control-label">@LANG('content.Examples'):</label>
			<textarea rows="3" name="examples" class="form-control" autocomplete="off" ></textarea>
		<div>
		@endif

		<div class="form-group">
			<div class="submit-button">
				<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Add')</button>
			</div>
		</div>

		{{ csrf_field() }}

	</form>

    @if ($type_flag != WORDTYPE_VOCABLIST)
        @if (isset($lesson_id))
            @component('components.data-course-words', ['edit' => $lesson ? '/words/edit/' : '/words/edit-user/', 'words' => $records])@endcomponent
        @else
            @component('components.data-badge-list', ['edit' => '/words/view/', 'records' => $records, 'title' => 'Vocabulary'])@endcomponent
        @endif
	@endif

</div>

@endsection
