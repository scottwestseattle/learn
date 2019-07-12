@extends('layouts.app')

@section('content')

<div class="container page-normal">
	
	@component($prefix . '.menu-submenu', ['prefix' => $prefix])@endcomponent

	<h1>@LANG('content.' . $titlePlural) ({{count($records)}})</h1>

	<table class="table">
		<thead>
			<tr>
				<th>@LANG('gen.Title')</th><th>@LANG('gen.Description')</th>
			</tr>
		</thead>
		<tbody>
		@foreach($records as $record)
			<tr>					
				<td><a href="/{{$prefix}}/view/{{$record->id}}">{{$record->getDisplayNumber()}}&nbsp;{{$record->title}}</a></td>
				<td>{{$record->description}}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
               
</div>
@endsection
