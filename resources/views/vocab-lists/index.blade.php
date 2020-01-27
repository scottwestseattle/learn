@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.' . $titlePlural) ({{count($records)}})</h1>

	<div class="row">
        <table>
		@foreach($records as $record)
            <tr>
                <td>
                    <a href="\words">{{$record->title}}</a>

@if (false)
                    <?php $published = $record->getStatus(); $finished = $record->getFinishedStatus(); ?>
                    @if (!$published['done'] || !$finished['done'])
                    <div>
                        @if (!$published['done'])
                            <a class="btn {{$published['btn']}} btn-xs" role="button" href="/lessons/publish/{{$record->id}}">{{$published['text']}}</a>
                        @endif
                        @if (!$finished['done'])
                            <a class="btn {{$finished['btn']}} btn-xs" role="button" href="/lessons/publish/{{$record->id}}">{{$finished['text']}}</a>
                        @endif
                    </div>
                    @endif
@endif
                </td>
            </tr>
		@endforeach
        </table>
	</div>
</div>

@endsection
