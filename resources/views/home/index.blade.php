@extends('layouts.app')

@section('content')

<div class="container page-normal">

	<div class="mb-5">
		<div class="">
			<h3>@LANG('content.Your Vocabulary Lists') ({{count($words)}})</h3>
		</div>
		<div class="">
			@component('components.data-badge-list', ['records' => $words, 'edit' => '/words/view/', 'title' => 'Latest Vocabulary'])@endcomponent	
			<p><a class="btn btn-primary btn-lg" href="/words/add-user/" role="button">@LANG('content.Add Vocabulary')</a></p>
		</div>
	</div>

	<hr />
	
	<div class="mb-5">
		<div class="">
			<h3>@LANG('content.Your Current Location')</h3>
		</div>
		<div class="">

			@if (isset($course))
				<div class="alert alert-primary" role="alert">
					<h3 class="alert-heading mb-3">{{$course->title}}</h3>
					@if (isset($lesson))
					<hr>
					<h4>@LANG('content.Chapter') {{$lesson->getFullName()}}</h4>
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
			
		</div>
	</div>
		
	<hr />
		
	<div class="mb-5">
		<h3>@LANG('content.Latest Quiz Results')</h3>

		<div class="">

			@if (count($quizes) > 0)
			<ul class="list-group">	
				@foreach($quizes as $quiz)
					<li class="list-group-item list-group-item-{{\App\Lesson::getQuizResultColor($quiz->extraInfo)}}">
						<p style="font-size:1.2em; font-weight:normal;" class="mb-0"><a style="color:#3f3f3f; text-decoration:none;" href="/courses/view/{{$quiz->course_id}}">{{$quiz->course_title}}</a></p>
						<p style="font-size:1.4em; font-weight:bold;" class="alert-heading mb-3"><a style="color:#3f3f3f; text-decoration:none;"  href="/lessons/view/{{$quiz->lesson_id}}">{{App\Lesson::getName($quiz)}}</a></p>
						<!-- p><strong>{{$quiz->extraInfo}}%</strong> - {{$quiz->created_at}}</p -->
						<p><strong><span style="font-size:1em;" class="badge badge-light badge-pill">{{floatVal($quiz->extraInfo)}}%</span></strong> - {{$quiz->created_at}}</p>
					</li>								
				@endforeach
			</ul>			
			@else
				<div class="mb-5">
					<h4>@LANG('content.No quiz results yet').<h4>
				</div>
			@endif							

		</div>
	</div>		
		
	<div class="mb-5">
		<div class="">
			<h3>@LANG('content.Your Stats')</h3>
		</div>
		<div class="">
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
		</div>
	</div>

	<hr />		
		
</div>

@endsection
