@if (isset($records))
<div>
@if (true)
	
	<table class="table-sm table-borderless {{isset($tableClass) ? $tableClass : ''}}">
		<tbody>
		@foreach($records as $record)
		<tr>
			<td>{{$record->getDisplayNumber()}}</td>
			<td><a href="/lessons/view/{{$record->id}}">{{$record->title}}</a></td>
		</tr>
		@endforeach
	</table>
	
@else
	
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
