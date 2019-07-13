@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component('translations.menu-submenu', ['prefix' => 'Translations'])@endcomponent

	<h1>@LANG('content.Translations')</h1>
				
		<?php $cnt = 0; $recs = $records['en']; ?>
		
		<table>
			<tr><th></th><th>@LANG('ui.Key')</th><th>@LANG('ui.English')</th><th>@LANG('ui.Spanish')</th><th>@LANG('ui.Chinese')</th></tr>
			@foreach($recs as $key => $value)
			<tr>
				<td>{{++$cnt}}.&nbsp;</td>
				<td>{{$key}}</td>
				<td>{{$records['en'][$key]}}</td>
				<td>{{$records['es'][$key]}}</td>
				<td>{{$records['zh'][$key]}}</td>
			<tr>
			@endforeach
		</table>		

</div>

@stop
