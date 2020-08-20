@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent
	
	<h1>@LANG('content.Dictionary') ({{count($records)}})</h1>
	
	@component('components.data-badge-list', ['edit' => '/definitions/view/', 'records' => $records, 'title' => null, 'fontStyle' => 'font-size: .9em;'])@endcomponent																			

</div>

@endsection
