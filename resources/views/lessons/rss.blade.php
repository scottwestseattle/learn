@section('content')
<xml>
@foreach($records as $record)
	<record>
		<name>{{$record->title}}</name>
		<runSeconds>{{$record->getTime()['runSeconds']}}</runSeconds>
		<breakSeconds>{{$record->getTime()['breakSeconds']}}</breakSeconds>
		<description>{{$record->description}}</description>
		<imageName>{{$record->main_photo}}</imageName>
	</record>
@endforeach
</xml>
