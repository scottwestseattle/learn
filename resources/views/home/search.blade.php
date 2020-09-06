@extends('layouts.app')

@section('content')

@component('components.ready-set-focus', ['controlId' => 'searchText'])@endcomponent

<div class="container page-normal">

	<h1>@LANG('ui.Search'){{$isPost ? ' (' . $count . ')' : ''}}</h1>

	<form method="POST" action="/search">
		<div class="form-group form-control-big">
			<input type="text" id="searchText" name="searchText" class="form-control" value="{{$search}}"/>

		</div>
		<div class="form-group">
				<button type="submit" name="submit" class="btn btn-primary">@LANG('ui.Search')</button>
		</div>
		{{ csrf_field() }}
	</form>

	@if (isset($lessons) || isset($words))
			<table class="table table-striped">
				<tbody>

				@if (isset($lessons))
					@foreach($lessons as $record)
						<tr>
							<td>@LANG('content.Lesson')</td>
							<td><a href="/lessons/view/{{$record->id}}" target="_blank">{{$record->courseTitle}} - {{$record->lesson_number}}.{{$record->section_number}} {{$record->title}}</a></td>
						</tr>
					@endforeach
				@endif

				@if (isset($words))
					@foreach($words as $record)
						<tr>
							@if ($record->isLessonWord())
								<td>@LANG('content.Lesson')</td>
								<td><a href="/lessons/view/{{$record->parent_id}}" target="_blank">{{$record->courseTitle}} - {{$record->lesson_number}}.{{$record->section_number}} {{$record->lessonTitle}}</a></td>
							@elseif ($record->isVocabListWord())
								<td>@LANG('content.Vocabulary')</td>
								<td>
								    <a href="/words/view/{{$record->id}}" target="_blank">{{$record->title}}</a>
								    &nbsp;(<a href="/vocab-lists/view/{{$record->vocab_list_id}}" target="_blank">@LANG('content.List')</a>)
								</td>
							@else
								<td>@LANG('content.Vocabulary')</td>
								<td><a href="/words/view/{{$record->id}}" target="_blank">{{$record->title}}</a></td>
							@endif
						</tr>
					@endforeach
				@endif

				</tbody>
			</table>
		</div>
	@endif

</div>

@endsection
