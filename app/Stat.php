<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
 	// this is the add for all records
    static public function add($type, $model, $action, $title, $description, $record_id)
    {
		$record = new Stat();

		$record->ip_address		= Tools::getIp();
		$record->site_id 		= SITE_ID;
		$record->user_id 		= Auth::id();

		$record->type_flag		= $type;
		$record->model_flag		= $model;
		$record->action_flag	= $action;

		$record->title 			= $title;
		$record->description	= $description;
		$record->record_id 		= intval($record_id);
		$record->extraInfo		= $extraInfo;

		try
		{
			$record->save();
		}
		catch (\Exception $e)
		{
		    $msg = "Fatal Error Adding Stat";

			// database failed so show and log message
			// write an emergency log file
			$line = $msg . ': ' . $e->getMessage() . ' / ' . $model . ' / ' . $action . ' / ' . $title;
			if (!Tools::appendFile('appeventlog.txt', $line))
			{
				// already dumping error message
			}
			
			dd($msg . ' - Check ~/appeventlog');			
		}
    }
}
