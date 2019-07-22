<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

define('RELEASE_NOTSET', 0);
define('RELEASE_ADMIN', 10);
define('RELEASE_DRAFT', 20);
define('RELEASE_REVIEW', 30);
define('RELEASE_APPROVED', 90);
define('RELEASE_PUBLISHED', 100);
define('RELEASE_DEFAULT', RELEASE_DRAFT);

define('WIP_NOTSET', 0);
define('WIP_INACTIVE', 10);
define('WIP_DEV', 20);
define('WIP_TEST', 30);
define('WIP_FINISHED', 100);
define('WIP_DEFAULT', WIP_DEV);

class Course extends Base
{
	private $test = 'test';
	
    const _releaseFlags = [
		RELEASE_NOTSET => 'Not Set',
		RELEASE_ADMIN => 'Admin Only',
		RELEASE_DRAFT => 'Draft',
		RELEASE_REVIEW => 'Review',
		RELEASE_APPROVED => 'Approved',
		RELEASE_PUBLISHED => 'Published',
    ];
	
	const _wipFlags = [
		WIP_NOTSET => 'Not Set',
		WIP_INACTIVE => 'Inactive',
		WIP_DEV => 'Dev',
		WIP_TEST => 'Test',
		WIP_FINISHED => 'Finished',
	];	
	
	private $_test = [
		'one',
		'two',
	];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }
	
    public function lessons()
    {
    	return $this->hasMany('App\Lesson', 'parent_id', 'id');
    }	

    public function isFinished()
    {
		return ($this->wip_flag == WIP_FINISHED);
	}
    public function isPublished()
    {
		return ($this->release_flag == RELEASE_PUBLISHED);
	}
	
    static public function getReleaseFlags()
    {
		return self::_releaseFlags;
	}

    static public function getWipFlags()
    {
		return self::_wipFlags;
	}
	
    public function getReleaseStatus()
    {
		$btn = '';		
		$text = Tools::safeArrayGetString(self::_releaseFlags, $this->release_flag, 'Unknown Value: ' . $this->release_flag);
		
		switch ($this->release_flag)
		{
			case RELEASE_NOTSET:
				$btn = 'btn-danger';
				break;
			case RELEASE_ADMIN:
				$btn = 'btn-primary';
				break;
			case RELEASE_DRAFT:
				$btn = 'btn-warning';
				break;
			case RELEASE_REVIEW:
				$btn = 'btn-info';
				break;
			case RELEASE_APPROVED:
				$btn = 'btn-success';
				break;
			case RELEASE_PUBLISHED: 
				// don't show anything for published records
				$btn = '';
				$text = '';
				break;
			default:
				$btn = 'btn-danger';
				$text = 'Unknown Value';
				break;
		}
		
		return [
				'btn' => $btn,
				'text' => $text,
			];
	}
	
    public function getWipStatus()
    {
		$btn = '';		
		$text = Tools::safeArrayGetString(self::_wipFlags, $this->wip_flag, 'Unknown Value: ' . $this->wip_flag);
		
		switch ($this->wip_flag)
		{
			case WIP_NOTSET:
				$btn = 'btn-danger';
				break;
			case WIP_INACTIVE:
				$btn = 'btn-info';
				break;
			case WIP_DEV:
				$btn = 'btn-warning';
				break;
			case WIP_TEST:
				$btn = 'btn-primary';
				break;
			case WIP_FINISHED:
				// don't show anything for finished records
				$btn = '';
				$text = '';
				break;
			default:
				$btn = 'btn-danger';
				$text = 'Unknown Value';
				break;
		}
		
		return [
				'btn' => $btn,
				'text' => $text,
			];
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
		
		if (Tools::isAdmin())
		{
			if (array_search('all', $parms) !== false)
			{
				$records = Course::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->orderBy('display_order')
					->get();
			}
			else if (array_search('unfinished', $parms) !== false)
			{
				$records = Course::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->where('wip_flag', '!=', WIP_FINISHED)
					->orderBy('display_order')
					->get();
			}
			else
			{
				$records = Course::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
					->where('wip_flag', '!=', WIP_INACTIVE)
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
		$releaseFlags = Course::getReleaseFlags();

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
