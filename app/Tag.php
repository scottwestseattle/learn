<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Entry;

class Tag extends Base
{	
	// many to many
    public function entries()
    {
        return $this->belongsToMany('App\Entry')->orderBy('created_at');
    }

	// add or update 'recent' tag for entries.
	// this lets us order by most recent entries
    static public function recent(Entry $entry)
    {
		if (Auth::check())
		{
			$recent = Tag::getRecent();
			if (isset($recent)) // replace old one if exists
				$entry->tags()->detach($recent->id, ['user_id' => Auth::id()]);
			
			$entry->tags()->attach($recent->id, ['user_id' => Auth::id()]);
			$entry->refresh();
		}
    }

    static public function getRecent()
	{
		$record = self::get('recent');
		if (!isset($record))
		{
			$record = self::addTag('recent');
		}
		
		return $record;
	}
	
    static public function get($name)
    {
		$name = Tools::alphanum($name, true);

		$record = $record = Tag::select()
				->where('deleted_at', null)
				->where('name', $name)
				->first();

		return $record;
    }
	
    static public function addTag($name)
    {
		$record = null;
		$name = Tools::alphanum($name, true);
		if (isset($name) && strlen($name) > 0) // anything is left
		{		
			if (Auth::check())
			{
				$record = new Tag();
				$record->user_id	= Auth::id();
				$record->name 		= $name;
				
				try
				{
					$record->save();
					Event::logAdd(LOG_MODEL, $record->name, $record->user_id, $record->id);
				}
				catch (\Exception $e)
				{
					$msg = 'Error adding ' . LOG_MODEL . ': ' . $record->name . ' for user ' . Auth::id();
					Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->name, null, $msg . ': ' . $e->getMessage());
				}	
			}
		}
		else
		{
			$msg = 'Error adding ' . LOG_MODEL . ': invalid name, user_id = ' . Auth::id();
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, 'inavalid name', null, $msg . ': ' . $e->getMessage());
		}
		
		return $record;
    }
}
