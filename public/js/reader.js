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
var max = 0;    // number of slides

var _debug = true;
var _mute = false;
var _paused = false;
var _lastCharIndex = 0;
var _voices = null;
var _voicesLoadAttempts = 0;
var _cancelled = false;
var _readFontSize = 18;
var _maxFontSize = 99;

$(document).ready(function() {

	var fontSize = localStorage['readFontSize'];
	if (!fontSize)
		localStorage['readFontSize'] = _readFontSize;
	else
	{
		_readFontSize = parseInt(fontSize);
		if (_readFontSize > _maxFontSize)
			_readFontSize = _maxFontsize;
	}
	setFontSize();
	
	window.speechSynthesis.cancel();	
	setTimeout(loadVoices, 500);
	loadData();
	setReadLocation();
	deck.start();
	
	$("#pause").hide();
	$("#resume").show();
	
});

$(window).on('unload', function() {
	window.speechSynthesis.cancel();	
});

$(document).keyup(function(event) {
    if(event.keyCode == 32)		// spacebar
	{
		togglePause();
    }
    else if(event.keyCode == 37) // left arrow
	{
		prev();
    }
    else if(event.keyCode == 39) // right arrow
	{
		next();
    }
});

//
// slide class
//
function deck() {

	this.slides = [];   // slides
	this.speech = null;
	this.language = "";
	this.isAdmin = false;

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
	this.run = function(fromBeginning = true) {
		if (fromBeginning)
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
		$('#selected-word').text('');
		$('#selected-word-definition').text('');
	}

	this.readSlideResume = function() {
	    var slide = deck.slides[curr];
		read(slide.description, _lastCharIndex);
	}
	
	this.readSlide = function() {
	    var slide = deck.slides[curr];
        //debug("read slide " + (curr+1) + ": " + slide.description);
		read(slide.description, 0);
		
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
		deck.language = container.data('language');			// this is the language that the web site is in
		deck.isAdmin = container.data('isadmin') == '1';
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

function prev()
{
	pause();
	_cancelled = true;
	_lastCharIndex = 0;
	
	curr--;
	if (curr < 0)
		curr = max - 1;

	loadSlide();
}

function incLine(count)
{	
	curr += count;
	mod = curr % 50;
	curr -= (mod);
	
	if (curr < 0)
		curr = 0;
	else if (curr >= max)
		curr = 0;
	
	$('#button-start-reading').text("Start reading from the beginning");
	$('#readCurrLine').text("Line: " + (curr + 1));
	$('#button-continue-reading').show();
	$('#button-continue-reading').text("Continue reading from line " + (curr + 1));
	
}

function next()
{
	pause();
	_cancelled = true;
	_lastCharIndex = 0;
	
	curr++;
	if (curr >= max)
		curr = 0;

	loadSlide();
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

function run()
{
	resume();
}

function runContinue() 
{
	$("#pause").show();
	$("#resume").hide();	
	deck.run(/* fromBeginning = */ false);
}

function togglePause()
{
	if (_paused)
		resume();
	else
		pause();
}

function pause()
{
	_paused = true;
	window.speechSynthesis.cancel();
	$("#pause").hide();
	$("#resume").show();
}

function resume()
{
	if (_paused)
	{
		_paused = false;
		deck.readSlideResume(); // picks up at curr	+ _lastCharIndex
	}
	else
	{
		deck.run();	
	}
	
	$("#pause").show();
	$("#resume").hide();
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
var _utter = null;
function read(text, charIndex)
{
	_cancelled = false;
	clearTimeout(_speechTimerId);
	_utter = new SpeechSynthesisUtterance();

	if (deck.voice != null)
	{
		_utter.voice = deck.voice;  // if voices for language were found, then use the one we saved on start-up
		_utter.lang = deck.voice.lang;
	}
	else
	{
		_utter.lang = deck.language; // if voice not found, try to the language from the web site
	}
	
	_utter.text = text.substring(charIndex);
	_utter.onend = function(event) {
		if (!_paused && !_cancelled)
			readNext();
		
		_cancelled = false;
	}
	
	var wordIndex = -1;
	var charIndexPrev = -1;
	_utter.onboundary = function(event) {
		
		// Highlight browser support
		// Windows 10 - Edge
		// Windows 10 - Chrome (Microsoft voices only)
		// Windows 10 - Firefox

		// Android - Edge (case 2)
		// Android - Firefox
		// Android - Firefox Focus

		// MacBook - Safari
		// MacBook - Chrome
		// MacBook - Firefox
		
		// Not Supported:
		// Windows 10 - Chrome - Google Voices
		// Android - Chrome (only has Google voices, need to install more)
		// Android - TOR (no voices)```````````````````````````````````````````
		// Android - Opera (no voices)```````````````````````````````````````````
		
		if (event.name == "word")
		{
			//setDebug(event.charLength + ' / ' + event.wordLength);			
			var cases = -1; 
			if (typeof event.charLength !== 'undefined')
			{
				if (event.charLength < text.length)
				{
					//case 1: charLength implemented correctly in browser
					cases = 1;
					var start = event.charIndex + charIndex;
					_lastCharIndex = start;
					var end = start + event.charLength;
					var word = text.substring(start, end);
					var before = (start > 0) ? text.substring(0, start) : "";
					var after = text.substring(end);
					$("#slideDescription").html(before + '<span class="highlight-word">' + word + '</span>' + after);
				}
				else
				{
					//case 2: charLength exists but it's always set to length of the full text being read (Edge on Mobile)
					cases = 2;
				}
			}
			else
			{
				//case 3: charLength not implemented in browser
				cases = 2;
			}

			//setDebug("Case " + cases);
			if (cases != 1) // do it the hard way
			{
				var start = event.charIndex;
				_lastCharIndex = start;
				var word = text.substring(start);
				debug(event.name + ': ' + word + ', index:' + event.charIndex + ", charLength: " + event.charLength);
				var words = word.split(" ");
				if (words.length > 0)
				{
					word = words[0];
					var before = (start > 0) ? text.substring(0, start) : "";
					var after = text.substring(start + word.length);
					$("#slideDescription").html(before + '<span class="highlight-word">' + word + '</span>' + after);					
				}
			}
			
			//
			// make sure element is visible in the viewport
			//
			scrollTo('.highlight-word'); // has to be a class

			// case 4: onBoundary not implemented so highlighting isn't possible
		}
	}	
	
	window.speechSynthesis.speak(_utter);
	_speechTimerId = setTimeout(speechBugWorkaround, 10000);		
}

function speechBugWorkaround()
{		
	//debug("reset speech");
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
			
			if (true) // normal path
			{
				// look for voices which map the language we are looking for and save the first one
				if (deck.language.startsWith("es") && (_voices[i].lang.startsWith("es") || _voices[i].lang.startsWith("spa")))
				{
					if (found == 0)
					{								
						found++;
					}
					
					voiceSelect.appendChild(option);
				}
				else if (deck.language.startsWith("en") && _voices[i].lang.startsWith("en"))
				{
					if (found == 0)
					{								
						found++;
					}
					
					voiceSelect.appendChild(option);
				}
			}
			else
			{
				// load all languages for testing
				found++;
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

	//
	// set the active voice from the select dropdown
	//
	if (found)
	{
		setSelectedVoice(voiceSelect);
		changeVoice();
	}
	else
	{
		msg = "Language not found: " + deck.language + ", text can't be read correctly.";
		$("#language").text(msg);
		$("#languages").show();
	}
}

function saveSelectedVoice(voiceIndex)
{
	localStorage['readVoiceIndex'] = voiceIndex;
	//debug("set readVoiceIndex: " + voiceIndex);
}

function setSelectedVoice(voiceSelect)
{
	var voiceIndex = localStorage['readVoiceIndex'];
	if (!voiceIndex)
	{
		localStorage['readVoiceIndex'] = 0;
		voiceIndex = 0;
	}

	voiceSelect.selectedIndex = (voiceIndex < voiceSelect.options.length) ? voiceIndex : 0;
	//debug("get: readVoiceIndex: " + voiceIndex);
}

function changeVoice()
{
	var index = $("select")[0].selectedIndex;
	saveSelectedVoice(index);
	
	var voice = $("select").children("option:selected").val();
	deck.voice = _voices[voice];
	if (_utter != null)
	{
		_utter.voice = deck.voice;
		//setDebug(deck.voice.name);
		//window.speechSynthesis.pause();
		//window.speechSynthesis.resume();
	}

	//$("#language").text("Language: " + deck.voice.lang + ", voice: " + deck.voice.name);
}

function setDebug(text = null)
{
    $("#debug").text(text);
}

function showSeconds(text = null)
{
    $(".showSeconds").text(text);
}

function end()
{
	saveReadLocation(0);
	clearTimeout(_speechTimerId);
	reset();
	loadData();
	deck.start();
	$("#pause").show();
	$("#resume").hide();
}

function reset()
{
	clear();
	curr = 0;
}

function clear()
{
    // clear slides
	deck.slides.forEach(function(slide, index){
	    slide.done = false;
	});
}

function loadSlide()
{
	saveReadLocation(curr);
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

var _dictionary = "_blank";
function getSelectedText() 
{
	pause();
	
    var text = "";
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
	text = text.trim();
	if (text.length > 0)
	{
		//setDebug(text);
		// copy selected text
		var succeed;
		try
		{
			succeed = document.execCommand("copy");
		}
		catch(e)
		{
			succeed = false;
		}

		var html = "<div style='margin-bottom:10px;'><span style='font-size:1.2em;'>" + text + "</span>"
			+ "&nbsp;<a target='_blank' href='https://translate.google.com/#view=home&op=translate&sl=es&tl=en&text=" + text + "'>(Google)</a>"
			+ "&nbsp;<a target='_blank' href='https://www.spanishdict.com/translate/" + text + "'>(SpanDict)</a>";
			if (deck.isAdmin)
				html += "&nbsp;<a target='_blank' href='/definitions/add'>(add)</a>";
			html+= "</div>";
	
		$('#selected-word').html(html);
		$('#selected-word-definition').text('');
		ajaxexec('/definitions/get/' + text, '#selected-word-definition');	
	}
}

function xlate(word) 
{
	$('#selected-word-definition').text('translating...');
	ajaxexec('/definitions/translate/' + word, '#selected-word-definition');	
}
		
function zoom(amount)
{
	//var size = $("#slideDescription").css("font-size");
	_readFontSize += amount;
	
	if (_readFontSize > _maxFontSize) // don't go crazy
		_readFontSize = _maxFontSize;
		
	localStorage['readFontSize'] = _readFontSize;
	setFontSize();
}

function setFontSize()
{
	$("#slideDescription").css("font-size", _readFontSize + "px");
	$("#slideTitle").css("font-size", _readFontSize + "px");
	$("#readFontSize").text("Size: " + _readFontSize);
}

function saveReadLocation(location)
{
	var tag = 'readLocation' + deck.lessonId;
	
	localStorage[tag] = location;
	if (location == 0)
	{
		$('#button-continue-reading').hide();
		$('#button-start-reading').text("Start Reading");
	}

	//debug("saveReadLocation: " + location);
}

function setReadLocation()
{
	var tag = 'readLocation' + deck.lessonId;
	var location = localStorage[tag];
	location = parseInt(location);
	if (location > 0 && location < max)
	{
		$('#button-continue-reading').show();
		$('#button-continue-reading').text("Continue reading from line " + (location + 1));
		$('#button-start-reading').text("Start reading from the beginning");
		
		curr = location;
	}
	
	$('#readCurrLine').text("Line: " + (curr + 1));
	//debug("setReadLocation: " + location);
}