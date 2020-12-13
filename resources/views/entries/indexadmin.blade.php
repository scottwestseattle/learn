@extends('layouts.app')

@section('content')

<?php 

$header = 'Entries';
if (isset($title))
{
	$header = $title;
}

?>

<div class="page-size container">

	<!-- Sub-menu ------>
	<div class="submenu-view" style="font-size:20px;">
		<table class=""><tr>			
			<td style="width:40px;"><a href='/entries/add/'><span class="glyphCustom glyphicon glyphicon-plus-sign"></span></a></td>			
		</tr></table>
	</div>			

		<!-- -1=not set, 1=entry, 2=tour/hike, 3=blog, 4=blog entry, 5=article, 6=note, 7=section, 99=other -->
		<div style="margin:15px 0;">
			<a style="margin-right:10px;" href="/entries/indexadmin">Show All</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_ARTICLE}}">Articles</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_BLOG}}">Blogs</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_BLOG_ENTRY}}">Blog Entries</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_ENTRY}}">Entries</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_GALLERY}}">Galleries</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_HOTEL}}">Hotels</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_NOTE}}">Notes</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_SECTION}}">Sections</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_TOUR}}">Tours</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_OTHER}}">Other</a>
			<a style="margin-right:10px;" href="/entries/indexadmin/{{ENTRY_TYPE_NOTSET}}">Not Set</a>
		</div>
	
	
	<h1 style="font-size:1.3em;">{{ $header }} ({{ count($records) }})</h1>
	
	@if (Auth::check())
		<table class="table table-striped">
			<tbody>
			@foreach($records as $record)
				<tr>
					<td style="width:20px;"><a href='/entries/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
					<td style="width:20px;"><a href='/photos/entries/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-picture"></span></a></td>
					<td style="width:20px;"><a href='/entries/publish/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-flash"></span></a></td>
					<td>
						<a href="{{ route('entry.permalink', [$record->permalink]) }}">{{$record->title}}&nbsp;({{$entryTypes[$record->type_flag] . ', ' . intval($record->photo_count) . ' photos'}})</a>

						@if (intval($record->view_count) > 0)
							<span style="color:#8CB7DD; margin-left: 5px; font-size:.9em;" class="glyphCustom glyphicon glyphicon-copy"><span style="font-family:verdana; margin-left: 2px;" >{{ $record->view_count }}</span></span>
						@endif

						@if ($record->published_flag == 0 || $record->approved_flag == 0)
							<a style="color: red; margin-left:5px;" href="/entries/publish/{{$record->id}}">
								<span class="glyphCustom glyphicon glyphicon-flash"></span>
							</a>
						@endif
													
						@if (strlen($record->permalink) === 0)
							<div><a href="/entries/edit/{{$record->id}}"><button type="button" class="btn btn-danger btn-alert">@LANG('ui.No Permalink')</button></a></div>
						@endif
												
					</td>
					<td>
						<a href='/entries/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-trash"></span></a>
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	@else
		<h3>You need to log in. <a href="/login">Click here to login</a></h3>
	@endif       
</div>
@endsection
