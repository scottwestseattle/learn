<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use App\User;
use App\Event;
use App\Tools;
use App\Definition;

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
    static public function getIndex($sort = null, $limit = PHP_INT_MAX)
	{
		$sort = intval($sort);
		$limit = intval($limit);
		$records = [];
		$orderBy = 'title';
		switch($sort)
		{
			case 1:
				$orderBy = 'title';
				break;
			case 2:
				$orderBy = 'title desc';
				break;
			case 3:
				$orderBy = 'id desc';
				break;
			case 4:
				$orderBy = 'updated_at desc';
				break;
			case 5: // missing translation
				$orderBy = 'title';
				break;
			case 6: // missing definition
				$orderBy = 'title';
				break;
			case 7: // missing conjugation
				$orderBy = 'title';
				break;
			default:
				break;
		}

		try
		{
			if ($sort === 5)
			{
				$records = Definition::select()
					->whereNull('deleted_at')
					->whereNull('translation_en')
					->orderByRaw($orderBy)
					->limit($limit)
					->get();
			}
			else if ($sort == 6)
			{
				$records = Definition::select()
					->whereNull('deleted_at')
					->whereNull('definition')
					->orderByRaw($orderBy)
					->limit($limit)
					->get();
			}
			else if ($sort == 7)
			{
				$records = Definition::select()
					->whereNull('deleted_at')					
					->whereNotNull('conjugations')
					->where(function ($query) {$query
						->whereNull('conjugations_search')
						->orWhereRaw('LENGTH(conjugations) < 50')
						;})
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
	
    static public function get($word)
    {
		$word = Tools::alphanum($word, /* strict = */ true);
		
		$record = null;

		try
		{
			$record = Definition::select()
				->where('title', $word)
				->where('deleted_at', null)
				->first();
		}
		catch (\Exception $e)
		{
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
		$v = preg_replace('/[ ;,]+/', ';', $v); // replace one or more spaces with one ';' to separate words
		if (strlen($v) > 0)
		{
			// wrap it in ';'
			if (!Tools::startsWith($v, ';'))
				$v = ';' . $v;
			
			if (!Tools::endsWith($v, ';'))
				$v .= ';';
		}

		//dd($v);
		
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
			$rc = self::cleanConjugations($raw);
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
	
    static public function cleanConjugations($raw)
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
					// for wiki
					case 'vos':
					case 'usted':
					case 'nosotras':
					case 'vosotras':
					case 'ustedes':
					case 'ellosellas':
					case 'élellaello':
						break;
					*/
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
		
		dd($words);
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
			$conjugations[CONJ_PARTICIPLE] = ';' . $words[$index++] . ';' . $words[$index++] . ';';
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
			$msg = 'Error cleaning conjugation: total results: ' . count($words);
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
		$v = str_replace(';', ' ', $forms);
		$v = trim($v);
		$v = str_replace(' ', ', ', $v);
		
		return trim($v);
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

			//todo: need to handle multiple matches
			//if ($records->count() > 1);
			//	dd($records);
			
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
			dump($any . ': ' . $suffixes[$any] . ', word: ' . $word);
			
			// we're only looking for verbs at this point
			$record = Definition::select()
				->whereNull('deleted_at')
				->where('conjugations_search', 'LIKE', '%;' . $word . ';%')
				->first();			
		}

		return $record;
	}
	
	static public function add($title, $definition, $examples = null)
	{
		$title = strtolower($title);
	
		$record = new Definition();

		$record->user_id 		= Auth::id();
		$record->title 			= $title;
		$record->forms 			= $title;
		$record->translation_en = $definition;
		$record->language_id	= LANGUAGE_SPANISH;
		$record->permalink		= Tools::createPermalink($title);
		$record->examples		= $examples;
		
		try
		{
			$record->save();
			Event::logAdd(LOG_MODEL, $record->title, $record->description, $record->id);
		}
		catch (\Exception $e)
		{
			$msg = 'Error adding definition: ' . $title . ', ' . $definition;
			Event::logException(LOG_MODEL, LOG_ACTION_ADD, $record->title, null, $msg . ': ' . $e->getMessage());
			$rc = $msg;
		}
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
			CONJ_PARTICIPLE 		=> ['ando', 'ado'],
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
		'er' => [ //todo: not done, just copied
			CONJ_PARTICIPLE 		=> ['ando', 'ado'],
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
		'ir' => [ //todo: not done, just copies
			CONJ_PARTICIPLE 		=> ['iendo', 'ido'],
			CONJ_IND_PRESENT		=> ['o', 'as', 'a', 'amos', 'áis', 'an'],
			CONJ_IND_PRETERITE		=> ['é', 'aste', 'ó', 'amos', 'asteis', 'aron'],
			CONJ_IND_IMPERFECT		=> ['aba', 'abas', 'aba', 'ábamos', 'abais', 'aban'],
			CONJ_IND_CONDITIONAL	=> ['aría', 'arías', 'aría', 'aríamos', 'aríais', 'arían'],
			CONJ_IND_FUTURE 		=> ['aré', 'arás', 'ará', 'aremos', 'aréis', 'arán'],
			CONJ_SUB_PRESENT		=> ['e', 'es', 'e', 'emos', 'éis', 'en'],
			CONJ_SUB_IMPERFECT		=> ['ara', 'aras', 'ara', 'áramos', 'arais', 'aran'],
			CONJ_SUB_IMPERFECT2		=> ['ase', 'ases', 'ase', 'ásemos', 'aseis', 'asen'],
			CONJ_SUB_FUTURE 		=> ['are', 'ares', 'are', 'áremos', 'areis', 'aren'],
			CONJ_IMP_AFFIRMATIVE 	=> ['a', 'e', 'emos', 'ad', 'en'],
			CONJ_IMP_NEGATIVE		=> ['es', 'e', 'emos', 'éis', 'en'],
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
				$records = self::conjugateAr($text, $endings, $stem, $middle);
				
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
				$records = self::conjugateAr($text, $endings, $stem, $middle);
				
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
				$records = self::conjugateAr($text, $endings, $stemTrimmer, $middle);
				
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
				
				$records = self::conjugateAr($text, $endings, $stem, $middle);
			}
			// Case 5: regular AR verbs that don't match a pattern yet
			else if (in_array($text, self::$_regularVerbsAr))
			{
				$stem = 'ar';
				$middle = '';
				$endings = self::$_verbEndings[$stem];
				
				$records = self::conjugateAr($text, $endings, $stem, $middle);
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
	
    static private function conjugateAr($text, $endings, $stem, $middle)
    {	
		$records = null;

		// crack it up to pieces
		$parts = preg_split('/(' . $stem . '$)/', $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		if (count($parts) > 0)
		{
			$root = $parts[0]; // verb root such as 
			$records['root'] = $root;

			// participles
			for ($i = 0; $i < 2; $i++)
				$records[CONJ_PARTICIPLE][] = $root . $middle . $endings[CONJ_PARTICIPLE][$i];
								
			// indication
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_IND_PRESENT][] = $root . $middle . $endings[CONJ_IND_PRESENT][$i];
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_IND_PRETERITE][] = $root . $middle . $endings[CONJ_IND_PRETERITE][$i];
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_IND_IMPERFECT][] = $root . $middle . $endings[CONJ_IND_IMPERFECT][$i];
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_IND_CONDITIONAL][] = $root . $middle . $endings[CONJ_IND_CONDITIONAL][$i];
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_IND_FUTURE][] = $root . $middle . $endings[CONJ_IND_FUTURE][$i];
						
			// subjunctive
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_SUB_PRESENT][] = $root . $middle . $endings[CONJ_SUB_PRESENT][$i];
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_SUB_IMPERFECT][] = $root . $middle . $endings[CONJ_SUB_IMPERFECT][$i];
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_SUB_IMPERFECT2][] = $root . $middle . $endings[CONJ_SUB_IMPERFECT2][$i];
			for ($i = 0; $i < 6; $i++)
				$records[CONJ_SUB_FUTURE][] = $root . $middle . $endings[CONJ_SUB_FUTURE][$i];

			// imperative
			for ($i = 0; $i < 5; $i++)
				$records[CONJ_IMP_AFFIRMATIVE][] = $root . $middle . $endings[CONJ_IMP_AFFIRMATIVE][$i];
			for ($i = 0; $i < 5; $i++)
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
}
