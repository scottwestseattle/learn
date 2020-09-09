<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use DateTime;
use Session;

use App\Definition;
use App\Status;
use App\Tag;
use App\Tools;

class Entry extends Base
{
	static private $entryTypes = [
		ENTRY_TYPE_NOTSET => 'Not Set',
		ENTRY_TYPE_ARTICLE => 'Article',
		ENTRY_TYPE_BOOK => 'Book',
		ENTRY_TYPE_ENTRY => 'Entry',
//		ENTRY_TYPE_OTHER => 'Other',
	];
	
	static public function getEntryTypes()
	{		
		return self::$entryTypes;
	}

	static public function getTypeFlagName($type)
	{		
		return self::$entryTypes[$type];
	}
	
    public function user()
    {
    	return $this->belongsTo(User::class);
    }

	//////////////////////////////////////////////////////////////////
	// Definitions - many to many
	//////////////////////////////////////////////////////////////////

    public function definitions()
    {
		return $this->belongsToMany('App\Definition')->wherePivot('user_id', Auth::id())->orderBy('title');
		//return $this->belongsToMany('App\Definition')->orderBy('title');
    }	

	public function getDefinitions($userId)
	{
		$userId = intval($userId);
		$entryId = $this->id;
		$records = DB::table('entries')
			->join('definition_entry', function($join) use ($entryId, $userId) {
				$join->on('definition_entry.entry_id', '=', 'entries.id');
				$join->where('definition_entry.entry_id', $entryId);
				$join->where('definition_entry.user_id', $userId);
			})
			->join('definitions', function($join) use ($entryId) {
				$join->on('definitions.id', '=', 'definition_entry.definition_id');
				$join->whereNull('definitions.deleted_at');
			})
			->select('definitions.*')
			->where('entries.deleted_flag', 0)
			->orderBy('definitions.title')
			->get();

		return $records;
	}

	static public function getDefinitionsUser()
	{
		$records = DB::table('entries')
			->join('definition_entry', function($join) {
				$join->on('definition_entry.entry_id', '=', 'entries.id');
				$join->where('definition_entry.user_id', Auth::id());
			})
			->select(DB::raw('entries.id, entries.title, count(definition_entry.entry_id) as wc'))			
			->where('entries.deleted_flag', 0)
			->whereIn('entries.type_flag', array(ENTRY_TYPE_ARTICLE, ENTRY_TYPE_BOOK))
			->where('entries.release_flag', '>=', RELEASE_PUBLIC)
			->groupBy('entries.id', 'entries.title')
			->orderBy('entries.title')
			->get();

		//dd($records);

		return $records;
	}

    static public function addDefinitionUserStatic($entryId, $def)
    {
		$entryId = intval($entryId);
		$userId = Auth::id();
		if ($entryId > 0)
		{
			if (isset($def))
			{
				$record = $record = Entry::select()
					->where('deleted_flag', 0)
					->where('id', $entryId)
					->first();
				
				if (isset($record))
				{
					$record->addDefinitionUser($def);
				}
				else
				{
					$info = 'entry not found, entry id: ' . $entryId . ', user id: ' . $userId . '';
					Event::logError(LOG_MODEL_ENTRIES, LOG_ACTION_ADD, 'error adding definition for user, ' . $info);			
				}
			}
			else
			{
				$info = 'def not set, user id: ' . $userId;
				Event::logError(LOG_MODEL_ENTRIES, LOG_ACTION_ADD, 'error adding definition for user, ' . $info);			
			}
		}
		else
		{
			$info = 'entry id not set, user id: ' . $userId;
			Event::logError(LOG_MODEL_ENTRIES, LOG_ACTION_ADD, 'error adding definition for user, ' . $info);			
		}
	}
	
    public function addDefinitionUser(Definition $def)
    {
		if (Auth::check())
		{
			$this->addDefinition($def, Auth::id());
		}
	}
	
    public function addDefinition($def, $userId = null)
    {
		if (isset($def) && isset($userId))
		{
			$this->definitions()->detach($def->id); // if it's already tagged, remove it so it will by updated
			$this->definitions()->attach($def->id, ['user_id' => $userId]);
			Event::logAdd(LOG_MODEL_ENTRIES, 'added definition: "' . $def->title . '" to entry', 'user id: ' . $userId, $def->id);			
		}
		else
		{
			$info = 'def or user not set, user id: ' . $userId;
			Event::logError(LOG_MODEL_ENTRIES, LOG_ACTION_ADD, 'error adding definition for user, ' . $info);			
		}
    }

