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
			<li>{{$domain}} ({{$ip}})</li>
			<li>{{substr(base_path(), 0, 28)}}...</li>
			<li>Debug:&nbsp;{{(NULL != env('APP_DEBUG')) ? 'ON' : 'OFF'}}, SITE_ID: {{SITE_ID}}, Site: {{\App\Tools::getSiteId()}}</li>
			<li>Life:&nbsp;{{env('SESSION_LIFETIME', 0)}}, New Visitor:&nbsp;{{$new_visitor ? 'Yes' : 'No'}}</li>
			@if ($isSuperAdmin)
				<li>Size:
					<span class="size-xs">Extra Small</span>
					<span class="size-sm">Small</span>
					<span class="size-md">Medium</span>
					<span class="size-lg">Large</span>
					<span class="size-xl">Extra Large</span>
				</li>
			@endif
		</ul>
	</div>

	@if (count($users) > 0)
	<div class="form-group">
		<h3 style="">Latest New Users ({{count($users)}} Total)&nbsp;<a href="/users/index"><span class="glyphCustom glyphicon glyphicon-list"></span></a></h3>
		<div class="table-responsive" style="font-size: 12px;">
		<table class="table table-striped">
			<tbody>
			@foreach($users as $record)
				<tr>
					<td class="glyphicon-width"><a href='/users/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
					<td>{{$record->created_at}}</td>
					<td><a href="/users/view/{{ $record->id }}">{{$record->name}}</a></td>
					<td>{{$record->email}}</td>
					<td><a target="_blank" href="https://www.ip2location.com/demo/{{$record->ip_register}}">{{$record->ip_register}}</a></td>
					<td>{{App\Tools::getSiteName($record->site_id)}}</td>
					<td class="glyphicon-width"><a href='/users/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
				</tr>
			    @if ($loop->index >= 2)
			        @break
			    @endif
			@endforeach
			</tbody>
		</table>
		</div>
	</div>
	<hr />
	@endif

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
							<a href="/courses/publish/{{$record->id}}"><button type="button" class="btn btn-xs {{App\Status::getWipStatus($record->wip_flag)['btn']}}">{{App\Status::getWipStatus($record->wip_flag)['text']}}</button></a>
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
		<div class="table-responsive" style="font-size: 12px;">
		<table class="table table-striped table-sm">
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
		<div class="table-responsive" style="font-size: 12px;">
		<table class="table table-striped table-sm">
			<tbody>
				@foreach($visitors as $record)
					<tr>
						<td>{{$record['date']}}</td>
						<?php $cnt = $record['count']; $count = ($cnt > 1) ? '<b>(' . $cnt . ')</b>' : ''; ?>
						<td class="medium-text"><a href="https://whatismyipaddress.com/ip/{{$record['ip']}}" target="_blank">{{$record['ip']}}</a> {!!$count!!}</td>
						<td class="medium-text">{{$record['domain_name']}}</td>
						<td class="medium-text">{{$record['agent']}}</td>
						<td>{{$record['ref']}}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
	<p>&nbsp;</p>
	@endif

	<div>
		<h3 style="">Latest Events ({{count($events)}})&nbsp;<a href="/events"><span class="glyphCustom glyphicon glyphicon-list"></span></a></h3>
@if (false)
		@component('events.menu-submenu-events-filter')@endcomponent
@endif
		<div class="table-responsive" style="font-size: 12px;">
		<table class="table table-striped table-sm">
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
				@foreach($sites as $key => $data)
					<tr>
						<td><a target="_blank" href="http://{{$data}}">{{$data}}</a></td>
						<td>{{$key}}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
	@endif

</div>
@endsection
