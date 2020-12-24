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

			<table class="table table-striped table-sm">
				<tbody>
					<tr><th>Timestamp</th><th>IP</th><th>Site</th><th>Client</th><th>Page</th><th>Referrer</th><th>Host</th></tr>
					@foreach($records as $record)
					<tr class="small-thin-text">
						<td>{{$record['date']}}</td>
						<?php $cnt = $record['count']; $count = ($cnt > 1) ? '<b>(' . $cnt . ')</b>' : ''; ?>
						<td class="medium-text"><a target="_blank" href="https://whatismyipaddress.com/ip/{{$record['ip']}}">{{$record['ip']}} {!!$count!!}</a></td>
						<td class="medium-text">{{$record['domain_name']}}</td>
						<td class="medium-text">{{$record['agent']}}</td>
						<td>{{$record['model']}}/{{$record['page']}}</td>
						<td>{{$record['ref']}}</td>
						<td>{{$record['host']}}</td>
					</tr>
					@endforeach

				</tbody>
			</table>

		</div>

		{{ csrf_field() }}
    </form>
</div>
@endsection
