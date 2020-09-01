//-----------------------------------------------------------------------------
// THE REVIEW VERSION OF QNA
//-----------------------------------------------------------------------------

//
// Constants
//
const RUNSTATE_START = 1;
const RUNSTATE_ASKING = 2;
const RUNSTATE_CHECKING = 3;
const RUNSTATE_ENDOFROUND = 4;
const RUNSTATE_ENDOFQUIZ = 5;

const CHECKANSWER_NORMAL = 1;
const CHECKANSWER_KNOW = 2;
const CHECKANSWER_DONTKNOW = 3;
const CHECKANSWER_FROMBUTTON = 4;

const SCORE_NOTSET = 0;
const SCORE_CORRECT = 1;
const SCORE_WRONG = 2;

const COLOR_QUESTION_PROMPT = 'black';

//
// numbers
//
var wrong = 0;
var right = 0;
var round = 1;
var curr = 0;
var nbr = 0;

//
// max number of qna
//
var max = 0;
var statsMax = 0;

var nextAttemptTimer = null;

$(document).keydown(function(event) {

	//alert(event.altKey);

	if (event.altKey )
	{

		if (event.which == 75 || event.which == 107) // alt-k
		{
			$("#button-know").click()
			event.preventDefault();
			event.stopPropagation();
		}
		else if (event.which == 68 || event.which == 100) // alt-d
		{
			$("#button-dont-know").click()
			event.preventDefault();
			event.stopPropagation();
		}
		else if (event.which == 99 || event.which == 67) // alt-c
		{
			$("#button-override").click();
			event.preventDefault();
			event.stopPropagation();
		}
	}

});

$( document ).ready(function() {

	quiz.setButtonStates(RUNSTATE_START);
	quiz.setControlStates();
	loadData();
	loadOrder();
	quiz.showAnswersClick();
	quiz.typeAnswersClick();

	$("#checkbox-type-answers").prop('checked', startWithTypeAnswers());

	quiz.showPanel();

	quiz.start();
});

