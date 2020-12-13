@extends('layouts.app')

@section('content')

<div class="container page-normal">

	<h1>@LANG('ui.Tags')
		<a class="btn btn-info btn-xs" role="button" href="/tags/add">
			@LANG('ui.Add')<span class="glyphicon glyphicon-plus-sign ml-1"></span>
		</a>
	</h1>

@if (isset($records))

    <div class="card-deck">
    @foreach($records as $record)
	<div class="col-sm-12 col-lg-6 col-xl-4"><!-- xl = 3 cols, lg = 2 cols, sm = 1 col -->	
		<div class="mb-3 mr-0">
			<div class="card-body drop-box-ghost">
				<h5 class="card-title">
					@if ($record->type_flag == TAG_TYPE_DEFINITION_FAVORITE)
						<a href="/definitions/list/{{$record->id}}">{{$record->name}}</a>
						@if (false)	@component('components.badge', ['text' => $record->wc])@endcomponent @endif
					@else
						{{$record->name}}
					@endif
				</h5>
				<div class="medium-thin-text">
					@if (isset($record->user_id))
						<div class="middle mr-2">User: {{$record->user_id}}</div>
					@else
						<div class="">No Owner</div>
					@endif
					<div class="mt-2">
						<a class="btn btn-info btn-xs {{$record->getTypeButtonColor()}}" role="button" href="/tags/edit/{{$record->id}}">
							@LANG('ui.Type') {{$record->getTypeFlagName()}}
						</a>
						<a href='/{{$prefix}}/edit/{{$record->id}}'><span class="ml-3 glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
						<a href='/{{$prefix}}/confirmdelete/{{$record->id}}'><span class="glyphCustom-sm glyphicon glyphicon-delete"></span></a>
					</div>
				</div>
			</div>
		</div>
	</div>
    @endforeach
    </div>
@endif

</div>

@endsection
