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
					<p style="font-size:1.2em;"><strong>@LANG('content.Last Login'):</strong>&nbsp;{{$lastLogin}}</p>
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
						<p>@LANG('content.Last viewed on') {{$lessonDate}}</p>
						<p><a class="btn btn-primary btn-lg" href="/lessons/view/{{$lesson->id}}" role="button">@LANG('content.Continue Lesson') &raquo;</a></p>
						@endif
				</div>
			@else
				<div class="mb-5">
					<h4>@LANG('content.No lessons viewed yet').<h4>
				</div>
			@endif							
			
				<p><a class="btn btn-primary btn-lg" href="/courses" role="button">@LANG('content.Go to Courses') &raquo;</a></p>			
			
			</span>
		</div>
	</div>
		
</div>

@endsection
