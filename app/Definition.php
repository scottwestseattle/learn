<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use Auth;

use App\Definition;
use App\Entry;
use App\Event;
use App\Tools;
use App\User;

define('CONJ_PARTICIPLE', 'participle');
define('CONJ_IND_PRESENT', 'ind_pres');
define('CONJ_IND_PRETERITE', 'ind_pret');
define('CONJ_IND_IMPERFECT', 'ind_imp');
define('CONJ_IND_CONDITIONAL', 'ind_cond');
define('CONJ_IND_FUTURE', 'ind_fut');
define('CONJ_SUB_PRESENT', 'sub_pres');
define('CONJ_SUB_IMPERFECT', 'sub_imp');
define('CONJ_SUB_IMPERFECT2', 'sub_imp2');
define('CONJ_SUB_FUTURE', 'sub_fut');
define('CONJ_IMP_AFFIRMATIVE', 'imp_pos');
define('CONJ_IMP_NEGATIVE', 'imp_neg');

class Definition extends Base
{
    use SoftDeletes;
	
    public function entries()
    {
		return $this->belongsToMany('App\Entry');
    }	
	
	public function removeEntries()
	{
		foreach($this->entries as $entry)
		{
			$this->entries()->detach($entry->id);
		}
	}
	
	//////////////////////////////////////////////////////////////////////
	//
	// Tags - User Definition Favorite lists
	//
	//////////////////////////////////////////////////////////////////////
	
    public function tags()
    {
		return $this->belongsToMany('App\Tag')->wherePivot('user_id', Auth::id());
    }	

    public function addTagFavorite()
    {
		$tag = false;
		$name = 'Favorites';
		
		if (Auth::check())
		{
			$tag = Tag::getOrCreate($name, TAG_TYPE_DEFINITION_FAVORITE, Auth::id());
			if (isset($tag))
			{
				$this->tags()->detach($tag->id); // if it's already tagged, remove it so it will by updated
				$this->tags()->attach($tag->id, ['user_id' => Auth::id()]);
				$this->refresh();
				//dump($tag);
				//dd($this->tags);
			}
		}
		
		return $tag;
    }

    public function removeTagFavorite()
    {
		$rc = false;
		
		if (Auth::check())
		{
			$name = 'Favorites';
			$tag = Tag::get($name, TAG_TYPE_DEFINITION_FAVORITE, Auth::id());
			if (isset($tag))
			{
				$this->tags()->detach($tag->id);
				$rc = true;
			}
		}
		
		return $rc;
    }

    public function addTag($tagId)
    {		
		if (Auth::check())
		{
			// 0 is okay so we can use the same flow when removing a tag
			if ($tagId > 0) 
			{
				$this->tags()->detach($tagId); // if it's already tagged, remove it so it will by updated
				$this->tags()->attach($tagId, ['user_id' => Auth::id()]);
			}
		}
    }

    public function removeTags()
    {
		foreach($this->tags as $tag)
		{
			$this->removeTag($tag->id);
		}
	}
	
    public function removeTag($tagId)
    {
		if (Auth::check())
		{
			// 0 is okay so we can use the same flow for adding first tag
			if ($tagId > 0) 
				$this->tags()->detach($tagId);
		}
    }
	
    static public function getUserFavoriteLists()
    {
		$records = null;
		
		try
		{
			$records = DB::table('tags')
				->leftJoin('definition_tag', function($join) {
					$join->on('definition_tag.tag_id', '=', 'tags.id');
					$join->where('definition_tag.user_id', Auth::id());
				})	
				->select(DB::raw('tags.id, tags.name, tags.user_id, count(definition_tag.tag_id) as wc'))
				->where('tags.deleted_at', null)
				->where('tags.user_id', Auth::id())
				->where('type_flag', TAG_TYPE_DEFINITION_FAVORITE)
				->groupBy('tags.id', 'tags.name', 'tags.user_id')
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting favorite lists';
			Event::logException(LOG_MODEL_TAGS, LOG_ACTION_SELECT, 'getUserFavoriteLists', $msg, $e->getMessage());
			Tools::flash('danger', $msg);
		}		

		return $records;
    }	

    static public function getUserFavoriteListsOptions()
    {
		$options = [];
		
		$records = self::getUserFavoriteLists();
		if (isset($records))
		{
			foreach($records as $record)
			{
				$options[$record->id] = $record->name;
			}
		}
		
		return $options;
	}
	
	//////////////////////////////////////////////////////////////////////
	// End of Tag Functions
	//////////////////////////////////////////////////////////////////////
	
