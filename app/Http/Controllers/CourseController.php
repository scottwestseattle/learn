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
		$records = []; // make this countable so view will always work
		
		try
		{
			$records = Course::getIndex();
		}
		catch (\Exception $e) 
		{
			$msg = 'Error getting ' . TITLE_LC . ' list';
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
			$records = Course::getIndex();
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
		$record = $course;
		
		$records = []; // make this countable so view will always work
		try
		{			
			if (Tools::isAdmin())
			{
				$records = Lesson::select()
	//				->where('site_id', SITE_ID)
					->where('parent_id', $record->id)
					->where('deleted_flag', 0)
					->orderBy('lesson_number')
					->orderBy('section_number')
					->get();
			}
			else
			{
				$records = Lesson::select()
	//				->where('site_id', SITE_ID)
					->where('parent_id', $record->id)
					->where('deleted_flag', 0)
					->where('published_flag', 1)
					->where('approved_flag', 1)
					->orderBy('lesson_number')
					->orderBy('section_number')
					->get();
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
		$firstId = (count($records) > 0) ? $records[0]->id : 0;
		
		return view(PREFIX . '.view', $this->getViewData([
			'record' => $record,
			'records' => $records,
			'disabled' => $disabled,
			'firstId' => $firstId,
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
	
    public function confirmdelete(Course $course)
    {	
		$record = $course; 
	
		$vdata = $this->getViewData([
			'record' => $record,
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
			'release_flags' => Course::getReleaseFlags(),
			'wip_flags' => Course::getWipFlags(),
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
