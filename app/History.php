<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Event;
use App\Tools;

class History extends Base
{
    static public function getRss()
    {
		$records = History::select()
			->where('deleted_flag', 0)
			->orderByRaw('created_at DESC')
			->get();
		
		return $records;
	}
	
	static public function add($programName, $programId, $sessionName, $sessionId, $seconds)
	{
		$record = new History();
		
		$record->ip_address = Tools::getIp();
		$record->program_name = Tools::trimNull($programName, true);
		$record->program_id = intval($programId);
		$record->session_name = Tools::trimNull($sessionName, true);;
		$record->session_id = intval($sessionId);
		$record->seconds = intval($seconds);
				
		try
		{
			$record->save();
			
			$msg = 'Program: ' . $record->program_name . ', Session: ' . $record->session_name . ', Seconds: ' . $record->seconds;
			Event::logInfo(LOG_MODEL, LOG_ACTION_ADD, $msg);
			Tools::flash('success', 'History record added: ' . $msg);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . TITLE_LC . ' list';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}	
	}
}
