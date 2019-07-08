<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use DB;
use App;

define('SITE_ID', intval(env('SITE_ID')));

// event logger info
define('LOG_TYPE_INFO', 1);
define('LOG_TYPE_WARNING', 2);
define('LOG_TYPE_ERROR', 3);
define('LOG_TYPE_EXCEPTION', 4);
define('LOG_TYPE_OTHER', 99);
	
define('LOG_MODEL_ARTICLES', 'articles');
define('LOG_MODEL_BLOGS', 'blogs');
define('LOG_MODEL_BLOG_ENTRIES', 'blog entries');
define('LOG_MODEL_ENTRIES', 'entries');
define('LOG_MODEL_GALLERIES', 'galleries');
define('LOG_MODEL_LOCATIONS', 'locations');
define('LOG_MODEL_OTHER', 'other');
define('LOG_MODEL_PHOTOS', 'photos');
define('LOG_MODEL_SECTIONS', 'sections');
define('LOG_MODEL_SITES', 'sites');
define('LOG_MODEL_TOURS', 'tours');
define('LOG_MODEL_USERS', 'users');
define('LOG_MODEL_TEMPLATES', 'templates');
define('LOG_MODEL_TRANSLATIONS', 'translations');

define('LOG_ACTION_ACCESS', 'access');
define('LOG_ACTION_ADD', 'add');
define('LOG_ACTION_EDIT', 'edit');
define('LOG_ACTION_DELETE', 'delete');
define('LOG_ACTION_VIEW', 'view');
define('LOG_ACTION_SELECT', 'select');
define('LOG_ACTION_MOVE', 'move');
define('LOG_ACTION_UPLOAD', 'upload');
define('LOG_ACTION_MKDIR', 'mkdir');
define('LOG_ACTION_RESIZE', 'resize');
define('LOG_ACTION_OTHER', 'other');
define('LOG_ACTION_INDEX', 'index');
define('LOG_ACTION_PERMALINK', 'permalink');

define('LOG_PAGE_INDEX', 'index');
define('LOG_PAGE_VIEW', 'view');
define('LOG_PAGE_SHOW', 'show');
define('LOG_PAGE_GALLERY', 'gallery');
define('LOG_PAGE_SLIDERS', 'sliders');
define('LOG_PAGE_PERMALINK', 'permalink');
define('LOG_PAGE_MAPS', 'maps');
define('LOG_PAGE_LOCATION', 'location');
define('LOG_PAGE_ABOUT', 'about');
define('LOG_PAGE_CONFIRM', 'confirm login');

// translations
define('TRANSLATIONS_FOLDER', '../resources/lang/');

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
						
			if (Controller::startsWith($dn, 'www.'))
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
	
	static protected function startsWith($haystack, $needle)
	{
		$rc = false;
		$pos = strpos($haystack, $needle);

		if ($pos === false) 
		{
			// not found
		} 
		else 
		{
			// found, check for pos == 0
			if ($pos === 0)
			{
				$rc = true;
			}
			else
			{
				// found but string doesn't start with it
			}
		}
		
		return $rc;
	}
	
	static protected function endsWith($haystack, $needle)
	{
		$rc = false;
		$pos = strrpos($haystack, $needle);

		if ($pos === false) 
		{
			// not found
		} 
		else 
		{
			// found, check for pos == 0
			if ($pos === (strlen($haystack) - strlen($needle)))
			{
				$rc = true;
			}
			else
			{
				// found but string doesn't start with it
			}
		}
		
		return $rc;
	}	
}
