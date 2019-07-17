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
	
	<a href="/lessons/view/{{$firstId}}">
		<button type="button" style="text-align:center; font-size: 1.3em; color:white;" class="btn btn-info btn-lesson-index" {{$disabled}}>Start at the beginning</button>	
	</a>
	
	<h1>@LANG('content.Lessons') ({{count($records)}})
	@if ($isAdmin)
		<span style="font-size:.6em;"><a href="/lessons/admin"><span class="glyphCustom glyphicon glyphicon-admin"></span></a></span>
	@endif	
	</h1>
	
	@foreach($records as $record)
	<a href="/lessons/view/{{$record->id}}">
		<button style="" type="button" class="btn btn-outline-info btn-lesson-index link-dark">
			<span style="font-size:.8em;">{{$record->lesson_number}}.{{$record->section_number}}</span>&nbsp;
			{{$record->title}}<br/>
			<span style="font-size:.9em">{{$record->description}}</span>	
		</button>
	</a>
	@endforeach
	
@if (false)
	<table class="table xtable-bordered table-responsive">
		@foreach($records as $record)
			<tr class="table-info">
				<td>
					<span style="font-size:.8em;">{{$record->lesson_number}}.{{$record->section_number}}</span>&nbsp;
					<a href="/lessons/view/{{$record->id}}">{{$record->title}}</a><br/>
					<span style="font-size:.9em">{{$record->description}}</span>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>														
@endif

</div>
@endsection
