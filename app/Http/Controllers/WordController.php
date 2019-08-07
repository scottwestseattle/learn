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
        $this->middleware('is_admin')->except(['index', 'review', 'view', 'permalink', 'updateajax']);

		$this->prefix = PREFIX;
		$this->title = TITLE;
		$this->titlePlural = TITLE_PLURAL;

		parent::__construct();
	}

    public function index(Request $request, $parent_id)
    {
		$parent_id = intval($parent_id);

		$records = []; // make this countable so view will always work

		try
		{
			$records = Word::getIndex($parent_id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . $this->title . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg . ' for parent ' . $parent_id, $parent_id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.index', $this->getViewData([
			'records' => $records,
			'parent_id' => $parent_id,
		]));
    }

    public function indexowner(Request $request, $parent_id)
    {
		$parent_id = intval($parent_id);

		$records = []; // make this countable so view will always work

		try
		{
			$records = Word::getIndex($parent_id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . $this->title . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg . ' for parent ' . $parent_id, $parent_id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.indexowner', $this->getViewData([
			'records' => $records,
			'parent_id' => $parent_id,
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

    public function add($parent_id = null)
    {
		return view(PREFIX . '.add', $this->getViewData([
			'parent_id' => $parent_id,
			'type_flag' => WORDTYPE_LESSONLIST,
			]));
	}

    public function addUserWords($parent_id = false)
    {
		return view(PREFIX . '.add-user-words', $this->getViewData([
			'parent_id' => $parent_id,
			'type_flag' => $parent_id ? WORDTYPE_LESSONLIST_USER : WORDTYPE_USERLIST,
			]));
	}
	
    public function create(Request $request)
    {
		$record = new Word();
		
		$parent_id = isset($request->parent_id) ? $request->parent_id : null;

		$record->user_id 		= Auth::id();
		$record->parent_id 		= $parent_id;
		$record->type_flag 		= $request->type_flag;
		$record->title 			= $request->title;
		$record->description	= $request->description;
		$record->permalink		= Tools::createPermalink($request->title);

		$duplicate = Word::exists($record->title) ? 'Duplicate: ' : '';
		
		try
		{
			$record->save();

			Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);
			
			$msg = 'New vocabulary has been added';
			if ($request->type_flag == WORDTYPE_LESSONLIST)
				$msg = 'New lesson vocabulary has been added';
			else if ($request->type_flag == WORDTYPE_LESSONLIST_USERCOPY)
				$msg = 'New lesson user vocabulary has been added';
			else if ($request->type_flag == WORDTYPE_USERLIST)
				$msg = 'New user vocabulary has been added';
				
			Tools::flash('success', /* $duplicate . */$msg);
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding new ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);

			return back();
		}

		if ($request->type_flag == WORDTYPE_LESSONLIST)
			return redirect('/words/add/' . $parent_id);
		else
			return redirect('/words/indexowner/' . $parent_id);
    }
	
    public function permalink(Request $request, $permalink)
    {
		$permalink = trim($permalink);

		$record = null;

		try
		{
			$record = Word::select()
				->where('deleted_flag', 0)
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
	
	public function view(Word $word)
    {
		$record = $word;
		$prev = null;//Word::getPrev($word);
		$next = null;//Word::getNext($word);

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			'prev' => $prev,
			'next' => $next,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function edit(Word $word)
    {
		$record = $word;
		
		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $record,
			]));
    }

    public function update(Request $request, Word $word)
    {
		$record = $word;

		$isDirty = false;
		$changes = '';

		$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);
		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);
		
		if ($isDirty)
		{
			try
			{
				$record->save();

				Event::logEdit(LOG_MODEL, $record->title, $record->id, $changes);
				Tools::flash('success', $this->title . ' has been updated');
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
			Tools::flash('success', 'No changes made to ' . TITLE_LC);
		}

		return redirect('/words/' . $record->parent_id);
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
				$word = Word::getLessonUserWord($record->parent_id, Auth::id(), $record->id);
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
						$word->parent_id 	= $record->parent_id;
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
				$rc = $isAdd ? 'user definition added' : 'user definition saved';
				Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);
			}
			catch (\Exception $e)
			{
				$msg = 'Error ' . ($isAdd ? 'adding new' : 'updating') . ' lesson user word';
				Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
				$rc = $msg;
			}
		}
		
		return $rc;
	}
	
    public function confirmdelete(Word $word)
    {
		$record = $word;
		
		$vdata = $this->getViewData([
			'record' => $record,
		]);

		return view(PREFIX . '.confirmdelete', $vdata);
    }

    public function delete(Request $request, Word $word)
    {
		$record = $word;

		try
		{
			$record->deleteSafe();
			Event::logDelete(LOG_MODEL, $record->title, $record->id);
			Tools::flash('success', $this->title . ' has been deleted');
		}
		catch (\Exception $e)
		{
			Event::logException(LOG_MODEL, LOG_ACTION_DELETE, $record->title, $record->id, $e->getMessage());
			Tools::flash('danger', $e->getMessage());
		}

		return redirect('/words/' . $record->parent_id);
    }

    public function fastdelete(Word $word)
    {
		$record = $word;

		try
		{
			$record->deleteSafe();
		}
		catch (\Exception $e)
		{
			$msg = 'Error during fast delete';
			Event::logException(LOG_MODEL, LOG_ACTION_DELETE, $msg . ': ' . $record->title, $record->id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		//return redirect('/words/indexowner/' . $record->parent_id);
		return back();
    }
	
    public function undelete()
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = Word::select()
//				->where('site_id', SITE_ID)
				->where('deleted_flag', 1)
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

	public function review(Word $word, $reviewType = null)
    {
		$record = $word;
		
		$prev = Word::getPrev($record);
		$next = Word::getNext($record);
		
		return view(PREFIX . '.review', $this->getViewData([
			'record' => $record,
			'prev' => $prev,
			'next' => $next,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }	
}
