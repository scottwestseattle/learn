<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Entry;

class Tag extends Base
{	
    public function entries()
    {
		// many to many
        return $this->belongsToMany('App\Entry')->orderBy('created_at');
    }

    public function definitions()
    {
		// many to many
        return $this->belongsToMany('App\Definition')->orderBy('title');
    }

	// add or update 'recent' tag for entries.
	// this lets us order by most recent entries
    static public function recent(Entry $entry)
    {
		if (Auth::check())
		{
			$recent = self::getRecent();
			if (isset($recent)) // replace old one if exists
				$entry->tags()->detach($recent->id, ['user_id' => Auth::id()]);
			
			$entry->tags()->attach($recent->id, ['user_id' => Auth::id()]);
			$entry->refresh();
		}
    }

	// tag the specified entry with the named tag
	// if it doesn't exist create it.
    static public function tag(Entry $entry, $name, $userId = null)
    {
		if (Auth::check())
		{
			$tag = self::getOrCreate($name);
			if (isset($tag))
			{
				$entry->tags()->detach($tag->id);
				$entry->tags()->attach($tag->id, ['user_id' => $userId]);
			}
		}
    }

    static public function remove(Entry $entry, $name, $userId = null)
    {
		if (Auth::check())
		{
			$tag = self::get($name);
			if (isset($tag))
			{
				$entry->tags()->detach($tag->id);
			}
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

    static public function getOrCreate($name)
	{
		$record = self::get($name);
		if (!isset($record))
		{
			$record = self::addTag($name);
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
		if (isset($name) && strlen($name) > 0) // if anything is left
		{		
			if (Auth::check())
			{
				$record = new Tag();
				$record->user_id = Auth::id();
				$record->name 	 = $name;
				
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
