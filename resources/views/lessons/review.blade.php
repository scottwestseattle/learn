@extends('layouts.app')

@section('content')

<script src="{{ asset('js/qna.js') }}"></script>

<script>
document.getElementsByTagName("BODY")[0].onload = function() { quiz.start(); };
</script>

<div class="data-misc"
	data-max="{{$sentenceCount}}"
	data-prompt="{{$questionPrompt}}"
	data-prompt-reverse="{{$questionPromptReverse}}"
></div>

@foreach($records as $rec)
	<div class="data-qna" data-question="{{$rec['q']}}" data-answer="{{$rec['a']}}" data-id="{{$rec['id']}}" ></div>
@endforeach

<div class="container page-normal lesson-page">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="/courses/view/{{$record->parent_id}}">@LANG('content.Back to')&nbsp;{{$record->course->title}}<span class="glyphicon glyphicon-button-back-to"></span></a></span>

    <div style="font-size:.8em;">
		{{$record->course->title}},&nbsp;@LANG('content.Chapter')&nbsp;{{$record->lesson_number}}.{{$record->section_number}}&nbsp;({{$sentenceCount}})
		@if ($isAdmin)
			&nbsp;<a href="/{{$prefix}}/edit2/{{$record->id}}"><span class="glyphCustom-sm glyphicon glyphicon-pencil"></span></a>
			<a class="btn {{($status=$record->getStatus())['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$status['text']}}</a>
		@endif
	</div>
	<div style="margin: 10px 0 20px 0">
	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->

<!----------------------------------------------------------------------------->
<!-- SHOW QUESTIONS -->
<!----------------------------------------------------------------------------->

@if (count($records) > 0)

<section class="quizSection" id="sectionStats">

	<!-------------------------------------------------------->
	<!-- STATS -->
	<!-------------------------------------------------------->
	<div>
		<span id="statsCount"></span>&nbsp;&nbsp;&nbsp;<span id="statsScore"></span>&nbsp;&nbsp;<span id="statsAlert"></span>
	</div>

	<!-------------------------------------------------------->
	<!-- DEBUG -->
	<!-------------------------------------------------------->

<?php if (isset($showDebug) && $showDebug) : ?>
	<div><span id="statsDebug"></span></div>
<?php else : ?>
	<div style="display: none;"><span id="statsDebug"></span></div>
<?php endif; ?>

</section>

<section class="quizSection" id='sectionQna'>

	<!-------------------------------------------------------->
	<!-- QUESTION -->
	<!-------------------------------------------------------->

	<span id="question-graphics" style="background: white; font-size: 150%;">
		<img id="question-prompt" src="/img/question-prompt.jpg" height="30" />
		<img id="question-right" src="/img/question-right.jpg" height="30" />
		<img id="question-wrong" src="/img/question-wrong.jpg" height="30" />
		<span id="promptQuestion"></span><span id="prompt"><a></a></span>
	</span>

	<!-------------------------------------------------------->
	<!-- ANSWER -->
	<!-------------------------------------------------------->

	<div class="kbase form">
		<form method="POST" id="form-edit" action="/{{$prefix}}/updatesbw/{{$record->id}}">
		<!-- ?= $this->Form->create($kbase) ? -->
		<fieldset id="runtimeFields">
@if (false)
		<h4 id="alertPrompt" style='margin: 0; margin-top: 10px; font-weight: normal;'>Type Answer:</h4>
@endif
		<div id="typeAnswers">

			<!-------------------------------------------------------->
			<!-- TEXTBOX TO ENTER ANSWER -->
			<!-------------------------------------------------------->
@if (false)
			<input type="text" name="answer" id="attempt" onkeypress="onKeypress(event)" >
@endif
		</div>
			<!-------------------------------------------------------->
			<!-- SPACE TO SHOW SCORED ANSWER -->
			<!-------------------------------------------------------->
			<div style="padding: 10px; font-size: 100%; min-height: 70px; sbwbackground: #efefef; sbwborder: 1px gray solid; margin-top: 2px;" id="answer-show-div"></div>

		</fieldset>

	<!----------------------------------------------------------------------------->
	<!-- CONTROL BUTTONS -->
	<!----------------------------------------------------------------------------->

		<!-- BUTTONS ROW 1 -->
		<input class="btn btn-default btn-quiz" type="button" value="Next Question" onclick="nextAttempt()" id="button-next-attempt">
		<input class="btn btn-default btn-quiz" type="button" value="Check Typed Answer" onclick="checkAnswer(1)" id="button-check-answer">
		<button class="btn btn-info btn-quiz" onclick="event.preventDefault(); quiz.start()" id="button-start">Start Quiz</button>
		<input class="btn btn-default btn-quiz" type="button" value="I KNOW IT (Alt+k)" onclick="checkAnswer(2)" id="button-know" style="display: default; background-color: green; color: white;">
		<input class="btn btn-default btn-quiz" type="button" value="I DON'T KNOW (Alt+d)" onclick="checkAnswer(3)" id="button-dont-know" style="display: none; background-color: red; color: white;">
		<button class="btn btn-warning btn-quiz" onclick="event.preventDefault(); resetQuiz()" id="button-stop">STOP QUIZ</button>
		<input class="btn btn-default btn-quiz" type="button" value="Change to Wrong (Alt+c)" onclick="override()" id="button-override" style="display: none;">

		<!-- BUTTONS ROW 2 -->

		<div style="margin: 20px 0;" class="control-group" id="buttonRowReview">
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); first()"><span class="glyphicon glyphicon-circle-arrow-up"></span>@LANG('ui.First')</a></span>
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); prev()"><span class="glyphicon glyphicon-button-prev"></span>@LANG('ui.Prev')</a></span>
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); next()">@LANG('ui.Next')<span class="glyphicon glyphicon-button-next"></span></a></span>
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); last()">@LANG('ui.Last')<span class="glyphicon glyphicon-circle-arrow-down"></span></a></span>
@if (false) //sbw not working
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); clear2()">@LANG('ui.Clear')</a></span>
@endif
@if (false)
			<button class="btn btn-success" onclick="event.preventDefault(); first()"><< First</button>
			<button class="btn btn-success" onclick="event.preventDefault(); prev()">< Prev</button>
			<button class="btn btn-success" onclick="event.preventDefault(); next()" id="button-next">Next ></button>
			<button class="btn btn-success" onclick="event.preventDefault(); last()">Last >></button>
			<button class="btn btn-success" onclick="event.preventDefault(); clear2()">Clear</button>
@endif
		</div>

		<!-- CHECKBOX ROW -->

		<div class="form-group">
			<input type="checkbox" name="checkbox-type-answers" id="checkbox-type-answers" class="" onclick="quiz.typeAnswersClick()" />
			<label for="checkbox-type-answers" class="checkbox-big-label" onclick="quiz.typeAnswersClick()">@LANG('content.Type Answers')</label>
		</div>

		<div class="form-group">
			<input type="checkbox" name="checkbox-flip" id="checkbox-flip" class="" />
			<label for="checkbox-type-answers" class="checkbox-big-label">@LANG('content.Flip Question/Answer')</label>
		</div>

		{{ csrf_field() }}

		</form>

	</div>

</section>

@endif





	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->
	</div>
</div>
@endsection
