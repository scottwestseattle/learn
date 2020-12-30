@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@if (isset($vocabLists) && count($vocabLists) > 0)
	<div class="mb-5">
		<div class="">
            <!-- SHOW VOCAB LISTS -->
 			<h3>@LANG('content.Your Vocabulary Lists') ({{count($vocabLists)}})</h3>
            <div class="row row-course">
                @foreach($vocabLists as $record)
                <div class="col-sm-4 col-course"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
                    <div class="card card-course-list truncate">
                    <a href="/vocab-lists/view/{{$record->id}}">
                        <div class="card-header">{{$record->title}}</div>
                        <div class="card-body">
                            <p class="card-text">{{count($record->words)}} entries</p>

                            <?php $published = App\Status::getReleaseStatus($record->release_flag); ?>
                            <?php $finished = App\Status::getWipStatus($record->wip_flag); ?>
        					<p>
        					    @if (!$finished['done'])
        					    <a class="btn btn-warning btn-xs" href="/vocab-lists/publish/{{$record->id}}" role="button">{{$finished['text']}}</a>
        		                @endif
        		                @if ($published['done'])
            					    <a class="btn btn-success btn-xs" href="/vocab-lists/publish/{{$record->id}}" role="button">Public</a>
        		                @else
            					    <a class="btn btn-warning btn-xs" href="/vocab-lists/publish/{{$record->id}}" role="button">{{$published['text']}}</a>
        		                @endif
        					</p>
                        </div>
                    </a>
                    </div>
                </div>
                @endforeach
            </div>
            <!-- END OF VOCAB LISTS -->
		</div>

		@if (false)
		<div>
			<p><a class="btn btn-primary btn-lg" href="/words/add-user/" role="button">@LANG('content.Add Vocabulary')</a></p>
		</div>
		@endif

	</div>

	<hr />
	@endif

	@if (App\Tools::siteUses(ID_FEATURE_COURSES))
        @if (Auth::check() && isset($course))
            <div class="mb-5">
                <div class="">
                    <h3>@LANG('content.Courses in Progress')</h3>
                </div>
                <div class="">

                    @if (isset($course))
                        <div class="alert alert-primary" role="alert">
                            <h3 class="alert-heading mt-0">{{$course->title}}</h3>
                            @if (isset($lesson))
                            <hr>
                            <h4>@LANG('content.Chapter') {{$lesson->getFullName()}}</h4>
                            <p>@LANG('content.Last viewed on') {{$stats['lessonDate']}}</p>
                            <p><a class="btn btn-primary btn-lg" href="/lessons/view/{{$lesson->id}}" role="button">@LANG('content.Continue Lesson') &raquo;</a></p>
                            @endif
                        </div>
                    @else
                        <div class="mb-5">
                            <h4>@LANG('content.No lessons started').<h4>
                        </div>
                    @endif

                </div>
            </div>
            <hr />
        @else
            <h3>@LANG('content.No Courses Started')</h3>
            <p><a class="btn btn-primary btn-lg" href="/courses" role="button">@LANG('content.Go to Courses')</a></p>
        @endif
	@endif

	@if (isset($quizes))
	<div class="mb-5">
		<h3>@LANG('content.Review Results')</h3>

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
	@endif

	<div class="mb-5">
		<div class="">
			<h3>@LANG('content.Stats')</h3>
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

</div>

@endsection
