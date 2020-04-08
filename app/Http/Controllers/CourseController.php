<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\User;
use App\Course;
use App\Event;
use App\Lesson;
use App\Tools;
use App\Status;

define('PREFIX', 'courses');
define('LOG_MODEL', 'courses');
define('TITLE', 'Course');
define('TITLE_LC', 'course');
define('TITLE_PLURAL', 'Courses');
define('REDIRECT', '/courses');
define('REDIRECT_ADMIN', '/courses/admin');

class CourseController extends Controller
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
		$public = []; // make this countable so view will always work
		$private = []; // make this countable so view will always work

		try
		{
			$public = Course::getIndex(['public']);
			$private = Course::getIndex(['private']);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . TITLE_LC . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return view(PREFIX . '.index', $this->getViewData([
			'public' => $public,
			'private' => $private,
		]));
    }

    public function admin(Request $request)
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = Course::getIndex(['all']);
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
		$record = new Course();

		$record->user_id 		= Auth::id();
		$record->title 			= $request->title;
		$record->description	= $request->description;
		$record->permalink		= Tools::createPermalink($request->title);
		$record->release_flag	= RELEASE_DEFAULT;
		$record->wip_flag		= WIP_DEFAULT;
        $record->type_flag      = COURSETYPE_DEFAULT;

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

		return redirect(REDIRECT_ADMIN);
    }

    public function permalink(Request $request, $permalink)
    {
		$permalink = trim($permalink);

		$record = null;

		try
		{
			$record = Course::select()
				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->where('release_flag', '>=', RELEASE_PUBLISHED)
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

	public function view(Course $course)
    {
        if ($course->isTimedSlides())
            return $this->startTimedSlides($course);

		$record = $course;
		$count = 0;
		$records = []; // make this countable so view will always work

		try
		{
			$records = Lesson::getChapters($course->id);

			// get the lesson count.  if only one chapter, count it's sections
			$count = count($records); // count the chapters
			if ($count == 1)
			{
				$count = count($records->first()); // count the sections
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting lesson list';
			Event::logException(LOG_MODEL, LOG_ACTION_VIEW, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		// put some view helpers together
		$disabled = (count($records) > 0) ? '' : 'disabled';

		$firstId = (count($records) > 0) ? $records->first()[0]->id : 0; // collection index starts at 1

		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			'records' => $records,
			'disabled' => $disabled,
			'firstId' => $firstId,
			'displayCount' => $count,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function startTimedSlides(Course $course)
    {
		$record = $course;
		$count = 0;
		$records = []; // make this countable so view will always work

		try
		{
			$records = Lesson::getChapters($course->id);

			// get the lesson count.  if only one chapter, count it's sections
			$count = count($records); // count the chapters
			if ($count == 1)
			{
				$count = count($records->first()); // count the sections
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting timed slides';
			Event::logException(LOG_MODEL, LOG_ACTION_VIEW, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		// put some view helpers together
		$disabled = (count($records) > 0) ? '' : 'disabled';
		$firstId = (count($records) > 0) ? $records->first()[0]->id : 0; // collection index starts at 1

        foreach($records as $chapter)
        {
			$times = Lesson::getTimes($chapter);

            $chapter['time'] = $times['timeSeconds'];
            $chapter['totalTime'] = $times['timeTotal'];
        }

		return view(PREFIX . '.viewTimedSlides', $this->getViewData([
			'record' => $record,
			'records' => $records,
			'disabled' => $disabled,
			'firstId' => $firstId,
			'displayCount' => $count,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }

	public function edit(Course $course)
    {
		$record = $course;

		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $record,
			]));
    }

    public function update(Request $request, Course $course)
    {
		$record = $course;

		$isDirty = false;
		$changes = '';

		$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);
		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);
		$record->display_order = Tools::copyDirty($record->display_order, $request->display_order, $isDirty, $changes);
		$record->type_flag = Tools::copyDirty($record->type_flag, $request->type_flag, $isDirty, $changes);

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
			Tools::flash('success', 'No changes were made');
		}

		return redirect('/' . PREFIX . '/view/' . $record->id);
	}

    public function confirmdelete(Course $course)
    {
		$record = $course;

		$vdata = $this->getViewData([
			'record' => $record,
			'children' => Lesson::getIndex($record->id),
		]);

		return view(PREFIX . '.confirmdelete', $vdata);
    }

    public function delete(Request $request, Course $course)
    {
		$record = $course;

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
			$records = Course::select()
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

    public function publish(Request $request, Course $course)
    {
		$record = $course;

		return view(PREFIX . '.publish', $this->getViewData([
			'record' => $record,
			'release_flags' => Status::getReleaseFlags(),
			'wip_flags' => Status::getWipFlags(),
		]));
    }

    public function publishupdate(Request $request, Course $course)
    {
		$record = $course;

		$record->release_flag = $request->release_flag;
		$record->wip_flag = $request->wip_flag;

		try
		{
			$record->save();

			Event::logEdit(LOG_MODEL, $record->title, $record->id, 'Release/work status updated');
			Tools::flash('success', $this->title . ' status has been updated');
		}
		catch (\Exception $e)
		{
			$msg = 'Error updating ' . TITLE_LC . ' status';
			Event::logException(LOG_MODEL, LOG_ACTION_PUBLISH, $record->title, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return redirect(REDIRECT_ADMIN);
    }
}
