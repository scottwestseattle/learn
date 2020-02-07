@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-md" role="button" href="/{{$prefix}}/">
		    @LANG('content.Back to Course List')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	</div>

	<h3 name="title" class="">{{$record->title }}
		@if ($isAdmin)
			@if (!\App\Status::isFinished($record->wip_flag))
				<a class="btn {{($wip=\App\Status::getWipStatus($record->wip_flag))['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$wip['text']}}</a>
			@endif
			@if (!\App\Status::isPublished($record->release_flag))
				<a class="btn {{($release=\App\Status::getReleaseStatus($record->release_flag))['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$release['text']}}</a>
			@endif
		@endif
	</h3>

	<p>{{$record->description}}</p>

    <h4>{{$displayCount}}&nbsp;Exercises</h4>

	<div class="row" style="margin-bottom:10px;">
		@foreach($records as $record)
		<div style="padding:10px;" class="col-sm-4"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
			<div class="drop-box" style="height:200px;  background-color: #4993FD; color:white;" ><!-- inner col div -->
                <a style="height:100%; width:100%;" class="btn btn-success btn-lg" role="button" href="/lessons/start/{{$record[0]->id}}">
                    {{$record[0]->title_chapter}}<br/>{{count($record)}} exercises ({{App\Tools::secondsToTime($record['seconds'])}})
                </a>
			</div><!-- inner col div -->
		</div><!-- outer col div -->
		@endforeach
	</div><!-- row -->

</div>
@endsection
