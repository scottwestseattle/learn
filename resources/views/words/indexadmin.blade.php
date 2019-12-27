@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'parent_id' => $parent_id, 'isAdmin' => $isAdmin])@endcomponent
	
	<h1>@LANG('content.Vocabulary') ({{count($records)}})</h1>
	
	<div class="row">

		<!-- repeat this block for each column -->
		<div class="col-sm"><!-- need to split word list into multiple columns here -->
			<div class="table" style="font-size: 13px;">
				<table class="table-responsive table-striped table-condensed">
					<tbody>
						@foreach($records as $word)
						<tr>
							<td><a href='/words/edit-user/{{$word->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
							<td>
								<a href="/words/view/{{$word->id}}">
									{{$word->title}}
									@if ($word->view_count > 0)
										<span class="badge" style="vertical-align: middle; background-color: LightGray; color: gray; margin: 0 0 0 3px; font-size:12px; padding:3px 3px; font-weight:bold;">{{$word->view_count}}</span>
									@endif
								</a>
							</td>
							<td>{{$word->description}}<br/><i>{{$word->examples}}</i></td>
							<td>{{$word->last_viewed_at}}</td>
							<td><a href='/{{$prefix}}/confirmdelete/{{$word->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
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