//
// quiz class
//
function quiz() {

	this.qna = [];

	// options
	this._flip = false;
	this.promptQuestionNormal = ''; // loaded after ready
	this.promptQuestionReverse = ''; // loaded after ready
	this.promptQuestion = ''; // set to appropriate prompt: normal or reverse
	this.lastScore = SCORE_NOTSET;
	this.runState = RUNSTATE_START;

	//new:
	this.quizType = 0;
	this.isMc = 0;
	this.quizTextRound = 'not set';
	this.quizTextCorrect = 'not set';
	this.quizTextOf = 'not set';
	this.quizTextQuestion = 'not set';
	this.quizTextCorrectAnswer = 'not set'; // Correct! <- separate for the exclamations in Spanish
	this.quizTextWrongAnswer = 'not set';	// Wrong!
	this.quizTextOverrideCorrect = 'not set';
	this.quizTextOverrideWrong = 'not set';
	this.quizTextScoreChanged = 'not set';
	this.lessonId = 'not set';

	this.getQuestionId = function(index) {
		return this.qna[this.qna[index].order].id;
	}

	this.question = function(index) {
		return this.qna[this.qna[index].order];
	}

	this.setControlStates = function() {
		if (this.isTypeAnswers())
			$("#attemptInput").focus();
		else
			$("#button-start").focus();
	}

	this.showPanel = function(state = null) {

		$(".quiz-panel").hide();

		if (state == null)
			state = this.runState;

		switch(state)
		{
			case RUNSTATE_ENDOFQUIZ:
				$("#panel-endofquiz").show();
				break;

			case RUNSTATE_ENDOFROUND:
			{
				$("#panel-endofround").show();

				roundText = $("#panelResultsRoundBase").text() + ' ' + round;
				count = right + '/' + total;
				var fScore = score.toFixed(2);
				percent = fScore + '%';

				$("#panelResultsRound").text(roundText);
				$("#panelResultsPercent").text(percent);
				$("#panelResultsCount").text(count);

				// log the quiz round
				if (parseInt(round) == 1)
					ajaxexec('/lessons/log-quiz/' + this.lessonId + '/' + fScore);

				break;
			}
			case RUNSTATE_START:
				$("#panel-start").show();
				$("#panelStartCount").text(this.qna.length);
				break;
			default:
				$("#panel-quiz").show();
				this.setFocus();
				break;
		}

	}

	//todo: only implemented for setFocus()
	this.isTypeAnswers = function() {
		return $("#checkbox-type-answers").prop('checked');
	}

	this.setFocus = function() {

		//todo: only done for start quiz
		if (this.isTypeAnswers())
			$("#attemptInput").focus();
	}

	this.setButtonStates = function(state) {

		this.runState = state;

		if (this.isMc)
		{
			$(".hide-for-mc").hide();
		}

		if (state == RUNSTATE_START)
		{
			//
			// only show the start button
			//
			quiz.showOverrideButton(false, null);
			$("#button-check-answer").hide();
			$("#button-next-attempt").hide();
			$("#button-know").hide();
			$("#button-dont-know").hide();

			//$("#button-start").show();
			$("#button-stop").hide();

			$("#question-right").hide();
			$("#question-wrong").hide();
			$("#question-prompt").hide();

			$("#attemptInput").hide();
		}
		else if (state == RUNSTATE_ASKING)
		{
			//
			// asking the question
			//

			if (quiz.isMc)
			{
				$("#button-dont-know").hide();
				$("#button-check-answer").hide();
				$("#button-know").hide();
			}
			else
			{
				if (this.isTypeAnswers())
				{
					$("#button-check-answer").show();
					$("#button-dont-know").hide();
					$("#button-know").show();
				}
				else
				{
					$("#button-check-answer").hide();
					$("#button-dont-know").show();
					$("#button-know").show();
					$("#button-know").focus();
				}
			}

			quiz.showOverrideButton(false, null);
			$("#button-next-attempt").hide();
			//$("#button-start").hide();
			$("#button-stop").show();

			$("#question-right").hide();
			$("#question-wrong").hide();
			$("#question-prompt").show();
		}
		else if (state == RUNSTATE_CHECKING)
		{
			$("#question-prompt").hide();

			//
			// checking the answer
			//
			$("#button-check-answer").hide();
			$("#button-know").hide();
			$("#button-dont-know").hide();

			quiz.showOverrideButton(true, null);
			//$("#button-start").hide();
			$("#button-stop").show();

			if (quiz.isMc)
			{
				// change the button colors to show the answer
				$(".btn-right").css('background-color','#5CB85C');
				$(".btn-right").css('border-color','#5CB85C');

				// wrong button option
				$(".btn-wrong").css('background-color','LightGray');
				$(".btn-wrong").css('border-color','LightGray');

				// answer chosen but wrong
				$(".btn-chosen").css('background-color','red');
				$(".btn-chosen").css('border-color','black');

				// [I don't know] button clicked, show answer in yellow
				$(".btn-right-show").css('color', 'purple');
				$(".btn-right-show").css('background-color', '#ffc107');
				$(".btn-right-show").css('border-color', '#ffed4a');

				// check if the chosen button is invisible
				//if ($(".btn-chosen").is(":hidden"))
				//{
				//	$(".btn-chosen").css('color','red');
				//}

				$("#button-next-attempt").show();
			}
			else
			{
				$("#button-next-attempt").show();
			}
		}
		else
		{
			alert("setButtonStates - bad value");
		}
	}

	this.flipped = function() {
		return $('#checkbox-flip').prop('checked');
	}
	
	this.useDefinition = function() {
		return $("#checkbox-use-definition").prop("checked");
	}
	

	//review: not used
	this.flip = function() {
		this._flip = !this._flip;
		this.promptQuestion = (this._flip ? this.promptQuestionReverse : this.promptQuestionNormal);
		this.showQuestion();
	}

	this.start = function() {
		$("#rounds").text($("#roundsStart").text());
		resetQuiz();
		this.showQuestion();
		nbr = 1;
		updateScore();

		this.setButtonStates(RUNSTATE_ASKING);

		this.showPanel();
	}

	this.showQuestion = function() {

		clear();
		var q = getQuestion();
		var a = getAnswer();
		var currIndex = quiz.qna[curr].order;
		var currQuestion = quiz.qna[currIndex];

		// show question
		$("#prompt").html(q);

		// shows or hides answer option buttons according to checkbox
		displayAnswerButtons();
		
		if (true)
		{
			// new way where buttons are in html and configured from here
			var answers = new Array();
			var choices = Math.min(quiz.qna.length, 5);
						
			for (var i = 0; i < choices; i++) // start at one because we've already added the correct answer
			{
				var rnd = Math.floor(Math.random() * choices);

				// if it's not the correct answer AND it's not already in the answers list
				if (!answers.includes(rnd))
				{
					// not in array yet, add it
					answers.push(rnd);
				}
				else
				{
					// continue from the random position until we find an unused answer
					var loop = 0;
					while(loop < quiz.qna.length) // don't loop forever
					{
						rnd++;
						if (rnd >= choices)
							rnd = 0; // wrap to the beginning and keep looking
						
						// if not in the answers list, add it
						if (!answers.includes(rnd))
						{
							answers.push(rnd);
						}

						loop++;
					}
				}
			}
			
			// now lay in the correct answer randomly if it's not already in the array
			if (!answers.includes(currIndex))
			{
				var correctButton = Math.floor(Math.random() * choices);
				answers[correctButton] = currIndex;
			}

			if (false)
			{
				console.log('choices: ' + choices);
				console.log('currIndex: ' + currIndex);
				console.log('correct button: ' + correctButton);				
				answers.forEach(function (item, index, arr) {
					console.log('random array: ' + index + ', item: ' + item + ', ans: ' +  quiz.qna[item].a);
				});				
			}

			// reset the buttons
			$(".btn-quiz-mc3").removeClass('btn-right');
			$(".btn-quiz-mc3").removeClass('btn-right-show');
			$(".btn-quiz-mc3").removeClass('btn-wrong');
			$(".btn-quiz-mc3").removeClass('btn-chosen');
			$(".btn-quiz-mc3").css('background-color', '#2fa360');
			$(".btn-quiz-mc3").css('border-color', '#2d995b');
			$(".btn-quiz-mc3").css('color', 'white');
			
			answers.forEach(function (item, index, arr) {
				var text = getAnswer(item); // quiz.qna[item].a;
				var btn = '#' + index;
												
				if (item == currIndex) // the right answer
					$(btn).addClass('btn-right');
				else
					$(btn).addClass('btn-wrong');
					
				$(btn).html(text);
				
				// buttons start as hidden in case we are using less than the max (5)
				// only show the ones we are using so we're not lugging around dead empty buttons
				$(btn).show(); 
			});
		}
		else
		{
			// old way where buttons were created in php
			o = quiz.qna[quiz.qna[curr].order].options;
			if (o && o.length > 0)
				$("#optionButtons").html(o);	// show the option buttons
		}

		// show answer
		if ($("#checkbox-show").prop('checked'))
		{
			$("#answer-show").html(a);
			$("#answer-show").val(a);
		}

		// show prompt
		$("#promptQuestion").text(quiz.promptQuestion + " ");

		var typeAnswers = !this.isMc && this.isTypeAnswers();
		if (typeAnswers)
		{
			$("#attemptInput").show();
			$("#attemptInput").focus();
		}
		else
		{
			$("#attemptInput").hide();
			$("#button-know").focus();
		}

		quiz.setAlertPrompt(quiz.promptQuestion, COLOR_QUESTION_PROMPT);

		$("#stats").show();
	}

	this.showOverrideButton = function(show, label)
	{
		$("#button-override").prop('disabled', !show);

		if (label != null)
			$("#button-override").val(label);

		if (!show)
		{
			$("#button-override").hide();
			$("#button-override").css('background-color', 'white');
		}
		else
		{
			$("#button-override").show();
			$("#button-override").css('background-color', 'yellow');
		}
	}

	this.typeAnswersClick = function()
	{
		this.setButtonStates(this.runState);

		var typeAnswers = $("#checkbox-type-answers").prop('checked');

		if (this.runState != RUNSTATE_START)
		{
			if (typeAnswers)
			{
				$("#attemptInput").show();
				$("#attemptInput").focus();
			}
			else
			{
				$("#attemptInput").hide();
			}
		}

		if (this.runState == RUNSTATE_ASKING)
		{
			quiz.setAlertPrompt(quiz.promptQuestion, COLOR_QUESTION_PROMPT);
		}
	}

	this.showAnswersClick = function() {

		var showAnswers = $("#checkbox-show").prop('checked');
		var answer = '';
		if (showAnswers)
		{
			$("#buttonRowReview").css('display', 'default');
			answer = getAnswer();
		}
		else
		{
			$("#buttonRowReview").css('display', 'none');
		}

		$("#answer-show").val(answer);
		$("#answer-show").html(answer);
	}

	this.showList = function() {
		if ($("#showAllLink").html() == "Show All Questions")
		{
			$("#showAllLink").html("Show Quiz");
			$(".quizSection").hide();
			$("#sectionReview").show();
		}
		else
		{
			$("#showAllLink").html("Show All Questions");
			$(".quizSection").show();
			$("#sectionReview").hide();
		}
	}

	this.setAlertPrompt = function(text, color, bold = false) {

		$("#alertPrompt").html(text);
		$("#alertPrompt").css('color', color);
		$("#alertPrompt").css('font-weight', bold ? 'bold' : 'normal');
	}
}

