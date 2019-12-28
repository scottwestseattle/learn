@extends('layouts.app')

@section('content')

@component('components.ready-set-focus', ['controlId' => 'title'])@endcomponent

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin, 'parent_id' => $parent_id])@endcomponent
	
	@if ($lesson)
		@if ($type_flag == WORDTYPE_LESSONLIST)
			<h1>@LANG('ui.Add') @LANG('content.Lesson Vocabulary')</h1>
		@elseif ($type_flag == WORDTYPE_LESSONLIST_USERCOPY)
			<h1>@LANG('ui.Add') @LANG('content.Vocabulary')</h1>
		@else
			<h1>Unknown Parent Add Condition</h1>
		@endif
	@else
		@if ($type_flag == WORDTYPE_USERLIST)
			<h1>@LANG('ui.Add') @LANG('content.Vocabulary')</h1>
		@else
			<h1>Unknown No Parent Add Condition</h1>
		@endif
	@endif

	@component('components.control-accent-chars-esp', ['visible' => true, 'target' => null, 'flat' => true])@endcomponent																		
	
	<form method="POST" action="{{isset($parent_id) ? '/words/create' : '/words/create-user' }}">
	
		<input type="hidden" name="parent_id" value={{$parent_id}} />
		<input type="hidden" name="type_flag" value={{$type_flag}} />
		
		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word or Phrase'):</label>
			<input type="text" id="title" name="title" class="form-control" autocomplete="off" onfocus="setFocus($(this))" />
		<div>
		
		@if (!$lesson)
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

	@if (isset($parent_id))
		@component('components.data-course-words', ['edit' => $lesson ? '/words/edit/' : '/words/edit-user/', 'words' => $records])@endcomponent																				
	@else
		@component('components.data-badge-list', ['edit' => '/words/view/', 'records' => $records, 'title' => 'Vocabulary'])@endcomponent																			
	@endif
	
</div>

@endsection
