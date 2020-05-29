<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\History;
use App\Tools;
use App\Event;

define('PREFIX', 'history');
define('LOG_MODEL', 'history');
define('TITLE', 'History');
define('TITLE_LC', 'history');
define('TITLE_PLURAL', 'History');
define('REDIRECT', '/history');

class HistoryController extends Controller
{
	public function __construct ()
	{
        $this->middleware('is_admin')->except(['add', 'rss']);

		$this->prefix = PREFIX;
		$this->title = TITLE;
		$this->titlePlural = TITLE_PLURAL;

		parent::__construct();
	}    
	
	// sample url:
	// http://localhost/history/add/Plancha/14/Day+4/4/750
    public function add($programName, $programId, $sessionName, $sessionId, $seconds)
    {
		History::add(urldecode($programName), $programId, urldecode($sessionName), $sessionId, $seconds);
		
		return redirect('/');
	}

    public function rss()
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = History::getRss();
			
			//dd($records);
			//foreach($records as $record)
			//{
			//	dd($record);
			//}
		}
		catch (\Exception $e)
		{
			//dd($e);
			$msg = 'Error getting ' . TITLE_LC . ' rss';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
		}

		return view(PREFIX . '.rss', $this->getViewData([
			'records' => $records,
		]));
    }		
	
    public function index()
    {
		$records = []; // make this countable so view will always work

		try
		{
			$records = History::getRss();
		}
		catch (\Exception $e)
		{
			//dd($e);
			$msg = 'Error getting ' . TITLE_LC . ' rss';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
		}

		return view(PREFIX . '.index', $this->getViewData([
			'records' => $records,
		]));
    }	

    public function confirmdelete(History $history)
    {
		$record = $history;
		$title = $record->program_name . ': ' . $record->session_name;
		
		$vdata = $this->getViewData([
			'record' => $record,
			'historyTitle' => $title,
		]);

		return view(PREFIX . '.confirmdelete', $vdata);
    }

    public function delete(Request $request, History $history)
    {
		$record = $history;
		$title = $record->program_name . ': ' . $record->session_name;
		try
		{
			$record->deleteSafe();
			Event::logDelete(LOG_MODEL, $title, $record->id);
			Tools::flash('success', 'Hisotry record has been deleted');
		}
		catch (\Exception $e)
		{
			Event::logException(LOG_MODEL, LOG_ACTION_DELETE, $title, $record->id, $e->getMessage());
			Tools::flash('danger', $e->getMessage());
		}

		return redirect('history');
    }	
}