var quiz = new quiz();

function loadData()
{
	//
	// load qna arrays from the html tag 'data-' attributes, for example: data-question, data-answer, data-prompt
	//
	var i = 0;
	$('.data-qna').each(function() {
        var container = $(this);
        var service = container.data('title');

		var question = container.data('question');
		var answer = container.data('answer');
		var def = container.data('definition');
		var options = container.data('options'); // mc options
		var id = container.data('id');
		var wordId = container.data('wid');

		// add the record
		quiz.qna[i] = {
		    q:question.toString(),
		    a:answer.toString(),
			definition:def.toString(),
		    id:id.toString(),
		    options:options.toString(),
		    order:0,
		    correct:false
		};

		//alert(quiz.qna[i].id);
		//if (i == 0) alert(quiz.qna[i].q);

		i++;
    });

	//
	// load misc variables
	//
	$('.data-misc').each(function() {
        var container = $(this);

		max = container.data('max');
		quiz.promptQuestionNormal = container.data('prompt');
		quiz.promptQuestionReverse = container.data('prompt-reverse');
		quiz.promptQuestion = quiz.promptQuestionNormal;

		// new settings
		quiz.quizType = container.data('quiztype');
		quiz.isMc = container.data('ismc');
		quiz.quizTextRound = container.data('quiztext-round');
		quiz.quizTextCorrect = container.data('quiztext-correct');
		quiz.quizTextOf = container.data('quiztext-of');
		quiz.quizTextQuestion = container.data('quiztext-question');
		quiz.quizTextCorrectAnswer = container.data('quiztext-correct-answer');
		quiz.quizTextWrongAnswer = container.data('quiztext-wrong-answer');
		quiz.quizTextOverrideCorrect = container.data('quiztext-override-correct') + " (Alt+c)";
		quiz.quizTextOverrideWrong = container.data('quiztext-override-wrong') + " (Alt+c)";
		quiz.quizTextScoreChanged = container.data('quiztext-score-changed');
		quiz.lessonId = container.data('lessonid');
		quiz.touchPath = container.data('touchpath');

		i++;
    });

	statsMax = max;
	//alert("max=" + max + ", prompt=" + quiz.promptQuestion);
}

