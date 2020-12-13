<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Tools;

class Geo extends Base
{
	// if geo info was found and loaded
	private $_loaded = false;	// has the geo info been loaded?
	
	private $_test = false;		// is the page test running?
	public function setTest($test)
	{
		$this->_test = $test;
	}
	public function isTest()
	{
		return $this->_test;
	}
	
	// was IP valid and the Geo info found
	private $_valid = false;
	public function isValid()
	{		
		$this->loadGeo();		
		return $this->_valid;
	}
	
	// from _SERVER global variable
	private $_ip = null;
	public function ip()
	{		
		if (!isset($this->_ip))
			$this->_ip = Tools::getIp();
		
		return $this->_ip;
	}
	
	private $_serverName = null;		
	public function serverName()
	{
		if (!isset($this->_serverName))
			$this->_serverName = Tools::getDomainName(); // left this in tools because it's needed statically from different places
		
		return $this->_serverName;
	}

	private $_host = null;
	public function host()
	{
		if (!isset($this->_host))
			$this->_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			
		return $this->_host;
	}

	private $_referrer = null;
	public function referrer()
	{	
		if (!isset($this->_referrer) && array_key_exists("HTTP_REFERER", $_SERVER))
			$this->_referrer = $_SERVER["HTTP_REFERER"];
			
		return $this->_referrer;
	}
	
	private $_userAgent = null;	
	public function userAgent()
	{	
		if (!isset($this->_userAgent) && array_key_exists("HTTP_USER_AGENT", $_SERVER))
			$this->_userAgent = $_SERVER["HTTP_USER_AGENT"];	
			
		return $this->_userAgent;
	}
		
	//
	// from ip2location table
	//
	
	private $_loadTime = -1;
	public function loadTime()
	{
		$this->loadGeo();
		return $this->_loadTime;
	}
	
	private $_countryCode = null;
	public function countryCode()
	{		
		$this->loadGeo();		
		return $this->_countryCode;
	}

	private $_country = null;
	public function country()
	{		
		$this->loadGeo();		
		return $this->_country;
	}
	
	private $_city = null;
	public function city()
	{		
		$this->loadGeo();		
		return $this->_city;
	}	

	private $_locale = 'EN';
	public function locale()
	{		
		$this->loadGeo();
		return $this->_locale;
	}

	// used to show countries
	private $_flag = '/img/flags/blank.png';
	public function flag()
	{		
		$this->loadGeo();
		return $this->_flag;
	}

	private $_flagSize = 0;
	public function flagSize()
	{		
		$this->loadGeo();
		return $this->_flagSize;
	}
	
	// needed for GYG widget
	private $_language = 'en-US';
	public function language()
	{		
		$this->loadGeo();
		return $this->_language;
	}
	
	private $_currency = 'USD';
	public function currency()
	{		
		$this->loadGeo();
		return $this->_currency;
	}
	
	private $_location = 'Unknown';
	public function location()
	{		
		$this->loadGeo();
		return $this->_location;
	}
	
	private $_gygLocation = null;	// ex: Madrid, Spain				
	public function gygLocation()
	{		
		$this->loadGeo();
		return $this->_gygLocation;
	}
	
	// 'language' code is needed for GYG, 'locale' is used to set Laravel Translation: en, es, or zh
	static private $_languages = [
	
		// set these countries to the languages supported by GYG
		'DE' => ['language' => 'de-DE', 'locale' => 'en', 'currency' => 'EUR'],
		'FR' => ['language' => 'fr-FR', 'locale' => 'en', 'currency' => 'EUR'],
		'DK' => ['language' => 'da-DK', 'locale' => 'en', 'currency' => 'EUR'],
		'AT' => ['language' => 'de-AT', 'locale' => 'en', 'currency' => 'EUR'],
		'CH' => ['language' => 'de-CH', 'locale' => 'en', 'currency' => 'EUR'],
		'GB' => ['language' => 'en-GB', 'locale' => 'en', 'currency' => 'EUR'],
		'IT' => ['language' => 'it-IT', 'locale' => 'en', 'currency' => 'EUR'],
		'NL' => ['language' => 'nl-NL', 'locale' => 'en', 'currency' => 'EUR'],
		'NO' => ['language' => 'no-NO', 'locale' => 'en', 'currency' => 'EUR'],
		'PL' => ['language' => 'pl-PL', 'locale' => 'en', 'currency' => 'EUR'],
		'PT' => ['language' => 'pt-PT', 'locale' => 'en', 'currency' => 'EUR'],
		'BR' => ['language' => 'pt-BR', 'locale' => 'en', 'currency' => 'USD'],
		'FI' => ['language' => 'fi-FI', 'locale' => 'en', 'currency' => 'EUR'],
		'SE' => ['language' => 'sv-SE', 'locale' => 'en', 'currency' => 'EUR'],
		'TR' => ['language' => 'tr-TR', 'locale' => 'en', 'currency' => 'EUR'],		
		'ES' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'EUR'],
		'RU' => ['language' => 'ru-RU', 'locale' => 'en', 'currency' => 'USD'],
		'JP' => ['language' => 'ja-JP', 'locale' => 'en', 'currency' => 'USD'],
		
