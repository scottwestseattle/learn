@extends('layouts.app')

@section('content')

<div class="container page-normal">
	
	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin, 'record' => $record])@endcomponent
	
	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	@component('components.control-accent-chars-esp', ['visible' => true, 'flat' => true])@endcomponent																		
	
	<form method="POST" id="form-edit" action="/definitions/update/{{$record->id}}">
		
		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word'):</label>
			<a onclick="event.preventDefault(); $('#title').val(''); $('#title').focus();" href="" tabindex="-1" class="ml-3"><span id="" class="glyphicon glyphicon-remove" ></span></a>			
			<input type="text" id="title" name="title" class="form-control" value="{{$record->title}}" autocomplete="off" onfocus="setFocus($(this))" />
			<div class="mb-2 ml-2">
				<a onclick="event.preventDefault(); translateGoogle($('#title').val());" href="" tabindex="-1" class="small-thin-text">Google</a>
				<a onclick="event.preventDefault(); translateSpanishDict($('#title').val());" href="" tabindex="-1"  class="small-thin-text ml-2">Span!shD¡ct</a>
			</div>
			
			<label for="forms" class="control-label mr-3">@LANG('content.Word Forms'): <span class="small-thin-text">(separate with comma or semi-colon)</span></label>
			<a onclick="event.preventDefault(); $('#forms').val(''); $('#forms').focus();" href="" tabindex="-1"><span id="button-clear" class="glyphicon glyphicon-remove" ></span></a>			
			<input type="text" rows="3" name="forms" id="forms" class="form-control" autocomplete="off" onfocus="setFocus($(this))" value="{{$formsPretty}}" />
			<div class="small-thin-text mb-2 ml-2">{{$record->forms}}</div>

			<label for="conjugations" class="control-label mr-3">@LANG('content.Conjugations'):</label>
			<a onclick="event.preventDefault(); conjugationsGen('#title', '#conjugations');" href="" tabindex="-1" class="ml-2"><span id="button-increment-line" class="glyphicon glyphicon-plus-sign" ></span></a>			
			<a onclick="event.preventDefault(); $('#conjugations').val(''); $('#conjugations').focus();" href="" tabindex="-1" class="ml-2"><span id="button-clear" class="glyphicon glyphicon-remove" ></span></a>			
			<textarea rows="3" name="conjugations" id="conjugations" class="form-control" autocomplete="off" onfocus="setFocus($(this))" >{{$record->conjugations}}</textarea>
			<div class="small-thin-text mb-2 wordwrap m-1">{{$record->conjugations_search}}</div>

		</div>

		<div class="form-group">
			<label for="definition" class="control-label">@LANG('content.Definition'):</label>
			<textarea rows="3" name="definition" id="definition" class="form-control" autocomplete="off" onfocus="setFocus($(this))" >{{$record->definition}}</textarea>
			
			<label for="examples" class="control-label">@LANG('content.Examples'):</label>
			<textarea rows="5" name="examples" id="examples" class="form-control" autocomplete="off" onfocus="setFocus($(this))">{{$record->examples}}</textarea>

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

