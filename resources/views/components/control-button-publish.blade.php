@if (App\Tools::isAdmin())

	<?php $published = App\Status::getReleaseStatus($record->release_flag); ?>
	<?php $finished = App\Status::getWipStatus($record->wip_flag); ?>
	@if (!$published['done'])
		<a class="btn {{$published['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$published['text']}}</a>
	@endif
	@if (!$finished['done'])
		<a class="btn {{$finished['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$finished['text']}}</a>
	@endif

@endif
