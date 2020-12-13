<div class="">
	<table><tr>
		<td><a href="#" onclick="window.history.back()"><span class="glyphCustom glyphicon glyphicon-back"></span></a></td>
		<td><a href='/events'><span class="glyphCustom glyphicon glyphicon-index"></span></a></td>
		@if (isset($record))
			<td><a href='/events/view/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-view"></span></a></td>
			<td><a href='/events/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
			<td><a href='/events/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-delete"></span></a></td>
		@endif
	</tr></table>
</div>
