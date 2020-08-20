@extends('layouts.app')

@section('content')

<div class="page-size container">
               
	@guest
	@else	
		@component('entries.menu-submenu', ['record' => $record])@endcomponent		
	@endguest
	
	<div class="mb-5">
	<!-- Title -->
	<h2 name="title" class="">{{$record->title}}</h2>
	
	<h3><b>{{$stats['wordCount']}}</b> total words</h3>
	<h3><b>{{$stats['uniqueCount']}}</b> unique words</h3>

	<div>
	
	@if ($stats['uniqueCount'] <= 300)
		
		<h3>Most Commonly Used</h3>
		@foreach($stats['sortCount'] as $key => $value)
			<span style="color:green;">{{$key}}</span> <span style="font-size:11px; color:gray; margin-right:10px;">({{$value}}) </span>
		@endforeach
		
		<h3>Alphabetical Order</h3>
		@foreach($stats['sortAlpha'] as $key => $value)
			<span style="color:teal;">{{$key}}</span> <span style="font-size:11px; color:gray; margin-right:10px;">({{$value}}) </span>
		@endforeach
		
	@else

		<h3>Most Common Words</h3>
		<?php $i = 0; ?>
		@foreach($stats['sortCount'] as $key => $value)
			<span style="color:green;">{{$key}}</span> <span style="font-size:11px; color:gray; margin-right:10px;">({{$value}}) </span>
			<?php if ($i++ > 200) break; ?>
			<?php //if ($value == 1) break; ?>
		@endforeach
		
	@endif
	</div>
		
	@if (false)
	<h3>A</h3>
	<div>
	@foreach($stats['sortAlpha'] as $key => $value)
		@if (App\Tools::startsWith($key, 'a'))
			{{$key . ' (' . $value . ') '}}
		@endif
	@endforeach
	</div>

	<h3>B</h3>
	<div>
	@foreach($stats['sortAlpha'] as $key => $value)
		@if (App\Tools::startsWith($key, 'b'))
			{{$key . ' (' . $value . ') '}}
		@endif
	@endforeach
	</div>
	@endif
	
	</div>

</div>

@endsection
