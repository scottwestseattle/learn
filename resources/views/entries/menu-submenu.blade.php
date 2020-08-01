@if (Auth::user() && Auth::user()->user_type >= 1000)
	<div class="submenu-view" style="font-size:20px;">
		<table><tr>
			<td style="width:40px;"><a href="#" onclick="window.history.back()"><span style="font-size: 23px" class="glyphCustom glyphicon glyphicon-circle-arrow-left"></span></a></td>
			<td style="width:40px; font-size:20px;"><a href='/entries/indexadmin/'><span class="glyphCustom glyphicon glyphicon-list"></span></a></td>
			<td style="width:40px;"><a href='/entries/add/'><span class="glyphCustom glyphicon glyphicon-plus-sign"></span></a></td>
			@if (isset($record))
				<td style="width:40px; font-size:20px;"><a href='/entries/{{$record->permalink}}'><span class="glyphCustom glyphicon glyphicon-eye-open"></span></a></td>
				<td style="width:40px;"><a href='/entries/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
				<td style="width:40px;"><a href='/entries/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-trash"></span></a></td>
				<td style="width:40px;"><a href='/photos/entries/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-picture"></span></a></td>
				<td style="width:40px;"><a href='/entries/setlocation/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-map-marker"></span></a></td>
				<td style="width:40px;"><a href='/entries/publish/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-flash"></span></a></td>
			@endif
		</tr></table>
	</div>
@endif
