@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component('home.menu-submenu', ['isAdmin' => $isAdmin])@endcomponent

	<h2 style="">{{$isSuperAdmin ? 'Super ' : ''}}Admin Dashboard</h2>

	<div class="form-group">	
		<ul style="font-size: 1.1em; list-style-type: none; padding-left: 0px;">
			<li>Time: {{date("Y-m-d H:i:s")}}</li>
			@if (false)
			<li>Site: {{$site->site_name}}, id: {{$site->id}}</li>
			@endif
			<li>My IP:&nbsp;{{$ip}}</li>
			<li>{{substr(base_path(), 0, 28)}}...</li>
			<li>Debug:&nbsp;{{(NULL != env('APP_DEBUG')) ? 'ON' : 'OFF'}}, SITE_ID: {{SITE_ID}}</li>
			<li>Life:&nbsp;{{env('SESSION_LIFETIME', 0)}}, New Visitor:&nbsp;{{$new_visitor ? 'Yes' : 'No'}}</li>
			<li><a href="/eunoticereset">Reset Privacy</a></li></ul>
	</div>
	
	@if (isset($courses))
	<div class="form-group">	
		<h3>@LANG('content.Unfinished Courses') ({{count($courses)}})&nbsp;<a href="/courses/admin"><span class="glyphCustom glyphicon glyphicon-list"></span></a></h3>
		<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
				@foreach($courses as $record)
					<tr>
						<td>
							<a href="/courses/view/{{$record->id}}">{{$record->title}}</a>&nbsp;
							<a href="/courses/publish/{{$record->id}}"><button type="button" class="btn btn-xs {{$record->getWipStatus()['btn']}}">{{$record->getWipStatus()['text']}}</button></a>
							<a href="/courses/publish/{{$record->id}}"><button type="button" class="btn btn-xs {{$record->getStatus()['btn']}}">{{$record->getStatus()['text']}}</button></a>
						</td>						
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
		
	</div>
	@endif

	@if (isset($site_admins))
	<hr />
	@endif	
	
	@if (isset($comments))
	<div>	
		<h3 style="color:red;">Comments to Approve ({{count($comments)}})</h3>
		<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
				<tr><th></th><th>Created</th><th>Name</th><th>Comment</th><th></th></tr>
				@foreach($comments as $record)
					<tr>
						<td class="glyphicon-width"><a href='/comments/publish/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-publish"></span></a></td>
						<td>{{$record->created_at}}</td>
						<td><a href="/comments/publish/{{ $record->id }}">{{$record->name}}</a></td>
						<td>{{$record->comment}}</td>
						<td class="glyphicon-width"><a href='/comments/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
		<a href="/comments/indexadmin">Show All Comments</a>
	</div>
	<hr />
	@endif
	
	@if (isset($visitors))			
	<div class="form-group">
		<h3 style="">Today's Visitors: {{count($visitors)}}&nbsp;<a href="/visitors"><span class="glyphCustom glyphicon glyphicon-list"></span></a></h3>
	</div>
	<p>&nbsp;</p>
	@endif
	
	@if (count($users) > 0)
	<div class="form-group">	
		<h3 style="">Last New User ({{count($users)}} Total)&nbsp;<a href="/users/index"><span class="glyphCustom glyphicon glyphicon-list"></span></a></h3>
		<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
			@foreach($users as $record)
				<tr>
					<td class="glyphicon-width"><a href='/users/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
					<td>{{$record->created_at}}</td>
					<td><a href="/users/view/{{ $record->id }}">{{$record->name}}</a></td>
					<td>{{$record->email}}</td>
					<td>{{$record->user_type}}</td>
					<td class="glyphicon-width"><a href='/entries/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
				</tr>
				@break
			@endforeach
			</tbody>
		</table>
		</div>
	</div>
	<hr />
	@endif
		
	<div>
		<h3 style="">Latest Events ({{count($events)}})&nbsp;<a href="/events"><span class="glyphCustom glyphicon glyphicon-list"></span></a></h3>
@if (false)		
		@component('events.menu-submenu-events-filter')@endcomponent	
@endif
		<div class="table-responsive">
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
		</div>
	</div>
	<hr />
	
	@if (isset($sites))
	<div style="margin-bottom:30px;">	
		<h3>Sites ({{count($sites)}})</h3>
		<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
				@foreach($sites as $record)
					<tr>
						<td><a target="_blank" href="http://{{$record}}">{{$record}}</a></td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
	@endif	
	
</div>
@endsection
