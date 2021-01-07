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

var _debug = false;
var _mute = false;
var _paused = false;
var _lastCharIndex = 0;
var _voices = null;
var _voicesLoadAttempts = 0;
var _cancelled = false;
var _readFontSize = 18;
var _maxFontSize = 99;
var _hotWords = [];
var _bottomPanelHeight; // height of bottom button panel
var _incLine = 0; // helper to get to a starting line

// track read time
var _startTime = null;

$(document).ready(function() {

	window.speechSynthesis.cancel();
	setTimeout(loadVoices, 500);
	loadData();
	getReadLocation();
	deck.start();

	_bottomPanelHeight = $("#bottom-panel").outerHeight(); // needed for scrolling
	//console.log("bottom panel height: " + _bottomPanelHeight);
});

$(window).on('unload', function() {
	window.speechSynthesis.cancel();
});

$(document).keyup(function(event) {
    if(event.keyCode == 32)		// spacebar
	{
		togglePause();
    }
});

//
// slide class
//
function deck() {

	this.slides = [];   // slides
	this.speech = null;
	this.language = "";
	this.languageLong = "";
	this.isAdmin = false;
	this.userId = 0;

	// options
	this.runState = RUNSTATE_START;

	this.contentType 	 = 'contentTypeNotSet';	// type of the content being read
	this.contentId 		 = 'contentIdNotSet';	// id of the content being read
	this.readLocationTag = 'readLocation';		// readLocation session id tag
	this.readLocationOtherDevice = 0;			// read location from another device for logged in user

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

		//debug("read next: " + curr, _debug);

        if (curr < max)
        {
			//debug("run slide: " + deck.slides[curr].title, _debug);
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
    });
}

function run()
{
	resume();
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
		// resuming without being paused means play was clicked from start panel
		startClock();
		deck.run(_incLine == 0); // if line has been inc'ing then don't start at the beginning.
	}

	$("#pause").show();
	$("#resume").hide();
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
			//debug(event.charLength + ' / ' + event.wordLength, _debug);
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

			//debug("Case " + cases, _debug);
			if (cases != 1) // do it the hard way
			{
				var start = event.charIndex;
				_lastCharIndex = start;
				var word = text.substring(start);
				//debug(event.name + ': ' + word + ', index:' + event.charIndex + ", charLength: " + event.charLength, _debug);
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
			if ($('#tab1').is(':visible')) // only scroll when on the read tab, otherwise it scrolls the other tabs
				scrollTo('.highlight-word', _bottomPanelHeight); // has to be a class

			// case 4: onBoundary not implemented so highlighting isn't possible
		}
	}

	window.speechSynthesis.speak(_utter);
	_speechTimerId = setTimeout(speechBugWorkaround, 10000);
}

function speechBugWorkaround()
{
	//debug("reset speech", _debug);
	window.speechSynthesis.resume(); // fix to keep speech from stopping

	if (window.speechSynthesis.speaking)
	{
		clearTimeout(_speechTimerId);
		_speechTimerId = setTimeout(speechBugWorkaround, 10000);
	}
}

function loadVoices()
{
	_voices = window.speechSynthesis.getVoices();

	if (_voices.length == 0 && _voicesLoadAttempts++ < 10)
	{
		console.log("loading voices...not ready");
		setTimeout(loadVoices, 500);
		return;
	}

	//tts('ready with ' + _voices.length + ' voices');

	var voiceSelect = document.querySelector('#selectVoice');
	var found = 0;

	if (_voices.length > 0)
	{
	    var langCodeSize = 2;
	    var deckLang = deck.language.substring(0, langCodeSize);

	    // figure out how the voices are formatted, either 'en-US' or 'eng-USA', 'es-ES' or 'spa-ESP'
	    if (_voices.length > 0 && _voices[0].lang.length > 5)
	    {
	        // using 3 letter language and country codes: 'spa-ESP'
	        langCodeSize = 3;
    	    deckLang = deck.languageLong.substring(0, langCodeSize);
	    }

        // quick check to see if there are any matches
        var showAll = true;
		for(i = 0; i < _voices.length ; i++)
		{
            var lang = _voices[i].lang.substring(0, langCodeSize);
            if (deckLang == lang)
            {
                // if at least one found, bail out
                showAll = false;
                break;
            }
		}

		for(i = 0; i < _voices.length ; i++)
		{
			var option = document.createElement('option');
			option.textContent = _voices[i].name + ' (' + _voices[i].lang + ')';
			option.value = i;

			if(_voices[i].default) {
			  option.textContent += ' (default)';
			}

			option.setAttribute('data-lang', _voices[i].lang);
			option.setAttribute('data-name', _voices[i].name);

            var lang = _voices[i].lang.substring(0, langCodeSize);
            //console.log('looking for: ' + deckLang + ', voice: ' + lang);

            if (showAll || deckLang == lang)
            {
                if (found == 0)
                {
                    found++;
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
	//debug("set readVoiceIndex: " + voiceIndex, _debug);
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
	//debug("get: readVoiceIndex: " + voiceIndex, _debug);
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
	$('#readCurrLine').text("Line " + (curr + 1));
	showElapsedTime();
	clearTimeout(_clockTimerId);
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


