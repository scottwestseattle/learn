@section('content')
<courses>
@foreach($records as $record)
	<course>
		<course_name>{{$record->title}}</course_name>
		<course_description>{{$record->description}}</course_description>
		<course_id>{{$record->id}}</course_id>
		@foreach($record->lessons as $lesson)
			@if ($lesson->section_number == 1 && $lesson->deleted_flag == 0)<lesson>
				<lesson_name>{{$lesson->title_chapter}}</lesson_name>
				<lesson_id>{{$lesson->id}}</lesson_id>
				<lesson_number>{{$lesson->lesson_number}}</lesson_number>
			</lesson>
			@endif
		@endforeach
	</course>
@endforeach
</courses>
