@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-md" role="button" href="/{{$prefix}}/">@LANG('content.Back to Course List')
		<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	</div>
	
	<h3 name="title" class="">{{$record->title }}</h3>

	<p>{{$record->description }}</p>
	
	<h1>@LANG('content.Lessons') ({{count($records)}})
	
	@if ($isAdmin)
		<span style="font-size:.6em;"><a href="/lessons/admin"><span class="glyphCustom glyphicon glyphicon-admin"></span></a></span>
	@endif	
	</h1>
	
	<table class="table xtable-bordered table-responsive">
		@foreach($records as $record)
			<tr>
				<th>{{$record->lesson_number}}.{{$record->section_number}}</th>
				<td><a href="/lessons/view/{{$record->id}}">{{$record->title}}</a></td>
				<td>{{$record->description}}</td>				
			</tr>
		@endforeach
		</tbody>
	</table>														
	

</div>
@endsection
