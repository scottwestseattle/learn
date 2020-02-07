//-----------------------------------------------------------------------------
// THE TIMED SLIDES JS APPLICATION
//-----------------------------------------------------------------------------

//
// Constants
//
const RUNSTATE_START        = 1;
const RUNSTATE_COUNTDOWN    = 2;
const RUNSTATE_RUN          = 3;
const RUNSTATE_BETWEEN      = 4;
const RUNSTATE_END          = 5;

//
// numbers
//
var curr = 0;   // current slide
var nbr = 0;
var max = 0;    // number of slides

var deckTimer = null;

$( document ).ready(function() {
	loadData();
	deck.start();
});

//
// slide class
//
function deck() {

	this.slides = [];

	// options
	this.runState = RUNSTATE_START;

	//new:
	//this.quizTextRound = 'not set';
	//this.quizTextCorrect = 'not set';
	this.lessonId = 'not set';

	this.getId = function(index) {
		return this.slides[this.slides[index].order].id;
	}

	this.slide = function(index) {
		return this.slides[this.slides[index]];
	}

	this.start = function() {
        this.setStates(RUNSTATE_START);
	}

	this.run = function() {
		reset();
		this.setStates(RUNSTATE_COUNTDOWN);
	    deck.showSlide();

	    _countdownAudio = 3;
        startTimer(deck.slides[curr].countdown, this.runSlide);
	}

	this.runSlide = function() {

        clearTimer();
        if (curr < max)
        {
            loadSlide();
            var seconds = deck.slides[curr].seconds;

    	    _countdownAudio = 10;
            if (curr < (max - 1)) // not last one
                startTimer(seconds, deck.runBetween);
            else    // last one
                startTimer(seconds, stop);
        }
        else
        {
    		stop();
        }
	}

	this.runBetween = function() {
        clearTimer();
	    deck.setStates(RUNSTATE_BETWEEN);
	    betweenSeconds = deck.slides[curr].between;
        curr++; // do this here because we need the between seconds from the previous record
	    deck.showSlide(); // show the upcoming slide during the break

	    _countdownAudio = 3;
        startTimer(betweenSeconds, deck.runSlide);
	}

	this.showPanel = function(id) {

        // hide all
		$(".slide-panel").hide();

		// show the current panel
		$(id).show();

	}

	this.setFocus = function() {
		//todo: only done for start slide
		//if (this.isTypeAnswers())
		//	$("#attemptInput").focus();
	}

	this.setStates = function(state) {

		this.runState = state;

        var id = null;
		switch(state)
		{
			case RUNSTATE_START:
			    id = "#panel-start";
				break;

			case RUNSTATE_COUNTDOWN:
			    id = "#panel-countdown";
				break;

			case RUNSTATE_RUN:
			    id = "#panel-run";
				break;

			case RUNSTATE_BETWEEN:
			    id = "#panel-between";
				break;

			case RUNSTATE_END:
			    id = "#panel-end";
				break;

			default:
				$("#panel-start").show();
				this.setFocus();
				break;
		}

		this.showPanel(id);
	}

	this.showSlide = function() {
	    var slide = deck.slides[curr];
        $(".slideCount").text(slide.number + " of " + deck.slides.length);
        $(".slideTitle").text(slide.number + ". " + slide.title);
        $(".slideSeconds").text("For " + slide.seconds + " seconds");
        $(".slideDescription").text(deck.slides[curr].description);
        $(".sliderPhoto").attr("src", "/img/plancha/" + deck.slides[curr].photo)
        //alert(deck.slides[curr].photo);
        //$(".slidePhoto").text();
	}

	this.setAlertPrompt = function(text, color, bold = false) {
		//$("#alertPrompt").html(text);
		//$("#alertPrompt").css('color', color);
		//$("#alertPrompt").css('font-weight', bold ? 'bold' : 'normal');
	}
}

var deck = new deck();

