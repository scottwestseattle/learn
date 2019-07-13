@extends('layouts.app')

@section('content')

<div class="container page-normal lesson-page">

	<a class="btn btn-success btn-sm" role="button" href="/{{$prefix}}/">@LANG('content.Back to Lessons')</a>

	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-sm {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}">@LANG('ui.Prev')</a>
		<a class="btn btn-primary btn-sm {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">@LANG('ui.Next')</a>
	</div>

    <div style="font-size:.8em;">
		@LANG('content.Lesson')&nbsp;{{$record->lesson_number}}.{{$record->section_number}}
		@if (Auth::user() && Auth::user()->isAdmin())
			&nbsp;<a href="/{{$prefix}}/admin"><span class="glyphCustom-sm glyphicon glyphicon-admin"></span></a>
			&nbsp;<a href="/{{$prefix}}/edit/{{$record->id}}"><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a>
		@endif
	</div>
	<h3 name="title" class="">{{$record->title }}</h3>

	@if (strlen($record->description) > 0)
		<p class=""><i>{{$record->description }}</i></p>
	@endif

	<p>{!! $record->text !!}</p>

	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-sm {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}">@LANG('ui.Prev')</a>
		<a class="btn btn-primary btn-sm {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">@LANG('ui.Next')</a>
	</div>
	<a class="btn btn-success btn-sm" role="button" href="/{{$prefix}}/">@LANG('content.Back to Lessons')</a>

</div>
@endsection
