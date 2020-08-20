@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h3>{{$verb}}</h3>

	<div class="">
		@if (isset($forms))
			<p style="">{{$forms}}</p>
		@endif
		
		@if (isset($records))
			
			<div class="mb-3">
			<h4>Participles:</h4>
			@foreach($records[CONJ_PARTICIPLE] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Indicative Present:</h4>
			@foreach($records[CONJ_IND_PRESENT] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Indicative Preterite:</h4>
			@foreach($records[CONJ_IND_PRETERITE] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Indicative Imperfect:</h4>
			@foreach($records[CONJ_IND_IMPERFECT] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Indicative Conditional:</h4>
			@foreach($records[CONJ_IND_CONDITIONAL] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Subjective Present:</h4>
			@foreach($records[CONJ_SUB_PRESENT] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Subjective Imperfect:</h4>
			@foreach($records[CONJ_SUB_IMPERFECT] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Subjective Imperfect:</h4>
			@foreach($records[CONJ_SUB_IMPERFECT2] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Subjective Future:</h4>
			@foreach($records[CONJ_SUB_FUTURE] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>
			
			<div class="mb-3">
			<h4>Imperative:</h4>
			@foreach($records[CONJ_IMP_AFFIRMATIVE] as $record)
				<span><i>{{$record}}</i></span>
			@endforeach
			</div>

		@endif
	<div>

</div>

@endsection

