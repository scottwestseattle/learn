@extends('layouts.app')

@section('content')

<div class="container page-normal">
	<h2 style="">Super Admin Dashboard</h2>

	<div style="margin-bottom:40px;">
		<ul style="font-size: 1.1em; list-style-type: none; padding-left: 0px;">
			<li>Time: {{date("Y-m-d H:i:s")}}</li>
			<li>Site: {{$domainName}}, ID: {{SITE_ID}}</li>
			<li>My IP:&nbsp;{{$ip}}</li>
			<li>{{base_path()}}</li>
			<li>Debug:&nbsp;{{(NULL != env('APP_DEBUG')) ? 'ON' : 'OFF'}}</li>
			<li>New Visitor:&nbsp;{{$new_visitor ? 'Yes' : 'No'}}
				&nbsp;&nbsp;<a href="/eunoticereset">EU Notice</a>
				&nbsp;&nbsp;<a href="/hash">Hash</a>				
			</li>
		</ul>
	</div>

	@if (isset($sites))
	<div>	
		<h3>Sites ({{count($sites)}})</h3>
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
	@endif

	@if (isset($site_admins))
	<hr />
	@endif
	
</div>
@endsection
