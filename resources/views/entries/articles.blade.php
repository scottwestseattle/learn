@extends('layouts.app')

@section('content')

<div class="container">

	@component('entries.menu-submenu')@endcomponent	

	<h1 style="font-size:1.3em;">@LANG('ui.Articles') ({{ count($records) }})</h1>

			<div class="row clearfix text-left">
				
				<table>
				<tbody>
				@foreach($records as $record)
					@if (($record->approved_flag != 1 || $record->published_flag !=1) && (!Auth::check() || Auth::user()->user_type < 1000))
						@continue
					@endif
					<tr>
						<td><a href="/entries/{{$record->permalink}}">{{$record->title}}</a></td>
						<td class="article-extra">{{$record->display_date}}</td>
						<td class="article-extra">{{$record->view_count}} @LANG('ui.views')</td>
						
						@if (Auth::user() != null && Auth::user()->isAdmin())
							<?php $glyphRed = (($record->approved_flag != 1 || $record->published_flag !=1)) ? 'glyphRed' : ''; ?>				
							<td><a href='/entries/publish/{{$record->id}}'><span class="glyphCustom-sm {{$glyphRed}} glyphicon glyphicon-flash"></span></a></td>						
							<td><a href='/entries/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
							<td><a href='/entries/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-trash"></span></a></td>
						@endif
					</tr>
					<tr><td>&nbsp;</td><td></td></tr>
				@endforeach
				</tbody>
				</table>
					
			</div><!-- row -->		
	
</div>
@endsection
