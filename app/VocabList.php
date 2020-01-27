<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VocabList extends Base
{
    public function words()
    {
    	return $this->hasMany('App\Word', 'parent_id', 'id');
    }
}
