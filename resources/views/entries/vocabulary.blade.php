@extends('layouts.app')

@section('content')

<div style="xbackground-color:green;" class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin, 'isIndex' => true])@endcomponent
	
	<div class="page-nav-buttons">
		<a class="btn btn-success btn-sm btn-nav-top" role="button" href="{{$referrerUrl}}">
		    @LANG('content.Back')<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	    <a class="btn btn-primary btn-sm btn-nav-top" role="button" href="/entries/review-vocabulary/{{$record->id}}">
            @LANG('ui.Review')<span class="glyphicon glyphicon-eye-open ml-1"></span>
	    </a>
	    <a class="btn btn-primary btn-sm btn-nav-top" role="button" href="/entries/review-vocabulary/{{$record->id}}/{{REVIEWTYPE_FLASHCARDS}}">
            @LANG('content.Flashcards')<span class="glyphicon glyphicon-flash ml-1"></span>
	    </a>
	</div>

	<div><a href="/entries/{{$record->permalink}}">{{$record->title}}</a></div>
	<h3 class="mt-2">Vocabulary List ({{count($record->definitions)}})
		@component('components.control-delete-glyph', ['margin' => 'ml-3', 'glyphicon' => 'glyphCustom-md glyphicon-trash', 'href' => '/entries/remove-vocabulary-list/' . $record->id . '', 'prompt' => 'Remove all words from list'])@endcomponent
	</h3>

	<table class="table xtable-responsive table-striped" style="width:100%;" >
		<tbody>
		@foreach($record->definitions as $r)
			<tr>
            @if (App\User::isSuperAdmin())
				<td class="icon"><a href="/definitions/edit/{{$r->id}}"><span class="glyphCustom-sm glyphicon glyphicon-edit"></span></a></td>
            @endif
				<td>
				    <a href="/definitions/view/{{$r->id}}">{{$r->title}}</a>
				    <div>{{substr($r->translation_en, 0, 200)}}</div>
				</td>
				<td class="icon">
					<a href='/entries/remove-definition-user/{{$record->id}}/{{$r->id}}'>
						<span class="glyphCustom-sm glyphicon glyphicon-remove small-thin-text mediumgray"></span>
					</a>
				</td>			
			</tr>
		@endforeach
		</tbody>
	</table>

</div>
@endsection
