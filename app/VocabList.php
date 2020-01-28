<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
