<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use App\Lesson;
use App\Event;
use App\Tools;
use DateTime;

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
	
    public function getNext()
    {
		$record = null;
			
		try
		{
			$record = Word::select()
				->where('deleted_flag', 0)
				->where('words.type_flag', WORDTYPE_USERLIST)
				->where('words.id', '<', $this->id)
				->orderByRaw('words.id DESC')
				->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting next word';
			Event::logException('word', LOG_ACTION_SELECT, 'word id = ' . $this->id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}			

		return $record;		
    }	
	
	static public function search($search)
    {
		$records = null;
		
		try
		{
			$search = '%' . $search . '%';

			if (Tools::isSuperAdmin())
			{
				$records = Word::select()
					->where('words.user_id', Auth::id())
					->where('words.deleted_flag', 0)
					->where('words.type_flag', WORDTYPE_USERLIST)
					->where(function ($query) use ($search){$query
						->where('words.title', 'LIKE', $search)
						->orWhere('words.description', 'LIKE', $search);})
					->get();
					//->toSql();
				//dd($records);
			}
			else if (Tools::isAdmin())
			{
			}
			else if (Auth::check())
			{
			}
			else
			{
			}			
		}
		catch(\Exception $e)
		{
		    $msg = "Search Error";
			Event::logException(LOG_MODEL_WORDS, LOG_ACTION_SEARCH, 'search = ' . $search, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}
		
		return $records;
	}	
	
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
				if (self::exists($word, $parent_id))
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

    static public function exists($word, $parent_id = null)
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
	
    static public function getParent($word, $parent_id)
    {
		$count = 0;
		$parent = null;
		$msg = '';
		$rc = null;
		
		if (isset($parent_id) && $parent_id > 0)
			$parent = Lesson::getLesson($parent_id); // get the lesson we are working on
		
		try
		{
			if (isset($parent))
			{
				$msg = 'lesson word';
				
				// check if this word already belongs to any lesson in the course
				$lesson = DB::table('words')
					->join('lessons', 'lessons.id', '=', 'words.parent_id')
					->select('words.*', 'lessons.title as lessonTitle')
					->where('words.deleted_flag', 0)
					->where('words.type_flag', WORDTYPE_LESSONLIST)
					->where('words.title', $word)
					->where('lessons.deleted_flag', 0)
					->where('lessons.parent_id', $parent->parent_id)
					->first();

				if (isset($lesson))
				{
					$rc = [];
					$rc['lesson'] = $lesson->lessonTitle;
				}				
			}
			else if (Auth::check())
			{
				$msg = 'user word';
				
				$count = Word::select()
					->where('deleted_flag', 0)
					->where('user_id', Auth::id())
					->where('type_flag', WORDTYPE_USERLIST)
					->where('title', $word)
					->count();
					
				if ($count > 0)
				{
					$rc = [];
					$rc['count'] = $count;
				}
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting ' . $msg;
			Event::logException('word', LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $rc;
	}

    static public function getCourseLessons($lessons)
    {
		$rc = [];
		
		if (isset($lessons))
		{
			foreach($lessons as $lesson)
			{
				foreach($lesson as $word)
				{
					$rc[$word->lessonId] = $word->lesson_number . '.' . $word->section_number . ' - ' . $word->lessonTitle;
				}
			}
		}
		
		return $rc;
	}
	
    static public function getCourseWords($parent_id, $orderBy)
    {
		$parent_id = intval($parent_id);	
		$words = [];		
		
		$parent = Lesson::getLesson($parent_id); // get the lesson for the course id
		
		try
		{
			if (isset($parent))
			{				
				// check if this word already belongs to any lesson in the course
				$words = DB::table('words')
					->join('lessons', 'lessons.id', '=', 'words.parent_id')
					->select('words.*', 'lessons.id as lessonId', 'lessons.title as lessonTitle', 'lessons.lesson_number', 'lessons.section_number')
					->where('words.deleted_flag', 0)
					->where('words.type_flag', WORDTYPE_LESSONLIST)
					->where('lessons.deleted_flag', 0)
					->where('lessons.parent_id', $parent->parent_id) // only lessons of this course
					->orderByRaw($orderBy)
					->get();		
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting course words';
			Event::logException('word', LOG_ACTION_SELECT, 'lesson id: ' . $parent_id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $words;
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
	
    static public function getIndex($parent_id = null, $limit = 10000)
	{
		$records = [];
		
		if (isset($parent_id))
			$records = self::getByParent($parent_id, WORDTYPE_LESSONLIST, $limit);
		else
			$records = self::getByParent(null, WORDTYPE_USERLIST, $limit);
			
		return $records;
	}
	
    static public function getByParent($parent_id, $type_flag, $limit)
    {
		$records = [];
			
		try
		{
			if (isset($parent_id))
			{
				$parent_id = intval($parent_id);
				
				$records = Word::select()
					->where('deleted_flag', 0)
					->where('parent_id', $parent_id)
					->where('type_flag', $type_flag)
					->orderBy('id')
					->limit($limit)
					->get();
			}
			else
			{
				$records = Word::select()
					->where('deleted_flag', 0)
					->whereNull('parent_id')
					->where('user_id', Auth::id())
					->where('type_flag', $type_flag)
					->orderByRaw('id desc')
					->limit($limit)
					->get();
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting vocabulary list';
			Event::logException('word', LOG_ACTION_SELECT, 'parent_id = ' . Tools::itoa($parent_id), null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}			

		return $records;		
    }	
	
    static public function getLessonUserWord($parent_id, $user_id, $vocab_id)
    {
		$parent_id = intval($parent_id);
		$user_id = intval($user_id);
			
		try
		{
			$record = Word::select()
				->where('deleted_flag', 0)
				->where('parent_id', $parent_id)
				->where('user_id', $user_id)
				->where('type_flag', WORDTYPE_LESSONLIST_USERCOPY)
				->where('vocab_id', $vocab_id)
				->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting user lesson word';
			Event::logException('word', LOG_ACTION_SELECT, 'parent_id = ' . $parent_id . ', user: ' . $user_id . ', title=' . $title, null, $msg . ': ' . $e->getMessage());
		}			

		return $record;		
    }
	
    static public function getLessonUserWords($parent_id)
    {
		$parent_id = intval($parent_id);
		$parent_id = $parent_id > 0 ? $parent_id : '%';

		$records = [];
			
		try
		{
			$records = Word::select()
				->where('deleted_flag', 0)
				->where('parent_id', 'like', $parent_id)
				->where('user_id', Auth::id())
				->where('type_flag', WORDTYPE_LESSONLIST_USERCOPY)
				->orderBy('id')
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting user vocabulary list';
			Event::logException('word', LOG_ACTION_SELECT, 'parent_id = ' . $parent_id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}			

		return $records;		
    }
	
    static public function getByType($parent_id, $type_flag)
    {
		$type_flag = intval($type_flag);
		$parent_id = intval($parent_id);

		$records = [];
			
		try
		{
			$records = Word::select()
				->where('deleted_flag', 0)
				->where('parent_id', $parent_id)
				->where('type_flag', $type_flag)
				->orderBy('id')
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting vocabulary list by type';
			Event::logException('word', LOG_ACTION_SELECT, 'parent_id = ' . $parent_id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}			

		return $records;		
    }	

    static public function getWod($userId)
    {
		$wod = null;
		$records = [];
			
		try
		{
			$record = Word::select()
				->where('deleted_flag', 0)
				->where('user_id', $userId)
				->where('type_flag', WORDTYPE_USERLIST)
				->orderBy('wod_at')
				->first();

			// update the wod timestamp so it will move to the back of the list
			$dt = new DateTime();
			$record->wod_at = $dt->format('Y-m-d H:i:s');
			$record->save();
			//dd($record);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word of the day list';
			Event::logException('word', LOG_ACTION_SELECT, null, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		if (!isset($record))
		{
			$msg = 'Word of the Day Not Found';
			Event::logException(LOG_MODEL_WORDS, LOG_ACTION_SELECT, null, null, $msg);
			Tools::flash('danger', $msg);
		}

		return $record; // return one random word
    }

	// Not Used
    static public function getWodRandom($userId)
    {
		$wod = null;
		$records = [];
			
		try
		{
			$records = Word::select()
				->where('deleted_flag', 0)
				->where('user_id', $userId)
				->where('type_flag', WORDTYPE_USERLIST)
				->orderBy('id')
				->get();				
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word of the day list';
			Event::logException('word', LOG_ACTION_SELECT, null, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		$cnt = count($records) - 1;
		if ($cnt > 0)
		{
			$index = rand(0, $cnt);
			$wod = $records[$index];
		}
		else
		{
			$msg = 'Error getting word list, cnt = ' . $cnt;
			Event::logException(LOG_MODEL_WORDS, LOG_ACTION_SELECT, null, null, $msg);
			Tools::flash('danger', $msg);
		}

		return $wod; // return one random word
    }	
}