    public function removeDefinitionUser($defId)
    {
		$rc = '';
		
		if (Auth::check())
		{
			$def = Definition::getById($defId);
			if (isset($def))
			{
				$this->definitions()->detach($def->id, ['user_id' => Auth::id()]);
				$rc = 'success';
			}
			else
			{
				$rc = 'definition not found';
			}
		}
		else
		{
			$rc = 'not logged in';
		}
		
		return $rc;
	}

    public function removeDefinition(Definition $def)
    {
		$this->definitions()->detach($def->id);
    }

	//////////////////////////////////////////////////////////////////
	// Tags - many to many
	//////////////////////////////////////////////////////////////////
	
    public function tags()
    {
		return $this->belongsToMany('App\Tag');
    }	

	//
	// todo: this is how the article tags should be used
	// this code is currently in the tags
	// tags should be generic
	// **NOT USED YET**
	//
    public function addTagUserSbw($name)
    {
		if (Auth::check())
		{
			$this->addTag($name, Auth::id());
		}
	}
	
    public function addTagSbw($name, $userId = null)
    {
		$tag = Tag::getOrCreate($name);
		if (isset($tag))
		{
			$this->tags()->detach($tag->id); // if it's already tagged, remove it so it will by updated
			$this->tags()->attach($tag->id, ['user_id' => $userId]);
		}
    }

    public function removeTagUserSBW($name)
    {
		if (Auth::check())
		{
			$this->removeTag($name);
		}
	}

    public function removeTagSBW($name)
    {
		$tag = Tag::get($name);
		if (isset($tag))
		{
			$this->tags()->detach($tag->id);
		}
    }
	
	//
	// End of new tag code
	//

    public function getSpeechLanguage()
    {
    	return Tools::getSpeechLanguage($this->language_flag);
    }
	
    protected function countView(Entry $entry)
    {		
		$entry->view_count++;
		$entry->save();	
	}
	
	// get all locations that have at least one entry record
	static public function getAdminIndex()
	{
		$q = '
			SELECT *
			FROM entries
			WHERE 1=1
			AND entries.site_id = ?
			AND entries.type_flag = ?
			AND entries.deleted_flag = 0
			AND (entries.release_flag = 0 OR entries.location_id = null)
		';
		
		$records = DB::select($q, [Tools::getSiteId(), ENTRY_TYPE_TOUR]);
		
		return $records;
	}

	// get all entries except tours
	static public function getEntries($approved_flag = false)
	{
		$q = '
			SELECT entries.id, entries.type_flag, entries.view_count, entries.title, entries.description, entries.release_flag, entries.wip_flag, entries.updated_at, entries.permalink,
				count(photos.id) as photo_count
			FROM entries
			LEFT JOIN photos
				ON photos.parent_id = entries.id AND photos.deleted_flag = 0
			WHERE 1=1
			AND entries.site_id = ?
			AND entries.deleted_flag = 0
			AND entries.type_flag <> ?
			GROUP BY entries.id, entries.type_flag, entries.view_count, entries.title, entries.description, entries.release_flag, entries.wip_flag, entries.updated_at, entries.permalink
			ORDER BY entries.release_flag ASC, entries.wip_flag ASC, entries.display_date ASC, entries.id DESC
		';
				
		$records = DB::select($q, [Tools::getSiteId(), ENTRY_TYPE_TOUR]);
		
		return $records;
	}

	static public function getArticles($limit = PHP_INT_MAX)
	{			
		$records = $record = Entry::select()
				->where('deleted_flag', 0)
				->where('entries.site_id', Tools::getSiteId())
				->where('type_flag', ENTRY_TYPE_ARTICLE)
				->where('release_flag', '>=', self::getReleaseFlag())
				->orderByRaw('display_date DESC, id DESC')
				->limit($limit)
				->get();

		return $records;
	}
	
	// get all entries for specified type
	static public function getEntriesByType($type_flag, $limit = 0, $orderBy = ORDERBY_APPROVED)
	{		
		$q = '
			SELECT *
			FROM entries
			WHERE 1=1
			AND entries.deleted_flag = 0
			AND entries.type_flag = ?
			AND entries.release_flag >= ?
		';

		if (Tools::isSuperAdmin())
		{
			// super admins can see everything
		}
		else
		{
			$q .= '	AND entries.site_id = ' . Tools::getSiteId() . ' ';
		}
				
		$orderByPhrase = 'ORDER BY entries.display_date DESC, entries.id DESC';
		
		switch($orderBy)
		{
			case ORDERBY_APPROVED:
				// already set above
				break;
			case ORDERBY_TITLE:
				$orderByPhrase = 'ORDER BY entries.title ASC';
				break;
			case ORDERBY_DATE:
				$orderByPhrase = 'ORDER BY entries.display_date DESC, entries.id DESC';
				break;
			case ORDERBY_VIEWS:
				$orderByPhrase = 'ORDER BY entries.view_count DESC';
				break;
			default:
				// already set above
				break;
		}
		
		$q .= ' ' . $orderByPhrase . ' ';
		
		if ($limit > 0)
			$q .= ' LIMIT ' . $limit . ' ';

		//dd($q);
		
		$releaseFlag = self::getReleaseFlag();
		
		$records = DB::select($q, [$type_flag, $releaseFlag, ]);
		
		return $records;
	}

