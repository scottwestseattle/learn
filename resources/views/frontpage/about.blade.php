@extends('layouts.app')

@section('content')

<!--------------------------------------------------------------------------------------->
<!-- About Page -->
<!--------------------------------------------------------------------------------------->

<div class="container page-normal">
	
	<h1>@LANG('ui.About')</h1>
		
	<span style="font-size:1.5em;">
		All Images, Photos, Graphics, and Content on this site ©2019 Learn Online. 
		All Rights Reserved. 
		Do not copy, archive or re-post without written permission from the author. 
		For more information, please contact info@learnonline.com.			
	</span>
	
	<p style="margin-top: 20px;">{{$domainName}} {{$version}}<p>
	
@if (false)
	<div class="text-center" style="margin-top:50px;">
		<img style="" src="/img/logo.png" title="@LANG('content.About Page Image')" />
	</div>	
@endif
		
</div>

@endsection
