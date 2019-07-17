<div class="submenu-view">
	<table><tr>
		<td><a href="#" onclick="window.history.back()"><span class="glyphCustom glyphicon glyphicon-back"></span></a></td>
		@if (isset($record->course))
		<td><a href='/courses/view/{{$record->course->id}}/'><span class="glyphCustom glyphicon glyphicon-index"></span></a></td>
		@endif
		@if ($isAdmin)
			<td><a href='/{{$prefix}}/admin'><span class="glyphCustom glyphicon glyphicon-admin"></span></a></td>
			<td><a href='/{{$prefix}}/add/'><span class="glyphCustom glyphicon glyphicon-add"></span></a></td>
		@if (isset($record->id))
			<td><a href='/{{$prefix}}/view/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-view"></span></a></td>
			<td><a href='/{{$prefix}}/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
			<td><a href='/{{$prefix}}/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-delete"></span></a></td>
			<td><a href='/{{$prefix}}/publish/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-publish"></span></a></td>
		@else
			<td><a href='/{{$prefix}}/undelete/'><span class="glyphCustom glyphicon glyphicon-undelete"></span></a></td>			
		@endif
		@endif
	</tr></table>
</div>
