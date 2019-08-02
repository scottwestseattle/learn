@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.' . $titlePlural) ({{count($records)}})</h1>
	
		@if (isset($records) && count($records) > 0)
		<div class="row">

			<!-- repeat this block for each column -->
			<div class="col-sm"><!-- need to split word list into multiple columns here -->
				<div class="table">
					<table class="table-responsive table-borderless xlesson-table">
						<tbody>
							@foreach($records as $word)
							<tr>
								<td style="width:20px; font-size:.8em;";><a href="/words/fastdelete/{{$word->id}}"><span class="glyphicon glyphicon-delete"></span></td></a>
								<td style="">
									<form id="form{{$word->id}}" method="POST" action="">
										<input type="hidden" name="fieldCnt" value="2" />
										<input name="title" id="title{{$word->id}}" onfocus="setFloat($(this), 'float{{$word->id}}');" onblur="ajaxPost('/words/updateajax/{{$word->id}}', 'form{{$word->id}}', 'result{{$word->id}}');" class="form-control" type="text" value="{{$word->title}}" />
										<input name="description" id="description{{$word->id}}" onfocus="setFloat($(this), 'float{{$word->id}}');" onblur="ajaxPost('/words/updateajax/{{$word->id}}', 'form{{$word->id}}', 'result{{$word->id}}');" class="form-control" type="text" value="{{$word->description}}" />
									</form>									
								</td>
								
								<td id="float{{$word->id}}" style="font-size:1em; font-weight:bold;";></td>
								<td id="result{{$word->id}}" style="font-size:.7em;"></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<!-- end of repeat block -->

		</div>
		@else
			<p>No Vocab List</p>
		@endif

		@component('components.control-accent-chars-esp')@endcomponent		
		
</div>

@endsection
