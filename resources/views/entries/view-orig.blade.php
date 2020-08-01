@extends('layouts.app')

@section('content')

@component('menu-submenu')
	@component('menu-icons-start')@endcomponent
	@component('menu-icons-entry') {{ $entry->id }} @endcomponent
	@component('menu-icons-links', ['data' => (isset($data) ? $data : null)])@endcomponent	
	@component('control-search')@endcomponent	
@endcomponent

<div class="page-size container">
               
<form method="POST" action="/entries/{{ $entry->id }}">

	<div class="form-group">
		<h1 name="title" class="">{{$entry->title }}</h1>
	</div>

	<div class="form-group">
		<span name="description" class="">{{$entry->description }}</span>	
	</div>

	<div class="form-group">
		<span name="description_language1" class="">{{$entry->description_language1 }}</span>
	</div>
	
{{ csrf_field() }}
</form>

</div>
@endsection