	static public function getReleaseFlag()
	{
		$rc = RELEASE_PUBLIC;
		
		if (Tools::isAdmin()) // admin sees all
		{
			$rc = RELEASE_NOTSET;
		}
		else if (Tools::isPaid()) // paid member
		{
			$rc = RELEASE_PAID;			
		}
		else if (Auth::check()) // member logged in_array
		{
			$rc = RELEASE_MEMBER;
		}

		return $rc;
	}
	
	static public function get($permalink, $id = null, $site_id = null)
	{
		$permalink = Tools::permalink($permalink);
		
		$record = null;
		if (isset($permalink))
		{
			$id = intval($id); // clean the id
			
			$record = $record = Entry::select()
					->where('deleted_flag', 0)
					->where('release_flag', '>=', self::getReleaseFlag())
					->where('permalink', $permalink)
					->first();
		}

		//dd($record);	
		return $record;
	}
	
	static public function getEntry($permalink)
	{		
		$q = '
			SELECT entries.id, entries.type_flag, entries.view_count, entries.permalink, entries.title, entries.description, entries.description_short, entries.release_flag, entries.wip_flag, entries.updated_at, entries.display_date, entries.photo_id, entries.parent_id 
				, photo_main.filename as photo
				, CONCAT(photo_main.alt_text, " - ", photo_main.location) as photo_title
				, CONCAT("' . PHOTO_ENTRY_PATH . '", entries.id, "/") as photo_path
				, photo_main_gallery.filename as photo_gallery 
				, CONCAT(photo_main_gallery.alt_text, " - ", photo_main_gallery.location) as photo_gallery_title
				, CONCAT("' . PHOTO_ENTRY_PATH . '", photo_main_gallery.parent_id, "/") as photo_gallery_path
				, count(photos.id) as photo_count
				, count(photo_main_gallery.id) as photo_gallery_count
				, locations.name as location, locations.location_type as location_type
				, locations_parent.name as location_parent
			FROM entries
			LEFT JOIN photos as photo_main
				ON photo_main.parent_id = entries.id AND photo_main.main_flag = 1 AND photo_main.deleted_flag = 0 
			LEFT JOIN photos as photo_main_gallery
				ON photo_main_gallery.id = entries.photo_id AND photo_main_gallery.deleted_flag = 0 
			LEFT JOIN photos
				ON photos.parent_id = entries.id AND photos.deleted_flag = 0
			LEFT JOIN locations
				ON locations.id = entries.location_id AND locations.deleted_flag = 0
			LEFT JOIN locations as locations_parent
				ON locations_parent.id = locations.parent_id AND locations_parent.deleted_flag = 0
			WHERE 1=1
			AND entries.deleted_flag = 0
			AND entries.permalink = ?

			GROUP BY entries.id, entries.type_flag, entries.view_count, entries.permalink, 	entries.title, entries.description, entries.description_short, entries.release_flag, entries.wip_flag, entries.updated_at, entries.display_date, entries.photo_id, entries.parent_id
				, photo, photo_title, photo_path
				, photo_gallery, photo_gallery_title, photo_gallery_path
				, location, location_parent, location_type 
		
				LIMIT 1
		';
						
		$records = DB::select($q, [$permalink]);
		
		$records = count($records) > 0 ? $records[0] : null;
			
		return $records;
	}	
	
