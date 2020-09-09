@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $tag, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-top" role="button" href="/vocab-lists/">
		    @LANG('content.Back to Lists')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	    <a class="btn btn-primary btn-sm btn-nav-top" role="button" href="/{{$prefix}}/review/{{$tag->id}}">
            @LANG('ui.Review')&nbsp;<span class="glyphicon glyphicon-eye-open"></span>
	    </a>
	</div>

	<h3 name="" class="" style="margin-bottom:10px;">{{$tag->name}}@component('components.badge', ['text' => count($records)])@endcomponent</h3>
	<div id="removeStatus"></div>

	<table style="width:100%;" class="table xtable-striped">
		<tbody>
		@foreach($records as $record)
			<tr id="row{{$record->id}}">
				<td style="width:100%;">
				    <a href="/definitions/view/{{$record->id}}">{{$record->title}}</a>
					<div>{{$record->translation_en}}</div>
				</td>

				@if (count($lists) > 1)
				<td class="icon mr-2">
					<div class="dropdown" >
						<!-- removed 'dropdown-toggle' class to remove the down arrow graphic -->
						<a class="" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true"	href="" onclick="">
							<div class="glyphCustom-md glyphicon glyphicon-heart"></div>
						</a>
						
						<ul class="small-thin-text dropdown-menu dropdown-menu-right">
							@foreach($lists as $list)
								@if ($tag->id != $list->id)
									<li><a class="dropdown-item" href="/definitions/set-favorite-list/{{$record->id}}/{{$tag->id}}/{{$list->id}}">{{$list->name}}</a></li>
								@endif
							@endforeach
						</ul>
					</div>
				</td>
				@endif
				
				<td class="icon mr-3">
					<div class="dropdown" >
						<a class="" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true"	href="" onclick="">
							<div class="glyphCustom-md glyphicon glyphicon-remove"></div>
						</a>
						
						<ul class="small-thin-text dropdown-menu dropdown-menu-right">
							<li><a class="dropdown-item" href="" onclick="unheartDefinition(event, {{$record->id}}, '#removeStatus'); $('#row{{$record->id}}').hide();">@LANG('content.Remove from List')</a></li>
						</ul>
					</div>				
				</td>
				
			</tr>
		@endforeach
		</tbody>
	</table>

</div>
@endsection
