<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

define('RELEASE_NOTSET', 0);
define('RELEASE_ADMIN', 10);
define('RELEASE_DRAFT', 20);
define('RELEASE_REVIEW', 30);
define('RELEASE_APPROVED', 90);
define('RELEASE_PUBLISHED', 100);

define('WIP_NOTSET', 0);
define('WIP_INACTIVE', 10);
define('WIP_DEV', 20);
define('WIP_TEST', 30);
define('WIP_FINISHED', 100);

class Course extends Base
{
    static private $_releaseFlags = [
			RELEASE_NOTSET => 'Not Set',
			RELEASE_ADMIN => 'Admin Only',
			RELEASE_DRAFT => 'Draft',
			RELEASE_REVIEW => 'Review',
			RELEASE_APPROVED => 'Approved',
			RELEASE_PUBLISHED => 'Published',
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
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

    static public function getReleaseFlags()
    {
		return Course::$_releaseFlags;
	}

    static public function getWipFlags()
    {
		$v = [
			WIP_NOTSET => 'Not Set',
			WIP_INACTIVE => 'Inactive',
			WIP_DEV => 'Development',
			WIP_TEST => 'Test',
			WIP_FINISHED => 'Finished',
		];

		return $v;
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
					->orderBy('display_order')
					->get();
			}
			else if (array_search('unfinished', $parms) !== false)
			{
				$records = Course::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
					->where('wip_flag', '!=', WIP_INACTIVE)
					->orderBy('display_order')
					->get();
			}
			else
			{
				$records = Course::select()
	//				->where('site_id', SITE_ID)
					->where('deleted_flag', 0)
					->where('wip_flag', '>', WIP_INACTIVE)
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

    static public function safeArrayGetString($array, $key, $default)
    {
        $v = $default;

        if (array_key_exists($key, $array))
        {
            $v = $array[$key];
        }

        return $v;
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

		    $text = Course::safeArrayGetString($releaseFlags, $this->release_flag, 'Not Found');
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
