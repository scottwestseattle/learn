<script>

function xshow(event, id)
{
	event.preventDefault();
	
	document.getElementById("en").style.display = "none";
	document.getElementById("es").style.display = "none";
	document.getElementById("zh").style.display = "none";
	
	document.getElementById(id).style.display = "initial";		
}

</script>

@extends('layouts.app')

@section('content')

<div class="container page-size">

	@component('entries.menu-submenu', ['record' => $record])@endcomponent	

	<h1>Edit Entry</h1>

	<form method="POST" action="/entries/update/{{ $record->id }}">
		<div class="form-group form-control-big">
		
			<input type="hidden" name="referer" value={{array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER["HTTP_REFERER"] : ''}} />
						
			@component('components.control-entry-types', ['current_type' => $record->type_flag, 'entryTypes' => $entryTypes])
			@endcomponent
					
			@component('components.control-dropdown-date', ['div' => true, 'months' => $dates['months'], 'years' => $dates['years'], 'days' => $dates['days'], 'filter' => $filter])@endcomponent		

			<div class="mb-3" style="clear:both;">
				<button type="submit" name="update" class="btn btn-primary">Save</button>
			</div>

			<div id="en" style="display:default;">
																		
				<div class="mb-1">
					<label class="tiny">Title</label>
					<input onblur="javascript:urlEncode('title', 'permalink')" type="text" id="title" name="title" class="form-control" value="{{ $record->title }}"  placeholder="Title" />
				</div>

				<div class="mb-1" style="font-size:.6em;">
					<a tabindex="-1" href='#' onclick="javascript:urlEncode('title', 'permalink')";>
						<span id="" class="glyphCustom glyphicon glyphicon-link" style="font-size:1.3em; margin-left:5px;"></span>
					</a>
				</div>
					
				<div class="entry-title-div mb-3">
					<input tabindex="-1" type="text" id="permalink" name="permalink" class="form-control" value="{{ $record->permalink }}"  placeholder="Permalink" />
				</div>
			
				<div class="entry-title-div mb-3">
					<label class="tiny">Credit</label>
					@component('components.data-accent-chars-esp')@endcomponent					
					<input type="text" id="source_credit" name="source_credit" placeholder="Source Credit" class="form-control" value="{{$record->source_credit}}" />
				</div>

				<div class="entry-title-div mb-3">
					<label class="tiny">Source Link</label>
					<input type="text" id="source_link" name="source_link" placeholder="Source Link" class="form-control" value="{{$record->source_link}}" />
				</div>

				<div class="entry-description-div mb-3">
					<label class="tiny">Tag Line</label>
					<textarea id="description_short" name="description_short" class="form-control entry-description-text" placeholder="Highlights" >{{ $record->description_short }}</textarea>
				</div>
						
				<div class="entry-description-div mb-3">
					<label class="tiny">Main Text</label>
					<textarea id="description" name="description" rows="12" class="form-control" placeholder="Description" >{{ $record->description }}</textarea>
				</div>
								
			</div>

			<div class="mb-3">
				<button type="submit" name="update" class="btn btn-primary">Save</button>
			</div>
			
			{{ csrf_field() }}
		</div>
	</form>
	
</div>

@endsection