<?php

namespace App;

use Auth;
use Lang;
use App;
use App\User;
use DateTime;

class Quiz
{

	// this version puts the answer options into a separate cell
	static public function makeReviewQuiz($quiz)
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
				$optionsReverse = [];

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
								// add in the real answer at the random position
								$options[$record['a']] = $record['a'];
								$optionsReverse[$record['a']] = $record['q'];
							}

							$options[$option['a']] = $option['a'];
							$optionsReverse[$option['a']] = $option['q'];

							if ($pos == count($options))
							{
								// add in the real answer at the random position
								$options[$record['a']] = $record['a'];
								$optionsReverse[$record['a']] = $record['q'];
							}
						}
						else
						{
							//dump('duplicate: ' . $option);
						}
					}

					$quizNew[$cnt]['options'] = $options;
					$quizNew[$cnt]['optionsReverse'] = $optionsReverse;
				}

				$quizNew[$cnt]['q'] = $record['q'];
				$quizNew[$cnt]['a'] = $record['a'];
				$quizNew[$cnt]['id'] = $record['id'];
				$quizNew[$cnt]['ix'] = $record['id'];

				//dump($quizNew[$cnt]);

				$cnt++;
			}
		}

		//dd($quizNew);
		return self::addAnswerButtons($quizNew);
	}
	
	// creates buttons for each answer option
	// and puts them into the question
	static private function addAnswerButtons($quiz)
    {
		$quizNew = [];
		$i = 0;

		foreach($quiz as $record)
		{
			$a = trim($record['a']);
			$q = trim($record['q']);
			$id = $record['id'];

			if (strlen($a) > 0)
			{
				$buttonId = 0;
				if (array_key_exists('options', $record) && is_array($record['options']))
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

						$buttons .= self::formatButton($m, $buttonId++, $buttonClass);
					}

					// put the formatted info back into the quiz
					$quizNew[] = [
						'q' => $q,
						'a' => $a,
						'options' => $buttons,
						'id' => $record['id'],
        				'ix' => $record['id'],
					];
				}
			}
		}

		//dd($quizNew);
		return $quizNew;
	}
	
	static private function formatButton($text, $id, $class)
    {
		$button = '<div><button id="'
            . $id
            . '" onclick="checkAnswerMc1('
            . $id . ', \''
		    . $text . '\')" class="btn btn-primary btn-quiz-mc3 '
		    . $class . '">'
		    . $text
		    . '</button></div>';

		//dump($button);

		return $button;
	}	
	
	static private function getCommaSeparatedAnswers($text)
    {
		$words = '';
		$array = [];

		// pattern looks like: "The words [am, is, are] in the sentence."
		preg_match_all('#\[(.*)\]#is', $text, $words, PREG_SET_ORDER);

		// if answers not found, set it to ''
		$words = (count($words) > 0 && count($words[0]) > 1) ? $words[0][1] : '';

		if (strlen($words) > 0)
		{
			$raw = explode(',', $words); // extract the comma-separated words

			if (is_array($raw) && count($raw) > 0)
			{
				foreach($raw as $word)
				{
					$array[] = trim($word);
				}
			}
		}

		return $array;
	}
	
}
