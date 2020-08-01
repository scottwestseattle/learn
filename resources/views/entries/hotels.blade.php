@extends('layouts.app')

@section('content')

<div class="page-size container">

	@component('entries.menu-submenu')@endcomponent	

	<h1 style="font-size:1.3em;">@LANG('content.Hotels') ({{ count($records) }})</h1>

			<div class="row clearfix text-left">
				
				<table>
				<tbody>
				@foreach($records as $record)
					@if (($record->approved_flag != 1 || $record->published_flag !=1) && (!Auth::check() || Auth::user()->user_type < 1000))
						@continue
					@endif
					<tr style="vertical-align:top;">
						<td style="margin-bottom:10px;" >
							<a href="/entries/{{$record->permalink}}">
								@component('entries.show-main-photo', ['record' => $record, 'class' => 'index-article'])@endcomponent
							</a>							
						</td>
						<td style="color:default; padding: 0 10px;">
							<table>
							<tbody>
								@if ($record->approved_flag != 1 || $record->published_flag != 1)
								<tr><td style="font-size:1.3em;"><a style="color:default;" href="/entries/{{$record->permalink}}"><span style="color:red;">PRIVATE:</span> {{$record->title}}</a></td></tr>
								@else
								<tr><td style="font-size:1.3em;"><a style="color:default;" href="/entries/{{$record->permalink}}">{{$record->title}}</a></td></tr>
								@endif
								@if (isset($record->display_date))
								<tr><td>{{$record->display_date}}</td></tr>
								@endif
								@if (isset($record->location))
									@if ($record->location_type != LOCATION_TYPE_COUNTRY)
										<tr><td>{{$record->location}}, {{$record->location_parent}}</td></tr>
									@else
										<tr><td>{{$record->location}}</td></tr>
									@endif
								@endif
							</tbody>
							</table>
						</td>
					</tr>
					<tr><td>&nbsp;</td><td></td></tr>
				@endforeach
				</tbody>
				</table>
					
			</div><!-- row -->		
	
</div>
@endsection
