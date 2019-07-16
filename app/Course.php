<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Base
{
    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    static public function getIndex()
    {
		$records = []; // make this countable so view will always work
		
		if (Tools::isAdmin())
		{
			$records = Course::select()
//				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->get();
		}
		else
		{
			$records = Course::select()
//				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->where('published_flag', 1)
				->where('approved_flag', 1)
				->get();
		}
		
		return $records;
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
}
