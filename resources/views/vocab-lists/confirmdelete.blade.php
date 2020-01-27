@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Delete') @LANG('content.' . $title)</h1>

	<form method="POST" action="/{{$prefix}}/delete/{{ $record->id }}">


		@if (isset($children) && count($children) > 0)

			<h3 name="title" class="">@LANG('content.' . $record->title) lessons ({{count($children)}})</h3>
			<p>A course with lessons cannot be deleted.</p>
			<p>Please move or remove the lessons first.</p>

			@component('lessons.comp-lesson-list', ['records' => $children])@endcomponent

		@else

		<h4>{{$record->title}}</h4>

        <p>Are you sure you want to delete <strong>{{$record->title}}</strong>?</p>
		<div class="submit-button">
			<button type="submit" class="btn btn-primary">@LANG('ui.Confirm Delete')</button>
		</div>

		@endif

	{{ csrf_field() }}
	</form>
</div>
@endsection
