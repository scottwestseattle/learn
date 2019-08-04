@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'parent_id' => $parent_id, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('content.Vocabulary') ({{count($records)}})</h1>
	
		@if (isset($records) && count($records) > 0)
			@foreach($records as $word)
				<form id="form{{$word->id}}" method="POST" action="">
					<input type="hidden" name="fieldCnt" value="2" />
					<input 
						name="title" id="title{{$word->id}}" 
						class="form-control" type="text" value="{{$word->title}}" 
						onfocus="setFloat($(this), 'float{{$word->id}}');" 
						onblur="ajaxPost('/words/updateajax/{{$word->id}}', 'form{{$word->id}}', 'result{{$word->id}}');" 
					/>
					<textarea name="description" id="description{{$word->id}}" 
						class="form-control" type="text"  
						onfocus="setFloat($(this), 'float{{$word->id}}');" 
						onblur="ajaxPost('/words/updateajax/{{$word->id}}', 'form{{$word->id}}', 'result{{$word->id}}');" 
					>{{$word->description}}</textarea>
				</form>									
				<div style="height:20px; margin-bottom: 20px;">
					<div style="float:left; height:20px; width:20px; margin-right:20px;"><a href="/words/fastdelete/{{$word->id}}"><span class="glyphicon glyphicon-delete"></span></td></a></div>
					<span id="float{{$word->id}}" style="font-weight:bold;";></span>
					<span id="result{{$word->id}}" style="font-size:.7em;"></span>
				</div>
			@endforeach
		@else
			<p>No Vocab List</p>
		@endif

		@component('components.control-accent-chars-esp')@endcomponent		
		
</div>

@endsection
