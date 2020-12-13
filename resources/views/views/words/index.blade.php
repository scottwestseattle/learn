@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix, 'parent_id' => $parent_id, 'isAdmin' => $isAdmin])@endcomponent
	
	<h1>@LANG('content.Vocabulary') ({{count($records)}})</h1>
	
	@component('components.data-badge-list', ['edit' => '/words/view/', 'records' => $records, 'title' => null, 'fontStyle' => 'font-size: .9em;'])@endcomponent																			

</div>

@endsection
