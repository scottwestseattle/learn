@section('content')
<xml>
@foreach($records as $record)
	<record>
		<field name="name">{{$record->title}}</field>
		<field name="runSeconds">{{$record->getTime()['runSeconds']}}</field>
		<field name="breakSeconds">{{$record->getTime()['breakSeconds']}}</field>
		<field name="description">{{$record->description}}</field>
	</record>
@endforeach
</xml>
