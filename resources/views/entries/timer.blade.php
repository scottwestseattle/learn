<div style="max-width:400px;">

<style>

a {
	text-decoration:none;
	color: black;
}

</style>

<div style="float:left; font-size:.7em; margin-bottom:10px; margin-left:10px;">
<a href='/'><span class="glyphCustom glyphicon glyphicon-time"></span>Home</a>
</div>
<div style="clear:both;"></div>

<div style="" id="clockdiv">

<div style="height: 10px; display: block; color: black; font-size:10px;">
<span style="color: white;">Break:</span>
<select id="hour1" onchange="start()">
  <option value="6">6</option>
  <option value="7">7</option>
  <option value="8">8</option>
  <option value="9">9</option>
  <option value="10">10</option>
  <option value="11">11</option>
  <option value="12">12</option>
  <option value="13">13</option>
  <option value="14">14</option>
  <option value="15">15</option>
  <option value="16">16</option>
  <option value="17" selected>17</option>
  <option value="18">18</option>
  <option value="19">19</option>
  <option value="20">20</option>
  <option value="21">21</option>
  <option value="22">22</option>
  <option value="23">23</option>
  <option value="24">24</option>
</select>
<select id="min1" onchange="start()">
</select>
<select id="length1" onchange="start()">
</select>
</div>

<div style="height: 10px; display: block; color: black; font-size:10px;">
<span style="color: white;">Lunch:</span>
<select id="hour2" onchange="start()">
  <option value="6">6</option>
  <option value="7">7</option>
  <option value="8">8</option>
  <option value="9">9</option>
  <option value="10">10</option>
  <option value="11">11</option>
  <option value="12">12</option>
  <option value="13">13</option>
  <option value="14">14</option>
  <option value="15">15</option>
  <option value="16">16</option>
  <option value="17">17</option>
  <option value="18">18</option>
  <option value="19" selected>19</option>
  <option value="20">20</option>
  <option value="21">21</option>
  <option value="22">22</option>
  <option value="23">23</option>
  <option value="24">24</option>
</select>
<select id="min2" onchange="start()">
</select>
<select id="length2" onchange="start()">
</select>
</div>

<div style="height: 10px; display: block; color: black; font-size:10px;">
<span style="color: white;">Break:</span>
<select id="hour3" onchange="start()">
  <option value="6">6</option>
  <option value="7">7</option>
  <option value="8">8</option>
  <option value="9">9</option>
  <option value="10">10</option>
  <option value="11">11</option>
  <option value="12">12</option>
  <option value="13">13</option>
  <option value="14">14</option>
  <option value="15">15</option>
  <option value="16">16</option>
  <option value="17">17</option>
  <option value="18">18</option>
  <option value="19">19</option>
  <option value="20">20</option>
  <option value="21" selected>21</option>
  <option value="22">22</option>
  <option value="23">23</option>
  <option value="24">24</option>
</select>
<select id="min3" onchange="start()">
</select>
<select id="length3" onchange="start()">
</select>
</div>

<h1 class="next" style="padding: 0; margin-top: 20px; font-size: .5em;"></h1>

  <div>
    <span class="hours"></span>
  </div>
  <div class="colon">:</div>
  <div>
    <span class="minutes"></span>
  </div>
  <div class="colon">:</div>
  <div>
    <span class="seconds"></span>
  </div>
  
</div>
<span id="dbg"></span>

<div id="clockdiv2"><!-- Show Amsterdam Time -->
	<h1 class="" style="color: black; padding: 0; margin-top: 20px; font-size: 1em;">Ams Time:&nbsp;<span style="font-weight: bold;"><span class="hours2"></span>:<span class="minutes2"></span></span></h1>
</div>

<!-- notes en English -->
<div style="color:#ff9966; padding:20px; margin:20px 0;">
<div>
  <p>You are going to receive a brief survey about how I handled your call.  I would appreciate your feedback.  It only reflects on me and not B.</p>
</div>
<div>
  <p>I see that you are a preferred partner, we appreciate that.</p>
</div>
<div>
  <p>I apologize if you were on hold for a long time.</p>
</div>
</div>

