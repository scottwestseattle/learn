<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\User;
use App\Event;
use App\Lesson;
use App\Tools;
use App\Course;

define('PREFIX', 'lessons');
define('LOG_MODEL', 'lessons');
define('TITLE', 'Lesson');
define('TITLE_LC', 'lesson');
define('TITLE_PLURAL', 'Lessons');
define('REDIRECT', '/lessons');
define('REDIRECT_ADMIN', '/lessons/admin');

class LessonController extends Controller
{
	public function __construct ()
	{
        $this->middleware('is_admin')->except(['index', 'review', 'reviewmc', 'view', 'permalink']);

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
			$records = Lesson::getIndex($parent_id);
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
		$course = null;

		try
		{
			$course = Course::get($parent_id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting course';
			Event::logException(LOG_MODEL_COURSES, LOG_ACTION_SELECT, $msg, $parent_id, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		try
		{
			$records = Lesson::getIndex($parent_id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . strtolower($this->title) . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.admin', $this->getViewData([
			'records' => $records,
			'course' => $course,
		]));
    }

    static public function getCourses($source)
    {
		$records = []; // make this countable so view will always work
		try
		{
			$records = Course::getIndex();
		}
		catch (\Exception $e)
		{
			$msg = 'Lesson: Error getting course list';
			Event::logException(LOG_MODEL_COURSES, LOG_ACTION_SELECT, $msg . ' (' . $source . ')', null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $records;
	}

    public function add(Course $course)
    {
		$lessons = null;
		$chapter = 1;
		$section = 1;
		
		if (isset($course->id))
		{
			$record = Lesson::getLast($course->id);
			if (isset($course->id) && isset($record)) // if a lesson already exists, get the next increment
			{
				$chapter = $record->lesson_number;		// use the current chapter
				$section = $record->section_number + 1;	// use the next section number
				
				$lessons = $record->getChapterIndex(); // get lessons to show in a list
			}			
		}
		
		return view(PREFIX . '.add', $this->getViewData([
			'course' => $course,										// parent
			'courses' => LessonController::getCourses(LOG_ACTION_ADD),	// for the course dropdown
			'chapter' => $chapter,
			'section' => $section,
			'lessons' => $lessons,
			]));
	}

    public function create(Request $request)
    {
		$record = new Lesson();

		$record->user_id 		= Auth::id();
		$record->parent_id 		= $request->parent_id;
		$record->title 			= $request->title;
		$record->description	= $request->description;
		$record->text			= Tools::convertFromHtml($request->text);
		$record->permalink		= Tools::createPermalink($request->title);
		$record->lesson_number	= intval($request->lesson_number);
		$record->section_number	= intval($request->section_number);

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
			return redirect('/lessons/edit/' . $record->id);
		else
			return redirect('/lessons/admin/' . $record->parent_id);
    }

    public function permalink(Request $request, $permalink)
    {
		$permalink = trim($permalink);

		$record = null;

		try
		{
			$record = Lesson::select()
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

	private static function autoFormat($text)
    {
		$t = $text;

		$posEx = strpos($t, 'For example:');
		$posH3 = strpos($t, '<h3>');

		if ($posEx && $posH3 && $posEx < $posH3)
		{
			$t = str_replace('For example:', 'For example:<div class="lesson-examples">', $t);
			$t = str_replace('<h3>', '</div><h3>', $t);
		}

		return $t;
	}
	
	public function view(Lesson $lesson)
    {
		$lesson->text = Tools::convertToHtml($lesson->text);

		if ($lesson->format_flag == LESSON_FORMAT_AUTO)
		{
			$lesson->text = LessonController::autoFormat($lesson->text);
		}

		$prev = Lesson::getPrev($lesson);
		$next = Lesson::getNext($lesson);
		$nextChapter = $lesson->getNextChapter();

		// count the paragraphs as sentences
		//preg_match_all('#<p>(.*?)</p>#is', $lesson->text, $matches, PREG_SET_ORDER);
		preg_match_all('#<p>#is', $lesson->text, $matches, PREG_SET_ORDER);

		$vocab = $lesson->getVocab();

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $lesson,
			'prev' => $prev,
			'next' => $next,
			'sentenceCount' => count($matches),
			'courseTitle' => isset($lesson->course) ? $lesson->course->title : '',
			'nextChapter' => $nextChapter,
			'lessons' => $lesson->getChapterIndex(),
			'vocab' => $vocab,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function edit(Lesson $lesson)
    {
		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $lesson,
			'courses' => LessonController::getCourses(LOG_ACTION_EDIT), // for the course dropdown
			]));
    }

    public function update(Request $request, Lesson $lesson)
    {
		$record = $lesson;

		$rc = $record->updateVocab(); // if it's vocab, it will save the word list
		if (!$rc['error']) 
		{
			$request->text = null;	// words moved to vocab list so remove the raw list
		}
		
		$isDirty = false;
		$changes = '';

		$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);
		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);
		$record->text = Tools::copyDirty($record->text, Tools::convertFromHtml(Tools::cleanHtml($request->text)), $isDirty, $changes);
		$record->parent_id = Tools::copyDirty($record->parent_id, $request->parent_id, $isDirty, $changes);
		$record->type_flag = Tools::copyDirty($record->type_flag, $request->type_flag, $isDirty, $changes);
		$record->options = Tools::copyDirty($record->options, $request->options, $isDirty, $changes);

		// autoformat is currently just a checkbox but the db value is a flag
		$format_flag = isset($request->autoformat) ? LESSON_FORMAT_AUTO : LESSON_FORMAT_DEFAULT;
		$record->format_flag = Tools::copyDirty($record->format_flag, $format_flag, $isDirty, $changes);

		// renumber action
		$renumberAll = isset($request->renumber_flag) ? true : false;

		$numbersChanged = $renumberAll; // if the numbering changes, then we need to check if an auto-renumber is needed
		$record->lesson_number = Tools::copyDirty($record->lesson_number, $request->lesson_number, $numbersChanged, $changes);
		$record->section_number = Tools::copyDirty($record->section_number, $request->section_number, $numbersChanged, $changes);
		if ($numbersChanged)
			$isDirty = true;

		if ($isDirty)
		{
			try
			{
				$record->save();

				Event::logEdit(LOG_MODEL, $record->title, $record->id, $changes);
				Tools::flash('success', $this->title . ' has been updated');

				if ($numbersChanged)
				{
					if ($record->renumber($renumberAll)) // check for renumbering
					{
						$msg = TITLE_PLURAL . ' have been renumbered';
						Event::logEdit(LOG_MODEL, $msg, $record->id, 'renumbering');
						Tools::flash('success', $msg);
					}
				}
			}
			catch (\Exception $e)
			{
				Event::logException(LOG_MODEL, LOG_ACTION_EDIT, 'title = ' . $record->title, null, $e->getMessage());
				Tools::flash('danger', $e->getMessage());
			}
		}
		else
		{
			Tools::flash('success', 'No changes made to ' . TITLE_LC);
		}

		return redirect('/' . PREFIX . '/view/' . $record->id);
	}

	public function edit2(Lesson $lesson)
    {
		return view(PREFIX . '.edit2', $this->getViewData([
			'record' => $lesson,
			]));
    }

    public function update2(Request $request, Lesson $lesson)
    {
		$record = $lesson;

		$isDirty = false;
		$changes = '';

		$record->text = Tools::copyDirty($record->text, Tools::convertFromHtml(Tools::cleanHtml($request->text)), $isDirty, $changes);

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
				Event::logException(LOG_MODEL, LOG_ACTION_EDIT, 'title = ' . $record->title, null, $e->getMessage());
				Tools::flash('danger', $e->getMessage());
			}
		}
		else
		{
			Tools::flash('success', 'No changes made to lesson');
		}

		return redirect('/' . PREFIX . '/view/' . $record->id);
	}

