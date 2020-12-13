@extends('layouts.app')

@section('content')

<div class="page-size container">

	@component('entries.menu-submenu', ['record' => $record])@endcomponent
	
	<h1>Set Location</h1>

	<form method="POST" action="/entries/locationupdate/{{ $record->id }}">

		<div class="form-group">
			<h3 name="title" class="">{{$record->title }}</h3>
		</div>
		
		<div class="form-group">
			<label for="location_id">Location:&nbsp;</label>		
			<select name="location_id" id="location_id">
				<option value="-1">(No Location)</option>
				@foreach($locations as $location)
					@if (isset($current_location) && $location->id === $current_location->id)
						<option value="{{$location->id}}" selected>{{$location->name}}</option>
					@else
						<option value="{{$location->id}}">{{$location->name}}</option>
					@endif
				@endforeach
			</select>
		</div>
		
		<div class="form-group">
			<button type="submit" class="btn btn-primary">Set Location</button>
		</div>
	{{ csrf_field() }}
	</form>
</div>
@endsection
