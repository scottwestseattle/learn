@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.' . $titlePlural) ({{count($public)}})</h1>
	
	<div class="row row-course">
		@foreach($public as $record)			
		<div class="col-sm-12 col-lg-6 col-xl-4 col-course"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
			<div class="card card-course {{$record->getCardColor()}} truncate">
				<a href="/{{$prefix}}/view/{{$record->id}}">
					<div class="card-header">
						<div>{{$record->title}}</div>
						@component('components.data-sitename', ['isAdmin' => $isAdmin, 'siteId' => $record->site_id])@endcomponent
					</div>
					<div class="card-body">
						<p class="card-text">{{$record->description}}</p>
					</div>
				</a>
			</div>
		</div>
		@endforeach
	</div>

	@if ($isAdmin)
	<h1>@LANG('content.Courses Under Development') ({{count($private)}})</h1>
	
	<div class="row row-course">
		@foreach($private as $record)			
		<div class="col-sm-12 col-lg-6 col-xl-4 col-course"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
			<div class="card card-course {{$record->getCardColor()}} truncate">
			<a href="/{{$prefix}}/view/{{$record->id}}">
				<div class="card-header">
					<div>{{$record->title}}</div>
					@component('components.data-sitename', ['isAdmin' => $isAdmin, 'siteId' => $record->site_id])@endcomponent					
				</div>
				<div class="card-body"><p class="card-text">{{$record->description}}</p></div>
			</a>
			</div>
		</div>
		@endforeach
	</div>
	@endif
</div>

@endsection
