@if ($isAdmin)
<div class="submenu-view">
	<table><tr>
		<td><a href="#" onclick="window.history.back()"><span class="glyphCustom glyphicon glyphicon-back"></span></a></td>
		<td><a href='/{{$prefix}}'><span class="glyphCustom glyphicon glyphicon-list"></span></a></td>
		<td><a href='/{{$prefix}}/add'><span class="glyphCustom glyphicon glyphicon-add"></span></a></td>
		@if (isset($record->id))
			<td><a href='/{{$prefix}}/view/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-eye-open"></span></a></td>
			<td><a href='/{{$prefix}}/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
			<td><a href='/{{$prefix}}/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-delete"></span></a></td>
		@endif
	</tr></table>
</div>
@endif