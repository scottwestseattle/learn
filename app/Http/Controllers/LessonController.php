<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\User;
use App\Event;
use App\Lesson;
use App\Tools;

define('PREFIX', 'lessons');
define('LOG_MODEL', 'lessons');
define('TITLE', 'Lesson');
define('TITLE_PLURAL', 'Lessons');
define('REDIRECT', '/lessons');
define('REDIRECT_ADMIN', '/lessons/admin');

class LessonController extends Controller
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
			$records = Lesson::select()
//				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->where('published_flag', 1)
				->where('approved_flag', 1)
				->orderBy('lesson_number')
				->orderBy('section_number')
				->get();
		}
		catch (\Exception $e) 
		{
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, 'Error Getting ' . $this->title . ' List', null, $e->getMessage());
			Tools::flash('danger', $e->getMessage());
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
			$records = Lesson::select()
//				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
//				->where('published_flag', 1)
//				->where('approved_flag', 1)
				->orderBy('lesson_number')
				->orderBy('section_number')
				->get();
		}
		catch (\Exception $e) 
		{
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, 'Error Getting ' . $this->title . ' List', null, $e->getMessage());
			Tools::flash('danger', $e->getMessage());
		}	
			
		return view(PREFIX . '.admin', $this->getViewData([
			'records' => $records,
		]));
    }	
	
    public function add()
    {		 
		return view(PREFIX . '.add', $this->getViewData([
			'lessonNumbers' => Lesson::getLessonNumbers(),
			'sectionNumbers' => Lesson::getSectionNumbers(),
			]));
	}
		
    public function create(Request $request)
    {					
		$record = new Lesson();
		
		$record->parent_id 		= 1; //todo: set real Course id
		$record->user_id 		= Auth::id();				
		$record->title 			= $request->title;
		$record->description	= $request->description;
		$record->text			= Tools::convertFromHtml($request->text);
		$record->permalink		= Tools::createPermalink($request->title);
		$record->lesson_number	= intval($request->lesson_number);
		$record->section_number	= intval($request->section_number);

		try
		{
			$record->save();
			
			Event::logAdd(LOG_MODEL, $record->title, $record->site_url, $record->id);			
			Tools::flash('success', $this->title . ' has been added');
		}
		catch (\Exception $e) 
		{
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, 'title = ' . $record->title, null, $e->getMessage());
			Tools::flash('danger', $e->getMessage());

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
		
	public function view(Lesson $lesson)
    {	
		$lesson->text = Tools::convertToHtml($lesson->text);
		
		$prev = Lesson::getPrev($lesson);
		$next = Lesson::getNext($lesson);
		
		//if (!isset($next))
		//{
		//	dump($next);
		//}
		
		return view(PREFIX . '.view', $this->getViewData([
			'record' => $lesson,
			'prev' => $prev,
			'next' => $next,
			], LOG_MODEL, LOG_PAGE_VIEW));
    }
	
	public function edit(Lesson $lesson)
    {		 
		return view(PREFIX . '.edit', $this->getViewData([
			'record' => $lesson,
			]));
    }
		
    public function update(Request $request, Lesson $lesson)
    {
		$record = $lesson;
		 
		$isDirty = false;
		$changes = '';

		$record->title = Tools::copyDirty($record->title, $request->title, $isDirty, $changes);
		$record->description = Tools::copyDirty($record->description, $request->description, $isDirty, $changes);
		$record->text = Tools::copyDirty($record->text, Tools::convertFromHtml($request->text), $isDirty, $changes);
		
		$numbersChanged = false; // if the numbering changes, then we need to check if an auto-renumber is needed
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
					if ($record->renumber()) // check for renumbering
					{
						$msg = 'Lessons have been renumbered';
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
			Tools::flash('success', 'No changes made to ' . $this->title);
		}

		return redirect(REDIRECT_ADMIN);
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
		$vdata = $this->getViewData([
			]);				
		 
		return view(PREFIX . '.undelete', $vdata);
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
		
		$published = isset($request->published_flag) ? 1 : 0;
		$record->published_flag = $published;
		
		if ($published === 0) // if it goes back to private, then it has to be approved again
			$record->approved_flag = 0;
		else
			$record->approved_flag = isset($request->approved_flag) ? 1 : 0;
		
		try
		{
			$record->save();
			Event::logEdit(LOG_MODEL, $record->title, $record->id, 'published/approved status updated');			
			Tools::flash('success', $this->title . ' status has been updated');
		}
		catch (\Exception $e) 
		{
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, 'title = ' . $record->title, null, $e->getMessage());
			Tools::flash('danger', $e->getMessage());
		}				
		
		return redirect(REDIRECT_ADMIN);
    }	
}
