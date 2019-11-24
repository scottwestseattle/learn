<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Visitor;

class VisitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin');

		parent::__construct();
    }

    public function index(Request $request)
    {
		$showBots = false;

		if (isset($request->showbots))
			$showBots = true;

		$dates = Controller::getDateFilter($request, false, false);

		$filter = Controller::getFilter($request, /* today = */ true);

		$date = isset($dates['from_date']) ? $dates['from_date'] : null;

		$records = Visitor::getVisitors($date);

		$records = VisitorController::removeRobots($records, $showBots);

		$vdata = $this->getViewData([
			'records' => $records,
			'dates' => Controller::getDateControlDates(),
			'filter' => Controller::getFilter($request, /* today = */ true),
			'bots' => $showBots,
		]);

		return view('visitors.index', $vdata);
    }

	static public function removeRobots($records, $showBots = false)
	{
		$count = 0;
		$out = [];

		foreach($records as $record)
		{
			// shorten the user_agent
			$agent = $record->user_agent;
			$new = null;

			if (stripos($agent, 'Googlebot') !== FALSE)
				$new = 'GoogleBot';
			else if (stripos($agent, 'Google-Site-Verification') !== FALSE)
				$new = 'GoogleSiteVerification';
			else if (stripos($agent, 'bingbot') !== FALSE)
				$new = 'BingBot';
			else if (stripos($agent, 'mediapartners') !== FALSE)
				$new = 'AdSense';
			else if (stripos($agent, 'a6-indexer') !== FALSE)
				$new = 'Amazon A6';
			else if (stripos($agent, 'pinterest') !== FALSE)
				$new = 'PinBot';
			else if (stripos($agent, 'yandex.com/bots') !== FALSE)
				$new = 'YandexBot';
			else if (stripos($agent, 'alphaseobot') !== FALSE)
				$new = 'AlphaSeoBot';
			else if (stripos($agent, 'uptimebot') !== FALSE)
				$new = 'UptimeBot';
			else if (stripos($agent, 'crawl') !== FALSE)
				$new = $agent;
			else if (stripos($agent, 'bot') !== FALSE)
				$new = $agent;
			else if (stripos($record->host_name, 'spider') !== FALSE)
				$new = $record->host_name;
			else if (stripos($record->host_name, 'crawl') !== FALSE)
				$new = $record->host_name;
			else if (stripos($record->host_name, 'bot') !== FALSE)
				$new = $record->host_name;
			else if (stripos($record->host_name, 'googleusercontent.com') !== FALSE)
				$new = 'GoogleUserContent';
			else if (stripos($record->host_name, 'amazonaws.com') !== FALSE)
				$new = 'AmazonAWS';
			else if (stripos($record->referrer, 'localhost') !== FALSE)
				$new = 'localhost';

			if (isset($new))
			{
				if (!$showBots)
					continue;

				$record->user_agent = $new;
			}

			$out[$count]['date'] = $record->updated_at;
			$out[$count]['id'] = $record->record_id;
			$out[$count]['page'] = $record->page;
			$out[$count]['ref'] = $record->referrer;
			$out[$count]['agent'] = $record->user_agent;
			$out[$count]['host'] = $record->host_name;
			$out[$count]['model'] = $record->model;
			$out[$count]['ip'] = $record->ip_address;
			$out[$count]['domain_name'] = $record->domain_name;

			$location = '';

			if (isset($record->city))
			    $location = $record->city;

			if (isset($record->country))
			{
			    if (strlen($location) > 0)
			        $location .= ', ';

			    $location .= $record->country;
			}

            $location = (strlen($location) > 0) ? '(' . $location . ')' : '';

			$out[$count]['location'] = $location;

			$count++;
		}

		return $out;
	}
}
