@if (isset($records))

<div>
	<div style="text-align:left;">
		<!-- repeat this block for each column -->
			<div class="table" style="font-size: 13px; background-color:white;">
				<table class="table-striped" style="width:100%;">
					<tbody>
						@foreach($records as $record)
						<tr>
							<td class="hidden-xs" style="xpadding: 2px 5px;">
								<a class="medium-thin-text" href="/definitions/view/{{$record->id}}" target="_blank">{{$record->title}}</a>
							</td>
							<td style="padding-top: 2px;">
								<div class="medium-thin-text hidden-lg hidden-md hidden-sm">
									<a href="/definitions/view/{{$record->id}}" target="_blank">{{$record->title}}</a>
								</div>
								
								@if (isset($record->translation_en))
									<div class="mt-2 steelblue">{!!nl2br($record->translation_en)!!}</div>
								@elseif (App\User::isSuperAdmin())
									<a href="/definitions/edit/{{$record->id}}" target="_blank" class="small-thin-text danger">add translation</a>
								@endif
																
							</td>
							
							<td>
							<a onclick="event.preventDefault(); removeDefinitionUser('/entries/remove-definition-user/{{$entryId}}/{{$record->id}}');" href=''>
							<span class="glyphCustom-sm glyphicon glyphicon-remove small-thin-text mediumgray"></span></a>
							</td>
							
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		<!-- end of repeat block -->
	</div>
</div>
@endif