<!-------------------------------------------------------->
<!-- Add misc data needed by the JS during runtime -->
<!-------------------------------------------------------->
<div class="data-misc"
	data-count="1"
	data-touchpath=""
	data-max="1"
	data-language={{$options['languageCodes']['short']}}
	data-language-long={{$options['languageCodes']['long']}}
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
<!-- The record form -->
<!--------------------------------------------------------------------------------------->
<div class="record-form text-center mt-4 p-1">

	<form method="POST" action="/words/create-snippet">
        <h3 class="practice-title mt-0 pt-0">@LANG('fp.Practice Speaking')</h3>
		<div class="">
		    <div style="xmin-height: 300px; ">
            <textarea
                id="textEdit"
                name="textEdit"
                class="form-control textarea-control"
                placeholder="Enter or paste practice text here"
                rows="7"
            >{{$options['snippet']->description}}</textarea>
            </div>
        </div>

        <span class='mini-menu'>
        @component('components.control-dropdown-language', [
            'record' => $options['snippet'],
			'options' => $options['snippetLanguages'],
			'selected_option' => $options['snippet']->language_flag,
			'field_name' => 'language_flag',
			'select_class' => 'mt-1 mr-2',
		])@endcomponent
            <select onchange="changeVoice();" name="selectVoice" id="selectVoice"></select>
            <a href="" onclick="event.preventDefault(); $('#textEdit').val(''); $('#textEdit').focus();" class="ml-1">@LANG('ui.Clear')<a/>
            <a href="" onclick="copySnippet(event)" class="ml-1">@LANG('ui.Copy')<a/>
        </span>

        @if (!App\Tools::isMobile())
    	    @component('components.control-accent-chars-esp', ['label_class' => 'white', 'visible' => true, 'target' => 'textEdit'])@endcomponent
        @endif

		{{csrf_field()}}
    </form>

    <section class="main-controls">
        <canvas id="feedback" class="visualizer hidden" height="40px"></canvas>
        <div id="record-buttons">
            <button id="buttonRecord" class="btn-primary" onclick="event.preventDefault(); startRecording()">@LANG('ui.Record')</button>
            <button id="buttonRead" class="bg-purple" onClick="event.preventDefault(); readPage($('#textEdit').val())">@LANG('ui.Robot')</button>
            <button id="buttonSave" class="btn-success">@LANG('ui.Save')</button>
        </div>
    </section>

    <section class="sound-clips">
    </section>

</div>

<!--------------------------------------------------------------------------------------->
<!-- SNIPPETS -->
<!--------------------------------------------------------------------------------------->
@if (isset($options['records']) && count($options['records']) > 0)
    <h3 class="mt-2">@LANG('content.Practice Text') <span style="font-size:.8em;">({{count($options['records'])}})</span></h3>
    <div class="text-center mt-2" style="">
        <div style="display: inline-block; width:100%">
            <table style="width:100%;">
            <?php $count = 0; ?>
            @foreach($options['records'] as $record)

            <tr class="drop-box-ghost-small" style="vertical-align:middle;">
                <td style="color:default; text-align:left; padding:5px 10px;">
                    <table>
                    <tbody>
                        <tr>
                            <td style="padding-bottom:5px; font-size: 14px; font-weight:normal;">
                                <a href="" onclick="copyToReader(event, '#{{$record->id}}', '#textEdit', '.record-form');">{{App\Tools::trunc($record->description, 200)}}</a>
                                <input id="{{$record->id}}" type="hidden" value="{{$record->description}}" />
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:.8em; font-weight:100;">
                                <div class="float-left mr-3">
                                    <img width="25" src="/img/flags/{{App\Tools::getSpeechLanguageShort($record->language_flag)}}.png" />
                                </div>
                                <div class="float-left" style="margin-top:2px;">
                                    <div class=""><a href="/entries/stats/{{$record->id}}">{{str_word_count($record->description)}} @LANG('content.words')</a></div>
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
            @if ($options['showAllButton'])
                <div class="mb-4"><a class="btn btn-sm btn-success" role="button" href="/words/practice">@LANG('ui.Show All')</a></div>
            @endif
        </div>
    </div>
@endif
