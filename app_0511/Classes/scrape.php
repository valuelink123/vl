<?php
namespace App\Classes;
	class Scrape {

		function __construct() {
		}

		function __destruct() {
		}

		/**
		* Finds a pattern in a string and returns the string between the 2nd last and last match
		*
		* @param array $pattern 	Array of strings to match in sequence
		* @param string $page 		The haystack to search in
		* @param int $start 		The starting position (default 0)
		*
		* @return string 			The string between 2nd last and last match in sequence. Returns blank if no match.
		*/

	    static function get_pattern($pattern, $page, $start = 0) {

	        $data = self::get_pattern_full($pattern, $page, $start);

	        if ($data === "") return "";

	        return $data['data'];
	    }


		/**
		* Finds a pattern in a string and returns the string between the 2nd last and last match
		*
		* @param array $pattern 	Array of strings to match in sequence
		* @param string $page 		The haystack to search in
		* @param int $start 		The starting position (default 0)
		*
		* @return array
		*		- string 'data'		The string between 2nd last and last match in sequence. Returns blank if no match.
		*		- int 'end'			The position of the end of the last match in array $pattern
		*/

	    static function get_pattern_full($pattern, $page, $start = 0) {

	        $end = $start;
	        $len = 0;

	        foreach ($pattern as $key => $p) {

	            $start = $end + $len;
	            $end = strpos($page, $p, $start);

	            if ($end === false) return "";
	            $len = strlen($p);
	        }

	        return array(
	            'data' => trim(substr($page, $start, $end - $start)),
	            'end' => $end + $len
	            );
	    }


		/**
		* Repeatedly finds a pattern in a string and returns the string between the 2nd last and last match; as an array of strings
		*
		* @param array $pattern 	Array of strings to match in sequence
		* @param string $page 		The haystack to search in
		* @param int $start 		The starting position (default 0)
		* @param int max 			The maximum number of pattern matches to return
		*
		* @return array string 		Array of strings matched
		*/

	    static function get_pattern_repeat($pattern, $page, $start = 0, $max = 50) {

	        if (!is_numeric($start)) {
	            $start = strpos($page, $start);

	            if ($start === false) return array();
	        }

	        $final = array();
	        $count = 0;

	        do {
	            $data = self::get_pattern_full($pattern, $page, $start);

	            if ($data != "") {
	                $final[] = $data['data'];
	                $start = $data['end'];
	            }

	            $count++;

	        } while (($data != "") && ($count < $max));

	        return $final;
	    }


		/**
		* Finds the string between a <div></div> or other <*></*> tag
		* i.e. <$seed> ...</*>
		*
		* @param string $seed 		The string to find within $page. Will return the string between tags that contains this string.
		* @param string $page 		The haystack to search in
		* @param int $start 		The starting position (default 0)
		* @param bool $inclusive 	TRUE returns surrounding tags. FALSE otherwise
		*
		* @return string 			The string within the <*></*> tags
		*/

	    static function get_div($seed, $page, $start = 0, $inclusive = false) {

	        $data = self::get_div_full($seed, $page, $start, $inclusive);

	        if ($data === "") return "";

	        return $data['data'];

	    }


		/**
		* Finds the string between a <div></div> or other <*></*> tag
		* i.e. <$seed> ...</*>
		*
		* @param string $seed 		The string to find within $page. Will return the string between tags that contains this string.
		* @param string $page 		The haystack to search in
		* @param int $start 		The starting position (default 0)
		* @param bool $inclusive 	TRUE returns surrounding tags. FALSE otherwise
		*
		* @return array
		*		- strng 'data' 		The string within the <*></*> tags
		*		- int 'end'			The position of the end of the closing tag </*>
		*/

	    static function get_div_full($seed, $page, $start = 0, $inclusive = false) {

	        $start = strpos($page, $seed, $start);
	        if ($start === false) return "";

	        if (!$inclusive) $start = strpos($page, '>', $start);
	        else $start = strrpos($page, '<', $start - strlen($page));

	        if ($start === false) return "";

	        $start++;
	        $div_count = 1;
	        $count = 0;

	        $end = $start;

	        while (($div_count > 0) && ($count < 500)) {
	            $next = strpos($page, '</', $end);
	            $next2 = strpos($page, '<', $end);

	            if ($next !== false) {
	                while (substr($page, $next + 1, 3) == '/br') {
	                    $next = strpos($page, '</', $next + 1);

	                    if ($next === false) break;
	                }
	            }

	            if ($next2 !== false) {
	                while ((substr($page, $next2 + 1, 1) == '!') ||
                		   (substr($page, $next2 + 1, 1) == '/') ||
                		   (substr($page, $next2 + 1, 2) == 'br') ||
                		   (substr($page, $next2 + 1, 3) == 'img') ||
                		   (substr($page, $next2 + 1, 2) == 'hr') ||
                		   (substr($page, $next2 + 1, 4) == 'link') ||
                		   (substr($page, $next2 + 1, 5) == 'input')) {
	                    $next2 = strpos($page, '<', $next2 + 1);

	                    if ($next2 === false) break;
	                }
	            }

	            if (($next === false) && ($next2 === false)) break;

	            if ($next === false) {
	                 $end = $next2 + 1;
	                 break;
	            }

	            if ($next2 === false) {
	                $end = $next + 2;
	                $div_count--;
	            }
	            else if ($next < $next2) {
	                $end = $next + 2;
	                $div_count--;
	            }
	            else {
	                $end = $next2 + 1;
	                $div_count++;
	            }
	            $count++;

	        }

	        if ($inclusive) {
	        	$start--;
	        	$end = strpos($page, '>', $end) + 1;
	        }
	        else {
	        	$end -= 2;
	        }

	        $desc = substr($page, $start, $end - $start);

	        return array(
	            'data' => trim($desc),
	            'end' => $end
	            );
	    }


		/**
		* Finds the string between a <div></div> or other <*></*> tag, repeatedly until end of $page or $max found.
		* i.e. <*>... $seed ...</*>
		*
		* @param string $seed 		The string to find within $page. Will return the string between tags that contains this string.
		* @param string $page 		The haystack to search in
		* @param int $start 		The starting position (default 0)
		* @param bool $inclusive 	TRUE returns surrounding tags. FALSE otherwise
		*
		* @return array
		*		- strng 'data' 		The string within the <*></*> tags
		*		- int 'end'			The position of the end of the closing tag </*>
		*/

	    static function get_div_repeat($seed, $page, $start = 0, $inclusive = 0, $max = 50) {

	        if (!is_numeric($start)) {
	            $start = strpos($page, $start);

	            if ($start === false) return array();
	        }

	        $final = array();
	        $count = 0;

	        do {
	            $data = self::get_div_full($seed, $page, $start, $inclusive);
	            if ($data != "") {
	                $final[] = $data['data'];
	                $start = $data['end'];
	            }

	            $count++;

	        } while (($data != "") && ($count < $max));

	        return $final;
	    }


	    /**
	    * Finds the position of the first number in a string
	    *
	    * @param string $text 		The string to search
	    *
	    * @return int 		The position of the first number or false if no number in string
	    */

	    static function get_first_number_pos($text, $start = 0) {

	    	if ($start > 0) $text = substr($text, $start);
	        preg_match('/\d/', $text, $m, PREG_OFFSET_CAPTURE);

	        if (sizeof($m)) return $m[0][1];

	        return false;
	    }


	    /**
	    * Removes a section of a string between two parts
	    *
	    * @param string $from 		Starting string
	    * @param string $to 		Ending string
	    *
	    * @return string 			The string with section removed.
	    */

	    static function remove_between($from, $to, $page) {

	        $final = "";

	        $start = strpos($page, $from);
	        if ($start !== false) {
	            $final .= substr($page, 0, $start);
	            $end = strpos($page, $to, $start + strlen($from));
	        }
	        else {
	            $end = strpos($page, $to, 0);
	        }

	        if ($end !== false) $final .= substr($page, $end + strlen($to));

	        if ($final == "") return $page;
	        return $final;
	    }


	    /**
	    * Returns a section of a string between two parts
	    *
	    * @param string $from 		Starting string
	    * @param string $to 		Ending string
	    *
	    * @return string 			The string between the two parts
	    */

	    static function get_between($from, $to, $page) {
	        $start = strpos($page, $from);

	        if ($start === false) return "";

	        $start += strlen($from);

	        $end = strpos($page, $to, $start);

	        if ($end === false) return "";

	        return substr($page, $start, $end - $start);
	    }



		/**
		* Removes non-numeric characters from a string
		*
		* @param string $text 		String to remove non-numeric characters from
		*
		* @return string 			String without non-numeric characters
		*/

	    static function remove_non_numeric($text) {
	    	return preg_replace("/[^0-9-]/", "", $text);
	    }


		/**
		* Gets value from <input name="" value=""> tags
		*
		* @param string $name 		The name of the tag
		* @param string $page 		The HTML of page
		*
		* @return string 			Formatted product group
		*/

	    function get_value($name, $page) {

	    	return self::get_pattern(array('name="'.$name.'"', 'value="', '"'), $page);

	    }

	}
?>