@extends('layouts.app')

@section('content')

<div class="container page-normal">

	<!-- Sub-menu ------>
	<div class="" style="font-size:20px;">
		<table class=""><tr>			
			<td style="width:40px;"><a href='/translations/add/'><span class="glyphCustom glyphicon glyphicon-plus-sign"></span></a></td>			
		</tr></table>
	</div>			
	
	<h1>@LANG('ui.Translations') ({{ count($records) }})</h1>

	<table class="table table-striped">
		<tbody>
		@foreach($records as $record)
			<tr>
				<td style="width:20px;"><a href='/translations/edit/{{$record}}'><span class="glyphCustom glyphicon glyphicon-edit"></span></a></td>
				<td>
					<a href="/translations/edit/{{$record}}">{{$record}}</a>		
				</td>
				<td>
					<a href='/translations/confirmdelete/{{$record}}'><span class="glyphCustom glyphicon glyphicon-trash"></span></a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	
</div>
@endsection
