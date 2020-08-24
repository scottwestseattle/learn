<?php

namespace App;

use Auth;
use Lang;
use App;
use App\User;
use DateTime;

define('SITE_ID_LOCALHOST',	0);
define('SITE_ID_ENGLISH',	1);
define('SITE_ID_ENGLISH50', 2);
define('SITE_ID_SPANISH50', 3);
define('SITE_ID_SPANISH',	4);
define('SITE_ID_PLANCHA',	5);
define('SITE_ID_TEST',		6);
define('SITE_ID_',			7);

class Tools
{
	static private $_accents = 'áÁéÉíÍóÓúÚüÜñÑ'; 

	static private $_sites = [
		'localhost' 			=> SITE_ID_LOCALHOST,
		'english.codespace.us'	=> SITE_ID_ENGLISH,
		'english50.com'			=> SITE_ID_ENGLISH50,
		'spanish50.com'			=> SITE_ID_SPANISH50,
		'spanish.codespace.us'	=> SITE_ID_SPANISH,
		'plancha.codespace.us'	=> SITE_ID_PLANCHA,
	];
	
	static private $_sitesLanguages = [
		//'localhost' 			=> 'es-ES',
		'localhost' 			=> 'en-EN',
		'english.codespace.us'	=> 'en-EN',
		'english50.com'			=> 'en-EN',
		'spanish.codespace.us'	=> 'es-ES',
		'spanish50.com'			=> 'es-ES',
	];	

	static private $_sitesLanguageFlags = [
		//'localhost'			=> LANGUAGE_ENGLISH,
		'localhost'				=> LANGUAGE_SPANISH,
		'english.codespace.us'	=> LANGUAGE_ENGLISH,
		'spanish.codespace.us'	=> LANGUAGE_SPANISH,
		'english50.com'			=> LANGUAGE_ENGLISH,
		'spanish50.com'			=> LANGUAGE_SPANISH,
	];	

    static public function getAccentChars()
    {
		return self::$_accents;
	}
	
    static public function isMobile()
    {
		$useragent = $_SERVER['HTTP_USER_AGENT'];

		$rc = (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
			substr($useragent,0,4)));

