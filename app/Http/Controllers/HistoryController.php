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
}
