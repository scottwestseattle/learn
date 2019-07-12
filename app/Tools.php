<?php

namespace App;

class Tools
{
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
