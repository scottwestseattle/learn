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
use App\Status;
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
        $records = VocabList::getIndex(['ownedOrPublic']);

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
		$quiz = self::makeQuiz($vocabList->words); // splits text into questions and answers
		$quiz = Lesson::formatMc3($quiz, LESSONTYPE_QUIZ_MC3); // format the answers according to quiz type

		$options = Tools::getOptionArray('font-size="150%"');

		$options['prompt'] = Tools::getSafeArrayString($options, 'prompt', 'Select the correct answer');
		$options['prompt-reverse'] = Tools::getSafeArrayString($options, 'prompt-reverse', 'Select the correct question');
		$options['question-count'] = Tools::getSafeArrayInt($options, 'question-count', 0);
		$options['font-size'] = Tools::getSafeArrayString($options, 'font-size', '120%');

		$quizText = [
			'Round' => 'Round',
			'Correct' => 'Correct',
			'TypeAnswers' => 'Type the Answer',
			'Wrong' => 'Wrong',
			'of' => 'of',
		];

		return view('lessons.reviewmc', $this->getViewData([
			'record' => $vocabList,
			'sentenceCount' => count($quiz),
			'records' => $quiz,
			'options' => $options,
			'canEdit' => true,
			'quizText' => $quizText,
			'isMc' => true,
			'returnPath' => PREFIX . '/view',
			'touchPath' => 'words/touch',
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function makeQuiz($records)
    {
		$qna = [];
		$cnt = 0;
		foreach($records as $record)
		{
		    // flip the title and description so title will be the answer
            $qna[$cnt]['q'] = $record->description;
            $qna[$cnt]['a'] = $record->title;
            $qna[$cnt]['id'] = $record->id;
            $qna[$cnt]['ix'] = $cnt; // this will be the button id, just needs to be unique
            $qna[$cnt]['options'] = '';

			$cnt++;
		}

		//dd($qna);

		return $qna;
	}
}
