@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.' . $titlePlural) ({{count($records)}})</h1>

	<div class="row">
        <table class="table table-responsive table-striped">
            <tbody>
            @foreach($records as $record)
                <tr>
                    <td>
                        <a href='/{{$prefix}}/publish/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-publish"></span></a>
                    </td>
                    <td><a href='/{{$prefix}}/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
                    <td>
                        <a href="/{{$prefix}}/view/{{$record->id}}">{{$record->title}} ({{$record->words->count()}})</a>

                        <?php $published = App\Status::getReleaseStatus($record->release_flag); ?>
                        <?php $finished = App\Status::getWipStatus($record->wip_flag); ?>
    					@if (App\User::isOwner($record->user_id))
                             @if (!$published['done'])
                                <a class="btn {{$published['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$published['text']}}</a>
                            @endif
                            @if (!$finished['done'])
                                <a class="btn {{$finished['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$finished['text']}}</a>
                            @endif
                        @endif
                    </td>
                    <td>
    					@if (App\User::isOwner($record->user_id))
                            <a class="btn btn-info btn-primary btn-xs" role="button" href="/words/add-vocab-word/{{$record->id}}">
                                Add Word&nbsp;<span class="glyphicon glyphicon-plus-sign"></span>
                            </a>
                        @endif
                    </td>

                    <td><a href='/{{$prefix}}/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
	</div>

</div>

@endsection
