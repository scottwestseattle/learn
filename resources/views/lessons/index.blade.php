@extends('layouts.app')

@section('content')

<div class="container page-normal">

	<h1>@LANG('content.' . $titlePlural) ({{count($records)}})</h1>
	
	<div class="row" style="margin-bottom:10px;">		
		@foreach($records as $record)			
		<div style="max-width: 400px; padding:10px;" class="col-sm-4"><!-- outer div needed for the columns and the padding, otherwise they won't center -->

			<div class="drop-box" style="height:200px;  background-color: #4993FD; color:white;" ><!-- inner col div -->

@if (false)
				<div class="index-blog-post text-center" style="padding:15px;">
						<p><a style="color:white; font-size:1.2em;" href="/{{$prefix}}/view/{{$record->id}}">{{$record->getDisplayNumber()}}&nbsp;{{$record->title}}</a></p>	
						<a class="blog-post-text" style="color: white;" href="/{{$prefix}}/view/{{$record->id}}">{{ $record->description}}</a>
				</div>
@endif		
				<a style="background-color: #4993FD; height:100%; width:100%;" class="btn btn-primary btn-lg" role="button" href="/{{$prefix}}/view/{{$record->id}}">
					{{$record->getDisplayNumber()}}&nbsp;{{$record->title}}<br/>{{ $record->description}}
				</a>
					
			</div><!-- inner col div -->			
			
		</div><!-- outer col div -->
		@endforeach		
	</div><!-- row -->														

</div>

@endsection
