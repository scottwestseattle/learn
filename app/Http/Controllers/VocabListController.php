<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\Definition;
use App\Entry;
use App\Event;
use App\Lesson;
use App\Quiz;
use App\Status;
use App\Tag;
use App\Tools;
use App\User;
use App\VocabList;
use App\Word;

define('PREFIX', 'vocab-lists');
define('LOG_MODEL', 'VocabList');
define('TITLE', 'Vocabulary List');
define('TITLE_LC', 'vocabulary list');
define('TITLE_PLURAL', 'Vocabulary Lists');
define('REDIRECT', '/vocab-lists');
define('REDIRECT_ADMIN', '/vocab-lists/admin');

class VocabListController extends Controller
{
	public function __construct ()
	{
        $this->middleware('is_admin')->except(['index', 'view', 'review', 'permalink']);

		$this->prefix = PREFIX;
		$this->title = TITLE;
		$this->titlePlural = TITLE_PLURAL;

		parent::__construct();
	}

    public function index(Request $request)
    {
		// qna lists
        $records = VocabList::getIndex(['owned']);

		// definitions favorites
		$favorites = Definition::getUserFavoriteLists();

		// articles/books look ups
		$entries = Entry::getDefinitionsUser();

		return view(PREFIX . '.index', $this->getViewData([
			'favorites' => $favorites,
			'records' => $records,
			'entries' => $entries,
			'newest' => true, // show the option for "New Dictionary Entries" review and flashcards
		]));
    }

    public function admin(Request $request)
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = VocabList::select()
//				->where('site_id', SITE_ID)
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
        $record->wip_flag       = WIP_DEFAULT;
        $record->release_flag   = RELEASE_DEFAULT;

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

		if (false) // one time code to export list words to the dictionary
		{
			foreach($record->words->where('deleted_flag', 0) as $r)
			{
				$def = Definition::search($r->title);
				if (!isset($def))
				{
					$def = Definition::add($r->title, $r->description, /* translation = */ null, $r->examples);
					//dump($r->title);
					Event::logAdd(LOG_MODEL_DEFINITIONS, $r->title, $r->description, 0);
				}
				else
				{
					//dump('skipped: ' . $r->title);
					Event::logAdd(LOG_MODEL_DEFINITIONS, 'skipped: ' . $r->title, $r->description, 0);
				}
			}
		}
		
		//dd('done');

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			'prefixWord' => 'words',
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

		return redirect(REDIRECT);
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
		    Word::deleteList($vocabList->words);

			$record->delete();
			Event::logDelete(LOG_MODEL, $record->title, $record->id);
			Tools::flash('success', $this->title . ' has been deleted');
		}
		catch (\Exception $e)
		{
			Event::logException(LOG_MODEL, LOG_ACTION_DELETE, $record->title, $record->id, $e->getMessage());
			Tools::flash('danger', $e->getMessage());
		}

		return redirect(REDIRECT);
    }

    public function undelete()
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = VocabList::select()
//				->where('site_id', SITE_ID)
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
			'release_flags' => Status::getReleaseFlags(),
			'wip_flags' => Status::getWipFlags(),
		]));
    }

    public function publishupdate(Request $request, VocabList $vocabList)
    {
		$record = $vocabList;

		$record->release_flag = $request->release_flag;
		$record->wip_flag = $request->wip_flag;

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

		return redirect(REDIRECT);
    }

	public function review(VocabList $vocabList, $reviewType = null)
    {
		$reviewType = intval($reviewType);
		$quiz = self::makeQuiz($vocabList->words); // splits text into questions and answers
		$settings = Quiz::getSettings($reviewType);

		return view($settings['view'], $this->getViewData([
			'sentenceCount' => count($quiz),
			'records' => $quiz,
			'canEdit' => true,
			'isMc' => true,
			'returnPath' => '/' .  PREFIX . '/view/' . $vocabList->id . '',
			'touchPath' => 'words/touch',
			'parentTitle' => $vocabList->title,			
			'settings' => $settings,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function makeQuiz($records)
    {
		$qna = [];
		$cnt = 0;
		foreach($records as $record)
		{
			$question = $record->title;
			$definition = Tools::getOrSetString($record->description, $question . ': definition not set');
			$examples = $record->examples;
			
            $qna[$cnt]['q'] = $question;
            $qna[$cnt]['a'] = $definition;
            $qna[$cnt]['definition'] = 'false';
            $qna[$cnt]['extra'] = nl2br($examples); // only used for flashcards
            $qna[$cnt]['id'] = $record->id;
            $qna[$cnt]['ix'] = $cnt; // this will be the button id, just needs to be unique
            $qna[$cnt]['options'] = '';

			$cnt++;
		}

		//dd($qna);

		return $qna;
	}
}
