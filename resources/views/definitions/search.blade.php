@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
	
	<h1>@LANG('content.Dictionary') (<span id="searchDefinitionsResultsCount">{{count($records)}}</span>)
		<span style="" class="small-thin-text mb-2">
			<a href="/definitions/search/1">{{'A-Z'}}</a>&nbsp;&nbsp;
			<a href="/definitions/search/2">{{'Z-A'}}</a>&nbsp;&nbsp;
			<a href="/definitions/search/3">{{'newest'}}</a>&nbsp;&nbsp;
			<a href="/definitions/search/4">{{'recent'}}</a>&nbsp;&nbsp;
			@if ($isAdmin)
				<a href="/definitions/search/5">{{'missing translation'}}</a>&nbsp;&nbsp;
				<a href="/definitions/search/6">{{'missing definition'}}</a>&nbsp;&nbsp;
				<a href="/definitions/search/7">{{'missing conjugation'}}</a>
			@endif
		</span>
	</h1>

	<div class="mb-3">
		<form method="POST" action="/definitions/create">
			<input type="text" id="title" name="title" value="{{$search}}" class="form-control" autocomplete="off" onfocus="setFocus($(this));" onkeyup="searchDefinitions('#title', '#searchResults');" autofocus />		
		</form>
	</div>

	<div id="searchResults" class="row">

		@component($prefix . '.component-search-results', ['prefix' => $prefix, 'isAdmin' => $isAdmin, 'records' => $records])@endcomponent

	</div>
</div>

@endsection
