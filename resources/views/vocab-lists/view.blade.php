@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-md" role="button" href="/{{$prefix}}/">
		    @LANG('content.Back to Vocabulary Lists')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	    <a class="btn btn-info btn-primary" role="button" href="/words/add-vocab-word/{{$record->id}}">
            @LANG('content.Add New Word')&nbsp;<span class="glyphicon glyphicon-plus-sign"></span>
	    </a>
	    <a class="btn btn-info btn-primary" role="button" href="/{{$prefix}}/review/{{$record->id}}">
            @LANG('content.Review')&nbsp;<span class="glyphicon glyphicon-eye-open"></span>
	    </a>
	</div>

	<h3 name="" class="" style="margin-bottom:10px;">{{$record->title}}
		@if ($isAdmin)
			@if (!\App\Status::isFinished($record->wip_flag))
				<a class="btn {{($wip=\App\Status::getWipStatus($record->wip_flag))['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$wip['text']}}</a>
			@endif
			@if (!\App\Status::isPublished($record->release_flag))
				<a class="btn {{($release=\App\Status::getReleaseStatus($record->release_flag))['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$release['text']}}</a>
			@endif
		@endif
	</h3>

	<table class="table table-responsive table-striped">
		<thead>
			<tr>
				<th></th><th></th><th>@LANG('gen.Title')</th><th>@LANG('gen.Description')</th><th></th><th></th>
			</tr>
		</thead>
		<tbody>
		@foreach($record->words as $r)
			<tr>
				<td><a href="/{{$prefixWord}}/edit-user/{{$r->id}}"><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
				<td><a href="/{{$prefixWord}}/publish/{{$r->id}}"><span class="glyphCustom-sm glyphicon glyphicon-publish"></span></a></td>
				<td><a href="/{{$prefixWord}}/view/{{$r->id}}">{{$r->title}}</a></td>
				<td>{{$r->description}}</td>
				<td><a href="/{{$prefixWord}}/confirmdelete/{{$r->id}}"><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
			</tr>
		@endforeach
		</tbody>
	</table>

</div>
@endsection
