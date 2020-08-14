@extends('layouts.reader')

@section('content')

<!-------------------------------------------------------->
<!-- Add misc data needed by the JS during runtime -->
<!-------------------------------------------------------->
<div class="data-misc"
	data-count="{{count($record['lines'])}}"
	data-type="{{$record->type_flag}}"
	data-lessonid="{{$record->id}}"
	data-touchpath="{{(isset($touchPath) ? $touchPath : '')}}"
	data-max="{{count($record['lines'])}}"
	data-language="{{$record->getSpeechLanguage()}}"
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
			<span style="font-size:1.3em; margin-right:10px;" class=""><a class="" role="" href="/articles"><span class="glyphicon glyphicon-button-back-to"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); reload()" href=""><span id="button-repeat" class="glyphicon glyphicon-repeat"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); mute()" href=""><span id="button-mute" class="glyphicon glyphicon-volume-up"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); prev()" href=""><span id="button-prev" class="glyphicon glyphicon-backward"></span></a></span>
			<span id="pause" style="font-size:1.3em; margin-right:10px; xdisplay:none;" class=""><a onclick="event.preventDefault(); pause()" href=""><span id="button-pause" class="glyphicon glyphicon-pause"></span></a></span>
			<span id="resume" style="font-size:1.3em; margin-right:10px; display:none;" class=""><a onclick="event.preventDefault(); resume()" href=""><span id="button-resume" class="glyphicon glyphicon-play"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); next()" href=""><span id="button-next" class="glyphicon glyphicon-forward"></span></a></span>
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
            <a onclick="event.preventDefault(); run()"  href="" class="btn btn-primary mb-3" role="button">Start Reading</a>
        </div>

	</div><!-- panel-start -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Run Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-run" class="slide-panel text-center">
        <div class="small-thin-text slideCount"></div>
	    <div id="debug"></div>
	    <div id="slideDescription" class="slideDescription" style="font-size: 18px;" ondrag="getSelectedText();" ondblclick="getSelectedText();"></div>
	    <h5 class="slideSeconds mt-2"></h5>
        <div class="text-center"><h1 style="font-size:100px" class="showSeconds"></h1></div>
	</div><!-- panel-run -->

    <audio id="audio">
        <source src="" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

</div><!-- container -->

@endsection
