@extends('layouts.app')

@section('content')

<div class="container page-normal">
	<h2 style="">Admin Dashboard</h2>

	<div style="margin-bottom:40px;">
		<ul style="font-size: 1.1em; list-style-type: none; padding-left: 0px;">
			<li>Time: {{date("Y-m-d H:i:s")}}</li>
			@if (false)
			<li>Site: {{$site->site_name}}, id: {{$site->id}}</li>
			@endif
			<li>My IP:&nbsp;{{$ip}}</li>
			<li>{{base_path()}}</li>
			<li>Debug:&nbsp;{{(NULL != env('APP_DEBUG')) ? 'ON' : 'OFF'}}</li>
			<li>New Visitor:&nbsp;{{$new_visitor ? 'Yes' : 'No'}}
				&nbsp;&nbsp;<a href="/eunoticereset">EU Notice</a>
				&nbsp;&nbsp;<a href="/hash">Hash</a>				
			</li>
		</ul>
	</div>
	
	@if (isset($comments))
	<div>	
		<h3 style="color:red;">Comments to Approve ({{count($comments)}})</h3>
		<table class="table table-striped">
			<tbody>
				<tr><th></th><th>Created</th><th>Name</th><th>Comment</th><th></th></tr>
				@foreach($comments as $record)
					<tr>
						<td style="width:10px;"><a href='/comments/publish/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-flash"></span></a></td>
						<td>{{$record->created_at}}</td>
						<td><a href="/comments/publish/{{ $record->id }}">{{$record->name}}</a></td>
						<td>{{$record->comment}}</td>
						<td><a href='/comments/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-trash"></span></a></td>
					</tr>
				@endforeach
			</tbody>
		</table>
		<a href="/comments/indexadmin">Show All Comments</a>
	</div>
	<hr />
	@endif
	
	@if (isset($visitors))			
	<div style="margin-bottom:50px;">
		<h3 style="">Today's Visitors: {{count($visitors)}}</h3>
		<p><a href="/visitors">Show All Visitors</a></p>
	</div>
	@endif
	
	@if (count($users) > 0)
	<div>	
		<h3 style="">Last New User ({{count($users)}} Total)</h3>
		<table class="table table-striped">
			<tbody>
			@foreach($users as $record)
				<tr>
					<td style="width:10px;"><a href='/users/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
					<td>{{$record->created_at}}</td>
					<td><a href="/users/view/{{ $record->id }}">{{$record->name}}</a></td>
					<td>{{$record->email}}</td>
					<td>{{$record->user_type}}</td>
					<td><a href='/entries/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-trash"></span></a></td>
				</tr>
				@break
			@endforeach
			</tbody>
		</table>
		<a href="/users/index">Show All Users</a>
	</div>
	<hr />
	@endif
		
	<div>
		<h3 style="">Latest Events ({{count($events)}})</h3>
@if (false)		
		@component('events.menu-submenu-events-filter')@endcomponent	
@endif
		<table class="table table-striped">
			<tbody>
				<tr>
					<th>Timestamp</th>
					<th>Site</th>
					<th>Type</th>
					<th>Model</th>
					<th>Action</th>
					<th>Title</th>
				</tr>
			@foreach($events as $record)
				<?php
					$type = '';
					if ($record->type_flag == 1) $type = 'Info';
					if ($record->type_flag == 2) $type = 'Warning';
					if ($record->type_flag == 3) $type = 'Error';
					if ($record->type_flag == 4) $type = 'Exception';
					if ($record->type_flag == 5) $type = 'Other';
				?>
				
				<tr>
					<td>{{$record->created_at}}</td>
					<td>{{$record->site_id}}</td>
					<td>{{$type}}</td>
					<td>{{$record->model_flag}}</td>
					<td>{{$record->action_flag}}</td>
					<td>{{$record->title}}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
		<a href="/events">Show All Events</a>
	</div>
	<hr />
	
</div>
@endsection
