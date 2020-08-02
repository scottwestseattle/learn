<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use DateTime;
use App;
use App\Tools;

class Entry extends Base
{
	static private $entryTypes = [
		ENTRY_TYPE_NOTSET => 'Not Set',
		ENTRY_TYPE_ARTICLE => 'Article',
		ENTRY_TYPE_ENTRY => 'Entry',
		ENTRY_TYPE_OTHER => 'Other',
	];
	
	static public function getEntryTypes()
	{		
		return self::$entryTypes;
	}
	
    public function user()
    {
    	return $this->belongsTo(User::class);
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
			AND (entries.published_flag = 0 OR entries.approved_flag = 0 OR entries.location_id = null)
		';
		
		$records = DB::select($q, [SITE_ID, ENTRY_TYPE_TOUR]);
		
		return $records;
	}

	// get all entries except tours
	static public function getEntries($approved_flag = false)
	{
		$q = '
			SELECT entries.id, entries.type_flag, entries.view_count, entries.title, entries.description, entries.published_flag, entries.approved_flag, entries.updated_at, entries.permalink,
				count(photos.id) as photo_count
			FROM entries
			LEFT JOIN photos
				ON photos.parent_id = entries.id AND photos.deleted_flag = 0
			WHERE 1=1
			AND entries.site_id = ?
			AND entries.deleted_flag = 0
			AND entries.type_flag <> ?
			GROUP BY entries.id, entries.type_flag, entries.view_count, entries.title, entries.description, entries.published_flag, entries.approved_flag, entries.updated_at, entries.permalink
			ORDER BY entries.published_flag ASC, entries.approved_flag ASC, entries.display_date ASC, entries.id DESC
		';
				
		$records = DB::select($q, [SITE_ID, ENTRY_TYPE_TOUR]);
		
		return $records;
	}
	
	static public function getArticles($limit = 10)
	{
		$records = self::getEntriesByType(ENTRY_TYPE_ARTICLE, !User::isAdmin(), $limit);
		
		//dd($records);
		
		return $records;
	}
	
	// get all entries for specified type
	static public function getEntriesByType($type_flag, $approved_flag = true, $limit = 0, $site_id = null, $orderBy = ORDERBY_APPROVED)
	{
		if (!isset($type_flag))
			return(Entry::getEntries($approved_flag));
		
		// if site_id is set use it, otherwise use the old SITE_ID constant for backwards compatibility
		$site_id = isset($site_id) && $site_id != false ? $site_id : SITE_ID;
		
		$q = '
			SELECT *
			FROM entries
			WHERE 1=1
			AND entries.deleted_flag = 0
			AND entries.type_flag = ?
			AND entries.site_id = ?
		';
				
		if ($approved_flag)
			$q .= ' AND entries.published_flag = 1 AND entries.approved_flag = 1 ';
		
		$orderByPhrase = 'ORDER BY entries.published_flag ASC, entries.approved_flag ASC, entries.display_date DESC, entries.id DESC';
		
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
		
		$records = DB::select($q, [$type_flag, $site_id]);
		
		return $records;
	}
	
	static public function get($permalink, $id = null, $site_id = null)
	{
		if (isset($permalink)) // clean the permalink
			$permalink = preg_replace("/[^a-zA-Z0-9\-]/", "", $permalink);
		
		$id = intval($id); // clean the id
		
		$record = null;
		if (User::isAdmin())
		{
			$record = Entry::select()
				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->where('permalink', $permalink)
				->first();
		}
		else
		{
			$record = Entry::select()
				->where('site_id', SITE_ID)
				->where('published_flag', 1)
				->where('approved_flag', 1)
				->where('deleted_flag', 0)
				->where('permalink', $permalink)
				->first();
		}

		//dd($record);	
		return $record;
	}
	
