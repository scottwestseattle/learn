@if (isset($records))
<div style="margin-top: 40px;">

	<div class="card" style="width:100%; max-width: 600px; font-size:.8em;">
	<div style="padding:10px;" class="card-header">@LANG('content.Lessons')</div>
		<ul class="list-group list-group-flush">
			@foreach($records as $record)
			<li style="padding:10px;" class="list-group-item">
				@if ($record->isTimedSlides())
					<table>
					<tr>
						<td>
							<img width="50" src="/img/plancha/{{$record->main_photo}}" />
						</td>
						<td>
							@if (isset($selectedId) && $selectedId == $record->id)
								<span class="glyphicon glyphicon-button-next bright-blue-fg"></span>&nbsp;
								<span style="xfont-weight:bold;">{{$record->section_number}}.&nbsp;<a style="text-decoration:none;" href="/lessons/view/{{$record->id}}">{{$record->title}}</a></span>
							@else
								{{$record->section_number}}.&nbsp;<a style="text-decoration:none;" href="/lessons/view/{{$record->id}}">{{$record->title}}</a>
							@endif						
						</td>
					</tr>
					</table>
				@else
					@if (isset($selectedId) && $selectedId == $record->id)
						<span class="glyphicon glyphicon-button-next bright-blue-fg"></span>&nbsp;
						<span style="xfont-weight:bold;">{{$record->section_number}}.&nbsp;<a style="text-decoration:none;" href="/lessons/view/{{$record->id}}">{{$record->title}}</a></span>
					@else
						{{$record->section_number}}.&nbsp;<a style="text-decoration:none;" href="/lessons/view/{{$record->id}}">{{$record->title}}</a>
					@endif
				@endif				
			</li>
			@endforeach
		</ul>
	</div>

@if (false)
	<table class="table-sm table-borderless {{isset($tableClass) ? $tableClass : ''}}">
		<tbody>
		@foreach($records as $record)
		<tr>
			<td>{{$record->getDisplayNumber()}}</td>
			<td><a href="/lessons/view/{{$record->id}}">{{$record->title}}</a></td>
		</tr>
		@endforeach
	</table>
@endif

@if (false)	
	@foreach($records as $record)
	<ul class="nav nav-pills nav-fill">
		<li class="nav-item">
			<a class="nav-link active" href="/lessons/view/{{$record->id}}">{{$record->getDisplayNumber()}}&nbsp;{{$record->title}}</a>
		</li>
	</ul>	
	@endforeach	
@endif	
		
</div>
@endif
