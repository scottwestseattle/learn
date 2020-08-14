<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App;
use App\Entry;
use App\Event;
use App\Translation;
use App\Tools;
use App\User;
use App\Geo;
use App\Status;

define('BODYSTYLE', '<span style="color:green;">');
define('ENDBODYSTYLE', '</span>');
define('EMPTYBODY', 'Empty Body');
define('BODY', 'Body');
define('INTNOTSET', -1);

define('PREFIX', 'entries');
define('LOG_MODEL', 'entries');
define('TITLE', 'Entries');

class EntryController extends Controller
{
	public function __construct ()
	{
        $this->middleware('is_admin')->except(['read', 'articles', 'index', 'view', 'permalink', 'rss']);

		$this->prefix = 'entries';
		$this->title = 'Entry';
		$this->titlePlural = 'Entries';

		parent::__construct();
	}
	
    public function indexAdmin()
    {				
		$entries = Entry::select()
			->where('site_id', Tools::getSiteId())
			//->where('type_flag', '<>', ENTRY_TYPE_TOUR)
			->where('deleted_flag', 0)
			->orderByRaw('entries.id DESC')
			->get();
			
		$vdata = $this->getViewData([
			'records' => $entries,
		]);
			
    	return view('entries.index', $vdata);
    }
	
    public function articles()
    {		
		$this->saveVisitor(LOG_MODEL_ARTICLES, LOG_PAGE_INDEX);

		$records = Entry::getArticles(9999);
		//dd($records);
		
		$vdata = $this->getViewData([
			'records' => $records,
			'page_title' => 'List of Articles',
		]);
			
    	return view('entries.articles', $vdata);
    }
 
    public function index($type_flag = null)
    {				
		$entries = $this->getEntriesByType($type_flag, false, 0, null, false, ORDERBY_DATE);

		$vdata = $this->getViewData([
			'records' => $entries,
			'redirect' => '/entries/indexadmin',
			'entryTypes' => Entry::getEntryTypes(),
		]);
		
    	return view('entries.indexadmin', $vdata);
    }
	
    public function add(Request $request)
    {			
		$vdata = $this->getViewData([
			'entryTypes' => Entry::getEntryTypes(),
			'dates' => Controller::getDateControlDates(),
			'filter' => Controller::getFilter($request, /* today = */ true),
		]);
		
		return view('entries.add', $vdata);
	}
	
    public function create(Request $request)
    {		
		$entry = new Entry();
		
		$entry->site_id = Tools::getSiteId();		
		$entry->user_id = Auth::id();
		$entry->type_flag = $request->type_flag;

		$entry->parent_id 			= $request->parent_id;		
		$entry->title 				= Tools::trimNull($request->title);
		$entry->description_short	= Tools::trimNull($request->description_short);
		$entry->description			= Tools::getMaxText($request->description);
		$entry->source_credit		= Tools::trimNull($request->source_credit);
		$entry->source_link			= Tools::trimNull($request->source_link);
		$entry->display_date 		= Controller::getSelectedDate($request);
		$entry->release_flag 		= RELEASE_PUBLIC;
		$entry->wip_flag 			= WIP_FINISHED;
		$entry->language_flag		= Tools::getLanguageFlag();

		$entry->permalink			= Tools::trimNull($request->permalink);
		if (!isset($entry->permalink))
			$entry->permalink = Tools::createPermalink($entry->title, $entry->display_date);
		
		try
		{
			//dd($entry);
			
			if ($entry->type_flag <= 0)
				throw new \Exception('Entry type not set');
			if (!isset($entry->title))
				throw new \Exception('Title not set');
			if (!isset($entry->display_date))
				throw new \Exception('Date not set');
			
			$entry->save();
			
			$msg = 'Entry has been added';
			$status = 'success';
			if (strlen($request->description) > MAX_DB_TEXT_COLUMN_LENGTH)
			{
				$msg .= ' - DESCRIPTION TOO LONG, TRUNCATED';
				$status = 'danger';
			}
			
			$request->session()->flash('message.level', $status);
			$request->session()->flash('message.content', $msg);
			
			Event::logAdd(LOG_MODEL_ENTRIES, $msg . ': ' . $entry->title, $entry->description, $entry->id);
			
			return redirect('/articles');
		}
		catch (\Exception $e) 
		{
			Event::logException(LOG_MODEL_ENTRIES, LOG_ACTION_ADD, Tools::getTextOrShowEmpty($entry->title), null, $e->getMessage());
				
			$request->session()->flash('message.level', 'danger');
			$request->session()->flash('message.content', "Error adding record, check Events");		

			return back(); 
		}						
    }
	
