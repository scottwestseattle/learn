@extends('layouts.app')

@section('content')

<!--------------------------------------------------------------------------------------->
<!-- Front Page -->
<!--------------------------------------------------------------------------------------->

	<!-- Main jumbotron for a primary marketing message or call to action -->
	<div class="fpheader">
		<div class="container text-center">
		  <h1 class="">Learn English Online With Live Instructors</h1>
		  <p>Learn and practice English from the comfort of your home.  One-on-one or small group classes for more personal attention.  We teach online so our costs are kept low and these savings are passed on to our students.</p>
		  <p><a class="btn btn-primary btn-lg" href="/signup" role="button">Learn more &raquo;</a></p>
		</div>
	</div>

    <!-- START THE FEATURETTES -->

	<div class="container marketing">	
		<div class="row featurette">
			<div class="col-md-7">
				<h2 class="featurette-heading">Live Teachers</h2>
				<p class="lead">
					For learning a language, there is no substitute for speaking with a live instructor.  
					Our teachers help you master grammar and pronunciation with an emphasis on real world conversation and constant feedback. 
					This is the only way to really become fluent and learn the intricacies of a new language.
				</p>
			</div>
			<div class="col-md-5">
				<img src="/img/image1.png" width="100%" style="max-width: 350px;" />
			</div>
		</div>
	</div>
	
    <hr class="featurette-divider">

	<div class="container marketing">	
		<div class="row featurette">
			<div class="col-md-7 order-md-2">
				<h2 class="featurette-heading">Fast Learning</h2>
				<p class="lead">Our system is optmized for you to learn as fast as possible and to retain as much information as possible.</p>
			</div>
			<div class="col-md-5 order-md-1">
				<img src="/img/image2.png" width="100%" style="max-width: 350px;" />
			</div>
		</div>
	</div>
	
    <hr class="featurette-divider">

	<div class="container marketing">	
		<div class="row featurette">
		  <div class="col-md-7">
			<h2 class="featurette-heading">Superior Curriculum</h2>
			<p class="lead">	
				We constantly update our course content to make sure that it provides the best possible learning experience for your time.  
				Our lessons are creative, engaging and designed to maximize retention of the content.  
				We want you to learn as fast as possible and benefit from our program.		
			</p>
		  </div>
		  <div class="col-md-5">
				<img src="/img/image3.png" width="100%" style="max-width: 350px;" />
		  </div>
		</div>
	</div>
	
    <div style="padding:50px;"><!-- SPACER ONLY --></div>

    <!-- /END THE FEATURETTES -->

	<!-- PRE-FOOTER SECTION -->
	<div class="grassy-green">
		<div class="container marketing text-center">
			<div style="padding:50px;">	
				<img src="/img/globe.png" width="100%" style="max-width: 350px;" /> 			
				<h2 class="featurette-heading">Mission Statement</h2>
				<p class="lead">
						Our company believes that speaking English is increasingly important in the modern world.  
						It's better and easier to learn and master this skill early and nobody should be denied the opportunity to do so.</p>
			</div>
		</div>
	</div>	

@endsection
	