<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use App\Lesson;
use App\Event;
use App\Tools;

class Word extends Base
{
    public function user()
    {
    	return $this->belongsTo(User::class);
    }

/*todo: need?
    public function lesson()
    {
    	return $this->belongsTo('App\Lesson', 'parent_id', 'id');
    }
*/
	
    static public function addList($parent_id, $words)
    {
		$parent_id = intval($parent_id);
		
		$rc = [];
		$rc['error'] = false;
		$cnt = 0;
		
		if (is_array($words))
		{
			foreach($words as $word)
			{
				if (self::exists($parent_id, $word))
				{
					// skip: already added
				}
				else
				{
					if (self::create($parent_id, $word))
					{
						$cnt++;
					}
					else
					{
						$rc['error'] = true;
						$cnt = 0;	// show 0 so it won't save
						break;	// break on any error
					}
				}
			}
		}
		
		$rc['cnt'] = $cnt;
		
		return $rc;
    }

    static public function exists($parent_id, $word)
    {
		$count = 0;
		
		try
		{
			$count = Word::select()
				->where('deleted_flag', 0)
				->where('parent_id', $parent_id)
				->where('title', $word)
				->count();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting count';
			Event::logException('word', LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}
		
		return($count > 0);
	}
	
    static public function create($parent_id, $word)
    {
		$rc = false;
		$record = new Word();

		$record->user_id 		= Auth::id();
		$record->parent_id 		= $parent_id;
		$record->title 			= $word;
		$record->permalink		= Tools::createPermalink($word);

		try
		{
			$record->save();
			$rc = true;

			Event::logAdd('word', $record->title, $record->description, $record->id);
			Tools::flash('success', 'New word has been added');
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding new word';
			Event::logException('word', LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}
		
		return $rc;
    }	
	
    static public function getIndex($parent_id, $chapter = '%')
	{
		$parent_id = intval($parent_id);
		$parent_id = $parent_id > 0 ? $parent_id : '%';

		$records = self::getByParent($parent_id);

		return $records;
	}	
	
    static public function getByParent($parent_id)
    {
		$parent_id = intval($parent_id);
		$parent_id = $parent_id > 0 ? $parent_id : '%';

		$records = [];
			
		try
		{
			$records = Word::select()
				->where('deleted_flag', 0)
				->where('parent_id', 'like', $parent_id)
				->orderBy('id')
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting vocabulary list';
			Event::logException('word', LOG_ACTION_SELECT, 'parent_id = ' . $parent_id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}			

		return $records;		
    }
	
}
