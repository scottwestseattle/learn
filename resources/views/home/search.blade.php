@extends('layouts.app')

@section('content')

@component('components.ready-set-focus', ['controlId' => 'searchText'])@endcomponent

<div class="container page-normal">

	<h1>@LANG('ui.Search'){{$isPost ? ' (' . $count . ')' : ''}}</h1>

	<form method="POST" action="/search">
		<div class="form-group form-control-big">
			<input type="text" id="searchText" name="searchText" class="form-control" value="{{$search}}"/>

		</div>
		<div class="form-group">
				<button type="submit" name="submit" class="btn btn-primary">@LANG('ui.Search')</button>
		</div>
		{{ csrf_field() }}
	</form>

</div>

@endsection
