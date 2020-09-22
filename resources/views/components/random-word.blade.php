<div id="randomWord">
<h2 class="section-heading mt-0 mb-1">@LANG('content.Random Word')</h2>
<h3 class=""><a style="color:white;" href="/definitions/view/{{$record->id}}">{{$record->title}}</a></h3>
<p>{{$record->translation_en}}</p>
<div><i>{!!nl2br($record->examples)!!}</i></div>
<a class="white" href="" onclick="getRandomWord(event, '#randomWord')"><div style="font-size:24px;" class="mt-2 glyphicon glyphicon-refresh"></div></a>
</div>
