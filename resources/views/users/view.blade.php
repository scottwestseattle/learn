@extends('layouts.app')

@section('content')

@component('menu-submenu', ['data' => $data])
	@component('menu-submenu-user')@endcomponent
@endcomponent

<div class="page-size container">
               
<form method="POST" action="/users/{{ $user->id }}">

	<div class="form-group">
		<h1 name="name" class="">{{$user->name }}</h1>
	</div>

	<table style="font-size:1.2em;">
		<tr><td>ID:</td><td><b>{{$user->id }}</b></td></tr>
		<tr><td>Email:</td><td><b>{{$user->email}}</b></td></tr>
		<tr><td>User Type:</td><td><b>{{$user->user_type}}</b></td></tr>
		<tr><td>Created:</td><td><b>{{$user->created_at}}</b></td></tr>
		<tr><td>Last Update:&nbsp;&nbsp;</td><td><b>{{$user->updated_at}}</b></td></tr>
	</table>
	
{{ csrf_field() }}
</form>

</div>
@endsection