		'CN' => ['language' => 'zh-CN', 'locale' => 'zh', 'currency' => 'USD'],
		'TW' => ['language' => 'zh-TW', 'locale' => 'zh', 'currency' => 'USD'],
		'HK' => ['language' => 'zh-CN', 'locale' => 'zh', 'currency' => 'USD'],
		
		// set remaining Spanish speaking countries to Spanish and USD
		'MX' => ['language' => 'es-MX', 'locale' => 'es', 'currency' => 'USD'],
		'AR' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'BO' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'CL' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'CR' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'CU' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'CO' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'DO' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'EC' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'HN' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'NI' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'PA' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'PE' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'PR' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'SV' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'VE' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'UY' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
		'PY' => ['language' => 'es-ES', 'locale' => 'es', 'currency' => 'USD'],
	];
		
	public function isLocalhost()
	{		
		return ($this->ip() == 'localhost' || $this->ip() == '127.0.0.1' || strlen($this->ip()) < strlen('1.1.1.1') );
	}	
	
	public function visitorInfoDebug()
	{		
		// get info about visitor		
		$info = 'referrer:' . $this->referrer() . ', ip:' . $this->ip() . ', host:' . $this->host() . ', agent:' . $this->userAgent();
			
		return $info;
	}
	
	//
	// Privates
	//

	private function loadGeo()
	{
		$testNoGeo = false; // turn geo load off for testing
		$profile = true;	// turn db profiling on for testing
		
		if ($testNoGeo)
		{
			dump('geo turned off');
			return;
		}
		
		// only load once
		if ($this->_loaded)
			return;
		
		$this->_loaded = true; // load was attempted, may be valid or !valid
		
		$ip = $this->ip();
		
		if ($profile)
			$t = time();
			
		$record = self::getGeo($ip);
		
		if ($profile)
			$this->_loadTime = time() - $t; // load time is secs
		
		if (isset($record))
		{
			$cc = $record->countryCode;
			if ($cc == '-')
			{
				// IP out of range in ip2location
			}
			else
			{
				$this->_city = $record->city == '-' ? '' : $record->city;
				$this->_country = $record->country;
				$this->_countryCode = $cc;
				
				$this->_flag = '/img/flags/' . strtolower($cc) . '.png';
				$this->_flagSize = 30;
					
				// get locale, language, currency overrides from array
				if (($overrides = Tools::getSafeArrayString(self::$_languages, $cc, null)))
				{
					$this->_locale = $overrides['locale'];
					$this->_language = $overrides['language'];
					$this->_currency = $overrides['currency'];
					
					//dump($overrides);
				}
				else
				{
					// use the defaults
				}
				
				// get location display text
				$this->formatLocation();
						
				// consider it a good IP
				$this->_valid = true;
				//dump($this);
			}
		}
	}

	private function formatLocation()
	{		
		if (strlen($this->_city) > 0)
		{
			$this->_gygLocation = $this->_city;
			$this->_location = $this->_city;
		}
		
		if (strlen($this->_country) > 0)
		{
			if (strlen($this->_location) > 0)
				$this->_location .= ', ';

			$this->_location .= $this->_country;
			
			if (strlen($this->_city) == 0)
				$this->_gygLocation = __('geo.' . $this->_country);
		}

		return $this->_location;
    }
	
	static public function getGeo($ip)
	{
		$record = null;
	
		$ipl = ip2long($ip);
		if (is_long($ipl) && $ipl > 0)
		{
			try
			{							
				// worst $q = "SELECT * FROM `ip2locations` WHERE 1 AND $ipl >= `iplongStart` AND $ipl <= `iplongEnd` LIMIT 1";
				// 2nd worst: $q = "SELECT * FROM `ip2locations` USE INDEX (INDEX_IPRANGE) WHERE 1 AND `iplongStart` <= $ipl AND `iplongEnd` >= $ipl";
				$q = "SELECT * FROM `ip2locations` USE INDEX (INDEX_IPRANGE) WHERE 1 AND $ipl >= `iplongStart` AND $ipl <= `iplongEnd` LIMIT 1";
				
				$record = DB::select($q);

				if (false) // doesn't work for some reason
				$record = Ip2location::select()
					->where('iplongStart', '<=', $ipl)
					->where('iplongEnd', '>=', $ipl)
					->get();

				//dump($record);

				if (isset($record) && count($record) > 0)
				{
					$record = $record[0];
				}
				else
				{
					// no records
					$record = null;
				}
					
				//dump($record);
			}
			catch (\Exception $e)
			{
				$msg = 'Error in Geo::getGeo: ip=' . $ip . ', ipl=' . $ipl . ', ' . $e->getMessage();
				Event::logException(LOG_MODEL_TOOLS, LOG_ACTION_SELECT, $msg, null, null);
				//dd('error in geo');
			}
		}
            		
		return $record;
	}	
}
