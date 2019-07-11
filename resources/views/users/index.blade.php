@extends('layouts.app')

@section('content')

<div class="container page-normal">
	<h1>@LANG('ui.Users') ({{count($records)}})</h1>
	<table class="table">
		<thead>
			<tr>
				<th></th><th>@LANG('ui.Name')</th><th>@LANG('ui.Email')</th><th>@LANG('ui.Type')</th><th>@LANG('ui.Blocked')</th><th>@LANG('ui.Site')</th>
			</tr>
		</thead>
		<tbody>@foreach($records as $record)
			<tr>
				<td style="width:10px;"><a href='/users/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit">Edit</span></a></td>
				<td><a href="/users/view/{{ $record->id }}">{{$record->name}} ({{$record->id}})</a></td>
				<td>{{$record->email}}</td>
				<td>{{$record->user_type}}</td>
				@if ($record->blocked_flag)
					<td>@LANG('ui.yes')</td>
				@else
					<td>@LANG('ui.no')</td>
				@endif				
				<td>{{$record->site_id}}</td>
			</tr>
		@endforeach</tbody>
	</table>
</div>
@endsection
