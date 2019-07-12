@extends('layouts.app')

@section('content')

<!--------------------------------------------------------------------------------------->
<!-- Sample Lesson -->
<!--------------------------------------------------------------------------------------->

<div class="container page-normal lesson">



<a class="btn btn-primary btn-sm" role="button" href="/sample">@LANG('ui.Prev') </a>
<a class="btn btn-primary btn-sm" role="button" href="/sample">@LANG('ui.Next') </a>

<h2>Basic English Lesson 1.1</h2>
<h1>The Verb "To Be"</h1>
<p>The verb <em><strong>to be</strong></em> describes the identity, qualities, or condition of a person or object.&nbsp; Use the following to form the present tense of the verb <em><strong>to be</strong></em>:</p>
<table cellspacing="15">
<tbody>
<tr>
<td>I</td>
<td>am</td>
<td>happy.</td>
</tr>
<tr>
<td>You</td>
<td>are</td>
<td>smart.</td>
</tr>
<tr>
<td>He</td>
<td>is</td>
<td>short.</td>
</tr>
<tr>
<td>She</td>
<td>is</td>
<td>busy.</td>
</tr>
<tr>
<td>It</td>
<td>is</td>
<td>tall.</td>
</tr>
<tr>
<td>We</td>
<td>are</td>
<td>tired.</td>
</tr>
<tr>
<td>They</td>
<td>are</td>
<td>here.</td>
</tr>
</tbody>
</table>
<h3>Vocabulary</h3>
<p>Before you begin, use your dictionary to find the meaning of the new vocabulary words needed for this lesson.&nbsp; Write the words in your language in the spaces provided:</p>
<div class="vocab-list">
<div class="vocab-prompt">flashlight</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">yellow</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">cold</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">hungry</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">tall</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">aunt</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">furniture</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">old</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">young</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">short</div>
<div><input id="flashlight" type="text" /></div>
<div class="vocab-prompt">skinny</div>
<div><input id="flashlight" type="text" /></div>
<div style="clear:both; margin-top: 20px;">
 <a class="btn btn-primary btn-lg" role="button" href="/signup">@LANG('content.Save Vocabulary') </a>
 </div>
</div>









</div>			

@endsection
