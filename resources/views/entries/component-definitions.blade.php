@if (isset($records))

<div>
	<div style="text-align:left;" class="row">
		<!-- repeat this block for each column -->
		<div class="col-sm"><!-- need to split word list into multiple columns here -->
			<div class="table" style="font-size: 13px;">
				<table class="table-responsive table-striped table-condensed">
					<tbody>
						@foreach($records as $record)
						<tr>
							<td class="hidden-xs">
								<a class="medium-thin-text" href="/definitions/view/{{$record->id}}">
									{{$record->title}}
								</a>
							</td>
							<td>
								<div class="large-text hidden-lg hidden-md hidden-sm">
									<a href="/definitions/view/{{$record->id}}">{{$record->title}}</a>
								</div>
								
								@if (isset($record->translation_en))
									<div class="mt-2 steelblue">{!!nl2br($record->translation_en)!!}</div>
								@else
									<a href="/definitions/edit/{{$record->id}}" class="small-thin-text danger">add translation</a>
								@endif
								
								@if (false && isset($record->conjugations))
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
							
							<td><a onclick="event.preventDefault(); removeDefinitionUser('/entries/remove-definition-user/{{$entryId}}/{{$record->id}}');" href=''><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a></td>
							
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
		<!-- end of repeat block -->
	</div>
</div>
@endif