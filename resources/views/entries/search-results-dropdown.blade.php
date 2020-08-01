<!-- search results -->
<style>
a:hover {
	text-decoration:none
}

.popup {
    position: relative;
    display: inline-block;
}
	
/* The actual popup (appears on top) */
.popup .popuptext 
{
	width:500px;
    background-color: white;
    color: #fff;
    position: absolute;
    z-index: 1;
	top: 14px;
    left: 100%;
    margin-left: -380px;
	padding: 10px;
	border: solid 1px lightblue;
    border-radius: 6px;	
}

</style>
	
<div class='popup'>
	<span class='popuptext'>
	
	@foreach($entries as $entry)
		<div style="padding: 3px 0px;" class="">
			<a style="" href="/entries/gen/{{$entry->id}}">{{$entry->title}}</a>
		</div>
	@endforeach
	
	@if (count($entries) === 25)
		<div style="padding: 3px 0px;" class="">
			<span style="color: gray;" >{{ '(Only showing first 25 results)' }}</span>
		</div>
	@elseif (count($entries) === 0)
		<span style="color: gray;" >{{ '(not found)' }}</span>
	@endif
	
	</span>
</div>
