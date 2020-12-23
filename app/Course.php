<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

define('COURSETYPE_NOTSET', 0);
define('COURSETYPE_ENGLISH', 10);
define('COURSETYPE_SPANISH', 20);
define('COURSETYPE_TECH', 30);
define('COURSETYPE_TIMED_SLIDES', 40);
define('COURSETYPE_OTHER', 99);
define('COURSETYPE_DEFAULT', COURSETYPE_NOTSET);

class Course extends Base
{
	const _typeFlags = [
        COURSETYPE_NOTSET => 'Not Set',
        COURSETYPE_ENGLISH => 'English',
        COURSETYPE_SPANISH => 'Spanish',
        COURSETYPE_TECH => 'Tech',
        COURSETYPE_TIMED_SLIDES => 'Timed Slides',
        COURSETYPE_OTHER => 'Other',
	];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function lessons()
    {
    	return $this->hasMany('App\Lesson', 'parent_id', 'id')->where('lessons.deleted_flag', 0)->orderBy('lesson_number');
    }

    public function isTimedSlides()
    {
        return($this->type_flag == COURSETYPE_TIMED_SLIDES);
    }

    public function getCardColor()
    {
		$cardClass = 'card-course-type0';

		if (isset($this->type_flag))
		{
			switch ($this->type_flag)
			{
				case COURSETYPE_ENGLISH:
					$cardClass = 'card-course-type1';
					break;
				case COURSETYPE_SPANISH:
					$cardClass = 'card-course-type2';
					break;
				case COURSETYPE_TECH:
					$cardClass = 'card-course-type3';
					break;
				case 4: // other
					$cardClass = 'card-course-type4';
					break;
				case COURSETYPE_TIMED_SLIDES: // exercise
					$cardClass = 'card-course-type40';
					break;
				case COURSETYPE_OTHER: // exercise
					$cardClass = 'card-course-type99';
					break;
				default:
					break;
			}
		}

		return $cardClass;
	}

    static public function getTypes()
	{
		return self::_typeFlags;
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
		$siteId = Tools::getSiteId();
		$siteIdCondition = '=';

		$showAll = (array_search('all', $parms) !== false);
		if ($showAll && Tools::isSuperAdmin())
		{
			// super admins can see all sites
			$siteId = 0;
			$siteIdCondition = '>=';
		}

		if (!$public && Tools::isAdmin())
		{
			if ($showAll)
			{
				$records = Course::select()
					->where('deleted_flag', 0)
					->where('site_id', $siteIdCondition, $siteId)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->orderBy('type_flag')
					->orderBy('site_id')
					->orderBy('display_order')
					->get();
			}
			else if (array_search('unfinished', $parms) !== false)
			{
				$records = Course::select()
					->where('deleted_flag', 0)
					->where('site_id', $siteIdCondition, $siteId)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->where('wip_flag', '!=', WIP_FINISHED)
					->orderBy('type_flag')
					->orderBy('site_id')
					->orderBy('display_order')
					->get();
			}
			else if (array_search('private', $parms) !== false)
			{
				$records = Course::select()
					->where('deleted_flag', 0)
					->where('site_id', $siteIdCondition, $siteId)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->where('release_flag', '!=', RELEASE_PUBLIC)
					->orderBy('type_flag')
					->orderBy('site_id')
					->orderBy('display_order')
					->get();
			}
			else
			{
				$records = Course::select()
					->where('deleted_flag', 0)
					->where('site_id', $siteIdCondition, $siteId)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->orderBy('type_flag')
					->orderBy('site_id')
					->orderBy('display_order')
					->get();
			}
		}
		else
		{
		    $typeFlag = -1;
		    $language = Tools::getSiteLanguage();
		    if ($language == LANGUAGE_SPANISH)
		        $typeFlag = COURSETYPE_SPANISH;
		    else if ($language == LANGUAGE_ENGLISH)
		        $typeFlag = COURSETYPE_ENGLISH;


			// public
			if ($typeFlag >= 0)
            {
                // use type flag for Spanish and English sites
                $records = Course::select()
                    ->where('deleted_flag', 0)
                    ->where('type_flag', $typeFlag)
                    ->where('release_flag', '>=', RELEASE_PUBLIC)
                    ->orderBy('type_flag')
                    ->orderBy('site_id')
                    ->orderBy('display_order')
                    ->get();
			}
			else
			{
			    // user site_id for the non-language sites
                $records = Course::select()
                    ->where('deleted_flag', 0)
                    ->where('site_id', $siteId)
                    ->where('release_flag', '>=', RELEASE_PUBLIC)
                    ->orderBy('type_flag')
                    ->orderBy('site_id')
                    ->orderBy('display_order')
                    ->get();
			}
		}

		return $records;
	}

    static public function getRss()
    {
		$records = Course::select()
			->where('deleted_flag', 0)
			->where('release_flag', '>=', RELEASE_PUBLIC)
			->where('type_flag', '=', COURSETYPE_TIMED_SLIDES)
			->orderBy('display_order')
			->get();

		return $records;
	}

    static public function getRssReader()
    {
		$records = Course::select()
			->where('deleted_flag', 0)
			->where('release_flag', '>=', RELEASE_PUBLIC)
			->where('type_flag', '=', COURSETYPE_SPANISH)
			->orderBy('display_order')
			->get();

		return $records;
	}

    static public function getRssFlat()
    {
		$records = Course::select(
				'courses.id as courseId', 'courses.title as courseName', 'courses.description as courseDescription',
				'lessons.id as lessonId', 'lessons.title_chapter as lessonName'
				)
			->join('lessons', 'courses.id', '=', 'lessons.parent_id')
			->where('courses.type_flag', '=', COURSETYPE_TIMED_SLIDES)
			->where('courses.deleted_flag', 0)
			->where('lessons.deleted_flag', 0)
			->where('lessons.section_number', 1)
			->where('courses.release_flag', '>=', RELEASE_PUBLIC)
			->orderBy('courses.id')
			->orderBy('lessons.section_number')
			->get();

		//dd($records);

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
