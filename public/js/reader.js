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

	var fontSize = localStorage['readFontSize'];
	if (!fontSize)
		localStorage['readFontSize'] = _readFontSize;
	else
	{
		_readFontSize = parseInt(fontSize, 10);
		if (_readFontSize > _maxFontSize)
			_readFontSize = _maxFontsize;
	}
	setFontSize();
	
	window.speechSynthesis.cancel();	
	setTimeout(loadVoices, 500);
	loadData();
	getReadLocation();
	deck.start();
	
	$("#pause").hide();
	$("#resume").show();
	ajaxexec('/entries/get-definitions-user/' + parseInt(deck.contentId, 10) + '', '#defs');

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

        //debug("setting state to " + state, _debug);

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
        $("#slideCount").text((curr+1) + " of " + deck.slides.length);
        $(".slideDescription").text(deck.slides[curr].description);
		$('#selected-word').text('');
		$('#selected-word-definition').text('');
		
		if ($('#tab1').is(':visible'))
			window.scroll(0, 0); // scroll to top

	}

	this.readSlideResume = function() {
	    var slide = deck.slides[curr];
		read(slide.description, _lastCharIndex);
	}
	
	this.readSlide = function() {
	    var slide = deck.slides[curr];
        //debug("read slide " + (curr+1) + ": " + slide.description, _debug);
		read(slide.description, 0);
		
        //$("#slideCount").text(slide.number + " of " + deck.slides.length);
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
		deck.touchPath = container.data('touchpath');
		deck.language = container.data('language');			// this is the language that the web site is in
		deck.isAdmin = container.data('isadmin') == '1';
		deck.userId = parseInt(container.data('userid'), 10);
		
		// use these to create a unique session id tag, looks like: 'readLocationEntry23'
		deck.contentType = container.data('contenttype');
		deck.contentId = container.data('contentid');
		deck.readLocationTag += deck.contentType + deck.contentId;
		
		// this is the read location from the db
		deck.readLocationOtherDevice = container.data('readlocation');
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

function incLine(e, count)
{	
	e.preventDefault(); 

	_incLine += count + 1;
	
	// put the line on multiples of 50
	mod = _incLine % 50;
	_incLine -= (mod + 1);
	
	if (_incLine < 0)
		_incLine = 0;
	else if (_incLine >= max)
		_incLine = 0;
	
	$('#button-start-reading').text("Start reading from the beginning");
	$('#readCurrLine').text("Line " + (_incLine + 1));
	$('#button-continue-reading').show();
	$('#button-continue-reading').text("Continue reading from line " + (_incLine + 1));

	curr = _incLine;	
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
	// not starting at the beginning
	
	if (_incLine != 0) // if line selector was used (+-50) then use that
		curr = _incLine;
	else 			  // use last location from the session
		curr = parseInt(localStorage[deck.readLocationTag], 10);
	
	$("#pause").show();
	$("#resume").hide();	
	startClock();
	deck.run(/* fromBeginning = */ false);
}

function runContinueOther() 
{
	curr = deck.readLocationOtherDevice;
	
	$("#pause").show();
	$("#resume").hide();	
	startClock();
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
		// resuming without being paused means play was clicked from start panel
		startClock();
		deck.run(_incLine == 0); // if line has been inc'ing then don't start at the beginning.
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

    //debug("mute set to " + _mute.toString(), _debug);
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
var _clockTimerId = null;
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
	_voices = window.speechSynthesis.getVoices();	

	if (_voices.length == 0 && _voicesLoadAttempts++ < 10)
	{
		console.log("loading voices...not ready");
		setTimeout(loadVoices, 500);
		return;
	}
	
	//tts('ready with ' + _voices.length + ' voices');	
	
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
			
			var showOnlyDeckLanguage = true; // change to false for testing languages
			if (showOnlyDeckLanguage) // normal path
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

function changeVoice()
{
	var index = $("select")[0].selectedIndex;
	saveSelectedVoice(index);
	
	var voice = $("select").children("option:selected").val();
	deck.voice = _voices[voice];
	if (_utter != null)
	{
		_utter.voice = deck.voice;
		//debug(deck.voice.name, _debug);
		//window.speechSynthesis.pause();
		//window.speechSynthesis.resume();
	}

	//$("#language").text("Language: " + deck.voice.lang + ", voice: " + deck.voice.name);
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

var _dictionary = "_blank";
var _selectedWordThrottle = ""; // used to slow down ajax definition calls for selected words
function getSelectedText(clicks) 
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
		if (text == _selectedWordThrottle)
		{
			// throttle the lookups because they come on mouseup for selections and dblclick for word selection
			setTimeout(function(){	_selectedWordThrottle = ""; /*console.log('cleared throttle');*/ }, 500);

			return;
		}
		//console.log('sent ajax for: ' + text);
		_selectedWordThrottle = text;
		
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
			+ "&nbsp;<a target='_blank' href='https://www.spanishdict.com/translate/" + text + "'>(SpanDict)</a>"
			+ "&nbsp;<a target='_blank' href='https://www.wordreference.com/definicion/" + text + "'>(WR)</a>";
			if (false && deck.isAdmin)
				html += "&nbsp;<a target='_blank' href='/definitions/add/" + text + "'>(add)</a>";
			html+= "</div>";
	
		//_hotWords.push(text + ": ");
		$('#selected-word').html(html);
		$('#selected-word-definition').text('');
		ajaxexec('/definitions/get/' + text + '/' + deck.contentId, '#selected-word-definition', false, translateCallback);
	}
}

function removeDefinitionUser(url)
{
	ajaxexec(url, '', false, translateCallback);
}

function translateCallback(definition) 
{
	ajaxexec('/entries/get-definitions-user/' + parseInt(deck.contentId, 10) + '', '#defs');
}

function xlate(word) 
{
	$('#selected-word-definition').text('translating...');
	ajaxexec('/definitions/translate/' + word + '/' + deck.contentId + '', '#selected-word-definition', false, translateCallback);	
}
		
function zoom(event, amount)
{
	event.preventDefault(); 	
	
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
	
	$("#readFontSize").css("font-size", _readFontSize + "px");
	$(".glyph-zoom-button").css("font-size", _readFontSize + "px");
	$("#readFontSize").text("Text Size: " + _readFontSize);
}

function saveReadLocation(location)
{
	localStorage[deck.readLocationTag] = location;
	if (location == 0)
	{
		$('#button-continue-reading').hide();
		$('#button-start-reading').text("Start Reading");
	}

	if (deck.userId > 0) // if logged in, save read location in db
		ajaxexec('/entries/set-read-location/' + parseInt(deck.contentId, 10) + '/' + location + '/');
	deck.readLocationOtherDevice = location;
	//debug("saveReadLocation: " + location, _debug);
}

function getReadLocation()
{
	var location = parseInt(localStorage[deck.readLocationTag], 10);
	var multipleLocations = (location != deck.readLocationOtherDevice);
	
	if (location > 0 && location < max)
	{
		$('#button-start-reading').text("Start reading from the beginning");
		$('#button-continue-reading').show();
		$('#button-continue-reading').text("Continue reading from line " + (location + 1));
	}
	
	if (multipleLocations && deck.readLocationOtherDevice > 0 && deck.readLocationOtherDevice < max)
	{
		$('#button-start-reading').text("Start reading from the beginning");
		$('#button-continue-reading').html("Continue reading from line " + (location + 1) + ""); // "<br/><span class='small-thin-text'>(location on this device)</span>");
		
		$('#button-continue-reading-other').show();
		$('#button-continue-reading-other').html("Continue reading from line " + (deck.readLocationOtherDevice + 1) + "<br/><span class='small-thin-text'>(location from a different session)</span>");
	}
	
	$('#readCurrLine').text("Line " + (curr + 1));
	//debug("getReadLocation: " + location, _debug);
}

//<div id="panel-run-col-defs" class="col-md-4 mt-3" style="background-color:white; padding:0;">
//<div id="panel-run-col-text" class="col-md-8" style="" >
function toggleShowDefinitions()
{
	if ($('#panel-run').is(':visible'))
	{
		if ($('#panel-run-col-defs').is(':visible'))
		{
			$('#panel-run-col-defs').hide();
			$('#panel-run-col-defs').removeClass('col-md-4');
			$('#panel-run-col-text').removeClass('col-md-8');		
		}
		else
		{
			$('#panel-run-col-defs').show();
			$('#panel-run-col-defs').addClass('col-md-4');
			$('#panel-run-col-text').addClass('col-md-8');
		}
	}
}

function startClock()
{
	_startTime = new Date();	
	clearTimeout(_clockTimerId);
	_clockTimerId = setTimeout(showElapsedTime, 1000);
}

function showElapsedTime()
{
	var time = getElapsedTime();
	$('#elapsedTime').text("Reading Time: " + time);
	$('#clock').text(time);

	clearTimeout(_clockTimerId);
	_clockTimerId = setTimeout(showElapsedTime, 1000);
}

function getElapsedTime()
{
	var time = '';
	
	// get run time
	if (_startTime != null)
	{
		endTime = new Date();
		var timeDiff = endTime - _startTime; //in ms
		timeDiff /= 1000; // to seconds
		var seconds = Math.round(timeDiff);		
		var total = seconds;

		if (seconds < 10)
			time = '00:0' + seconds;
		else
			time = '00:' + seconds
		
		if (seconds >= 60)
		{
			minutes = Math.round(seconds / 60);
			seconds = seconds % 60;
			
			if (minutes >= 60)
			{
				hours = Math.round(minutes / 60);
				minutes = minutes % 60;
				
				if (minutes < 10)
					minutes = "0" + minutes;
				if (seconds < 10)
					seconds = "0" + seconds;
				if (hours < 10)
					hours = "0" + hours;
				
				time = hours + ":" + minutes + ":" + seconds;
			}
			else
			{
				if (minutes < 10)
					minutes = "0" + minutes;
				if (seconds < 10)
					seconds = "0" + seconds;
				
				time = minutes + ":" + seconds;				
			}
		}		
	}

	return time;
}
