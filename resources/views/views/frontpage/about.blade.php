@extends('layouts.app')

@section('content')

<!--------------------------------------------------------------------------------------->
<!-- About Page -->
<!--------------------------------------------------------------------------------------->

<div class="container page-normal">
	
	<h1>@LANG('ui.About')</h1>
		
	<span style="font-size:1.5em;">@LANG('fp.about-p1')&nbsp;<a href="/contact">@LANG('ui.by clicking here')</a>.</span>
	
	<p style="margin-top: 20px;">{{$domainName}} {{$version}}<p>
	
@if (false)
	<div class="text-center" style="margin-top:50px;">
		<img style="" src="/img/logo.png" title="@LANG('content.About Page Image')" />
	</div>	
@endif
		
</div>

@endsection
