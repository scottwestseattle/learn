@extends('layouts.app')

@section('content')

<!--------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------->
<!-- Front Page -->
<!--------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------->

<!--------------------------------------------------------------------------------------->
<!-- Banner Photo -->
<!--------------------------------------------------------------------------------------->
<div><a href="/"><img src="/img/banners/{{$banner}}" style="width:100%;" /></a></div>

@if (Auth::check() && count($lesson) > 0)
    <!-- No logo or subscribe for signed-in user -->
    <!-- div style="height:5px;"></div -->
@else
    @if (isset($banner))
    <div class="bg-none">


        <!--------------------------------------------------------------------------------------->
        <!-- Logo and Subscribe Form-->
        <!--------------------------------------------------------------------------------------->
        <div class="fpBannerImage" style="background-color:#4993FD">
            <div class="container text-center pt-2 pb-2" >
    		    <img src="/img/logo-{{\App\Tools::getDomainName()}}.png" style="max-width:250px;"/>
                <form method="POST" action="/subscribe">
                    <div class="form-group text-center">
                        <div class="input-group mt-2">
                            <input name="email" id="email" type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                maxlength="50"
                                placeholder="@LANG('ui.Email Address')"
                                required
                            />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success" type="button">@LANG('ui.Subscribe')</button>
                            </div>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mt-2 white small-thin-text">@LANG('fp.Subscribe to mailing list')</div>

                    </div>
                    <div class="form-group">
                    </div>
                    {{ csrf_field() }}
                </form>
            </div>
        </div>
    </div>
    @else
        <!-- Main jumbotron for a primary marketing message or call to action -->
        <div class="app-color-primary fpBannerImage" style="min-height:300px;">
            <div class="container text-center" >
                @if (isset($jumboTitle))
                    <h1 class="text-lg">@LANG('fp.' . $jumboTitle)</h1>
                    <p>@LANG('fp.' . $jumboSlug)</p>
                @else
                    <h1 class="">@LANG('fp.Frontpage Header Title')</h1>
                    <p>@LANG('fp.Frontpage Header Body')</p>
                @endif
                @if (App\Tools::siteUses(LOG_MODEL_COURSES))
                    <p><a class="btn btn-primary btn-lg" href="/start" role="button">@LANG('fp.Start') &raquo;</a></p>
                @endif
            </div>
        </div>
    @endif
@endif

<div class="container page-normal mt-2 bg-none">

<!--------------------------------------------------------------------------------------->
<!-- Dictionary, Lists, and Books shortcuts widget -->
<!--------------------------------------------------------------------------------------->

    <div class="hidden-xs mb-3"></div>
    <div class="d-block d-md-none d-flex justify-content-center text-center bg-none p-0 mt-3">

        <div class="" style="width: 25%;">
            <a class="purple" href="/articles">
                <div class="glyphicon glyphicon-globe" style="font-size:35px;"></div>
                <div class="" style="font-size:10px;">@LANG('content.Articles')</div>
            </a>
        </div>

        <div class="" style="width: 25%;">
            <a class="purple" href="/books">
                <div class="glyphicon glyphicon-book" style="font-size:35px;"></div>
                <div class="" style="font-size:10px;">@LANG('content.Books')</div>
            </a>
        </div>

        <div class="" style="width: 25%;">
            <a class="purple" href="/definitions">
                <div class="glyphicon glyphicon-font" style="font-size:35px;"></div>
                <div class="" style="font-size:10px;">@LANG('content.Dictionary')</div>
            </a>
        </div>

        <div class="" style="width: 25%;">
            <a class="purple" href="/vocabulary">
                <div class="glyphicon glyphicon-th-list" style="font-size:35px;"></div>
                <div class="" style="font-size:10px;">@LANG('content.Lists')</div>
            </a>
        </div>

    </div>

