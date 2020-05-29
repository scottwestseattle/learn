@extends('layouts.app')

@section('content')

<div class="container page-normal">

@if (false)
	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
@endif

	<h3>History ({{count($records)}})</h3>
	
	<table class="table table-responsive table-striped">
		<tbody>
		@foreach($records as $record)
			<tr>
				<td><a href="/history/delete/{{$record->id}}"><span class="glyphCustom-sm glyphicon glyphicon-trash"></span></a></td>
				<td>{{$record->created_at}}</td>
				<td>{{$record->program_name}}</td>
				<td>{{$record->session_name}}</td>
			</tr>
		@endforeach
		</tbody>
	</table>

</div>
@endsection
