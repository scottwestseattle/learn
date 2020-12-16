@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">

		<div class="form-group">
			<label for="title" class="control-label">@LANG('gen.Title'):</label>
			<input type="text" name="title" class="form-control" value="{{$record->title}}"></input>
		</div>

        <div class="form-group">
            <label for="type_flag">List Type:&nbsp;</label>
            <select name="type_flag" id="type_flag">
                <option value="0" {{$record->type_flag == 0 ? 'selected' : ''}}>Default</option>
                <option value="{{VOCABLISTTYPE_POTD}}" {{$record->type_flag == VOCABLISTTYPE_POTD ? 'selected' : ''}}>Phrases of the day</option>
                <option value="{{VOCABLISTTYPE_WOTD}}" {{$record->type_flag == VOCABLISTTYPE_WOTD ? 'selected' : ''}}>Words of the day</option>
            </select>
        </div>

		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
		</div>

		{{ csrf_field() }}

	</form>

</div>

@endsection

