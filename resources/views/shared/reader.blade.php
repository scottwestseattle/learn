@extends('layouts.reader')

@section('content')

<!-------------------------------------------------------->
<!-- Add misc data needed by the JS during runtime -->
<!-------------------------------------------------------->
<div class="data-misc"
	data-count="{{count($record['lines'])}}"
	data-touchpath="{{(isset($touchPath) ? $touchPath : '')}}"
	data-max="{{count($record['lines'])}}"
	data-language="{{$speechLanguage}}"
	data-type="{{$record->type_flag}}"
	data-contenttype="{{$contentType}}"
	data-contentid="{{$record->id}}"
	data-isadmin="{{$isAdmin ? 1 : 0}}"
	data-userid="{{Auth::id()}}"
	data-readlocation={{$readLocation}}
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
	<div class="text-center m-2" style="">

		<!-------------------------------------------------------->
		<!-- Top Row Buttons -->
		<!-------------------------------------------------------->
		<div>
			<span class="glyphReader"><a href="{{$referrerUrl}}"><span class="glyphicon glyphicon-circle-arrow-up"></span></a></span>
			<span class="glyphReader"><a onclick="event.preventDefault(); reload()" href=""><span id="button-repeat" class="glyphicon glyphicon-repeat"></span></a></span>
			<span class="glyphReader"><a onclick="zoom(event, -3);" href=""><span class="glyphicon glyphicon-zoom-out"></span></a></span>
			<span class="glyphReader"><a onclick="zoom(event, 3);" href=""><span class="glyphicon glyphicon-zoom-in"></span></a></span>
			<span class="glyphReader"><a onclick="toggleActiveTab(event, '#tab3', '#tab1', '.tab-body');" href=""><span class="glyphicon glyphicon-cog"></span></a></span>
			@if (Auth::check())
				<span class="glyphReader"><a onclick="toggleActiveTab(event, '#tab2', '#tab1', '.tab-body');" href=""><span class="glyphicon glyphicon-th-list"></span></a></span>
			@endif

			@if (false)
			<span class="glyphReader"><a onclick="setActiveTab(event, '#tab1', '.tab-body');" href=""><span class="glyphicon glyphicon-volume-up"></span></a></span>
			@endif
		</div>
	</div>

	<!--------------------------------------------------------------->
	<!-- tab 1 ------------------------------------------------------>
	<!--------------------------------------------------------------->
	<div id="tab1" class="tab-body">
		<!---------------------------------------------------------------------------------------------------------------->
		<!-- Start Panel - Index -->
		<!---------------------------------------------------------------------------------------------------------------->
		<div id="panel-start" class="slide-panel">

			<div class="text-center">
				<div class="small-thin-text">{{count($record['lines'])}} lines</div>
				<div id="slideTitle" style="font-size:18px" class="mb-2">{{$record->title}}</div>
				<a onclick="event.preventDefault(); run()"  href="" class="btn btn-primary mb-3"  id="button-start-reading" role="button">Start Reading</a>
				<div><a onclick="event.preventDefault(); runContinue()"  href="" class="btn btn-success mb-3" id="button-continue-reading" style="display:none;" role="button">Continue reading from line</a></div>
				<div><a onclick="event.preventDefault(); runContinueOther()"  href="" class="btn btn-warning mb-3" id="button-continue-reading-other" style="display:none;" role="button">Continue reading from location on other device</a></div>
				<div>
					<a onclick="incLine(event, -50)" href=""><span id="button-decrement-line" class="glyphicon glyphCustom glyphicon-minus-sign"></span></a>
					<div id="readCurrLine" class="middle large-text mb-2" style="min-width:85px;">Line </div>
					<a onclick="incLine(event, 50)" href=""><span id="button-increment-line" class="glyphicon glyphCustom glyphicon-plus-sign"></span></a>
					<div id="elapsedTime" class="mt-5"></div>
				</div>
			</div>

		</div><!-- panel-start -->

		<!---------------------------------------------------------------------------------------------------------------->
		<!-- Run Panel -->
		<!---------------------------------------------------------------------------------------------------------------->

		<div id="run-panel" class="container-fluid" style="xpadding-bottom:60px;"><!-- padding is to make the elements scroll correctly -->
		  <div class="row">
			<div id="panel-run-col-text" class="" style="width:100%;" >
				<div id="panel-run" class="slide-panel text-center" style="">
					<div><span class="medium-thin-text" id="title">{{$record->title}}</span></div>
					<div>
						<span class="small-thin-text" id="slideCount"></span><span id="clock" class="small-thin-text ml-3">00:00</span>
					</div>

					<div id="debug"></div>
					<div id="slideDescription" class="slideDescription" style="font-size: 18px;" onmouseup="getSelectedText(1);" ondblclick="getSelectedText(2);" ontouchend="getSelectedText();"></div>
					<div class="" style="color: green;" id="selected-word"></div>
					<div class="" style="color: green;" id="selected-word-definition"></div>
				</div><!-- panel-run -->
			</div>
		  </div>
		</div>

	</div>

	<!--------------------------------------------------------------->
	<!-- tab 2 ------------------------------------------------------->
	<!--------------------------------------------------------------->
	<div id="tab2" class="tab-body" style="display:none;">
		<div id="panel-run-col-defs" class="mt-3">
			<div id="defs" style=""></div>
		</div>
	</div>

	<!--------------------------------------------------------------->
	<!-- tab 3 ------------------------------------------------------->
	<!--------------------------------------------------------------->
	<div id="tab3" class="tab-body" style="display:none;">
		<div class="text-center mt-3">
			<div class="mb-5">
			<div><span class="small-thin-text" id="language"></span></div>
			<div id="languages" class="mt-1" style="display:default; font-size:10px;"><select onchange="changeVoice();" name="select" id="select"></select></div>
			</div>

			<div class="">
				<div class="middle mr-1"><a onclick="zoom(event, -3)" href=""><span class="glyphicon glyphReader glyphicon-zoom-out glyph-zoom-button"></span></a></div>
				<div class="middle" id="readFontSize">Font Size: 18</div>
				<div class="middle ml-3"><a onclick="zoom(event, 3)" href=""><span class="glyphicon glyphReader glyphicon-zoom-in glyph-zoom-button"></span></a></div>
			</div>
		</div>
	</div>

    <section class="main-controls">
        <canvas class="visualizer" height="60px"></canvas>
        <div id="buttons">
            <button class="record">Record</button>
            <button class="read" onclick="readPage()">Read</button>
            <button class="">Settings</button>
        </div>
    </section>

    <section class="sound-clips">
    </section>

	<!--------------------------------------------------------------->
	<!-- Bottom panel ----------------------------------------------->
	<!--------------------------------------------------------------->
	<div id="bottom-panel" class="btn-panel-bottom m-0 pb-1">
		<div class="glyphReaderMove mr-4"><a onclick="event.preventDefault(); prev()" href=""><span class="glyphicon glyphicon-backward"></span></a></div>
		<div id="pause"  class="glyphReaderPlay mr-4 mb-1"><a onclick="event.preventDefault(); pause()" href=""><span class="glyphicon glyphicon-pause"></span></a></div>
		<div id="resume" class="glyphReaderPlay mr-4"><a onclick="event.preventDefault(); resume()" href=""><span class="glyphicon glyphcircle glyphicon-play"></span></a></div>
		<div class="glyphReaderMove"><a onclick="event.preventDefault(); next()" href=""><span class="glyphicon glyphicon-forward"></span></a></div>
	</div>


</div><!-- container -->

@endsection

<script src="/js/recorder.js"></script>

