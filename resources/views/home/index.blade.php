@extends('layouts.app')

@section('content')

<div class="container page-normal">
	<div class="text-center">
		<h3>@LANG('ui.You Are Not Authorized To Access This Page')</h3>
		<div style="margin:50px;"></div>
		<img src="/img/theme1/access-denied.png" />
	</div>
</div>

@endsection
