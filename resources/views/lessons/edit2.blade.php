@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" action="/{{$prefix}}/update2/{{$record->id}}">

		<h3>{{$record->title}}</h3>
		
		<div class="form-group">
			<div class="submit-button">
				<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Update')</button>
			</div>
		</div>
		
		<textarea style="height:500px" name="text" id="text" class="form-control big-text">{{$record->text}}</textarea>
		
		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Update')</button>
		</div>

		{{ csrf_field() }}
		
	</form>

</div>

@endsection
