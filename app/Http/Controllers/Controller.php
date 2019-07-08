<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	
	protected $domainName = 'Not Set';
	protected $euNoticeAccepted = false;
	protected $euNotice = 'ui.euNotice';
	
	public function __construct()
	{		
		if (array_key_exists("SERVER_NAME", $_SERVER))
		{			
			$dn = $_SERVER["SERVER_NAME"];
						
			if ($this->startsWith($dn, 'www.'))
				$dn = substr($dn, 4);
			
			$this->domainName = $dn;
		}

		// session don't work in constructors, work arround:
		$this->middleware(function ($request, $next){

			// check for EU notice
			$this->euNoticeAccepted = session('eunotice', false);
			
			// set locale according to selected language
			$locale = session('locale');
			if (isset($locale))
				App::setLocale($locale);
			
			return $next($request);
		});
	}
		
	protected function getViewData($vdata = null, $page_title = null)
	{			
		$this->viewData = isset($vdata) ? $vdata : [];
		
		// add-on the mandatory parts
//todo:		$this->viewData['site'] = Controller::getSite();
		$this->viewData['siteTitle'] = 'content.Site Title';
		$this->viewData['domainName'] = Controller::getDomainName();
		$this->viewData['euNoticeAccepted'] = $this->euNoticeAccepted;
		$this->viewData['euNotice'] = $this->euNotice;
		
		if ($this->domainName == 'localhost')
			$this->viewData['localhost'] = true;

		return $this->viewData;
	}
			
	static protected function getIp()
	{
		$ip = null;
		
		if (!empty($_SERVER["HTTP_CLIENT_IP"]))
		{
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else
		{
			$ip = $_SERVER["REMOTE_ADDR"];
		}	

		return $ip;
	}

	static public function getDomainName()
	{
		$dn = 'ui.Domain Unknown';
				
		if (array_key_exists("SERVER_NAME", $_SERVER))
		{			
			$dn = $_SERVER["SERVER_NAME"];
		}
		
		return $dn;
	}
		
}