    public function confirmdelete(Lesson $lesson)
    {
		$vdata = $this->getViewData([
			'record' => $lesson,
		]);

		return view(PREFIX . '.confirmdelete', $vdata);
    }

    public function delete(Request $request, Lesson $lesson)
    {
		$record = $lesson;

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
			$records = Lesson::select()
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

    public function publish(Request $request, Lesson $lesson)
    {
		return view(PREFIX . '.publish', $this->getViewData([
			'record' => $lesson,
		]));
    }

    public function publishupdate(Request $request, Lesson $lesson)
    {
		$record = $lesson;

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
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, 'title = ' . $record->title, null, $e->getMessage());
			Tools::flash('danger', $e->getMessage());
		}

		return redirect(REDIRECT_ADMIN);
    }

	public function makeQuiz($text)
    {
		$records = [];

		// count the paragraphs as sentences
		preg_match_all('#<p>(.*?)</p>#is', $text, $records, PREG_SET_ORDER);

		$qna = [];
		$cnt = 0;
		foreach($records as $record)
		{
			$parts = explode(' - ', $record[1]); // split the line into q and a

			if (count($parts) > 0)
			{
				$records[$cnt]['q'] = $parts[0];
				$records[$cnt]['a'] = array_key_exists(1, $parts) ? $parts[1] : '';
				$records[$cnt]['id'] = $cnt;
				$records[$cnt]['options'] = '';
			}
			//dd($qna);

			$cnt++;
		}

		//dd($records);

		return $records;
	}

	public function review(Lesson $lesson, $reviewType = null)
    {
		$prev = Lesson::getPrev($lesson);
		$next = Lesson::getNext($lesson);

		$quiz = LessonController::makeQuiz($lesson->text);
		$quiz = $lesson->formatByType($quiz, $reviewType);

		//todo: not working yet
		$quizText = [
			'Round' => 'Round',
			'Correct' => 'Correct',
			'TypeAnswers' => 'Type the Answer',
			'Wrong' => 'Wrong',
			'of' => 'of',
		];
		
		return view(PREFIX . '.review', $this->getViewData([
			'record' => $lesson,
			'prev' => $prev,
			'next' => $next,
			'sentenceCount' => count($quiz),
			'records' => $quiz,
			'questionPrompt' => '', //'What is the answer?',
			'questionPromptReverse' => '', // 'What is the question?',
			'canEdit' => true,
			'quizText' => $quizText,
			'isMc' => $lesson->isMc($reviewType),
			], LOG_MODEL, LOG_PAGE_VIEW));
    }
	
	public function reviewmc(Lesson $lesson, $reviewType = null)
    {
		$prev = Lesson::getPrev($lesson);
		$next = Lesson::getNext($lesson);

		$quiz = LessonController::makeQuiz($lesson->text); // splits text into questions and answers
		$quiz = $lesson->formatByType($quiz, $reviewType); // format the answers according to quiz type

		//todo: not working yet
		$quizText = [
			'Round' => 'Round',
			'Correct' => 'Correct',
			'TypeAnswers' => 'Type the Answer',
			'Wrong' => 'Wrong',
			'of' => 'of',
		];
		
		$options = Tools::getOptionArray($lesson->options);
		
		$options['prompt'] = Tools::getSafeArrayString($options, 'prompt', 'Select the correct answer');
		$options['prompt-reverse'] = Tools::getSafeArrayString($options, 'prompt-reverse', 'Select the correct question');
		$options['question-count'] = Tools::getSafeArrayInt($options, 'question-count', 0);
		$options['font-size'] = Tools::getSafeArrayString($options, 'font-size', '120%');
			
		return view(PREFIX . '.reviewmc', $this->getViewData([
			'record' => $lesson,
			'prev' => $prev,
			'next' => $next,
			'sentenceCount' => count($quiz),
			'records' => $quiz,
			'options' => $options,
			'canEdit' => true,
			'quizText' => $quizText,
			'isMc' => $lesson->isMc($reviewType),
			], LOG_MODEL, LOG_PAGE_VIEW));
    }	
}
