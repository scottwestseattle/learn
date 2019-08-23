<?php 
	$id = isset($target) ? $target : 0; 
	$tinymce = isset($tinymce);
	$flat = isset($flat);
?>

@if ($flat)
	
	<div class="data-accent-chars flat-content">
		<button onclick="event.preventDefault(); insertChar('á', '{{$id}}', '{{$tinymce}}')">á</button>
		<button onclick="event.preventDefault(); insertChar('é', '{{$id}}', '{{$tinymce}}')">é</button>
		<button onclick="event.preventDefault(); insertChar('í', '{{$id}}', '{{$tinymce}}')">í</button>
		<button onclick="event.preventDefault(); insertChar('ó', '{{$id}}', '{{$tinymce}}')">ó</button>
		<button onclick="event.preventDefault(); insertChar('ú', '{{$id}}', '{{$tinymce}}')">ú</button>
		<button onclick="event.preventDefault(); insertChar('ü', '{{$id}}', '{{$tinymce}}')">ü</button>
		<button onclick="event.preventDefault(); insertChar('ñ', '{{$id}}', '{{$tinymce}}')">ñ</button>
		<button onclick="event.preventDefault(); insertChar('Á', '{{$id}}', '{{$tinymce}}')">Á</button>
		<button onclick="event.preventDefault(); insertChar('É', '{{$id}}', '{{$tinymce}}')">É</button>
		<button onclick="event.preventDefault(); insertChar('Í', '{{$id}}', '{{$tinymce}}')">Í</button>
		<button onclick="event.preventDefault(); insertChar('Ó', '{{$id}}', '{{$tinymce}}')">Ó</button>
		<button onclick="event.preventDefault(); insertChar('Ú', '{{$id}}', '{{$tinymce}}')">Ú</button>
		<button onclick="event.preventDefault(); insertChar('Ü', '{{$id}}', '{{$tinymce}}')">Ü</button>
		<button onclick="event.preventDefault(); insertChar('Ñ', '{{$id}}', '{{$tinymce}}')">Ñ</button>	
		<button onclick="event.preventDefault(); insertChar('&rarr;', '{{$id}}', '{{$tinymce}}')">&rarr;</button>	
		
	</div>	
	
@else
	
<div class="data-accent-chars">
	<div id="accent-chars-esp" class="dropdown">
		<span class="dropdown-trigger">Ñ</span>
		<div class="dropdown-content accent-buttons">
			<button onclick="event.preventDefault(); insertChar('á', '{{$id}}', '{{$tinymce}}')">á</button>
			<button onclick="event.preventDefault(); insertChar('é', '{{$id}}', '{{$tinymce}}')">é</button>
			<button onclick="event.preventDefault(); insertChar('í', '{{$id}}', '{{$tinymce}}')">í</button>
			<button onclick="event.preventDefault(); insertChar('ó', '{{$id}}', '{{$tinymce}}')">ó</button>
			<button onclick="event.preventDefault(); insertChar('ú', '{{$id}}', '{{$tinymce}}')">ú</button>
			<button onclick="event.preventDefault(); insertChar('ü', '{{$id}}', '{{$tinymce}}')">ü</button>
			<button onclick="event.preventDefault(); insertChar('ñ', '{{$id}}', '{{$tinymce}}')">ñ</button>
			<button onclick="event.preventDefault(); insertChar('Á', '{{$id}}', '{{$tinymce}}')">Á</button>
			<button onclick="event.preventDefault(); insertChar('É', '{{$id}}', '{{$tinymce}}')">É</button>
			<button onclick="event.preventDefault(); insertChar('Í', '{{$id}}', '{{$tinymce}}')">Í</button>
			<button onclick="event.preventDefault(); insertChar('Ó', '{{$id}}', '{{$tinymce}}')">Ó</button>
			<button onclick="event.preventDefault(); insertChar('Ú', '{{$id}}', '{{$tinymce}}')">Ú</button>
			<button onclick="event.preventDefault(); insertChar('Ü', '{{$id}}', '{{$tinymce}}')">Ü</button>
			<button onclick="event.preventDefault(); insertChar('Ñ', '{{$id}}', '{{$tinymce}}')">Ñ</button>		
		</div>
	</div>
</div>

@endif
