<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
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
        $this->middleware('is_admin')->except(['index', 'review', 'view', 'permalink']);

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
		$record = new Word();

		$record->user_id 		= Auth::id();
		$record->parent_id 		= $request->parent_id;
		$record->title 			= $request->title;
		$record->description	= $request->description;
		$record->text			= $request->text;
		$record->permalink		= Tools::createPermalink($request->title);

		try
		{
			$record->save();

			Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);
			Tools::flash('success', 'New ' . TITLE_LC . ' has been added');
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding new ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);

			return back();
		}

		if (isset($record->id))
			return redirect('/words/edit/' . $record->id);
		else
			return redirect('/words/admin/' . $record->parent_id);
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

		return redirect('/' . PREFIX . '/view/' . $record->id);
	}

    public function updateajax(Request $request, Word $word)
    {
		$record = $word;
		$rc = ''; // no change		

		$isDirty = false;
		$changes = '';

		if (isset($request->fieldCnt) && intval($request->fieldCnt) == 2)
			$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);			
		
		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);

		if ($isDirty)
		{
			try
			{
				$record->save();
				$rc = 'saved';

				Event::logEdit(LOG_MODEL, $record->title, $record->id, $changes);
			}
			catch (\Exception $e)
			{
				$msg = "Error updating record";
				Event::logException(LOG_MODEL, LOG_ACTION_EDIT, $msg . ': title = ' . $record->title, null, $e->getMessage());
				$rc = 'error';
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

		return redirect(REDIRECT_ADMIN);
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
			$msg = 'error during fast delete';
			Event::logException(LOG_MODEL, LOG_ACTION_DELETE, $msg . ': ' . $record->title, $record->id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return redirect('/words/' . $record->parent_id);
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
