<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Course;

define('LESSON_FORMAT_DEFAULT', 0);
define('LESSON_FORMAT_AUTO', 1);

define('LESSONTYPE_NOTSET', 0);
define('LESSONTYPE_TEXT', 10);
define('LESSONTYPE_VOCAB', 20);
define('LESSONTYPE_QUIZ_FIB', 30);
define('LESSONTYPE_QUIZ_MC1', 40);
define('LESSONTYPE_QUIZ_MC2', 41);
define('LESSONTYPE_QUIZ_MC3', 42);
define('LESSONTYPE_QUIZ_MC4', 43);
define('LESSONTYPE_OTHER', 99);
define('LESSONTYPE_DEFAULT', LESSONTYPE_TEXT);

class Lesson extends Base
{	
    const _lessonTypeFlags = [
		LESSONTYPE_NOTSET => 'Not Set',
		LESSONTYPE_TEXT => 'Text',
		LESSONTYPE_VOCAB => 'Vocabulary List',
		LESSONTYPE_QUIZ_FIB => 'Fill in the Blank',
		LESSONTYPE_QUIZ_MC1 => 'Multiple Choice - Set Options (MC1)',
		LESSONTYPE_QUIZ_MC2 => 'Multiple Choice - Random Options (MC2)',
		LESSONTYPE_QUIZ_MC3 => 'Multiple Choice - New Layout (MC3)',
//todo: not used yet		LESSONTYPE_QUIZ_MC4 => 'Multiple Choice - Random Options New Layout (MC4)',
		LESSONTYPE_OTHER => 'Other',
    ];
	
    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function course()
    {
    	return $this->belongsTo('App\Course', 'parent_id', 'id');
    }

	public function formatByType($quiz, $reviewType)
    {
		// if $reviewType not set, it uses the type setting on the lesson
		if (!isset($reviewType))
			$reviewType = $this->type_flag;
			
		switch($reviewType)
		{
			case LESSONTYPE_QUIZ_FIB:
				// FIB doesn't have buttons with anwers, no formatting
				break;
				
			case LESSONTYPE_QUIZ_MC1:
				// expects embedded list of answers like [one, two, three] which will be converted to buttons
				$quiz = $this->formatMc1($quiz);
				break;
				
			case LESSONTYPE_QUIZ_MC2:
				// creates random answers and puts them in the questions
				$quiz = $this->formatMc2($quiz);
				break;

			case LESSONTYPE_QUIZ_MC3:
				// create random answers with new quiz layout
				$quiz = $this->formatMc3($quiz);
				break;

			case LESSONTYPE_QUIZ_MC4:
				// not used yet, same as LESSONTYPE_QUIZ_MC3
				$quiz = $this->formatMc4($quiz);
				break;
				
			default:
				break;
		}

		return $quiz;
	}

	// this version puts the answer options into a separate cell
	private function formatMc3($quiz)
    {
		$quizNew = [];
		$answers = [];

		$max = count($quiz) - 1; // max question index
		if ($max > 0)
		{
			$randomOptions = 5;
			$cnt = 0;
			foreach($quiz as $record)
			{
				$options = [];
				
				if (preg_match('#\[(.*)\]#is', $record['q']))
				{
					// there is already an answer so it will be handled in formatMc1
				}
				else
				{
					//
					// get random answers from other questions
					//

					// using 100 just so it's not infinite, only goes until three unique options are picked
					$pos = rand(0, $randomOptions - 1); // position of the correct answer
					for ($i = 0; $i < 100 && count($options) < $randomOptions; $i++)
					{
						// pick three random options
						$rnd = rand(0, $max);	// answer from other random question
						$option = $quiz[$rnd];
						//dd($option['a']);
						
						// not the current question AND has answer text AND answer not used yet
						if ($option['id'] != $record['id'] && strlen($option['a']) > 0 && !array_key_exists($option['a'], $options))
						{
							if ($pos == count($options))
							{
								// add in the real answer randomly
								$options[$record['a']] = $record['a'];
							}
								
							$options[$option['a']] = $option['a'];
							
							if ($pos == count($options))
							{
								// add in the real answer randomly
								$options[$record['a']] = $record['a'];
							}
						}
						else
						{
							//dump('duplicate: ' . $option);
						}
					}
					
					//dump($options);
				}
				
				$quizNew[$cnt]['q'] = $record['q'];
				$quizNew[$cnt]['options'] = $options;
				$quizNew[$cnt]['a'] = $record['a'];
				$quizNew[$cnt]['id'] = $record['id'];

				//dd($quizNew[$cnt]);				
				
				$cnt++;
			}
		}
		
		//dd($quizNew);
		return $this->formatMc1($quizNew);		
	}

