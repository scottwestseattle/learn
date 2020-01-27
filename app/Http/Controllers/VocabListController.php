<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\User;
use App\VocabList;
use App\Event;
use App\Lesson;
use App\Tools;

define('PREFIX', 'vocab-lists');
define('LOG_MODEL', 'VocabList');
define('TITLE', 'Vocabulary List');
define('TITLE_LC', 'vocabulary list');
define('TITLE_PLURAL', 'Vocabulary Lists');
define('REDIRECT', '/lists');
define('REDIRECT_ADMIN', '/lists/admin');

class VocabListController extends Controller
{
	public function __construct ()
	{
        $this->middleware('is_admin')->except(['index', 'view', 'permalink']);

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
			if (Tools::isAdmin())
			{
				$records = VocabList::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
					->get();
			}
			else
			{
				$records = VocabList::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
					->where('release_flag', '>=', RELEASE_PUBLISHED)
					->get();
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.index', $this->getViewData([
			'records' => $records,
		]));
    }

    public function admin(Request $request)
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = VocabList::select()
//				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
//				->where('published_flag', 1)
//				->where('approved_flag', 1)
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . TITLE_LC . ' list';
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
		$record = new VocabList();

		$record->user_id 		= Auth::id();
		$record->title 			= $request->title;
		$record->permalink		= Tools::createPermalink($request->title);

		try
		{
			$record->save();

			Event::logAdd(LOG_MODEL, $record->title, $record->site_url, $record->id);
			Tools::flash('success', 'New ' . TITLE_LC . ' has been added');
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding new ' . TITLE_LC;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);

			return back();
		}

		return redirect(REDIRECT);
    }

    public function permalink(Request $request, $permalink)
    {
		$permalink = trim($permalink);

		$record = null;

		try
		{
			$record = VocabList::select()
				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->where('published_flag', 1)
				->where('approved_flag', 1)
				->where('permalink', $permalink)
				->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Entry Not Found: ' . $permalink;

			Tools::flash('danger', $msg);
			Event::logError(LOG_MODEL, LOG_ACTION_PERMALINK, /* title = */ $msg);

			return back();
		}

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			], LOG_MODEL, LOG_PAGE_PERMALINK));
	}

	public function view(VocabList $vocabList)
    {
		$record = $vocabList;

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function edit(VocabList $vocabList)
    {
		$record = $vocabList;

		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $record,
			]));
    }

    public function update(Request $request, VocabList $vocabList)
    {
		$record = $vocabList;

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
				Tools::flash('success', TITLE . ' has been updated');
			}
			catch (\Exception $e)
			{
				$msg = 'Error updating ' . TITLE_LC;
				Event::logException(LOG_MODEL, LOG_ACTION_EDIT, $record->title, null, $msg . ': ' . $e->getMessage());
				Tools::flash('danger', $msg);
			}
		}
		else
		{
			Tools::flash('success', 'No changes made to ' . TITLE_LC);
		}

		return redirect('/' . PREFIX . '/view/' . $record->id);
	}

    public function confirmdelete(VocabList $vocabList)
    {
		$record = $vocabList;

		$vdata = $this->getViewData([
			'record' => $record,
		]);

		return view(PREFIX . '.confirmdelete', $vdata);
    }

    public function delete(Request $request, VocabList $vocabList)
    {
		$record = $vocabList;

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

    public function undelete()
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = VocabList::select()
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

    public function publish(Request $request, VocabList $vocabList)
    {
		$record = $vocabList;

		return view(PREFIX . '.publish', $this->getViewData([
			'record' => $record,
		]));
    }

    public function publishupdate(Request $request, VocabList $vocabList)
    {
		$record = $vocabList;

		$record->published_flag = isset($request->published_flag) ? 1 : 0;
		$record->approved_flag = isset($request->approved_flag) ? 1 : 0;
		$record->finished_flag = isset($request->finished_flag) ? 1 : 0;

		try
		{
			$record->save();
			Event::logEdit(LOG_MODEL, $record->title, $record->id, 'published/approved/finished status updated');
			Tools::flash('success', $this->title . ' status has been updated');
		}
		catch (\Exception $e)
		{
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $e->getMessage());
			Tools::flash('danger', $e->getMessage());
		}

		return redirect(REDIRECT_ADMIN);
    }
}
