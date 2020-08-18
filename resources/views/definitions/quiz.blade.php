@extends('layouts.app')

@section('content')

<div class="container page-normal lesson-page">

@if (false)
	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
@endif

@if (false)
	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-lesson-sm" role="button" href="/courses/view/{{$record->parent_id}}">@LANG('content.Back to Vocabulary')&nbsp;{{$courseTitle}}<span class="glyphicon glyphicon-button-back-to"></span></a>
	</div>
@endif

	<h3 name="title" class="">{{$record->title }}</h3>

	@if ($isAdmin)
	<ul class="nav nav-tabs" id="myTab" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">@LANG('content.Exercise')</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">@LANG('content.Questions')&nbsp;({{$sentenceCount}})</a>
		</li>
	</ul>
	@endif

	<div class="tab-content" id="myTabContent">

		<!------------------------------------------------------------------------------->
		<!-- The quiz launch tab                                                       -->
		<!------------------------------------------------------------------------------->
		
		<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
			<div style="min-height:300px;">
				<div style="margin: 20px 0;">
					<a href="/lessons/reviewmc/{{$record->id}}/{{LESSONTYPE_QUIZ_FIB}}"><button class="btn btn-success">Start Vocabulary Review</button></a>
				</div>
			</div>		
		</div>
		
		<!------------------------------------------------------------------------------->
		<!-- The quiz launch tab raw view                                              -->
		<!------------------------------------------------------------------------------->
		<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
			<p>{!! $record->text !!}</p>
		</div>
		
	</div>
		
</div>
@endsection
