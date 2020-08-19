@extends('layouts.app')

@section('content')

@component('components.ready-set-focus', ['controlId' => 'title'])@endcomponent

<div class="container page-normal">


	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Add') @LANG('content.Definition')</h1>

	@component('components.control-accent-chars-esp', ['visible' => true, 'target' => null, 'flat' => true])@endcomponent

	<form method="POST" action="/definitions/create">

		<div class="form-group">
			<label for="title" class="control-label">@LANG('content.Word or Phrase'):</label>
			<input type="text" id="title" name="title" class="form-control" autocomplete="off"  onfocus="setFocus($(this))" autofocus />
		<div>

		<div class="form-group">
			<label for="description" class="control-label">@LANG('content.Translation, Definition, or Hint'):</label>
			<textarea rows="3" name="description" id="description" class="form-control" autocomplete="off" onfocus="setFocus($(this))" ></textarea>
			<label for="examples" class="control-label">@LANG('content.Examples'):</label>
			<textarea rows="3" name="examples" id="examples" class="form-control" autocomplete="off" onfocus="setFocus($(this))"></textarea>
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
