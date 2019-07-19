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
	<div style="margin: 50px 0">
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
	
	<span id="question-graphics" style="background: white; font-size: 200%;">
		<img id="question-prompt" src="/img/question-prompt.jpg" height="30" />
		<img id="question-right" src="/img/question-right.jpg" height="30" />
		<img id="question-wrong" src="/img/question-wrong.jpg" height="30" />
		<span id="promptQuestion"></span><span id="prompt"><a></a></span>
	</span>
	
	<!-------------------------------------------------------->
	<!-- ANSWER -->
	<!-------------------------------------------------------->	
	
	<div class="kbase form">
		<form method="POST" id="form-edit" action="/{{$prefix}}/updateXX/{{$record->id}}">
		<!-- ?= $this->Form->create($kbase) ? -->
		<fieldset id="runtimeFields">
		<h4 id="alertPrompt" style='margin: 0; margin-top: 10px; font-weight: normal;'>Type Answer:</h4>
		<div id="typeAnswers">
			
			<!-------------------------------------------------------->
			<!-- TEXTBOX TO ENTER ANSWER -->
			<!-------------------------------------------------------->
			<?php //echo $this->Form->input('answer', ['onkeypress' => 'onKeypress(event)', 'id' => 'attempt', 'label' => '', 'style' => 'padding: 10px; border: 1px gray solid; font-size: 200%; width:100%;']); ?>
			<input type="text" name="answer" id="attempt" onkeypress="onKeypress(event)" >
		</div>
			<!-------------------------------------------------------->
			<!-- SPACE TO SHOW SCORED ANSWER -->			
			<!-------------------------------------------------------->
			<div style="padding: 10px; font-size: 200%; min-height: 70px; xbackground: #efefef; xborder: 1px gray solid; margin-top: 2px;" id="answer-show-div"></div>

		</fieldset>

	<!----------------------------------------------------------------------------->
	<!-- CONTROL BUTTONS -->
	<!----------------------------------------------------------------------------->
		
		<!-- BUTTONS ROW 1 -->
		
		<input class="btn btn-default" type="button" value="Next Question" onclick="nextAttempt()" id="button-next-attempt">
		<input class="btn btn-default" type="button" value="Check Typed Answer" onclick="checkAnswer(1)" id="button-check-answer">
		<button class="btn btn-info" onclick="event.preventDefault(); quiz.start()" id="button-start">Start Quiz</button>
		<input class="btn btn-default" type="button" value="I KNOW IT (Alt+k)" onclick="checkAnswer(2)" id="button-know" style="display: default; background-color: green; color: white;">
		<input class="btn btn-default" type="button" value="I DON'T KNOW (Alt+d)" onclick="checkAnswer(3)" id="button-dont-know" style="display: none; background-color: red; color: white;">
		<button class="btn btn-warning" onclick="event.preventDefault(); resetQuiz()" id="button-stop">STOP QUIZ</button>
		<input class="btn btn-default" type="button" value="Change to Wrong (Alt+c)" onclick="override()" id="button-override" style="display: none;">
		<br/>
		
		<!-- BUTTONS ROW 2 -->
		
		<div style="margin:20px;" class="control-group" id="buttonRowReview">
			<button class="btn btn-success" onclick="event.preventDefault(); first()"><< First</button>
			<button class="btn btn-success" onclick="event.preventDefault(); prev()">< Prev</button>
			<button class="btn btn-success" onclick="event.preventDefault(); next()" id="button-next">Next ></button>
			<button class="btn btn-success" onclick="event.preventDefault(); last()">Last >></button>
			<button class="btn btn-success" onclick="event.preventDefault(); clear2()">Clear</button>
		</div>
						
		<!-- CHECKBOX ROW -->
		
		<div class="form-group">
@if (false)
			<?= $this->Form->checkbox('type-answers', ['id' => 'checkbox-type-answers', 'checked' => true, 'onclick' => 'quiz.typeAnswersClick()']) ?><span style='font-size: 100%; margin: 0 5px'>Type Answers</span>
			<?= $this->Form->checkbox('flip',         ['id' => 'checkbox-flip',         'checked' => false, 'onclick' => 'quiz.flip()']) ?><span style='margin: 0 5px'>Flip QnA</span>
@endif		
			<input type="checkbox" name="checkbox-type-answers" id="checkbox-type-answers" class="form-control" />
			<label for="checkbox-type-answers" class="checkbox-big-label">@LANG('content.Type Answers')</label>
			&nbsp;
			<input type="checkbox" name="checkbox-flip" id="checkbox-flip" class="form-control" />
			<label for="checkbox-type-answers" class="checkbox-big-label">@LANG('content.Flip QnA')</label>
		</div>
		
		{{ csrf_field() }}
		
		</form>
	
	</div>
	
</section>

<!----------------------------------------------------------------------------->
<!-- Show All Questions Section -->
<!----------------------------------------------------------------------------->

@if (false)
<section id="sectionReview" style='display: none; border: 0;'>

<?php if (isset($questionPrompt) && strlen($questionPrompt) > 0) : ?>
<div style="font-size: 150%;"><?= $questionPrompt ?></div>
<?php endif; ?>

<?php $cnt = 0; foreach($records as $rec) : ?>
	<div style="margin: 10px 0; background-color: <?php echo (($cnt % 2 == 0) ? '#FEFEE3' : 'white'); ?>; border: lightGray solid 1px">
		<div style="border-bottom: lightGray dashed 1px; padding: 5px; font-size: 130%; min-width: 200px; margin: 20px; padding-bottom: 20px;">
			<?= ++$cnt . '. &nbsp;' . $rec['q'] ?>
			<?php if ($canEdit) : ?>
				<span style='font-size: 70%; float: right;'>
					<a href=<?= '/kbase/edit/' . $rec['id'] ?> >Edit</a>
					&nbsp;&nbsp;
					<?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $rec['id']], ['confirm' => __('Are you sure you want to delete: {0}?', $rec['q'])]) ?>
				</span>
			<?php endif; ?>			
		</div>
		<div style="padding: 5px; font-size: 120%; min-width: 200px; margin: 20px; xmargin-right: 20px;">
			<?= $rec['a'] ?>
		</div>
	</div>
<?php endforeach; ?>

<!-- table>
	<?php $cnt = 0; foreach($records as $rec) : ?>
		<tr>
			<td style='width: 30px;'><?= ++$cnt ?>)&nbsp;</td><td style="width: 30%;"><?= $rec['q'] ?></td><td><?= $rec['a'] ?></td>
		</tr>
	<?php endforeach; ?>
</table -->

</section>
@endif;

@endif





	

		
		
	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->
	<!---------------------------------------------------------------------------------------------------------------->
	</div>
</div>
@endsection
