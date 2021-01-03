@extends('layouts.app')

@section('content')

<!-------------------------------------------------------->
<!-- Add misc data needed by the JS during runtime -->
<!-------------------------------------------------------->
<div class="data-misc"
	data-count="1"
	data-touchpath=""
	data-max="1"
	data-language={{$languageCodes['short']}}
	data-language-long={{$languageCodes['long']}}
	data-type="1"
	data-contenttype="frontpage"
	data-contentid="1"
	data-isadmin="0"
	data-userid="0"
	data-readlocation=0
></div>

<!-------------------------------------------------------->
<!-- Add the body lines to read -->
<!-------------------------------------------------------->
<div class="data-slides"
    data-title="No title"
    data-number="1"
    data-description="No text to read"
    data-id="0"
    data-seconds="10"
    data-between="2"
    data-countdown="1"
>
</div>

<!--------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------->
<!-- Front Page -->
<!--------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------->

@if (Auth::check() && count($lesson) > 0)
    <!-- No logo or subscribe for signed-in user -->
    <!-- div style="height:5px;"></div -->
@else
@endif

<div class="container page-normal mt-1 bg-none">

<!--------------------------------------------------------------------------------------->
<!-- The record form -->
<!--------------------------------------------------------------------------------------->
<div class="text-center mt-4 p-1" style="">

	<form method="POST" action="/words/create-snippet">
        <h3 class="mt-0 pt-0">Speak Clearer</h3>
		<div class="">
		    <div style="xmin-height: 300px; ">
            <textarea
                id="textEdit"
                name="textEdit"
                class="form-control"
                placeholder="Enter or paste practice text here"
                rows="7"
            >{{$snippet->description}}</textarea>
            </div>

            @if (false)
            <div id="textShow" style="display:none; xmin-height: 200px; font-size:1.5em;">
                Show text here.
                <div id="languages" class="mt-1" style="display:default; font-size:10px;">
                    <select onchange="changeVoice();" name="select" id="select"></select>
                </div>
            </div>
            @endif

        </div>
        <div>
        @component('components.control-dropdown-language', [
            'record' => $snippet,
			'options' => $snippetLanguages,
			'selected_option' => $snippet->language_flag,
			'field_name' => 'language_flag',
			'select_class' => 'mt-1 mr-2',
		])@endcomponent
            <select onchange="changeVoice();" name="selectVoice" id="selectVoice"></select>
            <button type="submit" class="btn btn-primary btn-xs">Save</button>
            <a href="" onclick="event.preventDefault(); $('#textEdit').val(''); $('#textEdit').focus();" class="ml-1">Clear<a/>
            <a href="" onclick="copySnippet(event)" class="ml-2">Copy<a/>
            <!-- a href="" onclick="pasteSnippet(event)" class="ml-2">Paste<a/ -->
        </div>

		{{csrf_field()}}
    </form>

    <section class="main-controls">
        <canvas class="visualizer" height="60px"></canvas>
        <div id="buttons">
            <button id="buttonRecord" class="record" onclick="event.preventDefault(); startRecording()">Record</button>
            <button id="buttonPlay" class="play" onclick="event.preventDefault(); playRecording()">Play</button>
            <button id="buttonRead" class="" onClick="event.preventDefault(); readPage($('#textEdit').val())">Robot</button>
        </div>
    </section>

    <section class="sound-clips">
    </section>

</div>

