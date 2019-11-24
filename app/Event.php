<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use App\Tools;

class Event extends Base
{
    static public function get($limit = 0)
	{
		$q = '
			SELECT *
			FROM events
			WHERE 1=1
			AND site_id = ?
			AND deleted_flag = 0
			ORDER BY id DESC
		';

		if ($limit > 0)
			$q .= ' LIMIT ' . $limit . ' ';

		$records = DB::select($q, [SITE_ID]);

		return $records;
	}

    static public function getLast($type, $model, $action)
    {
		$record = null;

		try
		{
			$record = Event::select()
				->where('deleted_flag', 0)
				->where('user_id', Auth::id())
				->where('type_flag', $type)
				->where('model_flag', $model)
				->where('action_flag', $action)
				->orderByRaw('created_at DESC')
				->first();
		}
		catch(\Exception $e)
		{
		    $msg = "Error getting last event";
			Event::logException(LOG_MODEL_EVENTS, LOG_ACTION_SELECT, $type . '/' . $model . '/' . $action, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $record;
	}

    static public function getAlerts($limit = 0)
	{
		// get all alerts that are not 'Info' (Warning, Error, Exception, and Other)
		$q = '
			SELECT *
			FROM events
			WHERE 1=1
			AND site_id = ?
			AND deleted_flag = 0
			AND type_flag > 1
			ORDER BY id DESC
		';

		if ($limit > 0)
			$q .= ' LIMIT ' . $limit . ' ';

		$records = DB::select($q, [SITE_ID]);

		return $records;
	}

	// these are the shortcuts
    static public function logAdd($model, $title, $description, $record_id)
	{
		Event::add(LOG_TYPE_INFO, $model, LOG_ACTION_ADD, $title, $description, $record_id);
	}

    static public function logEdit($model, $title, $record_id, $changes = null)
	{
		Event::add(LOG_TYPE_INFO, $model, LOG_ACTION_EDIT, $title, null, $record_id, null, $changes);
	}

    static public function logDelete($model, $title, $record_id)
	{
		Event::add(LOG_TYPE_INFO, $model, LOG_ACTION_DELETE, $title, null, $record_id);
	}

    static public function logError($model, $action, $title, $description = null, $record_id = null, $error = null)
    {
		Event::add(LOG_TYPE_ERROR, $model, $action, $title, $description, $record_id, $error);
	}

    static public function logException($model, $action, $title, $record_id = null, $error = null)
    {
		Event::add(LOG_TYPE_EXCEPTION, $model, $action, $title, null, $record_id, $error);
	}

    static public function logTracking($model, $action, $record_id = null, $extraInfo = null)
    {
		Event::add(LOG_TYPE_TRACKING, $model, $action, null, null, $record_id, null, null, $extraInfo);
	}

    static public function logInfo($model, $action, $title)
	{
		Event::add(LOG_TYPE_INFO, $model, $action, $title);
	}

	// this is the add for all records
    static public function add($type, $model, $action, $title, $description = null, $record_id = null, $error = null, $changes = null, $extraInfo = null)
    {
		$record = new Event();

		$record->ip_address		= Tools::getIp();
		$record->site_id 		= SITE_ID;
		$record->user_id 		= Auth::id();

		$record->type_flag		= $type;
		$record->model_flag		= $model;
		$record->action_flag	= $action;

		$record->title 			= $title;
		$record->description	= $description;
		$record->record_id 		= intval($record_id);
		$record->error 			= $error;
		$record->updates 		= $changes;
		$record->extraInfo		= $extraInfo;

		try
		{
			$record->save();
		}
		catch (\Exception $e)
		{
		    $msg = "Fatal Error Adding Event";

			// database failed so show and log event message
			// write an emergency log file
			$line = $msg . ': ' . $e->getMessage() . ' / ' . $model . ' / ' . $action . ' / ' . $title;
			if (!Tools::appendFile('appeventlog.txt', $line))
			{
				// already dumping error message below
    			dd($msg . ' - Error Appending Log File: ~/appeventlog');
			}
			else
			{
    			dd($msg . ' - Check ~/appeventlog');
			}

		}
    }

}
