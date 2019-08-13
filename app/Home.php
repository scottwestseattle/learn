<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use App\Lesson;
use App\Event;
use App\Tools;

class Home extends Base
{

    static public function search($text)
    {
		$count = 0;
				
		try
		{
			if (isset($parent_id))
			{
				$count = Word::select()
					->where('deleted_flag', 0)
					->where('parent_id', 'like', $parent_id)
					->where('title', $word)
					->count();
			}
			else
			{
				$count = Word::select()
					->where('deleted_flag', 0)
					->where('title', $word)
					->count();
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting count';
			Event::logException('word', LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}
	
		return($count > 0);
	}

	static protected function searchEntries($text)
	{			
		$q = '
			SELECT *
			FROM entries
			WHERE 1=1
				AND (title like "%' . $text . '%" OR description like "%' . $text . '%")
				AND site_id = ? 
			ORDER by id DESC
		';
		//AND type_flag in (2,3,4,5,8)

		$records = DB::select($q, [SITE_ID]);
					
		return $records;
	}
	
	static protected function searchPhotos($text)
	{			
		$q = '
			SELECT *
			FROM photos
			WHERE 1=1
				AND (title like "%' . $text . '%" OR alt_text like "%' . $text . '%" OR filename like "%' . $text . '%" OR location like "%' . $text . '%")
				AND site_id = ? 
			ORDER by id DESC
		';

		$records = DB::select($q, [SITE_ID]);
					
		return $records;
	}
	
}
