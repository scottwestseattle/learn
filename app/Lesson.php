<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Course;

define('LESSON_FORMAT_DEFAULT', 0);
define('LESSON_FORMAT_AUTO', 1);

class Lesson extends Base
{
    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function course()
    {
    	return $this->belongsTo('App\Course', 'parent_id', 'id');
    }
	
    static public function getIndex($parent_id)
	{
		$parent_id = intval($parent_id);
		$parent_id = $parent_id > 0 ? $parent_id : '%';
		$released = Tools::isAdmin() ? '%' : '1';

		$records = [];

		$records = Lesson::select()
			->where('deleted_flag', 0)
			->where('parent_id', 'like', $parent_id)
			->where('published_flag', 'like', $released)
			->where('approved_flag', 'like', $released)
			->orderBy('parent_id')
			->orderBy('lesson_number')
			->orderBy('section_number')
			->get();

		return $records;
	}

    static public function convertCodes($text)
	{
		$v = $text;
		
		// replace underscores with input controls
		//$v = preg_replace('#___#is', "<input />", $v); //todo: do this at view time, don't permanently convert

		//
		// format tables
		//
		
		/*
		<div class="table-borderless">
		<table class="table lesson-table-sm">
		*/
		
		$v = preg_replace('#(<table.*</table>)#is', "<div class=\"table-borderless\">$1</div>", $v); // wrap in div
		$v = preg_replace('#border=\".*\"#is', "class=\"table lesson-table-sm\"", $v); // add table classes

		//dd($v);

		return $v;
	}

    public function isUnfinished()
    {
    	return (!$this->finished_flag || !$this->approved_flag || !$this->published_flag);
    }

    public function getStatus()
    {
		$text = '';
		$color = '';
		$done = true;
		$btn = '';

		if (!$this->approved_flag)
		{
			$text = 'Approve';
			$color = 'yellow';
			$btn = 'btn-warning';
			$done = false;
		}
		else if (!$this->published_flag)
		{
			$text = 'Publish';
			$color = 'green';
			$btn = 'btn-success';
			$done = false;
		}

    	return ['text' => $text, 'color' => $color, 'btn' => $btn, 'done' => $done];
    }

    public function getDisplayNumber()
    {
    	return $this->lesson_number . '.' . $this->section_number;
    }

    public function renumber($renumberAll)
    {
		$renumbered = false;

		// The format is Lesson.Section or Chapter.Section
		if ($renumberAll)
		{
			// renumber all records
			$records = Lesson::select()
				->where('deleted_flag', 0)
				->where('parent_id', $this->parent_id)
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
				->where('parent_id', $this->parent_id)
				->where('deleted_flag', 0)
				->where('lesson_number', $this->lesson_number)
				->where('section_number', $this->section_number)
				->count();

			if ($c > 0)
			{
				// renumber starting from current record
				$records = Lesson::select()
					->where('id', '<>', $this->id)
					->where('parent_id', $this->parent_id)
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

	public function getPrevChapter()
	{
		return $this->getNextPrevChapter();
	}

	public function getNextChapter()
	{
		return $this->getPrevNextChapter(/* next = */ true);
	}
	
	// default is prev
    public function getPrevNextChapter($next = false)
    {
		$r = Lesson::select()
			->where('deleted_flag', 0)
			->where('parent_id', $this->parent_id)
			->where('published_flag', 'like', Tools::isAdmin() ? '%' : 1)
			->where('approved_flag', 'like', Tools::isAdmin() ? '%' : 1)
			->where('lesson_number', $next ? '>' : '<', $this->lesson_number)
			->orderByRaw('lesson_number ASC, section_number ' . ($next ? 'ASC' : 'DESC '))
			->first();

		return $r ? $r->id : null;			
    }	
	
    static public function getLast($parentId)
    {
		$r = Lesson::select()
			->where('deleted_flag', 0)
			->where('parent_id', $parentId)
			->orderByRaw('lesson_number DESC, section_number DESC')
			->first();

		return $r;			
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
		if (Tools::isAdmin())
		{
			$r = Lesson::select()
				->where('deleted_flag', 0)
				->where('parent_id', $curr->parent_id)
				->where('lesson_number', $curr->lesson_number)
				->where('section_number',  $next ? '>' : '<', $curr->section_number)
				->orderByRaw('lesson_number ASC, section_number ' . ($next ? 'ASC' : 'DESC '))
				->first();
		}
		else
		{
			$r = Lesson::select()
				->where('deleted_flag', 0)
				->where('parent_id', $curr->parent_id)
				->where('published_flag', 1)
				->where('approved_flag', 1)
				->where('lesson_number', $curr->lesson_number)
				->where('section_number',  $next ? '>' : '<', $curr->section_number)
				->orderByRaw('lesson_number ASC, section_number ' . ($next ? 'ASC' : 'DESC '))
				->first();
		}

		return $r ? $r->id : null;
	}

    static public function getLessonNumbers($start = 1, $end = 15)
    {
    	return Tools::makeNumberArray($start, $end);
    }

    static public function getSectionNumbers($start = 1, $end = 15)
    {
    	return Tools::makeNumberArray($start, $end);
    }
}
