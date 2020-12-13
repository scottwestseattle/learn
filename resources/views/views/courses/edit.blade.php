@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">

		<div class="form-group">
			<label for="title" class="control-label">@LANG('gen.Title'):</label>
			<input type="text" name="title" class="form-control" value="{{$record->title}}"></input>	
		</div>

		<div class="form-group">
			<label for="display_order" class="control-label">@LANG('content.Display Order'):</label>
			<input type="number"  min="0" max="1000" step="1" name="display_order" class="form-control form-control-100" value="{{$record->display_order}}"></input>	
		</div>
		
		<div class="form-group">
		@component('components.control-dropdown-menu', ['record' => $record, 'prefix' => $prefix, 
			'isAdmin' => $isAdmin, 
			'prompt' => 'Course Type: ',
			'empty' => 'Select Course Type',
			'options' => App\Course::getTypes(),
			'selected_option' => $record->type_flag,
			'field_name' => 'type_flag',
			'prompt_div' => true,
			'select_class' => 'form-control form-control-sm',
		])@endcomponent
		</div>
		
		<label for="description" class="control-label">@LANG('gen.Description'):</label>
		<textarea name="description" class="form-control">{{$record->description}}</textarea>

		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
		</div>

		{{ csrf_field() }}
		
	</form>
	
</div>

@endsection