function loadOrder()
{
	//
	// load random map in a work array
	//
	var order = [];
	for (var i = 0; i < max; i++)
		order[i] = i;

	order = shuffle(order); // mix it up

	//
	// now copy it to the real place
	//
	for (var i = 0; i < max; i++)
		quiz.qna[i].order = order[i];

	/*
	var s = "";
	for (var i = 0; i < max; i++)
		s += quiz.qna[i].order + ",";
	alert(s);
	*/
}

function shuffle(array)
{
	var currentIndex = array.length, temporaryValue, randomIndex ;

	// While there are elements to shuffle...
	while (0 !== currentIndex)
	{
		// Pick a remaining element...
		randomIndex = Math.floor(Math.random() * currentIndex);
		currentIndex -= 1;

		// And swap it with the current element.
		temporaryValue = array[currentIndex];
		array[currentIndex] = array[randomIndex];
		array[randomIndex] = temporaryValue;
	}

	return array;
}

function first()
{
	curr = 0;
	loadQuestion();
}

function last()
{
	curr = max - 1;
	loadQuestion();
}

function next()
{
	curr++;
	if (curr >= max)
	{
		curr = 0;
		nbr = 0;
	}

	loadQuestion();
}

function nextAttempt()
{
	clearTimeout(nextAttemptTimer);

	quiz.setButtonStates(RUNSTATE_ASKING);

	var done = false;
	var count = 0;
	while(!done)
	{
		curr++;

		// check if at the end of round
		if (curr >= max)
		{
			curr = 0;
			nbr = 0;
			score = (right / (right+wrong)) * 100;
			total = right + wrong;
			if (total > 0)
			{
				results = '<p>' + quiz.quizTextRound + ' ' + round + ': ' + score.toFixed(2) + '% (' + right + '/' + total + ')</p>';
				if (round == 1)
					$("#rounds").text('');
				$("#rounds").append(results);
				//alert('End of Round, Starting next round');
				quiz.showPanel(RUNSTATE_ENDOFROUND);
			}
			else
			{
				//alert('End of Round???');
			}

			//alert('End of Round ' + round + ': ' + score.toFixed(2) + '% (' + right + ' of ' + (right+wrong) + ')');

			round++;
			statsMax = wrong;
			right = 0;
			wrong = 0;
		}

		// if this question has not been answered correctly yet
		if (!quiz.qna[quiz.qna[curr].order].correct)
		{
			loadQuestion();
			done = true;
		}
		else if (count++ >= max)
		{
			// no wrong answers left
			//alert('Done, all answered correctly!!');
			//quiz.showPanel(RUNSTATE_ENDOFQUIZ);
			//resetQuiz();
			quiz.runState = RUNSTATE_ENDOFQUIZ;
			done = true;
		}

		if (count > 10000)
		{
			// break out just in care we're looping
			break;
		}
	}
}

