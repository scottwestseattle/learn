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
			<a onclick="event.preventDefault(); $('#title').val(''); $('#title').focus();" href="" tabindex="-1" class="ml-3"><span id="" class="glyphicon glyphicon-remove" ></span></a>			
			<input type="text" id="title" name="title" value="{{$word}}" class="form-control" autocomplete="off"  onfocus="setFocus($(this), '#accent-chars'); $('#wordexists').html('');" onblur="wordExists($(this))" autofocus />
			<div id="wordexists" class="small-thin-text ml-2 mb-2"></div>
			<div class="mb-2 ml-2">
				<a onclick="translateOnWebsite(event, 'google', $('#title').val());" href="" tabindex="-1" class="small-thin-text">Google</a>
				<a onclick="translateOnWebsite(event, 'spanishdict', $('#title').val());" href="" tabindex="-1"  class="small-thin-text ml-2">Span!shD¡ct</a>
				<a onclick="translateOnWebsite(event, 'wordreference', $('#title').val());" href="" tabindex="-1"  class="small-thin-text ml-2">WR</a>
			</div>
			
			<label for="forms" class="control-label">@LANG('content.Word Forms'): <span class="small-thin-text">(comma or semi-colon)</span></label>
			<a onclick="wordFormsGen(event, '#title', '#forms', true);" href="" tabindex="-1" class="ml-2"><div class="middle mb-2"><b>+s</b></div></a>
			<a onclick="wordFormsGen(event, '#title', '#forms');" href="" tabindex="-1" class="ml-2"><span class="glyphicon glyphicon-plus-sign" ></span></a>			
			<a onclick="event.preventDefault(); $('#forms').val(''); $('#forms').focus();" href="" tabindex="-1" class="ml-2"><span id="button-clear" class="glyphicon glyphicon-remove" ></span></a>			
			<input type="text" rows="3" name="forms" id="forms" class="form-control" autocomplete="off" onfocus="setFocus($(this), '#accent-chars')" />

			<label for="definition" class="control-label">@LANG('content.Definition'):</label>
			<textarea rows="3" name="definition" id="definition" class="form-control" autocomplete="off" onfocus="setFocus($(this), '#accent-chars')" ></textarea>
						
			<label for="translation_en" class="control-label">@LANG('ui.Translation'):</label>
			<textarea rows="3" name="translation_en" id="translation_en" class="form-control" onfocus="setFocus($(this))" ></textarea>

			<label for="examples" class="control-label">@LANG('content.Examples'):</label>
			<textarea rows="5" name="examples" id="examples" class="form-control" onfocus="setFocus($(this), '#accent-chars')"></textarea>

			<label for="conjugations" class="control-label mr-3">@LANG('content.Conjugations'):  <span class="small-thin-text">(regular verbs only)</span></label></label>
			<a onclick="event.preventDefault(); conjugationsGen('#title', '#conjugations');" href="" tabindex="-1" class="ml-2"><span id="button-increment-line" class="glyphicon glyphicon-plus-sign" ></span></a>			
			<a onclick="event.preventDefault(); $('#conjugations').val(''); $('#conjugations').focus();" href="" tabindex="-1" class="ml-2"><span id="button-clear" class="glyphicon glyphicon-remove" ></span></a>			
			<textarea rows="3" name="conjugations" id="conjugations" class="form-control" autocomplete="off" onfocus="setFocus($(this))" ></textarea>

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
