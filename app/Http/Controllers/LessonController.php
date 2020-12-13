<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;

use App\Course;
use App\Entry;
use App\Event;
use App\History;
use App\Lesson;
use App\Quiz;
use App\Tools;
use App\User;
use App\VocabList;

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
        $this->middleware('is_admin')->except([
			'index', 'review', 'reviewmc', 'read', 'view', 
			'start', 'permalink', 'logQuiz', 'rss', 'rssReader'
		]);

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

    //todo: add canned descriptions somewhere
    private $_timedSlideDescriptions = [
        'cobra' => 'Estiramiento de Cobra',
        'leg-lift' => 'Lay flat on your back with arms by your sides and lift both legs about one foot into the air while keeping them straight.',
    ];

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
			'photoPath' => '/img/plancha/',
			]));
	}

    public function create(Request $request)
    {
		$record = new Lesson();

		$record->user_id 		= Auth::id();
		$record->parent_id 		= $request->parent_id;
		$record->title 			= $request->title;
		$record->title_chapter 	= $request->title_chapter;
		$record->description	= $request->description;
		$record->text			= Tools::convertFromHtml($request->text);
		$record->permalink		= Tools::createPermalink($request->title);
		$record->lesson_number	= intval($request->lesson_number);
		$record->section_number	= intval($request->section_number);
        $record->type_flag      = intval($request->type_flag);
        $record->main_photo     = $request->main_photo;
        $record->seconds        = $request->seconds;
        $record->break_seconds  = $request->break_seconds;
        $record->reps           = $request->reps;

        if ($record->isTimedSlides())
        {
            $record->published_flag = 1;
            $record->approved_flag = 1;
            $record->finished_flag = 1;
		}

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
		{
		    if ($record->isText())
			    return redirect('/lessons/edit/' . $record->id);
			else
			    return redirect('/lessons/view/' . $record->id);
		}
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
		Lesson::setCurrentLocation($lesson->id);

		$lesson->text = Tools::convertToHtml($lesson->text);

		if ($lesson->format_flag == LESSON_FORMAT_AUTO)
		{
			$lesson->text = LessonController::autoFormat($lesson->text);
		}

		$prev = Lesson::getPrev($lesson);
		$next = Lesson::getNext($lesson);
		$nextChapter = $lesson->getNextChapter();

		// count the <p>'s as sentences
		preg_match_all('#<p>#is', $lesson->text, $matches, PREG_SET_ORDER);
		$sentenceCount = count($matches);
		// if there's a table, count the rows, add it to the count
		if (strpos($lesson->text, '<table') !== false)
		{
			preg_match_all('#<tr#is', $lesson->text, $matches, PREG_SET_ORDER); // a formatted table not using <p>'s
			$sentenceCount += count($matches);
		}

		// only vocab pages may have vocab
		$vocab = $lesson->getVocab();
		
		// get course time to show
		$records = Lesson::getIndex($lesson->parent_id, $lesson->lesson_number);
				
		$times = Lesson::getTimes($records);		

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $lesson,
			'prev' => $prev,
			'next' => $next,
			'sentenceCount' => count($matches),
			'courseTitle' => isset($lesson->course) ? $lesson->course->title : '',
			'nextChapter' => $nextChapter,
			'lessons' => $lesson->getChapterIndex(),
			'vocab' => $vocab['records'],
			'hasDefinitions' => $vocab['hasDefinitions'], // if the user has already added one or more definitions
			'photoPath' => '/img/plancha/',
			'times' => $times,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function convertToList(Lesson $lesson)
    {
        $text = html_entity_decode($lesson->text); // make it plain text

		$qna = LessonController::makeQuiz($text);
        //dd($qna);

		if (VocabList::import($qna, $lesson->title, $lesson->isMc()))
    		return redirect('/vocab-lists');
    	else
    	    return back();
    }

	public function edit(Lesson $lesson)
    {
		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $lesson,
			'courses' => LessonController::getCourses(LOG_ACTION_EDIT), // for the course dropdown
			'tinymce' => true,
			'photoPath' => '/img/plancha/',
			]));
    }

    public function update(Request $request, Lesson $lesson)
    {
		$record = $lesson;

		$rc = $record->updateVocab(); // if it's vocab, it will save the word list
		if (!Tools::getSafeArrayBool($rc, 'error', false))
		{
			// don't do anything destructive
		}

		$isDirty = false;
		$changes = '';
		
		$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);
		$record->title_chapter = Tools::copyDirty($record->title_chapter, $request->title_chapter, $isDirty, $changes);
		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);
		$record->text = Tools::copyDirty($record->text, Tools::convertFromHtml(Tools::cleanHtml($request->text)), $isDirty, $changes);
		$record->parent_id = Tools::copyDirty($record->parent_id, $request->parent_id, $isDirty, $changes);
		$record->type_flag = Tools::copyDirty($record->type_flag, $request->type_flag, $isDirty, $changes);
		$record->options = Tools::copyDirty($record->options, $request->options, $isDirty, $changes);
		$record->main_photo = Tools::copyDirty($record->main_photo, $request->main_photo, $isDirty, $changes);
		$record->seconds = Tools::copyDirty($record->seconds, $request->seconds, $isDirty, $changes);
		$record->break_seconds = Tools::copyDirty($record->break_seconds, $request->break_seconds, $isDirty, $changes);
		$record->reps = Tools::copyDirty($record->reps, $request->reps, $isDirty, $changes);

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
			    $msg = "Error updating record";
				Event::logException(LOG_MODEL, LOG_ACTION_EDIT, 'title = ' . $record->title, null, $e->getMessage());
				Tools::flash('danger', $msg);
			}
		}
		else
		{
			Tools::flash('success', 'No changes made to ' . TITLE_LC);
		}

        if ($record->isText())
            $redirect = '/lessons/view/' . $record->id;
        else
            $redirect = '/lessons/start/' . $record->id;

		return redirect($redirect);
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

		return redirect('/lessons/view/' . $lesson->id);
    }

	// this is the original way
	public function makeQuiz($text)
    {
		$records = [];

		// count the paragraphs as sentences
		preg_match_all('#<p>(.*?)</p>#is', $text, $records, PREG_SET_ORDER);

		$qna = [];
		$cnt = 0;
		foreach($records as $record)
		{
		    // had to do this because html_entity_decode() wouldn't work in explode
		    $line = str_replace('&nbsp;', ' ', htmlentities($record[1]));
            $line = html_entity_decode($line); // decode it back

            // this doesn't work
            //$line = html_entity_decode($record[1]); // doesn't change &nbsp; to space
            //dump($line);

			$parts = explode(' - ', $line); // split the line into q and a, looks like: "question text - correct answer text"
            //dd($parts);

			if (count($parts) > 0)
			{
				$records[$cnt]['q'] = trim($parts[0]);
				$records[$cnt]['a'] = array_key_exists(1, $parts) ? trim($parts[1]) : '';
				$records[$cnt]['id'] = $cnt;
				$records[$cnt]['options'] = '';
			}
			//dd($qna);

			$cnt++;
		}

		//dd($records);

		return $records;
	}

	//
	// this is the new way, updated for review.js
	//
	public function makeQna($text)
    {
		$records = [];

		// chop it into lines by using the <p>'s
	    $text = str_replace(['&ndash;', '&nbsp;'], ['-', ' '], $text);
		preg_match_all('#<p>(.*?)</p>#is', $text, $records, PREG_SET_ORDER);

		$qna = [];
		$cnt = 0;
		$delim = (strpos($text, ' | ') !== false) ? ' | ' : ' - ';
		foreach($records as $record)
		{
			$line = $record[1];
			$line = strip_tags($line);

			$parts = explode($delim, $line); // split the line into q and a, looks like: "question text - correct answer text"
            //dd($parts);

			if (count($parts) > 0)
			{
				$q = trim($parts[0]);
				$qna[$cnt]['q'] = $q;
				$qna[$cnt]['a'] = array_key_exists(1, $parts) ? trim($parts[1]) : null;
				$qna[$cnt]['definition'] = 'false';
				$qna[$cnt]['translation'] = '';
				$qna[$cnt]['extra'] = '';
				$qna[$cnt]['id'] = $cnt;
				$qna[$cnt]['ix'] = $cnt; // this will be the button id, just needs to be unique
				$qna[$cnt]['options'] = '';
				
				if (!isset($qna[$cnt]['a']))
					throw new \Exception('parse error: ' . $q);
			}

			$cnt++;
		}

		//dd($qna);

		return $qna;
	}
	
	public function reviewOrig(Lesson $lesson, $reviewType = null)
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

		return view(PREFIX . '.review-orig', $this->getViewData([
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
		$quiz = $lesson->formatByType($quiz, $lesson->type_flag); // format the answers according to quiz type

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
            'returnPath' => PREFIX . '/view',
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	//
	// this is the version updated to work with review.js
	//
	public function review(Lesson $lesson, $reviewType = null)
    {
		$reviewType = intval($reviewType);
		$prev = Lesson::getPrev($lesson);
		$next = Lesson::getNext($lesson);

		try
		{
			$quiz = self::makeQna($lesson->text); // split text into questions and answers
		}
		catch (\Exception $e)
		{
			$msg = 'Error parsing for review';
			Event::logException(LOG_MODEL, LOG_ACTION_QUIZ, $msg, $lesson->id, $e->getMessage());
			Tools::flash('danger', $msg);
			return back();
		}

		$settings = Quiz::getSettings($reviewType);

		return view($settings['view'], $this->getViewData([
			'prev' => $prev,
			'next' => $next,
			'sentenceCount' => count($quiz),
			'records' => $quiz,
			'canEdit' => true,
			'isMc' => true, //$lesson->isMc($reviewType),
            'returnPath' => '/' . PREFIX . '/view/' . $lesson->id,
			'parentTitle' => $lesson->title,
			'settings' => $settings,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

    public function read(Request $request, Lesson $lesson)
    {
		$record = $lesson;		
		$text = [];
		
		//$title = Tools::getSentences($record->title);
		$text = str_replace("<br />", "\r\n", $record->text);
		$text = Tools::getSentences($text);
		//$text = array_merge($title, $text);
		//dd($text);
		
		$record['lines'] = $text;
				
    	return view('shared.reader', $this->getViewData([
			'record' => $record,
			'readLocation' => null,
			'speechLanguage' => 'es-ES',
			'contentType' => 'Lesson',
		]));
    }	

    public function logQuiz($lessonId, $score)
    {
		$rc = '';

		if (Auth::check())
		{
			Event::logTracking(LOG_MODEL_LESSONS, LOG_ACTION_QUIZ, $lessonId, $score);
			$rc = 'event logged';
		}
		else
		{
			//todo: set cookie
			$rc = 'user not logged in: event not logged';
		}

		return $rc;
	}

    public function toggleFinished(Lesson $lesson)
    {
		$rc = '';

		if (Auth::check())
		{
			//Event::logTracking(LOG_MODEL_LESSONS, LOG_ACTION_QUIZ, $lessonId, $score);
			$rc = 'event logged';
		}
		else
		{
			//todo: set cookie
			$rc = 'user not logged in: event not logged';
		}

		$rc = 'Not implemented yet';

		return $rc;
	}

	public function start(Lesson $lesson)
    {		
		Lesson::setCurrentLocation($lesson->id);

		$records = Lesson::getIndex($lesson->parent_id, $lesson->lesson_number);
		
		if (false) // one time fix for file namespace
		{
			foreach($records as $record)
			{
				$photo = str_replace('-', '_', $record->main_photo);
				$photo = str_replace(' ', '_', $photo);
				
				if ($photo != $record->main_photo)
				{
					//dump($photo);
					$record->main_photo = $photo;
					$record->save();
				}
			}
		}
		
		$times = Lesson::getTimes($records);

		// get background images by random album
		$bgAlbums = [
			'pnw', 'europe', 'africa', 'uk'
		];
		$ix = rand(1, count($bgAlbums)) - 1;
		$album = $bgAlbums[$ix];
        $bgs = Tools::getPhotos('/img/backgrounds/' . $album . '/');
		//dump($album);
		
        foreach($bgs as $key => $value)
        {
            $bgs[$key] = 0;
        }
        //dd($bgs);

		return view(PREFIX . '.runtimed', $this->getViewData([
			'record' => $lesson,
			'records' => $records,
			'returnPath' => 'courses/view',
			'displayTime' => $times['timeTotal'],
			'totalSeconds' => $times['seconds'],
			'bgs' => $bgs,
			'bgAlbum' => $album,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }
	
    public function rssReader(Lesson $lesson)
    {
		$records = Lesson::getIndex($lesson->parent_id, $lesson->lesson_number);

		$qna = [];
		
		foreach ($records as $record)
		{
			$lines = explode("\r\n", strip_tags(html_entity_decode($record->text)));
			//dd($lines);
			
			$cnt = 0;
			foreach($lines as $line)
			{
				$line = trim($line);
				if (strlen($line) > 0)
				{
					$parts = explode(" - ", $line);

					$qna[$cnt]['q'] = null;
					$qna[$cnt]['a'] = null;

					if (count($parts) > 0)
						$qna[$cnt]['q'] = $parts[0];
						
					if (count($parts) > 1)
						$qna[$cnt]['a'] = $parts[1];
					
					$cnt++;
				}
			}
			
			$record['qna'] = $qna;
		}

		return view(PREFIX . '.rss-reader', $this->getViewData([
			'record' => $lesson,
			'records' => $records,
			], LOG_MODEL, LOG_PAGE_VIEW));
	}
	
	public function rss(Lesson $lesson)
    {
		$records = Lesson::getIndex($lesson->parent_id, $lesson->lesson_number);

		return view(PREFIX . '.rss', $this->getViewData([
			'record' => $lesson,
			'records' => $records,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }
	
}