	static public function getEntryNEW($permalink)
	{		
		$q = '
			SELECT entries.id, entries.type_flag, entries.view_count, entries.permalink, entries.title, entries.description, entries.description_short, entries.release_flag, entries.wip_flag, entries.updated_at, entries.display_date, entries.photo_id, entries.parent_id, entries.site_id
				, photo_main.filename as photo
				, CONCAT(photo_main.alt_text, " - ", photo_main.location) as photo_title
				, CONCAT("' . PHOTO_ENTRY_PATH . '", entries.id, "/") as photo_path
				
				, photo_main_gallery.filename as photo_gallery 
				, CONCAT(photo_main_gallery.alt_text, " - ", photo_main_gallery.location) as photo_title_gallery
				, CONCAT("' . PHOTO_ENTRY_PATH . '", photo_main_gallery.parent_id) as photo_path_gallery

				, count(photos.id) as photo_count
				, count(photo_main_gallery.id) as photo_gallery_count
				, locations.name as location
				, locations_parent.name as location_parent
			FROM entries
			LEFT JOIN photos as photo_main
				ON photo_main.parent_id = entries.id AND photo_main.main_flag = 1 AND photo_main.deleted_flag = 0 
			LEFT JOIN photos as photo_main_gallery
				ON photo_main_gallery.id = entries.photo_id AND photo_main_gallery.deleted_flag = 0 
			LEFT JOIN photos
				ON photos.parent_id = entries.id AND photos.deleted_flag = 0
			LEFT JOIN locations
				ON locations.id = entries.location_id AND locations.deleted_flag = 0
			LEFT JOIN locations as locations_parent
				ON locations_parent.id = locations.parent_id AND locations_parent.deleted_flag = 0
			WHERE 1=1
			AND entries.deleted_flag = 0
			AND entries.permalink = ?

			GROUP BY entries.id, entries.type_flag, entries.view_count, entries.permalink, 	entries.title, entries.description, entries.description_short, entries.release_flag, entries.wip_flag, entries.updated_at, entries.display_date, entries.photo_id, entries.parent_id, entries.site_id
				, photo, photo_title, photo_path
				, photo_gallery, photo_title_gallery, photo_path_gallery
				, location, location_parent
		
				LIMIT 1
		';
						
		$records = DB::select($q, [$permalink]);
		
		$records = count($records) > 0 ? $records[0] : null;
			
		return $records;
	}
	
	static protected function getNextPrevEntry($entry, $next = true)
	{
		$record = null;
		
		if (isset($entry) && isset($entry->display_date))
		{
			$display_date = $entry->display_date;
			$id = $entry->id;
			$type_flag = $entry->type_flag;
		
			$record = Entry::select() 
				->where('entries.site_id', Tools::getSiteId())
				->where('entries.deleted_flag', 0)
				//->where('entries.release_flag', 1)
				//->where('entries.wip_flag', 1)
				->where('entries.type_flag', $type_flag)
				->where('entries.display_date', $next ? '=' : '=', $display_date)
				->where('entries.id', $next ? '>' : '<', $id)
				->orderByRaw('entries.display_date ' . ($next ? 'ASC' : 'DESC') . ', entries.id ' . ($next ? 'ASC' : 'DESC '))			
				->first();
		}
				
		return $record;
	}
	
	static protected function getEntryCount($entry_type, $allSites)
	{
		$q = '
			SELECT count(entries.id) as count
			FROM entries
			WHERE 1=1
				AND entries.deleted_flag = 0
				AND entries.release_flag = 1 
				AND entries.type_flag = ?
		';
		
		$q .= $allSites ? '' : ' AND entries.site_id = ' . Tools::getSiteId() . ' ';
		
		// get the list with the location included
		$record = DB::select($q, [$entry_type]);
		
		return intval($record[0]->count);
	}	
	
	static public function getStats()
	{
		$stats = [];
		
		$stats['articles'] = Entry::getEntryCount(ENTRY_TYPE_ARTICLE, /* allSites = */ false);
		
		return $stats;
	}
	
