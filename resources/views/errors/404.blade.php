@extends('layouts.app')

@section('content')

<!-- Fonts -->
@if (!isset($localhost))
	<link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
@endif

<!-- Styles -->
<style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 36px;
                padding: 20px;
            }
</style>

<div class="page-size container">
	<div class="flex-center position-ref full-height">
		<div class="content center">
			<div style="margin-top:40px; font-size:3em; color:#636b6f; font-family:'Raleway', sans-serif;font-weight: 100;" class="title">
                    The page you are looking for could not be found.
            </div>
        </div>
	</div>
@endsection
