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
					<a href="" onclick="event.preventDefault(); $('#selectList{{$record->id}}').show(); "><div class="glyphCustom-md glyphicon glyphicon-heart"></div></a>
					
				<div class="dropdown" >
					<a  style="font-size:12px;"href="#" class="dropdown-toggle navbar-brand" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">{{$tag->name}}</a>
					<ul class="dropdown-menu">
						@foreach($lists as $list)
							<li><a href="/definitions/set-favorite-list/{{$record->id}}/{{$tag->id}}/{{$list->id}}">{{$list->name}}</a></li>
						@endforeach
					</ul>
				</div>						
					
			
				<div id="selectList{{$record->id}}" class="form-group hidden">
					@component('components.control-dropdown-menu', ['record' => $record, 'prefix' => $prefix, 
						'options' => $favoriteListsOptions,
						'selected_option' => $tag->id,
						'field_name' => 'type_flag',
						'prompt_div' => true,
						'select_class' => 'form-control form-control-sm',
						'onchange' => "$('#selectList" . $record->id . "').hide();",
					])@endcomponent
				</div>
					
				<div>{{$record->definition}}</div>
				</td>
				
				<td class="icon mr-3">
					<a href='' onclick="unheartDefinition(event, {{$record->id}}, '#removeStatus'); $('#row{{$record->id}}').hide();">
						<span id="heart" class="glyphCustom-md glyphicon glyphicon-remove"></span>
					</a>
				</td>
				
			</tr>
		@endforeach
		</tbody>
	</table>

</div>
@endsection
