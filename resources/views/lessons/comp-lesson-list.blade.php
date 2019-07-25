@if (isset($records))
<div style="font-size:.9em;">
	<table class="table-sm table-borderless {{isset($tableClass) ? $tableClass : ''}}">
		<tbody>
		@foreach($records as $record)
		<tr>
			<td>{{$record->getDisplayNumber()}}</td>
			<td><a href="/lessons/view/{{$record->id}}">{{$record->title}}</a></td>
		</tr>
		@endforeach
	</table>
</div>
@endif