<!-- notes en espanol -->
<div style="color:#009999; text-align:left; padding:20px; margin:20px 0;">
<div>
  <p>Le va a llegar una breve encuesta de como manejé su llamada.  Solo se refleja en mi.  Si la llena, se lo agradezco mucho.</p>
</div>
<div>
  <p>Gracias por ser un alojamiento preferente.</p>
</div>
<div>
  <p>Lo siento mucho por la espera.</p>
</div>
</div>

<script>

var times = null; //[1, 0.5, 0.1, 0.5, 0.1, 0.5];

var ix = 0;
var deadline = null;
var timeinterval = null;
var timercount = 0;

//loadMenu("min1", 0, 59, 1);
//loadMenu("min2", 0, 59, 1);
//loadMenu("min3", 0, 59, 1);
loadMenu("min1", 0, 3, 15);
loadMenu("min2", 0, 3, 15);
loadMenu("min3", 0, 3, 15);
loadMenu("length1", 1, 4, 15);
loadMenu("length2", 1, 4, 15, 1);
loadMenu("length3", 1, 4, 15);
setTime2();

function getTimeRemaining(endtime) 
{
  var t = endtime - new Date();
  
  var seconds = Math.floor((t / 1000) % 60);
  var minutes = Math.floor((t / 1000 / 60) % 60);
  var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
  var days = Math.floor(t / (1000 * 60 * 60 * 24));
  return {
    'total': t,
    'days': days,
    'hours': hours,
    'minutes': minutes,
    'seconds': seconds
  };
}

function clearDebug(info)
{
	var d = document.getElementById('dbg');
	d.innerHTML = "";
}

function showDebug(info)
{
	var d = document.getElementById('dbg');
	d.innerHTML = d.innerHTML + '<br/>' + info;
}

function initializeClock(id, endtime) 
{
	var clock = document.getElementById(id);
	var hoursSpan = clock.querySelector('.hours');
	var minutesSpan = clock.querySelector('.minutes');
	var secondsSpan = clock.querySelector('.seconds');
	var nextSpan = clock.querySelector('.next');
	
	var mins = endtime.getMinutes();
	if (mins < 10)
		mins = '0' + mins;
	//nextSpan.innerHTML = 'Next Alarm: ' + endtime.getHours() + ':' + mins;
	nextSpan.innerHTML = 'Next Alarm: ' + endtime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
	
	function updateClock() 
	{
		var t = getTimeRemaining(endtime);
		//showDebug('here: ' + endtime);
		//showDebug('t.total: ' + t.total);

		hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
		minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
		secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
		
		//if (t.total <= 0 && timeinterval != null) 
		if (t.total <= 0) // timer has ended
		{
			showAlarm();
			ix++;
		  
			// if anymore times are defined
			if (ix < times.length)
			{
				initializeClock('clockdiv', times[ix]);	
			}
			else
			{
				clearClock(id);
			}
		}
				
		setTime2();
	}

	updateClock();
	timercount++;
	clearInterval(timeinterval);
	timeinterval = setInterval(updateClock, 1000);
	showAlarm();
}

function setTime2()
{
	// Amsterdam Time
	var t2 = new Date();
	t2.setHours(t2.getHours() + 9);	
	
	//alert(t2);
		
	var clock = document.getElementById("clockdiv2");
		
	var hoursSpan2 = clock.querySelector('.hours2');
	var minutesSpan2 = clock.querySelector('.minutes2');

	var h = t2.getHours();
	if (h < 10)
		h = '0' + h;
		
	var m = t2.getMinutes();
	if (m < 10)
		m = '0' + m;
	
	hoursSpan2.innerHTML = h;
	minutesSpan2.innerHTML = m;
}

function clearClock(id)
{
	var clock = document.getElementById(id);
	var hoursSpan = clock.querySelector('.hours');
	var minutesSpan = clock.querySelector('.minutes');
	var secondsSpan = clock.querySelector('.seconds');

	hoursSpan.innerHTML = "";
	minutesSpan.innerHTML = "";
	secondsSpan.innerHTML = "";
}

