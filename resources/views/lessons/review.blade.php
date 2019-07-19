@extends('layouts.app')

@section('content')

<script src="{{ asset('js/qna.js') }}"></script>

<div class="data-misc" 
	data-max="{{$sentenceCount}}"
	data-prompt="{{$questionPrompt}}"
	data-prompt-reverse="{{$questionPromptReverse}}"
></div>

@foreach($quiz as $rec)
	<div class="data-qna" data-question="{{$rec['q']}}" data-answer="{{$rec['a']}}" data-id="{{$rec['id']}}" ></div>
@endforeach

<div class="container page-normal lesson-page">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="/courses/view/{{$record->parent_id}}">@LANG('content.Back to')&nbsp;{{$record->course->title}}<span class="glyphicon glyphicon-button-back-to"></span></a></span>
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-lg btn-nav-lesson {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}">
			<span class="glyphicon glyphicon-button-prev"></span>
			@LANG('ui.Prev')
		</a>
		<a class="btn btn-primary btn-lg btn-nav-lesson {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">
			@LANG('ui.Next')
			<span class="glyphicon glyphicon-button-next"></span>
		</a>
	</div>

    <div style="font-size:.8em;">
		{{$record->course->title}},&nbsp;@LANG('content.Chapter')&nbsp;{{$record->lesson_number}}.{{$record->section_number}}&nbsp;({{$sentenceCount}})
		@if ($isAdmin)
			&nbsp;<a href="/{{$prefix}}/edit2/{{$record->id}}"><span class="glyphCustom-sm glyphicon glyphicon-pencil"></span></a>
			<a class="btn {{($status=$record->getStatus())['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$status['text']}}</a>
		@endif
	</div>

	<div style="margin: 50px 0">

		<p id="prompt"></p>
		<p id="answer"></p>

		<button class="btn btn-primary" onclick="first()">First</button>
		<button class="btn btn-primary" onclick="prev()">Prev</button>
		<button class="btn btn-primary" id="button-next" onclick="next()">Next</button>
		<button class="btn btn-primary" onclick="last()">Last</button>
		
	</div>
	
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-lg btn-nav-lesson {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}"><span class="glyphicon glyphicon-button-prev"></span>@LANG('ui.Prev')</a>
		<a class="btn btn-primary btn-lg btn-nav-lesson {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">@LANG('ui.Next')<span class="glyphicon glyphicon-button-next"></span></a>
	</div>

</div>
@endsection
