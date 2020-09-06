<div class="col-sm"><!-- need to split word list into multiple columns here -->
	<div class="table" style="font-size: 13px;">
		<table id="searchDefinitionsResultsTable" class="table-responsive table-striped table-condensed">
			<tbody>
				@foreach($records as $record)
				<tr>
					@if ($isAdmin)
						<td class="icon"><a href='/definitions/edit/{{$record->id}}'><span class="glyphCustom-md glyphicon glyphicon-edit"></span></a></td>
					@endif
					<?php $heart = (count($record->tags) > 0) ? 'heart' : 'heart-empty'; ?>
					<td class="hidden-xs">
						<a class="medium-thin-text" href="/definitions/view/{{$record->id}}">{{$record->title}}</a>
						<div class="middle ml-2"><a href='' onclick="heartDefinition(event, {{$record->id}}, '#heartStatus-{{$record->id}}')"><span id="a{{$record->id}}" class="glyphCustom-md glyphicon glyphicon-{{$heart}}"></span></a></div>
						<div id="heartStatus-{{$record->id}}" class="small-thin-text red"></div>
					</td>
					<td>
						<div class="large-text hidden-lg hidden-md hidden-sm">
							<a href="/definitions/view/{{$record->id}}">{{$record->title}}</a>
							
							<div class="middle ml-2"><a href='' onclick="heartDefinition(event, {{$record->id}}, '#heartStatus2-{{$record->id}}')"><span id="b{{$record->id}}" class="glyphCustom-md glyphicon glyphicon-{{$heart}}"></span></a></div>
							<div id="heartStatus2-{{$record->id}}" class="small-thin-text red"></div>							
						</div>
						
						@if (isset($record->definition))
							<div class="medium-thin-text mb-2">{!!nl2br($record->definition)!!}</div>
						@elseif (App\User::isSuperAdmin())
							<a href="/definitions/edit/{{$record->id}}" class="small-thin-text danger">add definition</a>
						@endif
																						
						<div class="teal"><i>{!!nl2br($record->examples)!!}</i></div>
						
						@if (isset($record->translation_en))
							<div class="mt-2 steelblue">English: {!!nl2br($record->translation_en)!!}</div>
						@elseif (App\User::isSuperAdmin())
							<a href="/definitions/edit/{{$record->id}}" class="small-thin-text danger">add translation</a>
						@endif
						
						<div class="small-thin-text mt-2">
							{{$record->view_count}} view{{$record->view_count !== 1 ? 's' : ''}}@if (isset($record->last_viewed_at))<span>,  last on {{App\Tools::timestamp2date($record->last_viewed_at)}}</span>@endif
						</div>
						
						@if (isset($record->conjugations))
							<div class="small-thin-text mt-2"><a href="" onclick="event.preventDefault(); $('#hide-{{$record->id}}').show(); $('#showconjugations-{{$record->id}}').show(); ajaxexec('/definitions/conjugationscomponent/{{$record->id}}', '#showconjugations-{{$record->id}}');">
								@if (App\Definition::fixConjugations($record))
									<a href="/definitions/edit/{{$record->id}}" class="small-thin-text danger">conjugations</a>
								@else
									<span>conjugations</span>
								@endif
							</a> <span style="display:none;" id="hide-{{$record->id}}"><a href="" onclick="event.preventDefault(); $('#hide-{{$record->id}}').hide(); $('#showconjugations-{{$record->id}}').hide();">(hide)</a></span></div>
							<div id="showconjugations-{{$record->id}}"></div>
						@endif
						
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