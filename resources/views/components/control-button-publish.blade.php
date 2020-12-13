@if (App\Tools::isAdmin())

	<?php
		$showPublic = isset($showPublic) ? $showPublic : false;
		$published = App\Status::getReleaseStatus($record->release_flag, $showPublic); 
		$finished = App\Status::getWipStatus($record->wip_flag);
		$btnStyle =  isset($btnStyle) ? $btnStyle : 'btn-xs';
	?>
	
	@if ($showPublic || !$published['done'])
		<a class="btn {{$published['btn']}} {{$btnStyle}}" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$published['text']}}</a>
	@endif
	
	@if (!$finished['done'])
		<a class="btn {{$finished['btn']}} {{$btnStyle}}" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$finished['text']}}</a>
	@endif

@endif
