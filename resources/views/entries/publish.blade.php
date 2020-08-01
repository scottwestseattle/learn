@extends('layouts.app')

@section('content')

<div class="page-size container">

	@component($record->type_flag === ENTRY_TYPE_TOUR ? 'menu-submenu-tours' : 'entries.menu-submenu', ['record' => $record])@endcomponent
	
	<h1>@LANG('ui.Publish')</h1>

	<form method="POST" action="/entries/publishupdate/{{ $record->id }}">

		@if (isset($referer))<input type="hidden" name="referer" value="{{$referer}}">@endif

		<div class="form-group">
			<h3 name="title" class="">{{$record->title }}</h3>
		</div>

		<div class="form-group">
			<input type="checkbox" name="finished_flag" id="finished_flag" class="" value="{{$record->finished_flag }}" {{ ($record->finished_flag) ? 'checked' : '' }} />
			<label for="finished_flag" class="checkbox-big-label">@LANG('ui.Finished')</label>
		</div>
				
		<div class="form-group">
			<input type="checkbox" name="published_flag" id="published_flag" class="" value="{{$record->published_flag }}" {{ ($record->published_flag) ? 'checked' : '' }} />
			<label for="published_flag" class="checkbox-big-label">@LANG('ui.Published')</label>
		</div>

		<div class="form-group">
			<input type="checkbox" name="approved_flag" id="approved_flag" class="" value="{{$record->approved_flag }}" {{ ($record->approved_flag) ? 'checked' : '' }} />
			<label for="approved_flag" class="checkbox-big-label">@LANG('ui.Approved')</label>
		</div>
		
		@if ($record->type_flag === ENTRY_TYPE_BLOG_ENTRY && !isset($record->parent_id))
			<div class="form-group">
				<label for="distance">Parent ID:</label>
				<input type="text" name="parent_id" class="form-control"  />
			</div>
		@else
			<input type="hidden" name="parent_id" value="{{$record->parent_id}}">			
		@endif

		<div class="form-group">
			<label for="distance">@LANG('ui.View Count'):</label>
			<input type="text" name="view_count" class="form-control" value="{{ $record->view_count }}"  />		
		</div>		
		
		<div class="form-group">
			<button type="submit" class="btn btn-primary">@LANG('ui.Update')</button>
		</div>
	{{ csrf_field() }}
	</form>
</div>
@endsection
