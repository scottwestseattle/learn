@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.' . $titlePlural) ({{count($records)}})</h1>

    <div class="card-deck">
    @foreach($records as $record)
	<div class="col-sm-12 col-lg-6 col-xl-4"><!-- outer div needed for the columns, otherwise they won't center -->	
		<div class="mb-3 mr-0">
			<div class="card-body drop-box-ghost">
				<h5 class="card-title">
					<a href="/vocab-lists/view/{{$record->id}}">{{$record->title}}</a>@component('components.badge', ['text' => count($record->words)])@endcomponent
				</h5>
				<p class="card-text">
					<a class="btn btn-primary btn-xs" role="button" href="/vocab-lists/review/{{$record->id}}">
						@LANG('ui.Review')<span class="glyphicon glyphicon-eye-open ml-1"></span>
					</a>
					@if (App\User::isOwner($record->user_id))
						<a class="btn btn-info btn-xs" role="button" href="/words/add-vocab-word/{{$record->id}}">
							@LANG('ui.Add')<span class="glyphicon glyphicon-plus-sign ml-1"></span>
						</a>

						<a href='/{{$prefix}}/publish/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-publish"></span></a>
						<a href='/{{$prefix}}/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
						<a href='/{{$prefix}}/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a>

						<?php $published = App\Status::getReleaseStatus($record->release_flag); ?>
						<?php $finished = App\Status::getWipStatus($record->wip_flag); ?>
						@if (!$published['done'])
							<a class="btn {{$published['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$published['text']}}</a>
						@endif
						@if (!$finished['done'])
							<a class="btn {{$finished['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$finished['text']}}</a>
						@endif

					@endif
				</p>
			</div>
		</div>
	</div>
    @endforeach
    </div>

</div>

@endsection
