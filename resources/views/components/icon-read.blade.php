@if (!isset($nodiv) || !$nodiv)
<div class="mr-2 float-left">
@endif

@if (isset($href))
	<a href='{{$href}}'>
@else
	<a href="" onclick='{{$onclick}}'>
@endif
		<span style="font-size:20px;" class="glyphCustom glyphicon glyphicon-volume-up"></span>
	</a>

@if (!isset($nodiv) || !$nodiv)
</div>
@endif
