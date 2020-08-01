@extends('layouts.app')

@section('content')

<div class="container page-size">

	@component('entries.menu-submenu')@endcomponent	

	@if (isset($title))
		<h3>{{$title}}</h3>
	@endif
	
	<h1>Add Entry</h1>
               
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
			
			<div class="entry-title-div">
				<input type="text" id="title" name="title" placeholder="Title" class="form-control" autofocus />
			</div>

			<div class="entry-title-div">
				<a href='#' onclick="javascript:urlEncodeWithDate('title', 'year', 'month', 'day', 'permalink')";>
					<span id="" class="glyphCustom glyphicon glyphicon-link" style="font-size:1.3em; margin-left:5px;"></span>
				</a>						
				<input type="text" id="permalink" name="permalink" class="form-control"  placeholder="Permalink" />
			</div>			

			<div style="margin:20px 0;">
				<button type="submit" name="update" class="btn btn-primary">Add</button>
			</div>	

			<div class="entry-title-div mb-3">
				<input type="text" id="source_credit" name="source_credit" placeholder="Source Credit" class="form-control" />
			</div>

			<div class="entry-title-div mb-3">
				<input type="text" id="source_link" name="source_link" placeholder="Source Link" class="form-control" />
			</div>
			
			<div class="entry-description-div mb-3">
				<textarea rows="12" name="description" class="form-control" placeholder="Description"></textarea>	
			</div>

			<div class="entry-description-div">
				<textarea rows="12" name="description_short" class="form-control" placeholder="Summary"></textarea>	
			</div>
			
			<div style="margin:20px 0;">
				<button type="submit" name="update" class="btn btn-primary">Add</button>
			</div>	
						
			{{ csrf_field() }}
		</div>
	</form>

</div>
@endsection

@if (isset($type_flag) && $type_flag == ENTRY_TYPE_BLOG_ENTRY)
<script>
		
window.onload = function(){
	var input = document.getElementById("title");

	if (!input)
		return;
		
	// for blog entries, put the date in the title field as a starting title
	var d = new Date().getDay();
	var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	
	input.value = days[d] + ', ' + $("#month option:selected").text() + ' ' + $("#day option:selected").text() + ', ' + $("#year option:selected").text();
};

</script>
@endif