<!--------------------------------------------------------------------------------------->
<!-- SNIPPETS -->
<!--------------------------------------------------------------------------------------->
@if (isset($snippets) && count($snippets) > 0)
    <h3 class="mt-2">@LANG('content.Latest Practice Text') <span style="font-size:.8em;">({{count($snippets)}})</span></h3>
    <div class="text-center mt-2" style="">
        <div style="display: inline-block; width:100%">
            <table style="width:100%;">
            <?php $count = 0; ?>
            @foreach($snippets as $record)

            <tr class="drop-box-ghost-small" style="vertical-align:middle;">
                <td style="color:default; text-align:left; padding:5px 10px;">
                    <table>
                    <tbody>
                        <tr>
                            <td style="padding-bottom:5px; font-size: 14px; font-weight:normal;">
                                <a href="/{{$record->id}}">{{App\Tools::trunc($record->description, 200)}}</a>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:.8em; font-weight:100;">
                                <div class="float-left mr-3">
                                    <img width="25" src="/img/flags/{{App\Tools::getSpeechLanguageShort($record->language_flag)}}.png" />
                                </div>
                                <div class="float-left" style="margin-top:2px;">
                                    <div class=""><a href="/entries/stats/{{$record->id}}">{{str_word_count($record->description)}} @LANG('content.words')</a></div>

                                    @if (false && App\User::isAdmin())
                                        <div style="margin-right:15px; float:left;">
                                            @component('components.control-button-publish', ['record' => $record, 'btnStyle' => 'btn-xxs', 'prefix' => 'entries', 'showPublic' => true])@endcomponent
                                        </div>
                                    @endif
                                </div>
                                <div style="float:left;">
                                    @if (App\User::isAdmin())
                                    <div style="margin-right:5px; float:left;"><a href='/words/edit/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-edit"></span></a></div>
                                    <div style="margin-right:0px; float:left;"><a href='/words/delete/{{$record->id}}'><span class="glyphCustom glyphCustom-lt glyphicon glyphicon-trash"></span></a></div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    </table>
                </td>
            </tr>

            <tr style="" class=""><td colspan="2"><div style="height:15px;">&nbsp;</div></td></tr>

            @endforeach
            </table>
            <div class="mb-4"><a class="btn btn-sm btn-success" role="button" href="/articles">@LANG('content.Show All')</a></div>
        </div>
    </div>
@endif

<!--------------------------------------------------------------------------------------->
<!-- ARTICLES -->
<!--------------------------------------------------------------------------------------->
@if (App\Tools::siteUses(ID_FEATURE_ARTICLES))
    <h3 class="mt-2">@LANG('content.Latest Articles')</h3>
    <div class="text-center mt-2" style="">
        <div style="display: inline-block; width:100%">
            <table style="width:100%;">
            <?php $count = 0; ?>
            @foreach($articles as $record)

            <tr class="drop-box-ghost-small" style="vertical-align:middle;">
                <td style="color:default; text-align:left; padding:5px 10px;">
                    <table>
                    <tbody>
                        <tr><td style="padding-bottom:5px; font-size: 14px; font-weight:normal;"><a href="/entries/{{$record->permalink}}">{{$record->title}}</a></td></tr>
                        <tr>
                            <td style="font-size:.8em; font-weight:100;">
                                <div class="float-left mr-3">
                                    <img width="25" src="/img/flags/{{App\Tools::getSpeechLanguageShort($record->language_flag)}}.png" />
                                </div>
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
                            </td>
                        </tr>
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
@if (App\Tools::siteUses(ID_FEATURE_PREFOOTER))
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
@endif

@endsection

<script>

function saveSnippet(event)
{
    event.preventDefault();
}

function copySnippet(event)
{
    event.preventDefault();

    var txtarea = document.getElementById('textEdit');
    var start = txtarea.selectionStart;
    var finish = txtarea.selectionEnd;
    if (start != finish) // doesn't work
    {
        // already selected, use the current selection
        //console.log(start);
        //console.log(finish);
        txtarea.select(); // just select it all for now
    }
    else
    {
        txtarea.select();
    }

    // copy the selection
    var succeed;
    try {
        succeed = document.execCommand("copy");
        //console.log('text copied: ' + succeed);
    } catch(e) {
        succeed = false;
		//console.log('error copying text');
	}
}

function pasteSnippet(event)
{
    event.preventDefault();

    $('#textEdit').focus();
    document.execCommand("paste");
}

function toggleTextView()
{
    if ($('#textShow').is(':visible'))
    {
        setEdit();
    }
    else
    {
        setShow();
    }

}

function setEdit()
{
    return;

    console.log('setEdit');
    $('#buttonEdit').text('Show');
    $('#textEdit').show();
    $('#textShow').hide();
}

function setShow()
{
    return;

    console.log('setShow');
    $('#textShow').html($('#textEdit').val())
    $('#buttonEdit').text('Edit');
    $('#textEdit').hide();
    $('#textShow').show();
}

</script>