<!--------------------------------------------------------------------------------------->
<!-- WORD AND PHRASE OF THE DAY -->
<!--------------------------------------------------------------------------------------->
@if (isset($wotd) || isset($potd))
	<div class="row row-course">
    @if (isset($wotd))
		<div class="col-sm-12 col-lg-6 col-course" style="">
            <div class="card card-wotd truncate mt-1" style="">
                <div class="card-header card-header-potd">
                    <div>@LANG('content.Word of the day')</div>
                    <div class="small-thin-text">@LANG('content.A new word to learn every day')</div>
                </div>
                <div class="card-body card-body-potd">
                    @if(isset($wotd))
                        <div><b>{{$wotd->title}}</b> - <i>{{$wotd->description}}</i></div>
                        <div class="large-thin-text">{{$wotd->examples}}</div>
                    @else
                        <div>@LANG('ui.Not Found')</div>
                    @endif
                </div>
            </div>
		</div>
    @endif

    @if (false && isset($potd))
		<div class="col-sm-12 col-lg-6 col-course" style="">
            <div class="card card-votd truncate mt-1" style="">
                <div class="card-header card-header-potd">
                    <div>@LANG('content.Verb of the day')</div>
                    <div class="small-thin-text">@LANG('content.Practice this phrase out loud')</div>
                </div>
                <div class="card-body card-body-potd">
                    <div><b>{{$wotd->title}}</b> - <i>{{$wotd->description}}</i></div>
                    <div class="large-thin-text">{{$wotd->examples}}</div>
                </div>
            </div>
		</div>
    @endif

    @if (isset($potd))
		<div class="col-sm-12 col-lg-6 col-course" style="">
            <div class="card card-potd truncate mt-1" style="">
                <div class="card-header card-header-potd">
                    <div>@LANG('content.Phrase of the day')</div>
                    <div class="small-thin-text">@LANG('content.Practice this phrase out loud')</div>
                </div>
                <div class="card-body card-body-potd">
                    <div class="xl-thin-text">{{$potd}}</div>
                </div>
            </div>
		</div>
    @endif

	</div>

@endif

<!--------------------------------------------------------------------------------------->
<!-- VOCAB LISTS (Logged in only) -->
<!--------------------------------------------------------------------------------------->
	@if (isset($vocabLists) && count($vocabLists) > 0)
		<h3>@LANG('content.Vocabulary') ({{count($vocabLists)}})</h3>
		<div class="row row-course">
			@foreach($vocabLists as $record)
			<div class="col-sm-4 col-course"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
				<div class="card card-vocab-list truncate">
				<a href="/vocab-lists/view/{{$record->id}}">
					<div class="card-header">{{$record->title}}</div>
					<div class="card-body"><p class="card-text">Word Count: {{$record->words->count()}}</p></div>
				</a>
				</div>
			</div>
			@endforeach
		</div>
	@endif
	<!-- END OF VOCAB LISTS -->

<!--------------------------------------------------------------------------------------->
<!-- COURSES (Logged in only) -->
<!--------------------------------------------------------------------------------------->
	@if (App\Tools::siteUses(LOG_MODEL_COURSES))
		@if (Auth::check())

		@if (isset($lesson['course']))
		<h3>@LANG('content.Courses in Progress')</h3>
		<div class="row row-course m-1">
				<div class="alert alert-primary" role="alert">
					<h3 class="alert-heading mt-0">{{$lesson['course']->title}}</h3>
					@if (isset($lesson['lesson']))
						<hr>
						<h4>@LANG('content.Chapter') {{$lesson['lesson']->getFullName()}}</h4>
						<p>@LANG('content.Last viewed on') {{$lesson['date']}}</p>
						<p><a class="btn btn-primary btn-lg" href="/lessons/view/{{$lesson['lesson']->id}}" role="button">@LANG('content.Continue Lesson') &raquo;</a></p>
					@endif
				</div>
		</div>
		@endif

		@else
		<h3>@LANG('content.Courses') ({{count($courses)}})</h3>
		<div class="row row-course">
			@foreach($courses as $record)
			<div class="col-sm-4 col-course"><!-- outer div needed for the columns and the padding, otherwise they won't center -->
				<div class="card card-course {{$record->getCardColor()}} truncate">
				<a href="/courses/view/{{$record->id}}">
					<div class="card-header">{{$record->title}}</div>
					<div class="card-body"><p class="card-text">{{$record->description}}</p></div>
				</a>
				</div>
			</div>
			@endforeach
		</div>
		@endif
	@endif
	<!-- END OF COURSES -->

