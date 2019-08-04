@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'parent_id' => $parent_id, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.Vocabulary') ({{count($records)}})</h1>
	
	<div class="row">

		<!-- repeat this block for each column -->
		<div class="col-sm"><!-- need to split word list into multiple columns here -->
			<div class="table">
				<table class="table-responsive table-borderless xlesson-table">
					<tbody>
						@foreach($records as $word)
						<tr>
							<td>{{$word->title}}</td>
							<td>{{$word->description}}</td>
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
