@extends('layouts.app')

@section('content')

@if (Auth::user()->user_type >= 100)

@component('menu-submenu', ['data' => $data])
	@component('menu-submenu-user')@endcomponent
@endcomponent

@endif

<div class="page-size container">

	<h1>Edit User</h1>

	<form method="POST" action="/users/update/{{ $user->id }}">

		<div class="form-group">
			<input type="text" name="name" class="form-control" value="{{$user->name }}"></input>
		</div>
		
		<div class="form-group">
			<input type="text" name="email" class="form-control" value="{{$user->email }}"></input>
		</div>

		@if (Auth::user()->user_type >= 100)
			<div class="form-group">
				<input type="text" name="user_type" class="form-control" value="{{$user->user_type}}"></input>
			</div>
			<div class="form-group">
				<input type="text" name="password" class="form-control" value="{{$user->password}}"></input>
			</div>
		<div class="form-group">
			<input type="checkbox" name="blocked_flag" id="blocked_flag" class="" value="{{$user->blocked_flag }}" {{ ($user->blocked_flag) ? 'checked' : '' }} />
			<label for="blocked_flag" class="checkbox-big-label">Blocked</label>
		</div>
		@endif

		<div class="form-group">
			<button type="submit" name="update" class="btn btn-primary">Update</button>
		</div>
	{{ csrf_field() }}
	</form>

</div>

@stop
