@extends('layouts.reader')

@section('content')

<!-------------------------------------------------------->
<!-- Add misc data needed by the JS during runtime -->
<!-------------------------------------------------------->
<div class="data-misc"
	data-count="{{count($record['lines'])}}"
	data-touchpath="{{(isset($touchPath) ? $touchPath : '')}}"
	data-max="{{count($record['lines'])}}"
	data-language="{{$record->getSpeechLanguage()}}"
	data-isadmin="{{$isAdmin ? 1 : 0}}"
	data-type="{{$record->type_flag}}"
	data-contenttype="Entry"
	data-contentid="{{$record->id}}"
></div>

	<!-------------------------------------------------------->
	<!-- Add the body lines to read -->
	<!-------------------------------------------------------->
@foreach($record['lines'] as $r)
	<div class="data-slides"
	    data-title="{{$r}}"
	    data-number="1"
	    data-description="{{$r}}"
	    data-id="{{$record->id}}"
	    data-seconds="10"
	    data-between="2"
	    data-countdown="{{App\Tools::isLocalhost() ? '3' : '1'}}"
	>
	</div>
@endforeach

<div class="container">
	
	<!-------------------------------------------------------->
	<!-- Header -->
	<!-------------------------------------------------------->
	<div style="margin-top: 5px;">

		<!-------------------------------------------------------->
		<!-- Top Row Buttons -->
		<!-------------------------------------------------------->
		<div style="margin: 0 5px 0 0;">
			<span style="font-size:1.3em; margin-right:10px;" class=""><a class="" role="" href="/{{$index}}"><span class="glyphicon glyphicon-button-back-to"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); reload()" href=""><span id="button-repeat" class="glyphicon glyphicon-repeat"></span></a></span>
			<!-- span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); mute()" href=""><span id="button-mute" class="glyphicon glyphicon-volume-up"></span></a></span -->
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); prev()" href=""><span id="button-prev" class="glyphicon glyphicon-backward"></span></a></span>
			<span id="pause" style="font-size:1.3em; margin-right:10px; xdisplay:none;" class=""><a onclick="event.preventDefault(); pause()" href=""><span id="button-pause" class="glyphicon glyphicon-pause"></span></a></span>
			<span id="resume" style="font-size:1.3em; margin-right:10px; display:none;" class=""><a onclick="event.preventDefault(); resume()" href=""><span id="button-resume" class="glyphicon glyphicon-play"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); next()" href=""><span id="button-next" class="glyphicon glyphicon-forward"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); toggleShowDefinitions();" href=""><span id="button-show" class="glyphicon glyphicon-th-list"></span></a></span>
		</div>
		<div class="text-center">
			<div><span class="small-thin-text" id="language"></span></div>
			<div><span class="small-thin-text" id="title">{{$record->title}}</span></div>
			<div id="languages" class="mt-2 mb-2" style="display:default; font-size:10px;"><select onchange="changeVoice();" name="select" id="select"></select></div>
			<div style="line-height: 18px;">
			<a onclick="event.preventDefault(); zoom(3)" href=""><span id="button-increase-text-size" class="glyphicon glyphicon-zoom-in"></span></a>
				<span id="readFontSize" class="small-thin-text" style="valign:middle;">Font Size: 18</span>
			<a onclick="event.preventDefault(); zoom(-3)" href=""><span id="button-increase-text-size" class="glyphicon glyphicon-zoom-out"></span></a>
			</div>
		</div>

	</div>

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Start Panel - Index -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-start" class="slide-panel">
		
	    <div class="text-center">
            <div class="small-thin-text">{{count($record['lines'])}} lines</div>
            <div id="slideTitle" style="font-size:18px" class="mb-2">{{$record->title}}</div>
            <a onclick="event.preventDefault(); run()"  href="" class="btn btn-primary mb-3"  id="button-start-reading" role="button">Start Reading</a>
            <div>
				<a onclick="event.preventDefault(); runContinue()"  href="" class="btn btn-success mb-3" id="button-continue-reading" style="display:none;" role="button">Continue reading from line</a>
			</div>
			<div style="line-height: 24px; vertical-align:middle;">
				<a onclick="event.preventDefault(); incLine(-50)" href=""><span id="button-decrement-line" class="glyphicon glyphicon-minus-sign"></span></a>
				<span id="readCurrLine" class="" style="margin:10px;">Line: </span>
				<a onclick="event.preventDefault(); incLine(50)" href=""><span id="button-increment-line" class="glyphicon glyphicon-plus-sign"></span></a>
				<div id="elapsedTime" class="mt-5"></div>
				
			</div>
        </div>

	</div><!-- panel-start -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Run Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	
	<div class="container-fluid">
	  <div class="row">
		<div id="panel-run-col-text" class="" style="width:100%;" >
			<div id="panel-run" class="slide-panel text-center" style="">
				<div class="small-thin-text slideCount"></div>
				<div id="debug"></div>
				<div id="slideDescription" class="slideDescription" style="font-size: 18px;" onmouseup="getSelectedText();" ondblclick="getSelectedText();" ontouchend="getSelectedText();"></div>
				<div class="" style="color: green;" id="selected-word"></div>
				<div class="" style="color: green;" id="selected-word-definition"></div>
			</div><!-- panel-run -->
		</div>
		<div id="panel-run-col-defs" class="mt-3" style="display:none; padding:0;">
			<div id="defs" style=""></div>
		</div>
	  </div>
	</div>	
	

</div><!-- container -->

@endsection
