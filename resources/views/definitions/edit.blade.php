@extends('layouts.app')

@section('content')

<div class="container page-normal">
	
	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	@component('components.control-accent-chars-esp', ['visible' => true, 'flat' => true])@endcomponent																		
	
	<form method="POST" id="form-edit" action="/definitions/update/{{$record->id}}">
		
		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word or Phrase'):</label>
			<input type="text" id="title" name="title" class="form-control" value="{{$record->title}}" autocomplete="off" onfocus="setFocus($(this))" />
			<label for="forms" class="control-label">@LANG('content.Word Forms'):</label>
			<input type="text" id="forms" name="forms" class="form-control" value="{{$record->forms}}" autocomplete="off" onfocus="setFocus($(this))" />
		</div>

		<div class="form-group">
			<label for="definition" class="control-label">@LANG('content.Definition'):</label>
			<textarea rows="3" name="definition" id="definition" class="form-control" autocomplete="off" onfocus="setFocus($(this))" >{{$record->definition}}</textarea>
			
			<label for="examples" class="control-label">@LANG('content.Examples'):</label>
			<textarea rows="3" name="examples" id="examples" class="form-control" autocomplete="off" onfocus="setFocus($(this))">{{$record->examples}}</textarea>

			<label for="translation_en" class="control-label">@LANG('content.English'):</label>
			<textarea rows="3" name="translation_en" id="translation_en" class="form-control" autocomplete="off" onfocus="setFocus($(this))" >{{$record->translation_en}}</textarea>

			<label for="translation_es" class="control-label">@LANG('content.Spanish'):</label>
			<textarea rows="3" name="translation_es" id="translation_es" class="form-control" autocomplete="off" onfocus="setFocus($(this))" >{{$record->translation_es}}</textarea>


		<div>
		
		<div class="form-group">
			<div class="submit-button">
				<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
			</div>
		</div>

		{{ csrf_field() }}
		
	</form>
	
</div>

@endsection

