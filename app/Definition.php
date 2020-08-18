<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use App\User;
use App\Event;
use App\Tools;
use App\Definition;

class Definition extends Base
{
    static public function getIndex($parent_id = null, $limit = 10000)
	{
		$records = [];

		try
		{
			$records = Definition::select()
				->whereNull('deleted_at')
				->orderBy('title')
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting vocabulary list';
			Event::logException('word', LOG_ACTION_SELECT, 'parent_id = ' . Tools::itoa($parent_id), null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $records;
    }
	
    static public function get($word)
    {
		$word = trim($word);
		$record = null;

		try
		{
			$record = Definition::select()
				->where('title', $word)
				->where('deleted_at', null)
				->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word: ' . $word;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $record;
	}
	
	static public function add($title, $definition, $examples = null)
	{		
		$record = new Definition();

		$record->user_id 		= Auth::id();
		$record->title 			= $title;
		$record->translation_en = $definition;
		$record->language_id	= LANGUAGE_SPANISH;
		$record->permalink		= Tools::createPermalink($title);
		$record->examples		= $examples;
		
		try
		{
			$record->save();
			Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding definition: ' . $title . ', ' . $definition;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			$rc = $msg;
		}
	}

}