function loadData()
{
	//
	// load slides arrays from the html tag 'data-' attributes, for example: data-question, data-answer, data-prompt
	//
	var i = 0;

	$('.data-slides').each(function() {
        var container = $(this);
        var service = container.data('title');

		var title = container.data('title');
		var number = parseInt(container.data('number'));
		var description = container.data('description');
		var id = container.data('id');
        var seconds = parseInt(container.data('seconds'));
        var between = parseInt(container.data('between'));
        var countdown = parseInt(container.data('countdown'));
        var photo = container.data('photo');
        var reps = 0;

		// add the record
		deck.slides[i] = {
		    title:title.toString(),
		    number:number,
		    description:description.toString(),
		    id:id.toString(),
		    order:0,
		    seconds:seconds,
		    between:between,
		    countdown:countdown,
		    photo:photo,
		    reps:reps,
		    done:false
		};

		//alert(deck.slides[i].between);
		//if (i == 0) alert(deck.slides[i].q);

		i++;
    });

	//
	// load misc variables
	//
	$('.data-misc').each(function() {
        var container = $(this);

		max = container.data('max');

		// new settings
		deck.quizTextDone = container.data('quiztext-done');
		deck.lessonId = container.data('lessonid');
		deck.touchPath = container.data('touchpath');

		if (i == 0)
			alert(deck.slides[i].title);

		i++;
    });

	//alert("max=" + max);
}

function first()
{
	curr = 0;
	loadSlide();
}

function last()
{
	curr = max - 1;
	loadSlide();
}

function next()
{
	curr++;
	if (curr >= max)
	{
		curr = 0;
		nbr = 0;
	}

	loadSlide();
}

function startTimer(seconds, func)
{
	clearTimeout(deckTimer);
    deckTimer = setTimeout(func, seconds * 1000);
    startInterval(seconds);

    showSeconds(seconds);
}

var timerInterval = 0;
var timerSeconds = 0;
var countdownTimer = null;
var _countdownAudio = 0;

function startInterval(seconds)
{
    timerInterval = seconds;
    countdownTimer = setInterval(updateTimer, 1000);
    showSeconds(timerInterval);
}

function updateTimer()
{
    if (_countdownAudio > 0 && timerInterval <= (_countdownAudio + 1))
        playAudio(timerInterval - 1);

    timerInterval--;
    showSeconds(timerInterval);

    if (timerInterval <= 0)
       clearTimer();
}

function clearTimer()
{
    clearInterval(countdownTimer);
    timerInterval = 0;
    showSeconds();
}

function playAudio(seconds)
{
    var a = document.getElementById("audio");
    var src = "/audio/" + seconds + ".mp3";
    $("#audio").attr("src", src)
    a.play();
}

function setDebug(text = null)
{
    $("#debug").text(text);
}

function showSeconds(text = null)
{
    $(".showSeconds").text(text);
}

function run()
{
    deck.run();
}

function stop()
{
	deck.setStates(RUNSTATE_END);
    clearTimer();
}

function reset()
{
	clear();

	for (var i = 0; i < max; i++)
		deck.slides[i].done = false;

	curr = 0;
	nbr = 0;

	//$("#stats").hide();
	//$("#panelEndofquizFinished").show();
	//$("#panelEndofquizStopped").hide();
}

function clear()
{
	//$("#promptQuestion").val('');
	//$("#promptQuestion").text('');
	//$("#prompt").val('');
	//$("#prompt").text('');
}

function loadSlide()
{
	deck.setStates(RUNSTATE_RUN);
	deck.showSlide();
	updateStatus();
}

function onKeypress(e)
{
	if (e.keyCode == 13) // enter key
	{
		e.stopImmediatePropagation();
		e.preventDefault();
		return false;
	}
	else
	{
		//$("#answer-show").val('');
	}
}

function updateStatus()
{
/*
	var total = right + wrong;
	var percent = total > 0 ? (right / total) * 100 : 0;
	percent = percent.toFixed(2).replace(/\.?0*$/,'');

	$("#statsCount").html("<span class='quizStats'>" + deck.quizTextQuestion + ": " + nbr + "/" + statsMax + "</span>");
	$("#statsScore").html("<span class='quizStats'>" + deck.quizTextdone + ": " + right + "/" + total + " (" + percent + "%)</span>");
	$("#statsDebug").html("<span class='quizStats'>"
		+ "round=" + round
		+ ", right=" + right
		+ ", wrong=" + wrong
		+ ", curr=" + curr
		+ ", order=" + deck.slides[curr].title
		+ ", nbr=" + nbr
		+ ", max=" + max
		+ ", statsMax=" + statsMax
		+ "<br/>"
		+ "<br/>"
		+ "<span style='font-size: 55%; '>"
		+ "</span>"
		+ "</span>");
*/
}

function touch(q)
{
    // if it's a word, update it's last display time
    if (deck.touchPath.length > 0) // if touchPath set
    {
        var path = '/' + deck.touchPath + '/' + q.id;
        ajaxexec(path);

        //alert('id: ' + q.id + ', word: ' + q.a);
    }
}
