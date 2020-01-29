<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class VocabList extends Base
{
    public function words()
    {
    	return $this->hasMany('App\Word', 'parent_id', 'id');
    }

    static public function getIndex()
    {
		$records = []; // make this countable so view will always work

		try
		{
            $records = VocabList::select()
//				->where('site_id', SITE_ID)
                ->where('deleted_flag', 0)
                ->where('release_flag', '>=', RELEASE_PUBLISHED)
                ->get();
		}
		catch (\Exception $e)
		{
			$msg = 'Error getting Vocab Lists';
			Event::logException(LOG_MODEL, LOG_ACTION_SELECT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $records;
	}

    static public function import($qna, $title, $isQuiz)
    {
        $success = false;

		try
		{
		    //
            // create new vocab list
            //
            $record = new VocabList();

            $record->user_id 		= Auth::id();
            $record->title 			= $title;
            $record->permalink		= Tools::createPermalink($title);
            $record->wip_flag       = WIP_DEFAULT;
            $record->release_flag   = RELEASE_DEFAULT;

            $record->save();

            //
            // add words to the new list
            //
            foreach($qna as $q)
            {
        		$w = new Word();

                if ($isQuiz)
                {
                    // if it's text from a quiz, flip the qna
                    $wordTitle = strip_tags($q['a']);
                    $wordDescription = strip_tags($q['q']);

                    // remove any embedded answer options, for example: [one, two three]
                    $wordDescription = trim(preg_replace('`\[[^\]]*\]`', '', $wordDescription));
                }
                else
                {
                    $wordTitle = strip_tags($q['q']);
                    $wordDescription = strip_tags($q['a']);
                }

                if (strlen($wordTitle) > 255)
                {
                    dd($q);
                }

                $w->parent_id   = $record->id;
                $w->user_id 	= Auth::id();
                $w->type_flag 	= WORDTYPE_VOCABLIST;
                $w->permalink	= $record->id . '-' . Tools::createPermalink($wordTitle);
                $w->title 		= $wordTitle;
                $w->description	= $wordDescription;

                $w->save();
            }

            $success = true;
		}
		catch (\Exception $e)
		{
			$msg = 'Error importing to Vocab List';
			Event::logException(LOG_MODEL, LOG_ACTION_IMPORT, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

		return $success;
	}
}