function prev()
{
	curr--;
	if (curr < 0)
		curr = max - 1;

	loadQuestion();
}

function startQuiz()
{
	quiz.setButtonStates(RUNSTATE_START);
	quiz.setControlStates();
	loadData();
	loadOrder();
	$("#checkbox-type-answers").prop('checked', startWithTypeAnswers());

	quiz.showPanel();
}

function continueQuiz()
{
	// if end of round but not end of quiz, keep asking
	if (quiz.runState == RUNSTATE_ENDOFROUND)
		quiz.runState = RUNSTATE_ASKING;

	quiz.showPanel();
}

function stopQuiz()
{
	$("#panelEndofquizFinished").hide();
	$("#panelEndofquizStopped").show();
	quiz.runState = RUNSTATE_ENDOFQUIZ;
	quiz.showPanel();
}

function showAnswer()
{
	$("#button-show-answer").hide();
	var id = $(".btn-right").attr('id');
	$('.btn-right').addClass('btn-right-show');	
	checkAnswerFromButton(id, true);
}

function showAnswerOptionButtons()
{
	// use visibility instead of show/hide to keep the spacing
	$("#optionButtons").css('visibility', 'visible');
	$("#button-show-options").hide();
	$("#button-show-answer").show();	
}

function displayAnswerButtons()
{	
	if ($("#checkbox-hide-options").prop('checked'))
	{
		// use visibility instead of show/hide to keep the spacing
		$("#optionButtons").css('visibility', 'hidden'); 
		
		$("#button-show-options").show();
		$("#button-show-answer").hide();
	}
	else
	{
		$("#optionButtons").css('visibility', 'visible');
		$("#button-show-options").hide();
		$("#button-show-answer").show();
	}
}

