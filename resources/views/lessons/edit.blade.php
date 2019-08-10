@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix, 'isAdmin' => $isAdmin])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">
	
		<ul class="nav nav-tabs">
			<li class="nav-item">
				<a id="nav-link-text" class="nav-link active" href="#" onclick="setTab(event, 1);">@LANG('gen.Text')</a>
			</li>
			<li class="nav-item">
				<a id="nav-link-title" class="nav-link" href="#" onclick="setTab(event, 2);">@LANG('ui.Title')</a>
			</li>
			<li class="nav-item">
				<button type="submit" name="update" style="margin-top:5px; margin-left:5px;" class="btn btn-sm btn-primary">@LANG('ui.Save')</button>
			</li>
			<li class="nav-item">
				<a class="nav-link" href='/{{$prefix}}/edit2/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-pencil"></span></a>
			</li>
			<li class="nav-item">
				@component('components.data-accent-chars-esp')@endcomponent																		
			</li>
		</ul>	
	
		<div style="display:none;" id="tab-title">

			<div class="form-group">
				<label for="parent_id" class="control-label">@LANG('content.Course'):</label>
				<select name="parent_id" class="form-control">
					<option value="0">(@LANG('content.Select Course'))</option>
					@foreach ($courses as $course)
						<option value="{{$course->id}}" {{ $course->id == $record->parent_id ? 'selected' : ''}}>{{$course->title}}</option>
					@endforeach
				</select>
			</div>
		
			<div class="form-group">
				<label for="title" class="control-label">@LANG('gen.Title'):</label>
				<input type="text" id="title" name="title" class="form-control" value="{{$record->title}}"></input>	
			</div>
		
			<!--------------------------------------------------------------------------->
			<!-- Lesson Type Dropdown -->
			<!--------------------------------------------------------------------------->
			
			<div class="form-group">
			@component('components.control-dropdown-menu', ['record' => $record, 'prefix' => $prefix, 
				'isAdmin' => $isAdmin, 
				'prompt' => 'Lesson Type: ',
				'empty' => 'Select Lesson Type',
				'options' => App\Lesson::getLessonTypes(),
				'selected_option' => $record->type_flag,
				'field_name' => 'type_flag',
				'prompt_div' => true,
				'select_class' => 'form-control',
			])@endcomponent
			</div>
		
			<!--------------------------------------------------------------------------->
			<!-- Chapter / Section -->
			<!--------------------------------------------------------------------------->
		
			<div class="form-group">
				<label for="lesson_number" class="control-label">@LANG('content.Chapter'):</label>
				<input type="number"  min="1" max="1000" step="1" name="lesson_number" class="form-control form-control-100" value="{{$record->lesson_number}}" />
			</div>	

			<div class="form-group">		
				<label for="section_number" class="control-label">@LANG('content.Section'):</label>
				<input type="number"  min="0" max="1000" step="1" name="section_number" class="form-control form-control-100" value="{{$record->section_number}}" />
			</div>	

			<div class="form-group">
				<input type="checkbox" name="renumber_flag" id="renumber_flag" class="" />
				<label for="renumber_flag" class="checkbox-big-label">@LANG('content.Renumber All')</label>
				&nbsp;
				<input type="hidden" name="format_flag" value="{{$record->format_flag}}" />
				<input type="checkbox" name="autoformat" id="autoformat" {{$record->format_flag == LESSON_FORMAT_AUTO ? 'checked' : ''}} />
				<label for="autoformat" class="checkbox-big-label">@LANG('content.Auto-format')</label>
			</div>

			<!--------------------------------------------------------------------------->
			<!-- Options -->
			<!--------------------------------------------------------------------------->
			<label for="options" class="control-label">@LANG('content.Options'):</label>
			<input type="text" name="options" class="form-control" value="{{$record->options}}" />
			
			<!--------------------------------------------------------------------------->
			<!-- Description -->
			<!--------------------------------------------------------------------------->
			<label for="description" class="control-label">@LANG('gen.Description'):</label>
			<textarea name="description" class="form-control">{{$record->description}}</textarea>

		</div>
		
		<div id="tab-text">
		
			<div id ="rich" style="clear:both;display:default;">
				<textarea style="height:500px" name="text" id="text" class="form-control big-text">{{$record->text}}</textarea>
			</div>
		
		</div>
				
		@if (false)		
			<button onclick="event.preventDefault(); saveAndStay();" name="update" class="btn btn-success">Save and Stay</button>
		@endif				
		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Save')</button>
		</div>

		{{ csrf_field() }}

		<div id ="preview" style="display:none;">
		</div>
		
	</form>
	
</div>

@endsection

<script src="https://cdn.tiny.cloud/1/vft1qzd41vab0e8lnjogftv02qxpfv11j340z7i97o2poj6n/tinymce/5/tinymce.min.js"></script>

<script>

function setTab(event, tab)
{
	event.preventDefault();
	
	if (tab == 1)
	{
		$('#tab-text').show(); 
		$('#tab-title').hide();
		
		$('#nav-link-text').addClass('active'); 
		$('#nav-link-title').removeClass('active');
	}
	else
	{
		$('#tab-text').hide(); 
		$('#tab-title').show();
		
		$('#nav-link-text').removeClass('active'); 
		$('#nav-link-title').addClass('active');
	}
	
}

tinymce.init({
	selector:'#text',
	plugins: 'table code lists',
	toolbar: 'code formatselect | bold italic forecolor backcolor permanentpen formatpainter | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol'
});

function saveAndStay()
{
	alert('Not implemented yet');
	return;
	
	//$.post('/lesson/update/{{$record->id}}', $('#form-edit').serialize());	
	
$("#form-edit").submit(function(e) {

    e.preventDefault(); // avoid to execute the actual submit of the form.

    var form = $(this);
    var url = form.attr('action');

    $.ajax({
           type: "POST",
           url: url,
           data: form.serialize(), // serializes the form's elements.
           success: function(data)
           {
               alert(data); // show response from the php script.
           }
         });


});
	
}

function refreshView()
{
	if ($("#preview").is(":visible"))
	{
		$("#preview").hide();
		$("#rich").show();
		
		tinymce.init({selector:'#text'});		
	}
	else
	{
		tinymce.remove();
		
		$("#preview").html(
			$("#text").text()
		);
		
		$("#preview").show();
		$("#rich").hide();
	}
}

</script>
