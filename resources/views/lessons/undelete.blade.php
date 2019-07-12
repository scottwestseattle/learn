@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['prefix' => $prefix])@endcomponent
	
	<h1>@LANG('ui.Undelete')</h1>

	<h3>List Deleted Records with option to undelete</h3>
</div>
@endsection
