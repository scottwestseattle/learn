@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component('entries.menu-submenu', ['record' => $record])@endcomponent
	
	<h1>@LANG('ui.Publish')</h1>

	<form method="POST" action="/entries/publishupdate/{{ $record->id }}">

		@if (isset($referer))<input type="hidden" name="referer" value="{{$referer}}">@endif

		<div class="form-group">
			<h4 name="title" class="">{{$record->title }}</h4>
		</div>

		<div class="form-group">
			@component('components.control-dropdown-menu', ['record' => $record, 'prompt' => 'Work Status:', 'field_name' => 'wip_flag', 'options' => $wip_flags, 'selected_option' => $record->wip_flag, 'select_class' => 'form-control'])@endcomponent
		</div>
		<div class="form-group">
			@component('components.control-dropdown-menu', ['record' => $record, 'prompt' => 'Release Status:', 'field_name' => 'release_flag', 'options' => $release_flags, 'selected_option' => $record->release_flag, 'select_class' => 'form-control'])@endcomponent
		</div>

		@if (false)
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
