<?php

namespace App;

use Auth;
use App\User;

class Tools
{
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

	// if string has non-whitespace chars, then it gets trimmed, otherwise gets set to null
	static protected function trimNull($text, $alphanum = false)
	{
		if (isset($text))
		{
			$text = trim($text);

			if ($alphanum)
			{
				// remove all but the specified chars
				$text = preg_replace("/[^a-zA-Z0-9!@.,()-+=?!' \r\n]+/", "", $text);
			}

			if (strlen($text) === 0)
				$text = null;
		}

		return $text;
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
			$v = $_SERVER["SERVER_NAME"];

			// trim the duba duba duba
			if (Tools::startsWith($v, 'www.'))
				$v = substr($v, 4);
		}

		return $v;
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

	static public function endsWith($haystack, $needle)
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
		catch (\Exception $e)
		{
			dump('error writing file: ' . $filename);
		}

		return $rc;
	}
}
