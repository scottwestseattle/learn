@if ($isAdmin)
<div class="submenu-view">
	<table><tr>
		@if (isset($parent_id))
			<td><a href='/lessons/view/{{$parent_id}}'><span class="glyphCustom glyphicon glyphicon-home"></span></a></td>
			<!-- td><a href='/{{$prefix}}/{{$parent_id}}'><span class="glyphCustom glyphicon glyphicon-index"></span></a></td -->
			<td><a href='/{{$prefix}}/add/{{$parent_id}}'><span class="glyphCustom glyphicon glyphicon-add"></span></a></td>
			<!-- td><a href='/{{$prefix}}/indexowner/{{$parent_id}}'><span class="glyphCustom glyphicon glyphicon-index"></span></a></td -->
		@else
			<td><a href='/home'><span class="glyphCustom glyphicon glyphicon-home"></span></a></td>
			<!-- td><a href='/{{$prefix}}/{{$parent_id}}'><span class="glyphCustom glyphicon glyphicon-index"></span></a></td -->
			<td><a href='/words/add-user'><span class="glyphCustom glyphicon glyphicon-add"></span></a></td>
			<td><a href='/words/index'><span class="glyphCustom glyphicon glyphicon-list"></span></a></td>
		@endif
		@if (isset($record->id))
			<td><a href='/{{$prefix}}/view/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-eye-open"></span></a></td>
			<td><a href='/{{$prefix}}/edit-user/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
			<td><a href='/{{$prefix}}/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-delete"></span></a></td>
		@else
		@endif
	</tr></table>
</div>
@elseif (Auth::check())
<div class="submenu-view">
	<table><tr>
		@if (isset($parent_id))
			<td><a href='/{{$prefix}}/{{$parent_id}}'><span class="glyphCustom glyphicon glyphicon-index"></span></a></td>
		@else
			<td><a href='/home'><span class="glyphCustom glyphicon glyphicon-home"></span></a></td>
			<td><a href='/words/add-user'><span class="glyphCustom glyphicon glyphicon-add"></span></a></td>
			@if (isset($record->id))
				<td><a href='/{{$prefix}}/confirmdelete-user/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-delete"></span></a></td>
			@endif
		@endif
	</tr></table>
</div>
@endif