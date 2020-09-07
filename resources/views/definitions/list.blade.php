@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $tag, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-top" role="button" href="/vocab-lists/">
		    @LANG('content.Back to Lists')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	    <a class="btn btn-primary btn-sm btn-nav-top" role="button" href="/{{$prefix}}/review/{{$tag->id}}">
            @LANG('ui.Review')&nbsp;<span class="glyphicon glyphicon-eye-open"></span>
	    </a>
	</div>

	<h3 name="" class="" style="margin-bottom:10px;">{{$tag->name}}@component('components.badge', ['text' => count($records)])@endcomponent
	</h3>

	<table class="table xtable-responsive table-striped">
		<tbody>
		@foreach($records as $r)
			<tr>
				<td>
				    <a href="/definitions/view/{{$r->id}}">{{$r->title}}</a>
				    <div>{{substr($r->definition, 0, 200)}}</div>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

</div>
@endsection
