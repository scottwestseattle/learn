@if (isset($options) && count($options) > 0)

	@if (isset($label))

		@if (isset($label_div))
			<div>
		@endif
		
		<label for="{{$field_name}}">{{$label}}</label>
			
		@if (isset($label_div))
			</div>
		@endif
		
	@endif

	<select id="{{$field_name}}" name="{{$field_name}}">

	@if (isset($empty))
		<option value="0">({{$empty}})</option>	
	@endif

	@foreach ($options as $key => $value)
		@if (isset($selected_option) && $key == $selected_option)
			<option value="{{$key}}" selected>{{$value}}</option>
		@else
			<option value="{{$key}}">{{$value}}</option>
		@endif
	@endforeach
		
	</select>

@endif
