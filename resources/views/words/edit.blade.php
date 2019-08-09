@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin, 'parent_id' => $record->parent_id])@endcomponent

	<div class="form-group">
	@if (isset($record->parent_id))
		<a href="/lessons/view/{{$record->parent_id}}"><button class="btn btn-success">@LANG('content.Back to Lesson')</button></a>
	@else
		<a href="/home"><button class="btn btn-success">@LANG('content.Back to Home')</button></a>
	@endif
	</div>
	
	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	@component('components.control-accent-chars-esp', ['visible' => true, 'target' => 'title'])@endcomponent																		
	
	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">
		
		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word'):</label>
			<input type="text" id="title" name="title" class="form-control" value="{{$record->title}}" ></input>	
		</div>

		@if ($record->type_flag == WORDTYPE_USERLIST)
		<div class="form-group">
			<label for="description" class="control-label">@LANG('content.Translation, Definition, or Hint'):</label>
			<textarea name="description" class="form-control">{{$record->description}}</textarea>
		<div>
		@endif
		
		<div class="form-group">
			<div class="submit-button">
				<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
			</div>
		</div>

		{{ csrf_field() }}
		
	</form>
	
	@component('components.data-badge-list', ['edit' => '/words/edit/', 'records' => $records])@endcomponent																		
	
</div>

@endsection

