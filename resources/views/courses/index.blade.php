@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.' . $titlePlural) ({{count($records)}})</h1>
	
	<div class="row row-course">
		@foreach($records as $record)			
		<div class="col-sm-4 col-course"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
			<div class="card card-course {{$record->getCardColor()}} truncate">
			<a href="/{{$prefix}}/view/{{$record->id}}">
				<div class="card-header">{{$record->title}}</div>
				<div class="card-body"><p class="card-text">{{$record->description}}</p></div>
			</a>
			</div>
		</div>
		@endforeach
	</div>

</div>

@endsection
