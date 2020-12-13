@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.' . $titlePlural) ({{count($records)}})</h1>

    <div class="card-deck">
    @foreach($records as $record)
    <div style="min-width:300px;" class="card mb-1">
        <div class="card-body">
            <h5 class="card-title">
                <a href="/vocab-lists/view/{{$record->id}}">{{$record->title}}</a>
                <span style="background-color:purple; margin-left:10px; font-size:12px;" class="badge badge-dark">{{$record->words->count()}}</span>
            </h5>
            <p class="card-text">
            <a class="btn btn-primary btn-xs" role="button" href="/vocab-lists/review/{{$record->id}}">
                Review&nbsp;<span class="glyphicon glyphicon-eye-open"></span>
            </a>
            @if (App\User::isOwner($record->user_id))
                <a class="btn btn-info btn-xs" role="button" href="/words/add-vocab-word/{{$record->id}}">
                    Add Word&nbsp;<span class="glyphicon glyphicon-plus-sign"></span>
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
    @endforeach
    </div>

</div>

@endsection
