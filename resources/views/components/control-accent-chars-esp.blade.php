<?php $id = isset($target) ? $target : 0; ?>
<div class="{{isset($visible) && $visible ? '' : 'hidden'}}">
	<div id="accent-chars-esp" class="dropdown">
		<span class="dropdown-trigger">Ñ</span>
		<div class="dropdown-content">
			<button onclick="event.preventDefault(); insertChar('á', '{{$id}}')">á</button>
			<button onclick="event.preventDefault(); insertChar('é', '{{$id}}')">é</button>
			<button onclick="event.preventDefault(); insertChar('í', '{{$id}}')">í</button>
			<button onclick="event.preventDefault(); insertChar('ó', '{{$id}}')">ó</button>
			<button onclick="event.preventDefault(); insertChar('ú', '{{$id}}')">ú</button>
			<button onclick="event.preventDefault(); insertChar('ü', '{{$id}}')">ü</button>
			<button onclick="event.preventDefault(); insertChar('ñ', '{{$id}}')">ñ</button>
			<button onclick="event.preventDefault(); insertChar('Á', '{{$id}}')">Á</button>
			<button onclick="event.preventDefault(); insertChar('É', '{{$id}}')">É</button>
			<button onclick="event.preventDefault(); insertChar('Í', '{{$id}}')">Í</button>
			<button onclick="event.preventDefault(); insertChar('Ó', '{{$id}}')">Ó</button>
			<button onclick="event.preventDefault(); insertChar('Ú', '{{$id}}')">Ú</button>
			<button onclick="event.preventDefault(); insertChar('Ü', '{{$id}}')">Ü</button>
			<button onclick="event.preventDefault(); insertChar('Ñ', '{{$id}}')">Ñ</button>		
		</div>
	</div>
</div>