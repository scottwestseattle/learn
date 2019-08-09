@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'parent_id' => $parent_id, 'isAdmin' => $isAdmin])@endcomponent
	
	<div class="form-group">
	@if (isset($parent_id))
		<a href="/lessons/view/{{$parent_id}}"><button class="btn btn-success">@LANG('content.Back to Lesson')</button></a>
	@else
		<a href="/home"><button class="btn btn-success">@LANG('content.Back to Home')</button></a>
	@endif
	</div>
	
	
	<h1>@LANG('content.Vocabulary') ({{count($records)}})</h1>
	
	<div class="row">

		<!-- repeat this block for each column -->
		<div class="col-sm"><!-- need to split word list into multiple columns here -->
			<div class="table">
				<table class="table-responsive table-borderless xlesson-table">
					<tbody>
						@foreach($records as $word)
						<tr>
							<td><a href='/{{$prefix}}/edit/{{$word->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
							<td>{{$word->title}}</td>
							<td>{{$word->description}}</td>
							<td><a href='/{{$prefix}}/confirmdelete/{{$word->id}}'><span class="glyphCustom glyphicon glyphicon-delete"></span></a></td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
		<!-- end of repeat block -->

	</div>
</div>

@endsection
