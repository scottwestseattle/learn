<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Lang;
use Auth;
use App\User;
use App\Event;
use App\Tools;
use App\Word;
use App\VocabList;
use App\Definition;

define('PREFIX', 'words');
define('LOG_MODEL', 'words');
define('TITLE', 'Word');
define('TITLE_LC', 'word');
define('TITLE_PLURAL', 'Words');
define('REDIRECT', '/words');
define('REDIRECT_ADMIN', '/words/admin');

class WordController extends Controller
{
	public function __construct ()
	{
        $this->middleware('is_admin')->except(['index', 'translate', 'getajax', 'updateajax', 'addUser', 'createUser', 'editUser', 'updateUser'
            , 'confirmDeleteUser', 'deleteUser', 'view', 'touch']);

		$this->prefix = PREFIX;
		$this->title = TITLE;
		$this->titlePlural = TITLE_PLURAL;

		parent::__construct();
	}

    public function index(Request $request, $parent_id = null)
    {
		$records = []; // make this countable so view will always work

		try
		{
			//
			// check for dupe words in course lessons
			//
			$records = Word::getCourseWords($parent_id, 'title');

			$words = [];
			$dupe = false;
			foreach($records as $record)
			{
				if (array_key_exists($record->title, $words))
				{
					dump('dupe: ' . $record->title);
					$dupe = true;
				}

				$words[$record->title] = $record->title;
			}

			if ($dupe)
				dd('done');
			// done with dupe check

			$records = Word::getIndex($parent_id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . $this->title . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg . ' for parent ' . Tools::itoa($parent_id), $parent_id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.index', $this->getViewData([
			'records' => $records,
			'parent_id' => $parent_id,
			'lesson' => isset($parent_id),
		]));
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

    public function addUser()
    {
		$words = Word::getWodIndex(['limit' => WORDTYPE_USERLIST_LIMIT]);

		return view(PREFIX . '.add', $this->getViewData([
			'lesson_id' => null,
			'type_flag' => WORDTYPE_USERLIST,
			'records' => $words,
			'lesson' => false,
			'postAction' => isset($parent_id) ? '/words/create' : '/words/create-user',
			]));
	}

    public function createUser(Request $request)
    {
		return $this->create($request);
	}

    public function add($lesson_id = null)
    {
		$words = isset($lesson_id) ? Word::getCourseWords($lesson_id, 'lesson_number, section_number, id')->groupBy('lesson_id') : null;

		return view(PREFIX . '.add', $this->getViewData([
			'lesson_id' => $lesson_id,
			'vocab_list_id' => null,
			'type_flag' => WORDTYPE_LESSONLIST,
			'records' => $words,
			'lesson' => true,
			'postAction' => isset($parent_id) ? '/words/create' : '/words/create-user',
			]));
	}

    public function create(Request $request)
    {
		$msg = null;
		$record = new Word();
		$parent_id = isset($request->parent_id) ? intval($request->parent_id) : null;

		$record->user_id 		= Auth::id();
		$record->type_flag 		= $request->type_flag;
		$record->title 			= $request->title;
		$record->description	= $request->description;
		$record->examples		= $request->examples;
		$record->permalink		= Tools::createPermalink($request->title);

        // use both because the one is use will not be null
        $record->lesson_id = $request->lesson_id;
        $record->vocab_list_id = $request->vocab_list_id;

		$parent = Word::getParent($record->title, $parent_id);

		try
		{
			if (isset($parent))
			{
				if (array_key_exists('lesson', $parent))
					$msg = 'Word already exists in this course in lesson' . ': ' . $parent['lesson'];
				else
					$msg = 'Word already exists in your vocabulary list';

				throw new \Exception($msg);
			}

			if (isset($record->parent_id))
			{
				if ($record->type_flag == WORDTYPE_USERLIST)
					throw new \Exception("user list word with parent_id");
			}
			else
			{
				if ($record->type_flag == WORDTYPE_LESSONLIST)
					throw new \Exception("lesson list word without parent_id");
			}

			$record->save();

			Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);

			$msg = 'New record has been added';

			Tools::flash('success', /* $duplicate . */$msg);
		}
		catch (\Exception $e)
		{
			$msg = isset($msg) ? $msg : 'Error adding new ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);

			return back();
		}