function resetQuiz()
{
	clear();

	for (var i = 0; i < max; i++)
		quiz.qna[i].correct = false;

	curr = 0;
	right = 0;
	wrong = 0;
	round = 1;
	statsMax = max;
	nbr = 0;

	loadOrder();

	$("#stats").hide();
	$("#panelEndofquizFinished").show();
	$("#panelEndofquizStopped").hide();
}

function clear2()
{
	clear();
}

function clear()
{
	$("#promptQuestion").val('');
	$("#promptQuestion").text('');
	$("#prompt").val('');
	$("#prompt").text('');

	$("#attemptInput").val('');
	$("#attemptInput").text('');

	$("#answer-show").val('');
	$("#answer-show").text('');

	$("#answer-show-div").text('');
}

function getAnswer(index = null)
{
	var rc = null;
	index = (index == null) ? quiz.qna[curr].order : index;

	if (quiz.flipped())
	{
		rc = quiz.qna[index].q;
	}
	else
	{
		if (quiz.useDefinition())
			rc = quiz.qna[index].definition;
		else
			rc = quiz.qna[index].a;
	}

	return rc;
}

function getQuestion(index = null)
{
	var rc = null;
	index = (index == null) ? quiz.qna[curr].order : index;

	if (quiz.flipped())
	{
		if (quiz.useDefinition())
			rc = quiz.qna[index].definition;
		else
			rc = quiz.qna[index].a;
	}
	else
	{
		rc = quiz.qna[index].q;
	}

	return rc;

}

function loadQuestion()
{
	quiz.showQuestion();
	nbr++;
	updateScore();

	quiz.setAlertPrompt(quiz.promptQuestion, COLOR_QUESTION_PROMPT);
}

function reloadQuestion()
{
	quiz.showQuestion();
}

function toStringBoolArray(a)
{
	var s = '';

	for (var i = 0; i < a.length; i++)
	{
		s += (a[i] ? "1" : "0");
	}

	return s;
}

function onKeypress(e)
{
	if (e.keyCode == 13)
	{
		e.stopImmediatePropagation();
		e.preventDefault();
		checkAnswer(CHECKANSWER_NORMAL);
		return false;
	}
	else
	{
		$("#answer-show").val('');
		//$("#answer-show").text('');
	}
}

function cleanUpSpecialChars(str)
{
	var start = str;

    str = str.replace(/[����]/g,"e");
    str = str.replace(/[������]/g,"A");
    str = str.replace(/[������]/g,"a");
    str = str.replace(/[����]/g,"E");
	//str = str.replace(/[^a-z0-9]/gi,''); // final clean up
	//alert(str);

	//if (str == 'Noumea' || str == 'Noum�a' || start != str)
	//alert('start: ' + start + ", str: " + str);

    return str;
}

function checkAnswerFromButtonClick(event)
{
	checkAnswerFromButton(event.target.id);
}

