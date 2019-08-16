<?php $title = isset($title) ? $title : 'Vocabulary'; ?>
@if (isset($records))
<h4>@LANG('content.' . $title) ({{count($records)}})</h4>
<div class="mb-3">
	@foreach($records as $record)
		<span class="badge badge-info vocab-pills">
		@if (isset($edit))
			<a href="{{$edit}}{{$record->id}}">{{$record->title}}</a>
		@else
			{{$record->title}}
		@endif
		</span>
	@endforeach
</div>
@endif