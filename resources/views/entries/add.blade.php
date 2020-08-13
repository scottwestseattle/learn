@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component('entries.menu-submenu')@endcomponent	
	
	<h1>Add</h1>
               
	<form method="POST" action="/entries/create">
		<div class="form-control-big">	

			@if (isset($type_flag))
				<input type="hidden" name="type_flag" value="{{$type_flag}}">
			@else
				@component('components.control-entry-types', ['entryTypes' => $entryTypes, 'current_type' => 2])@endcomponent	
			@endif
				
			@if (isset($parent_id))
				<input type="hidden" name="parent_id" value="{{$parent_id}}">
			@endif

			@if (isset($type_flag) && $type_flag == ENTRY_TYPE_LESSON)
			@else
				@component('components.control-dropdown-date', ['div' => true, 'months' => $dates['months'], 'years' => $dates['years'], 'days' => $dates['days'], 'filter' => $filter])@endcomponent		
			@endif
			
			<div class="entry-title-div mb-3 mt-2">			
				<input onblur="javascript:urlEncode('title', 'permalink')" type="text" id="title" name="title" placeholder="Title" onfocus="setFocus($(this))" class="form-control" autofocus />
			</div>

			<div class="entry-title-div mb-3">
				<input tabindex="-1" type="text" id="permalink" name="permalink" class="form-control"  placeholder="Permalink" />
			</div>			

			<div class="entry-title-div mb-3">
				@component('components.control-accent-chars-esp', ['visible' => true, 'flat' => true])@endcomponent																						
				<input type="text" id="source_credit" name="source_credit" placeholder="Source Credit" onfocus="setFocus($(this))" class="form-control" />
			</div>

			<div class="entry-title-div mb-3">
				<input type="text" id="source_link" name="source_link" placeholder="Source Link" class="form-control" />
			</div>

			<div class="entry-description-div mb-3">
				<textarea rows="3" id="description_short" name="description_short" class="form-control" placeholder="Summary" onfocus="setFocus($(this))"></textarea>	
			</div>
			
			<div class="mt-3 mb-3">
				<button tabindex="-1" type="submit" name="update" class="btn btn-primary">Add</button>
			</div>	
			
			<div class="entry-description-div mb-3">
				<textarea rows="12" id="description" name="description" class="form-control" placeholder="Description" onfocus="setFocus($(this))"></textarea>	
			</div>
			
			<div style="margin:20px 0;">
				<button tabindex="-1" type="submit" name="update" class="btn btn-primary">Add</button>
			</div>	
						
			{{ csrf_field() }}
		</div>
	</form>

</div>
@endsection

