<?php
namespace App\Classes;
	use App\Classes\Scrape;
	use App\Classes\amzCaptcha;
	use App\Classes\Encoding;
	class amzScrape extends Scrape {
		/**
		* @var string $proxy_ip		Proxy IP
		* @var string $proxy_pass	Proxy password
		* @var bool $socks5 		SOCKS5 (otherwise HTML). Used mainly for TOR.
		* @var string $cookie_file	Where the cookie is stored
		*/

		private $proxy_ip;
		private $proxy_pass;
		private $socks5;
		private $cookie_file;
		private $cookie_folder;



		protected $product_group_alias = [
			"Prime Pantry"				 => [],
			"Appliances" 				 => ['Major Applicances'],
			"Arts, Crafts & Sewing" 	 => ['Art And Craft Supply'],
			"Automotive"				 => [],
			"Baby" 						 => ['Baby Product'],
			"Beauty"					 => [],
			"Books" 					 => ['Book'],
			"Camera & Photo" 			 => ['Photo'],
			"Cell Phones & Accessories"  => ['Wireless'],
			"Clothing" 					 => ['Apparel'],
			"Collectible Coins" 		 => ['Coins'],
			"Computers & Accessories" 	 => ['Pc', 'Computers'],
			"Electronics" 				 => ['Car Audio Or Theater', 'CE', 'Gps Or Nav System'],
			"Entertainment Collectibles" => ['Entmnt Collectibles'],
			"Grocery & Gourmet Food" 	 => ['Grocery'],
			"Health & Personal Care" 	 => ['Health And Beauty'],
			"Home & Kitchen" 			 => ['Home'],
			"Home Improvement" 			 => ['Lighting'],
			"Industrial & Scientific" 	 => ['Biss'],
			"Jewelry"					 => [],
			"Kindle Store" 				 => ['eBooks'],
			"Kitchen & Dining" 			 => ['Kitchen'],
			"Musical Instruments"		 => [],
			"Office Products" 			 => ['Office Product'],
			"Patio, Lawn & Garden" 		 => ['Lawn And Garden'],
			"Pet Supplies" 				 => ['Pet Products'],
			"Shoes"						 => [],
			"Software" 					 => ['Digital Software'],
			"Sports & Outdoors" 		 => ['Sports'],
			"Toys & Games" 				 => ['Toy'],
			"Watches" 					 => ['Watch'],
			"Video Games"				 => [],
		];

		// Domains which require https://

		protected $https_amazon = [
				// 'www.amazon.com',
				'www.amazon.co.uk',
				'www.amazon.com.br',
				'www.amazon.ca',
				'www.amazon.cn',
				// 'www.amazon.fr',
				'www.amazon.de',
				'www.amazon.in',
				// 'www.amazon.it',
				'www.amazon.co.jp',
				'www.amazon.es'
			];


		/**
		* Constructor. Also sets the proxy parameters.
		*
		* @param string $ip 	Proxy IP to be set
		* @param string $pass 	Proxy password to use
		* @param bool $socks5 	TRUE to use Socks5, FALSE for HTTP
		* @param string $cookie_folder 	Folder where cookie files will be kept
		*
		* @return void
		*/

		function __construct($ip = "", $pass = "", $socks5 = false, $cookie_folder = "/tmp") {
			self::set_proxy($ip, $pass, $socks5);

			$this->cookie_folder = $cookie_folder;
		}

		function __destruct() {
			if ($this->cookie_file != "") {
				if (file_exists($this->cookie_file)) unlink($this->cookie_file);
			}
		}


		/**
		* Set the proxy parameters.
		*
		* @param string $ip 	Proxy IP to be set
		* @param string $pass 	Proxy password to use
		* @param bool $socks5 	TRUE to use Socks5, FALSE for HTTP
		*
		* @return void
		*/

		function set_proxy($ip, $pass, $socks5 = false) {
			$this->proxy_ip = $ip;
			$this->proxy_pass = $pass;
			$this->socks5 = $socks5;
		}


		/**
		* Get the proxy parameters
		*
		* @return array ( string 'proxy', string 'password', bool 'socks5' )
		*/

		function get_proxy() {

			// Gets proxy details

			return array(
				'proxy' => $this->proxy_ip,
				'password' => $this->proxy_pass,
				'socks5' => $this->socks5
				);
		}


		/**
		* Sets up the curl handler using parameters of class
		*
		* @param string $url 	URL of page (optional)
		* @param int $timeout 	timeout in seconds (default: 60s)
		* @param array $header 	HTTP headers
		* @param bool $cookie 	Sets up cookie jar
		*
		* @return resource Curl Handler
		*/

		function get_curl_handler($url = "", $timeout = 60, $header = "Content-type: text/html; charset=UTF-8", $cookie = false, $followlocation = false, $encoding = "UTF-8") {

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			if ($this->socks5) curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);

			curl_setopt($ch, CURLOPT_PROXY, $this->proxy_ip);
			if ($this->proxy_pass != "") curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy_pass);

			if ($header != "") curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

			// curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:39.0) Gecko/20100101 Firefox/39.0");
			//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:42.0) Gecko/20100101 Firefox/42.0");
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36");
			// curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36");

			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followlocation);
			curl_setopt($ch, CURLOPT_ENCODING, $encoding);

			// Removes and creates new cookie file

			if ($this->cookie_file != "") {
				if (file_exists($this->cookie_file)) unlink($this->cookie_file);
			}


			if ($cookie) {
			    $this->cookie_file = $this->cookie_folder."/".uniqid("curl_cookie").".txt";
			    if (file_exists($this->cookie_file)) unlink($this->cookie_file);

			    curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
			    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file );
			    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file );
			}


		    if (!is_int($timeout)) $timeout = 60;
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_URL, $url);

			return $ch;
		}


		/**
		* Function to retrieve page $url
		*
		* @param string $url 		URL of page to retrieve
		* @param int $max_retry 	How many times to retry when getting a captcha page
		* @param resource $ch 		Curl handler. If blank will get new curl handler.
		* @param array $header 		HTTP headers. Only needed if Curl hander not passed.
		*
		* @return array
		*		'html' => html of page
		*		'error' => 0 success, 1 error
		*		'error_message' => description of error or blank on success
		*/

		function get_page($url, $max_retry = 5, $curl_handler = "", $header = "", $followlocation = false) {

			// Get curl handler

			if ($curl_handler == "") $ch = self::get_curl_handler($url, 180, $header, true, $followlocation);
			else {
				$ch = $curl_handler;
				curl_setopt($ch, CURLOPT_URL, $url);
			}

			$retry = 0;			// Count of retry when curl gets captcha
			$curl_error = 0;	// Curl error flag
			$curl_exec = "Type the characters you see in this image";
			$error_message = "";
			$max_retry =3;

			// Start get page loop

			while ((strpos($curl_exec, "Type the characters you see in this image") !== false) && ($retry < $max_retry) && (!$curl_error)) {

				echo "Retry: ".$retry.", ".$url."\n";

				set_time_limit(180);

				curl_setopt($ch, CURLOPT_URL, $url);
				$curl_exec = curl_exec($ch);

				if(curl_errno($ch)) {

					// curl error

					$error_message = 'curl error:'.curl_error($ch);
					echo $error_message."\n";
				    $curl_error = 1;
					sleep(rand(2, 6));
				}

				if (strpos($curl_exec, "502 Bad Gateway") !== false) {
					$error_message = "error: 502 Bad Gateway";
					$curl_error = 1;
					sleep(rand(2, 6));
				}

				if ((strpos($curl_exec, "To discuss automated access to Amazon data please contact api-services-support@amazon.com") !== false) ||
					(strpos($curl_exec, "Type the characters you see in this image") !== false)) {

					echo "Captcha!\n";


					// Solve captcha

					$solve_url = amzCaptcha::getCaptchaURL($curl_exec);

					echo "Url: ".$solve_url."\n";

					sleep(rand(5, 8));

					if ($solve_url != "") {

						curl_setopt($ch, CURLOPT_URL, $solve_url);
						$curl_exec = curl_exec($ch);

						$curl_exec = "Type the characters you see in this image";
						sleep(rand(2, 6));
					}
					else {
						curl_setopt($ch, CURLOPT_URL, "https://www.amazon.com");
						curl_exec($ch);
						$curl_exec = "Type the characters you see in this image";
						sleep(rand(2, 6));
					}

					$retry++;
				}
				else if (strpos($curl_exec, "ERROR: The requested URL could not be retrieved") !== false) {
					$retry++;
					$curl_exec = "Type the characters you see in this image";
					sleep(rand(2, 6));
				}

				echo "Retry: ".$retry.", Max Retry: ".$max_retry.", Curl Error: ".$curl_error."\n";
			}


			// Max retries hit

			if ($retry == $max_retry) {
				$error_message = "error: ".$max_retry." Captchas";
				$curl_error = 1;
			}


			$html = (!$curl_error) ? $curl_exec : "";


			// Only close the curl handler if it was not passed in arguments


			if ($curl_handler == "") {
			    if (($this->cookie_file != "") && (file_exists($this->cookie_file))) {

			    	unlink($this->cookie_file);
			    	$this->cookie_file = "";
			    }

				curl_close($ch);
			}

			if ((strpos($url, "www.amazon.fr") !== false) ||
				(strpos($url, "www.amazon.de") !== false) ||
				(strpos($url, "www.amazon.es") !== false))
				$html = Encoding::fixUTF8($html);


			return array('html' => $html, 'error' => $curl_error, 'error_message' => $error_message);
		}


		/**
		* Function to retrieve an Amazon search page
		*
		* @param string $amazon_site 	Amazon domain. e.g. www.amazon.co.uk
		* @param string $keyword 		keyword to search for
		* @param int $page 				Page number. First page is 1.
		*
		* @return array
		*		string 'html' => html of page
		*		bool 'error' => 0 success, 1 error
		*		string 'error_message' => description of error or empty string on success
		*/

		function get_search_page($amazon_site, $keyword, $page = 1) {

			$url = $amazon_site."/s/?keywords=".urlencode($keyword);

			if ($page > 1) {

				// Add qid timestamp if search page is 2 or higher (to comply with Amazon format)

				date_default_timezone_set("UTC");
				$url .= "&page=".$page."&qid=".time();
			}

			return self::get_page($url, 5, "", "", true);
		}


		/**
		* Function to retrieve an Amazon product page
		*
		* @param string $amazon_site 	Amazon domain. e.g. www.amazon.co.uk
		* @param string $asin 			Product ASIN
		*
		* @return array
		*		string 'html' => html of page
		*		bool 'error' => 0 success, 1 error
		*		string 'error_message' => description of error or empty string on success
		*/

		function get_product_page($amazon_site, $asin) {

			if (strpos($amazon_site, "amazon.") === false) $amazon_site = "www.amazon.".$amazon_site;	// To account for passing only the extendtion. e.g. co.uk rather than www.amazon.co.uk

			return self::get_page($amazon_site."/dp/".$asin, 5, "", "", true);

		}


		/**
		* Function to scrape an Amazon search page
		*
		* @param string $page 	The html of search page
		*
		* @return array
		*		string 'asin'
		*		string 'name'
		*		float 'price'
		*		float 'rating'
		*		int 'reviews'
		*		string 'image'
		*		int 'rank'
		*
		* 		- Note: key of the array is also the rank
		*/

		function scrape_search_page($page) {

			$start = 0;
			$asin_list = array();

			if (strpos($page, 'id="results-atf-next"') !== false) {
				$page = substr($page, 0, strpos($page, 'id="results-atf-next"'));
			}

			while (self::get_div("id=\"result_", $page, $start, 1) != "") {

				$div_full = self::get_div_full("id=\"result_", $page, $start, 1);
				$div = $div_full['data'];
				$start = $div_full['end'];

				$rank = self::get_pattern(array('result_', '"'), $div);

				if ((is_numeric($rank)) && (strpos($div, "s-sponsored") === false)) {	// Don't scrape sponsored products

					$asin = self::get_pattern(array('data-asin="', '"'), $div);
					$name = self::get_pattern(array('title="', '"'), $div);
					if ($name == "") $name = self::get_pattern(array('a-link-normal', '>', '<'), $div);

					$price = self::get_pattern(array('a-color-price', '>', '<'), $div);
					$prices = explode("-", $price);
					$price = $prices[0];
				    $price = preg_replace("/[^0-9]/", "", $price);
				    if (!is_numeric($price)) $price = "";
				    else $price = (float) $price;


					$rating = self::get_pattern(array('a-icon-star', 'a-icon-alt', '>', ' '), $div);
					$rating = str_replace(",", ".", $rating);

					if (!is_numeric($rating)) $rating = "";

					$reviews = self::get_pattern(array('#customerReviews', '>', '<'), $div);
				    $reviews = preg_replace("/[^0-9]/", "", $reviews);

				    $image = self::get_pattern(array('img', 'src="', '"'), $div);

				    $text_bold = self::get_pattern_repeat(array('a-text-bold', '>', '<'), $div);
				    $product_group = "";

				    foreach ($text_bold as $text) {
				    	$text = trim(strtolower(str_replace(":", "", $text)));

				    	foreach ($this->product_group_alias as $key => $aliases) {

				    		if ($text == strtolower($key)) $product_group = $key;

				    		foreach ($aliases as $alias) {
					    		if ($text == strtolower($alias)) $product_group = $key;
				    		}
				    	}
				    }

				    if ($product_group == "") {
				    	$product_group = "xxxx";

				    	if (strpos(self::get_pattern(array('<h2', '</h2'), $div), 'acs-mn-title') !== false) echo "NOT PRODUCT!!";
				    }

					$product_details = array(
						'asin' => $asin,
						'name' => $name,
						'product_group' => $product_group,
						'price' => $price,
						'rating' => $rating,
						'reviews' => $reviews,
						'image' => $image,
						'rank' => $rank + 1
						);

					$asin_list[] = $product_details;
				}
			}

			return $asin_list;
		}


		/**
		* Function to scrape an Amazon search page, given the keyword and Amazon domain
		*
		* @param string $amazon_site 	The Amazon domain. e.g. www.amazon.co.uk
		* @param string $keyword 		Keyword to search for
		* @param int $start_page		The page number to start at. First page is 1.
		* @param int $end_page			The page number to end at. Default 50 to effectvely scrape until no more searches.
		*
		* @return array
		*		array 'products'
		*				string 'asin'
		*				string 'name'
		*				float 'price'
		*				float 'rating'
		*				int 'reviews'
		*				string 'image'
		*				int 'rank'
		* 		- Note: key of the array is also the rank
		*
		*		bool 'error'			Error flag
		*		string 'error_message'	HTML of error page or error message
		*		int 'last_page'			Last page number
		*/

		function scrape_search_keyword($amazon_site, $keyword, $start_page = 1, $end_page = 50) {

			$page_no = $start_page;		// Page number count
			$products = array();		// Array of products
			$error = false;				// Error flag
			$error_message = "";		// Error message


			while ($page_no <= $end_page) {

				$search_page = self::get_search_page($amazon_site, $keyword, $page_no);

				if (!$search_page['error']) {

					// Success with retrieving page

					$page_products = self::scrape_search_page($search_page['html']);


					if (sizeof($page_products)) {
						// $products = array_replace($products, $page_products);
						$products = array_merge($products, $page_products);
						$page_no++;
					}
					else {

						if (strpos($search_page['html'], 'id="noResultsTitle"') !== false) {

							// No more results
							$end_page = 0;

						}
						else {

							// No products but not the "no search results" page?
							// Something went wrong

							$end_page = 0;
							$error = true;
							$error_message = $search_page['html'];
						}
					}
				}
				else {

					// Error retrieving page

					$end_page = 0;
					$error = true;
					$error_message = $search_page['error_message'];
				}
			}

			return array(
				'products' => $products,
				'error' => $error,
				'error_message' => $error_message,
				'last_page' => $page_no
				);


		}


		/**
		* Scrapes HTML to find parent ASIN
		*
		* @param string $page 		HTML of page
		* @param string $asin 		ASIN of product (optional)
		*
		* @return string 	The parent ASIN or empty string on error
		*/

		function scrape_parent_asin($page, $asin = "") {

			// Method 1: <input name="parentASIN" value="____">

		    $parent_asin = self::get_pattern(array('name="parentASIN"', 'value="', '"'), $page);
		    if ($parent_asin != "") return $parent_asin;


		    // Method 2: Parent ASIN in a URL on page
		    // Note: there can be multiples of these on a page, some of which are blank

		    $page = strtoupper($page);

		    $parent_asin = self::get_pattern_repeat(array('PARENTASIN=', '&'), $page);
		    $parent_asin = array_unique($parent_asin);
		    $parent_asin = array_filter($parent_asin);


		    if (sizeof($parent_asin)) {
		        if ($asin != "") {
		            foreach ($parent_asin as $key => $value) {
		                if ($value == $asin) return $asin;
		            }
		        }
		        return reset($parent_asin);
		    }


		    // No parent ASIN found

		    return "";
		}


		/**
		* Scrapes HTML to find Sales Rank (BSR)
		*
		* @param string $page 		HTML of page
		*
		* @return string 			The sales rank or empty string on error
		*/

		function scrape_sales_rank($page) {

			// Method 1

		    $div = self::get_div('id="SalesRank"', $page);
		    $div = self::remove_between('<style', '</style>', $div);

		    $pos = self::get_first_number_pos($div);

		    if ($pos !== false) {

			    $div = substr($div, $pos);

			    $end = strpos($div, ' ');
			    $end2 = strpos($div, '<');

			    if ($end2 < $end) $end = $end2;

			    $sales_rank = substr($div, 0, $end);
			    $sales_rank = preg_replace("/[^0-9]/", "", $sales_rank);

			    return $sales_rank;
			}


			// Method 2

			$div = self::get_pattern(array('Best Sellers Rank', '<td','>', '</td>'), $page);

		    $pos = self::get_first_number_pos($div);

		    if ($pos !== false) {

			    $div = substr($div, $pos);

			    $end = strpos($div, ' ');
			    $end2 = strpos($div, '<');

			    if ($end2 < $end) $end = $end2;

			    $sales_rank = substr($div, 0, $end);
			    $sales_rank = preg_replace("/[^0-9]/", "", $sales_rank);

			    return $sales_rank;
			}

		}



		/**
		* Scrapes HTML to find product title
		*
		* @param string $page 		HTML of page
		*
		* @return string 			The product title or empty string on error
		*/

		function scrape_product_title($page) {

			// Method 1

		    $title = self::get_pattern(array('id="productTitle"', '>', '<'), $page);
		    if ($title != "") return $title;


		    // Method 2

		    $title = self::get_pattern(array('id="btAsinTitle"', '>', '</'), $page);
		    $title = trim(strip_tags($title));
		    if ($title != "") return $title;


		    // Method 3
		    $title = self::get_pattern(array('id="ebooksProductTitle"', '>', '</span>'), $page);
		    $title = trim(strip_tags($title));
		    if ($title != "") return $title;


		    // Method 4

		    $title = self::get_pattern(array('"title":"', '"'), $page);
		    $title = trim(strip_tags($title));
		    if ($title == "Oops!") $title = "";
		    if ($title != "") return $title;


		    // Method 5

		    $title = self::get_pattern(array('id="item_name">', '<'), $page);
		    $title = trim(strip_tags($title));
		    if ($title == "Oops!") $title = "";
		    if ($title != "") return $title;


		    // No title found

		    return "";
		}


		/**
		* Scrapes HTML to find URL that Amazon uses for this product
		*
		* @param string $page 		HTML of page
		*
		* @return string 			The product URL or empty string on error
		*/

	    function scrape_product_url($page) {

		    return self::get_pattern(array('canonical', 'href="', '"'), $page);
	    }



		/**
		* Scrapes HTML to find product variations
		*
		* @param string $page 		HTML of page
		*
		* @return array
		*		array
		*			string 'asin'
		*			string 'attributes'
		*/


	    function scrape_product_variations($page) {

	        $variations = array();
	        $data = array();
	        $start = 0;
	        $count = 0;

	        // JSON on page

	        do {
	            $d = self::get_pattern_full(array('dataToReturn =', 'return dataToReturn'), $page, $start);
	            if ($d <> "") {
	                $data[] = trim($d['data'], ';');
	                $start = $d['end'];
	            }

	            $count++;

	        } while (($d <> "") && ($count < 5));

	        foreach ($data as $key => $d) {

	            if (json_decode($d) !== NULL) {

	                $json = json_decode($d, true);

	                if (isset($json['dimensionValuesDisplayData'])) {
	                    foreach ($json['dimensionValuesDisplayData'] as $key => $value) {
	                        $variations[] = array('asin' => $key, 'attributes' => implode(", ", $value));
	                    }
	                }
	            }
	        }


	        // JSON on page type 2

	        $display_data = self::get_pattern(array('dimensionValuesDisplayData = {', '};'), $page);

	        if (sizeof($display_data)) {
	        	$d = self::get_pattern_repeat(array('"', ']'), $display_data);

	        	foreach ($d as $key => $value) {
	        		if (strpos($value, '"') !== false) {
	        			$asin = substr($value, 0, strpos($value, '"'));
	        			$vars = self::get_pattern(array(":[", "]"), $value."]");
	        			$att = self::get_pattern_repeat(array("'", "'"), $vars);
	        			$attributes = implode(", ", $att);

	        			if ($attributes != "") $variations[] = array('asin' => trim($asin), 'attributes' => trim($attributes));
	        		}
	        	}
	        }


	        // JSON on page type 3

	        $display_data = self::get_pattern(array('dimensionValuesDisplayData" : {', '}'), $page);
	        if ($display_data == "") $display_data = self::get_pattern(array('dimensionValuesDisplayData":{', '}'), $page);

	        if (sizeof($display_data)) {
	        	$d = self::get_pattern_repeat(array('"', ']'), $display_data);

	        	foreach ($d as $key => $value) {
	        		if (strpos($value, '"') !== false) {
	        			$asin = substr($value, 0, strpos($value, '"'));

	        			if (strpos($value, '[') !== false) {
		        			$attributes = substr($value, strpos($value, '[') + 1);
		        			$attributes = str_replace('"', '', $attributes);
		        		}
		        		else $attributes = "";

	        			if ($attributes != "") $variations[] = array('asin' => trim($asin), 'attributes' => trim($attributes));
	        		}
	        	}
	        }



	        // Method 2

	        // Unselected

	        $asins = self::get_pattern_repeat(array('swatchElement unselected', '/dp/', '/'), $page);
	        $attributes = self::get_pattern_repeat(array('swatchElement unselected', '<span>', '</span>'), $page);

	        foreach ($asins as $key => $value) {
	            if (isset($attributes[$key])) {
	                $variations[] = array('asin' => $asins[$key], 'attributes' => $attributes[$key]);
	            }
	            else {
	                $variations[] = array('asin' => $asins[$key], 'attributes' => $asins[$key]);
	            }
	        }

	        // Selected

	        $attribute = self::get_pattern(array('swatchElement selected', '<span>', '</span>'), $page);
	        $asin = self::get_pattern(array('name="ASIN"', 'value="', '"'), $page);

	        if (($attribute != "") && ($asin != "")) {
	            $in_array = 0;

	            foreach ($variations as $key => $value) {
	                if ($value['asin'] == $asin) $in_array = 1;
	            }

	            if (!$in_array) $variations[] = array('asin' => $asin, 'attributes' => $attribute);
	        }

	        // Method - dropdown - dimensionMatrix['']

	        $asins = self::get_pattern_repeat(array("dimensionMatrix['", ".ASIN = '", "'"), $page);
	        $desc1 = self::get_pattern_repeat(array("dimensionMatrix['", ".desc1 = '", "'"), $page);
	        $desc2 = self::get_pattern_repeat(array("dimensionMatrix['", ".desc2 = '", "'"), $page);
	        $desc3 = self::get_pattern_repeat(array("dimensionMatrix['", ".desc3 = '", "'"), $page);

	        if (sizeof($asins)) {
	            foreach ($asins as $key => $value) {

	                $var = "";
	                if (isset($desc1[$key])) $var .= $desc1[$key];
	                if (isset($desc2[$key])) $var .= ", ".$desc2[$key];
	                if (isset($desc3[$key])) $var .= ", ".$desc3[$key];

	                if ($var == "") $var = $value;

	                $variations[] = array('asin' => $value, 'attributes' => $var);
	            }
	        }


	        // Method - dropdown - dimensionMatrix.

	        $asins = self::get_pattern_repeat(array("dimensionMatrix.", ".ASIN = '", "'"), $page);
	        $desc1 = self::get_pattern_repeat(array("dimensionMatrix.", ".desc1 = '", "'"), $page);
	        $desc2 = self::get_pattern_repeat(array("dimensionMatrix.", ".desc2 = '", "'"), $page);
	        $desc3 = self::get_pattern_repeat(array("dimensionMatrix.", ".desc3 = '", "'"), $page);

	        if (sizeof($asins)) {
	            foreach ($asins as $key => $value) {

	                $var = "";
	                if (isset($desc1[$key])) $var .= $desc1[$key];
	                if (isset($desc2[$key])) $var .= ", ".$desc2[$key];
	                if (isset($desc3[$key])) $var .= ", ".$desc3[$key];

	                if ($var == "") $var = $value;

	                $variations[] = array('asin' => $value, 'attributes' => $var);
	            }
	        }

	        // Method - single dropdown

	        $part = self::get_between('id="asinRedirect"', '</select', $page);

	        $asins = self::get_pattern_repeat(array('<option value="', '"'), $part);
	        $attributes = self::get_pattern_repeat(array('<option value="', '>', '<'), $part);


	        foreach ($asins as $key => $value) {
	            if ($value != "") {
	                if (isset($attributes[$key])) {
	                    $variations[] = array('asin' => $asins[$key], 'attributes' => $attributes[$key]);
	                }
	                else {
	                    $variations[] = array('asin' => $asins[$key], 'attributes' => $asins[$key]);
	                }
	            }
	        }



	        // Method 4 - eBooks

	        $asins = self::get_pattern_repeat(array('<tbody id="', 'id="tmm_', '"'), $page);
	        $attributes = self::get_pattern_repeat(array('<tbody id="', 'id="tmm_', 'class="tmm_bookTitle', '>', '</td'), $page);

	        foreach ($asins as $key => $value) {
	            if (isset($attributes[$key])) {
	                $variations[] = array('asin' => $asins[$key], 'attributes' => strip_tags($attributes[$key]));
	            }
	            else {
	                $variations[] = array('asin' => $asins[$key], 'attributes' => $asins[$key]);
	            }
	        }


	        // Remove duplicates

	        $final_variations = [];
	        $final_asins = [];

	        foreach ($variations as $key => $v) {
	        	if (!in_array($v['asin'], $final_asins)) {
	        		$final_variations[] = $v;
	        		$final_asins[] = $v['asin'];
	        	}
	        }
	        return $final_variations;
	    }



		/**
		* Scrapes HTML to find product images
		*
		* @param string $page 		HTML of page
		* @param string $type
		*		- "thumb": thumbnail
		*		- "large": large image
		*
		* @return array 	String array of images found
		*/

	    function scrape_product_images($page, $type) {

	        // Type: hiRes, large, thumb

	        $images = array();
	        $start = 0;

            $images = self::get_pattern_repeat(array('"'.$type.'":"', '"'), $page);

	        if ($type == "thumb") {
	            $type = "thumbUrl";
	            $images = array_merge($images, self::get_pattern_repeat(array('"'.$type.'":"', '"'), $page));
	        }

	        if ($type == "large") {
	            $type = "mainUrl";
	            $images += array_merge($images, self::get_pattern_repeat(array('"originalLargeImage":"', '"'), $page));
	            $images += array_merge($images, self::get_pattern_repeat(array('"'.$type.'":"', '"'), $page));
	            $images += array_merge($images, self::get_pattern_repeat(array('id="mainImageContainer" ', '<img', 'src="', '"'), $page));	// For books
	            $images += array_merge($images, self::get_pattern_repeat(array('addToIVImageSet(', ", ('", "'"), $page));

	            // Books
	            $images_div = self::get_div('id="booksImageBlock_feature_div"', $page);
	            $dynamic_images = self::get_pattern(array('data-a-dynamic-image="', '"'), $images_div);
	            $dynamic_images = self::get_pattern_repeat(array('&quot;', '&quot;'), $dynamic_images);

	            if (sizeof($dynamic_images)) {
	            	$images[] = end($dynamic_images);
	            }

	            // eBooks
	            $images_div = self::get_div('id="ebooksImageBlockContainer"', $page);
	            $dynamic_images = self::get_pattern(array('data-a-dynamic-image="', '"'), $images_div);
	            $dynamic_images = self::get_pattern_repeat(array('&quot;', '&quot;'), $dynamic_images);

	            if (sizeof($dynamic_images)) {
	            	$images[] = end($dynamic_images);
	            }
	        }


	        return array_unique($images);
	    }


		/**
		* Scrapes HTML to find lowest price (in cents) on page.
		* e.g. Finds the discounted price or lowest in range of price ($1.25 - $2.30 will return 125)
		*
		* @param string $page 		HTML of page
		*
		* @return int 	Lowest price in cents or empty string on error
		*/

	    function scrape_product_price_lowest($page) {

	    	// Method 1

	        $price = self::get_pattern(array('priceblock_', '>', '<'), $page);
	        $price = preg_replace("/[^0-9-]/", "", $price);

	        $prices = explode("-", $price);
	        if (isset($prices[0])) {
	            if (is_numeric($prices[0])) return $prices[0];
	        }


	        // Method 2

	        $price = self::get_pattern(array('priceblock_ourprice"', '>', '<'), $page);
	        $price = preg_replace("/[^0-9-]/", "", $price);

	        $prices = explode("-", $price);
	        if (isset($prices[0])) {
	            if (is_numeric($prices[0])) return $prices[0];
	        }


	        // Method 3

	        $price = self::get_pattern(array('priceblock_ourprice"', '</span>', '</span>'), $page);
	        $price = preg_replace("/[^0-9-]/", "", $price);

	        $prices = explode("-", $price);
	        if (isset($prices[0])) {
	            if (is_numeric($prices[0])) return $prices[0];
	        }


	        // Method 4

            $price = self::get_pattern(array('class="priceLarge"', '>', '<'), $page);
            $price = preg_replace("/[^0-9-]/", "", $price);
	        if (is_numeric($price)) return $price;


	        // Method 5

            $price = self::get_div('id="priceblock_saleprice"', $page);
            $price = preg_replace("/[^0-9-]/", "", $price);
	        if (is_numeric($price)) return $price;


	        // Method 6

	        $prices = self::get_pattern_repeat(array('swatchElement', 'a-color-', '>', '</span>'), $page);

	        if (sizeof($prices)) {
	        	$prices = array_map('strip_tags', $prices);
	        	$prices = array_map('self::remove_non_numeric', $prices);
	        	$prices = array_filter($prices);
	        	asort($prices);

	        	if (sizeof($prices)) return reset($prices);
	        }


	        // Method 7

	        $price = self::get_div('id="olp_feature_div"', $page);
	        $price = self::get_div('a-color-price', $price, 0, false);
	        $price = strip_tags($price);
            $price = preg_replace("/[^0-9-]/", "", $price);

            if (is_numeric($price)) return $price;



	        return "";
	    }



		/**
		* Scrapes HTML to find the FULL price (not discounted) in cents on page.
		*
		* @param string $page 		HTML of page
		* @param string $domain 	Amazon domain e.g. www.amazon.co.uk
		*
		* @return int 	Price in cents or empty string on error
		*/

	    function scrape_product_price_full($page, $domain) {

	        $price = "";
	        $div = self::get_div('id="price', $page);
	        $div = mb_convert_encoding($div, "UTF-8");

	        if (($domain == "www.amazon.de") ||
	            ($domain == "www.amazon.fr") ||
	            ($domain == "www.amazon.it") ||
	            ($domain == "www.amazon.es") ||
	            ($domain == "www.amazon.de")) {

	            $price = self::get_pattern(array('EUR', '<'), $div);
	            if ($price == "") $price = self::get_pattern(array('a-color-price', '>', '<'), $page);
	        }

	        if (($domain == "www.amazon.com") ||
	            ($domain == "www.amazon.ca") ||
	            ($domain == "www.amazon.com.br")) {

	            $price = self::get_pattern(array('$', '<'), $div);

	            if ($price == "") $price = self::get_pattern(array('price"', '>', '<'), $page);
	        }

	        if ($domain == "www.amazon.co.uk") {
	            $price = self::get_pattern(array('£', '<'), $div);
	            if ($price == "") $price = self::get_pattern(array('a-color-price', '>', '<'), $page);
	        }

	        if ($domain == "www.amazon.co.jp") {
	            $price = self::get_pattern(array('¥', '<'), $div);
	            if ($price == "") $price = self::get_pattern(array('"price', '>', '<'), $div);
	        }

	        if ($domain == "www.amazon.cn") {
	            $price = self::get_pattern(array('¥', '<'), $div);
	        }

	        if ($domain == "www.amazon.in") {
	            $price = self::get_pattern(array('INR', '<', '<'), $div);
		        $price = strip_tags($price);
		        $price = self::remove_non_numeric($price);

		        if (!is_numeric($price)) {
		            $price = self::get_pattern(array('INR', '<', '<'), $page);
			    }
	        }


	        $price = strip_tags($price);
	        $price = self::remove_non_numeric($price);

	        if (is_numeric($price)) return $price;


	        // Swatches

	        $price = self::get_pattern(array('swatchElement selected', 'a-color-price', '>', '<'), $page);
	        $price = self::remove_non_numeric($price);
	        if (is_numeric($price)) return $price;

	        return "";
	    }


	    /**
	    * Scrapes HTML to find product description
	    *
	    * @param string $page 	HTML of product page
	    *
	    * @return string 		The product description or empty string on error
	    */

	    function scrape_product_description($page) {


	        // eBook

	        $desc = self::get_pattern(array('bookDescEncodedData =', '"', '"'), $page);
	        if ($desc != "") return urldecode($desc);

	        $desc = trim(self::get_pattern(array('<div id="postBodyPS">', '</div>'), $page));
	        if ($desc != "") return urldecode($desc);


	        // Style 1

	        $desc = self::get_pattern(array('iframeContent = "', '";'), $page);

	        if ($desc != "") {

	        	$desc = urldecode($desc);

	        	$desc = self::get_div('<div class="productDescriptionWrapper">', $desc);

	        	return trim($desc);
	        }



	        // Books

        	$desc = self::get_pattern(array('encodedDescription', '"', '",'), $page);

        	if ($desc != "") {
	        	$desc = urldecode($desc);
        		return $desc;
        	}


	        // Style 2

	        $desc = trim(self::get_div('id="productDescription"', $page));
	        if ($desc != "") return $desc;

	        $desc = trim(self::get_div('id="dp_productDescription_container_div"', $page));
	        if ($desc != "") return $desc;

	        $desc = trim(self::get_div('id="descriptionAndDetails"', $page));
	        if ($desc != "") return $desc;


	        // Style 3

	        $start = strpos($page, '<h2>Product Description');
	        if (strpos($page, '<h2>Descrizione del prodotto') !== false) $start = strpos($page, '<h2>Descrizione del prodotto');    // www.amazon.it
	        if (strpos($page, '<h2>Descrizione prodotto') !== false) $start = strpos($page, '<h2>Descrizione prodotto');            // www.amazon.it
	        if (strpos($page, '<h2>Descrições do Produto') !== false) $start = strpos($page, '<h2>Descrições do Produto');          // www.amazon.com.br
	        if (strpos($page, '<h2>Descrição do produto') !== false) $start = strpos($page, '<h2>Descrição do produto');            // www.amazon.com.br
	        if (strpos($page, '<h2>Descriptions du produit') !== false) $start = strpos($page, '<h2>Descriptions du produit');      // www.amazon.fr
	        if (strpos($page, '<h2>Description du produit') !== false) $start = strpos($page, '<h2>Description du produit');        // www.amazon.fr
	        if (strpos($page, '<h2>Descripci') !== false) $start = strpos($page, '<h2>Descri');                                     // www.amazon.es
	        if (strpos($page, '<h2>商品描述') !== false) $start = strpos($page, '<h2>商品描述');                                      // www.amazon.cn
	        if (strpos($page, '<h2>Produktbeschreibungen') !== false) $start = strpos($page, '<h2>Produktbeschreibungen');          // www.amazon.de


	        $desc = trim(self::get_div('<div class="content">', $page, $start));

	        if ($desc != "") return $desc;


	        // No description found

	        return "";
	    }


        /**
         * Scrapes HTML to find product aplus
         *
         *Add a comment to this line
         * @param string $page 	HTML of product page
         *
         * @return string 		The product aplus or empty string on error
         */

        function scrape_product_aplus($page) {
            $desc = trim(self::get_div('class="aplus ', $page));
            if ($desc != "") return $desc;
            $desc = trim(self::get_div('id="aplus"', $page));
            if ($desc != "") return $desc;
            return '';
        }

	    /**
	    * Scrapes HTML to determine if the product is an adult product
	    *
	    * @param string $page 	HTML of product page
	    *
	    * @param int 			1: adult product, 0: not adult product
	    */

	    function scrape_product_adult($page) {

	    	$adult = 0;

	        $div = self::get_div('id="wayfinding-breadcrumbs_container', $page);
	        if (strpos(strtolower($div), "sex") !== false) $adult = 1;
	        if (strpos(strtolower($div), "exotic apparel") !== false) $adult = 1;

	        $div = self::get_div('id="wayfinding-breadcrumbs_feature', $page);
	        if (strpos(strtolower($div), "sex") !== false) $adult = 1;
	        if (strpos(strtolower($div), "exotic apparel") !== false) $adult = 1;

	        $regex = '/dildo|penis|\\bg.spot\\b|\\bvagina|\\banal\\b|vibrator|\\bmasturbat|\\bclitor|\\bsex\\b|\\berection\\b|crotchless/i';

			$title = self::scrape_product_title($page);
	        if (preg_match($regex, $title)) $adult = 1;


	        $regex = '/dildo|penis|\\bg.spot\\b|\\banal\\b|vibrator|\\bmasturbat|\\bclitor|\\berection\\b|crotchless/i';

			$description = self::scrape_product_description($page);
	        if (preg_match($regex, $description)) $adult = 1;

	        // Bullet points

	    	$ul = self::get_div('id="feature-bullets', $page, 0, true);
	    	$li = self::get_div_repeat('<li', $ul);

	    	foreach ($li as $key => $value) {
		        if (preg_match($regex, $value)) $adult = 1;
	    	}

	        return $adult;
	    }


	    /**
	    * Scrape product details given the ASIN and domain
	    *
	    * @param array $asins 			Array of product ASINs to scrape
	    * @param string $amazon_site 	Amazon domain. e.g. www.amazon.co.uk
	    *
	    * @return array
	    *			array
		*				string 'asin'
		*				string 'parent_asin'
		*				string 'name'
		*				string 'sales_rank'
		*				string 'image_small'
		*				string 'image_medium'
		*				string 'image_large'
		*				string 'domain'
		*				string 'url'
		*				int 'price'
		*				int 'price_lowest'
		*				string 'description'
		*				int 'adult'
		*				string 'error'
		*				array 'variations'
		*					string 'asin'
		*					string 'attributes'
	    */


		function scrape_products($asins, $amazon_site) {

			if (strpos($amazon_site, "amazon.") === false) $amazon_site = "www.amazon.".$amazon_site;	// To account for passing only the extension. e.g. co.uk rather than www.amazon.co.uk
		    $final = array();

		    foreach ($asins as $asin) {

		        $page_details = self::get_product_page($amazon_site, $asin);

		        if (!$page_details['error']) {

		        	$page = $page_details['html'];

		            $final[] = self::scrape_product_page($asin, $amazon_site, $page);
		        }
		    }

		    return $final;
		}

		function scrape_product_page($asin, $amazon_site, $page) {

            //$parent_asin = self::scrape_parent_asin($page, $asin);
            $sales_rank = self::scrape_sales_rank($page);
            $title = self::scrape_product_title($page);

            $product_group = self::scrape_product_group($page, $amazon_site);
            $product_group_hidden = "";

            if (sizeof($product_group)) {
            	$pg = $product_group[0]['product_group'];

            	if (strpos($pg, '&gt;') !== false) $product_group_hidden = trim(substr($pg, 0, strpos($pg, '&gt;')));
            	else $product_group_hidden = $pg;
            }

            //$url = self::scrape_product_url($page);
            //$variations = self::scrape_product_variations($page);
            $large_images = self::scrape_product_images($page, "large");
            //$thumb_images = self::scrape_product_images($page, "thumb");

            $price_lowest = self::scrape_product_price_lowest($page);
            //$price = self::scrape_product_price_full($page, $amazon_site);

            $description = self::scrape_product_description($page);
            //$aplus = self::scrape_product_aplus($page);

            //$adult = self::scrape_product_adult($page);
            $bullet_points_array=array();
            $bullet_points_no_trim = self::scrape_product_bullet_points($page, $amazon_site, $asin);
            if(is_array($bullet_points_no_trim)){
                foreach($bullet_points_no_trim as $v){
                    $bullet_points_array[]=trim(strip_tags($v));
               }
            }
            $bullet_points = ($bullet_points_array)?implode(PHP_EOL,$bullet_points_array):'';
            $fba = self::scrape_product_fba($page, $amazon_site);

            //if (sizeof($large_images)) $large_images = $large_images[0];
            //else $large_images = "";

            //if (sizeof($thumb_images)) $thumb_images = $thumb_images[0];
             //else $thumb_images = "";

            $error = "";
            if (self::is_404($page)) $error = "Product page does not exist";
			$product_rating = self::scrape_product_rating($page);
		    $review_count = self::scrape_product_review_count($page);
			$brand = self::scrape_product_brand($page);	
			$seller_count = self::scrape_product_seller_count($page);
            return array(
                'asin' => $asin,
                //'parent_asin' => $parent_asin,
                'name' => $title,
                'product_group' => $product_group,
                'product_group_hidden' => $product_group_hidden,
                'sales_rank' => $sales_rank,
                //'image_small' => $thumb_images,
                'image_large' => $large_images,
                //'domain' => $amazon_site,
                //'url' => $url,
                //'variations' => $variations,
                'error' => $error,
                //'price' => $price,
                'price_lowest' => $price_lowest,
                'description' => $description,
                //'aplus' => $aplus,
                'bullet_points' => $bullet_points,
                //'adult' => $adult,
                'fba' => $fba,
				'product_rating' => $product_rating,
			    'review_count' => $review_count,
				'brand'=> $brand,
				'seller_count'=>$seller_count,
                );

		}


	    /**
	    * Checks if HTML is a 404 page
	    *
	    * @param string $page 		HTML of page
	    *
	    * @return bool 				TRUE if 404, otherwise FALSE
	    */

		function is_404($page) {

		    $title = self::get_pattern(array('<title>', '</'), $page);

		    if ((strlen($title) > 3) && ((substr($title, 0, 3) == "404") || (strpos($title, "Page Not Found") !== false)) ) return true;
		    if (strpos($page, "The Web address you entered is not a functioning page on our site") !== false) return true;

		    return false;
		}


		/**
		* Checks if a product is FBA given the product page
		*
		* @param string $page 		HTML of product page
		* @param string $domain 	Amazon domain
		*
		* @return string 			AMZ: Sold by Amazon, FBA: Fulfilled by Amazon, Merch: Fulfilled by Merchant
		*/

	    function scrape_product_fba($page, $domain = "www.amazon.com") {

	    	// Sold by Amazon

	    	if (preg_match('/((ships|dispatched)\sfrom\sand\ssold\sby\samazon)|(Expédié\set\svendu\spar\sAmazon)|(Verkauf\sund\sVersand\sdurch\sAmazon)/i', $page)) return "AMZ";

	        if (strpos($page, 'isAmazonFulfilled=1') !== false) return "FBA";


	        // Needs a child ASIN chosen before FBA is shown

	        $url = self::get_pattern(array('"immutableURLPrefix":"', '"'), $page);

	        if ($url != "") {
	        	$url = $domain.$url."&psc=1&isFlushing=2&dpEnvironment=hardlines&mType=full";

	        	$variations = self::scrape_product_variations($page);

	        	if (sizeof($variations)) {
	        		$asin = $variations[0]['asin'];

	        		$url .= "&asinList=".$asin."&id=".$asin;

	        		$page = self::get_page($url);

	        		if (strpos($page['html'], 'isAmazonFulfilled=1') !== false) {
	        			return "FBA";
	        		}
	        	}
	        }

	        return "Merch";
	    }


	    /**
	    * Scrapes bullet points from a product page
	    *
	    * @param string $page 		HTML of product page
	    * @param string $domain 	Amazon domain. e.g. www.amazon.com
	    * @param string $asin 		ASIN of product
	    *
	    * @return string 			The string array of bullet points, or "error" on error, blank string if no bullet points
	    */

	    function scrape_product_bullet_points($page, $domain = "www.amazon.com", $asin = "") {

	    	$ul = self::get_div('id="feature-bullets', $page, 0, true);
	    	$li = self::get_div_repeat('<li', $ul);

	    	if (sizeof($li)) return $li;


	    	// Check the bullets from AJAX URL
	    	// Sometimes Amazon loads bullet points with AJAX call

	    	$url = self::get_pattern(array('var url = "', '"'), $page);

	    	if ($url == "") $url = self::get_pattern(array('"immutableURLPrefix":"', '"'), $page);

	    	if ($url != "") {

	    		$new_page = self::get_page($domain.$url."&isUDPFlag=1&asinList=".$asin."&id=".$asin);

	    		if ($new_page['error'] == "") {
		    		$features = self::get_pattern(array('{"featurebullets_feature_div":', '}'), $new_page['html']);
		    		$li = self::get_pattern_repeat(array('<li>', '<\/li>'), $features);

		    		$li = array_map(function($l) { return stripslashes($l); }, $li);

			    	if (sizeof($li)) return $li;
			    }
			    else return "error";


	    	}

	    	return "";
	    }


	    /**
	    * Returns the word count of a string after removing HTML tags
	    *
	    * @param string $text 		The string to analyse
	    *
	    * @return int 				The word count
	    */

	    function word_count($text) {
	        return str_word_count(strip_tags($text));
	    }


		function scrape_product_brand($page) {
			$brand = trim(self::get_pattern(array('id="brand"', '>', '<'), $page));
            $brand = preg_replace("/[\s]/", "", $brand);
			if ($brand) return $brand;
			return '';
		}
		
		
		function scrape_product_seller_count($page) {
			$count = trim(self::get_pattern(array('condition=new', '(', ')'), $page));
            $count = preg_replace("/[^0-9]/", "", $count);
			if ($count) return $count;
			return '';
		}
	    /**
	    * Returns rating of a product
	    *
	    * @param string $page 		The HTML of product page
	    *
	    * @return float 			The rating (or empty string if no reviews)
	    */

	    function scrape_product_rating($page) {

	    	// Products
			
			
	    	$rating = trim(self::get_pattern(array('avgRating', '<span', '</span>'), $page));
	    	$rating = self::get_pattern(array(' '), $rating);
            $rating = preg_replace("/[^0-9.]/", "", $rating);	// Keeps numbers and . in string
	    	if (is_numeric($rating)) return $rating;
			
			
			$rating = trim(self::get_pattern(array('id="acrPopover"', 'title="','"'), $page));
	    	$rating = self::get_pattern(array(' '), $rating);
            $rating = preg_replace("/[^0-9.]/", "", $rating);	// Keeps numbers and . in string
	    	if (is_numeric($rating) && $rating<=5) return $rating;
			
	    	// Apps

	    	$rating = trim(self::get_pattern(array('crAvgStars', '<span>', '</span>'), $page));
	    	$rating = self::get_pattern(array(' '), $rating);
            $rating = preg_replace("/[^0-9.]/", "", $rating);	// Keeps numbers and . in string
	    	if (is_numeric($rating)) return $rating;


	        // eBooks
	    	$rating = trim(self::get_pattern(array('crRating', '>', '</span>'), $page));
	    	$rating = self::get_pattern(array(' '), $rating);
            $rating = preg_replace("/[^0-9.]/", "", $rating);	// Keeps numbers and . in string
	    	if (is_numeric($rating)) return $rating;

	    	return "";
	    }


	    /**
	    * Returns review count of a product
	    *
	    * @param string $page 		The HTML of product page
	    *
	    * @return int 				The review count
	    */

	    function scrape_product_review_count($page) {

	    	// Products

	    	$count = self::get_pattern(array('id="acrCustomerReviewText"', '>', ' '), $page);
	    	$count = self::remove_non_numeric($count);
	    	if (is_numeric($count)) return $count;


	        // eBooks

	    	$count = self::get_pattern(array('id="acrCount"', '<a', '<a'), $page);
            $count = trim($count);
            $count = strip_tags($count);
            $count = preg_replace("/[^0-9]/", "", $count);
	    	if (is_numeric($count)) return $count;


	        // Apps

	    	$count = self::get_pattern(array('acrCount', '<a', '>', '<'), $page);
            $count = trim($count);
            $count = strip_tags($count);
            $count = preg_replace("/[^0-9]/", "", $count);
	    	if (is_numeric($count)) return $count;

	        return 0;
	    }


	    /**
	    * Returns questions answered count of a product
	    *
	    * @param string $page 		The HTML of product page
	    *
	    * @return int 				The number of questions answered
	    */

	    function scrape_product_questions_answered($page) {

	    	$count = self::get_div('href="#Ask"', $page, 0, true);
	    	$count = strip_tags($count);
            $count = preg_replace("/[^0-9]/", "", $count);
	    	if (is_numeric($count)) return $count;

	        return 0;
	    }


	    /**
	    * Returns metrics of a product used in On Page Analyzer
	    *
	    * @param array $asins 			The product ASINS to analyze
	    * @param string $amazon_site 	Amazon domain. e.g. www.amazon.co.uk
	    *
	    * @return array
	    *			array
		*				string 'asin'
		*				string 'name'
		*				int    'sales_rank'
		*				string 'domain'
		*				string 'url'
		*				array  'hires_images'
		*				array  'large_images'
		*				array  'thumb_images'
		*				array  'bullet_points'
		*				string 'description'
		*				int    'word_count'
		*				int    'char_count'
		*				float  'product_rating'
		*				int    'review_count'
		*				int    'questions_answered'
		*				int    'fba'
	    */

		function scrape_products_iq($asins, $amazon_site) {

			if (strpos($amazon_site, "amazon.") === false) $amazon_site = "www.amazon.".$amazon_site;	// To account for passing only the extension. e.g. co.uk rather than www.amazon.co.uk

		    $final = array();

		    foreach ($asins as $asin) {

		        $page_details = self::get_product_page($amazon_site, $asin);

		        if (!$page_details['error']) {

		        	$page = $page_details['html'];

		            $parent_asin = self::scrape_parent_asin($page);
		            $sales_rank = self::scrape_sales_rank($page);
		            $title = self::scrape_product_title($page);

		            $bullet_points = self::scrape_product_bullet_points($page, $amazon_site, $asin);
		            $thumb_images = self::scrape_product_images($page, "thumb");
		            $hires_images = self::scrape_product_images($page, "hiRes");
		            $large_images = self::scrape_product_images($page, "large");
		            $description = self::scrape_product_description($page);
		            $word_count = self::word_count($description);
		            $char_count = strlen(strip_tags($description));
		            $product_rating = self::scrape_product_rating($page);
		            $review_count = self::scrape_product_review_count($page);
		            $questions_answered = self::scrape_product_questions_answered($page);
		            $url = self::scrape_product_url($page);
		            $fba = self::scrape_product_fba($page, $amazon_site);
		            $product_group = self::scrape_product_group($page);

		            if ((is_array($bullet_points)) || ($bullet_points == "")) {

			            $final[] = array(
			                'asin' => $asin,
			                'name' => $title,
			            	'product_group' => $product_group,
			                'sales_rank' => $sales_rank,
			                'domain' => $amazon_site,
			                'url' => $url,
			                'hires_images' => $hires_images,
			                'large_images' => $large_images,
			                'thumb_images' => $large_images,
			                'bullet_points' => $bullet_points,
			                'description' => $description,
			                'word_count' => $word_count,
			                'char_count' => $char_count,
			                'product_rating' => $product_rating,
			                'review_count' => $review_count,
			                'questions_answered' => $questions_answered,
			                'fba' => $fba
			                );
			        }
		        }
		    }

		    return $final;
		}

		function scrape_product_group($page, $amazon_site = "www.amazon.com") {

			$rank_group = array();

			$div = self::get_div('id="SalesRank', $page);

			if ($div == "") {

				// Method 2

				$div = self::get_pattern(array('Best Sellers Rank', '<td', '>', '</td>'), $page);

			}


			// Main product group

			$div = self::remove_between('<style', '</style>', $div);
			$top = self::get_pattern(array('<ul'), $div);

			if ($top == "") $top = $div;

			preg_match_all("/\b#?(\d+[.,]*)+\b/i", $top, $sales_ranks);


			if (isset($sales_ranks[0][0])) {

				$product_group = "";

				if (strpos(self::get_pattern(array($sales_ranks[0][0], '(', ')'), $div), '100') !== false) {
					$product_group = trim(self::get_pattern(array($sales_ranks[0][0], '('), $div));
				}

				if ($product_group == "") $product_group = trim(self::get_pattern(array($sales_ranks[0][0], "\n"), $div));

				if (strpos($product_group, '&gt;') !== false) $product_group = "";

				$product_group = strip_tags($product_group);

				if (($amazon_site == "www.amazon.cn") ||
					($amazon_site == "www.amazon.co.jp")) {
					preg_match('/(.*)'.$sales_ranks[0][0].'/i', $div, $product_group);
					$product_group = (isset($product_group[1])) ? $product_group[1] : "";

					$product_group = str_replace("里排第", "", $product_group);

					if (strpos($product_group, '-') !== false) {
						$product_group = trim(substr($product_group, 0, strpos($product_group, '-')));
					}
				}

				if (substr($product_group, 0, 3) == "en ") $product_group = substr($product_group, 3);
				if (substr($product_group, 0, 3) == "in ") $product_group = substr($product_group, 3);
				if (substr($product_group, 0, 3) == "em ") $product_group = substr($product_group, 3);
				if (substr($product_group, 0, 8) == "dans la ") $product_group = substr($product_group, 8);
				$product_group = str_replace("entre os mais vendidos na ", "", $product_group);

				$product_group = self::consolidate_product_group($product_group);

				$value = str_replace("#", "", $sales_ranks[0][0]);
				$value = str_replace(",", "", $value);
				$value = str_replace(".", "", $value);

				if ($product_group != "") {
					$rank_group[] = array(
						'sales_rank' => $value,
						'product_group' => $product_group
						);
				}
			}


			// Method 1 for sub groups

			$ul = self::get_pattern_repeat(array('<li', '</li'), $div);

			foreach ($ul as $li) {

				$spans = self::get_div_repeat('<span', $li);

				if (sizeof($spans) == 2) {
					$value = self::remove_non_numeric($spans[0]);
					$product_group = strip_tags($spans[1]);
				}

				if (substr($product_group, 0, 8) == "en&nbsp;") $product_group = substr($product_group, 8);
				if (substr($product_group, 0, 8) == "in&nbsp;") $product_group = substr($product_group, 8);
				if (substr($product_group, 0, 8) == "em&nbsp;") $product_group = substr($product_group, 8);
				if (substr($product_group, 0, 10) == "dans&nbsp;") $product_group = substr($product_group, 10);
				if (substr($product_group, 0, 7) == "-&nbsp;") $product_group = substr($product_group, 7);

				$product_group = self::consolidate_product_group($product_group);

				$value = str_replace("#", "", $value);
				$value = str_replace(",", "", $value);
				$value = str_replace(".", "", $value);
				$value = str_replace("n°", "", $value);

				$rank_group[] = array(
					'sales_rank' => $value,
					'product_group' => $product_group
					);
			}


			// Method 2 for sub groups

			$ul = self::get_pattern_repeat(array('<span>', '</span>'), $div);

			$ul = array_map('strip_tags', $ul);


			foreach ($ul as $key => $li) {
				$parts = explode(" in ", $li);
				if (sizeof($parts) != 2) $parts = explode(" en ", $li);
				if (sizeof($parts) != 2) $parts = explode(" em ", $li);
				if (sizeof($parts) != 2) $parts = explode(" dans ", $li);

				if (sizeof($parts) == 2) {
					$parts[0] = self::remove_non_numeric($parts[0]);

					if ((is_numeric($parts[0])) && (strpos(self::get_pattern(array('(', ')'), $parts[1]), '100') === false)) {
						$rank_group[] = array(
							'sales_rank' => $parts[0],
							'product_group' => trim(self::consolidate_product_group($parts[1]))
						);
					}
				}
			}


			// Method 3 for sub groups

			$ul = self::get_pattern_repeat(array('<span>', '<br>'), $div);
			$ul = array_map('strip_tags', $ul);

			foreach ($ul as $key => $li) {
				$parts = explode(" in ", $li);
				if (sizeof($parts) != 2) $parts = explode(" en ", $li);
				if (sizeof($parts) != 2) $parts = explode(" em ", $li);
				if (sizeof($parts) != 2) $parts = explode(" dans ", $li);

				if (sizeof($parts) == 2) {
					$parts[0] = self::remove_non_numeric($parts[0]);

					if ((is_numeric($parts[0])) && (strpos(self::get_pattern(array('(', ')'), $parts[1]), '100') === false)) {
						$rank_group[] = array(
							'sales_rank' => $parts[0],
							'product_group' => trim(self::consolidate_product_group($parts[1]))
						);
					}
				}
			}


			// Method 4 for sub groups

			$ul = self::get_div_repeat('a-section', $div);
			$ul = array_map('strip_tags', $ul);


			foreach ($ul as $key => $li) {
				$parts = explode("in&nbsp;", $li);
				if (sizeof($parts) != 2) $parts = explode("en&nbsp;", $li);
				if (sizeof($parts) != 2) $parts = explode("em&nbsp;", $li);
				if (sizeof($parts) != 2) $parts = explode("dans&nbsp;", $li);

				if (sizeof($parts) == 2) {
					$parts[0] = self::remove_non_numeric($parts[0]);

					if ((is_numeric($parts[0])) && (strpos(self::get_pattern(array('(', ')'), $parts[1]), '100') === false)) {
						$rank_group[] = array(
							'sales_rank' => $parts[0],
							'product_group' => trim(self::consolidate_product_group($parts[1]))
						);
					}
				}
			}

			$rank_group = array_unique($rank_group, SORT_REGULAR);
			$rank_group = array_map(function($p) {
				return array(
					'sales_rank' => $p['sales_rank'],
					'product_group' => trim(str_replace("  ", " ", $p['product_group']))
					);
			}, $rank_group);


			return $rank_group;
		}


		/**
		* Changes product groups from Amazon for consistency
		*
		* @param string $group
		*
		* @return string
		*/

		function consolidate_product_group($group) {

			$group = str_replace("&amp;", "&", $group);

			return $group;
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


		/**
		* Returns currency given domain
		*
		* @param string $domain 	Amazon domain. e.g www.amazon.com
		*
		* @return string 			Currency. e.g. USD
		*/

	    function get_currency($domain) {

	        switch ($domain) {
	            case 'www.amazon.co.uk':
	                $currency = "GBP";
	                break;

	            case 'www.amazon.ca':
	                $currency = "CAD";
	                break;

	            case 'www.amazon.com.br':
	                $currency = "BRL";
	                break;

	            case 'www.amazon.cn':
	                $currency = "CNY";
	                break;

	            case 'www.amazon.fr':
	                $currency = "EUR";
	                break;

	            case 'www.amazon.de':
	                $currency = "EUR";
	                break;

	            case 'www.amazon.it':
	                $currency = "EUR";
	                break;

	            case 'www.amazon.es':
	                $currency = "EUR";
	                break;

	            case 'www.amazon.in':
	                $currency = "INR";
	                break;

	            case 'www.amazon.co.jp':
	                $currency = "JPY";
	                break;


	            default:
	                $currency = "USD";
	                break;
	        }

	        return $currency;
	    }




		/**
		* Scrapes profile url
		*
		* @param string $profile_url 	URL of profile to scrape
		*
		* @return array
		*			string 'error'
		*			string 'error_message'
		*			array 'profile'
		*				'ranking' => $ranking,
		*				'helpful' => $helpful,
		*				'about' => $about,
		*				'interests' => $interests,
		*				'review_count' => $review_count,
		*				'amazon_email' => $amazon_email,
		*				'website' => $website,
		*				'image' => $image,
		*				'reviewer_name' => $reviewer_name,
		*				'occupation' => $occupation
		*			array 'reviews'
		*				array 'review'
	    *					'asin' => $asin,
	    *					'rating' => $rating,
	    *					'title' => $title,
	    *					'review_date' => $date_final,
	    *					'content' => $content,
	    *					'review' => $review_code,
	    *					'video' => $video
		*
		*/

		function scrape_profile_url($profile_url, $start_page = 1) {

			$url = self::get_page_url_reviewer($profile_url);

			if ($url == "") {
				return array(
					'error' => 0,
					'error_message' => "",
					'profile' => array(
						'reviewer_name' => "Not a profile URL"
						),
					'reviews' => array(),
					'last_page' => $start_page
					);
			}

			$page = self::get_page($url, 5, "", array('X-Requested-With: XMLHttpRequest', 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0'), true);

			$error = $page['error'];
			$error_message = $page['error_message'];

			if ($page['error'] == "") {


				// Bio-expander

				$bio = self::get_div('bio-expander', $page['html']);


				// Ranking

				$ranking = self::get_pattern(array('profile-info', 'top-reviewers', 'rank=', '#'), $page['html']);
			    $ranking = self::remove_non_numeric($ranking);

				if (!is_numeric($ranking)) {
					$ranking = self::get_pattern(array('profile-info', '#', '<'), $page['html']);
				    $ranking = self::remove_non_numeric($ranking);
				}

				if (!is_numeric($ranking)) {
					$ranking = self::get_pattern(array('Reviewer ranking', '#', '<'), $page['html']);
				    $ranking = self::remove_non_numeric($ranking);
				}

				if (!is_numeric($ranking)) $ranking = "NULL";



				// Helpful %

			    $helpful = "";
				// $helpful = self::get_pattern(array('customer-helpfulness">', '>', '%'), $page['html']);
			    // $helpful = self::remove_non_numeric($helpful);


			    if (!is_numeric($helpful)) {
			    	$helpful = self::get_pattern(array('Helpful votes', 'a-size-small', '>', '<'), $bio);
				    $helpful = self::remove_non_numeric($helpful);
			    }

			    if (!is_numeric($helpful)) {
			    	$helpful = self::get_pattern(array('Voti utili', 'a-size-small', '>', '<'), $bio);
				    $helpful = self::remove_non_numeric($helpful);
			    }

			    if (!is_numeric($helpful)) {
			    	$helpful = self::get_pattern(array("class='title'", "Helpful votes", "a-text-bold", '>', '<'), $page['html']);

			    	if (strpos($helpful, 'k') !== false) {
					    $helpful = trim(str_replace("k", "", $helpful));
					    if (is_numeric($helpful)) $helpful = $helpful * 1000;
			    	}
			    }

				if (!is_numeric($helpful)) $helpful = 0;



				// About me

				$about = trim(strip_tags(self::get_pattern(array('activity-heading', 'About Me', '>', '</span>'), $page['html'])));
				if ($about == "") $about = trim(strip_tags(self::get_pattern(array('activity-heading', 'About', '<p>', '</p>'), $page['html'])));

				if ($about == "") {

					$bio_parts = self::get_div_repeat('a-size-base', $bio);

					if ((isset($bio_parts[0])) &&
						($bio_parts[0] != "Helpful votes") &&
						($bio_parts[0] != "Reviewer ranking") &&
						($bio_parts[0] != "Voti utili") &&
						($bio_parts[0] != "") &&
						(substr($bio_parts[0], 0, 1) != "#"))
						$about = trim($bio_parts[0]);
				}

				// Interests

				$interests = trim(strip_tags(self::get_pattern(array('profile-interests', 'activity-heading', '</span>', '</span>'), $page['html'])));


				// Reviews

				$review_count = self::get_pattern(array('reviews-link', '(', ')'), $page['html']);
				if (!is_numeric($review_count)) $review_count = 0;


				// Amazon email

				$amazon_email = self::get_pattern(array('mailto:', '"'), $page['html']);


				// Reviewer website

				$website = self::get_pattern(array('rel="nofollow"', 'href="', '"'), $page['html']);


				// Image

				$image = self::get_pattern(array('profile-image-holder', 'src="', '"'), $page['html']);


				// Reviewer name

				$reviewer_name = self::get_pattern(array('profile-display-name', '>', '<'), $page['html']);
				if ($reviewer_name == "") $reviewer_name = self::get_pattern(array('public-name-text', '>', '<'), $page['html']);
				if ($reviewer_name == "") $reviewer_name = self::get_pattern(array('<h1>', '<'), $page['html']);


				// Occupation, Location

				$profile_info = self::get_div('profile-info', $page['html']);
				$name_div = self::get_div('a-spacing-micro', $profile_info);
				$occupation = self::get_div('a-color-secondary', $name_div);

				if ($occupation == "") {
					$location_occupation_holder = self::get_div('location-and-occupation-holder', $page['html']);
					$occupation_divs = self::get_div_repeat('location-and-bio-text', $location_occupation_holder);
					$occupation = implode(", ", $occupation_divs);
				}



				// Check if profile page

				if ((strpos($page['html'], 'profile-feedback') === false) && (strpos($page['html'], 'profile-holder') === false) && ($reviewer_name == "")) {
					return array(
						'error' => 0,
						'error_message' => "",
						'profile' => array(
							'reviewer_name' => "Not a profile URL"
							),
						'reviews' => array(),
						'last_page' => $start_page
					);
				};



				$profile = array(
					'ranking' => $ranking,
					'helpful' => $helpful,
					'about' => $about,
					'interests' => $interests,
					'review_count' => $review_count,
					'amazon_email' => $amazon_email,
					'website' => $website,
					'image' => $image,
					'reviewer_name' => $reviewer_name,
					'occupation' => $occupation
					);
			}
			else $profile = array();

			// Get Reviews

			$end_page = 6;

			if ((is_numeric($review_count)) && ($review_count > 0)) {
				$end_page = ceil($review_count/10);

				if ($end_page > 6) $end_page = 6;
                if ($end_page < $start_page) $end_page = $start_page;
			}

			$reviews = self::scrape_reviews($url, $start_page, $end_page);


			if (isset($reviews['profile'])) {
				if ((!is_numeric($profile['ranking'])) && (is_numeric($reviews['profile']['ranking']))) $profile['ranking'] = $reviews['profile']['ranking'];
				if (!is_numeric($reviews['profile']['helpful'])) $profile['helpful'] = $reviews['profile']['helpful'];
			}

			if ((isset($profile['review_count'])) && ($profile['review_count'] == 0) && (isset($reviews['review_count']))) $profile['review_count'] = $reviews['review_count'];

			$rating_total = 0;
			$word_count_total = 0;
			$video_count = 0;
			$images_count = 0;
			$verified_count = 0;
			$timestamp = 2147483647;
			$last_review = 0;

			foreach ($reviews['reviews'] as $key => $review) {
				$rating_total += $review['rating'];
				$word_count_total += $review['word_count'];
				$video_count += $review['video'];
				$images_count += $review['images'];
				$verified_count += $review['verified'];

				if (strtotime($review['review_date']) < $timestamp) $timestamp = strtotime($review['review_date']);
				if (strtotime($review['review_date']) > $last_review) $last_review = strtotime($review['review_date']);
			}

			$review_count = sizeof($reviews['reviews']);

			if ($review_count > 0) {
				$profile['word_count'] = round($word_count_total / $review_count);
				$profile['rating'] = round($rating_total / $review_count, 2);
				$profile['reviews_per_month'] = round($review_count / (time() - $timestamp) * 60 * 60 * 24 * 30, 2);
			}
			else {
				$profile['word_count'] = 0;
				$profile['rating'] = 0;
				$profile['reviews_per_month'] = 0;
			}

			$profile['video_count'] = $video_count;
			$profile['images_count'] = $images_count;
			$profile['verified_count'] = $verified_count;
			$profile['last_review'] = date('Y-m-d 00:00:00', $last_review);

			if (($reviews['error'] != "") && ($error == "")) {
				$error = $reviews['error'];
				$error_message = $reviews['error_message'];
			}

			return array(
				'error' => $error,
				'error_message' => $error_message,
				'profile' => $profile,
				'reviews' => $reviews['reviews'],
				'last_page' => $reviews['last_page']
				);
		}



		/**
		* Returns the URL to use when scraping profile URL given a review offset
		*
		* @param string $url 	URL of profile to scrape
		*
		* @return string 		The profile URL with offset
		*/

		function get_page_url_reviewer($profile_url) {


			// Get Amazon domain
			preg_match('/(amazon\..*?)\//i', $profile_url, $domain);
			$domain = (isset($domain[1])) ? "www.".$domain[1] : "www.amazon.com";


			// Get Profile Id
		    preg_match('/profile\/(\w+)/i', $profile_url, $profile_id);
		    $profile_id = (isset($profile_id[1])) ? $profile_id[1] : "";


		    if ($profile_id == "") {
			    preg_match('/member-reviews\/(\w+)/i', $profile_url, $profile_id);
			    $profile_id = (isset($profile_id[1])) ? $profile_id[1] : "";
		    }

		    if ($profile_id == "") return "";

			return "https://".$domain."/gp/profile/".$profile_id;
		}



		/**
		* Scrapes reviews from profile URL
		*
		* @param string $profile_url 	URL of profile to scrape
		* @param int $start_page 		Review page to start at
		* @param int $end_page 			Review page to end at
		*
		* @return array
		*			string 'error'
		*			string 'error_message'
		*			array 'reviews'
		*				array
	    *					'asin' => $asin,
	    *					'rating' => $rating,
	    *					'title' => $title,
	    *					'review_date' => $date_final,
	    *					'content' => $content,
	    *					'review' => $review_code,
	    *					'video' => $video,
	    *					'images' => $images,
	    *					'verified' => $verified,
	    *					'word_count' => $word_count
	    *			array 'profile'
	    *				array
	    *					'ranking'
	    *					'helpful'
		*
		*/

		function scrape_reviews($profile_url, $start_page = 1, $end_page = 5) {

			// Get Amazon domain
			preg_match('/(amazon\..*?)\//i', $profile_url, $domain);
			$domain = (isset($domain[1])) ? "www.".$domain[1] : "www.amazon.com";

			// Get Profile Id
		    preg_match('/profile\/(\w+)/i', $profile_url, $profile_id);
		    $profile_id = (isset($profile_id[1])) ? $profile_id[1] : "";

			$reviews = array();
			$review_count = 0;
			$ranking = 0;
			$helpful = 0;

			$profile = array(
				'ranking' => "",
				'helpful' => ""
			);


			$review_count_pattern = array(
				array('Customer Reviews</', ':', '</div'),
				array('Kundenrezensionen</', ':', '</div'),
				array('Commentaires en ligne</', ':', '</div'),
				array('Avaliação de clientes</', ':', '</div'),
				array('Opiniones de clientes</', ':', '</div'),
				array('Recensioni clienti</', ':', '</div'),
				array('商品评论</', ':', '</div'),
				array('カスタマーレビュー</', ':', '</div')
				);

			$ranking_pattern = array(
				array('Top Reviewer Ranking:&nbsp;', '<br />'),
				array('Top-Rezensenten Rang:&nbsp;', '<br />'),
				array('Classement des meilleurs critiques:&nbsp;', '<br />'),
				array('Ranking dos principais avaliadores:&nbsp;', '<br />'),
				array('Ranking de top de opiniones:&nbsp;', '<br />'),
				array('Classifica Top recensori:&nbsp;', '<br />'),
				array('最佳评论者排名:&nbsp;', '<br />'),
				array('ベストレビュワーランキング:&nbsp;', '<br />'),
				);

			$helpful_pattern = array(
				array('Helpful Votes:&nbsp;', '<'),
				array('Hilfreiche Bewertungen:&nbsp;', '<'),
				array('Votes utiles&#160;:&nbsp;', '<'),
				array('Voti utili:&nbsp;', '<'),
				array('Votos úteis:&nbsp;', '<'),
				array('Votos útiles:&nbsp;', '<'),
				array('有用票数&nbsp;', '<'),
				array('参考になった：&nbsp;', '<'),
				);

			$product_name_pattern = array(
				array('>This review is from:', '<a', '>', '</a'),
				array('>Ce commentaire fait référence à cette édition', '<a', '>', '</a'),
				array('>评论的商品', '<a', '>', '</a'),
				array('>レビュー対象商品', '<a', '>', '</a'),
				array('>Este comentário é de', '<a', '>', '</a'),
				array('>Rezension bezieht sich auf', '<a', '>', '</a'),
				array('>Esta opinión es de', '<a', '>', '</a'),
				array('>Questa recensione', '<a', '>', '</a'),

				);

			if ($start_page < 1) $start_page = 1;

			for ($page = $start_page; $page < $end_page + 1; $page++) {
				$review_url = "https://".$domain."/gp/cdp/member-reviews/".$profile_id."?sort_by=MostRecentReview";
				if ($page > 1) $review_url .= "&page=".$page;

				$review_page = self::get_page($review_url);

				if ($review_page['error'] != "") {
					return array(
						'error' => $review_page['error'],
						'error_message' => $review_page['error_message'],
						'profile' => $profile,
						'reviews' => $reviews,
						'last_page' => $page
						);
				}


				// Get ranking

				if ($ranking == 0) {
					foreach ($ranking_pattern as $pattern) {
						if ($ranking == 0) {
							$ranking = trim(strip_tags(self::get_pattern($pattern, $review_page['html'])));
						    $ranking = self::remove_non_numeric($ranking);
							if (!is_numeric($ranking)) $ranking = 0;
						}
					}
				}


				// Get helpful

				if ($helpful == 0) {
					foreach ($helpful_pattern as $pattern) {
						if ($helpful == 0) {
							$helpful = trim(self::get_pattern($pattern, $review_page['html']));
						    $helpful = self::remove_non_numeric($helpful);
							if (!is_numeric($helpful)) $helpful = 0;
						}
					}
				}


				// Get review count

				if ($review_count == 0) {
					foreach ($review_count_pattern as $pattern) {
						if ($review_count == 0) {
							$review_count = trim(self::get_pattern($pattern, $review_page['html']));
							if (!is_numeric($review_count)) $review_count = 0;
						}
					}
				}


				$profile = array(
					'ranking' => ($ranking) ? $ranking : "",
					'helpful' => $helpful
					);

				$review_blocks = self::get_pattern_repeat(array('<a name=', '</tr>'), $review_page['html']);

				$review_page['html'] = iconv('ISO 8859-1', 'UTF-8', $review_page['html']);


				foreach ($review_blocks as $key => $block) {
					$review_id = self::get_pattern(array('"', '"'), $block);
					$title = self::get_pattern(array('<b>', '</b>'), $block);
					$date = self::get_pattern(array('<nobr>', '</nobr>'), $block);
					$rating = self::get_pattern(array('title="', ' '), $block);
					$asin = strtoupper(self::get_pattern(array('<a href="', '/dp/', '/'), $block));
					if ($asin == "") $asin = strtoupper(self::get_pattern(array('<a href="', '/product/', '/'), $block));

					$product_name = "";

					foreach ($product_name_pattern as $pattern) {
						if ($product_name == "") {
							$product_name = trim(self::get_pattern($pattern, $block));
						}
					}

					$product_image = self::get_pattern(array('/'.$asin.'/', '<img', 'src="', '"'), $review_page['html']);

					$images = (strpos($block, "review-image-thumbnail") !== false) ? 1 : 0;
					$verified = (strpos($block, "amazon-verified-purchase") !== false) ? 1 : 0;

					$content = self::get_div('class="reviewText', $block);

					$video = (strpos($content, "amznJQ") !== false) ? 1 : 0;

					$divs = self::get_div_repeat('<div', $content, 0, 1);

					foreach ($divs as $div) {
						$content = str_replace($div, "", $content);
					}

					$divs = self::get_div_repeat('<span', $content, 0, 1);

					foreach ($divs as $div) {
						$content = str_replace($div, "", $content);
					}

					if ($asin != "") {
						if (!isset($reviews[$asin])) {
							$reviews[$asin] = array(
								'review' => $review_id,
								'domain' => $domain,
								'content' => $content,
								'title' => $title,
								'review_date' => self::convert_date($date, $domain),
								'rating' => $rating,
								'asin' => $asin,
								'product_name' => $product_name,
								'product_image' => $product_image,
								'video' => $video,
								'images'=> $images,
								'verified' => $verified,
								'word_count' => str_word_count($content)
								);
						}
						else $page = $end_page;
					}
				}
			}

	    	return array(
	    		'error' => '',
	    		'error_message' => '',
	    		'reviews' => $reviews,
	    		'last_page' => $page,
	    		'review_count' => $review_count,
	    		'profile' => $profile
	    		);
		}


		/**
		* Returns a date converted into format Y-m-d 00:00:00
		*
		* @param string $date_final 	The date
		* @param string $domain 		Used to specify country
		*
		* @return string 	The formatted date
		*/

		function convert_date($date_final, $domain) {

			$domain = parse_url($domain."/");

			if (isset($domain['host'])) $domain = $domain['host'];
			else {
				if (isset($domain['path'])) {
					$domain = substr($domain['path'], 0, strpos($domain['path'], '/'));
				}
			}


		    // French

		    if ($domain == "www.amazon.fr") {

			    $date_final = str_replace('janvier', 'january', $date_final);
				$date_final = str_replace('février', 'february', $date_final);
				$date_final = str_replace('mars', 'march', $date_final);
				$date_final = str_replace('avril', 'april', $date_final);
				$date_final = str_replace('mai', 'may', $date_final);
				$date_final = str_replace('juin', 'june', $date_final);
				$date_final = str_replace('juillet', 'july', $date_final);
				$date_final = str_replace('août', 'august', $date_final);
				$date_final = str_replace('septembre', 'september', $date_final);
				$date_final = str_replace('octobre', 'october', $date_final);
				$date_final = str_replace('novembre', 'november', $date_final);
				$date_final = str_replace('décembre', 'december', $date_final);

				$date_final = str_replace("le ", "", $date_final);
			}

			// Spanish

		    if ($domain == "www.amazon.es") {

			    $date_final = utf8_encode($date_final);

				$date_final = str_replace('enero', 'January', $date_final);
				$date_final = str_replace('febrero', 'February', $date_final);
				$date_final = str_replace('marzo', 'March', $date_final);
				$date_final = str_replace('abril', 'April', $date_final);
				$date_final = str_replace('mayo', 'May', $date_final);
				$date_final = str_replace('junio', 'June', $date_final);
				$date_final = str_replace('julio', 'July', $date_final);
				$date_final = str_replace('agosto', 'August', $date_final);
				$date_final = str_replace('septiembre', 'September', $date_final);
				$date_final = str_replace('octubre', 'October', $date_final);
				$date_final = str_replace('noviembre', 'November', $date_final);
				$date_final = str_replace('diciembre', 'December', $date_final);
				$date_final = str_replace(' de ', ' ', $date_final);
				$date_final = str_replace('el ', '', $date_final);
			}

			// German

		    if ($domain == "www.amazon.de") {

				$date_final = str_replace('Januar', 'January', $date_final);
				$date_final = str_replace('Februar', 'February', $date_final);
				$date_final = str_replace('März', 'March', $date_final);
				$date_final = str_replace('Mai', 'May', $date_final);
				$date_final = str_replace('Juni', 'June', $date_final);
				$date_final = str_replace('Juli', 'July', $date_final);
				$date_final = str_replace('Oktober', 'October', $date_final);
				$date_final = str_replace('Dezember', 'December', $date_final);

				$date_final = str_replace("am ", "", $date_final);
			}

			// Portugese (.com.br)

		    if ($domain == "www.amazon.com.br") {

			    $date_final = utf8_encode($date_final);

				$date_final = str_replace('de janeiro de', 'January', $date_final);
				$date_final = str_replace('de fevereiro de', 'February', $date_final);
				$date_final = str_replace('de marÃ§o de', 'March', $date_final);
				$date_final = str_replace('de abril de', 'April', $date_final);
				$date_final = str_replace('de maio de', 'May', $date_final);
				$date_final = str_replace('de junho de', 'June', $date_final);
				$date_final = str_replace('de julho de', 'July', $date_final);
				$date_final = str_replace('de agosto de', 'August', $date_final);
				$date_final = str_replace('de setembro de', 'September', $date_final);
				$date_final = str_replace('de outubro de', 'October', $date_final);
				$date_final = str_replace('de novembro de', 'November', $date_final);
				$date_final = str_replace('de dezembro de', 'December', $date_final);

				$date_final = str_replace('janeiro', 'January', $date_final);
				$date_final = str_replace('fevereiro', 'February', $date_final);
				$date_final = str_replace('marÃ§o', 'March', $date_final);
				$date_final = str_replace('abril', 'April', $date_final);
				$date_final = str_replace('maio', 'May', $date_final);
				$date_final = str_replace('junho', 'June', $date_final);
				$date_final = str_replace('julho', 'July', $date_final);
				$date_final = str_replace('agosto', 'August', $date_final);
				$date_final = str_replace('setembro', 'September', $date_final);
				$date_final = str_replace('outubro', 'October', $date_final);
				$date_final = str_replace('novembro', 'November', $date_final);
				$date_final = str_replace('dezembro', 'December', $date_final);

				$date_final = str_replace('em ', '', $date_final);
			}

			// Italian

		    if ($domain == "www.amazon.it") {

			    $date_final = utf8_encode($date_final);

				$date_final = str_replace('gennaio', 'January', $date_final);
				$date_final = str_replace('febbraio', 'February', $date_final);
				$date_final = str_replace('marzo', 'March', $date_final);
				$date_final = str_replace('aprile', 'April', $date_final);
				$date_final = str_replace('maggio', 'May', $date_final);
				$date_final = str_replace('giugno', 'June', $date_final);
				$date_final = str_replace('luglio', 'July', $date_final);
				$date_final = str_replace('agosto', 'August', $date_final);
				$date_final = str_replace('settembre', 'September', $date_final);
				$date_final = str_replace('ottobre', 'October', $date_final);
				$date_final = str_replace('novembre', 'November', $date_final);
				$date_final = str_replace('dicembre', 'December', $date_final);
				$date_final = str_replace('il ', '', $date_final);
			}

			// Chinese

			if ($domain == "www.amazon.cn") {

			    $date_final = utf8_encode($date_final);

				$date_split = str_split($date_final);

				$date_final = "";

				foreach ($date_split as $key => $value) {
					if (is_numeric($value)) $date_final .= $value;
					else {
						if (substr($date_final, -1) != "-")	$date_final .= "-";
					}
				}

				$date_final = trim($date_final, "-");
			}

			// Japan

			if ($domain == "www.amazon.co.jp") {
				preg_match_all('/\d+/', $date_final, $date_parts);

				if (isset($date_parts[0]))
					if (sizeof($date_parts[0]) == 3) $date_final = implode("-", $date_parts[0]);
			}


		    $date_final = date('Y-m-d 00:00:00', strtotime($date_final));

		    return $date_final;
		}



		function scrape_inventory($asin, $domain, $merchant_id = "") {

			$domain = "https://".$domain;

			$url = $domain."/dp/".strtoupper($asin);

	    	if ($merchant_id != "") $url .= "?m=".$merchant_id;

			$ch = self::get_curl_handler($url, 60, "", true, true);

			$page = self::get_page($url, 5, $ch);

			$error = "";
			$scrape_error = 0;
			$product = array();
	        $currency = self::get_currency($domain);

			if ($page['error'] == "") {

				// Check if product exists

				if (self::is_404($page['html'])) {

					$error = "Product page does not exist";
					$qty = 0;
					$price = 0;

				} else {

					$product = self::scrape_product_page($asin, $domain, $page['html']);

				    $postfields = array(
				        'session-id' => self::get_value("session-id", $page['html']),
				        'offerListingID' => self::get_value("offerListingID", $page['html']),
				        'ASIN' => self::get_value("ASIN", $page['html']),
				        'isMerchantExclusive' => self::get_value("isMerchantExclusive", $page['html']),
				        'merchantID' => self::get_value("merchantID", $page['html']),
				        'isAddon' => self::get_value("isAddon", $page['html']),
				        'nodeID' => self::get_value("nodeID", $page['html']),
				        'sellingCustomerID' => self::get_value("sellingCustomerID", $page['html']),
				        'qid' => self::get_value("qid", $page['html']),
				        'sr' => self::get_value("sr", $page['html']),
				        'storeID' => self::get_value("storeID", $page['html']),
				        'tagActionCode' => self::get_value("tagActionCode", $page['html']),
				        'viewID' => self::get_value("viewID", $page['html']),
				        'rsid' => self::get_value("rsid", $page['html']),
				        'sourceCustomerOrgListID' => self::get_value("sourceCustomerOrgListID", $page['html']),
				        'sourceCustomerOrgListItemID' => self::get_value("sourceCustomerOrgListItemID", $page['html']),
				        'wlPopCommand' => self::get_value("wlPopCommand", $page['html']),
				        'quantity' => 9999,
				        'submit.add-to-cart' => 'Add+to+Cart'
				    );


					// Sometimes offerListingID is not on the page until you click a variation

					if ($postfields['offerListingID'] == "") {
						$ajax_url = self::get_pattern(array('immutableURLPrefix":"', '"'), $page['html']);
						if ($ajax_url != "") {

							sleep(rand(2, 6));

				            $offer_page = self::get_page($domain.$ajax_url."&asinList=".$asin."&id=".$asin."&psc=1&tagActionCode=dsf", 5, $ch);

				            if ($offer_page['error'] == "") {
				            	$postfields['offerListingID'] = self::get_pattern(array('name=\"offerListingID\"', 'value=\"', '\"'), $offer_page['html']);

	                            if ($postfields['offerListingID'] == "") {

	                                // No offerListingID
	                                if (strpos($offer_page['html'], 'name=\"offerListingID\"') === false) $scrape_error = 1;
	                                else {
	                                    $qty = 0;
	                                    $price = 0;
	                                }
	                            }

				            }
				            else $scrape_error = 1;
						}
					}

				    $merchant_count = self::get_pattern(array('offer-listing', '>', '</'), $page['html']);
				    $merchant_count = self::remove_non_numeric($merchant_count);
				    if (!is_numeric($merchant_count)) $merchant_count = 0;


				    // Check for multiple sellers

				    if ($merchant_count > 3) $error = "More than 4 sellers of this product";


				    // Check for Amazon Prime Pantry

				    $title = self::get_pattern(array('<title>', '</'), $page['html']);

				    if (strpos($title, 'Prime Pantry') !== false) {
				        $qty = 0;
				        $price = 0;
				        $error = "Cannot track Prime Pantry";
				    }
				    else if (strpos($page['html'], 'id="outOfStock"')) {

				        // Check for "Currently unavailable"

				        $qty = 0;
				        $price = 0;
				        $error = "Recently unavailable";
				    }
				    else {

	                    if (($postfields['ASIN'] != "") && ($postfields['offerListingID'] != "")) {

				            $price = self::scrape_product_price_lowest($page['html']);

				            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));

							sleep(rand(2, 6));
				            $page = self::get_page($domain."/gp/product/handle-buy-box/ref=dp_start-bbf_1_glance", 5, $ch);

							if ($page['error'] == "") {

					            // Get item count in cart

					            $qty = self::get_pattern(array('id="hlb-cart-itemcount"', '<strong>', '</span>'), $page['html']);

					            // Method 2

					            if ($qty == "") {
					                $qty = self::get_pattern(array('<span class="subtotal-with-count">', '</span>'), $page['html']);
					                $qty = preg_replace("/[^0-9]/", "", $qty);
					            }

					            // Method 3

					            if ($qty == "") {
					                $qty = self::get_pattern(array('class="quantityBox', 'value="', '"'), $page['html']);
					                $qty = preg_replace("/[^0-9]/", "", $qty);
					            }

					            // Method 4

					            if ($qty == "") {
					                $qty = self::get_pattern(array('class="itemTitle"', '<input', 'value="', '"'), $page['html']);
					                $qty = preg_replace("/[^0-9]/", "", $qty);
					            }

					            // Method 5

					            if ($qty == "") {
					                $qty = self::get_pattern(array('<span class="subtotal-with-count"', '>', '</span>'), $page['html']);
					                $qty = preg_replace("/[^0-9]/", "", $qty);
					            }

					            if ($qty == "") {
					                $qty = self::get_div('class="subtotal-with-count', $page['html']);
					                $qty = preg_replace("/[^0-9]/", "", $qty);
					            }

					            // Method 6

					            if ($qty == "") {
					                $div = self::get_div('huc-subtotal', $page['html']);
					                $qty = self::get_pattern(array('(', ')'), $div);
					                $qty = preg_replace("/[^0-9]/", "", $qty);
					            }

					            // Method 7

					            if ($qty == "") {
					                $div = self::get_div('huc-subtotal', $page['html']);
					                $qty = self::get_pattern(array('<span>', '</span>'), $div);
					                $qty = preg_replace("/[^0-9]/", "", $qty);
					            }

				                // Shopping Cart Empty?

				                if ($qty == "") {

					                if ((strpos($page['html'], 'Your Shopping Cart is empty')) ||
					                    (strpos($page['html'], 'Your Shopping Basket is empty')) ||
					                    (strpos($page['html'], 'Ihr Einkaufswagen ist leer')) ||
					                    (strpos($page['html'], 'Votre panier est vide pour le moment'))
					                    ) {
					                    $qty = 0;

					                    if (!is_numeric($price)) $price = 0;
					                }
					            }


				                // Strange...

				                if (!is_numeric($qty)) {
				                    $scrape_error = 1;
				                }


				                // Get price from cart page

				                if ($qty > 0) {

				                	// Method 1

				                    $cart_price = self::get_pattern(array('subtotal-with-count', ' hlb-price', '>', '<'), $page['html']);
				                    $cart_price = preg_replace("/[^0-9-]/", "", $cart_price);

				                    if (is_numeric($cart_price)) $cart_price = $cart_price / $qty;


				                    // Method 2

				                    if (!is_numeric($cart_price)) {

					                    $cart_price = self::get_div(' hlb-price', $page['html']);
					                    $cart_price = preg_replace("/[^0-9-]/", "", $cart_price);

					                    if (is_numeric($cart_price)) $cart_price = $cart_price / $qty;
					                }

					                if (is_numeric($cart_price)) $price = $cart_price;
					            }

			                    if (!is_numeric($price)) $price = 0;
					        }
					        else {

					        	// Couldn't get cart page

					        	$qty = 0;
					        	$price = 0;

					        	$scrape_error = 1;
					        }
						}
					}
				}

				if (!isset($qty)) $qty = 0;
				if (!isset($price)) $price = 0;

		        if ($qty == 1000) $error = "More than 1000 in stock";

		        $results = array(
		        	'product' => $product,
		        	'qty' => $qty,
		        	'price' => $price,
		        	'currency' => $currency,
		        	'error' => $error,
		        	'scrape_error' => 0
		        	);

			}
			else {
				$scrape_error = 1;

		        $results = array(
		        	'product' => $product,
		        	'qty' => 0,
		        	'price' => 0,
		        	'currency' => $currency,
		        	'error' => '',
		        	'scrape_error' => 1
		        	);

			}

			return $results;
		}



		/**
		* Returns reviews scraped from a product review page
		*
		* @param string $asin 			Product ASIN
		* @param string $domain 		Used to specify country
		* @param string $rating 		Star rating. 0 for all ratings.
		*
		* @return array
		*/

		function get_reviews_customer_id($domain,$reviewer_id_encrypt){

            $page = self::get_page( "https://" . $domain . "/gp/profile/" . $reviewer_id_encrypt."/", 5, "", "", true);
            if ($page['error']) {
                return array(
                    'error' => 1,
                    'reviewer_id' => ''
                );
            }
            $reviewer_id = self::get_pattern(array('customerId":"', '"'), $page['html']);
            return array(
                'error' => 0,
                'reviewer_id' => $reviewer_id
            );

        }
        function get_reviews($asin, $domain, $rating = 0)
        {

            $url = "https://" . $domain . "/product-reviews/" . $asin . "?showViewpoints=0&sortBy=bySubmissionDateDescending";

            if ($rating == 1) $url .= "&filterBy=addOneStar";
            if ($rating == 2) $url .= "&filterBy=addTwoStar";
            if ($rating == 3) $url .= "&filterBy=addThreeStar";
            if ($rating == 4) $url .= "&filterBy=addFourStar";
            if ($rating == 5) $url .= "&filterBy=addFiveStar";

            $page = self::get_page($url, 5, "", "", true);;

            if ($page['error']) {
                return array(
                    'error' => 1,
                    'reviews' => array()
                );
            }

            $page = $page['html'];
            $all_reviews = array();


            // Method 1

            $divs = self::get_div_repeat('a-section review"', $page, 0, true);


            foreach ($divs as $div) {
                $title = self::get_div('review-title', $div);
                $content = self::get_div('review-text', $div);
                $reviewer_name = self::get_pattern(array('review-author','/gp/profile/','>','<'), $div);//self::get_div('review-author', $div);
                $review = self::get_pattern(array('id="', '"'), $div);
                $reviewer_id = self::get_pattern(array('gp/profile/', '/'), $div);



                $rating_start = strpos($div, 'a-star-') + 7;
                $rating = substr($div, $rating_start, 1);

                $rating = self::get_pattern(array('a-star-', ' '), $div);

                $review_date = self::get_div('review-date', $div);
                if (strlen($review_date) > 3)
                    if (substr($review_date, 0, 3) == "on ") $review_date = substr($review_date, 3);


                $review_date = self::convert_date($review_date, $domain);

                $all_reviews[] = array(
                    'title' => $title,
                    'reviewer_name' => $reviewer_name,
                    'content' => $content,
                    'review' => $review,
                    'reviewer_id' => $reviewer_id,
                    'rating' => $rating,
                    'review_date' => $review_date
                );
            }


            // Method 2

            $main_div = self::get_pattern(array('id="productReviews"', '</table>'), $page);
            $starts = array();
            $start = 0;

            while (strpos($main_div, '<!-- BOUNDARY -->', $start) !== false) {
                $start = strpos($main_div, '<!-- BOUNDARY -->', $start + 1);
                $starts[] = $start;
                $start++;
            }

            foreach ($starts as $start) {

                $review = self::get_pattern(array('<a name="', '"'), $main_div, $start);
                $title = self::get_pattern(array('<span style="vertical-align:middle;"><b>', '<'), $main_div, $start);
                $reviewer_id = self::get_pattern(array('gp/profile/', '/'), $main_div, $start);
                $reviewer_name = self::get_pattern(array('"font-weight: bold;">', '<'), $main_div, $start);
                $content = self::get_div('reviewText', $main_div, $start);
                $review_date = convert_date(self::get_pattern(array('<nobr>', '<'), $main_div, $start), $domain);

                $rating_start = strpos($main_div, 'swSprite s_star_', $start) + 16;
                $rating = substr($main_div, $rating_start, 1);


                $all_reviews[] = array(
                    'title' => $title,
                    'reviewer_name' => $reviewer_name,
                    'content' => $content,
                    'review' => $review,
                    'reviewer_id' => $reviewer_id,
                    'rating' => $rating,
                    'review_date' => $review_date
                );

            }

            return array(
                'error' => 0,
                'reviews' => $all_reviews
            );

        }


		function scrape_review_permalink($url) {

			$domain = parse_url($url."/");

			if (isset($domain['host'])) $domain = $domain['host'];
			else {
				if (isset($domain['path'])) {
					$domain = substr($domain['path'], 0, strpos($domain['path'], '/'));
				}
			}

			$page = self::get_page($url);

			if ($page['error']) {
				return array(
					'error' => $page['error'],
					'error_message' => $page['error_message'],
					'review' => array()
					);
			}

			$html = $page['html'];
			$title = self::get_div('class="summary"', $html);
			$content = self::get_div('class="reviewText"', $html);
			if ($content == "") $content = self::get_div('class="summary"', $html);


			$video = (strpos($content, "amznJQ") !== false) ? 1 : 0;

			$divs = self::get_div_repeat('<div', $content, 0, 1);

			foreach ($divs as $div) {
				$content = str_replace($div, "", $content);
			}

			$divs = self::get_div_repeat('<span', $content, 0, 1);

			foreach ($divs as $div) {
				$content = str_replace($div, "", $content);
			}

			$profile_id = self::get_pattern(array('profile/', '/'), $html);

			if ($domain == "www.amazon.co.jp") {
				$rating = self::get_pattern(array('<!-- BOUNDARY -->', '<img', 'alt="', ' ', '.'), $html);
			}
			else $rating = self::get_pattern(array('<!-- BOUNDARY -->', '<img', 'alt="', '.'), $html);

			$review = self::get_pattern(array('<!-- BOUNDARY -->', '<a name="', '"'), $html);
			$review_date = self::convert_date(self::get_pattern(array('<!-- BOUNDARY -->', '<nobr>', '</nobr>'), $html), $url);

			$reviewer_name = self::get_pattern(array('vcard"', '<a', '>', '<'), $html);


			$asin = self::get_pattern(array('ASIN":"', '"'), $html);
			if ($asin == "") $asin = $asin = self::get_div('class="asin"', $html);
			if ($asin == "") $asin = self::get_pattern(array('ASIN=', '"'), $html);

			$images = (strpos($html, "review-image-thumbnail") !== false) ? 1 : 0;

			$verified = (strpos($html, "amazon-verified-purchase") !== false) ? 1 : 0;

			$review = array(
				'title' => $title,
				'rating' => $rating,
				'content' => $content,
				'profile_id' => $profile_id,
				'review' => $review,
				'review_date' => $review_date,
				'reviewer_name' => $reviewer_name,
				'asin' => $asin,
				'video' => $video,
				'images' => $images,
				'verfied' => $verified
				);


			return array(
				'error' => $page['error'],
				'error_message' => $page['error_message'],
				'review' => $review
				);
		}
	}