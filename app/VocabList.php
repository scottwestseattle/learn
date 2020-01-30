<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Auth;

class VocabList extends Model
{
    use SoftDeletes;

    public function words()
    {
        return $this->hasMany('App\Word', 'vocab_list_id', 'id')->orderByDesc('id');
    }

   static public function getIndex($parms = [])
    {
		$records = []; // make this countable so view will always work

        // if no parms or 'public' specified, return public
		$public = count($parms) == 0 || array_search('public', $parms) !== false;

        if ($public)
        {
			$records = self::select()
//				->where('site_id', SITE_ID)
				->where('release_flag', '>=', RELEASE_PUBLISHED)
				->orderByRaw('id DESC')
				->get();
        }
		else
		{
            if (array_search('ownedOrPublic', $parms) !== false)
            {
                // only return the user's list
                $records = self::select()
    				->where('release_flag', '>=', RELEASE_PUBLISHED)
                    ->orWhere('user_id', Auth::id())
                    ->orderBy('type_flag')
                    ->orderByRaw('id DESC')
                    ->get();
            }
            else if (array_search('owned', $parms) !== false)
            {
                // only return the user's list
                $records = self::select()
                    ->where('user_id', Auth::id())
                    ->orderBy('type_flag')
                    ->orderByRaw('id DESC')
                    ->get();
            }
            else if (array_search('deleted', $parms) !== false)
            {
                // only return deleted
                $records = self::select()
                    ->orderBy('type_flag')
                    ->orderByRaw('id DESC')
                    ->get();
            }
            else if (array_search('all', $parms) !== false)
            {
                // return all
                $records = self::select()
                    ->orderBy('type_flag')
                    ->orderByRaw('id DESC')
                    ->get();
            }
            else
            {
                Event::logError(LOG_MODEL, LOG_ACTION_SELECT, 'getIndex - unknown parameter');
                Tools::flash('danger', 'Error getting vocabulary lists');
            }
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

                $w->vocab_list_id   = $record->id;
                $w->user_id 	    = Auth::id();
                $w->type_flag 	    = WORDTYPE_VOCABLIST;
                $w->permalink	    = $record->id . '-' . Tools::createPermalink($wordTitle);
                $w->title 		    = $wordTitle;
                $w->description	    = $wordDescription;

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
