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
    static public function getIndex($parent_id = null, $limit = 10000)
	{
		$records = [];

		try
		{
			$records = Definition::select()
				->whereNull('deleted_at')
				->orderBy('title')
				->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting vocabulary list';
			Event::logException('word', LOG_ACTION_SELECT, 'parent_id = ' . Tools::itoa($parent_id), null, $msg . ': ' . $e->getMessage());
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
		$word = Tools::alphanum($word, /* strict = */ true);
		
		$rc = (Tools::endsWith($word, 'ar') || Tools::endsWith($word, 'er') || Tools::endsWith($word, 'ir'));
		if ($rc == false)
			$rc = (Tools::endsWith($word, 'arse') || Tools::endsWith($word, 'erse') || Tools::endsWith($word, 'irse'));
			
		return $rc;
	}
	
	// make forms easier to search line: ';one;two;three;'
    static public function formatForms($forms)
    {		
		/* old way
		$v = str_replace('; ', ';', $v);
		$v = preg_replace('/[^\da-z; ' . Tools::getAccentChars() . ']/i', '', $v); // replace all non-alphanums except for ';'
		$v = str_replace(';;', ';', $v);
		$v = str_replace(';', '\0', $v); // just to trim the leading and trailing ';' and any spaces
		$v = trim($v);
		$v = str_replace('\0', ';', $v); // put back the non-trimmed ';'
		$v = trim($v);
		*/
		
		$v = self::cleanForms($forms);		
		
		if (strlen($v) > 0)
		{
			if (!Tools::startsWith($v, ';'))
				$v = ';' . $v;
			
			if (!Tools::endsWith($v, ';'))
				$v .= ';';
		}

		//dd($v);
		
		return $v;
	}
	
	// make forms easier to search line: ';one;two;three;'
    static public function getConjugations($raw)
    {
		$clean = $raw;
		
		// quick check to see if it's raw or has already been formatted
		$parts = explode('|', $raw); 
		if (count($parts) === 12 && Tools::startsWith($parts[11], ';no '))
		{
			// already cleaned and formatted
		}
		else
		{
			$clean = self::cleanConjugations($raw);
		}
	
		return $clean;
	}
	
    static public function cleanConjugations($raw)
    {		
		if (!isset($raw))
			return null;
		
		$words = [];
		$v = str_replace(';', ' ', $raw); 	// replace all ';' with spaces
		$v = Tools::alpha($v, true);			// clean it up
		$v = preg_replace('/[ *]/i', '|', $v);	// replace all spaces with '|'

		$parts = explode('|', $v);
		//dd($parts);
		$prefix = null;
		foreach($parts as $part)
		{			
			$word = trim($part);
			
			if (strlen($word) > 0)
			{
				// the clean is specific to the verb conjugator in SpanishDict.com
				switch($word)
				{
					case 'PARTICIPLES':
					case 'are':
					case 'Present':
					case '1':
					case '2':
					case 'Affirmative':
					case 'Conditional':
					case 'ellosellasUds':
					case 'Future':
					case 'Imperfect':
					case 'Imperative':
					case 'in':
					case 'Indicative':
					case 'Irregularities':
					case 'Negative':
					case 'nosotros':
					case 'Past':
					case 'Preterite':
					case 'red':
					case 'Subjunctive':
					case 'Ud':
					case 'Uds':
					case 'vosotros':
					case 'yo':
					case 'tú':
					case 'élellaUd':
						break;
					case 'no':
						$prefix = $word; // we need the 'no'
						break;
					default:
					{
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
		
		$conj = null;
		if (count($words) == 66) // total verb conjugations
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
			
		return $conj;
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
					->where('title', $word)
					->orWhere('forms', 'LIKE', '%;' . $word . ';%')
					;})
				->first();
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
			CONJ_PARTICIPLE => ['ando', 'ado'],
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
			CONJ_IMP_NEGATIVE 		=> ['', '', '', '', '', ''],	
		],
		'er' => [
			CONJ_PARTICIPLE => ['iendo', 'ido'],
			CONJ_IND_PRESENT => ['o', 'as', 'a', 'amos', 'áis', 'an'],
			CONJ_IND_PRETERITE => ['é', 'aste', 'ó', 'amos', 'asteis', 'aron'],
			CONJ_IND_IMPERFECT => ['aba', 'abas', 'aba', 'ábamos', 'abais', 'aban'],
			CONJ_IND_CONDITIONAL => ['aría', 'arías', 'aría', 'aríamos', 'aríais', 'arían'],
			CONJ_IND_FUTURE 		=> ['aré', 'arás', 'ará', 'aremos', 'aréis', 'arán'],
			CONJ_SUB_PRESENT => ['e', 'es', 'e', 'emos', 'éis', 'en'],
			CONJ_SUB_IMPERFECT => ['ara', 'aras', 'ara', 'áramos', 'arais', 'aran'],
			CONJ_SUB_IMPERFECT2 => ['ase', 'ases', 'ase', 'ásemos', 'aseis', 'asen'],
			CONJ_SUB_FUTURE => ['are', 'ares', 'are', 'áremos', 'areis', 'aren'],
			CONJ_IMP_AFFIRMATIVE => ['a', 'e', 'emos', 'ad', 'en'],
			CONJ_IMP_NEGATIVE => ['', '', '', '', '', ''],	
		],
		'ir' => [
			CONJ_PARTICIPLE => ['iendo', 'ido'],
			CONJ_IND_PRESENT => ['o', 'as', 'a', 'amos', 'áis', 'an'],
			CONJ_IND_PRETERITE => ['é', 'aste', 'ó', 'amos', 'asteis', 'aron'],
			CONJ_IND_IMPERFECT => ['aba', 'abas', 'aba', 'ábamos', 'abais', 'aban'],
			CONJ_IND_CONDITIONAL => ['aría', 'arías', 'aría', 'aríamos', 'aríais', 'arían'],
			CONJ_IND_FUTURE 		=> ['aré', 'arás', 'ará', 'aremos', 'aréis', 'arán'],
			CONJ_SUB_PRESENT => ['e', 'es', 'e', 'emos', 'éis', 'en'],
			CONJ_SUB_IMPERFECT => ['ara', 'aras', 'ara', 'áramos', 'arais', 'aran'],
			CONJ_SUB_IMPERFECT2 => ['ase', 'ases', 'ase', 'ásemos', 'aseis', 'asen'],
			CONJ_SUB_FUTURE => ['are', 'ares', 'are', 'áremos', 'areis', 'aren'],
			CONJ_IMP_AFFIRMATIVE => ['a', 'e', 'emos', 'ad', 'en'],
			CONJ_IMP_NEGATIVE => ['', '', '', '', '', ''],	
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
	
    static public function conjugate($text)
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
				$rc['forms'] = self::getFormsString($records);
				$rc['formsPretty'] = self::getFormsString($records, true);
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
			
			//dd($records);
		}
		
		return $records;
	}				
	
    static private function getFormsString($records, $pretty = false)
    {
		$rc = '';

		foreach($records[CONJ_PARTICIPLE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');			
		foreach($records[CONJ_IND_PRESENT] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_IND_PRETERITE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_IND_IMPERFECT] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_IND_CONDITIONAL] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_IND_FUTURE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_SUB_PRESENT] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_SUB_IMPERFECT] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_SUB_IMPERFECT2] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_SUB_FUTURE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		foreach($records[CONJ_IMP_AFFIRMATIVE] as $record)
			$rc .= $record . ';' . ($pretty ? ' ' : '');
		
		$rc = trim($rc);
		if (!$pretty && strlen($rc) > 0)
			$rc = ';' . $rc;
		
		return $rc;
	}	
}
