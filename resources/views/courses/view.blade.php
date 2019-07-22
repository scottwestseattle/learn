@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-md" role="button" href="/{{$prefix}}/">@LANG('content.Back to Course List')
		<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	</div>
	
	<h3 name="title" class="">{{$record->title }}
		@if ($isAdmin)
			@if (!$record->isFinished())
				<a class="btn {{($wip=$record->getWipStatus())['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$wip['text']}}</a>
			@endif
			@if (!$record->isPublished())
				<a class="btn {{($release=$record->getReleaseStatus())['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$release['text']}}</a>
			@endif
		@endif
	</h3>

	<p>{{$record->description }}</p>
	
	<a href="/lessons/view/{{$firstId}}">
		<button type="button" style="text-align:center; font-size: 1.3em; color:white;" class="btn btn-info btn-lesson-index" {{$disabled}}>Start at the beginning</button>	
	</a>
	
	<h1>@LANG('content.Lessons') ({{count($records)}})
	@if ($isAdmin)
		<span><a href="/lessons/admin/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-admin"></span></a></span>
		<span><a href="/lessons/add/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-add"></span></a></span>
	@endif	
	</h1>
	
	@foreach($records as $record)
	<a href="/lessons/view/{{$record[0]->id}}">
		<button style="" type="button" class="btn btn-outline-info btn-lesson-index link-dark">
			<table>
				<tr>
					<td>
						<div style="font-size:1.3em; color:purple; padding-right:5px;">Chapter {{$record[0]->lesson_number}}:&nbsp;{{$record[0]->title}}</div>
						<span style="font-size:.9em">{{$record[0]->description}}</span>	
					</td>
				</tr>
			</table>
		</button>
	</a>
	@endforeach


</div>
@endsection
