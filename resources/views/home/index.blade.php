@extends('layouts.app')

@section('content')

<div class="container page-normal">

	<div class="card mb-5">
		<div class="card-header">
			<h3>@LANG('content.Your Stats')</h3>
		</div>
		<div class="card-body">
			<span class="card-text">
				<div class="alert alert-primary" role="alert">
					<p style="font-size:1.2em;"><strong>@LANG('content.Account Created'):</strong>&nbsp;{{$stats['accountCreated']}}</p>
				</div>
				<div class="alert alert-success">
					<p style="font-size:1.2em;"><strong>@LANG('content.Last Login'):</strong>&nbsp;{{$stats['lastLogin']}}</p>
				</div>

				@guest
					<p>
						<a class="btn btn-primary btn-lg" href="/login" role="button">@LANG('ui.Login')</a>
						<a class="btn btn-primary btn-lg" href="/register" role="button">@LANG('ui.Register')</a>
					</p>
				@endguest
			</span>
		</div>
	</div>

	<div class="card mb-5">
		<div class="card-header">
			<h3>@LANG('content.Your Vocabulary Lists') ({{count($words)}})</h3>
		</div>
		<div class="card-body">
			<span class="card-text">
				@component('components.data-badge-list', ['records' => $words, 'edit' => '/words/edit-user/'])@endcomponent	
				<p><a class="btn btn-primary btn-lg" href="/words/add/" role="button">@LANG('content.Add Vocabulary')</a></p>
			</span>
		</div>
	</div>

	<div class="card mb-5">
		<div class="card-header">
			<h3>@LANG('content.Your Current Location')</h3>
		</div>
		<div class="card-body">
			<span class="card-text">

			@if (isset($course))
				<div class="alert alert-primary" role="alert">
					<h3 class="alert-heading mb-3">{{$course->title}}</h3>
					
						@if (isset($lesson))
						<hr>
						<h4>{{$lesson->title}}</h4>
						<p>@LANG('content.Last viewed on') {{$stats['lessonDate']}}</p>
						<p><a class="btn btn-primary btn-lg" href="/lessons/view/{{$lesson->id}}" role="button">@LANG('content.Continue Lesson') &raquo;</a></p>
						@endif
				</div>
			@else
				<div class="mb-5">
					<h4>@LANG('content.No lessons viewed yet').<h4>
				</div>
			@endif							
			
				<p><a class="btn btn-primary btn-lg" href="/courses" role="button">@LANG('content.Go to Courses')</a></p>			
			
			</span>
		</div>
	</div>
		
	<div class="card mb-5">
		<div class="card-header">
			<h3>@LANG('content.Your Quiz Results')</h3>
		</div>
		<div class="card-body">
			<span class="card-text">

			@if (count($quizes) > 0)
			<ul class="list-group">	
				@foreach($quizes as $quiz => $score)
					<li class="list-group-item list-group-item-primary">
						<p style="font-size:1.4em; font-weight:bold;" class="alert-heading mb-3">{{$quiz}}</p>
						<p><strong>{{$score}}</strong> - @LANG('content.Date'): 2019-02-12</p>					
					</li>								
				@endforeach
			</ul>			
			@else
				<div class="mb-5">
					<h4>@LANG('content.No quiz results yet').<h4>
				</div>
			@endif							

			</span>
		</div>
	</div>		
		
</div>

@endsection
