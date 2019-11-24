@extends('layouts.app')

@section('content')

<?php
$now = new DateTime();
?>

<div class="container page-normal">

	<form method="POST" action="/visitors">

		<div class="submenu-view">
			@component('components.control-dropdown-date', ['months' => $dates['months'], 'years' => $dates['years'], 'days' => $dates['days'], 'filter' => $filter])@endcomponent
			<button type="submit" name="update" class="btn btn-primary" style="font-size:12px; padding:1px 4px; margin:5px;">Apply Date</button>
		</div>

		<div style="padding:10px 0 0 20px;">
			<input style="width:20px;" type="checkbox" name="showbots" id="showbots" class="form-control-inline" {{ $bots ? 'checked' : '' }} />
			<label for="showbots" class="checkbox-label">Show Bots</label>
		</div>

		<h3>@LANG('ui.Visitors') ({{count($records)}}) ({{$now->format('Y-m-d H:i:s')}})</h3>

		<div class="table-responsive">

			<table class="table table-striped">
				<tbody>
					<tr><th>Timestamp</th><th>Site</th><th>Page</th><th>IP</th><th>Referrer</th><th>User</th><th>Host</th><th></th></tr>
					@foreach($records as $record)
					<tr>
						<td>{{$record['date']}}</td>

						<td>{{$record['domain_name']}}</td>

						@if (!isset($record['id']))
							<td>{{$record['model']}}/{{$record['page']}}</td>
						@else
							<td>{{$record['model']}}/{{$record['page']}} (<a href="/entries/show/{{$record['id']}}">{{$record['id']}}</a>)</td>
						@endif

						<td><a target="_blank" href="https://whatismyipaddress.com/ip/{{$record['ip']}}">{{$record['ip']}}</a> <a target="_blank" href="https://www.google.com/maps/place/{{$record['location']}}"><span style="font-size:.7em;">{{$record['location']}}</span></a></td>
						<td>{{$record['ref']}}</td>
						<td>{{$record['agent']}}</td>
						<td>{{$record['host']}}</td>
						<td><a href='/entries/confirmdelete/{{$record['id']}}'><span class="glyphCustom glyphicon glyphicon-trash"></span></a></td>
					</tr>
					@endforeach

				</tbody>
			</table>

		</div>

		{{ csrf_field() }}
    </form>
</div>
@endsection
