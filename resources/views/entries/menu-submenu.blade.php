<?php $index = isset($index) ? $index : 'articles'; ?>
	<div class="submenu-view">
	@if (App\User::isAdmin())
		<table><tr>
			<td style="width:40px;"><a href="#" onclick="window.history.back()"><span style="font-size: 23px" class="glyphCustom glyphicon glyphicon-circle-arrow-left"></span></a></td>
			<td style="width:40px; font-size:20px;"><a href="/{{$index}}/"><span class="glyphCustom glyphicon glyphicon-list"></span></a></td>
				<td style="width:40px;"><a href="/entries/add/"><span class="glyphCustom glyphicon glyphicon-plus-sign"></span></a></td>
				@if (isset($record))
					<td style="width:40px;"><a href="/entries/{{$record->permalink}}"><span class="glyphCustom glyphicon glyphicon-eye-open"></span></a></td>
					<td style="width:40px;"><a href="/entries/edit/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
					<td style="width:40px;"><a href="/entries/confirmdelete/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-trash"></span></a></td>
					<td style="width:40px;"><a href="/entries/publish/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-flash"></span></a></td>
				@endif
				<td style="width:40px;"><a href="/entries/superstats"><span class="glyphCustom glyphicon glyphicon-stats"></span></a></td>
		</tr></table>
	@else
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
	