	//todo: not used yet
	private function formatMc4($quiz)
    {
		return $this->formatMc3($quiz);
	}
	
	private function formatMc2($quiz)
    {
		$quizNew = [];
		$answers = [];

		$max = count($quiz) - 1; // max question index
		if ($max > 0)
		{
			$randomOptions = 5;
			$cnt = 0;
			foreach($quiz as $record)
			{
				if (preg_match('#\[(.*)\]#is', $record['q']))
				{
					// there is already an answer so it will be handled in formatMc1
				}
				else
				{
					//
					// get random answers from other questions
					//
					$options = [];

					// using 100 just so it's not infinite, only goes until three unique options are picked
					$pos = rand(0, $randomOptions - 1); // position of the correct answer
					for ($i = 0; $i < 100 && count($options) < $randomOptions; $i++)
					{
						// pick three random options
						$rnd = rand(0, $max);	// answer from other random question
						$option = $quiz[$rnd];
						//dd($option['a']);
						
						// not the current question AND has answer text AND answer not used yet
						if ($option['id'] != $record['id'] && strlen($option['a']) > 0 && !array_key_exists($option['a'], $options))
						{
							if ($pos == count($options))
							{
								// add in the real answer randomly
								$options[$record['a']] = $record['a'];
							}
								
							$options[$option['a']] = $option['a'];
							
							if ($pos == count($options))
							{
								// add in the real answer randomly
								$options[$record['a']] = $record['a'];
							}
						}
						else
						{
							//dump('duplicate: ' . $option);
						}
					}
					
					//dump($options);
					
					$q = $record['q'] . ' [';
					foreach($options as $option)
					{
						// put the options at the end of the question like: [one, two, three]
						$q .= $option . ', ';
					}
					$q .= ']';
					$q = str_replace(', ]', ']', $q);
					
					$record['q'] = $q;
				}
				
				$quizNew[$cnt]['q'] = $record['q'];
				$quizNew[$cnt]['a'] = $record['a'];
				$quizNew[$cnt]['id'] = $record['id'];
				//dd($quiz[$i]);				
				
				$cnt++;
			}
		}
		
		//dd($quizNew);
		return $this->formatMc1($quizNew);		
	}

	static private function getCommaSeparatedWords($text)
    {
		$words = '';
		$array = [];
		
		// pattern looks like: "The words [am, is, are] in the sentence."
		preg_match_all('#\[(.*)\]#is', $text, $words, PREG_SET_ORDER);

		// if answers not found, set it to ''
		$words = (count($words) > 0 && count($words[0]) > 1) ? $words[0][1] : '';
		
		if (strlen($words) > 0)
		{
			$array = explode(',', $words); // extract the comma-separated words		
		}

		return $array;
	}
	