    static public function getIndex($sort = null, $limit = PHP_INT_MAX)
	{
		$sort = intval($sort);
		$limit = intval($limit);
		$records = [];
		$orderBy = 'title';
		switch($sort)
		{
			case DEFINITIONS_SEARCH_REVERSE:
				$orderBy = 'title desc';
				break;
			case DEFINITIONS_SEARCH_NEWEST:
				$orderBy = 'id desc';
				break;
			case DEFINITIONS_SEARCH_RECENT:
				$orderBy = 'updated_at desc';
				break;
			case DEFINITIONS_SEARCH_ALL:
			case DEFINITIONS_SEARCH_VERBS:
			case DEFINITIONS_SEARCH_MISSING_TRANSLATION:
			case DEFINITIONS_SEARCH_MISSING_DEFINITION:
			case DEFINITIONS_SEARCH_MISSING_CONJUGATION:
			case DEFINITIONS_SEARCH_WIP_NOTFINISHED:
				$limit = PHP_INT_MAX;
				break;
			default:
				break;
		}

		try
		{
			if ($sort === DEFINITIONS_SEARCH_MISSING_TRANSLATION)
			{
				$records = Definition::select()
					->whereNull('deleted_at')
					->whereNull('translation_en')
					->orderByRaw($orderBy)
					->limit($limit)
					->get();
			}
			else if ($sort == DEFINITIONS_SEARCH_MISSING_DEFINITION)
			{
				$records = Definition::select()
					->whereNull('deleted_at')
					->whereNull('definition')
					->orderByRaw($orderBy)
					->limit($limit)
					->get();
			}
			else if ($sort == DEFINITIONS_SEARCH_MISSING_CONJUGATION)
			{
				$records = Definition::select()
					->whereNull('deleted_at')
					->where('wip_flag', '<', WIP_FINISHED)
					->where(function ($query) {$query
						->where('title', 'like', '%ar')
						->orWhere('title', 'like', '%er')
						->orWhere('title', 'like', '%ir')
						;})
					->where(function ($query) {$query
						->whereNull('conjugations_search')
						->orWhereRaw('LENGTH(conjugations) < 50')
						;})
					->orderByRaw($orderBy)
					->limit($limit)
					->get();
			}
			else if ($sort == DEFINITIONS_SEARCH_WIP_NOTFINISHED)
			{
				$records = Definition::select()
					->whereNull('deleted_at')
					->where('wip_flag', '<', WIP_FINISHED)
					->orderByRaw($orderBy)
					->limit($limit)
					->get();
			}
			else if ($sort == DEFINITIONS_SEARCH_VERBS)
			{
				$records = Definition::select()
					->whereNull('deleted_at')
					->where(function ($query) {$query
						->where('title', 'like', '%ar')
						->orWhere('title', 'like', '%er')
						->orWhere('title', 'like', '%ir')
						;})
					->whereNotNull('conjugations_search')
					->whereNotNull('conjugations')
					->orderByRaw($orderBy)
					->limit($limit)
					->get();
			}
			else
			{
				$records = Definition::select()
					->whereNull('deleted_at')
					->orderByRaw($orderBy)
					->limit($limit)
					->get();
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting index';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, null, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $records;
    }

    static public function getRandomWord()
    {
		$record = null;

		try
		{
			$count = Definition::select()
				->where('deleted_at', null)
				->whereNotNull('definition')
				->whereNotNull('translation_en')
				->where('wip_flag', '>=', WIP_FINISHED)
				->count();
				
			$rnd = rand(1, $count - 1);
				
			$record = Definition::select()
				->where('deleted_at', null)
				->whereNotNull('definition')
				->whereNotNull('translation_en')
				->where('wip_flag', '>=', WIP_FINISHED)
				->orderBy('id')
				->skip($rnd)
				->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting random word';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $record;
	}
	
    static public function getById($id)
    {
		$id = intval($id);
		$record = null;

		try
		{
			$record = Definition::select()
				->where('id', $id)
				->where('deleted_at', null)
				->first();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word: ' . $id;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $record;
	}
	
    static public function get($word)
    {
		$word = Tools::alphanum($word, /* strict = */ true);
		$record = null;

		try
		{
			$record = Definition::select()
				//->whereRaw("`title` = '$word' collate utf8mb4_bin") // to distinguish between accent chars
				->where('title', $word)
				->where('deleted_at', null)
				->first();
				
			//dd($record);
		}
		catch (\Exception $e)
		{
			//dd($e);
			$msg = 'Error getting word: ' . $word;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $record;
	}

    static public function exists($word)
    {
		$word = Tools::alphanum($word, /* strict = */ true);
		$rc = 0;

		try
		{
			$rc = Definition::select()
				->where('title', $word)
				->where('deleted_at', null)
				->count();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word: ' . $word;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $rc > 0;
	}

    static public function possibleVerb($word)
    {
		$word = Tools::alpha($word);
		
		$rc = Tools::endsWithAny($word, array_merge(self::$_verbSuffixes, self::$_verbReflexiveSuffixes));
			
		return $rc;
	}
	
	// make forms easier to search line: ';one;two;three;'
    static public function formatForms($forms)
    {
		$v = Tools::alphanumpunct($forms);
		$v = preg_replace('/[;,]+/', ';', $v); // replace one or more spaces with one ';' to separate words
		$v = self::trimDelimitedString(';', $v);
		$v = self::wrapString(';', $v);

		return $v;
	}

    static public function wrapString($wrapping, $text)
    {
		if (strlen($text) > 0)
		{
			if (!Tools::startsWith($text, $wrapping))
				$text = $wrapping . $text;
			
			if (!Tools::endsWith($text, $wrapping))
				$text .= $wrapping;
		}
		
		return $text;
	}
	
	// trims each part of a non-whitespace delimited string, like: "no one; one;   two;  three" to "no one;one;two;three;"
    static public function trimDelimitedString($d, $text)
    {
		$parts = explode($d, $text);
		$v = '';
		foreach($parts as $part)
		{
			$part = trim($part);
			if (strlen($part) > 0)
				$v .= $part . $d; // put the trimmed part back followed by the delimiter
		}
		
		return $v;
	}

    static public function fixConjugations($record)
    {
		$rc = false;

		if (!isset($record))
		{
			$rc = true;
		}
		else if (isset($record->conjugations))
		{
			if (!isset($record->conjugations_search))
				$rc = true;		
		}
		
		if (isset($record->conjugations_search))
		{
			if (!isset($record->conjugations))
				$rc = true;
			else if (!Tools::startsWith($record->conjugations_search, ';'))
				$rc = true;			
			else if (!Tools::endsWith($record->conjugations_search, ';'))
				$rc = true;			
		}
			
		return $rc;
	}
	
    static public function getConjugations($raw)
    {
		$rc['full'] = null;		// full conjugations
		$rc['search'] = null;	// conjugations list that can be searched (needed for reflexive conjugations like: 'nos acordamos')
		
		if (!isset($raw))
			return $rc; // nothing to do
		
		// quick check to see if it's raw or has already been formatted
		$parts = explode('|', $raw); 
		if (count($parts) === 12 && Tools::startsWith($parts[11], ';no '))
		{
			// already cleaned and formatted
			$rc['full'] = $raw;
			$rc['search'] = self::getConjugationsSearch($raw);
		}
		else
		{
			// looks raw so attempt to clean it
			// returns both 'full' and 'search'
			$rc = self::cleanConjugationsPasted($raw);
		}
	
		return $rc;
	}

	// make the search string either from a word array or from a full conjugation
    static public function getConjugationsSearch($words)
    {
		$rc = null;
		
		if (!is_array($words))
		{
			// make the words array first
			// raw conjugation looks like: |;mato;mata;matas;|mate;mate;matamos;|
			$tenses = [];
			$lines = explode('|', $words);
			foreach($lines as $line)
			{
				$parts = explode(';', $line);
				if (count($parts) > 0)
				{
					foreach($parts as $part)
					{
						// fix the reflexives
						if (Tools::startsWithAny($part, ['me ', 'te ', 'se ', 'nos ', 'os ', 'no te ', 'no se ', 'no nos ', 'no os ', 'no se ']))
						{
							// chop off the reflexive prefix words, like 'me acuerdo', 'no se acuerden'
							$pieces = explode(' ', $part);
							if (count($pieces) > 2)
								$part = $pieces[2];
							else if (count($pieces) > 1)
								$part = $pieces[1];
							else if (count($pieces) > 0)
								$part = $pieces[0];
						}
						
						$tenses[] = $part;
					}
				}
			}
			
			$words = $tenses;
		}
		
		if (isset($words) && is_array($words))
		{
			$unique = [];
			foreach($words as $word)
			{
				if (strlen($word) > 0)
				{
					if (!in_array($word, $unique))
					{
						$unique[] = $word;
						$rc .= $word . ';';
					}
				}
			}
			
			$rc = ';' . $rc; // make it mysql searchable for exact match, like: ";voy;vea;veamos;ven;vamos;
		}
	
		return $rc;
	}

    static public function cleanConjugationsScraped($raw)
    {		
		$rc['full'] = null;	  // full conjugations
		$rc['search'] = null; // conjugations list that can be searched (needed for reflexive conjugations like: 'nos acordamos')
		$conj = '';
		$search = '';
		
		if (!isset($raw))
			return null;
		
		$words = [];

		//$pos = strpos($raw, 'obtengo'); // 70574
		//dump($pos);
		//$pos = strpos($raw, 'play translation audio'); // 
		//dump($pos);
		preg_match_all('/aria-label\=\"(.*?)\"/is', substr($raw, 50000), $parts);
		//$parts = $parts[1];
		
		// figure out where the start and end are
		$start = 0;
		$end = 0;

		//dd($parts);
		$parts = $parts[1];
		$matches = count($parts);
		$participle = '';
		if ($matches >= 150) // use the exact number so we can tell if we get unexpected results
		{	
			
			// fix up the array first
			$words = [];
			$wordsPre = [];
			$word = '';
			foreach($parts as $part)
			{
				switch($part)
				{
					case 'Spanishdict Homepage':
					case 'SpanishDict logo':
					case 'more':
					case 'Menu':
					case 'Enter a Spanish verb':
					case 'Search':
					case 'play headword audio':
					case 'play translation audio':
					case 'Preterite':
					case 'Imperfect':
					case 'Present':
					case 'Subjunctive':
						break;
					default:
						$word = $part;
						$words[] = $word;					
						break;
				}

				if (Tools::startsWith($word, 'estoy '))
					$wordsPre[] = substr($word, strlen('estoy '));
				else if (Tools::startsWith($word, 'he '))
				{
					$wordsPre[] = substr($word, strlen('he '));
					break;
				}
			}

			// put the pre at the beginning
			$words = array_merge($wordsPre, $words);
			//dbg dump($words);

			// do a pass to create the search string
			$searchUnique = [];
			foreach($words as $word)
			{
				// remove the no from the imperatives
				if (Tools::startsWith($word, 'no '))
				{
					$word = substr($word, strlen('no ')); // remove the "no"
				}
				
				// check unique array to only add a word once to the search string
				if (!in_array($word, $searchUnique))
				{
					$searchUnique[] = $word;
					$search .= $word . ';';								
				}	
			}		
			
			//
			// save the conjugations
			//
			
			// participles
			$participleStem = Tools::str_truncate($words[1], 1);

			$offset = 5;
			$index = 0;
			$participleStem = Tools::str_truncate($words[1], 1);
			$conjugations[CONJ_PARTICIPLE] = ';' 
				. $words[$index++] 				// abarcando
				. ';' . $words[$index++] 		// abarcado
				. ';' . $participleStem . 'os' 	// abarcados
				. ';' . $participleStem . 'a' 	// abarcada
				. ';' . $participleStem . 'as' 	// abarcadas
				. ';';
			$conj .= $conjugations[CONJ_PARTICIPLE]; // save the conjugation string

			// indicative
			$factor = 1;
			$conjugations[CONJ_IND_PRESENT] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';		
			$conj .= '|' . $conjugations[CONJ_IND_PRESENT]; // save the conjugation string
			
			$factor = 1; $index++;
			$conjugations[CONJ_IND_PRETERITE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';		
			$conj .= '|' . $conjugations[CONJ_IND_PRETERITE]; // save the conjugation string
			
			$factor = 1; $index++;
			$conjugations[CONJ_IND_IMPERFECT] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';		
			$conj .= '|' . $conjugations[CONJ_IND_IMPERFECT]; // save the conjugation string
			
			$factor = 1; $index++;
			$conjugations[CONJ_IND_CONDITIONAL] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';		
			$conj .= '|' . $conjugations[CONJ_IND_CONDITIONAL]; // save the conjugation string
			
			$factor = 1; $index++;
			$conjugations[CONJ_IND_FUTURE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_IND_FUTURE]; // save the conjugation string

			// subjunctive
			$offset = 4;
			$factor = 1; 
			$index += 26;
			$conjugations[CONJ_SUB_PRESENT] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_SUB_PRESENT]; // save the conjugation string

			$factor = 1; $index++;
			$conjugations[CONJ_SUB_IMPERFECT] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_SUB_IMPERFECT]; // save the conjugation string

			$factor = 1; $index++;
			$conjugations[CONJ_SUB_IMPERFECT2] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_SUB_IMPERFECT2]; // save the conjugation string

			$factor = 1; $index++;
			$conjugations[CONJ_SUB_FUTURE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_SUB_FUTURE]; // save the conjugation string

			// imperatives
			$offset = 2;
			$factor = 1; 
			$index += 21;
			$conjugations[CONJ_IMP_AFFIRMATIVE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' ;		
			$conj .= '|' . $conjugations[CONJ_IMP_AFFIRMATIVE]; // save the conjugation string

			$factor = 1; $index++;		
			$conjugations[CONJ_IMP_NEGATIVE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_IMP_NEGATIVE]; // save the conjugation string
			
			//dbg dd($conjugations);
		}
		else
		{
			$msg = 'Error cleaning scraped conjugation: total results: ' . count($words);
			//dd($words);
			throw new \Exception($msg);
		}
		
		$rc['full'] = $conj;
		$rc['search'] = $search;
				
		return $rc;
	}
	
    static public function cleanConjugationsPasted($raw)
    {		
		$rc['full'] = null;		// full conjugations
		$rc['search'] = null;	// conjugations list that can be searched (needed for reflexive conjugations like: 'nos acordamos')
		
		if (!isset($raw))
			return null;
		
		$words = [];
		$v = str_replace(';', ' ', $raw); 	// replace all ';' with spaces
		$v = Tools::alpha($v, true);			// clean it up
		$v = preg_replace('/[ *]/i', '|', $v);	// replace all spaces with '|'
		$parts = explode('|', $v);
		//dd($parts);
		$prefix = null;
		$search = null;
		$searchUnique = [];
		foreach($parts as $part)
		{			
			$word = mb_strtolower(trim($part));

			if (strlen($word) > 0)
			{
				// the clean is specific to the verb conjugator in SpanishDict.com
				switch($word)
				{
					case 'participles':
					case 'are':
					case 'present':
					case '1':
					case '2':
					case 'affirmative':
					case 'conditional':
					case 'ellosellasuds':
					case 'future':
					case 'imperfect':
					case 'imperative':
					case 'in':
					case 'indicative':
					case 'irregularities':
					case 'negative':
					case 'nosotros':
					case 'past':
					case 'preterite':
					case 'red':
					case 'subjunctive':
					case 'ud':
					case 'uds':
					case 'vosotros':
					case 'yo':
					case 'tú':
					case 'élellaud':
					/*
					// for wiki, not done because the conjugations are in a different order
					case 'vos':
					case 'usted':
					case 'nosotras':
					case 'vosotras':
					case 'ustedes':
					case 'ellosellas':
					case 'élellaello':
					*/
						break;
					case 'no': // non reflexives with two words
						$prefix = $word; // we need the 'no'
						break;	
					default:
					{
						// do this before the 'no' is added
						// check unique array to only add a word once to the search string
						if (!in_array($word, $searchUnique))
						{
							switch($word)
							{
								case 'me': // skip reflexive prefixes
								case 'te':
								case 'se':
								case 'nos':
								case 'os':
									break;
								default:
									$searchUnique[] = $word;
									$search .= $word . ';';								
									break;
							}
						}
						
						if (isset($prefix)) // save the 'no' and use it
						{
							$word = $prefix . ' ' . $word;
							$prefix = null;
						}

						$words[] = $word;
						break;
					}
				}
			}
		}
		
		//dd($words);
		$search = isset($search) ? ';' . $search : null;
				
		$count = count($words);
		if ($count == 125) // it's reflexive so need more touch up
		{
			$parts = [];
			foreach($words as $word)
			{			
				switch($word)
				{
					case 'me': // reflexive prefixes
					case 'te':
					case 'se':
					case 'nos':
					case 'os':
					case 'no te':
					case 'no se':
					case 'no nos':
					case 'no os':
						$prefix = $word;
						break;
					default:
					{
						if (isset($prefix)) // save the 'no' and use it
						{
							$word = $prefix . ' ' . $word;
							$prefix = null;
						}

						$parts[] = $word;
						break;
					}
				}
			}			

			$words = $parts;
			//dd($parts);
		}
		
		$conj = null;
		$count = count($words);
		//dd($words);
		if ($count == 66) // total verb conjugations
		{
			//
			// save the conjugations
			//
			$conj = '';
			
			// participles
			$offset = 5;
			$index = 0;
			$participleStem = Tools::str_truncate($words[1], 1);
			$conjugations[CONJ_PARTICIPLE] = ';' 
				. $words[$index++] 				// abarcando
				. ';' . $words[$index++] 		// abarcado
				. ';' . $participleStem . 'os' 	// abarcados
				. ';' . $participleStem . 'a' 	// abarcada
				. ';' . $participleStem . 'as' 	// abarcadas
				. ';';
			$conj .= $conjugations[CONJ_PARTICIPLE]; // save the conjugation string
			
			// indicative
			$factor = 1;
			$conjugations[CONJ_IND_PRESENT] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';		
			$conj .= '|' . $conjugations[CONJ_IND_PRESENT]; // save the conjugation string
			
			$factor = 1; $index++;
			$conjugations[CONJ_IND_PRETERITE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';		
			$conj .= '|' . $conjugations[CONJ_IND_PRETERITE]; // save the conjugation string
			
			$factor = 1; $index++;
			$conjugations[CONJ_IND_IMPERFECT] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';		
			$conj .= '|' . $conjugations[CONJ_IND_IMPERFECT]; // save the conjugation string
			
			$factor = 1; $index++;
			$conjugations[CONJ_IND_CONDITIONAL] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';		
			$conj .= '|' . $conjugations[CONJ_IND_CONDITIONAL]; // save the conjugation string
			
			$factor = 1; $index++;
			$conjugations[CONJ_IND_FUTURE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_IND_FUTURE]; // save the conjugation string

			// subjunctive
			$offset = 4;
			$factor = 1; 
			$index += 26;
			$conjugations[CONJ_SUB_PRESENT] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_SUB_PRESENT]; // save the conjugation string

			$factor = 1; $index++;
			$conjugations[CONJ_SUB_IMPERFECT] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_SUB_IMPERFECT]; // save the conjugation string

			$factor = 1; $index++;
			$conjugations[CONJ_SUB_IMPERFECT2] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_SUB_IMPERFECT2]; // save the conjugation string

			$factor = 1; $index++;
			$conjugations[CONJ_SUB_FUTURE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_SUB_FUTURE]; // save the conjugation string

			// imperatives
			$offset = 2;
			$factor = 1; 
			$index += 21;
			$conjugations[CONJ_IMP_AFFIRMATIVE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' ;		
			$conj .= '|' . $conjugations[CONJ_IMP_AFFIRMATIVE]; // save the conjugation string

			$factor = 1; $index++;		
			$conjugations[CONJ_IMP_NEGATIVE] = ';' . $words[$index] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';' . $words[$index + ($offset * $factor++)] . ';';
			$conj .= '|' . $conjugations[CONJ_IMP_NEGATIVE]; // save the conjugation string
			
			//dd($conjugations);
		}
		else
		{
			$msg = 'Error cleaning pasted conjugation: total results: ' . count($words);
			//dd($words);
			throw new \Exception($msg);
		}

		$rc['full'] = $conj;
		$rc['search'] = $search;
		
		return $rc;
	}	

    static public function getConjugationsPretty($conj)
    {
		$tenses = null;
		if (isset($conj))
		{
			// raw conjugation looks like: |;mato;mata;matas;|mate;mate;matamos;|
			$tenses = [];
			$parts = explode('|', $conj);
			foreach($parts as $part)
			{
				$part = trim($part);
				if (strlen($part) > 0)
				{
					$part = trim($part, ";");
					$part = str_replace(';', ', ', $part);
					$tenses[] = $part;
				}
			}
		}
		//dd($tenses);
		
/* output:
  0 => "siendo, sido"
  1 => "soy, eres, es, somos, sois, son"
  2 => "fui, fuiste, fue, fuimos, fuisteis, fueron"
  3 => "era, eras, era, éramos, erais, eran"
  4 => "sería, serías, sería, seríamos, seríais, serían"
  5 => "seré, serás, será, seremos, seréis, serán"
  6 => "sea, seas, sea, seamos, seáis, sean"
  7 => "fuera, fueras, fuera, fuéramos, fuerais, fueran"
  8 => "fuese, fueses, fuese, fuésemos, fueseis, fuesen"
  9 => "fuere, fueres, fuere, fuéremos, fuereis, fueren"
  10 => "sé, sea, seamos, sed, sean"
  11 => "no seas, no sea, no seamos, no seáis, no sean"	
*/	
		return $tenses;
	}

    static public function getFormsPretty($forms)
    {
		$v = preg_replace('/^;(.*);$/', "$1", $forms);
		$v = str_replace(';', ', ', $v);
		return $v;
	}
	
	// search checks title and forms
    static public function searchPartial($word)
    {
		$word = Tools::alpha($word);
		$records = null;
		
		if (!isset($word))
		{
			// show full list
			return Definition::getIndex();
		}

		try
		{			
			$records = Definition::select()
				->where('deleted_at', null)
				->where(function ($query) use ($word){$query
					->where('title', 'LIKE', $word . '%')							// exact match of title
					->orWhere('forms', 'LIKE', '%;' . $word . ';%')					// exact match of ";word;"
					->orWhere('conjugations_search', 'LIKE', '%;' . $word . '%;%') 	// exact match of ";word;"
					;})
				->orderBy('title')
				->get();

			if (false && !isset($record)) // not yet
			{
				$record = self::searchDeeper($word);
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word: ' . $word;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}
		
		//dd($records);

		return $records;
	}	
	
	// search checks title and forms
    static public function searchGeneral($word)
    {
		$word = Tools::alpha($word);
		$records = null;
		
		if (!isset($word))
		{
			// show full list
			return Definition::getIndex();
		}

		try
		{			
			$records = Definition::select()
				->where('deleted_at', null)
				->where(function ($query) use ($word){$query
					->where('title', 'LIKE', $word . '%')						
					->orWhere('forms', 'LIKE', '%' . $word . '%')				
					->orWhere('conjugations_search', 'LIKE', '%' . $word . '%') 
					->orWhere('translation_en', 'LIKE', '%' . $word . '%') 
					;})
				->orderBy('title')
				->get();

			if (false && !isset($record)) // not yet
			{
				$record = self::searchDeeper($word);
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word: ' . $word;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}
		
		//dd($records);

		return $records;
	}	
	
	// search checks title and forms
    static public function search($word)
    {
		$word = Tools::alphanum($word, /* strict = */ true);
		$record = null;

		try
		{			
			$record = Definition::select()
				->where('deleted_at', null)
				->where(function ($query) use ($word){$query
					->where('title', $word)											// exact match of title
					->orWhere('forms', 'LIKE', '%;' . $word . ';%')					// exact match of ";word;"
					->orWhere('conjugations_search', 'LIKE', '%;' . $word . ';%') 	// exact match of ";word;"
					;})
				->first();
			
			if (!isset($record))
			{
				$record = self::searchDeeper($word);
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting word: ' . $word;
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $word, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}
		
		//dd($record);

		return $record;
	}	

	// handle special cases:
	// imperative: haz, haga, hagamos, hagan + me, melo, se, selo, nos, noslo
	// hazme, hazmelo, haznos, haznoslo
	// hagame, hágamelo, hagase, hágaselo, haganos, háganoslo
	// hagámoslo, hagámosle
	// haganme, haganmelo, haganse, haganselo
	// hacerme, hacermelo, hacernos, hacernoslo, hacerse, hacerselo	
	static public function searchDeeper($word)
	{
		$wordRaw = $word;
		$record = null;

		$suffixes = [
			'me',    'te',    'se',    'nos',
			'melo',  'telo',  'selo',  'noslo',
			'mela',  'tela',  'sela',  'nosla',
			'melos', 'telos', 'selos', 'noslos',
			'melas', 'telas', 'selas', 'noslas',
		];
		
		$any = Tools::endsWithAnyIndex($word, $suffixes);
		if ($any !== false)
		{
			// trim off the suffix and search for the stem which should be the imperative
			$word = rtrim($word, $suffixes[$any]);
			$wordReflexive = $word . 'se';
			
			//dump($any . ': ' . $suffixes[$any] . ', word: ' . $word);
			
			// we're only looking for verbs at this point
			$record = Definition::select()
				->where('deleted_at', null)
				->where(function ($query) use ($word, $wordReflexive){$query
					->where('title', $word)											// exact match of title
					->orWhere('title', $wordReflexive)								// exact match of reflexive
					->orWhere('forms', 'LIKE', '%;' . $word . ';%')					// exact match of ";word;"
					->orWhere('conjugations_search', 'LIKE', '%;' . $word . ';%') 	// exact match of ";word;"
					;})
				->first();
				
			if (!isset($record))
				Event::logInfo(LOG_MODEL, LOG_ACTION_SEARCH, 'searchDeeper, not found: ' . $wordRaw . ', search word: ' . $word);
		}

		return $record;
	}
	
	static public function add($title, $definition, $translation, $examples = null)
	{
		$title = mb_strtolower($title);
	
		$record = new Definition();

		$record->user_id 		= Auth::id(); // null is ok
		$record->title 			= $title;
		$record->forms 			= $title;
		$record->definition		= $definition;
		$record->translation_en = $translation;
		$record->examples		= $examples;
		$record->language_id	= LANGUAGE_SPANISH;
		$record->permalink		= Tools::createPermalink($title);
		
		try
		{
			$record->save();
			Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding definition: ' . $title . ', ' . $definition;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			$record = null;
		}
		
		return $record;
	}

    public function updateLastViewedTime()
    {
        $rc = false;

		try
		{
			// update the record's timestamp so it will move to the back of the list
			$this->last_viewed_at = Tools::getTimestamp();
			$this->view_count++;
			$this->save();

			$rc = true;
		}
		catch (\Exception $e)
		{
			$msg = 'Error updating last viewed timestamp';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, 'id = ' . $this->id, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $rc;
    }
	
    public function getPrev()
    {
		return self::getPrevNext($this->id);
	}

    public function getNext()
    {
		return self::getPrevNext($this->id, /* $prev = */ false);
	}

    static private function getPrevNext($id, $prev = true)
    {
		$record = null;

		try
		{
			// get prev or next word by id, null is okay
			$record = Definition::select()
				->where('id', $prev ? '<' : '>', $id)
				->whereNull('deleted_at')
				->orderBy('title')
				->first();

			//dd($record);
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting prev/next record';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, null, null, $msg . ': ' . $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $record;
    }	
	
	// á é í ó	
	static private $_verbEndings = [
		'ar' => [
			CONJ_PARTICIPLE 		=> ['ando', 'ado', 'ados', 'ada', 'adas'],
			CONJ_IND_PRESENT 		=> ['o', 'as', 'a', 'amos', 'áis', 'an'],
			CONJ_IND_PRETERITE 		=> ['é', 'aste', 'ó', 'amos', 'asteis', 'aron'],
			CONJ_IND_IMPERFECT 		=> ['aba', 'abas', 'aba', 'ábamos', 'abais', 'aban'],
			CONJ_IND_CONDITIONAL 	=> ['aría', 'arías', 'aría', 'aríamos', 'aríais', 'arían'],
			CONJ_IND_FUTURE 		=> ['aré', 'arás', 'ará', 'aremos', 'aréis', 'arán'],
			CONJ_SUB_PRESENT 		=> ['e', 'es', 'e', 'emos', 'éis', 'en'],
			CONJ_SUB_IMPERFECT 		=> ['ara', 'aras', 'ara', 'áramos', 'arais', 'aran'],
			CONJ_SUB_IMPERFECT2 	=> ['ase', 'ases', 'ase', 'ásemos', 'aseis', 'asen'],
			CONJ_SUB_FUTURE 		=> ['are', 'ares', 'are', 'áremos', 'areis', 'aren'],
			CONJ_IMP_AFFIRMATIVE 	=> ['a', 'e', 'emos', 'ad', 'en'],
			CONJ_IMP_NEGATIVE		=> ['es', 'e', 'emos', 'éis', 'en'],
		],
		'er' => [
			CONJ_PARTICIPLE 		=> ['iendo', 'ido', 'idos', 'ida', 'idas'],
			CONJ_IND_PRESENT 		=> ['o', 'es', 'e', 'emos', 'éis', 'en'],
			CONJ_IND_PRETERITE 		=> ['í', 'iste', 'ió', 'imos', 'isteis', 'ieron'],
			CONJ_IND_IMPERFECT 		=> ['ía', 'ías', 'ía', 'íamos', 'íais', 'ían'],
			CONJ_IND_CONDITIONAL 	=> ['ería', 'erías', 'ería', 'eríamos', 'eríais', 'erían'],
			CONJ_IND_FUTURE 		=> ['eré', 'erás', 'erá', 'eremos', 'eréis', 'erán'],
			CONJ_SUB_PRESENT 		=> ['a', 'as', 'a', 'amos', 'áis', 'an'],
			CONJ_SUB_IMPERFECT 		=> ['iera', 'ieras', 'iera', 'iéramos', 'ierais', 'ieran'],
			CONJ_SUB_IMPERFECT2 	=> ['iese', 'ieses', 'iese', 'iésemos', 'ieseis', 'iesen'],
			CONJ_SUB_FUTURE 		=> ['iere', 'ieres', 'iere', 'iéremos', 'iereis', 'ieren'],
			CONJ_IMP_AFFIRMATIVE 	=> ['e', 'a', 'amos', 'ed', 'an'],
			CONJ_IMP_NEGATIVE		=> ['as', 'a', 'amos', 'áis', 'an'],
		],
		'ir' => [ 			
			CONJ_PARTICIPLE 		=> ['iendo', 'ido', 'idos', 'ida', 'idas'],
			CONJ_IND_PRESENT 		=> ['o', 'es', 'e', 'imos', 'ís', 'en'],
			CONJ_IND_PRETERITE 		=> ['í', 'iste', 'ió', 'imos', 'isteis', 'ieron'],
			CONJ_IND_IMPERFECT 		=> ['ía', 'ías', 'ía', 'íamos', 'íais', 'ían'],
			CONJ_IND_CONDITIONAL 	=> ['iría', 'irías', 'iría', 'iríamos', 'iríais', 'irían'],
			CONJ_IND_FUTURE 		=> ['iré', 'irás', 'irá', 'iremos', 'iréis', 'irán'],
			CONJ_SUB_PRESENT 		=> ['a', 'as', 'a', 'amos', 'áis', 'an'],
			CONJ_SUB_IMPERFECT 		=> ['iera', 'ieras', 'iera', 'iéramos', 'ierais', 'ieran'],
			CONJ_SUB_IMPERFECT2 	=> ['iese', 'ieses', 'iese', 'iésemos', 'ieseis', 'iesen'],
			CONJ_SUB_FUTURE 		=> ['iere', 'ieres', 'iere', 'iéremos', 'iereis', 'ieren'],
			CONJ_IMP_AFFIRMATIVE 	=> ['e', 'a', 'amos', 'id', 'an'],
			CONJ_IMP_NEGATIVE		=> ['as', 'a', 'amos', 'áis', 'an'],
		],
	];
		
	static private $_irregularVerbs = [
		'tropezar',
		'tener',
		'poder',
		'ser',
		'poner',
		'estar',
		'tropezar',
	];

	static private $_irregularVerbEndings = [
		'guir', 
		'ger', 
		'gir', 
		'cer', 
		'ucir', 
	];

	static private $_regularVerbsAr = [ // needed for verbs that don't match a pattern
		'amar',
	];
	
	static private $_verbSuffixes = ['ar', 'er', 'ir'];
	static private $_verbReflexiveSuffixes = ['arse', 'erse', 'irse'];

    static public function canConjugate($word)
    {
		$rc = false;
		
		if (self::possibleVerb($word))
		{
			//todo: doing it the hard way, make a simple way to check if we can gen it
			$conj = self::conjugationsGen($word);
			$rc = isset($conj['records']);
		}
		
		return $rc;
	}
	
    static public function isIrregular($word)
    {
		$rc['irregular'] = false;
		$rc['conj'] = [];
		$rc['error'] = null;
		
		$word = Tools::alphanum($word);
		$url = "https://www.spanishdict.com/conjugate/" . $word;
		
		$opciones = array(
		  'https'=>array(
			'method'=>"GET",
		  )
		);		
		$contexto = stream_context_create($opciones);
		
		try 
		{
			$raw = file_get_contents($url, false, $contexto);
			$pos = strpos($raw, 'Irregularities are in');
			if ($pos !== false)
			{
				$rc['irregular'] = true;
				$rc['conj'] = self::cleanConjugationsScraped($raw);
			}			
		}
		catch (\Exception $e)
		{
			$msg = 'Error conjugating: ' . $word;
			
			if (strpos($e->getMessage(), '401') !== FALSE)
			{
				$msg .= ' - 401 Unauthorized';
			}
			
			Event::logException(LOG_MODEL, LOG_ACTION_TRANSLATE, $msg, null, $e->getMessage());
			//$result = $msg;
			//$rc['error'] = $msg;
			//$rc['found'] = $found;
			//return $rc;
		}
		
		return $rc;
	}
	
    static public function conjugationsGen($text)
    {
		$records = null;
		$rc['forms'] = null;
		$rc['formsPretty'] = null;
		$rc['records'] = null;
		$rc['status'] = null;
		
		$parts = null;
		$text = Tools::alphanum($text);
		if (isset($text)) // anything left?
		{			
			// find the right pattern
			if (in_array($text, self::$_irregularVerbs))
			{
				// Case 1: matches specific verbs in irregular list
				$rc['status'] = 'irregular verb not implemented yet';
			}
			else if (Tools::endsWithAny($text, self::$_irregularVerbEndings))
			{
				// Case 2: matches irregular pattern
				$rc['status'] = 'verb with irregular pattern not implemented yet';
			}
			// Case 3: ends with 'azar', 'ezar', 'ozar': aplazar, bostezar, gozar
			else if (strlen($text) > strlen('azar') && Tools::endsWithAny($text, ['azar', 'ozar', 'ezar']))
			{
				$stem = 'zar';
				$middle = 'z';
				$middleIrregular = 'c';
				$endings = self::$_verbEndings['ar'];
				
				// get the regular conjugations
				$records = self::conjugate($text, $endings, $stem, $middle);
				
				// apply 4 irregular conjugations
				$root = $records['root'];
				
				for ($i = 0; $i < 6; $i++)
					$records[CONJ_SUB_PRESENT][$i] = $root . $middleIrregular . $endings[CONJ_SUB_PRESENT][$i];
				
				$records[CONJ_IND_PRETERITE][0] = $root . $middleIrregular . $endings[CONJ_IND_PRETERITE][0];
				$records[CONJ_IMP_AFFIRMATIVE][1] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][1];
				$records[CONJ_IMP_AFFIRMATIVE][2] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][2];
				$records[CONJ_IMP_AFFIRMATIVE][4] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][4];
			}
			// Case 3a: recalcar, remolcar
			else if (strlen($text) > strlen('calcar') && Tools::endsWithAny($text, ['calcar', 'molcar']))
			{
				$stem = 'car';
				$middle = 'c';
				$middleIrregular = 'qu';
				$endings = self::$_verbEndings['ar'];
				
				// get the regular conjugations
				$records = self::conjugate($text, $endings, $stem, $middle);
				
				// apply 4 irregular conjugations
				$root = $records['root'];
				
				for ($i = 0; $i < 6; $i++)
					$records[CONJ_SUB_PRESENT][$i] = $root . $middleIrregular . $endings[CONJ_SUB_PRESENT][$i];
				
				$records[CONJ_IND_PRETERITE][0] = $root . $middleIrregular . $endings[CONJ_IND_PRETERITE][0];
				$records[CONJ_IMP_AFFIRMATIVE][1] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][1];
				$records[CONJ_IMP_AFFIRMATIVE][2] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][2];
				$records[CONJ_IMP_AFFIRMATIVE][4] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][4];
			}	
			// Case 3b: 'o' stem change: volcar
			else if (false && strlen($text) > strlen('olcar') && Tools::endsWithAny($text, ['olcar']))
			{
			//todo: same as??? 'olver', >>> revolver, revolcar, volver, resolver, devolver
			//todo: what about: 'over' >> mover: mueve / llover: llueve 
				$stemTrimmer = 'car';
				$stemChange = 'ue';
				$middle = 'c';
				$middleIrregular = 'qu';
				$endings = self::$_verbEndings['ar'];
				
				// get the regular conjugations
				$records = self::conjugate($text, $endings, $stemTrimmer, $middle);
				
				// apply irregular conjugations
				$root = $records['root'];
				$rootIrregular = 'vuelc';
				// vol
				// vuelc
				
				//todo: NOT DONE YET!!! do the irregulars...
				for ($i = 0; $i < 6; $i++)
					$records[CONJ_SUB_PRESENT][$i] = $root . $middleIrregular . $endings[CONJ_SUB_PRESENT][$i];
				
				$records[CONJ_IND_PRETERITE][0] = $root . $middleIrregular . $endings[CONJ_IND_PRETERITE][0];
				$records[CONJ_IMP_AFFIRMATIVE][1] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][1];
				$records[CONJ_IMP_AFFIRMATIVE][2] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][2];
				$records[CONJ_IMP_AFFIRMATIVE][4] = $root . $middleIrregular . $endings[CONJ_IMP_AFFIRMATIVE][4];
			}				
			// Case 4: vegular AR verbs that such as: acarrear, rodear, matar, llamar, tramar, increpar
			else if (strlen($text) > strlen('rear') && Tools::endsWithAny($text, ['rear', 'dear', 'tar', 'amar', 'ivar', 'epar']))
			{
				$stem = 'ar';
				$middle = '';
				$endings = self::$_verbEndings[$stem];
				
				$records = self::conjugate($text, $endings, $stem, $middle);
			}
			// Case 5: regular AR verbs that don't match a pattern yet
			else if (in_array($text, self::$_regularVerbsAr))
			{
				$stem = 'ar';
				$middle = '';
				$endings = self::$_verbEndings[$stem];
				
				$records = self::conjugate($text, $endings, $stem, $middle);
			}
			// Case 6: conjugate all AR verbs as regular
			else if (Tools::endsWith($text, 'ar'))
			{
				$stem = 'ar';
				$middle = '';
				$endings = self::$_verbEndings[$stem];
				
				$records = self::conjugate($text, $endings, $stem, $middle);
			}
			// Case 7: conjugate all ER verbs as regular
			else if (Tools::endsWith($text, 'er'))
			{
				$stem = 'er';
				$middle = '';
				$endings = self::$_verbEndings[$stem];
				
				$records = self::conjugate($text, $endings, $stem, $middle);
			}
			// Case 8: conjugate all IR verbs as regular
			else if (Tools::endsWith($text, 'ir'))
			{
				$stem = 'ir';
				$middle = '';
				$endings = self::$_verbEndings[$stem];
				
				$records = self::conjugate($text, $endings, $stem, $middle);
			}
			else
			{
				// verb case not handled yet
				$rc['status'] = 'verb pattern not implemented yet';
			}
			
			if (isset($records))
			{
				$rc['forms'] = self::getConjugationsGenString($records);
				$rc['formsPretty'] = self::getConjugationsGenString($records, /* pretty = */ true);
				$rc['records'] = $records;			
			}
		}
				
		//dd($rc);
		
		return $rc;
	}		
	
	//
	// used when we are gening our own conjugations (most regular only)
	//
    static private function conjugate($text, $endings, $stem, $middle)
    {	
		$records = null;

		// crack it up to pieces
		$parts = preg_split('/(' . $stem . '$)/', $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		if (count($parts) > 0)
		{
			$root = $parts[0]; // verb root such as 
			$records['root'] = $root;

			// participles
			$count = count($endings[CONJ_PARTICIPLE]);
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_PARTICIPLE][] = $root . $middle . $endings[CONJ_PARTICIPLE][$i];
						
			// indication
			$count = count($endings[CONJ_IND_PRESENT]);
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_IND_PRESENT][] = $root . $middle . $endings[CONJ_IND_PRESENT][$i];
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_IND_PRETERITE][] = $root . $middle . $endings[CONJ_IND_PRETERITE][$i];
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_IND_IMPERFECT][] = $root . $middle . $endings[CONJ_IND_IMPERFECT][$i];
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_IND_CONDITIONAL][] = $root . $middle . $endings[CONJ_IND_CONDITIONAL][$i];
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_IND_FUTURE][] = $root . $middle . $endings[CONJ_IND_FUTURE][$i];
						
