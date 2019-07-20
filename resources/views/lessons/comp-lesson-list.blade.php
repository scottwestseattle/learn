<div style="font-size:.9em;">
	<table class="table-sm table-borderless">
		<tbody>
		@foreach($records as $record)
		<tr>
			<td>{{$record->getDisplayNumber()}}</td>
			<td><a href="/lessons/view/{{$record->id}}">{{$record->title}}</a></td>
		</tr>
		@endforeach
	</table>
</div>

@if (false)
<a href="/lessons/view/{{$record->id}}">
	<button style="" type="button" class="btn btn-outline-info btn-lesson-index link-dark">
		<table>
			<tr>
				<td style="">
					<span style="font-size:1.3em; color:purple; padding-right:5px;">{{$record->getDisplayNumber()}}</span>&nbsp;
				</td>
				<td>
					{{$record->title}}<br/><span style="font-size:.9em">{{$record->description}}</span>	
				</td>
			</tr>
		</table>
	</button>
</a>
@endif