	// creates buttons for each answer option
	// and puts them into the question
	private function formatMc1($quiz)
    {
		/*
		I [am, is, are] hungry. - am
		<button class="btn btn-primary" type="button">am</button>
		<button class="btn btn-primary" type="button">is</button>
		<button class="btn btn-primary" type="button">are</button>
		*/

		$quizNew = [];
		$i = 0;
		//dump($quiz);
		foreach($quiz as $record)
		{
			$a = trim($record['a']);
			$q = trim($record['q']);
			$id = $record['id'];

			if (strlen($a) > 0)
			{
				if (array_key_exists('options', $record))
				{
					// use the options
					$options = $record['options'];
					
					//
					// create a button for each answer option
					//				
					$buttons = '';					
					foreach($options as $m)
					{
						// mark the correct button so it can be styled during the quiz
						$buttonClass = ($m == $a) ? 'btn-right' : 'btn-wrong';
							
						$buttons .= '<div><button id="' . $id . '" onclick="checkAnswerMc1(' . $id . ', \'' . $m . '\')" class="btn btn-primary btn-quiz-mc3 ' . $buttonClass . '">' . $m . '</button></div>';
						
					}
					
					// put the formatted info back into the quiz
					$quizNew[] = [
						'q' => $q,
						'a' => $a,
						'options' => $buttons,
						'id' => $record['id'],
					];					
				}
				else
				{
					// get the answer options from the question text
					$answers = self::getCommaSeparatedWords($q);
					
					if (count($answers) > 0)
					{
						//
						// create a button for each answer option
						//				
						$buttons = '';
						foreach($answers as $m)
						{
							$m = str_replace("'", "", trim($m)); // fix single apostrophe, for exmaple D'Jamena
							
							// mark the correct button so it can be styled during the quiz
							$buttonClass = ($m == $a) ? 'btn-right' : 'btn-wrong';
								
							$buttons .= '<button onclick="checkAnswerMc1(\'' . $m . '\')" class="btn btn-primary btn-quiz-mc1 ' . $buttonClass . '">' . $m . '</button>';
						}
						//dd($buttons);
						
						// replace the options with the buttons
						$q = preg_replace("/\[.*\]/is", $buttons, $q);
						
						// put the formatted info back into the quiz
						$quizNew[] = [
							'q' => $q,
							'a' => $a,
							'id' => $record['id'],
							'options' => '',
						];
					}
				}
			}
		}
		
		//dd($quizNew);
		return $quizNew;
	}
	
    public function isMc($reviewType)
	{
		$v = false;
		
		if (!isset($reviewType))
			$reviewType = $this->type_flag;
		
		switch($reviewType)
		{
			case LESSONTYPE_QUIZ_MC1:
			case LESSONTYPE_QUIZ_MC2:
			case LESSONTYPE_QUIZ_MC3:
			case LESSONTYPE_QUIZ_MC4:
				$v = true;
				break;
			default:
				break;
		}
		
		return $v;
	}
	
    public function isQuiz()
	{
		$v = false;
		
		switch($this->type_flag)
		{
			case LESSONTYPE_QUIZ_FIB:
			case LESSONTYPE_QUIZ_MC1:
			case LESSONTYPE_QUIZ_MC2:
			case LESSONTYPE_QUIZ_MC3:
			case LESSONTYPE_QUIZ_MC4:
				$v = true;
				break;
			default:
				break;
		}
		
		return $v;
	}

    public function getLessonType()
	{		
		return $this->type_flag;
	}
	
    static public function getLessonTypes()
	{
		return self::_lessonTypeFlags;
	}
	
    public function getChapterIndex()
	{
		$records = Lesson::getIndex($this->parent_id, $this->lesson_number);

		return $records;
	}
	
    static public function getChapters($parentId)
	{
		$records = Lesson::getIndex($parentId);

		return $records->groupBy('lesson_number');
	}
	
    static public function getIndex($parent_id, $chapter = '%')
	{
		$parent_id = intval($parent_id);
		$parent_id = $parent_id > 0 ? $parent_id : '%';
		$released = Tools::isAdmin() ? '%' : '1';

		$records = [];

		$records = Lesson::select()
			->where('deleted_flag', 0)
			->where('parent_id', 'like', $parent_id)
			->where('published_flag', 'like', $released)
			->where('approved_flag', 'like', $released)
			->where('lesson_number', 'like', $chapter)
			->orderBy('parent_id')
			->orderBy('lesson_number')
			->orderBy('section_number')
			->get();

		return $records;
	}