function checkAnswerFromButton(id, showOnly = false)
{
	var correctButtonClicked = false;
	id = '#' + id;

	if (quiz.runState == RUNSTATE_ASKING)
	{
		if ($(id).hasClass("btn-right"))
		{
			// if RIGHT answer chosen
			correctButtonClicked = true;
		}
		else
		{
			// if WRONG answer chosen, mark so we can show it as red
			$(id).addClass( "btn-chosen" ); // set a class on the chosen button so we don't have to pass the id all the way through
		}

		//alert(answer);
		var timerSeconds = 2;
		if (!checkAnswer(CHECKANSWER_FROMBUTTON, correctButtonClicked, showOnly))
		    timerSeconds *= 2; // add extra time for wrong answer

		// load next question on a timer
		nextAttemptTimer = setTimeout(nextAttempt, timerSeconds * 1000 /* make it milliseconds */);
	}
	else if (quiz.runState == RUNSTATE_CHECKING)
	{
		nextAttempt();
	}
}

function checkAnswer(checkOptions, correctButtonClicked = false, showOnly = false)
{
	quiz.setButtonStates(RUNSTATE_CHECKING);
	$("#question-prompt").hide();

	var answerRaw = getAnswer();
	var answer = cleanUpSpecialChars(answerRaw);
	var attempt = $("#attemptInput").val();
	var rightAnswer = false;
	var result = '';
	var answerColor = 'black';

	if (checkOptions == CHECKANSWER_KNOW)
	{
		answerColor = "#4993FD";
		result = quiz.quizTextCorrectAnswer;
		quiz.qna[quiz.qna[curr].order].correct = true;
		$("#button-next-attempt").focus();
		quiz.showOverrideButton(true, quiz.quizTextOverrideWrong);
		quiz.lastScore = SCORE_CORRECT;
		$("#question-right").show();

		right++;
		rightAnswer = true;
	}
	else if (checkOptions == CHECKANSWER_DONTKNOW)
	{
		result = quiz.quizTextWrongAnswer;
		answerColor = 'red';
		$("#button-next-attempt").focus();
		quiz.showOverrideButton(true, quiz.quizTextOverrideCorrect);
		quiz.lastScore = SCORE_WRONG;
		$("#question-wrong").show();

		wrong++;
	}
	else
	{
		var correctAnswer = false;
		if (checkOptions == CHECKANSWER_FROMBUTTON)
		{
			correctAnswer = correctButtonClicked;		
		}
		else
		{
			// typing the answers so check the entry
			cleanAnswer = cleanQna(jQuery('<span>').html(answer).text());
			cleanAttempt = cleanQna(jQuery('<span>').html(attempt).text());
			if (cleanAnswer != cleanAttempt)
			{
				cleanAnswer = accentFold(cleanAnswer);
				cleanAttempt = accentFold(cleanAttempt);
			}
			
			correctAnswer = ((answer != null && attempt != null) && cleanAnswer == cleanAttempt);
		}

		if (correctAnswer)
		{
			if (showOnly)
			{
				result = "Answer marked as wrong";
				answerColor = 'purple';
				quiz.qna[quiz.qna[curr].order].correct = false;
				
			}
			else
			{
				result = quiz.quizTextCorrectAnswer;
				answerColor = 'green';
				quiz.qna[quiz.qna[curr].order].correct = true;
			}
			
			$("#button-next-attempt").focus();
			quiz.showOverrideButton(false, quiz.quizTextOverrideWrong);
			quiz.lastScore = SCORE_WRONG;
			$("#question-right").show();
			
			if (showOnly)
			{
				wrong++;
			}
			else
			{
				right++;
				rightAnswer = true;

				// mark the question since it was answered correctly
				touch(quiz.qna[quiz.qna[curr].order]);
			}
		}
		else
		{
			result = quiz.quizTextWrongAnswer;
			answerColor = 'red';
			$("#button-next-attempt").focus();
			quiz.showOverrideButton(true, quiz.quizTextOverrideCorrect);
			quiz.lastScore = SCORE_WRONG;
			$("#question-wrong").show();
			wrong++;
		}
	}

	quiz.setAlertPrompt(result, answerColor, /* bold = */ true);

	var answerMsg = answer;
	if (answer != answerRaw)
		answerMsg += " (" + answerRaw + ")";

	//alert(answer);

	if (quiz.isMc)
	{
		// the answer is shown in the button
		$("#answer-show-div").hide();
	}
	else
	{
		$("#answer-show-div").show();
		$("#answer-show-div").html(answerMsg);
		$("#answer-show-div").css('color', answerColor);
	}

	updateScore();

	return rightAnswer;
}

