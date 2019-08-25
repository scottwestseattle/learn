<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;

class EventController extends Controller
{
	protected $prefix = 'events';

    public function __construct()
    {
        $this->middleware('is_admin');

		parent::__construct();
    }
	
    public function index($type_flag = null)
    {
		$type = intval($type_flag);

		if (isset($type_flag) && $type === 0)
		{
			// show all for any type_flag that isn't a number
			$records = Event::select()
				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->orderByRaw('id DESC')
				->get();
		}
		else if ($type > 0)
		{
			// show by type
			$records = Event::select()
				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->where('type_flag', $type)
				->orderByRaw('id DESC')
				->get();
		}
		else
		{
			// default is to show latest 100
			$records = Event::select()
				->where('site_id', SITE_ID)
				->where('deleted_flag', 0)
				->orderByRaw('id DESC')
				->limit(100)
				->get();
		}

		// get total stats
		$totals['info'] = Event::select()->where('site_id', SITE_ID)->where('deleted_flag', 0)->where('type_flag', LOG_TYPE_INFO)->count();
		$totals['warning'] = Event::select()->where('site_id', SITE_ID)->where('deleted_flag', 0)->where('type_flag', LOG_TYPE_WARNING)->count();
		$totals['error'] = Event::select()->where('site_id', SITE_ID)->where('deleted_flag', 0)->where('type_flag', LOG_TYPE_ERROR)->count();
		$totals['exception'] = Event::select()->where('site_id', SITE_ID)->where('deleted_flag', 0)->where('type_flag', LOG_TYPE_EXCEPTION)->count();
		$totals['other'] = Event::select()->where('site_id', SITE_ID)->where('deleted_flag', 0)->where('type_flag', LOG_TYPE_OTHER)->count();
		$totals['all'] = $totals['info'] + $totals['warning'] + $totals['error'] + $totals['exception'] + $totals['other'];
		
		return view($this->prefix . '.index', $this->getViewData([
			'records' => $records,
			'totals' => $totals,
		]));		
    }
}
