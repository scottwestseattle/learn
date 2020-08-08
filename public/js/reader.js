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

var _debug = true;
var _mute = false;
var _paused = false;
var _voices = null;
var _voicesLoadAttempts = 0;

$( document ).ready(function() {
	window.speechSynthesis.cancel();	
	setTimeout(loadVoices, 500);
	loadData();
	deck.start();
});

//
// slide class
//
function deck() {

	this.slides = [];   // slides
	this.speech = null;
	this.language = "";

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

    // this shows the beginning count down and then starts the first slide
	this.run = function() {
		reset();
		this.setStates(RUNSTATE_COUNTDOWN);
	    deck.showSlide();
		this.runSlide();
	}

    // this shows the current slide
	this.runSlide = function() {

		//debug("read next: " + curr);

        if (curr < max)
        {
			//debug("run slide: " + deck.slides[curr].title);
            loadSlide();
			deck.readSlide();
        }
        else
        {
    		stop();
        }
	}

	this.skipSlide = function() {

		switch(this.runState)
		{
			case RUNSTATE_COUNTDOWN:
			    this.runSlide();
				break;

			case RUNSTATE_BETWEEN:
			    this.runSlide();
				break;

			case RUNSTATE_RUN:
				break;

			default:
			    // for everything else reload the page
			    reload();
				break;
		}

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

        //debug("setting state to " + state);

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
        $(".slideCount").text((curr+1) + " of " + deck.slides.length);
        $(".slideDescription").text(deck.slides[curr].description);
	}

	this.readSlide = function() {
	    var slide = deck.slides[curr];
        //debug("read slide " + (curr+1) + ": " + slide.description);
		read(slide.description);
		
        //$(".slideCount").text(slide.number + " of " + deck.slides.length);
        //$(".slideDescription").text(deck.slides[curr].description);
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
		var number = 1;
		var description = container.data('description');
		var id = container.data('id');
        var seconds = parseInt(container.data('seconds'));
        var between = parseInt(container.data('between'));
        var countdown = parseInt(container.data('countdown'));
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
		deck.language = container.data('language'); // this is the language that the web site is in
    });	
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
        end();		
	}
	else
	{
		loadSlide();
	}
}

// skip the current countdown, slide, or between break
function skip()
{
    if (true)
        deck.skipSlide();
}

// reload the page
function reload()
{
    location.reload();
}

function pause()
{
	_paused = !_paused;
	
	if (_paused)
		window.speechSynthesis.cancel();
	else
		deck.runSlide();	
}

function resume()
{
}

function mute()
{
    _mute = !_mute;

    if (_mute)
    {
        $("#button-mute").removeClass("glyphicon-volume-up");
        $("#button-mute").addClass("glyphicon-volume-off");
    }
    else
    {
        $("#button-mute").removeClass("glyphicon-volume-off");
        $("#button-mute").addClass("glyphicon-volume-up");
    }

    debug("mute set to " + _mute.toString());
}

function playAudioFile(file)
{
    if (!_mute)
    {
        var a = document.getElementById("audio");
        var src = "/audio/" + file;
        $("#audio").attr("src", src)
        a.play();
    }
}

var _speechTimerId = null;
function read(text)
{
	clearTimeout(_speechTimerId);
	var utter = new SpeechSynthesisUtterance();

	if (deck.voice != null)
		utter.voice = deck.voice;  // if voices for language were found, then use the one we saved on start-up
	else
		utter.lang = deck.language; // if voice not found, try to the language from the web site
	
	utter.text = text;
	utter.onend = function(event) {
		if (!_paused)
			readNext();
	}
	
	var wordIndex = -1;
	var charIndexPrev = -1;
	utter.onboundary = function(event) {
		
		if (event.name == "word")
		{
			//debug(event.name + ': ' + word + ', index:' + event.charIndex + ", charLength: " + event.charLength);
			//setDebug(event.charLength + ' / ' + event.wordLength);
			var start = event.charIndex;
			var end = event.charIndex + event.charLength;
			var word = text.substring(start, end);
			var before = (start > 0) ? text.substring(0, start) : "";
			var after = text.substring(end);
			$("#slideDescription").html(before + '<span class="highlight-word">' + word + '</span>' + after);
		}
	}	
	
	window.speechSynthesis.speak(utter);
	_speechTimerId = setTimeout(speechBugWorkaround, 10000);		
}

