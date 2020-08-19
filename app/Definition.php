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

	// make forms easier to search line: ';one;two;three;'
    static public function formatForms($forms)
    {
		$v = trim($forms);
		$accents = 'áÁéÉíÍóÓúÚüÜñÑ'; 
		$v = str_replace('; ', ';', $v);
		$v = preg_replace('/[^\da-z; ' . $accents . ']/i', '', $v); // replace all non-alphanums except for ';'
		$v = str_replace(';;', ';', $v);
		$v = str_replace(';', '\0', $v); // just to trim the leading and trailing ';' and any spaces
		$v = trim($v);
		$v = str_replace('\0', ';', $v); // put back the non-trimmed ';'
		
		$v = trim($v);
		$v = (strlen($v) > 0) ? $v = ';' . $v . ';' : null;
		//dd($v);
		
		return $v;
	}
	
	// search checks title and forms
    static public function search($word)
    {
		$word = trim($word);
		$record = null;

		try
		{
			$record = Definition::select()
				->where('deleted_at', null)
				->where(function ($query) use ($word){$query
					->where('title', $word)
					->orWhere('forms', 'LIKE', '%;' . $word . ';%')
					;})
				->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word: ' . $word;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}
		
		//dd($record);

		return $record;
	}	
	
	static public function add($title, $definition, $examples = null)
	{
		$title = strtolower($title);
	
		$record = new Definition();

		$record->user_id 		= Auth::id();
		$record->title 			= $title;
		$record->forms 			= $title;
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

    public function updateLastViewedTime()
    {
        $rc = false;

		try
		{
			// update the record's timestamp so it will move to the back of the list
			$this->last_viewed_at = Tools::getTimestamp();
			$this->view_count++;
			$this->save();

			$rc = true;
		}
		catch (\Exception $e)
		{
			$msg = 'Error updating last viewed timestamp';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, 'id = ' . $this->id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $rc;
    }
	
    public function getPrev()
    {
		return self::getPrevNext($this->id);
	}

    public function getNext()
    {
		return self::getPrevNext($this->id, /* $prev = */ false);
	}

    static private function getPrevNext($id, $prev = true)
    {
		$record = null;

		try
		{
			// get prev or next word by id, null is okay
			$record = Definition::select()
				->where('id', $prev ? '<' : '>', $id)
				->whereNull('deleted_at')
				->orderBy('title')
				->first();

			//dd($record);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting prev/next record';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, null, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $record;
    }	
}