			// subjunctive
			$count = count($endings[CONJ_SUB_PRESENT]);
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_SUB_PRESENT][] = $root . $middle . $endings[CONJ_SUB_PRESENT][$i];
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_SUB_IMPERFECT][] = $root . $middle . $endings[CONJ_SUB_IMPERFECT][$i];
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_SUB_IMPERFECT2][] = $root . $middle . $endings[CONJ_SUB_IMPERFECT2][$i];
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_SUB_FUTURE][] = $root . $middle . $endings[CONJ_SUB_FUTURE][$i];

			// imperative
			$count = count($endings[CONJ_IMP_AFFIRMATIVE]);
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_IMP_AFFIRMATIVE][] = $root . $middle . $endings[CONJ_IMP_AFFIRMATIVE][$i];
			for ($i = 0; $i < $count; $i++)
				$records[CONJ_IMP_NEGATIVE][] = 'no ' . $root . $middle . $endings[CONJ_IMP_NEGATIVE][$i];
			
			//dd($records);
		}
		
		return $records;
	}				
	
    static private function getConjugationsGenString($records, $pretty = false)
    {
		$rc = '';

		foreach($records[CONJ_PARTICIPLE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';
		
		foreach($records[CONJ_IND_PRESENT] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_IND_PRETERITE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_IND_IMPERFECT] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_IND_CONDITIONAL] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_IND_FUTURE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_SUB_PRESENT] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_SUB_IMPERFECT] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_SUB_IMPERFECT2] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_SUB_FUTURE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_IMP_AFFIRMATIVE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc .= '|;';

		foreach($records[CONJ_IMP_NEGATIVE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		if (!$pretty) $rc = ';' . $rc;		
		
		return $rc;
	}

    public function toggleWip()
    {
		if ($this->isFinished())
			$this->wip_flag = WIP_DEFAULT;
		else
			$this->wip_flag = WIP_FINISHED;
		
		$this->save();
		
		return $this->isFinished();
    }

    public function isFinished()
    {
    	return ($this->wip_flag == WIP_FINISHED);
    }

	static public function makeQna($records)
    {
		$qna = [];
		$cnt = 0;
		foreach($records as $record)
		{
			$question = $record->title;
			$translation = Tools::getOrSetString($record->translation_en, $question . ': translation not set');
			$definition = Tools::getOrSetString($record->definition, $question . ': definition not set');
	
            $qna[$cnt]['q'] = $question;
            $qna[$cnt]['a'] = $translation;
            $qna[$cnt]['definition'] = $definition;
            $qna[$cnt]['translation'] = $translation;
            $qna[$cnt]['extra'] = '';
            $qna[$cnt]['id'] = $record->id;
            $qna[$cnt]['ix'] = $cnt; // this will be the button id, just needs to be unique
            $qna[$cnt]['options'] = '';

			$cnt++;
		}

		//dd($qna);

		return $qna;
	}	
}
