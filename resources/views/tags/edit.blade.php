@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">

		<label for="name" class="control-label">@LANG('gen.Name'):</label>
		<input type="text" name="name" class="form-control" value="{{$record->name}}" autofocus></input>	
		
		@if ($allowTypeChange)
			<div class="form-group">
			@component('components.control-dropdown-menu', ['record' => $record, 'prefix' => $prefix, 
				'isAdmin' => $isAdmin, 
				'prompt' => 'Type: ',
				'empty' => 'Select Type',
				'options' => App\Tag::getTypeFlags(),
				'selected_option' => $record->type_flag,
				'field_name' => 'type_flag',
				'prompt_div' => true,
				'select_class' => 'form-control form-control-sm',
			])@endcomponent
			</div>
		@else
			<input name="type_flag" type="hidden" value={{$record->type_flag}} />
		@endif

		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
		</div>

		{{ csrf_field() }}
		
	</form>
	
</div>

@endsection

