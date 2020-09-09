@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('ui.List')</h1>
               
	<form method="POST" action="/{{$prefix}}/create-user-favorite-list">
							
		<div class="form-group">
			<label for="name" class="control-label">@LANG('ui.Name'):</label>
			<input type="text" name="name" class="form-control" autofocus />
		</div>	
				
		<div class="form-group">
		@component('components.control-dropdown-menu', ['prefix' => $prefix, 
			'isAdmin' => $isAdmin, 
			'prompt' => 'Type: ',
			'empty' => 'Select Type',
			'options' => App\Tag::getTypeFlags(),
			'field_name' => 'type_flag',
			'prompt_div' => true,
			'select_class' => 'form-control form-control-sm',
		])@endcomponent
		</div>
					
		<div class="form-group">
			<div class="submit-button">
				<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Add')</button>
			</div>
		</div>
		
		{{ csrf_field() }}

	</form>

</div>

@endsection