    public function permalink(Request $request, $permalink)
    {		
		$next = null;
		$prev = null;
		$isRobot = false;
		$wordCount = null;
		
		$entry = Entry::get($permalink);

		$id = isset($entry) ? $entry->id : null;
		$visitor = $this->saveVisitor(LOG_MODEL_ENTRIES, LOG_PAGE_PERMALINK, $id);
		$isRobot = isset($visitor) && $visitor->robot_flag;

		if (isset($entry))
		{
			Entry::countView($entry);
			$wordCount = str_word_count($entry->description); // count it before <br/>'s are added
			$entry->description = nl2br($entry->description);
		}
		else
		{
			return $this->pageNotFound404($permalink);
		}
		
		$page_title = $entry->title;
		$backLink = null;
		$backLinkText = null;
		if ($entry->type_flag == ENTRY_TYPE_ARTICLE)
		{
			$backLink = '/articles';
			$backLinkText = __('content.Back to List');
			$page_title = __('ui.Article') . ' - ' . $page_title;
			
			$next = Entry::getNextPrevEntry($entry);
			$prev = Entry::getNextPrevEntry($entry, /* next = */ false);
		}		

		$vdata = $this->getViewData([
			'record' => $entry, 
			'next' => $next,
			'prev' => $prev,
			'backLink' => $backLink,
			'backLinkText' => $backLinkText,
			'page_title' => $page_title,
			'display_date' => $entry->display_date,
			'isRobot' => false, // $isRobot, //todo: not yet because ome spam robot is coming from fikirandroy page (don't want them to switch pages)
			'wordCount' => $wordCount,
		]);
		
		return view('entries.view', $vdata);
	}

    public function pageNotFound404($address)
    {
		$msg = 'Page Not Found (404) - /entries/' . $address;			
		$geo = new Geo;
		$desc = $geo->visitorInfoDebug();
		Event::logError(LOG_MODEL_ENTRIES, LOG_ACTION_VIEW, /* title = */ $msg, $desc);			
		$data['title'] = '404';
		$data['name'] = 'Page not found';
		return response()->view('errors.404', $data, 404);		
	}
	
    public function view($title, $id)
    {
		$id = intval($id);
		
		$this->saveVisitor(LOG_MODEL, LOG_PAGE_VIEW, $id);
	
		$entry = Entry::select()
			->where('site_id', Tools::getSiteId())
			->where('deleted_flag', '<>', 1)
			->where('id', $id)
			->first();
		
		$photos = Photo::select()
			->where('site_id', Tools::getSiteId())
			->where('deleted_flag', '<>', 1)
			->where('parent_id', '=', $entry->id)
			->orderByRaw('created_at ASC')
			->get();
		
		$comments = Comment::select()
			->where('site_id', Tools::getSiteId())
			->where('deleted_flag', 0)
			->where('parent_id', $entry->id)
			->get();
			
		$vdata = $this->getViewData([
			'record' => $entry, 
			'photos' => $photos,
			'comments' => $comments,
		]);
		
		return view('entries.view', $vdata);
	}

    public function show(Request $request, $id)
    {				
		$id = intval($id);
		$this->saveVisitor(LOG_MODEL, LOG_PAGE_SHOW, $id);
		
		$next = null;
		$prev = null;
		$photos = null;
		
		try 
		{
			$entry = Entry::select()
				->where('site_id', Tools::getSiteId())
				->where('deleted_flag', 0)
				->where('id', $id)
				->first();
								
			if (isset($entry))
			{
				$entry->description = nl2br($entry->description);
				$entry->description = $this->formatLinks($entry->description);		
			}
				
			if (!isset($entry))
			{
				$msg = 'Record not found, ID: ' . $entry->id;
				Event::logError(LOG_MODEL_ENTRIES, LOG_ACTION_VIEW, /* title = */ $msg);			
						
				$request->session()->flash('message.level', 'danger');
				$request->session()->flash('message.content', $msg);
			}
			else if ($entry->type_flag == ENTRY_TYPE_BLOG_ENTRY)
			{						
				if (isset($entry->display_date))
				{
					$next = Entry::getNextPrevBlogEntry($entry->display_date, $entry->parent_id);
					$prev = Entry::getNextPrevBlogEntry($entry->display_date, $entry->parent_id, /* next = */ false);
				}
				else
				{
					$msg = 'Missing Display Date to show record: ' . $entry->id;
					Event::logError(LOG_MODEL_ENTRIES, LOG_ACTION_VIEW, /* title = */ $msg);			
						
					$request->session()->flash('message.level', 'danger');
					$request->session()->flash('message.content', $msg);
				}
				
				$photos = Photo::select()
					//->where('site_id', Tools::getSiteId())
					->where('deleted_flag', 0)
					->where('parent_id', $entry->id)
					->orderByRaw('id ASC')
					->get();
			}
							
			$vdata = $this->getViewData([
				'record' => $entry, 
				'next' => $next,
				'prev' => $prev,
				'photos' => $photos,
			]);
			
			return view('entries.view', $vdata);
		}
		catch (\Exception $e) 
		{
			Event::logException(LOG_MODEL_ENTRIES, LOG_ACTION_SELECT, Tools::getTextOrShowEmpty(isset($entry) ? $entry->title : 'record not found'), null, $e->getMessage());
				
			$request->session()->flash('message.level', 'danger');
			$request->session()->flash('message.content', $e->getMessage());		
		}			
		
        return redirect('/error');
	}
	