function cleanQna(str)
{
	str = str.toLowerCase().trim();
	str = str.replace(/\.|\,/gi, ""); // remove all ',' and '.'

	return str;
}

function updateScore()
{
	var total = right + wrong;
	var percent = total > 0 ? (right / total) * 100 : 0;
	percent = percent.toFixed(2).replace(/\.?0*$/,'');

	$("#statsCount").html("<span class='quizStats'>" + quiz.quizTextQuestion + ": " + nbr + "/" + statsMax + "</span>");
	$("#statsScore").html("<span class='quizStats'>" + quiz.quizTextCorrect + ": " + right + "/" + total + " (" + percent + "%)</span>");
	$("#statsDebug").html("<span class='quizStats'>"
		+ "round=" + round
		+ ", right=" + right
		+ ", wrong=" + wrong
		+ ", curr=" + curr
		+ ", order=" + quiz.qna[curr].order
		+ ", nbr=" + nbr
		+ ", max=" + max
		+ ", statsMax=" + statsMax
		+ "<br/>"
		//+ "order=" + quiz.order.toString()
		//+ ", correct=" + toStringBoolArray(quiz.correct)
		+ "<br/>"
		+ "<span style='font-size: 55%; '>"
		//+ "q=" + quiz.questions.toString()
		+ "</span>"
		+ "</span>");
}

function override()
{
	quiz.showOverrideButton(false, null);

	var answer = getAnswer();
	var result = "";
	var color = "black";

	if (quiz.lastScore == SCORE_NOTSET)
	{
		// no action
		alert('bad logic: no last score');
	}
	else if (quiz.lastScore == SCORE_WRONG)
	{
		//
		// it was wrong, make it right
		//
		quiz.qna[quiz.qna[curr].order].correct = true;
		$("#question-right").show();
		$("#question-wrong").hide();
		$("#question-prompt").hide();
		result = "Correct: ";
		color = "darkBlue";
		right++;
		wrong--;
	}
	else if (quiz.lastScore == SCORE_CORRECT)
	{
		//
		// it was right, make it wrong
		//
		$("#question-right").hide();
		$("#question-wrong").show();
		$("#question-prompt").hide();
		quiz.qna[quiz.qna[curr].order].correct = false;
		result = "Wrong: ";
		color = "red";

		right--;
		wrong++;
	}

	quiz.setAlertPrompt(quiz.quizTextScoreChanged, color);

	answer = result + answer;
	$("#answer-show").html(answer);
	$("#answer-show").val(answer);
	$("#answer-show").css("color", color);
	$("#answer-show-div").html(answer);
	$("#answer-show-div").val(answer);
	$("#answer-show-div").css("color", color);
	updateScore();
	$("#button-next-attempt").focus();
}

function startWithTypeAnswers()
{
	if (isMobile.any())
		return false;

	if (quiz.isMc)
		return false;

	return true;
}

var accentMap = {
  'á':'a',
  'é':'e',
  'í':'i',
  'ó':'o',
  'ú':'u',
  'ü':'u',
  'ñ':'n',
  'Á':'A',
  'É':'E',
  'Í':'I',
  'Ú':'U',
  'Ü':'U',
  'Ñ':'N'
};

function accentFold (s)
{
	if (!s) { return ''; }
	var ret = '';
	for (var i = 0; i < s.length; i++) {
		ret += accentMap[s.charAt(i)] || s.charAt(i);
	}

	return ret;
}

function touch(q)
{
    // if it's a word, update it's last display time
    if (quiz.touchPath.length > 0) // if touchPath set
    {
        var path = '/' + quiz.touchPath + '/' + q.id;
        ajaxexec(path);

        //alert('id: ' + q.id + ', word: ' + q.a);
    }
}

function flip()
{
	quiz.flip();
}