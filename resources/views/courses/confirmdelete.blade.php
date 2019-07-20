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
			
			@foreach($children as $record)
			<a href="/lessons/view/{{$record->id}}">
				<button style="" type="button" class="btn btn-outline-info btn-lesson-index link-dark">
					<table>
						<tr>
							<td style="">
								<span style="font-size:1.3em; color:purple; padding-right:5px;">{{$record->getDisplayNumber()}}</span>&nbsp;
							</td>
							<td>
								{{$record->title}}<br/><span style="font-size:.9em">{{$record->description}}</span>	
							</td>
						</tr>
					</table>
				</button>
			</a>
			@endforeach
	
		@else
			
		<h3 name="title" class="">@LANG('content.' . $record->title)</h3>
		
		<div class="form-group">
			<button type="submit" class="btn btn-primary">@LANG('ui.Confirm Delete')</button>
		</div>		
	
		<p>{{$record->permalink }}</p>

		<p>{{$record->description }}</p>
		
		<div class="submit-button">
			<button type="submit" class="btn btn-primary">@LANG('ui.Confirm Delete')</button>
		</div>
		
		@endif
		
	{{ csrf_field() }}
	</form>
</div>
@endsection
