@extends('layouts.app')

@section('content')

<div class="page-size container">

	@component('menu-submenu-sites', ['record' => $record])@endcomponent
               
	<h3 name="title" class="">{{$record->site_name }}</h3>

	<p>{{$record->site_url }}</p>	

	<p>{{$record->site_title }}</p>
	
</div>
@endsection
