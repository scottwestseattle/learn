@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h3 name="" class="">{{$record->title}}
		@if ($isAdmin)
			@if (!\App\Status::isFinished($record->wip_flag))
				<a class="btn {{($wip=\App\Status::getWipStatus($record->wip_flag))['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$wip['text']}}</a>
			@endif
			@if (!\App\Status::isPublished($record->release_flag))
				<a class="btn {{($release=\App\Status::getReleaseStatus($record->release_flag))['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$release['text']}}</a>
			@endif
		@endif
	</h3>

    @foreach($record->words as $word)
		<table>
			<tr>
				<td>{{$word->title}}</td>
			</tr>
		</table>
    @endforeach

@if (false)
	<a href="/lessons/view/{{$record[0]->id}}">
		<button style="" type="button" class="btn btn-outline-info btn-lesson-index link-dark">
			<table>
				<tr>
					<td>
						<div style="font-size:1.3em; color:purple; padding-right:5px;">Chapter {{$record[0]->lesson_number}}:&nbsp;{{$record[0]->title}}</div>
						<span style="font-size:.9em">{{$record[0]->description}}</span>
					</td>
				</tr>
			</table>
		</button>
	</a>
@endif

</div>
@endsection
