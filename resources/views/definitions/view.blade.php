@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	@if (isset($fromDictionary))
	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-top" role="button" href="/{{$prefix}}/">
		    @LANG('content.Back to Dictionary')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	</div>
	@endif
	
	<!-- Top nav buttons -->
	@if (isset($prev) || isset($next))
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-sm btn-nav-lesson {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{isset($prev) ? $prev->id : 0}}">
			<span class="glyphicon glyphicon-button-prev"></span>
			@LANG('ui.Prev')
		</a>
		<a class="btn btn-primary btn-sm btn-nav-lesson {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{isset($next) ? $next->id : 0}}">
			@LANG('ui.Next')
			<span class="glyphicon glyphicon-button-next"></span>
		</a>
	</div>
	@endif

	<!-- Show the record -->
	@if (isset($record))
		
	<div style="mt-3">
		<h3>
			{{$record->title}}<span style="vertical-align: middle; background-color: LightGray; color: gray; margin-left: 7px; font-size:13px; padding:3px 3px; font-weight:bold;" class="badge">{{$record->view_count}}</span>
			@if (App\Definition::possibleVerb($record->title))
				<div class="small-thin-text mt-2"><a href="/{{PREFIX . '/conjugate/' . $record->title}}/">conjugate</a>
			@endif
		</h3>
	</div>

	<div class="">
		@if (isset($record->definition))
			<p style="font-size:1.2em;">{!! nl2br($record->definition) !!}</p>
		@endif
		@if (isset($record->translation_en))
			<p style="font-size:1.2em;">{{$record->translation_en}}</p>
		@endif
		@if (isset($record->translation_es))
			<p style="font-size:1.2em;">{{$record->translation_es}}</p>
		@endif
		@if (isset($record->examples))
		@foreach($record->examples as $example)
			<p><i>{{$example}}</i></p>
		@endforeach
		@endif
	<div>
	@else
		
	<div style="mt-3">
		<h3>{{$word}}</h3>
	</div>

	<div class="">
		<p style="font-size:1.2em;">Not found in dictionary</p>
		<p><a target='_blank' href="https://translate.google.com/#view=home&op=translate&sl=es&tl=en&text={{$word}}">Google Translate: {{$word}}</a></p>
		<p><a target='_blank' href="https://www.spanishdict.com/translate/{{$word}}">Span¡shD!ct.com: {{$word}}</a></p>

	<div>
	
	@endif

	<!-- Bottom nav buttons -->
	<div class="page-nav-buttons">
		@if (isset($prev))
		<a class="btn btn-primary btn-sm btn-nav-lesson" role="button" href="/{{$prefix}}/view/{{$prev->id}}">
			<span class="glyphicon glyphicon-button-prev"></span>
			{{$prev->title}}
		</a>
		@endif
		@if (isset($next))
		<a class="btn btn-primary btn-sm btn-nav-lesson" role="button" href="/{{$prefix}}/view/{{$next->id}}">
			{{$next->title}}
			<span class="glyphicon glyphicon-button-next"></span>
		</a>
		@endif
	</div>

	@if (isset($record) && isset($record->forms))
		<div class="small-thin-hdr mt-2 mb-1">Forms</div>
		<div class="small-thin-text mt-2">{{App\Definition::getFormsPretty($record->forms)}}</div>
	@endif

	@if (isset($record) && isset($record->conjugations) && is_array($record->conjugations))
		<?php $i = 0; ?>
		<div class="small-thin-hdr mt-2 mb-1">Participles</div>
		<div class="small-thin-text mb-2">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-hdr mt-2 mb-1">Indicative</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-hdr mt-2 mb-1">Subjunctive</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-hdr mt-2 mb-1">Imperative</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
		<div class="small-thin-text mb-1">{{$record->conjugations[$i++]}}</div>
	@endif

</div>

@endsection

