<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use DateTime;
use App\Tools;

class Visitor extends Model
{
    static public function add($domainName, $ip, $model, $page, $record_id = null)
    {
		$save = false;
		$host = null;
		$referrer = null;
		$userAgent = null;

		Visitor::getVisitorInfo($host, $referrer, $userAgent);

		if (strlen($userAgent) == 0 && strlen($referrer) == 0)
		{
			return; // no host or referrer probably means that it's the tester so don't count it
		}

		$visitor = new Visitor();
		$visitor->ip_address = $ip;

		$visitor->visit_count++;
		$visitor->site_id = SITE_ID;
		$visitor->host_name = Tools::trunc($host, VISITOR_MAX_LENGTH);
		$visitor->user_agent = Tools::trunc($userAgent, VISITOR_MAX_LENGTH);
		//$visitor->user_agent = "host=" . $host . ', refer=' . $referrer . ', user=' . $userAgent;
		$visitor->referrer = Tools::trunc($referrer, VISITOR_MAX_LENGTH);

		// new fields
		$visitor->domain_name = $domainName;
		$visitor->model = $model;
		$visitor->page = $page;
		$visitor->record_id = $record_id;

		$ipInfo = Tools::getIpInfo($ip);
		if (isset($ipInfo))
		{
            $visitor->country = $ipInfo['country'];
            $visitor->city = $ipInfo['city'];
        }
//dd($visitor);
		try
		{
			$visitor->save();
		}
		catch (\Exception $e)
		{
//dd($e);
			Event::logException(LOG_MODEL_VISITORS, LOG_ACTION_ADD, 'Error Adding Visitor', null, $e->getMessage());
			throw $e;
		}
	}

    static public function getVisitors($date = null)
    {
		if (isset($date))
		{
			$date = DateTime::createFromFormat('Y-m-d', $date);

			$month = intval($date->format("m"));
			$year = intval($date->format("Y"));
			$day = intval($date->format("d"));
		}
		else
		{
			$month = intval(date("m"));
			$year = intval(date("Y"));
			$day = intval(date("d"));
		}

		$fromTime = ' 00:00:00';
		$toTime = ' 23:23:59';

		$fromDate = '' . $year . '-' . $month . '-' . $day . ' ' . $fromTime;
		$toDate = '' . $year . '-' . $month . '-' . $day . ' ' . $toTime;

		$q = '
			SELECT *
			FROM visitors
			WHERE 1=1
			AND site_id = ?
			AND deleted_flag = 0
 			AND (created_at >= STR_TO_DATE(?, "%Y-%m-%d %H:%i:%s") AND created_at <= STR_TO_DATE(?, "%Y-%m-%d %H:%i:%s"))
			ORDER BY id DESC
		';

		$records = DB::select($q, [SITE_ID, $fromDate, $toDate]);
//dump($fromDate);
//dump($toDate);
//dump(SITE_ID);
//dd($records);

		return $records;
    }

	static protected function getVisitorInfo(&$host, &$referrer, &$userAgent)
	{
		//
		// get visitor info
		//
		$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);

		$referrer = null;
		if (array_key_exists("HTTP_REFERER", $_SERVER))
			$referrer = $_SERVER["HTTP_REFERER"];

		$userAgent = null;
		if (array_key_exists("HTTP_USER_AGENT", $_SERVER))
			$userAgent = $_SERVER["HTTP_USER_AGENT"];
	}

	static protected function isNew($ip = null)
	{
		$record = Visitor::getByIp($ip);

		return !isset($record);
	}

	static public function getByIp($ip = null)
	{
		$ip = isset($ip) ? $ip : Tools::getIp();

		$visitor = Visitor::select()
			->where('ip_address', '=', $ip)
			->where('site_id', SITE_ID)
			->where('deleted_flag', 0)
			->first();

		return($visitor);
	}
}
