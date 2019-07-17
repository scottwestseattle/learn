<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

define('RELEASE_NOTSET', 0);
define('RELEASE_DRAFT', 1);
define('RELEASE_REVIEW', 5);
define('RELEASE_APPROVED', 9);
define('RELEASE_PUBLISHED', 10);

define('WIP_NOTSET', 0);
define('WIP_DEV', 1);
define('WIP_TEST', 5);
define('WIP_ONHOLD', 9);
define('WIP_FINISHED', 10);

class Course extends Base
{
    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    static public function getReleaseFlags()
    {
		$v = [
			RELEASE_NOTSET => 'Not Released',
			RELEASE_DRAFT => 'Draft',
			RELEASE_REVIEW => 'Review',
			RELEASE_APPROVED => 'Approved',
			RELEASE_PUBLISHED => 'Published',
		];
		
		return $v;
	}
	
    static public function getWipFlags()
    {
		$v = [
			WIP_NOTSET => 'Not Started',
			WIP_DEV => 'Development',
			WIP_TEST => 'Test',
			WIP_ONHOLD => 'On Hold',
			WIP_FINISHED => 'Finished',
		];
		
		return $v;
	}	
	
    static public function getIndex($parms = [])
    {
		$records = []; // make this countable so view will always work
				
		if (Tools::isAdmin())
		{
			if (array_key_exists('unfinished', $parms))
			{
				$records = Course::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
					->where('wip_flag', '<', WIP_FINISHED)
					->orderBy('display_order')
					->get();
			}
			else
			{
				$records = Course::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
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
	
    public function isPublished()
    {
    	return ($this->release_flag >= RELEASE_PUBLISHED);
    }

    public function getStatus()
    {
		$text = '';
		$color = '';
		$done = true;
		$btn = '';
		
		if ($this->release_flag < RELEASE_APPROVED)
		{
			$text = 'Approve';
			$color = 'yellow';
			$btn = 'btn-warning';
			$done = false;
		}
		else if ($this->release_flag < RELEASE_PUBLISHED)
		{
			$text = 'Publish';
			$color = 'green';
			$btn = 'btn-success';
			$done = false;
		}
		
    	return ['text' => $text, 'color' => $color, 'btn' => $btn, 'done' => $done];
    }
}
