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
		$word = trim($word);
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

    static public function possibleVerb($word)
    {
		$rc = (Tools::endsWith($word, 'ar') || Tools::endsWith($word, 'er') || Tools::endsWith($word, 'ir'));
		if ($rc == false)
			$rc = (Tools::endsWith($word, 'arse') || Tools::endsWith($word, 'erse') || Tools::endsWith($word, 'irse'));
			
		return $rc;
	}
	
	// make forms easier to search line: ';one;two;three;'
    static public function formatForms($forms)
    {
		$v = trim($forms);
		$accents = 'áÁéÉíÍóÓúÚüÜñÑ'; 
		$v = str_replace('; ', ';', $v);
		$v = preg_replace('/[^\da-z; ' . $accents . ']/i', '', $v); // replace all non-alphanums except for ';'
		$v = str_replace(';;', ';', $v);
		$v = str_replace(';', '\0', $v); // just to trim the leading and trailing ';' and any spaces
		$v = trim($v);
		$v = str_replace('\0', ';', $v); // put back the non-trimmed ';'
		
		$v = trim($v);
		$v = (strlen($v) > 0) ? $v = ';' . $v . ';' : null;
		//dd($v);
		
		return $v;
	}

    static public function displayForms($forms)
    {
		$v = str_replace(';', ' ', $forms);
		
		return trim($v);
	}
	
	// search checks title and forms
    static public function search($word)
    {
		$word = trim($word);
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
		
    static public function conjugate($text)
    {
		$records = null;
		$rc['forms'] = null;
		$rc['records'] = null;
		
		$parts = null;
		$text = Tools::alphanum($text);
		if (isset($text)) // anything left?
		{
			// Case 1: ends with 'azar': aplazar, desplazar, gozar
			if (Tools::endsWith($text, 'azar') || Tools::endsWith($text, 'ozar'))
			{
				// á é í ó 
				$stem = 'zar';
				$middle = 'z';
				$middleIrregular = 'c';
				
				// participles
				$tab[CONJ_PARTICIPLE] = ['ando', 'ado'];
								
				// indicative
				$tab[CONJ_IND_PRESENT] 	= ['o', 'as', 'a', 'amos', 'áis', 'an'];
				$tab[CONJ_IND_PRETERITE] 	= ['é', 'aste', 'ó', 'amos', 'asteis', 'aron'];
				$tab[CONJ_IND_IMPERFECT] 	= ['aba', 'abas', 'aba', 'ábamos', 'abais', 'aban'];
				$tab[CONJ_IND_CONDITIONAL] 	= ['aría', 'arías', 'aría', 'aríamos', 'aríais', 'arían'];
				
				// subjunctive
				$tab[CONJ_SUB_PRESENT] 	= ['e', 'es', 'e', 'emos', 'éis', 'en'];
				$tab[CONJ_SUB_IMPERFECT] 	= ['ara', 'aras', 'ara', 'áramos', 'arais', 'aran'];
				$tab[CONJ_SUB_IMPERFECT2] 	= ['ase', 'ases', 'ase', 'ásemos', 'aseis', 'asen'];
				$tab[CONJ_SUB_FUTURE] 	= ['are', 'ares', 'are', 'áremos', 'areis', 'aren'];

				// imperative
				$tab[CONJ_IMP_AFFIRMATIVE] 	= ['a', 'e', 'emos', 'ad', 'en'];
				//$tab[CONJ_IMP_NEGATIVE] 	= ['o', 'as', 'a', 'amos', 'áis', 'an'];
				
				// preg_split('/(\. |\.\' |\.\" )/', $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
				$parts = preg_split('/(' . $stem . ')/', $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
				if (count($parts) > 0)
				{
					$root = $parts[0]; // verb root such as 
					$records = [];
					
					// participles
					for ($i = 0; $i < 2; $i++)
						$records[CONJ_PARTICIPLE][] = $root . $middle . $tab[CONJ_PARTICIPLE][$i];
										
					// indication
					for ($i = 0; $i < 6; $i++)
						$records[CONJ_IND_PRESENT][] = $root . $middle . $tab[CONJ_IND_PRESENT][$i];
					for ($i = 0; $i < 6; $i++)
						$records[CONJ_IND_PRETERITE][] = $root . $middle . $tab[CONJ_IND_PRETERITE][$i];
					for ($i = 0; $i < 6; $i++)
						$records[CONJ_IND_IMPERFECT][] = $root . $middle . $tab[CONJ_IND_IMPERFECT][$i];
					for ($i = 0; $i < 6; $i++)
						$records[CONJ_IND_CONDITIONAL][] = $root . $middle . $tab[CONJ_IND_CONDITIONAL][$i];
					// one irregular
					$records[CONJ_IND_PRETERITE][0] = $root . $middleIrregular . $tab[CONJ_IND_PRETERITE][0];
					
					// subjunctive
					for ($i = 0; $i < 6; $i++)
						$records[CONJ_SUB_PRESENT][] = $root . $middleIrregular . $tab[CONJ_SUB_PRESENT][$i];
					for ($i = 0; $i < 6; $i++)
						$records[CONJ_SUB_IMPERFECT][] = $root . $middle . $tab[CONJ_SUB_IMPERFECT][$i];
					for ($i = 0; $i < 6; $i++)
						$records[CONJ_SUB_IMPERFECT2][] = $root . $middle . $tab[CONJ_SUB_IMPERFECT2][$i];
					for ($i = 0; $i < 6; $i++)
						$records[CONJ_SUB_FUTURE][] = $root . $middle . $tab[CONJ_SUB_FUTURE][$i];

					// imperative
					for ($i = 0; $i < 5; $i++)
						$records[CONJ_IMP_AFFIRMATIVE][] = $root . $middle . $tab[CONJ_IMP_AFFIRMATIVE][$i];
					// three irregulars
					$records[CONJ_IMP_AFFIRMATIVE][1] = $root . $middleIrregular . $tab[CONJ_IMP_AFFIRMATIVE][1];
					$records[CONJ_IMP_AFFIRMATIVE][2] = $root . $middleIrregular . $tab[CONJ_IMP_AFFIRMATIVE][2];
					$records[CONJ_IMP_AFFIRMATIVE][4] = $root . $middleIrregular . $tab[CONJ_IMP_AFFIRMATIVE][4];
					
					//dd($records);
					$rc['forms'] = self::getFormsString($records);
					$rc['records'] = $records;
				}
			}
		}
				
		//dd($rc);
		
		return $rc;
	}		
	
    static private function getFormsString($records)
    {
		$rc = '';
		
		foreach($records[CONJ_PARTICIPLE] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_IND_PRESENT] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_IND_PRETERITE] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_IND_IMPERFECT] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_IND_CONDITIONAL] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_SUB_PRESENT] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_SUB_IMPERFECT] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_SUB_IMPERFECT2] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_SUB_FUTURE] as $record)
			$rc .= $record . ';';
		foreach($records[CONJ_IMP_AFFIRMATIVE] as $record)
			$rc .= $record . ';';
		
		if (strlen($rc) > 0)
			$rc = ';' . $rc;
		
		return $rc;
	}	
}
