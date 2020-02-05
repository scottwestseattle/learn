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
></div>

	<!-------------------------------------------------------->
	<!-- Add the q and a records -->
	<!-------------------------------------------------------->
@foreach($records as $r)
	<div class="data-qna"
	    data-title="{{$r->title}}"
	    data-description="{{$r->description}}"
	    data-id="{{$r->id}}">
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
		<div style="float:left; margin: 0 5px 0 0;">
			<span style="font-size:1.3em;" class=""><a class="" role="" href="/{{$returnPath}}/{{$record->parent_id}}"><span class="glyphicon glyphicon-button-back-to"></span></a></span>
		</div>

		<!-------------------------------------------------------->
		<!-- Run-time Stats -->
		<!-------------------------------------------------------->
		<div style="font-size:.9em;" id="stats">
			<span id="statsCount"></span>&nbsp;&nbsp;&nbsp;<span id="statsScore"></span>&nbsp;&nbsp;<span id="statsAlert"></span>
		</div>

	</div>

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Start Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-start" class="quiz-panel">
	    <div style="text-align:center; class="">
            <h2 class="mb-0">{{$records[0]->course->title}}</h2>
            <h3 class="mt-2 mb-2">{{$records[0]->title_chapter}}</h3>
            <h4 class="">{{count($records) * 2}} minutes - {{count($records)}} exercises</h4>
        </div>

        <div class="card-deck">

        @foreach($records as $record)
        <div style="min-width:300px;" class="card mb-1">
            <div class="card-body">

                <table style="width:100%;">
                <tbody>
                    <tr>
                        <td style="">
                                <h5>
                                    <a href="/lessons/view/{{$record->id}}">{{$record->title}}</a>
                                </h5>

                                <div>
                                @if (App\User::isOwner($record->user_id))
                                    <div>
                                        <a href='/lessons/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a>
                                        <a href='/lessons/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a>
                                    </div>
                                @endif
                                {{$record->description}}
                                </div>
                        </td>
                        <td>
                                <img width="100" src="/img/plancha/figure-plancha.png" />
                        </td>
                    </tr>
                </tbody>
                </table>

            </div>
        </div>
        @endforeach
        </div><!-- card deck -->
	</div><!-- start panel -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- Run Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-run" class="quiz-panel text-center">
	Run Panel
	</div><!-- run panel -->

	<!---------------------------------------------------------------------------------------------------------------->
	<!-- End of Panel -->
	<!---------------------------------------------------------------------------------------------------------------->
	<div id="panel-end" class="quiz-panel text-center">
	End Panel
	</div><!-- end panel -->

</div><!-- container -->

@endsection
