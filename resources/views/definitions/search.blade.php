@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
	
	<h1>@LANG('content.Dictionary') ({{count($records)}})
		<span style="" class="small-thin-text mb-2">
			<a href="/definitions/search/1">{{'A-Z'}}</a>&nbsp;&nbsp;
			<a href="/definitions/search/2">{{'Z-A'}}</a>&nbsp;&nbsp;
			<a href="/definitions/search/3">{{'newest'}}</a>
			<a href="/definitions/search/4">{{'recently viewed'}}</a>
			<a href="/definitions/search/5">{{'missing translation'}}</a>
			<a href="/definitions/search/6">{{'missing definition'}}</a>
		</span>
	</h1>

	<div class="row">

		<!-- repeat this block for each column -->
		<div class="col-sm"><!-- need to split word list into multiple columns here -->
			<div class="table" style="font-size: 13px;">
				<table class="table-responsive table-striped table-condensed">
					<tbody>
						@foreach($records as $record)
						<tr>
							@if ($isAdmin)
								<td><a href='/definitions/edit/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
							@endif
							<td>
								<a class="medium-thin-text" href="/definitions/view/{{$record->id}}">
									{{$record->title}}
								</a>
							</td>
							<td>
								<div class="medium-thin-text mb-2">{!!nl2br($record->definition)!!}</div>
								@if (isset($record->conjugations))
									<div class="small-thin-text mb-2"><a href="" onclick="event.preventDefault(); $('#hide-{{$record->id}}').show(); $('#showconjugations-{{$record->id}}').show(); ajaxexec('/definitions/showconjugations/{{$record->id}}', '#showconjugations-{{$record->id}}');">{{'conjugations'}}</a> <span style="display:none;" id="hide-{{$record->id}}"><a href="" onclick="event.preventDefault(); $('#hide-{{$record->id}}').hide(); $('#showconjugations-{{$record->id}}').hide();">(hide)</a></span></div>
									<div id="showconjugations-{{$record->id}}"></div>
								@endif
								<div class="teal"><i>{!!nl2br($record->examples)!!}</i></div>
								@if (isset($record->translation_en))
									<div class="mt-2 steelblue">English: {!!nl2br($record->translation_en)!!}</div>
								@endif
								<div class="small-thin-text mt-2">
									{{$record->view_count}} view{{$record->view_count !== 1 ? 's' : ''}}@if (isset($record->last_viewed_at))<span>,  last on {{App\Tools::timestamp2date($record->last_viewed_at)}}</span>@endif
								</div>
							</td>
							@if ($isAdmin)
							<td><a href='/{{$prefix}}/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
							@endif
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
