<?php 
	$heart = (count($record->tags) > 0) ? 'heart' : 'heart-empty'; 
	$finished = $record->isFinished() ? 'ok-circle' : 'remove-circle'; 
	$status = 'status' . $id . '-' . $record->id . '';
	$heartId = 'heart' . $id . '-' . $record->id . '';
	$wipId = 'wip' . $id . '-' . $record->id . '';
?>

<div class="float-left">
	<div class="middle ml-2"><a href='' onclick="heartDefinition(event, {{$record->id}}, '#{{$status}}')"><span id="{{$heartId}}" class="glyphCustom-md glyphicon glyphicon-{{$heart}}"></span></a></div>
	@if ($isAdmin)
		<div class="middle ml-2"><a href='' onclick="toggleWip(event, {{$record->id}}, '#{{$status}}');"><span id="{{$wipId}}" class="glyphCustom-md glyphicon glyphicon-{{$finished}}"></span></a></div>
		<div class="middle ml-2"><a href='/definitions/edit/{{$record->id}}'><span class="glyphCustom-md glyphicon glyphicon-edit"></span></a></div>
		<div class="middle ml-2"><a href='/definitions/confirmdelete/{{$record->id}}'><span class="glyphCustom-md glyphicon glyphicon-delete"></span></a></div>
	@endif
</div>
<div style="clear:both;" id="{{$status}}" class="small-thin-text red"></div>
