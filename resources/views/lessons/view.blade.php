@extends('layouts.app')

@section('content')

<div class="container page-normal lesson-page">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-lesson-sm" role="button" href="/courses/view/{{$record->parent_id}}">@LANG('content.Back to')&nbsp;{{$courseTitle}}<span class="glyphicon glyphicon-button-back-to"></span></a>
		<a class="btn btn-success btn-sm btn-nav-lesson-sm {{isset($nextChapter) ? '' : 'disabled'}}" role="button" href="/lessons/view/{{$nextChapter}}">@LANG('content.Next Chapter')<span class="glyphicon glyphicon-button-next"></span></a>
	</div>
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-lg btn-nav-lesson {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}">
			<span class="glyphicon glyphicon-button-prev"></span>
			@LANG('ui.Prev')
		</a>
		<a class="btn btn-primary btn-lg btn-nav-lesson {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">
			@LANG('ui.Next')
			<span class="glyphicon glyphicon-button-next"></span>
		</a>
	</div>

    <div style="font-size:.8em;">
		{{$courseTitle}},&nbsp;@LANG('content.Chapter')&nbsp;{{$record->lesson_number}}.{{$record->section_number}}&nbsp;({{$sentenceCount}})
		
		@if ($isAdmin)
			&nbsp;<a href="/{{$prefix}}/edit2/{{$record->id}}"><span class="glyphCustom-sm glyphicon glyphicon-pencil"></span></a>
			<a class="btn {{($status=$record->getStatus())['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$status['text']}}</a>
		@endif
	</div>
	<h3 name="title" class="">{{$record->title }}</h3>

	@if (strlen($record->description) > 0)
		<p class=""><i>{{$record->description }}</i></p>
	@endif

	@if ($record->isQuiz())
		
		@if ($isAdmin)
		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">@LANG('content.Exercise')</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">@LANG('content.Questions')</a>
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
						<a href="/lessons/reviewmc/{{$record->id}}/{{LESSONTYPE_QUIZ_FIB}}"><button class="btn btn-success">Fill in the Blank</button></a>
					</div>
					
					@if ($record->getLessonType() == LESSONTYPE_QUIZ_MC1)
					<div style="margin: 20px 0;">
						<a href="/lessons/review/{{$record->id}}/{{LESSONTYPE_QUIZ_MC1}}"><button class="btn btn-primary">Multiple Choice</button></a>
					</div>
					@elseif ($record->getLessonType() == LESSONTYPE_QUIZ_MC2)
					<div style="margin: 20px 0;">
						<a href="/lessons/review/{{$record->id}}/{{LESSONTYPE_QUIZ_MC2}}"><button class="btn btn-info">Multiple Choice 2</button></a>
					</div>
					@elseif ($record->getLessonType() == LESSONTYPE_QUIZ_MC3)
					<div style="margin: 20px 0;">
						<a href="/lessons/reviewmc/{{$record->id}}/{{LESSONTYPE_QUIZ_MC3}}"><button class="btn btn-info">Multiple Choice 3</button></a>
					</div>
					@elseif ($record->getLessonType() == LESSONTYPE_QUIZ_MC4)
					<div style="margin: 20px 0;">
						<a href="/lessons/reviewmc/{{$record->id}}/{{LESSONTYPE_QUIZ_MC4}}"><button class="btn btn-info">Multiple Choice 4</button></a>
					</div>
					@else
						<!-- FIB ONLY -->
					@endif
				</div>		
			</div>
			
			<!------------------------------------------------------------------------------->
			<!-- The quiz launch tab                                                       -->
			<!------------------------------------------------------------------------------->
			<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
				<p>{!! $record->text !!}</p>
			</div>
			
		</div>
		
	@else
		
		<p>{!! $record->text !!}</p>
		
	@endif
	
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-lg btn-nav-lesson {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}"><span class="glyphicon glyphicon-button-prev"></span>@LANG('ui.Prev')</a>
		<a class="btn btn-primary btn-lg btn-nav-lesson {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">@LANG('ui.Next')<span class="glyphicon glyphicon-button-next"></span></a>
	</div>

	@component('lessons.comp-lesson-list', ['records' => $lessons, 'tableClass' => 'table-lesson-list'])@endcomponent
	
</div>
@endsection
