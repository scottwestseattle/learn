@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<div class="page-nav-buttons">
		<a class="btn btn-success btn-md" role="button" href="/{{$prefix}}/">@LANG('content.Back to Course List')
		<span class="glyphicon glyphicon-button-back-to"></span>
		</a>
	</div>
	
	<h3 name="title" class="">{{$record->title }}
		@if ($isAdmin)
			@if (!$record->isFinished())
				<a class="btn {{($wip=$record->getWipStatus())['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$wip['text']}}</a>
			@endif
			@if (!$record->isPublished())
				<a class="btn {{($release=$record->getReleaseStatus())['btn']}} btn-xs" role="button" href="/{{$prefix}}/publish/{{$record->id}}">{{$release['text']}}</a>
			@endif
		@endif
	</h3>

	<p>{{$record->description }}</p>
	
	<a href="/lessons/view/{{$firstId}}">
		<button type="button" style="text-align:center; font-size: 1.3em; color:white;" class="btn btn-info btn-lesson-index" {{$disabled}}>@LANG('content.Start at the beginning')</button>	
	</a>
	
	<h1>@LANG('content.Lessons') ({{count($records)}})
	@if ($isAdmin)
		<span><a href="/lessons/admin/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-admin"></span></a></span>
		<span><a href="/lessons/add/{{$record->id}}"><span class="glyphCustom glyphicon glyphicon-add"></span></a></span>
	@endif	
	</h1>
	
<div class="accordion" id="accordionExample">
@foreach($records as $record)

@if (count($records) == 1)

@foreach($record as $rec)
	<a href="/lessons/view/{{$rec->id}}">
	<button style="" type="button" class="btn btn-outline-info btn-lesson-index link-dark">
		<table>
			<tr>
				<td>
					<div style="font-size:1em; color:purple; padding-right:5px;">{{$rec->section_number}}.&nbsp;{{$rec->title}}</div>
					<span style="font-size:.9em">{{$rec->description}}</span>	
				</td>
			</tr>
		</table>
	</button>
	</a>
@endforeach

@else

  <div class="card">
    <div style=""  class="card-header" id="headingOne">
      <h3 style=""  class="mb-0">
        <button style="text-decoration:none; text-align:left;" class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{{$record[0]->id}}" aria-expanded="true" aria-controls="collapseOne">
			@LANG('content.Lesson')&nbsp;{{$record[0]->lesson_number}}:&nbsp;{{$record[0]->title}}
		</button>
      </h3>
    </div>

	<div id="collapse{{$record[0]->id}}" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
		<div style="" class="card-body">
		@foreach($record as $rec)
			<a href="/lessons/view/{{$rec->id}}">
			<button style="" type="button" class="btn btn-outline-info btn-lesson-index link-dark">
				<table>
					<tr>
						<td>
							<div style="font-size:1em; color:purple; padding-right:5px;">{{$rec->section_number}}.&nbsp;{{$rec->title}}</div>
							<span style="font-size:.9em">{{$rec->description}}</span>	
						</td>
					</tr>
				</table>
			</button>
			</a>
		@endforeach
		</div>
    </div>
  </div>
  
@endif

@endforeach

</div>
	
@if (false)
	<a href="/lessons/view/{{$record[0]->id}}">
		<button style="" type="button" class="btn btn-outline-info btn-lesson-index link-dark">
			<table>
				<tr>
					<td>
						<div style="font-size:1.3em; color:purple; padding-right:5px;">Chapter {{$record[0]->lesson_number}}:&nbsp;{{$record[0]->title}}</div>
						<span style="font-size:.9em">{{$record[0]->description}}</span>	
					</td>
				</tr>
			</table>
		</button>
	</a>
@endif

</div>
@endsection
