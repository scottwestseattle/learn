@extends('layouts.app')

@section('content')

<div class="container page-normal">

	@component($prefix . '.menu-submenu', ['record' => $record, 'prefix' => $prefix])@endcomponent

	<h1>@LANG('ui.Edit') @LANG('content.' . $title)</h1>

	<form method="POST" id="form-edit" action="/{{$prefix}}/update/{{$record->id}}">

		<label for="title" class="control-label">@LANG('gen.Title'):</label>
		<input type="text" name="title" class="form-control" value="{{$record->title}}"></input>

		<div style="margin-top: 20px;" class="form-group">

			<label for="lesson_number" class="control-label">@LANG('content.Lesson'):</label>
			<select name="lesson_number" id="lesson_number" class="">
				@foreach ($record->getLessonNumbers() as $key => $value)
					<option value="{{$key}}" {{ $key == $record->lesson_number ? 'selected' : ''}}>{{$value}}</option>
				@endforeach
			</select>
			&nbsp;
			<label for="section_number" class="control-label">@LANG('content.Section'):</label>
			<select name="section_number" id="section_number" class="">
				@foreach ($record->getSectionNumbers() as $key => $value)
					<option value="{{$key}}" {{ $key == $record->section_number ? 'selected' : ''}}>{{$value}}</option>
				@endforeach
			</select>
			&nbsp;
			<input type="checkbox" name="renumber_flag" id="renumber_flag" class="" />
			<label for="renumber_flag" class="checkbox-big-label">@LANG('content.Renumber All')</label>

		</div>

		<div class="form-group">
			<input type="hidden" name="format_flag" value="{{$record->format_flag}}" />
			<input type="checkbox" name="autoformat" id="autoformat" {{$record->format_flag == LESSON_FORMAT_AUTO ? 'checked' : ''}} />
			<label for="autoformat" class="checkbox-big-label">@LANG('content.Auto-format')</label>
		</div>

		<label for="description" class="control-label">@LANG('gen.Description'):</label>
		<textarea name="description" class="form-control">{{$record->description}}</textarea>

		<div style="margin-bottom: 20px;" class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Update')</button>
		</div>

@if (false)
		<a href='/{{$prefix}}/edit2/{{$record->id}}'><span class="glyphCustom glyphicon glyphicon-pencil"></span></a>
		<label for="text" class="control-label">@LANG('gen.Text'):</label>

		<a href='#' onclick="event.preventDefault(); tinymce.init({selector:'#text'}); "><span class="glyphCustom glyphicon glyphicon-zoom-out"></span></a>
		<a href='#' onclick='event.preventDefault(); tinymce.remove(); '><span class="glyphCustom glyphicon glyphicon-zoom-in"></span></a>
		<a href='#' onclick='event.preventDefault(); refreshView();'><span class="glyphCustom glyphicon glyphicon-refresh"></span></a>
			<button onclick="event.preventDefault(); saveAndStay();" name="update" class="btn btn-success">Save and Stay</button>
@endif

		<div id ="rich" style="clear:both;display:default;">
			<textarea style="height:500px" name="text" id="text" class="form-control big-text">{{$record->text}}</textarea>
		</div>

		<div id ="preview" style="display:none;">
		</div>

		<div class="submit-button">
			<button type="submit" name="update" class="btn btn-primary">@LANG('ui.Update')</button>
		</div>

		{{ csrf_field() }}

	</form>

</div>

@endsection

<script src="https://cdn.tiny.cloud/1/vft1qzd41vab0e8lnjogftv02qxpfv11j340z7i97o2poj6n/tinymce/5/tinymce.min.js"></script>

<script>

//tinymce.init({selector:'#text'});

tinymce.init({
	selector:'#text',
	plugins: 'table code',
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