		return $rc;
	}

    static public function getOptionArray($options)
    {
		$arr = [];

		// prompt="Select the correct capital"; reverse-prompt="Select the country for the capital"; question-count=20; text-size="medium";
		$key = '/([a-zA-Z\-^=]*)=\"([^\"]*)/i';
		if (preg_match_all($key, $options, $matches))
		{
			if (count($matches) > 2)
			{
				foreach($matches[1] as $key => $data)
				{
					$arr[$data] = $matches[2][$key];
				}
			}
		}

        return $arr;
    }

    static public function getOption($options, $key)
    {
		$r = '';

		// prompt="Select the correct capital"; reverse-prompt="Select the country for the capital"; question-count=20; text-size="medium";
		$key = "/" . $key . '=\"([^\"]*)/';
		if (preg_match($key, $options, $matches))
		{
			//dd($matches);
			if (count($matches) > 1)
			{
				$r = $matches[1];
			}
		}

        return $r;
    }

    static public function itoa($n)
    {
		$rc = '';

		$rc .= isset($n) ? intval($n) : 'null';

		return $rc;
	}

    static public function getSafeArrayInt($array, $key, $default)
    {
		$rc = $default;
		$s = self::getSafeArrayString($array, $key, null);
		if (isset($s))
		{
			$rc = intval($s);
		}

		return $rc;
	}

    static public function getSafeArrayString($array, $key, $default)
    {
		return self::safeArrayGetString($array, $key, $default);
	}

    static public function getSafeArrayBool($array, $key, $default)
    {
		$rc = $default;
		$s = self::getSafeArrayString($array, $key, null);
		if (isset($s))
		{
			$rc = $s;
		}

		return $rc;
	}

    static public function safeArrayGetString($array, $key, $default)
    {
        $v = $default;

        if (isset($array) && is_array($array) && array_key_exists($key, $array))
        {
            $v = $array[$key];
        }

        return $v;
    }

    static public function cleanHtml($text)
	{
		$v = preg_replace('#style="(.*?)"#is', "", $text); // remove styles
		$v = preg_replace('#<p >#is', "<p>", $v); // fix <p>
		//one time fix: $v = self::convertParens($v);
		//dd($v);

		return $v;
	}

    static public function convertParens($text)
	{
		$v = $text;

		$v = preg_replace('/\(/is', "[", $v);	// change ( to [
		$v = preg_replace('/\)/is', "]", $v);	// change ) to ]
		//dd($v);

		return $v;
	}

    static public function getHash($text)
	{
		$s = sha1(trim($text));
		$s = str_ireplace('-', '', $s);
		$s = strtolower($s);
		$s = substr($s, 0, 8);
		$final = '';

		for ($i = 0; $i < 6; $i++)
		{
			$c = substr($s, $i, 1);

			if ($i % 2 != 0)
			{
				if (ctype_digit($c))
				{
                    if ($i == 1)
                    {
                        $final .= "Q";
                    }
                    else if ($i == 3)
                    {
                        $final .= "Z";
                    }
                    else
                    {
                        $final .= $c;
                    }
				}
				else
				{
					$final .= strtoupper($c);
				}
			}
			else
			{
				$final .= $c;
			}
		}

		// add last 2 chars
		$final .= substr($s, 6, 2);

		//echo $final;

		return $final;
	}

	// shortcut
    static public function isAdmin()
    {
		return (Auth::user() && Auth::user()->isAdmin());
	}

    static public function isPaid()
    {
		return (Auth::user() && false); // not implemented yet
	}

	// shortcut
    static public function isSuperAdmin()
    {
		return (Auth::user() && Auth::user()->isSuperAdmin());
	}

    static public function makeNumberArray($start = 1, $end = 10)
    {
		$v = [];

		for ($i = $start; $i < $end; $i++)
			$v[$i] = $i;

		return $v;
	}

    static public function convertToHtml($text)
    {
		$v = $text;

		// check for HTML
		if (strpos($v, '[') !== false)
		{
			//$v = str_replace('[', '<', $v);
			//$v = str_replace(']', '>', $v);
		}
		else if (strpos($v, '<') !== false)
		{
			// has regular html, leave it alone
		}
		else
		{
			// no html so add br's
			$v = nl2br($v);
		}

		return $v;
	}

    static public function convertFromHtml($text)
    {
		$v = $text;

		if (strpos($v, '[') !== false)
		{
			//$v = str_replace('[', '<', $v);
			//$v = str_replace(']', '>', $v);
		}
		else if (strpos($v, '<') !== false)
		{
			// has regular html, so convert it
			//$v = str_replace('<', '[', $v);
			//$v = str_replace('>', ']', $v);
		}

		// replace <table border="1">
		$v = preg_replace("/\<table( *)border=\"1\"\>/", "<table class=\"table table-borderless\">", $v);

		return $v;
	}

    static public function copyDirty($to, $from, &$isDirty, &$updates = null, $alphanum = false)
    {
		$from = Tools::trimNull($from, $alphanum);
		$to = Tools::trimNull($to, $alphanum);

		if ($from != $to)
		{
			$isDirty = true;

			if (!isset($updates) || strlen($updates) == 0)
				$updates = '';

			$updates .= '|';

			if (strlen($to) == 0)
				$updates .= '(empty)';
			else
				$updates .= $to;

			$updates .= '|';

			if (strlen($from) == 0)
				$updates .= '(empty)';
			else
				$updates .= $from;

			$updates .= '|  ';
		}

		return $from;
	}

	static public function getMaxText($text)
	{
		$text = self::trimNull($text);
		
		$text = substr($text, 0, MAX_DB_TEXT_COLUMN_LENGTH);
		
		return $text;
	}

	// if string has non-whitespace chars, then it gets trimmed, otherwise gets set to null
	static public function trimNull($text, $alphanum = false)
	{
		if (isset($text))
		{
			$text = trim($text);

			if ($alphanum)
				$text = alphanum($text);

			if (strlen($text) === 0)
				$text = null;
		}

		return $text;
	}

	static public function alphanumpunct($text)
	{
		return(self::alphanum($text));
	}
	
	static public function alphanum($text, $strict = false)
	{
		if (isset($text))
		{			
			// replace all chars except alphanums, some punctuation, accent chars, and whitespace
			$base = "a-zA-Z0-9 \r\n";
			$punct = "!@.,()-+=?!';";
			
			$match = $base . self::$_accents;
			if (!$strict)
				$match .= $punct;
			
			$text = preg_replace("/[^" . $match . "]+/", "", trim($text));
		}

		return $text;
	}	

	static public function alpha($text)
	{
		if (isset($text))
		{			
			// replace all chars except alphanums, some punctuation, accent chars, and whitespace
			$text = str_replace("\r\n", ' ', $text);
			$base = "a-zA-Z ";			
			$match = $base . self::$_accents;
			
			$text = preg_replace("/[^" . $match . "]+/", "", trim($text));
		}

		return $text;
	}	
	
	static public function getTextOrShowEmpty($text)
	{
		$r = '(empty)';
		
		if (isset($text))
		{
			$text = trim($text);
			
			if (strlen($text) === 0)
				$text = null;
			else
				$r = $text;
		}
		
		return $r;
	}	

    static public function createPermalink($title, $date = null)
    {
		$v = null;

		if (isset($title))
		{
			$v = $title;
		}

		if (isset($date))
		{
			$v .= '-' . $date;
		}

		$v = preg_replace('/[^\da-z ]/i', ' ', $v); // replace all non-alphanums with spaces
		$v = str_replace(" ", "-", $v);				// replace spaces with dashes
		$v = strtolower($v);						// make all lc
		$v = Tools::trimNull($v);					// trim it or null it

		return $v;
	}

	static public function flash($level, $content)
	{
		request()->session()->flash('message.level', $level);
		request()->session()->flash('message.content', $content);
    }

	static public function getDomainName()
	{
		$v = null;

		if (array_key_exists("SERVER_NAME", $_SERVER))
		{
			$v = strtolower($_SERVER["SERVER_NAME"]);

			// trim the duba duba duba
			if (Tools::startsWith($v, 'www.'))
				$v = substr($v, 4);
		}

		return $v;
	}

	static public function getSiteIds()
	{
		$ids = array_flip(self::$_sites);
		
		return $ids;
	}
	
	static public function getSiteId()
	{
		$siteId = -1;
		$domain = self::getDomainName();
		if (array_key_exists($domain, self::$_sites))
		{
			$siteId = self::$_sites[$domain];
		}
		//dump($domain . " " . $siteId);
		
		return $siteId;
	}

	static public function getSiteName($id)
	{
		$id = intval($id);
		$rc = "not found";
		
		$sites = array_flip(self::$_sites);
		if (array_key_exists($id, $sites))
		{
			$rc = $sites[$id];
		}
			
		return $rc;
	}
	
	static public function siteUses($model)
	{
		$rc = false;
		$siteId = self::getSiteId();
		switch($siteId)
		{
			case SITE_ID_LOCALHOST: // localhost
				$rc = ($model == LOG_MODEL_COURSES);
				$rc = ($model == LOG_MODEL_ARTICLES || $model == LOG_MODEL_DEFINITIONS);
				$rc = true;
				break;
			case SITE_ID_ENGLISH:	// english.codespace
			case SITE_ID_ENGLISH50: // english50
				$rc = ($model == LOG_MODEL_ARTICLES); // only articles
				break;
			case SITE_ID_PLANCHA:
				$rc = ($model == LOG_MODEL_COURSES);
				break;
			case SITE_ID_SPANISH: // spanish.codespace.us
				$rc = ($model == LOG_MODEL_ARTICLES); // only articles
				break;
			case SITE_ID_SPANISH50: // spanish50
				$rc = true;
				break;
			default:
				break;
		}

		return $rc;
	}

	static public $_lineSplitters = array('Mr.', 'Miss.', 'Sr.', 'Mrs.', 'Ms.', 'St.');
	static public $_lineSplittersSubs = array('Mr:', 'Miss:', 'Sr:', 'Mrs:', 'Ms:', 'St:');
	static public $_fixWords = array(
		'Mr.', 'Mrs.', 'Miss.', 'Y, ', 'Y; ', 
		'Jessica', 'Jéssica', 'Jess', 
		'Max', 'Aspid', 'Áspid',
		'Mariel', 'MARIEL', 'Beaumont', 'BEAUMONT',
		'Dennis', 
		'Geovanny', 'Giovanny', 'Geo', 'Gio',
		);
	static public $_fixWordsSubs = array(
		'Señor', 'Señora', 'Señorita', 'Y ', 'Y ', 
		'Sofía', 'Sofía', 'Sofía', 
		'Pedro', 'Picapiedra', 'Picapiedra',
		'Gerarda', 'Gerarda', 'Gonzalez', 'Gonzalez',
		'Fernando', 
		'Jorge', 'Jorge', 'Jorge', 'Jorge',
		);

	static public function getSentences($text)
	{		
		$lines = [];
		
		$paragraphs = explode("\r\n", strip_tags(html_entity_decode($text)));
		foreach($paragraphs as $p)
		{		
			$p = trim($p);

			// doesn't work for: "Mr. Tambourine Man" / Mr. Miss. Sr. Mrs. Ms. St.
			$p = str_replace(self::$_lineSplitters, self::$_lineSplittersSubs, $p);
			
			// sentences end with: ". " or "'. " or "\". "
			$sentences = preg_split('/(\. |\.\' |\.\" )/', $p, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			//$sentences = explode($eos, $p);
			for($i = 0; $i < count($sentences); $i++)
			{
				// get the sentence text
				$s = self::formatForReading($sentences[$i]);
				
				// get the delimiter which is stored in the next array entry				
				$i++;
				if (count($sentences) > $i)
				{
					$s .= trim($sentences[$i]);
				}
				
				// save the sentence
				if (strlen($s) > 0)
				{
					$lines[] = $s;
				}			
			}
		}	
		
		//dump($lines);
		return $lines;
	}
		
	static public function formatForReading($text)
	{		
		// change dash to long dash so it won't be read as 'minus'
		$text = str_replace('-', '–', trim($text));
	
		// put the sentence splitter subs back to the originals
		$text = str_replace(self::$_lineSplittersSubs, self::$_lineSplitters, $text);
	
		// apply any word fixes
		$text = str_replace(self::$_fixWords, self::$_fixWordsSubs, $text);
	
		return $text;
	}
	
	static public function getSites()
	{		
		return self::$_sites;
	}

	static public function getSpeechLanguage($language_flag)
	{
		if ($language_flag == LANGUAGE_ENGLISH)
			return 'en-EN';
		else if ($language_flag == LANGUAGE_SPANISH)
			return 'es-ES';
		else
			return 'en-EN';	
	}
	
	static public function getLanguage()
	{		
		$rc = 'en-US';
		
		$domain = self::getDomainName();
		if (array_key_exists($domain, self::$_sitesLanguages))
		{
			$rc = self::$_sitesLanguages[$domain];
		}
	
		return $rc;
	}

	static public function getLanguageFlag()
	{		
		$rc = LANGUAGE_ENGLISH;
		
		$domain = self::getDomainName();
		if (array_key_exists($domain, self::$_sitesLanguageFlags))
		{
			$rc = self::$_sitesLanguageFlags[$domain];
		}
	
		return $rc;
	}
	
	static public function getSiteTitle($withDomainName = true)
	{
		$d = self::getDomainName();
		$s = '';

		if ($d == 'spanish50.com')
			$s = Lang::get('content.Site Title Spanish');
		else
			$s = Lang::get('content.Site Title English');

		$s = $withDomainName ? $d . ' - ' . $s : $s;

		return $s;
	}

	static public function isLocalhost()
	{
		$ip = self::getIp();

		return ($ip == '::1');
	}

	static public function getIp()
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

	static public function trunc($string, $length)
	{
		$ellipsis = '...';
		$newLength = $length - strlen($ellipsis);
		$string = (strlen($string) > $length) ? substr($string, 0, $newLength) . $ellipsis : $string;

		return $string;
	}

	static public function getFlashMessage($msg)
	{
		$rc = '';
		$translated = 'translated.'; // prefix to show that it was already translated
		$flash = 'flash.'; // no prefix means not translated, so lang key will be added

		if (self::startsWith($msg, $translated))
		{
			// already translated
			$rc = substr($msg, strlen($translated)); // remove the prefix
		}
		else
		{
			// needs translation from the 'flash' section
			$rc = Lang::get($flash . $msg); // add the prefix 'flash' and get the translation
		}

		return $rc;
	}

	static public function startsWith($haystack, $needle)
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

	static public function endsWithAny($haystack, $needle)
	{
		$rc = false;
		
		if (is_array($needle))
		{
			foreach($needle as $n)
			{
				$rc = self::endsWith($haystack, $n);
				if ($rc) // if it ends with any of them then it's true
					break;
			}
		}
		
		return $rc;
	}

	static public function endsWith($haystack, $needle)
	{
		$rc = false;
		
		$pos = strlen($haystack) - strlen($needle); // get the end
		if ($pos > 0) // not the same length
		{
			if ($needle === substr($haystack, $pos)) // matches
			{
				$rc = true;
			}
		}
		
		return $rc;
	}
	
	static public function endsWithORIG($haystack, $needle)
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

	// appends text if it doesn't already exist.
	static public function appendIfMissing($haystack, $needle)
	{
		if (!self::endsWith($haystack, $needle))
		{
			$haystack .= $needle;
		}

		return $haystack;
	}

	static public function splitSentences($string)
	{
		$sentences = null;

		if (isset($string))
		{
			$pattern = '/[\r\n]/';
			$parts = preg_split($pattern, $string);

			foreach($parts as $part)
			{
				$part = trim($part);
				if (strlen($part) > 0)
					$sentences[] = ucfirst($part); // self::appendIfMissing(ucfirst($part), '.');
			}
		}

		return $sentences;
	}

	static public function appendFile($filename, $line)
	{
		$rc = false;

		try
		{
			$myfile = fopen($filename, "a") or die("Unable to open file!");

			fwrite($myfile, utf8_encode($line . PHP_EOL));

			fflush($myfile);
			fclose($myfile);

			$rc = true;
		}
		catch(\Exception $e)
		{
			dump('error writing file: ' . $filename);
		}


		return $rc;
	}

	static public function getTimestamp()
	{
		$dt = new DateTime();

		return $dt->format('Y-m-d H:i:s');
	}

	static public function getPhotos($path)
	{
	    $array = [];

		try
		{
            // '/img/plancha'
            $path = base_path() . '/public' . $path;
            $files = scandir($path);
//dd($files);
            $i = 0;
            foreach($files as $file)
            {
                try {
                    //$file = strtolower($file);
                    if (!is_dir($path . $file))
                    {
                        if (exif_imagetype($path . $file) !== FALSE)
                            $array[$file] = $file;
                    }
                }
                catch (\Exception $e)
                {
                    // ignore the read errors
                    dd('file type: ' . $e);
                }

            }
            //dd($array);
        }
		catch (\Exception $e)
		{
            //dd($e->getMessage());
		    $msg = "Error reading directory: " . $path;
			Event::logException(LOG_MODEL_LESSONS, LOG_ACTION_SCANDIR, $msg, null, $e->getMessage());
			Tools::flash('danger', $msg);
		}

	    return $array;
	}

	static public function secondsToTime($seconds)
	{
	    $seconds = intval($seconds);
	    $time = '';

        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);

        if ($hours > 0)
            $time = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        else
            $time = sprintf('%02d:%02d', $mins, $secs);

        return $time;
    }

    static public function timestamp2date($timestamp)
    {
		return self::translateDate($timestamp);
	}
	
    static public function translateDate($date)
    {		
		$dateFormat = "%B %e, %Y";
				
		if (App::getLocale() == 'es')
		{
			$dateFormat = "%e " . __('ui.of') . ' ' . __('ui.' . strftime("%B", strtotime($date))) . ", %Y";
			
		}
		else if (App::getLocale() == 'zh')
		{
			// 2019年12月25日
			$dateFormat = "%Y" . __('ui.year') . "%m" . __('ui.month') . "%e" . __('ui.date');
		}
		else
		{
		}	
		
		$date = strftime($dateFormat, strtotime($date));
		
		return $date;
	}	
	
    static public function getWordStats($text, $words = null)
    {	
		$words = isset($words) ? $words : [];
		
		$text = strip_tags(html_entity_decode($text));
		$text = str_replace("\r\n", ' ', $text);
		$parts = explode(' ', $text);
		foreach($parts as $part)
		{			
			$word = strtolower(trim($part));
			$word = self::alphanum($word, true);
						
			if (strlen($word) > 0)
			{
				if (array_key_exists($word, $words))
				{
					$words[$word]++;
				}
				else
				{
					$words[$word] = 1;
				}
			}
		}
		
		ksort($words);
		$stats['sortAlpha'] = $words;
		
		arsort($words);
		$stats['sortCount'] = $words;
		
		$stats['wordCount'] = str_word_count($text);
		$stats['uniqueCount'] = count($words);
	
		return $stats;
	}	
}
