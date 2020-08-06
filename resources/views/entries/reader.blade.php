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
	data-language="{{$language}}"
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
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); pause()" href=""><span id="button-pause" class="glyphicon glyphicon-pause"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); skip()" href=""><span id="button-skip" class="glyphicon glyphicon-step-forward"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); mute()" href=""><span id="button-mute" class="glyphicon glyphicon-volume-up"></span></a></span>
		</div>

	</div>

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Start Panel - Index -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-start" class="slide-panel">

		<div class="text-center">
		<div><span style="" id="language"></span></div>
			<div id="languages" class="mt-2 mb-2" style="display:default; font-size:10px;"><select onchange="changeLanguage();" name="select" id="select"></select></div>
		</div>
		
	    <div class="text-center">
            <h3 class="mt-2 mb-2">{{$record->title}}</h3>
            <p style="font-size:13px;" class="">{{count($record['lines'])}} lines</p>
            <a onclick="event.preventDefault(); run()"  href="" class="btn btn-primary mb-3" role="button">Start Reading</a>
        </div>

	</div><!-- panel-start -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Run Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-run" class="slide-panel text-center">
	    <div class="slideCount"></div>
	    <h5 class="slideTitle"></h5>
	    <div class="slideDescription"></div>
	    <h5 class="slideSeconds mt-2"></h5>
        <div class="text-center"><h1 style="font-size:100px" class="showSeconds"></h1></div>
	</div><!-- panel-run -->

    <audio id="audio">
        <source src="" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

</div><!-- container -->

@endsection
