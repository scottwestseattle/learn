@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-top" role="button" href="/{{$prefix}}/">
		    @LANG('content.Back to Vocabulary Lists')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	    <a class="btn btn-primary btn-sm btn-nav-top" role="button" href="/{{$prefix}}/review/{{$record->id}}">
            @LANG('ui.Review')&nbsp;<span class="glyphicon glyphicon-eye-open"></span>
	    </a>
        @if (App\User::isOwner($record->user_id))
	    <a class="btn btn-info btn-sm btn-nav-top" role="button" href="/words/add-vocab-word/{{$record->id}}">
            @LANG('ui.Add')&nbsp;<span class="glyphicon glyphicon-plus-sign"></span>
	    </a>
	    @endif
	</div>

	<h3 name="" class="" style="margin-bottom:10px;">{{$record->title}}@component('components.badge', ['text' => count($record->words)])@endcomponent
        @if (App\User::isOwner($record->user_id))
			@if (!\App\Status::isFinished($record->wip_flag))
				<a class="btn {{($wip=\App\Status::getWipStatus($record->wip_flag))['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$wip['text']}}</a>
			@endif
			@if (!\App\Status::isPublished($record->release_flag))
				<a class="btn {{($release=\App\Status::getReleaseStatus($record->release_flag))['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$release['text']}}</a>
			@endif
		@endif
	</h3>

	<table class="table table-responsive table-striped">
		<tbody>
		@foreach($record->words->where('deleted_flag', 0) as $r)
			<tr>
            @if (App\User::isOwner($record->user_id))
				<td><a href="/{{$prefixWord}}/edit-user/{{$r->id}}"><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
            @endif
				<td>
				    <a href="/{{$prefixWord}}/view/{{$r->id}}">{{$r->title}}</a>
				    <div>{{substr($r->description, 0, 200)}}</div>
				</td>
            @if (App\User::isOwner($record->user_id))
				<td><a href="/{{$prefixWord}}/confirmdelete/{{$r->id}}"><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
            @endif
			</tr>
		@endforeach
		</tbody>
	</table>

</div>
@endsection
