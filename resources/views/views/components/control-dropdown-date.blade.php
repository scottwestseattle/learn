@if (isset($div))
<div class="form-group">
@endif

	@if (isset($prompt))
	<label for="{{$field_name}}">{{$prompt}}</label>
	@endif
	
	<a href='#' onclick="event.preventDefault(); javascript:changeDate(-1, 'year', 'month', 'day')";>
		<span id="" class="glyphCustom glyphicon glyphicon-minus-sign"></span>
	</a>						

	<select name="month" id="month">
			<option value="0">(month)</option>
		@foreach ($months as $key => $value)
			@if (isset($filter['selected_month']) && $key == $filter['selected_month'])
				<option value="{{$key}}" selected>{{$value}}</option>
			@else
				<option value="{{$key}}">{{$value}}</option>
			@endif
		@endforeach
	</select>
	
	@if (isset($days))
	<select name="day" id="day">
			<option value="0">(day)</option>
		@foreach ($days as $key => $value)
			@if (isset($filter['selected_day']) && $key == $filter['selected_day'])
				<option value="{{$key}}" selected>{{$value}}</option>
			@else
				<option value="{{$key}}">{{$value}}</option>
			@endif
		@endforeach
	</select>
	@endif

	<select name="year" id="year">
			<option value="0">(year)</option>
		@foreach ($years as $key => $value)
			@if (isset($filter['selected_year']) && $key == $filter['selected_year'])
				<option value="{{$key}}" selected>{{$value}}</option>
			@else
				<option value="{{$key}}">{{$value}}</option>
			@endif
		@endforeach
	</select>

	<a href='#' onclick="event.preventDefault(); javascript:changeDate(1, 'year', 'month', 'day')";>
		<span id="" class="glyphCustom glyphicon glyphicon-plus-sign"></span>
	</a>						
	<a href='#' onclick="event.preventDefault(); javascript:changeDate(0, 'year', 'month', 'day')";>
		<span id="" class="glyphCustom glyphicon glyphicon-remove"></span>
	</a>						
	<a href='#' onclick="event.preventDefault(); javascript:changeDate(99, 'year', 'month', 'day', true)";>
		<span id="" class="glyphCustom glyphicon glyphicon-calendar"></span>
	</a>						

@if (isset($div))	
</div>	
@endif


