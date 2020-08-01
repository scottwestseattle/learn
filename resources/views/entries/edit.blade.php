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

			<div style="float:left; margin: 0 10px 10px 0;"><a href="" onclick="xshow(event, 'en')">en</a></div>
			@foreach($languages as $language)
				<div style="float:left; margin: 0 10px 10px 0;"><a href="" onclick="xshow(event, '{{$language}}')">{{$language}}</a></div>
			@endforeach
			
			<div style="clear:both;"></div>
			
			@component('control-entry-types', ['current_type' => $record->type_flag, 'entryTypes' => $entryTypes])
			@endcomponent
					
			@component('control-dropdown-date', ['div' => true, 'months' => $dates['months'], 'years' => $dates['years'], 'days' => $dates['days'], 'filter' => $filter])@endcomponent		

			<div class="" style="clear:both;">
				<button type="submit" name="update" class="btn btn-primary">Save</button>
			</div>

			<div id="en" style="display:default;">
						
				<div id="copy1" class="form-group" style="margin-top:10px;">
					<a href='#' onclick="javascript:clipboardCopyText(event, 'copy1', 'title')";>
						<span id="" class="glyphCustom glyphicon glyphicon-copy" style="font-size:1.3em; margin-left:5px;"></span>
					</a>		
				</div>				
				<input type="text" id="title" name="title" class="form-control" value="{{ $record->title }}"  placeholder="Title" />

				<div class="entry-title-div">
					<a href='#' onclick="javascript:urlEncodeWithDate('title', 'year', 'month', 'day', 'permalink')";>
						<span id="" class="glyphCustom glyphicon glyphicon-link" style="font-size:1.3em; margin-left:5px;"></span>
					</a>						
					<input type="text" id="permalink" name="permalink" class="form-control" value="{{ $record->permalink }}"  placeholder="Permalink" />
				</div>
						
				<div id="copy2" class="form-group" style="margin-top:10px;">
					<a href='#' onclick="javascript:clipboardCopyText(event, 'copy2', 'description')";>
						<span id="" class="glyphCustom glyphicon glyphicon-copy" style="font-size:1.3em; margin-left:5px;"></span>
					</a>		
				</div>				
						
				<div class="entry-description-div">
					<textarea id="description" name="description" rows="12" class="form-control" placeholder="Description" >{{ $record->description }}</textarea>
				</div>

				<div id="copy3" class="form-group" style="margin-top:10px;">
					<a href='#' onclick="javascript:clipboardCopyText(event, 'copy3', 'description_short')";>
						<span id="" class="glyphCustom glyphicon glyphicon-copy" style="font-size:1.3em; margin-left:5px;"></span>
					</a>		
				</div>				
				
				<div class="entry-description-div">
					<textarea id="description_short" name="description_short" class="form-control entry-description-text" placeholder="Highlights" >{{ $record->description_short }}</textarea>
				</div>
			</div>
			
			<?php $i = 0; ?>
			@foreach($languages as $language)
			<?php $translation = isset($translations[$i]) ? $translations[$i] : null ?>
			<input type="hidden" name="translations[{{$language}}]" value="{{$language}}" />
			
			<div id="{{$language}}" style="display:none;">
				<h1>Translation {{$language}}</h1>			
				
				<input type="text" id="medium_col1[{{$language}}]" name="medium_col1[{{$language}}]" class="form-control" value="{{isset($translation) ? $translation->medium_col1 : null}}"  placeholder="Title {{$language}}" />
														
				<div class="entry-description-div">
					<textarea name="large_col1[{{$language}}]" rows="12" class="form-control" placeholder="Description {{$language}}" >{{isset($translation) ? $translation->large_col1 : null}}</textarea>
				</div>

				<div class="entry-description-div">
					<textarea name="large_col2[{{$language}}]" class="form-control entry-description-text" placeholder="Highlights {{$language}}" >{{isset($translation) ? $translation->large_col2 : null}}</textarea>
				</div>
			</div>
			<?php $i++; ?>
			@endforeach

			<div style="clear:both;">				
				<div class="">
					<button type="submit" name="update" class="btn btn-primary">Save</button>
				</div>
			</div>
			
			{{ csrf_field() }}
		</div>
	</form>
	
</div>

@endsection