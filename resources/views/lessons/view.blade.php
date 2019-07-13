@extends('layouts.app')

@section('content')

<div class="container page-normal lesson-page">

	<a class="btn btn-success btn-sm" role="button" href="/{{$prefix}}/">@LANG('content.Back to Lessons')</a>
	
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-sm {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}">@LANG('ui.Prev')</a>
		<a class="btn btn-primary btn-sm {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">@LANG('ui.Next')</a>
	</div>
	
	<h3 name="title" class="">{{$record->title }}</h3>

	@if (strlen($record->description) > 0)
		<p class=""><i>{{$record->description }}</i></p>
	@endif
	
	<p>{!! $record->text !!}</p>

	<a class="btn btn-success btn-sm" role="button" href="/{{$prefix}}/">@LANG('content.Back to Lessons')</a>
	<div class="page-nav-buttons">
		<a class="btn btn-primary btn-sm {{isset($prev) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$prev}}">@LANG('ui.Prev')</a>
		<a class="btn btn-primary btn-sm {{isset($next) ? '' : 'disabled'}}" role="button" href="/{{$prefix}}/view/{{$next}}">@LANG('ui.Next')</a>
	</div>
	
</div>
@endsection
