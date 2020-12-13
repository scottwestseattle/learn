@extends('layouts.app')

@section('content')

<div class="container page-normal">
		
	@component('events.menu-submenu')@endcomponent

	@component('events.menu-submenu-filter', ['totals' => $totals])@endcomponent
				
	<h3>@LANG('ui.Events') ({{$records->count()}})</h3>
		
	<div class="table-responsive">
		
		<table style="width:100%;" class="xtable xtable-striped">
			<tbody>
			<?php $cnt = 0; ?>
			@foreach($records as $record)
				<?php
					$type = '';
					if ($record->type_flag == 1) $type = 'Info';
					if ($record->type_flag == 2) $type = 'Warning';
					if ($record->type_flag == 3) $type = 'Error';
					if ($record->type_flag == 4) $type = 'Exception';
					if ($record->type_flag == 5) $type = 'Tracking';
					if ($record->type_flag == 99) $type = 'Other';
				?>
				
				<tr>
					<td>
						<table style="margin-bottom:0;" class="table">
							@if ($cnt++ == 0)
							<tr>
								<th>Timestamp</th>
								<th>Site</th>
								<th>User</th>
								<th>Type</th>
								<th>Model</th>
								<th>Action</th>
							</tr>			
							@endif
							<tr>
								<td>{{$record->created_at}}</td>
								<td>{{$record->site_id}}</td>
								<td>{{$record->user_id}}</td>
								<td>{{$type}}</td>
								<td>{{$record->model_flag}}</td>
								<td>{{$record->action_flag}}</td>
							</tr>
						</table>
						@if (isset($record->updates))
							<?php $parts = explode('  ', $record->updates); ?>
							<div style="padding:0px 5px 10px 5px;">
								Updates:|From|To|<br/>
								@foreach($parts as $part)
									{{$part}}<br/>
								@endforeach
							</div>
						@endif
						@if (isset($record->title))
							<div style="padding:0px 5px 10px 5px;">{{$record->title}}</div>
						@endif
						@if (isset($record->description))
							<div style="padding:0px 5px 10px 5px;">{{$record->description}}</div>
						@endif
						@if (isset($record->updates))
							<div style="padding:0px 5px 10px 5px;">{{$record->updates}}</div>
						@endif
						@if (isset($record->error))
							<div style="padding:0px 5px 10px 5px;">{{$record->error}}</div>
						@endif
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>	
</div>
@endsection