		if (isset($parent_id) && $parent_id > 0)
			return redirect('/words/add/' . $parent_id);
		else
			return redirect('/words/indexowner/');
    }

    public function addVocabListWord(VocabList $vocabList)
    {
        $parent = $vocabList;

		return view(PREFIX . '.add', $this->getViewData([
		    'lesson_id' => null,
			'vocab_list_id' => $parent->id,
			'parent_title' => $parent->title,
			'type_flag' => WORDTYPE_VOCABLIST,
			'records' => null,//$words,
			'postAction' => '/words/create-vocab-word',
			]));
	}

    public function createVocabListWord(Request $request, VocabList $vocabList)
    {
		$msg = null;
		$record = new Word();

        // use both because the one is use will not be null
        $record->lesson_id = intval($request->lesson_id);
        $record->vocab_list_id = intval($request->vocab_list_id);

		$record->user_id 		= Auth::id();
		$record->type_flag 		= $request->type_flag;
		$record->title 			= $request->title;
		$record->description	= $request->description;
		$record->examples		= $request->examples;
		$record->permalink		= Tools::createPermalink($request->title);

		//$parent = Word::getParent($record->title, $parent_id);
        $parent = null; //todo: check for dupes

		try
		{
			if (isset($parent))
			{
				if (array_key_exists('lesson', $parent))
					$msg = 'Word already exists in this course in lesson' . ': ' . $parent['lesson'];
				else
					$msg = 'Word already exists in your vocabulary list';

				throw new \Exception($msg);
			}

			if (isset($record->vocab_list_id))
			{
				if ($record->type_flag == WORDTYPE_USERLIST)
					throw new \Exception("user list word with parent_id");
			}
			else
			{
				if ($record->type_flag == WORDTYPE_LESSONLIST)
					throw new \Exception("lesson list word without parent_id");
			}

			$record->save();

			Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);

			$msg = 'New record has been added';

			Tools::flash('success', /* $duplicate . */$msg);
		}
		catch (\Exception $e)
		{
			$msg = isset($msg) ? $msg : 'Error adding new ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);

			return back();
		}

		return redirect('/vocab-lists/view/' . $record->vocab_list_id);
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

	public function edit(Word $word)
    {
		$record = $word;

		$words = isset($word->lesson_id) ? Word::getCourseWords($word->lesson_id, 'lesson_number, section_number, id')->groupBy('lesson_id') : [];

		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $record,
			'records' => Word::getIndex($word->lesson_id),
			'lesson' => $record->type_flag == WORDTYPE_LESSONLIST,
			'words' => $words,
			]));
    }

	public function editUser(Word $word)
    {
		$record = $word;

		$words = [];

		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $record,
			'records' => Word::getWodIndex(['limit' => WORDTYPE_USERLIST_LIMIT]),
			'lesson' => $record->type_flag == WORDTYPE_LESSONLIST,
			'words' => $words,
			]));
    }

    public function updateUser(Request $request, Word $word)
    {
		return $this->update($request, $word);
	}

    public function update(Request $request, Word $word)
    {
		$record = $word;
		$msg = '';
		$isDirty = false;
		$changes = '';
		$parent = null;

		$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);
		$isDirtyTitle = $isDirty;

		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);
		$record->examples = Tools::copyDirty($record->examples, $request->examples, $isDirty, $changes);

		if ($record->type_flag == WORDTYPE_VOCABLIST)
		{
		    //todo: allow words to be moved between lists
		}
		else
		{
		    $record->lesson_id =  Tools::copyDirty($record->lesson_id, $request->lesson_id, $isDirty, $changes);
		}

		if ($isDirty)
		{
			try
			{
				if ($isDirtyTitle)
				{
					// check for dupes if the word has changed
					$parent_id = isset($word->lesson_id) ? $word->lesson_id : null;
					$parent = Word::getParent($record->title, $parent_id);
				}

				if (isset($parent))
				{
					if (array_key_exists('lesson', $parent))
						$msg = 'Word already exists in this course in lesson' . ': ' . $parent['lesson'];
					else
						$msg = 'Word already exists in your vocabulary list';

					throw new \Exception($msg);
				}

				$record->save();

				Event::logEdit(LOG_MODEL, $record->title, $record->id, $changes);
				Tools::flash('success', 'Record has been updated');
			}
			catch (\Exception $e)
			{
				$msg = isset($msg) ? $msg : "Error updating record";
				Event::logException(LOG_MODEL, LOG_ACTION_EDIT, $msg . ': title = ' . $record->title, null, $e->getMessage());
				Tools::flash('danger', $msg);
			}
		}
		else
		{
			Tools::flash('success', 'No changes were made');
		}

        $returnPath = '/words/view/' . $word->id;

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

    public function saveAjax(Request $request, Word $record)
    {
		$rc = '';

		// if he doesn't already have a copy, make one for him
		$isAdd = (!isset($record->id));
		$isDirty = $isAdd;

		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);

		//$duplicate = Word::exists($record->title) ? 'Duplicate: ' : '';

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

    public function confirmdelete(Word $word)
    {
		return view(PREFIX . '.confirmdelete', $this->getViewData([
			'record' => $word,
		]));
    }

    public function confirmDeleteUser(Word $word)
    {
		return view(PREFIX . '.confirmdelete', $this->getViewData([
			'record' => $word,
		]));
    }

    public function deleteUser(Request $request, Word $word)
    {
		return $this->delete($request, $word);
	}

    public function delete(Request $request, Word $word)
    {
		$record = $word;

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

		if ($word->type_flag == WORDTYPE_LESSONLIST)
			return redirect('/words/add/' . $word->lesson_id);
		else if ($word->type_flag == WORDTYPE_VOCABLIST)
			return redirect('/vocab-lists/view/' . $word->vocab_list_id);
		else
			return redirect('/words/add-user');
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

	public function view(Word $word)
    {
		$record = $word;

		// update the view timestamp so it will move to the back of the list
		$record->updateLastViewedTime();

		// format the examples to display as separate sentences
		$record->examples = Tools::splitSentences($record->examples);

		// since the currend WOD just got updated, this will get the next wod in line
		$nextWod = $word->getWod($word->user_id);
        $wodList = Word::getWodIndex(['limit' => WORDTYPE_USERLIST_LIMIT]);
        $firstWod = (isset($wodList) && count($wodList) > 0) ? $wodList[0] : null;

		// get next and prev words in ID order
		$prev = $word->getPrev();
		$next = $word->getNext();

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			'firstWod' => $firstWod,
			'records' => $wodList,
			'lesson' => false,
			'nextWod' => $nextWod,
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
