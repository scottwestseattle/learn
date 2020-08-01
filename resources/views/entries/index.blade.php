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
			<div class="" style="font-size:20px;">
				<table class=""><tr>			
					<td style="width:40px;"><a href='/entries/add/'><span class="glyphCustom glyphicon glyphicon-plus-sign"></span></a></td>			
				</tr></table>
			</div>			

	<h1 style="font-size:1.3em;">@LANG('ui.' . $header) ({{ count($records) }})</h1>
	@if (Auth::check())
		<table class="table table-striped">
			<tbody>
			@foreach($records as $record)
				<tr>
					<td style="width:20px;"><a href='/entries/show/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-asterisk"></span></a></td>
					<td style="width:20px;"><a href='/entries/edit/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
					<td>
						<a href="/entries/show/{{$record->id}}">{{$record->title}}</a>
						
						<?php if (intval($record->view_count) > 0) : ?>
							<span style="color:#8CB7DD; margin-left: 5px; font-size:.9em;" class="glyphCustom glyphicon glyphicon-copy"><span style="font-family:verdana; margin-left: 2px;" >{{ $record->view_count }}</span></span>
						<?php endif; ?>
						
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