	static public function getEntry($permalink)
	{		
		$q = '
			SELECT entries.id, entries.type_flag, entries.view_count, entries.permalink, entries.title, entries.description, entries.description_short, entries.published_flag, entries.approved_flag, entries.updated_at, entries.display_date, entries.photo_id, entries.parent_id 
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

			GROUP BY entries.id, entries.type_flag, entries.view_count, entries.permalink, 	entries.title, entries.description, entries.description_short, entries.published_flag, entries.approved_flag, entries.updated_at, entries.display_date, entries.photo_id, entries.parent_id
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
			SELECT entries.id, entries.type_flag, entries.view_count, entries.permalink, entries.title, entries.description, entries.description_short, entries.published_flag, entries.approved_flag, entries.updated_at, entries.display_date, entries.photo_id, entries.parent_id, entries.site_id
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

			GROUP BY entries.id, entries.type_flag, entries.view_count, entries.permalink, 	entries.title, entries.description, entries.description_short, entries.published_flag, entries.approved_flag, entries.updated_at, entries.display_date, entries.photo_id, entries.parent_id, entries.site_id
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
				->where('entries.site_id', SITE_ID)
				->where('entries.deleted_flag', 0)
				//->where('entries.published_flag', 1)
				//->where('entries.approved_flag', 1)
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
				AND entries.published_flag = 1 
				AND entries.approved_flag = 1
				AND entries.type_flag = ?
		';
		
		$q .= $allSites ? '' : ' AND entries.site_id = ' . SITE_ID . ' ';
		
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

	static protected function getLocationsFromEntries($standardCountryNames)
	{
		$q = '
SELECT e.title, e.display_date, country.name FROM entries AS e
LEFT JOIN locations AS city
	ON city.id = e.location_id AND city.deleted_flag = 0
LEFT JOIN locations as country
	ON country.id = city.parent_id AND country.deleted_flag = 0
WHERE 1=1
AND e.type_flag = 4
AND e.deleted_flag = 0
AND e.approved_flag = 1
AND e.published_flag = 1
AND city.name IS NOT NULL
AND country.name IS NOT NULL
AND YEAR(e.display_date) >= 2018
ORDER BY e.display_date DESC
;
		';

		$records = null;
		try {		
			$records = DB::select($q);
		}
		catch(\Exception $e)
		{
			// todo: log me
		}
		
		$locations = [];
		$cnt = 0;
		foreach($records as $record)
		{
			$country = Tools::getStandardCountryName($standardCountryNames, $record->name);
		
			//if ($country == 'Washington')
			//	dump($record);
				
			if (!array_key_exists($country, $locations))
			{
				$record->display_date = new DateTime($record->display_date);
				$record->name = $country;
				
				$locations[$country] = $record;
			}
		}
		
		//dd('stop');

		return $locations;
	}
	
	static protected function getLocationsFromSettings($standardCountryNames)
	{		
		$record = null;
		try {		
			$record = Entry::select()
				->where('permalink', '=', 'settings-country-list')
				->where('deleted_flag', 0)
				->first();
		}
		catch(\Exception $e)
		{
			// todo: log me
			//dump($e);
		}
	
		$locations = [];
		if (isset($record))
		{
			$lines = preg_split('/\r\n+/', $record->description, -1, PREG_SPLIT_NO_EMPTY);

			foreach($lines as $country)
			{
				$country = Tools::getStandardCountryName($standardCountryNames, $country);
			
				if (!array_key_exists($country, $locations))
					$locations[$country] = $country;
			}
		}

		return $locations;
	}
	
	protected function getLatestLocationsFromBlogEntries()
	{
		$q = '
SELECT country.name, photos.filename, photos.parent_id 
FROM entries AS e
LEFT JOIN locations AS city
	ON city.id = e.location_id AND city.deleted_flag = 0
LEFT JOIN locations as country
	ON country.id = city.parent_id AND country.deleted_flag = 0
LEFT JOIN photos
	ON photos.id = e.photo_id AND photos.deleted_flag = 0
WHERE 1=1
AND e.type_flag = 4
AND e.deleted_flag = 0
AND e.approved_flag = 1
AND e.published_flag = 1
AND city.name IS NOT NULL
AND country.name IS NOT NULL
ORDER BY e.display_date DESC
;
		';

		$records = null;
		$domainName = null;
		try {		
			$records = DB::select($q);
		}
		catch(\Exception $e)
		{
			// todo: log me
			//dump($e);
		}
		
		$locations = [];			// only used for uniqueness and count
		$currentLocation = null;	// first location is the current location
		$recentLocations = '';		// recent locations are the next 10 unique location names
		
		foreach($records as $record)
		{
			// only add entries once
			if (!array_key_exists($record->name, $locations))
			{
				if (isset($currentLocation))
				{
					// locations 2 - 11 are the recent locations
					$recentLocations .= trans('geo.' . $record->name) . '<br>';
				}
				else
				{
					// first location is the current location	
						
					$currentLocation = $record->name;
					
					$currentLocationPhoto = null;
					if (isset($record->filename))
					{
						$currentLocationPhoto = '/img/entries/' . $record->parent_id . '/' . $record->filename;
					}
					else
					{
						if (!isset($domainName))
							$domainName = Tools::getServerName();
					
						$currentLocationPhoto = '/img/theme1/' . PHOTOS_PLACEHOLDER_PREFIX . $domainName . '.jpg';
					}
				}
				
				$locations[$record->name] = $record->name;
			}
				
			if (count($locations) == 11)
				break;
		}
		
		// pack up the return values
		$rc['currentLocation'] = $currentLocation;
		$rc['currentLocationPhoto'] = $currentLocationPhoto;
		$rc['recentLocations'] = $recentLocations;
		
		//dd($rc);

		return $rc;
	}
}