var color = 0;
function showAlarm()
{
	//changeColor((color % 2) == 0);
	if (ix % 2)
	{
		document.body.style.backgroundColor = "red";
		document.getElementById("clockdiv").style.backgroundColor = "red";
	}
	else
	{
		document.body.style.backgroundColor = "white";
		document.getElementById("clockdiv").style.backgroundColor = "blue";
	}	
}

function changeColor(odd = false)
{
	if (!odd)
	{
		document.body.style.backgroundColor = "red";
		document.getElementById("clockdiv").style.backgroundColor = "red";
	}
	else
	{
		document.body.style.backgroundColor = "white";
		document.getElementById("clockdiv").style.backgroundColor = "blue";
	}
}

function loadTimes()
{  
	//clearDebug();
	
	var d = [];
	var index = 0;
	for (var i = 0; i < 20; i++)
	{
		var h = getValue("hour", i + 1);
		var m = getValue("min", i + 1);
		var l = getValue("length", i + 1);
		
		if (h == null || m == null || l == null)
			break;

		h = parseInt(h);
		m = parseInt(m);
		l = parseInt(l);

		var on = new Date();
		on.setHours(h);
		on.setMinutes(m);
		on.setSeconds(0);

		var off = new Date();
		off.setHours(h);
		off.setMinutes(m + l);
		off.setSeconds(0);			

		var t = getTimeRemaining(on);
		if (t.total <= 0)
			continue; // time is in the past, skip it
		
		// time is future
		d[index++] = on;
		d[index++] = off;
	}
			
	return d;
}

function getValue(tag, id)
{
	var v = tag + id.toString()
	v = document.getElementById(v);	
	if (v != null)
		v = v.value;
	
	return v;
}

function setDeadline(minutes)
{
	deadline = new Date(Date.parse(new Date()) + minutes * 60 * 1000);
	setDebug(deadline);
	return;
}

function start()
{
	ix = 0;
	color = 0;
	timercount--;
	clearInterval(timeinterval);
	timeinterval = null;
	clearDebug();
	times = loadTimes();

	if (false)
	{
		showDebug('dump times');
		for (var i = 0; i < times.length; i++)
			showDebug(times[i]);
		showDebug('end dump');
	}

	// spool up to the first future time
	for (var i = 0; i < times.length; i++)
	{
		var t = getTimeRemaining(times[ix]);
		if (t.total > 0)
			break;
			
		ix++;
	}
		
	if (ix < times.length)
	{
		if (false)
			showDebug('Start time: ' + times[ix]);
			
		initializeClock('clockdiv', times[ix]);	
	}
	else
	{
		//showDebug('NO TIMES REMAINING');
		clearClock('clockdiv');
	}
}

function loadMenu(id, start, end, factor, selectedIx = 0)
{
	var select = document.getElementById(id); 
	
	for(var i = start; i <= end; i++) 
	{
		var opt = (i * factor).toString();
		var o = parseInt(opt);
		if (o < 10)
			opt = "0" + opt;
			
		var el = document.createElement("option");
		el.textContent = opt;
		el.value = opt;
		el.selected = 2;
		select.appendChild(el);
	}

	select.selectedIndex = selectedIx;
}

function dump()
{		
}

</script>

<style>

body{
	text-align: center;
	background: white;
	font-family: sans-serif;
	font-weight: 100;
}

h1{
	color: white;
	font-weight: 100;
	font-size: 30px;
	margin: 40px 0px 20px;
}

#clockdiv{
  background-color: blue;
	font-family: sans-serif;
	color: #fff;
	display: inline-block;
	font-weight: 100;
	text-align: center;
	font-size: 30px;
  padding: 5px 20px 20px 50px;
  border-radius: 10px;
  min-width:250px;
}

#clockdiv > div{
	padding: 10px 5px 5px 5px;
	display: inline-block;
}

#clockdiv div > span{
	padding: 5px;
	display: inline-block;
}

.smalltext{
	padding-top: 5px;
	font-size: 16px;
}

.colon {
  vertical-align:top;
  padding-top:5px;
  xcolor:green;
}

p {
    text-align:left; 
    font-family: trebuchet; 
    font-family: georgia; 
    font-family: verdana; 
    font-size:15px;
}

</style>

</div>