	// Get a setting record where $name is the record permalink and has the format 'key|value'
	static protected function getSetting($name)
	{		
		$record = null;
		try {		
			$record = Entry::select()
				->where('permalink', '=', $name)
				->where('deleted_flag', 0)
				->first();
		}
		catch(\Exception $e)
		{
			// todo: log me
			//dump($e);
		}
	
		$values = [];
		
		if (isset($record))
		{
			$lines = preg_split('/\r\n+/', $record->description, -1, PREG_SPLIT_NO_EMPTY);
		
			foreach($lines as $line)
			{	
				$parts = explode('|', $line);
				
				if (count($parts) > 1) // format is $key|$value
					$values[trim($parts[0])] = trim($parts[1]);
				else if (count($parts) == 1) // format is $key only
					$values[trim($parts[0])] = null;
				else
					; // blank line, ignored
			}
		}

		return $values;
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Tag functions: recent tag and read location
	//
	//////////////////////////////////////////////////////////////////////

	// add or update system 'recent' tag for entries.
	// this lets us order by most recent entries
    public function tagRecent()
    {
		$readLocation = 0;
		
		if (Auth::check()) // only for logged in users
		{
			$recent = self::getRecentTag();
			if (isset($recent)) // replace old one if exists
			{
				$readLocation = $this->getReadLocation($recent->id);
				$this->tags()->detach($recent->id, ['user_id' => Auth::id()]);
			}
			
			$this->tags()->attach($recent->id, ['user_id' => Auth::id(), 'read_location' => $readLocation]);
			$this->refresh();
		}
		
		return $readLocation;
    }

    static public function getRecentTag()
    {
		return Tag::getOrCreate('recent', TAG_TYPE_SYSTEM);
	}
	
    public function setReadLocation($readLocation)
    {
		$rc = false;
		
		if (Auth::check())
		{
			$recent = self::getRecentTag();
			if (isset($recent)) // replace old one if exists
				$this->tags()->detach($recent->id, ['user_id' => Auth::id()]);
			
			$this->tags()->attach($recent->id, ['user_id' => Auth::id(), 'read_location' => $readLocation]);
			$this->refresh();
			$rc = true;
		}
		
		return $rc;
	}

    private function getReadLocation($tagId)
    {
		$readLocation = 0;
		
		if (Auth::check())
		{
			$record = DB::table('entry_tag')
					->where('tag_id', $tagId)
					->where('entry_id', $this->id)
					->where('user_id', Auth::id())
					->first();
					
			if (isset($record))
			{
				$readLocation = $record->read_location;
			}
		}
		
		return intval($readLocation);
	}	

	static public function getRecentList($type, $limit = PHP_INT_MAX)
	{			
		$type = intval($type);
		$records = [];
		$tag = self::getRecentTag();
		$logInfo = 'type_flag: ' . self::getTypeFlagName($type);
		
		if (isset($tag)) // should always exist
		{
			try
			{
				$records = DB::table('entries')
					->leftJoin('entry_tag', function($join) use ($tag) {
						$join->on('entry_tag.entry_id', '=', 'entries.id');
						$join->where('entry_tag.user_id', Auth::id()); // works for users not logged in
						$join->where('entry_tag.tag_id', $tag->id);
					})			
					->select('entries.*')
					->where('entries.deleted_flag', 0)
					->where('entries.site_id', Tools::getSiteId())
					->where('entries.type_flag', $type)
					->where('entries.release_flag', '>=', self::getReleaseFlag())
					->orderByRaw('entry_tag.created_at DESC, entries.display_date DESC, entries.id DESC')
					->limit($limit)
					->get();					
			}
			catch (\Exception $e)
			{
				$msg = 'Error getting recent list';
				Event::logException(LOG_MODEL, LOG_ACTION_INDEX, 'getRecentList', $msg . ', ' . $logInfo, $e->getMessage());
				Session::flash('message.level', 'danger');
				Session::flash('message.content', $msg);				
			}	
		}
		else
		{
			$msg = 'Error getting recent list, recent tag not found';
			Event::logError(LOG_MODEL, LOG_ACTION_INDEX, 'getRecentList', $msg . ', ' . $logInfo);
			Session::flash('message.level', 'danger');
			Session::flash('message.content', $msg);				
		}

		return $records;
	}
		
	static public function getBooksRecentOLD_NOT_USED($limit = PHP_INT_MAX) // need this?
	{
		$tag = Tag::getOrCreate(TAG_BOOK, TAG_TYPE_BOOK);
		$records = null;
		if (isset($tag))
		{
			$tagRecent = self::getRecentTag();
			$records = DB::table('entries')
				->join('entry_tag', function($join) use ($tag) {
					$join->on('entry_tag.entry_id', '=', 'entries.id');
					$join->where('entry_tag.tag_id', $tag->id);
				})	
				->leftJoin('entry_tag as recent_tag', function($join) use ($tagRecent) {
					$join->on('recent_tag.entry_id', '=', 'entries.id');
					$join->where('recent_tag.user_id', Auth::id());
					$join->where('recent_tag.tag_id', $tagRecent->id);
				})							
				->select('entries.*')
				->where('entries.deleted_flag', 0)
				->where('entries.release_flag', '>=', self::getReleaseFlag())
				->orderByRaw('recent_tag.created_at DESC, entries.display_date DESC, entries.id DESC')
				->get($limit);
		}

		return $records;
	}		

	//////////////////////////////////////////////////////////////////////
	// End of Specialized - Recent Articles Tag
	//////////////////////////////////////////////////////////////////////
	
}
