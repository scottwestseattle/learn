<?php $id = isset($target) ? $target : 0; ?>
<div class="{{isset($visible) && $visible ? '' : 'hidden'}}">
	<div id="accent-chars-esp" class="dropdown">
		<span class="dropdown-trigger">Ñ</span>
		<div class="dropdown-content">
			<button onclick="insertChar('á', '{{$id}}')">á</button>
			<button onclick="insertChar('é', '{{$id}}')">é</button>
			<button onclick="insertChar('í', '{{$id}}')">í</button>
			<button onclick="insertChar('ó', '{{$id}}')">ó</button>
			<button onclick="insertChar('ú', '{{$id}}')">ú</button>
			<button onclick="insertChar('ü', '{{$id}}')">ü</button>
			<button onclick="insertChar('ñ', '{{$id}}')">ñ</button>
			<button onclick="insertChar('Á', '{{$id}}')">Á</button>
			<button onclick="insertChar('É', '{{$id}}')">É</button>
			<button onclick="insertChar('Í', '{{$id}}')">Í</button>
			<button onclick="insertChar('Ó', '{{$id}}')">Ó</button>
			<button onclick="insertChar('Ú', '{{$id}}')">Ú</button>
			<button onclick="insertChar('Ü', '{{$id}}')">Ü</button>
			<button onclick="insertChar('Ñ', '{{$id}}')">Ñ</button>		
		</div>
	</div>
</div>