    public function home()
    {
		return $this->index();
	}
	
    public function edit(Request $request, Entry $entry)
    {				
		$dates = null;
		if (isset($entry->display_date))
			$dates = Controller::getDateControlSelectedDate($entry->display_date);
			
		$vdata = $this->getViewData([
			'record' => $entry,
			'entryTypes' => Entry::getEntryTypes(),
			'dates' => Controller::getDateControlDates(),
			'filter' => $dates,
		]);
		
		return view('entries.edit', $vdata);
    }
	
    public function update(Request $request, Entry $entry)
    {
		$record = $entry;
		$prevTitle = $record->title;
		
		$record->site_id 			= Tools::getSiteId();
		$record->type_flag 			= $request->type_flag;
		$record->title 				= Tools::trimNull($request->title);
		$record->permalink			= Tools::trimNull($request->permalink);
		$record->description_short	= Tools::trimNull($request->description_short);
		$record->description		= Tools::getMaxText($request->description);
		$record->source_credit		= Tools::trimNull($request->source_credit);
		$record->source_link		= Tools::trimNull($request->source_link);
		$record->display_date 		= Controller::getSelectedDate($request);		
		$entry->language_flag		= Tools::getLanguageFlag();

		try
		{
			$record->save();

			Event::logEdit(LOG_MODEL_ENTRIES, $record->title, $record->id, $prevTitle . '  ' . $record->title);			
			
			$msg = 'Entry has been updated';
			$status = 'success';
			if (strlen($request->description) > MAX_DB_TEXT_COLUMN_LENGTH)
			{
				$msg .= ' - DESCRIPTION TOO LONG, TRUNCATED';
				$status = 'danger';
			}
			
			$request->session()->flash('message.level', $status);
			$request->session()->flash('message.content', $msg);
		}
		catch (\Exception $e) 
		{
			Event::logException(LOG_MODEL_ENTRIES, LOG_ACTION_EDIT, Tools::getTextOrShowEmpty($record->title), null, $e->getMessage());
			
			$request->session()->flash('message.level', 'danger');
			$request->session()->flash('message.content', $e->getMessage());		
		}			

		return redirect('/entries/' . $record->permalink);
    }

    public function confirmdelete(Request $request, Entry $entry)
    {	
		$entry->description = nl2br(trim($entry->description));
		
		$vdata = $this->getViewData([
			'entry' => $entry,
		]);
		
		return view('entries.confirmdelete', $vdata);        	
    }
	
    public function delete(Request $request, Entry $entry)
    {	
		$entry->deleteSafe();
		return redirect('/articles');
    }
	
    public function viewcount(Entry $entry)
    {		
		$this->countView($entry);
		
    	return view('entries.viewcount');
	}

    public function publish(Request $request, Entry $entry)
    {	
		$vdata = $this->getViewData([
			'record' => $entry,
			'release_flags' => Status::getReleaseFlags(),
			'wip_flags' => Status::getWipFlags(),
		]);
		
		return view('entries.publish', $vdata);
    }
	
    public function publishupdate(Request $request, Entry $entry)
    {	
		$record = $entry;
		
		$record->release_flag = intval($request->release_flag);
		$record->wip_flag = intval($request->wip_flag);
		$record->parent_id = intval($request->parent_id);
		$record->view_count = intval($request->view_count);

		$record->save();
		
		return redirect('/articles'); 
    }
	
    public function read(Request $request, Entry $entry)
    {
		if ($entry->deleted_flag != 0)
		{
			return $this->pageNotFound404('read/' . $entry->id);			
		}
		
		$record = $entry;
		$text = [];
		
		$text = Tools::getSentences($record->title);
		$text = array_merge($text, Tools::getSentences($record->description_short));
		$text = array_merge($text, Tools::getSentences($record->description));
		//dd($text);
		
		$record['lines'] = $text;
				
    	return view('entries.reader', $this->getViewData([
			'record' => $record,
		]));
    }	

	//////////////////////////////////////////////////////////////////////////////////////////
	// Privates
	//////////////////////////////////////////////////////////////////////////////////////////
	
    private function fixEmpty(string $text, string $show)
    {	
		if (mb_strlen($text) === 0)
		{
			$text = BODYSTYLE 
			. '(' . strtoupper(__($show)) . ')'
			. ENDBODYSTYLE;			

		}
		
		return $text;
	}
	
	private function merge($layout, $text, $style = false)
	{
		$body = trim($text);
		if (mb_strlen($body) == 0)
		{
			if ($style === true)
			{
				// only show empty in the view version
				$body = '(' . strtoupper(__(EMPTYBODY)) . ')';
			}
			else
			{
				// leave a space for the copy version
				$body = ' ';
			}
		}
		
		if (mb_strlen($layout) > 0)
		{
			if ($style)
				$body = BODYSTYLE . $body . ENDBODYSTYLE;
				
			$text = nl2br(str_replace(BODY_PLACEHODER, $body, trim($layout))) . '<br/>';
		}
		else
		{
			$text = nl2br($body) . '<br/>';
		}
	
		return $text;
	}		
}
