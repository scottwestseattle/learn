@extends('layouts.app')

@section('content')

<div class="container page-normal">

<form method="POST" action="/hasher">

	<div class="form-group">
		<label name="" class="">Enter Text:</label>
		<input type="text" name="hash" id="hash" class="form-control" style="width: 90%; max-width:200px;" value="{{ $hash }}" />
	</div>

	<div id="flash" class="form-group">
		<span id='entry'>{{ $hashed }}</span>
		<a href='#' onclick="clipboardCopy(event, 'entry', 'entry')";>
			<span id="" class="glyphCustom glyphicon glyphicon-copy" style="font-size:1.3em; margin-left:5px; display:{{isset($hashed) && strlen($hashed) > 0 ? 'default' : 'none'}}"></span>
		</a>
		<span id='status'></span>
	</div>

	<div class="form-group">
		<button type="submit" name="submit" class="btn btn-primary">Submit</button>
	</div>

{{ csrf_field() }}
</form>

</div>

<script>

$( document ).ready(function() {
    $( "#hash" ).focus();
});

</script>

@endsection