function speechBugWorkaround()
{		
	debug("reset speech");
	window.speechSynthesis.resume(); // fix to keep speech from stopping
	
	if (window.speechSynthesis.speaking)
	{
		clearTimeout(_speechTimerId);
		_speechTimerId = setTimeout(speechBugWorkaround, 10000);		
	}
}

function readNext()
{	
	curr++;
	
	if (curr >= max)
	{
		curr = 0;
		nbr = 0;
        end();		
	}
	else
	{
		deck.runSlide();
	}
}

function tts(text)
{
    if (!_mute)
    {
        var utter = new SpeechSynthesisUtterance();

        utter.lang = 'es-US';
        utter.text = text;
		
        window.speechSynthesis.speak(utter);
		
    }
}

function loadVoices()
{
	_voices = speechSynthesis.getVoices();	

	if (_voices.length == 0 && _voicesLoadAttempts++ < 10)
	{
		debug("loading voices...not ready");
		setTimeout(loadVoices, 500);
		return;
	}
	
	//tts('ready');	
	
	var voiceSelect = document.querySelector('select');	
	var found = 0;
	var languageIndex = -1;
	
	if (_voices.length > 0)
	{
		for(i = 0; i < _voices.length ; i++) 
		{
			var option = document.createElement('option');
			option.textContent = _voices[i].name + ' (' + _voices[i].lang + ')';
			option.value = i; //_voices[i].lang;

			if(_voices[i].default) {
			  option.textContent += ' (default)';
			}

			option.setAttribute('data-lang', _voices[i].lang);
			option.setAttribute('data-name', _voices[i].name);
			
			// look for voices which map the language we are looking for and save the first one
			if (deck.language.startsWith("es") && (_voices[i].lang.startsWith("es") || _voices[i].lang.startsWith("spa")))
			{
				if (found == 0)
				{								
					found++;
					languageIndex = i;
				}
				
				voiceSelect.appendChild(option);
			}
			else if (deck.language.startsWith("en") && _voices[i].lang.startsWith("en"))
			{
				if (found == 0)
				{								
					found++;
					languageIndex = i;
				}
				
				voiceSelect.appendChild(option);
			}
		}		
	}
	else
	{
		var option = document.createElement('option');
		option.textContent = "Default voice set: " + deck.language;
		voiceSelect.appendChild(option);
	}
	
	if (found == 0)
	{
		if (languageIndex >= 0)
		{
			msg = "language not found: " + deck.language + ", using: " + _voices[languageIndex].lang + ", voice: " + _voices[languageIndex].name;
			deck.voice 		= _voices[languageIndex];
			$("#language").text("Language: " + deck.voice.lang);
			voiceSelect.index = languageIndex;
		}
		else
		{
			msg = "Language not found: " + deck.language + ", text can't be read correctly.";
			$("#language").text(msg);
			$("#languages").show();
		}
		
		debug(msg);
	}
	else
	{
		deck.voice = _voices[languageIndex];
		$("#language").text("Language: " + deck.voice.lang + ", voice: " + deck.voice.name);
	}

	voiceSelect.selectedIndex = 0;
}

function changeLanguage()
{
	var index = $("select")[0].selectedIndex;
	index = $("select").children("option:selected").val();
	deck.voice = _voices[index];

	$("#language").text("Language: " + deck.voice.lang + ", voice: " + deck.voice.name);
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
	//deck.setStates(RUNSTATE_END);
}

function end()
{
	clearTimeout(_speechTimerId);
    stop();
	reset();
	loadData();
	deck.start();

    //playAudioFile("small-crowd-applause.mp3");
	//tts("Terminado");
}

function reset()
{
	clear();

    // clear slides
	deck.slides.forEach(function(slide, index){
	    slide.done = false;
	});

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

function debug(text)
{
    if (_debug)
        console.log(text);
}
