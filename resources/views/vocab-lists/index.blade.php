@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<!--------------------------------------------------------------------->
	<!-- User Created QNA                                                -->
	<!--------------------------------------------------------------------->

	<h1 class="mb-0">@LANG('content.' . 'Your Questions and Answers') ({{count($records)}})</h1>
	<div class="mb-2 small-thin-text">Vocabulary lists created by you</div>

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

	<!--------------------------------------------------------------------->
	<!-- Favorites Lists                                                 -->
	<!--------------------------------------------------------------------->

	@if (isset($favorites))
	<h1 class="mb-0">@LANG('content.' . 'Dictionary Favorites')
		<a class="btn btn-info btn-xs" role="button" href="/tags/add-user-favorite-list">
			@LANG('ui.Add') @LANG('ui.List')<span class="glyphicon glyphicon-plus-sign ml-1"></span>
		</a>
	</h1>
	<div class="mb-2 small-thin-text">Vocabulary favorited from dictionary</div>

	@if (count($favorites) > 0)
    <div class="card-deck">
    @foreach($favorites as $record)
	<div class="col-sm-12 col-lg-6 col-xl-4"><!-- xl = 3 cols, lg = 2 cols, sm = 1 col -->	
		<div class="mb-3 mr-0">
			<div class="card-body drop-box-ghost">
				<h5 class="card-title">
					<a href="/definitions/list/{{$record->id}}">{{$record->name}}</a>@component('components.badge', ['text' => $record->wc])@endcomponent
				</h5>
				<p class="card-text">
					<a class="btn btn-primary btn-xs" role="button" href="/definitions/review/{{$record->id}}">
						@LANG('ui.Review')<span class="glyphicon glyphicon-eye-open ml-1"></span>
					</a>
					<a href='/tags/edit-user-favorite-list/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
					<a href='/tags/confirm-user-favorite-list-delete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a>					
				</p>
			</div>
		</div>
	</div>
    @endforeach
    </div>
	@else
		<div class="medium-thing-text mb-5">No favorites lists</div>
	@endif
	@endif

	<!--------------------------------------------------------------------->
	<!-- Newest Definitions                                              -->
	<!--------------------------------------------------------------------->

	@if (isset($newest))
	<h1 class="mb-0">@LANG('content.New Dictionary Words')</h1>
	<div class="mb-2 small-thin-text">@LANG('content.20 Newest definitions added to the dictionary')</div>
    <div class="card-deck">
		<div class="col-sm-12 col-lg-6 col-xl-4"><!-- xl = 3 cols, lg = 2 cols, sm = 1 col -->	
			<div class="mb-3 mr-0">
				<div class="card-body drop-box-ghost">
					<h5 class="card-title">@LANG('content.New Dictionary Words')</h5>
					<p class="card-text">
						<a class="btn btn-primary btn-xs" role="button" href="/definitions/review-newest">
							@LANG('ui.Review')<span class="glyphicon glyphicon-eye-open ml-1"></span>
						</a>
						<a class="btn btn-primary btn-xs" role="button" href="/definitions/review-newest/1">
							@LANG('content.Flashcards')<span class="glyphicon glyphicon-flash ml-1"></span>
						</a>
					</p>
				</div>
			</div>
		</div>
    </div>
	@endif

	<!--------------------------------------------------------------------->
	<!-- Lists from Articles/Books                                       -->
	<!--------------------------------------------------------------------->

	@if (isset($entries) && count($entries) > 0)
	<h1 class="mb-0">@LANG('content.' . 'Articles')</h1>
	<div class="mb-2 small-thin-text">Vocabulary saved from articles</div>

    <div class="card-deck">
    @foreach($entries as $record)
	<div class="col-sm-12 col-lg-6 col-xl-4"><!-- xl = 3 cols, lg = 2 cols, sm = 1 col -->	
		<div class="mb-3 mr-0">
			<div class="card-body drop-box-ghost">
				<h5 class="card-title">
					<a href="/entries/vocabulary/{{$record->id}}">{{$record->title}}</a>@component('components.badge', ['text' => $record->wc])@endcomponent
				</h5>
				<p class="card-text">
					<a class="btn btn-primary btn-xs" role="button" href="/entries/review-vocabulary/{{$record->id}}">
						@LANG('ui.Review')<span class="glyphicon glyphicon-eye-open ml-1"></span>
					</a>
				</p>
			</div>
		</div>
	</div>
    @endforeach
    </div>
	@endif

</div>

@endsection
