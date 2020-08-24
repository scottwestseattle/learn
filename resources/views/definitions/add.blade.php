@extends('layouts.app')

@section('content')

@component('components.ready-set-focus', ['controlId' => 'title'])@endcomponent

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('content.Definition')</h1>

	@component('components.control-accent-chars-esp', ['visible' => true, 'target' => null, 'flat' => true])@endcomponent

	<form method="POST" action="/definitions/create">

		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word'):</label>
			<input type="text" id="title" name="title" value="{{$word}}" class="form-control" autocomplete="off"  onfocus="setFocus($(this)); $('#wordexists').html('');" onblur="wordExists($(this))" autofocus />
			<div id="wordexists" class="mb-2"></div>
			
			<label for="forms" class="control-label">@LANG('content.Word Forms'): <span class="small-thin-text">(separate with comma, space, or semi-colon)</span></label>
			<a onclick="event.preventDefault(); $('#forms').val(''); $('#forms').focus();" href="" tabindex="-1'><span id="button-clear" class="glyphicon glyphicon-remove" ></span></a>			
			<input type="text" rows="3" name="forms" id="forms" class="form-control" autocomplete="off" onfocus="setFocus($(this))"  value="{{$word}}"/>

			<label for="conjugations" class="control-label">@LANG('content.Conjugations'):</label>
			<a onclick="event.preventDefault(); getVerbForms('#title', '#conjugations');" href="" tabindex="-1'><span id="button-increment-line" class="glyphicon glyphicon-plus-sign" ></span></a>			
			<a onclick="event.preventDefault(); $('#conjugations').val(''); $('#conjugations').focus();" href="" tabindex="-1'><span id="button-clear" class="glyphicon glyphicon-remove" ></span></a>			
			<textarea rows="3" name="conjugations" id="conjugations" class="form-control" autocomplete="off" onfocus="setFocus($(this))" ></textarea>


		<div>

		<div class="form-group">
			<label for="definition" class="control-label">@LANG('content.Definition'):</label>
			<textarea rows="3" name="definition" id="definition" class="form-control" autocomplete="off" onfocus="setFocus($(this))" ></textarea>
			
			<label for="examples" class="control-label">@LANG('content.Examples'):</label>
			<textarea rows="5" name="examples" id="examples" class="form-control" onfocus="setFocus($(this))"></textarea>
			
			<label for="translation_en" class="control-label">@LANG('content.English'):</label>
			<textarea rows="3" name="translation_en" id="translation_en" class="form-control" onfocus="setFocus($(this))" ></textarea>

			<label for="translation_es" class="control-label">@LANG('content.Spanish'):</label>
			<textarea rows="3" name="translation_es" id="translation_es" class="form-control" onfocus="setFocus($(this))" ></textarea>			
			
		<div>

		<div class="form-group">
			<div class="submit-button">
				<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Add')</button>
			</div>
		</div>

		{{ csrf_field() }}

	</form>

</div>

@endsection
