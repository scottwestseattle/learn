<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Lang;
use Auth;
use App\User;
use App\Event;
use App\Tools;
use App\Definition;
use App\VocabList;

define('PREFIX', 'definitions');
define('LOG_MODEL', 'definitions');
define('TITLE', 'Definition');
define('TITLE_LC', 'definition');
define('TITLE_PLURAL', 'Definitions');
define('REDIRECT', '/definitions');
define('REDIRECT_ADMIN', '/definitions/admin');

class DefinitionController extends Controller
{
	public function __construct ()
	{
        $this->middleware('is_admin')->except(['index', 'view', 'find', 'conjugate', 'verbforms', 'wordexists']);

		$this->prefix = PREFIX;
		$this->title = TITLE;
		$this->titlePlural = TITLE_PLURAL;

		parent::__construct();
	}

    public function index(Request $request)
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = Definition::getIndex();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . $this->title . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.index', $this->getViewData([
			'records' => $records,
		]));
    }
	
    public function conjugate(Request $request, $text)
    {
		$records = Definition::conjugate($text);
		$status = null;
		if (isset($records))
		{
			$status = $records['status'];
			$forms = $records['forms'];
			$records = $records['records'];
		}
		
		return view(PREFIX . '.conjugate', $this->getViewData([
			'records' => $records,
			'verb' => $text,
			'status' => $status,
		]));
    }	
	
    public function verbforms(Request $request, $text)
    {
		$forms = null;
		$records = Definition::conjugate($text);
		if (isset($records))
		{
			$forms = $records['formsPretty'];
		}
		
		return $forms;
    }	

    public function wordexists(Request $request, $text)
    {
		$rc = '';
		
		$record = Definition::get($text);
		if (isset($record))
		{ 
			$rc = "<a href='/definitions/view/" . $record->id . "'>" . $record->title . ": already in dictionary (show)</a>&nbsp;<a href='/definitions/edit/" . $record->id . "'>(edit)</a>";
		}
			
		return $rc;
    }	

    public function indexUser(Request $request)
    {
		$parent_id = null;

		$records = Word::getWodIndex();

		return view(PREFIX . '.index', $this->getViewData([
			'records' => $records,
			'lesson_id' => $parent_id,
			'lesson' => isset($parent_id),
		]));
    }

    public function indexowner(Request $request, $parent_id = null)
    {
		$records = []; // make this countable so view will always work
		$view = PREFIX . '.indexowner';

		try
		{
			if (isset($parent_id))
			{
				$parent_id = intval($parent_id);
				$records = Word::getIndex($parent_id);
			}
			else
			{
				$records = Word::getWodIndex(['orderBy' => 'id desc']);
				$view = PREFIX . '.indexadmin';
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . $this->title . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg . ' for parent ' . $parent_id, $parent_id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view($view, $this->getViewData([
			'records' => $records,
			'lesson_id' => $parent_id,
		]));
    }

    public function admin(Request $request, $parent_id = null)
    {
		$parent_id = intval($parent_id);

		$records = []; // make this countable so view will always work

		try
		{
			$records = Word::getIndex($parent_id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . strtolower($this->title) . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.admin', $this->getViewData([
			'records' => $records,
		]));
    }

    public function add()
    {
		return view(PREFIX . '.add', $this->getViewData([
			]));
	}

    public function create(Request $request)
    {
		$title = trim($request->title);
		$record = Definition::get($title);
		if (isset($record))
		{
			Tools::flash('danger', 'record already exists');
			return redirect('/' . PREFIX . '/edit/' . $record->id);
		}
		
		$record = new Definition();

		$record->user_id 		= Auth::id();
		$record->language_id 	= LANGUAGE_SPANISH;
		$record->title 			= $request->title;
		$record->forms 			= Definition::formatForms($request->forms);
		$record->definition		= $request->definition;
		$record->translation_en	= $request->translation_en;
		$record->translation_es	= $request->translation_es;
		$record->examples		= $request->examples;
		$record->permalink		= Tools::createPermalink($request->title);

		try
		{
			$record->save();

			Event::logAdd(LOG_MODEL, $record->title, $record->definition, $record->id);

			$msg = 'New record has been added';
			Tools::flash('success', $msg);
			
			return redirect('/' . PREFIX . '/view/' . $record->id);
		}
		catch (\Exception $e)
		{
			$msg = isset($msg) ? $msg : 'Error adding new ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);

			return back();
		}
    }

    public function permalink(Request $request, $permalink)
    {
		$permalink = trim($permalink);

		$record = null;

		try
		{
			$record = Word::select()
				->where('permalink', $permalink)
				->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Record Not Found: ' . $permalink;

			Tools::flash('danger', $msg);
			Event::logError(LOG_MODEL, LOG_ACTION_PERMALINK, /* title = */ $msg);

			return back();
		}

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			], LOG_MODEL, LOG_PAGE_PERMALINK));
	}

	public function edit(Definition $definition)
    {
		$record = $definition;
		$forms = null;
		
		if (isset($record->forms))
		{
			// make it prettier?
		}
		else
		{
			$records = Definition::conjugate($definition->title);
			if (isset($records))
			{
				$forms = $records['formsPretty'];
				$record->forms = $forms;
			}	
		}		

		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $record,
			]));
	}

    public function update(Request $request, Definition $definition)
    {
		$record = $definition;
		$isDirty = false;
		$changes = '';
		$parent = null;

		$forms = Definition::formatForms($request->forms);

		$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);
		$record->forms = Tools::copyDirty($record->forms, $forms, $isDirty, $changes);
		$record->definition = Tools::copyDirty($record->definition, $request->definition, $isDirty, $changes);
		$record->translation_en = Tools::copyDirty($record->translation_en, $request->translation_en, $isDirty, $changes);
		$record->translation_es = Tools::copyDirty($record->translation_es, $request->translation_es, $isDirty, $changes);
		$record->examples = Tools::copyDirty($record->examples, $request->examples, $isDirty, $changes);

		if ($isDirty)
		{
			try
			{
				$record->save();

				Event::logEdit(LOG_MODEL, $record->title, $record->id, $changes);
				Tools::flash('success', 'Record has been updated');
			}
			catch (\Exception $e)
			{
				$msg = "Error updating record";
				Event::logException(LOG_MODEL, LOG_ACTION_EDIT, $msg . ': title = ' . $record->title, null, $e->getMessage());
				Tools::flash('danger', $msg);
			}
		}
		else
		{
			Tools::flash('success', 'No changes were made');
		}

        $returnPath = '/' . PREFIX . '/view/' . $record->id;

		return redirect($returnPath);
	}

    public function updateajax(Request $request, Word $word)
    {
		$record = $word;
		$rc = ''; // no change
		$duplicate = false;

		if (!Auth::check())
		{
			$rc = Lang::get('content.not logged in');
			return $rc;
		}

		$isDirty = false;
		$changes = '';
		$fieldCnt = isset($request->fieldCnt) ? intval($request->fieldCnt) : 0;

		// this is called from one view where only the description is being updated and another view where both are being updated.
		if ($fieldCnt == 2)
		{
			$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);	// checked so we don't wipe it out where its not being used

			if ($isDirty && Word::exists($request->title)) // title changing, check for dupes
				$duplicate = true;

			$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);

			if ($isDirty)
			{
				try
				{
					$record->save();
					$rc = $duplicate
						? Lang::get('content.duplicate saved')
						: Lang::get('content.saved');

					Event::logEdit(LOG_MODEL, $record->title, $record->id, $changes);
				}
				catch (\Exception $e)
				{
					$msg = "Error updating record";
					Event::logException(LOG_MODEL, LOG_ACTION_EDIT, $msg . ': title = ' . $record->title, null, $e->getMessage());
					$rc = Lang::get('content.error');
				}
			}
		}
		else
		{
			// user is adding a definition, need to make a copy for this user
			if ($request->type_flag == WORDTYPE_LESSONLIST_USERCOPY)
			{
				// user is adding a definition to a vocab word
				$word = Word::getLessonUserWord($record->lesson_id, Auth::id(), $record->id);
				if (isset($word))
				{
					// updating existing lesson user word
					$rc = $this->saveAjax($request, $word);
				}
				else
				{
					if (strlen($request->description) > 0) // don't copy empties
					{
						// adding new lesson user word
						$word = new Word();

						$word->user_id 		= Auth::id();
						$word->lesson_id 	= $record->lesson_id;
						$word->vocab_id 	= $record->id;
						$word->type_flag 	= WORDTYPE_LESSONLIST_USERCOPY;
						$word->title 		= $record->title;
						$word->permalink	= $record->permalink;

						$rc = $this->saveAjax($request, $word);
					}
				}
			}
		}

		return $rc;
	}

    public function getajax(Request $request, $text)
    {	
		// 1. see if we already have it in the dictionary
		$record = Definition::search($text);
		if (isset($record))
		{
			$xlate = null;
			if (!isset($record->translation_en))
			{
				$rc = "<a target='_blank' href='/definitions/edit/$record->id'>Translation not set - Edit Dictionary</a>";
			}
			else
			{
				$xlate = $record->translation_en;
				if ($record->title == $text)
				{
					$rc = $xlate;
				}
				else
				{
					$rc = $record->title . ': ' . $xlate;
				}
				
				$rc = "<a target='_blank' href='/definitions/view/" . $record->id . "'>$rc</a>";
			}								
		}
		else
		{
			// 2. not in our list, show link to MS Translate ajax
			$rc = "<a href='' onclick='event.preventDefault(); xlate(\"" . $text . "\");'>Translate</a>";
		}

		return $rc;
	}

    public function translate(Request $request, $text)
    {	
		$rc = self::translateMicrosoft($text);
		
		if (strlen($rc['error']) == 0)
		{
			// add the translation to our dictionary for next time
			$rc = strtolower($rc['data']);
			Definition::add($text, $rc);
		}
		else
		{
			$rc = $rc['error'];
		}
		
		return $rc;
	}
	
    static public function translateMicrosoft($text)
    {
		$rc = ['error' => '', 'data' => ''];
		$text = trim($text);
		if (strlen($text) == 0)
		{
			$rc['error'] = 'empty string';
			return $rc;
		}
		
		// NOTE: Be sure to uncomment the following line in your php.ini file.
		// ;extension=php_openssl.dll
		// You might need to set the full path, for example:
		// extension="C:\Program Files\Php\ext\php_openssl.dll"
		
		// Prepare variables
		//$text = 'comulgar';
		$path = "/translate?api-version=3.0";
		$params = "&to=en";
		
		// Prepare cURL command
		$key = env('MICROSOFT_API_KEY', '');
		$host = 'api-apc.cognitive.microsofttranslator.com';
		$region = 'australiaeast';

		$guid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
				
		$requestBody = array (
			array (
				'Text' => $text,
			),
		);
		
		$content = json_encode($requestBody);
		//dd($content);

		$headers = "Content-type: application/json\r\n" .
			"Content-length: " . strlen($content) . "\r\n" .
			"Ocp-Apim-Subscription-Key: $key\r\n" .
			"Ocp-Apim-Subscription-Region: " . $region . "\r\n" .
			"X-ClientTraceId: " . $guid . "\r\n";
		//dd($headers);

		// NOTE: Use the key 'http' even if you are making an HTTPS request. See:
		// http://php.net/manual/en/function.stream-context-create.php
		$options = array (
			'http' => array (
				'header' => $headers,
				'method' => 'POST',
				'content' => $content
			)
		);
		//dd($options);
		
		$context  = stream_context_create($options);
		
		$url = 'https://' . $host . $path . $params;
		//dd($url);
		
		try {
			$json = file_get_contents($url, false, $context);
		}
		catch (\Exception $e)
		{
			$msg = 'Error Translating: ' . $text;
			
			if (strpos($e->getMessage(), '401') !== FALSE)
			{
				$msg .= ' - 401 Unauthorized';
			}
			
			Event::logException(LOG_MODEL, LOG_ACTION_TRANSLATE, $msg, null, $e->getMessage());
			$result = $msg;
			$rc['error'] = $msg;
			return $rc;
		}
		//dd($result);

		$json = json_decode($json);
		//dd($json);
		
		if (count($json) > 0)
		{
			$json = $json[0]->translations;
			if (count($json) > 0)
			{
				//dd($json[0]);
				
				$xlate = strtolower($json[0]->text);
				if ($text == $xlate) 
				{
					// if translation is same as the word, then it probably wasn't found
					$rc['error'] = 'translation not found';
				}
				else
				{
					$rc['data'] = $xlate;
				}
			}
			//dd($rc);
		}
	
		return $rc;
	}

    public function addajax(Request $request, $word)
    {
		$word = trim($word);
		$rc = ''; // no change

		// adding a word from js
		if (strlen($word) > 0)
		{
			$word = Word::getDefinition($word);
			//$duplicate = Definition::exists($record->title) ? 'Duplicate: ' : '';
			
			if (isset($word))
			{
				// word already exists
			}
			else
			{
				// adding new lesson user word
				$word = new Definition();

				$word->user_id 		= Auth::id();
				$word->title 		= $record->title;
				$word->definition	= $record->definition;
				$word->permalink	= $record->permalink;

				$rc = $this->saveAjax($request, $word);
			}
		}

		return $rc;
	}

    public function saveAjax(Request $request, Definition $record)
    {
		$rc = '';

		// if he doesn't already have a copy, make one for him
		$isAdd = (!isset($record->id));
		$isDirty = $isAdd;

		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);

		if ($isDirty)
		{
			try
			{
				$record->save();
				$rc = Lang::get('content.' . ($isAdd ? 'user definition added' : 'user definition saved'));
				Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);
			}
			catch (\Exception $e)
			{
				$msg = 'Error ' . ($isAdd ? 'adding new' : 'updating') . ' record';
				Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
				$rc = $msg;
			}
		}

		return $rc;
	}

    public function confirmdelete(Definition $definition)
    {
		return view(PREFIX . '.confirmdelete', $this->getViewData([
			'record' => $definition,
		]));
    }

    public function delete(Request $request, Definition $definition)
    {
		$record = $definition;

		try
		{
			$record->delete();
			Event::logDelete(LOG_MODEL, $record->title, $record->id);
			Tools::flash('success', 'Record has been deleted');
		}
		catch (\Exception $e)
		{
			$msg = 'Error deleting record';
			Event::logException(LOG_MODEL, LOG_ACTION_DELETE, $record->title, $record->id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return redirect('/' . PREFIX . '/');
    }

    public function fastdelete(Word $word)
    {
		$record = $word;

		try
		{
			$record->delete();
		}
		catch (\Exception $e)
		{
			$msg = 'Error during fast delete';
			Event::logException(LOG_MODEL, LOG_ACTION_DELETE, $msg . ': ' . $record->title, $record->id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return back();
    }

    public function undelete()
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = Word::select()
//				->where('site_id', SITE_ID)
				->where('deleted_at', 'is not null')
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . TITLE_LC . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_UNDELETE, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.undelete', $this->getViewData([
			'records' => $records,
		]));
	}

	public function find(Request $request, $text)
    {
		$record = Definition::search($text);
		if (isset($record))
		{ 
			// update the view timestamp so it will move to the back of the list
			$record->updateLastViewedTime();
			
			// format the examples to display as separate sentences
			$record->examples = Tools::splitSentences($record->examples);
		}
		
		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			'word' => Tools::alphanum($text, true),
			], LOG_MODEL, LOG_PAGE_VIEW));		
	}
	
	public function view(Definition $definition)
    {
		$record = $definition;

		// update the view timestamp so it will move to the back of the list
		$record->updateLastViewedTime();

		// format the examples to display as separate sentences
		$record->examples = Tools::splitSentences($record->examples);

		// get next and prev words in ID order
		$prev = $record->getPrev();
		$next = $record->getNext();

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			'next' => $next,
			'prev' => $prev,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function review()
    {
		$words = Word::getIndex();

		$options = [];

		$options['prompt'] = 'Select the correct answer';
		$options['prompt-reverse'] = 'Select the correct question';
		$options['question-count'] = count($words);
		$options['font-size'] = '120%';

		return view(PREFIX . '.review', $this->getViewData([
			'records' => $words,
			'options' => $options,
			'canEdit' => false,
			'quizText' => null,
			'isMc' => false,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function touch(Word $word)
    {
        $rc = 'not touched';

        if (User::isOwner($word->user_id))
        {
            // only touch the word for the owner
            if ($word->updateLastViewedTime())
            {
                $rc = 'touched';
            }
            else
            {
                $rc = 'not touched: update failed';
            }
        }
        else
        {
            $rc = 'not touched: not the owner';
        }

		Event::logInfo(LOG_MODEL, LOG_ACTION_TOUCH, 'touch ' . $word->title . ': ' . $rc);

		return $rc;
    }	
}
