@extends('layouts.app')

@section('content')

<div class="container page-normal">

	<h3>@LANG('content.Your Vocabulary Lists') ({{count($words)}})</h3>
	
	@component('components.data-badge-list', ['records' => $words, 'edit' => '/words/edit-user/'])@endcomponent																			

	<p><a class="btn btn-primary btn-lg" href="/words" role="button">@LANG('content.Go to Vocabulary') &raquo;</a></p>
	
	<h3>@LANG('content.Your Courses') ({{count($courses)}})</h3>

	@if (isset($courses))
		<?php $cnt = 0; ?>
		@foreach($courses as $record)
		<div class="alert alert-{{($cnt > 2) ? 'success' : 'primary' }}" role="alert">
			<h4 class="alert-heading mb-3">{{$record->title}}</h4>
			<p>Started on {{date("Y/m/d")}}</p>
			@if ($cnt > 2)
				<p>Completed on {{date("Y/m/d")}}</p>
			@else
				<hr>
				<p class="">Completed: {{$cnt}} of 5 lessons</p>
				<p>Last activity on {{date("Y/m/d")}}</p>
			@endif
			<?php $cnt++; ?>
		</div>
		@endforeach
		
	@else
	
		<h4>No courses started.</h4>
		
	@endif	

	<p><a class="btn btn-primary btn-lg" href="/courses" role="button">@LANG('content.Go to Courses') &raquo;</a></p>
	
</div>

@endsection
