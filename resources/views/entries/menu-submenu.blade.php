<?php $index = isset($index) ? $index : 'articles'; ?>
<div class="submenu-view">
@if (App\User::isAdmin())
	<!-- Admin sees the glyphicon list -->
	<table><tr>
		<td class="icon"><a href="#" onclick="window.history.back()"><span style="font-size: 23px" class="glyphCustom glyphicon glyphicon-circle-arrow-left"></span></a></td>
		<td class="icon"><a href="/{{$index}}/"><span class="glyphCustom glyphicon glyphicon-list"></span></a></td>
		<td class="icon"><a href="/entries/add/"><span class="glyphCustom glyphicon glyphicon-plus-sign"></span></a></td>
		@if (isset($record))
			<td class="icon"><a href="/entries/{{$record->permalink}}"><span class="glyphCustom glyphicon glyphicon-eye-open"></span></a></td>
			<td class="icon"><a href="/entries/edit/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
			<td class="icon"><a href="/entries/confirmdelete/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-trash"></span></a></td>
			<td class="icon"><a href="/entries/publish/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-flash"></span></a></td>
		@endif
		<td class="icon"><a href="/entries/superstats"><span class="glyphCustom glyphicon glyphicon-stats"></span></a></td>
	</tr></table>
@else
	<!-- regular user just sees a big Back button -->
	@if (isset($isIndex) && $isIndex)
		<!-- don't show the back button -->
	@else
	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm" role="button" href="/{{$index}}/">@LANG('content.Back to List')
		<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	</div>
	@endif
@endif
</div>
	