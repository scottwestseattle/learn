<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Base
{
    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function lessons()
    {
    	return $this->hasMany('App\Lesson', 'parent_id', 'id');
    }

    public function getCardColor()
    {
		$cardClass = 'card-course-type0';

		if (isset($this->type_flag))
		{
			switch ($this->type_flag)
			{
				case 1:
					$cardClass = 'card-course-type1';
					break;
				case 2:
					$cardClass = 'card-course-type2';
					break;
				case 3:
					$cardClass = 'card-course-type3';
					break;
				case 4: // other
					$cardClass = 'card-course-type4';
					break;
				default:
					break;
			}
		}

		return $cardClass;
	}

    static public function getTypes()
    {
		$types = [
			'Not Set',
			'English',
			'Spanish',
			'Tech',
			'Other',
		];

		return $types;
	}

    static public function get($id)
    {
		$id = intval($id);

		$record = Course::select()
			->where('deleted_flag', 0)
			->where('id', $id)
			->first();

		return $record;
	}

    static public function getIndex($parms = [])
    {
		$records = []; // make this countable so view will always work

		$public = array_search('public', $parms) !== false;

		if (!$public && Tools::isAdmin())
		{
			if (array_search('all', $parms) !== false)
			{
				$records = Course::select()
					->where('deleted_flag', 0)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->orderBy('type_flag')
					->orderBy('display_order')
					->get();
			}
			else if (array_search('unfinished', $parms) !== false)
			{
				$records = Course::select()
					->where('deleted_flag', 0)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->where('wip_flag', '!=', WIP_FINISHED)
					->orderBy('type_flag')
					->orderBy('display_order')
					->get();
			}
			else if (array_search('private', $parms) !== false)
			{
				$records = Course::select()
					->where('deleted_flag', 0)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->where('release_flag', '!=', RELEASE_PUBLISHED)
					->orderBy('type_flag')
					->orderBy('display_order')
					->get();
			}
			else
			{
				$records = Course::select()
					->where('deleted_flag', 0)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->orderBy('type_flag')
					->orderBy('display_order')
					->get();
			}
		}
		else
		{
			$records = Course::select()
//				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->where('release_flag', '>=', RELEASE_PUBLISHED)
				->orderBy('type_flag')
				->orderBy('display_order')
				->get();
		}

		return $records;
	}

    public function getStatus()
    {
		$text = '';
		$color = 'yellow';
		$done = false;
		$btn = 'btn-warning';
		$releaseFlags = Status::getReleaseFlags();

        if ($this->release_flag < RELEASE_APPROVED)
		{
		    //$text = $releaseFlags[$this->release_flag];

		    $text = Tools::safeArrayGetString($releaseFlags, $this->release_flag, 'Not Found');
		}
		else
		{
			$text = 'Publish';
			$color = 'green';
			$btn = 'btn-success';
			$done = false;
		}

    	return ['text' => $text, 'color' => $color, 'btn' => $btn, 'done' => $done];
    }
}
