@if (Auth::user() && Auth::user()->user_type >= 1000)
	<div class="" style="font-size:20px;">
		<table><tr>
			<td style="width:40px;"><a href="#" onclick="window.history.back()"><span style="font-size: 23px" class="glyphCustom glyphicon glyphicon-circle-arrow-left"></span></a></td>
			<td style="width:40px;"><a href='/events/index/'><span class="glyphCustom glyphicon glyphicon-list"></span></a></td>
			<td style="width:40px;"><a href='/events/add/'><span class="glyphCustom glyphicon glyphicon-plus-sign"></span></a></td>
			@if (isset($record))
				<td style="width:40px;"><a href='/events/view/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-eye-open"></span></a></td>
				<td style="width:40px;"><a href='/events/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
				<td style="width:40px;"><a href='/events/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-trash"></span></a></td>
			@endif
		</tr></table>
	</div>
@endif