<!--------------------------------------------------------------------------------------->
<!-- ARTICLES NEW SMALL -->
<!--------------------------------------------------------------------------------------->
@if (App\Tools::siteUses(LOG_MODEL_ARTICLES))
    <h3 class="mt-2">@LANG('content.Latest Articles')</h3>
    <div class="text-center mt-2" style="">
        <div style="display: inline-block; width:100%">
            <table style="width:100%;">
            <?php $count = 0; ?>
            @foreach($articles as $record)

            <tr class="drop-box-ghost-small" style="vertical-align:middle;">
                <td style="min-width:40px; font-size: 14px; padding:5px; color: white; background-color: #74b567; margin-bottom:10px;" >
                    <div style="margin:0; padding:0; line-height:100%;">
                        <div style="font-family:impact; font-size:1.7em; margin:10px 0 10px 0;">{{++$count}}</div>
                    </div>
                </td>
                <td style="color:default; text-align:left; padding:5px 10px;">
                    <table>
                    <tbody>
                        <tr><td style="padding-bottom:5px; font-size: 14px; font-weight:normal;"><a href="/entries/{{$record->permalink}}">{{$record->title}}</a></td></tr>
                        <tr><td style="font-size:.8em; font-weight:100;">
                            <div style="float:left;">
                                @component('components.icon-read', ['href' => "/entries/read/$record->id"])@endcomponent
                                <div style="margin-right:15px; float:left;">{{$record->view_count}} @LANG('content.views')</div>
                                <div style="margin-right:15px; margin-bottom:5px; float:left;"><a href="/entries/stats/{{$record->id}}">{{str_word_count($record->description)}} @LANG('content.words')</a></div>

                                @if (App\User::isAdmin())
                                    <div style="margin-right:15px; float:left;">
                                        @component('components.control-button-publish', ['record' => $record, 'btnStyle' => 'btn-xxs', 'prefix' => 'entries', 'showPublic' => true])@endcomponent
                                    </div>
                                @endif

                            </div>
                            <div style="float:left;">
                                @if (App\User::isAdmin())
                                <div style="margin-right:5px; float:left;"><a href='/entries/edit/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-edit"></span></a></div>
                                <div style="margin-right:0px; float:left;"><a href='/entries/confirmdelete/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-trash"></span></a></div>
                                @endif
                            </div>
                        </td></tr>
                    </tbody>
                    </table>
                </td>
            </tr>

            <tr style="" class=""><td colspan="2"><div style="height:15px;">&nbsp;</div></td></tr>

            @endforeach
            </table>
            <div class="mb-4"><a class="btn btn-sm btn-success" role="button" href="/articles">@LANG('content.Show All Articles')</a></div>
        </div>
    </div>
@endif

<!--------------------------------------------------------------------------------------->
<!-- WORD OF THE DAY (not used) -->
<!--------------------------------------------------------------------------------------->
    @if (false && isset($wod))
        <h3>@LANG('content.Word of the Day')</h3>
		<div style="max-width:600px;">
			<div class="card text-white bg-primary mb-3">
				<div class="card-header"><h4>{{$wod->title}}</h4></div>
				<div class="card-body">
					<p class="card-text">
						<p>{{$wod->description}}</p>
						@if (isset($wod->examples))
							@foreach($wod->examples as $example)
								<p><i>{{$example}}</i></p>
							@endforeach
						@endif
					</p>
				</div>
			</div>
		</div>
    @endif

<!--------------------------------------------------------------------------------------->
<!-- Podcasts -->
<!--------------------------------------------------------------------------------------->
@if ($siteLanguage == 'es-ES')
<div>
    <iframe frameBorder="0" height="482" scrolling="no" src="https://playlist.megaphone.fm/?p=HSW5050863615&light=true"
    width="100%">
    </iframe>
</div>
@endif

</div>

<!--------------------------------------------------------------------------------------->
<!-- BUY US A COFFEE BUTTON -->
<!--------------------------------------------------------------------------------------->
@if (isset($supportMessage))
<div class="text-center mb-4">
<script
    type="text/javascript"
    src="https://cdnjs.buymeacoffee.com/1.0.0/button.prod.min.js"
    data-name="bmc-button"
    data-slug="espdaily"
    data-color="#FFDD00"
    data-emoji=""
    data-font="Cookie"
    data-text="{{$supportMessage}}"
    data-outline-color="#000000"
    data-font-color="#000000"
    data-coffee-color="#ffffff" >
</script>
@endif
</div>
<!--------------------------------------------------------------------------------------->
<!-- PRE-FOOTER SECTION -->
<!--------------------------------------------------------------------------------------->
<div class="mars-sky">
	<div class="container marketing text-center">
		<div class="pb-4 pt-3">
			<img src="/img/image5.png" style="max-width: 200px;" />
			@if (isset($randomWord))
				@component('components.random-word', ['record' => $randomWord])@endcomponent
			@else
				<h2 class="section-heading mt-0 mb-4">@LANG('fp.Frontpage Subfooter Title')</h2>
				<h4 style="font-size: 20px; font-weight: 400;">@LANG('fp.Frontpage Subfooter Body')</h4>
			@endif
		</div>
	</div>
</div>

@endsection
