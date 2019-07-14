<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

define('LESSON_FORMAT_DEFAULT', 0);
define('LESSON_FORMAT_AUTO', 1);

class Lesson extends Base
{
    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function isUnfinished()
    {
    	return (!$this->finished_flag || !$this->approved_flag || !$this->published_flag);
    }

    public function getStatus()
    {
		$v = 'Finished';
		
		if (!$this->finished_flag)
			$v = 'Finish';
		else if (!$this->approved_flag)
			$v = 'Approve';
		else if (!$this->published_flag)
			$v = 'Publish';
		
    	return $v;
    }
	
    public function getDisplayNumber()
    {
    	return $this->lesson_number . '.' . $this->section_number;
    }

    public function renumber($renumberAll)
    {
		$renumbered = false;
		
		if ($renumberAll)
		{
			// renumber all records
			$records = Lesson::select()
				->where('deleted_flag', 0)
				->where('lesson_number', $this->lesson_number)
				->orderBy('section_number')
				->get();

			$next = 1;
			foreach($records as $record)
			{
				$record->section_number = $next++;
				$record->save();
				$renumbered = true;
			}			
		}
		else
		{		
			// check if record with this section_number already exists
			$c = Lesson::select()
				->where('id', '<>', $this->id)
				->where('deleted_flag', 0)
				->where('lesson_number', $this->lesson_number)
				->where('section_number', $this->section_number)
				->count();

			if ($c > 0)
			{
				// renumber starting from current record
				$records = Lesson::select()
					->where('id', '<>', $this->id)
					->where('deleted_flag', 0)
					->where('lesson_number', $this->lesson_number)
					->where('section_number', '>=', $this->section_number)
					->orderBy('section_number')
					->get();

				$next = $this->section_number + 1;
				foreach($records as $record)
				{
					$record->section_number = $next++;
					$record->save();
					$renumbered = true;
					//dump($record->title . ': ' . $next);
				}
				//die;
			}
		}

		return $renumbered;
    }

	static public function getPrev($curr)
	{
		return Lesson::getNextPrev($curr);
	}

	static public function getNext($curr)
	{
		return Lesson::getNextPrev($curr, /* next = */ true);
	}

	// default is prev
	static protected function getNextPrev($curr, $next = false)
	{
		$r = Lesson::select()
			->where('deleted_flag', 0)
			->where('published_flag', 1)
			->where('approved_flag', 1)
			->where('lesson_number', $curr->lesson_number)
			->where('section_number',  $next ? '>' : '<', $curr->section_number)
			->orderByRaw('lesson_number ASC, section_number ' . ($next ? 'ASC' : 'DESC '))
			->first();

		return $r ? $r->id : null;
	}

    static public function getLessonNumbers()
    {
    	return Tools::makeNumberArray(1, 10);
    }

    static public function getSectionNumbers()
    {
    	return Tools::makeNumberArray(1, 10);
    }
}
