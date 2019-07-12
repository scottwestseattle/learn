<div class="submenu-view">
	<table><tr>
		<td><a href="#" onclick="window.history.back()"><span style="font-size: 23px" class="glyphCustom glyphicon glyphicon-circle-arrow-left">Back</span></a></td>
		<td><a href='/{{$prefix}}/'><span class="glyphCustom glyphicon glyphicon-list">Index</span></a></td>
		@if (Auth::user() && Auth::user()->isAdmin())
			<td><a href='/{{$prefix}}/admin'><span class="glyphCustom glyphicon glyphicon-list">Admin</span></a></td>
			<td><a href='/{{$prefix}}/add/'><span class="glyphCustom glyphicon glyphicon-plus-sign">Add</span></a></td>
		@if (isset($record->id))
			<td><a href='/{{$prefix}}/view/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-eye-open">View</span></a></td>
			<td><a href='/{{$prefix}}/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit">Edit</span></a></td>
			<td><a href='/{{$prefix}}/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-trash">Del</span></a></td>
			<td><a href='/{{$prefix}}/publish/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-flash">Publish</span></a></td>
		@else
			<td><a href='/{{$prefix}}/undelete/'><span class="glyphCustom glyphicon glyphicon-plus-sign">Undelete</span></a></td>			
		@endif
		@endif
	</tr></table>
</div>
