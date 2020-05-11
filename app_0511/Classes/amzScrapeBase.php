<?php
	include ('scrape.php');
	include ('amzCaptcha.php');
	include ('Encoding.php');

	use App\Classes\Encoding;

	class amzScrapeBase extends Scrape {

		/**
		* Class which provides the basis for scraping Amazon pages
		*
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

			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:42.0) Gecko/20100101 Firefox/42.0");

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



			if ($curl_handler == "") $ch = self::get_curl_handler($url, 60, $header, true, $followlocation);
			else {
				$ch = $curl_handler;
				curl_setopt($ch, CURLOPT_URL, $url);
			}

			$retry = 0;			// Count of retry when curl gets captcha
			$curl_error = 0;	// Curl error flag
			$curl_exec = "Type the characters you see in this image";
			$error_message = "";


			// Start get page loop

			while ((strpos($curl_exec, "Type the characters you see in this image") !== false) && ($retry < $max_retry) && (!$curl_error)) {
				set_time_limit(500);

				curl_setopt($ch, CURLOPT_URL, $url);
				$curl_exec = curl_exec($ch);

				if(curl_errno($ch)) {

					// curl error

					$error_message = 'curl error:'.curl_error($ch);
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


					// Solve captcha

					$solve_url = amzCaptcha::getCaptchaURL($curl_exec);
					echo $solve_url."<br>";

					sleep(rand(5, 8));

					curl_setopt($ch, CURLOPT_URL, $solve_url);
					$curl_exec = curl_exec($ch);

					$retry++;

					$curl_exec = "Type the characters you see in this image";
					sleep(rand(2, 6));

				}
				else if (strpos($curl_exec, "ERROR: The requested URL could not be retrieved") !== false) {
					$retry++;
					$curl_exec = "Type the characters you see in this image";
					sleep(rand(2, 6));
				}
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

	}
?>