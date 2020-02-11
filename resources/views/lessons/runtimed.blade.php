@extends('layouts.timedSlides')

@section('content')

<!-------------------------------------------------------->
<!-- Add misc data needed by the JS during runtime -->
<!-------------------------------------------------------->
<div class="data-misc"
	data-count="{{count($records)}}"
	data-type="{{$record->type_flag}}"
	data-lessonid="{{$record->id}}"
	data-touchpath="{{(isset($touchPath) ? $touchPath : '')}}"
	data-max="{{count($records)}}"
></div>

	<!-------------------------------------------------------->
	<!-- Add the q and a records -->
	<!-------------------------------------------------------->
@foreach($records as $r)
	<div class="data-slides"
	    data-title="{{$r->title}}"
	    data-number="{{$r->section_number}}"
	    data-description="{{$r->description}}"
	    data-id="{{$r->id}}"
	    data-photo="{{$r->main_photo}}"
	    data-seconds="{{$r->getTime()['runSeconds']}}"
	    data-between="{{$r->getTime()['breakSeconds']}}"
	    data-countdown="{{App\Tools::isLocalhost() ? '1' : '10'}}"
	>
	</div>
@endforeach

<div class="container">

	<!-------------------------------------------------------->
	<!-- Header -->
	<!-------------------------------------------------------->
	<div style="margin-top: 5px;">

		<!-------------------------------------------------------->
		<!-- Top Return Button -->
		<!-------------------------------------------------------->
		<div style="margin: 0 5px 0 0;">
			<span style="font-size:1.3em; margin-right:10px;" class=""><a class="" role="" href="/{{$returnPath}}/{{$record->parent_id}}"><span class="glyphicon glyphicon-button-back-to"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); pause()" href=""><span id="button-pause" class="glyphicon glyphicon-pause"></span></a></span>
			<span style="font-size:1.3em; margin-right:10px;" class=""><a onclick="event.preventDefault(); mute()" href=""><span id="button-mute" class="glyphicon glyphicon-volume-up"></span></a></span>
		</div>

		<!-------------------------------------------------------->
		<!-- Run-time Stats -->
		<!-------------------------------------------------------->
		<div style="font-size:.9em;" id="stats">
			<span id="statsCount"></span>&nbsp;&nbsp;&nbsp;<span id="statsScore"></span>&nbsp;&nbsp;<span id="statsAlert"></span>
		</div>

	</div>

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Start Panel - Index -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-start" class="slide-panel">

	    <div class="text-center">
            <p style="font-size:13px;" class="mb-0">
                <strong>{{$records[0]->course->title}}</strong>
                @if (App\User::isOwner($records[0]->course->user_id))
                <a href="/lessons/add/{{$record->parent_id}}" class="btn btn-success btn-xs ml-1" role="button">Add</a>
                @endif
            </p>
            <h3 class="mt-2 mb-2">{{$records[0]->title_chapter}}</h3>
            <p style="font-size:13px;" class="">{{count($records)}} exercises ({{$displayTime}})</p>
            <a onclick="event.preventDefault(); run()"  href="" class="btn btn-primary mb-3" role="button">Start</a>
        </div>

        <div class="card-deck">
        @foreach($records as $record)
        <div style="min-width:300px;" class="card mb-1">
            <div class="card-body">

                <table style="width:100%;">
                <tbody>
                    <tr>
                        <td style="">
                                <div><a href="/lessons/view/{{$record->id}}">{{$record->section_number}}.&nbsp;{{$record->title}}</a></div>
                                <div>{{$record->getTime()['runSeconds']}} seconds ({{$record->getTime()['breakSeconds']}} rest)</div>
                                <div>
                                @if (App\User::isOwner($record->user_id))
                                    <div>
                                        <a href='/lessons/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a>
                                        <a href='/lessons/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a>
                                        @if ($record->isUnfinished())
                                        <a href='/lessons/publish/{{$record->id}}'><strong><span style="color:red;" class="glyphCustom-sm glyphicon glyphicon-flash"></span></strong></a>
                                        @endif
                                    </div>
                                @endif
                                <div style="font-size:13px;">{{$record->description}}</div>
                                </div>
                        </td>
                        <td>
                                <img width="100" src="/img/plancha/{{$record->main_photo}}" />
                        </td>
                    </tr>
                </tbody>
                </table>

            </div>
        </div>
        @endforeach
        </div><!-- card deck -->

	</div><!-- panel-start -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Countdown Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-countdown" class="slide-panel text-center">
	    <h1>Get Ready</h1>
        <div class="text-center"><h1 style="font-size:100px" class="showSeconds"></h1></div>
	    <h5>Coming up 1 of {{count($records)}}:</h5>
	    <div><img class="sliderPhoto" style="max-width:200px; width:70%;" src="/img/plancha/figure-plancha.png" /></div>
	    <h5 class="slideTitle"></h5>
	    <div class="slideDescription"></div>
	    <h5 class="slideSeconds mt-2"></h5>
	</div><!-- panel-countdown -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Run Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-run" class="slide-panel text-center">

	    <div id="bg" class="text-center" style="
	        height:300px;
	        line-height:300px;
	        background-size: 500px;
	        background-repeat:no-repeat;
	        background-image:url('/* set randomly by js */');
	        background-position:center bottom;
	        "
	    >
	        <img class="sliderPhoto" style="vertical-align: bottom; max-width:400px; width:100%;" src="/* set by js */" />
	    </div>

	    <div class="slideCount"></div>
	    <h5 class="slideTitle"></h5>
	    <div class="slideDescription"></div>
	    <h5 class="slideSeconds mt-2"></h5>
        <div class="text-center"><h1 style="font-size:100px" class="showSeconds"></h1></div>
	</div><!-- panel-run -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Between Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-between" class="slide-panel text-center">
	    <h1>Take a Break</h1>
        <div class="text-center"><h1 style="font-size:100px" class="showSeconds"></h1></div>
	    <h5>Coming up next:</h5>
	    <div class="slideCount"></div>
	    <div><img class="sliderPhoto" style="max-width:200px; width:60%;" src="/img/plancha/figure-plancha.png" /></div>
	    <h5 class="slideTitle"></h5>
	    <div class="slideDescription"></div>
	    <h5 class="slideSeconds mt-2"></h5>
	</div><!-- panel-between -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Pause Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-pause" class="slide-panel text-center">
	Pause Panel
	</div><!-- panel-pause -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- End Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-end" class="slide-panel text-center">
	    <h1>Congratulations!</h1>
	    <h5>{{count($records)}} Exercises completed in {{$displayTime}}</h5>
        <div class="card-deck">
        @foreach($records as $record)
        <div style="min-width:300px;" class="card mb-1">
            <div class="card-body">

                <table style="width:100%;">
                <tbody>
                    <tr>
                        <td style="">
                                <div><a href="/lessons/view/{{$record->id}}">{{$record->section_number}}.&nbsp;{{$record->title}}</a></div>
                                <div>{{$record->getTime()['runTime']}}</div>
                                <div>
                                    @if (App\User::isOwner($record->user_id))
                                    <div>
                                        <a href='/lessons/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a>
                                        <a href='/lessons/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a>
                                    </div>
                                    @endif
                                </div>
                        </td>
                        <td>
                                <img width="100" src="/img/plancha/{{$record->main_photo}}" />
                        </td>
                    </tr>
                </tbody>
                </table>

            </div>
        </div>
        @endforeach
        </div><!-- card deck -->


	</div><!-- panel-end -->

    <div class="text-center">
        <h1 style="font-size:100px" id="debug"></h1>
    </div>

    <audio id="audio">
        <source src="" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

</div><!-- container -->

@endsection
