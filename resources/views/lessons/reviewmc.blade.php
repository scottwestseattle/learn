@extends('layouts.quiz')

@section('content')

<script>
document.getElementsByTagName("BODY")[0].onload = function() { quiz.start(); };
</script>

<div class="data-misc"
	data-max="{{$sentenceCount}}" 
	data-prompt="{{$questionPrompt}}" 
	data-prompt-reverse="{{$questionPromptReverse}}" 
	data-quiztext-round="@LANG('content.' . $quizText['Round'])" 
	data-quiztext-correct="@LANG('content.' . $quizText['Correct'])" 
	data-quiztype="{{$record->type_flag}}" 
	data-ismc="{{$isMc}}" 
	data-quiztext-of="@LANG('content.' . $quizText['of'])" 
></div>

@foreach($records as $rec)
	<div class="data-qna" data-question="{{$rec['q']}}" data-answer="{{$rec['a']}}" data-options="{{$rec['options']}}" data-id="{{$rec['id']}}" ></div>
@endforeach

<div class="container">

	<div style="margin-top: 5px;">
	
		<div style="float:left; margin: 0 5px 0 0;">
			<span class="page-nav-buttons"><a class="" role="" href="/lessons/view/{{$record->id}}"><span class="glyphicon glyphicon-button-back-to"></span></a></span>
		</div>
		
		<!-------------------------------------------------------->
		<!-- STATS -->
		<!-------------------------------------------------------->
		<div id="stats">
			<span id="statsCount"></span>&nbsp;&nbsp;&nbsp;<span id="statsScore"></span>&nbsp;&nbsp;<span id="statsAlert"></span>
		</div>
		
	</div>
	
	<div style="margin: 10px 0 20px 0">
	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->	
	
<!----------------------------------------------------------------------------->
<!-- SHOW QUESTIONS -->
<!----------------------------------------------------------------------------->

@if (count($records) > 0)

<section xstyle="max-width: 600px;" class="quizSection" id='sectionQna'>

	<!-------------------------------------------------------->
	<!-- Instructions -->
	<!-------------------------------------------------------->

	<div class="text-center" id="" style="font-size: 1em; margin-bottom:10px;">	
		<!-------------------------------------------------------->
		<!-- SHOW Question prompt and results RIGHT/WRONG -->
		<!-------------------------------------------------------->
		<span id="alertPrompt"></span>
	</div>
	

	<!-------------------------------------------------------->
	<!-- QUESTION -->
	<!-------------------------------------------------------->

	<div id="question-graphics" class="text-center" style="font-size: 150%; margin-bottom:20px;">
		<span id="prompt"></span>
	</div>

	<!-------------------------------------------------------->
	<!-- ANSWER -->
	<!-------------------------------------------------------->

	<div class="">
		<fieldset id="runtimeFields">

		<div>
			<!-------------------------------------------------------->
			<!-- TEXTBOX TO ENTER ANSWER -->
			<!-------------------------------------------------------->
			<input type="text" name="answer" id="attempt" onkeypress="onKeypress(event)" >
		</div>

		<!-------------------------------------------------------->
		<!-- SPACE TO SHOW SCORED ANSWER -->
		<!-------------------------------------------------------->
		<div style="display: none; padding: 10px 0; font-size: 100%; min-height: 70px; margin-top: 2px;" id="answer-show-div"></div>

		<!-------------------------------------------------------->
		<!-- ANSWER OPTION BUTTONS  -->
		<!-------------------------------------------------------->		
		<div style="xmax-width: 400px; width:100%; min-height:300px;" id="optionButtons"></div>
			
		</fieldset>

	<!----------------------------------------------------------------------------->
	<!-- CONTROL BUTTONS -->
	<!----------------------------------------------------------------------------->

		<!-- BUTTONS ROW 1 -->
		<div class="form-group">
			<button class="btn btn-success btn-quiz" onclick="event.preventDefault(); nextAttempt()" id="button-next-attempt">Next Question</button>
			<button class="btn btn-primary btn-quiz" onclick="event.preventDefault(); checkAnswer(1)" id="button-check-answer">Check Typed Answer</button>
			<button class="btn btn-lg btn-primary btn-quiz" onclick="event.preventDefault(); quiz.start()" id="button-start">@LANG('content.Start Quiz')</button>
			<input class="btn btn-default btn-quiz" type="button" value="I KNOW IT (Alt+k)" onclick="checkAnswer(2)" id="button-know" style="display: default; background-color: green; color: white;">
			<input class="btn btn-default btn-quiz" type="button" value="I DON'T KNOW (Alt+d)" onclick="checkAnswer(3)" id="button-dont-know" style="display: none; background-color: red; color: white;">
			<input class="btn btn-default btn-quiz" type="button" value="Change to Wrong (Alt+c)" onclick="override()" id="button-override" style="display: none;">
			<button class="btn btn-warning btn-quiz" onclick="event.preventDefault(); resetQuiz()" id="button-stop">STOP QUIZ</button>
		</div>
		
		<!-- BUTTONS ROW 2 -->
		<div class="form-group" id="buttonRowReview">
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); first()"><span class="glyphicon glyphicon-circle-arrow-up"></span>@LANG('ui.First')</a></span>
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); prev()"><span class="glyphicon glyphicon-button-prev"></span>@LANG('ui.Prev')</a></span>
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); next()">@LANG('ui.Next')<span class="glyphicon glyphicon-button-next"></span></a></span>
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); last()">@LANG('ui.Last')<span class="glyphicon glyphicon-circle-arrow-down"></span></a></span>
			<span class="page-nav-buttons"><a class="btn btn-success btn-sm" role="button" href="#" onclick="event.preventDefault(); clear2()">@LANG('ui.Clear')</a></span>
		</div>

		<!-- SHOW ROUND RESULTS -->
		<div class="form-group">
			<span id="rounds"></span>
		</div>
		
		<!-- CHECKBOX ROW -->
		<div class="form-group">
			<div class="">
				<input type="checkbox" name="checkbox-type-answers" id="checkbox-type-answers" class="" onclick="quiz.typeAnswersClick()" />
				<label for="checkbox-type-answers" class="checkbox-big-label" onclick="quiz.typeAnswersClick()">@LANG('content.Type Answers')</label>
			</div>

			@if (!$isMc)
			<div class="">
				<input type="checkbox" name="checkbox-flip" id="checkbox-flip" onclick="quiz.flip()" />
				<label for="checkbox-flip" class="checkbox-big-label">@LANG('content.Flip Question/Answer')</label>
			</div>
			@endif
			
			<div class="">
				<input type="checkbox" name="checkbox-show-answers" id="checkbox-show-answers" onclick="quiz.showAnswersClick()" />
				<label for="checkbox-show-answers" class="checkbox-big-label">@LANG('content.Show Answers')</label>
			</div>
		</div>
	</div>

</section>

@if (false) // debug dump
	<div>
	@foreach($records as $rec)
		<p>{!!$rec['q']!!}</p>
	@endforeach
	</div>
@endif

@endif





	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->
	</div>
</div>
@endsection