    static public function convertCodes($text)
	{
		$v = $text;
		
		// replace underscores with input controls
		//$v = preg_replace('#___#is', "<input />", $v); //todo: do this at view time, don't permanently convert

		//
		// format tables
		//
		
		/*
		<div class="table-borderless">
		<table class="table lesson-table-sm">
		*/
		
		// this will wipe out any first table if more than 1 tables
		//$v = preg_replace('#(<table.*</table>)#is', "<div class=\"table-borderless\">$1</div>", $v); // wrap in div
		//$v = preg_replace('#border=\".*\"#is', "class=\"table lesson-table-sm\"", $v); // add table classes

		//dd($v);

		return $v;
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

    public function getDisplayNumber()
    {
    	return $this->lesson_number . '.' . $this->section_number;
    }

    public function renumber($renumberAll)
    {
		$renumbered = false;

		// The format is Lesson.Section or Chapter.Section
		if ($renumberAll)
		{
			// renumber all records
			$records = Lesson::select()
				->where('deleted_flag', 0)
				->where('parent_id', $this->parent_id)
				->where('lesson_number', $this->lesson_number)
				->orderBy('section_number')
				->get();

			$next = 1;
			foreach($records as $record)
			{
				$record->section_number = $next++;
				$record->save();
				$renumbered = true;
			}
		}
		else
		{
			// check if record with this section_number already exists
			$c = Lesson::select()
				->where('id', '<>', $this->id)
				->where('parent_id', $this->parent_id)
				->where('deleted_flag', 0)
				->where('lesson_number', $this->lesson_number)
				->where('section_number', $this->section_number)
				->count();

			if ($c > 0)
			{
				// renumber starting from current record
				$records = Lesson::select()
					->where('id', '<>', $this->id)
					->where('parent_id', $this->parent_id)
					->where('deleted_flag', 0)
					->where('lesson_number', $this->lesson_number)
					->where('section_number', '>=', $this->section_number)
					->orderBy('section_number')
					->get();

				$next = $this->section_number + 1;
				foreach($records as $record)
				{
					$record->section_number = $next++;
					$record->save();
					$renumbered = true;
					//dump($record->title . ': ' . $next);
				}
				//die;
			}
		}

		return $renumbered;
    }

	public function getPrevChapter()
	{
		return $this->getNextPrevChapter();
	}

	public function getNextChapter()
	{
		return $this->getPrevNextChapter(/* next = */ true);
	}
	
	// default is prev
    public function getPrevNextChapter($next = false)
    {
		$r = Lesson::select()
			->where('deleted_flag', 0)
			->where('parent_id', $this->parent_id)
			->where('published_flag', 'like', Tools::isAdmin() ? '%' : 1)
			->where('approved_flag', 'like', Tools::isAdmin() ? '%' : 1)
			->where('lesson_number', $next ? '>' : '<', $this->lesson_number)
			->orderByRaw('lesson_number ASC, section_number ' . ($next ? 'ASC' : 'DESC '))
			->first();

		return $r ? $r->id : null;			
    }	
	
    static public function getLast($parentId)
    {
		$r = Lesson::select()
			->where('deleted_flag', 0)
			->where('parent_id', $parentId)
			->orderByRaw('lesson_number DESC, section_number DESC')
			->first();

		return $r;			
    }	
	
	static public function getPrev($curr)
	{
		return Lesson::getNextPrev($curr);
	}

	static public function getNext($curr)
	{
		return Lesson::getNextPrev($curr, /* next = */ true);
	}
	
	// default is prev
	static protected function getNextPrev($curr, $next = false)
	{
		if (Tools::isAdmin())
		{
			$r = Lesson::select()
				->where('deleted_flag', 0)
				->where('parent_id', $curr->parent_id)
				->where('lesson_number', $curr->lesson_number)
				->where('section_number',  $next ? '>' : '<', $curr->section_number)
				->orderByRaw('lesson_number ASC, section_number ' . ($next ? 'ASC' : 'DESC '))
				->first();
		}
		else
		{
			$r = Lesson::select()
				->where('deleted_flag', 0)
				->where('parent_id', $curr->parent_id)
				->where('published_flag', 1)
				->where('approved_flag', 1)
				->where('lesson_number', $curr->lesson_number)
				->where('section_number',  $next ? '>' : '<', $curr->section_number)
				->orderByRaw('lesson_number ASC, section_number ' . ($next ? 'ASC' : 'DESC '))
				->first();
		}

		return $r ? $r->id : null;
	}

    static public function getLessonNumbers($start = 1, $end = 15)
    {
    	return Tools::makeNumberArray($start, $end);
    }

    static public function getSectionNumbers($start = 1, $end = 15)
    {
    	return Tools::makeNumberArray($start, $end);
    }
}
