<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use Auth;
use App\Lesson;
use App\Event;
use App\Tools;

class Word extends Model
{
    use SoftDeletes;

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function isVocabListWord()
    {
        return($this->type_flag == WORDTYPE_VOCABLIST);
    }

    public function isLessonWord()
    {
        return($this->type_flag == WORDTYPE_LESSONLIST || $this->type_flag == WORDTYPE_LESSONLIST_USERCOPY);
    }

    static public function deleteList($records)
    {
		try
		{
            foreach($records as $record)
            {
    			$record->delete();
    			$msg = 'deleteList() - deleted ' . $record->title;
	    		Event::logDelete(LOG_MODEL, $msg, $record->id);
            }
		}
		catch (\Exception $e)
		{
			$msg = 'Error deleting list';
			Event::logException('word', LOG_ACTION_DELETE, 'parent id = ' . $records->first()->vocab_list_id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}
	}

    public function updateLastViewedTime()
    {
        $rc = false;

		try
		{
			// update the wod timestamp so it will move to the back of the list
			$this->last_viewed_at = Tools::getTimestamp();
			$this->view_count++;
			$this->save();

			$rc = true;
		}
		catch (\Exception $e)
		{
			$msg = 'Error updating last viewed timestamp';
			Event::logException('word', LOG_ACTION_SELECT, 'id = ' . $this->id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $rc;
    }

	static public function search($search)
    {
		$records = null;

		try
		{
			$search = '%' . $search . '%';

            $records = Word::select()
                ->where(function ($query) use ($search){$query
                    ->where('words.title', 'LIKE', $search)
                    ->orWhere('words.description', 'LIKE', $search);})
                ->get();
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
					->where('lesson_id', 'like', $parent_id)
					->where('title', $word)
					->count();
			}
			else
			{
				$count = Word::select()
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
					->where('words.type_flag', WORDTYPE_LESSONLIST)
					->where('words.title', $word)
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
					->where('words.type_flag', WORDTYPE_LESSONLIST)
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

	static public function getWodIndex($parms = null)
	{
		$records = [];

		$limit = is_array($parms) && array_key_exists('limit', $parms) ? $parms['limit'] : PHP_INT_MAX;
		$orderBy = is_array($parms) && array_key_exists('orderBy', $parms) ? $parms['orderBy'] : 'last_viewed_at';

		try
		{
			$records = Word::select()
				->whereNull('lesson_id')
				->where('user_id', Auth::id())
				->where('type_flag', WORDTYPE_USERLIST)
				->orderByRaw($orderBy)
				->limit($limit)
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting wod index';
			Event::logException('word', LOG_ACTION_SELECT, 'getWodIndex()', null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $records;
	}

    static public function getIndex($parent_id = null, $limit = 10000)
	{
		$records = [];

		$records = self::getByParent($parent_id, WORDTYPE_LESSONLIST, $limit);

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
					->where('lesson_id', $parent_id)
					->where('type_flag', $type_flag)
					->orderBy('id')
					->limit($limit)
					->get();
			}
			else
			{
				$records = Word::select()
					->whereNull('lesson_id')
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
				->where('lesson_id', $parent_id)
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
				->where('lesson_id', 'like', $parent_id)
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
				->where('lesson_id', $parent_id)
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
        $record = null;

		try
		{
			$record = Word::select()
				->where('user_id', $userId)
				->where('type_flag', WORDTYPE_VOCABLIST)
				->orderBy('last_viewed_at')
				->first();

			//dd($record);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word of the day list';
			Event::logException('word', LOG_ACTION_SELECT, null, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		if ($record == null)
		{
			$msg = 'No words found for word of the day';
			Event::logError('word', LOG_ACTION_SELECT, /* title = */ $msg);
			Tools::flash('danger', $msg);
		}

		return $record; // return one random word
    }

    public function getPrev()
    {
		return self::getPrevNext($this->user_id, $this->id);
	}

    public function getNext()
    {
		return self::getPrevNext($this->user_id, $this->id, /* $prev = */ false);
	}

    static public function getPrevNext($userId, $id, $prev = true)
    {
		$record = null;

		try
		{
			// get prev or next word by id, null is okay
			$record = Word::select()
				->where('user_id', $userId)
				->where('type_flag', WORDTYPE_USERLIST)
				->where('id', $prev ? '<' : '>', $id)
				->orderByRaw('id ' . ($prev ? 'DESC' : 'ASC'))
				->first();

			//dd($record);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word of the day list';
			Event::logException('word', LOG_ACTION_SELECT, null, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $record;
    }

	// Not Used
    static public function getWodRandom($userId)
    {
		$wod = null;
		$records = [];

		try
		{
			$records = Word::select()
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
