<div class="dropdown {{isset($margin) ? $margin : ''}}" >
	<a class="" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true"	href="" onclick="" tablindex="-1">
		<div class="glyphicon {{$glyphicon}}"></div>
	</a>
	<ul class="small-thin-text dropdown-menu dropdown-menu-right">
		<li><a id="a0" class="dropdown-item" href="{{$href}}" onclick="{{isset($onclick) ? $onclick : ''}}">{{$prompt}}</a></li>
	</ul>
</div>
