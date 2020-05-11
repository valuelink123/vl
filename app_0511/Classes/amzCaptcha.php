<?php
namespace App\Classes;
	class amzCaptcha extends Scrape {

		// Histogram for letters slanting forward

		private static $oddLetterPattern = array(
			'a' => array( array(14,25,31,30,20,10,11,11,11,11,11,10,10,10,8,8,7,6,7,7,7,4,3), array(2,2,2,2,4,4,4,4,4,4,4,4,4,2,2,2,2,2,2,2,2,2,2) ),
			'b' => array( array(7,10,14,19,19,19,21,17,13,11,12,11,11,10,10,10,12,16,17,16,14,9,13,10,8), array(2,2,2,2,4,4,4,6,6,6,6,6,6,6,6,6,6,6,4,4,6,4,2,2,2) ),
			'c' => array( array(5,13,16,19,14,9,9,7,7,8,8,8,6,6,6,8,7,8,10,11,8,8,11,10,10,7), array(6,2,2,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,2,2,2) ),
			'e' => array( array(3,7,10,15,19,18,17,21,18,15,12,10,10,10,12,12,11,12,12,8,7,7,6,4,4,4,4), array(2,2,2,2,2,4,4,4,6,6,6,6,6,6,6,6,6,6,6,6,4,4,4,2,2,2,2) ),
			'f' => array( array(3,7,10,15,19,18,17,21,19,13,9,8,8,8,7,7,7,7,7,4,3), array(2,2,2,2,2,4,4,4,6,6,6,4,4,4,4,4,4,4,4,4,2) ),
			'g' => array( array(4,13,15,20,12,9,10,6,8,7,8,7,6,6,8,10,10,10,10,13,12,13,10,12,13,14,13,10,8,3), array(4,2,2,2,4,4,4,4,4,4,4,4,4,4,6,6,6,6,6,6,6,6,6,6,4,2,2,2,2,2) ),
			'h' => array( array(7,10,14,14,15,14,17,14,11,6,3,4,3,4,4,3,4,5,9,12,17,15,15,14,14,12,8,4), array(2,2,2,2,2,2,2,4,4,4,2,2,2,2,2,2,2,4,4,4,2,2,2,2,2,2,2,2) ),
			'j' => array( array(5,5,7,6,4,4,8,12,16,19,18,19,18,12,7), array(2,2,2,2,2,2,4,4,4,4,4,4,2,2,2) ),
			'k' => array( array(7,10,14,14,14,15,15,18,13,10,6,9,11,12,12,11,10,11,10,7,5,5,5,5,4,3), array(2,2,2,2,2,2,2,2,4,4,2,2,2,4,4,4,4,4,4,4,2,2,2,2,2,2) ),
			'l' => array( array(7,11,14,14,14,14,14,10,7,4,4,4,4,4,4,4,4,4,4,4,4,4,4), array(2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2) ),
			'm' => array( array(7,11,14,18,21,20,20,16,13,8,5,6,6,6,5,5,6,6,6,14,28,30,21,10,9,13,15,14,14,14,12,8,3), array(2,2,2,2,2,4,4,4,4,4,2,2,2,2,2,2,2,2,2,4,2,2,4,4,2,2,2,2,2,2,2,2,2) ),
			'n' => array( array(7,11,14,18,20,19,20,16,10,8,5,5,6,4,6,4,5,6,10,14,18,19,21,19,15,12,8,4), array(2,2,2,2,2,4,4,4,4,4,2,2,2,2,2,2,2,4,4,4,4,4,4,2,2,2,2,2) ),
			'p' => array( array(3,7,10,15,19,19,19,20,17,13,10,8,8,7,8,7,6,7,11,13,10,7), array(2,2,2,2,2,4,4,4,6,6,6,4,4,4,4,4,4,4,4,2,2,2) ),
			'r' => array( array(3,7,11,14,18,17,18,21,19,15,10,7,8,7,8,7,7,6,8,13,15,13,9,9,9,8,4), array(2,2,2,2,2,4,4,4,6,6,6,4,4,4,4,4,4,4,4,4,2,4,4,2,2,2,2) ),
			't' => array( array(4,4,3,4,4,4,4,7,11,15,20,19,19,18,14,10,6,4), array(2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,2) ),
			'u' => array( array(7,11,14,15,14,12,10,7,4,4,3,4,3,3,3,4,4,4,9,13,17,18,20,19,14,9), array(2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,2,2,2) ),
			'x' => array( array(4,5,5,6,4,8,11,13,14,16,17,14,12,9,14,17,16,14,12,10,8,4,5,6,4,4,4), array(2,2,2,2,2,4,4,4,4,4,2,2,2,2,2,2,6,4,4,4,4,2,2,2,2,2,2) ),
			'y' => array( array(4,6,5,5,5,5,5,5,5,5,4,5,7,11,17,25,21,18,11,6,3), array(2,2,2,2,2,2,2,2,2,2,2,2,2,4,2,2,4,4,4,2,2) ),
		);

		// Histogram for letters slanting backwards

		private static $evenLetterPattern = array(
			'a' => array( array(5,5,7,7,7,6,7,7,8,10,11,11,11,10,10,11,10,16,22,31,31,20,5), array(2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,6,4,2,2,4,4) ),
			'b' => array( array(5,9,13,18,18,18,20,20,16,12,10,11,10,11,10,11,11,11,13,19,17,19,13,7,5), array(2,2,2,2,4,4,4,6,6,6,6,6,6,6,6,6,6,6,6,4,6,6,4,2,2) ),
			'c' => array( array(4,13,16,17,14,11,8,8,8,8,8,7,6,6,7,7,7,7,8,10,10,11,13,10,7,6), array(4,2,2,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,2,2) ),
			'e' => array( array(5,9,13,17,18,18,20,20,16,13,12,12,10,12,12,12,11,12,12,10,8,7,4,4,4), array(2,2,2,2,4,4,4,6,6,6,6,6,6,6,6,6,6,6,6,6,4,4,2,2,2) ),
			'f' => array( array(5,9,13,14,15,14,16,16,11,8,7,7,7,8,8,8,7,8,8,8,3,4,3,4,3), array(2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,4,4,4,4,2,2,2,2,2) ),
			'g' => array( array(3,12,16,20,12,9,10,7,8,8,8,7,6,6,9,10,9,11,11,10,11,16,20,21,18,14,8), array(4,2,2,2,4,4,4,4,4,4,4,4,4,4,6,6,6,6,6,6,6,6,4,4,4,4,4) ),
			'h' => array( array(5,9,14,15,14,14,16,16,12,8,3,4,4,4,4,4,4,4,8,12,16,16,15,15,15,13,10,6), array(2,2,2,2,2,2,2,4,4,4,2,2,2,2,2,2,2,2,4,4,4,2,2,2,2,2,2,2) ),
			'j' => array( array(6,9,10,9,4,3,4,3,3,3,4,6,8,11,14,15,15,14,11,7,3), array(2,2,2,4,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2) ),
			'k' => array( array(6,9,14,14,15,15,17,15,12,8,4,5,8,12,15,14,15,14,10,10,6,3,4,3,4,3), array(2,2,2,2,2,2,2,4,4,4,2,2,2,2,4,4,4,4,4,4,4,2,2,2,2,2) ),
			'l' => array( array(5,9,14,17,18,18,18,16,12,8,4,4,4,4,4,4,3), array(2,2,2,2,4,4,4,4,4,4,2,2,2,2,2,2,2) ),
			'm' => array( array(5,9,14,14,14,14,15,12,9,15,29,30,22,8,6,6,5,6,5,6,5,6,6,10,15,18,20,21,20,17,13,8,4), array(2,2,2,2,2,2,2,2,2,2,4,2,4,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,2,2,2,2,2) ),
			'n' => array( array(5,9,14,14,14,14,15,12,8,10,10,12,13,12,12,12,12,11,8,8,12,15,15,15,15,13,10,6), array(2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2) ),
			'p' => array( array(5,9,13,14,14,14,17,16,11,8,7,7,8,8,7,7,7,7,8,9,8,13,12,9,4), array(2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,2,2,4) ),
			'r' => array( array(5,9,14,14,15,14,16,16,12,8,7,8,8,7,7,7,7,13,17,23,21,11,13,11,8,7), array(2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,4,4,6,6,4,6,6,2,2,2,2) ),
			't' => array( array(4,6,10,14,18,18,18,19,15,11,7,4,4,4,4,4,4,4,4,4), array(2,4,4,4,4,4,4,2,2,2,2,2,2,2,2,2,2,2,2,2) ),
			'u' => array( array(3,11,16,20,19,18,15,12,8,4,4,3,3,3,4,3,4,4,5,8,11,13,15,15,13,10,6), array(4,2,2,4,4,4,4,4,4,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2) ),
			'x' => array( array(3,4,5,6,5,5,5,9,10,12,15,16,14,11,11,12,15,18,16,14,12,8,6,5,4,5,5,3), array(2,2,2,2,2,2,2,4,4,4,4,4,2,2,2,2,2,4,4,4,4,4,4,2,2,2,2,2) ),
			'y' => array( array(4,8,14,20,23,24,16,9,6,5,4,4,6,5,5,6,5,5,5,5,4,3), array(2,2,4,4,8,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2) ),
		);

		/**
		* Pass through the Amazon captcha page. Returns the URL to solve captcha.
		*
		* @param string $page 		The HTML of captcha page
		*
		* @return string 			The URL of the captcha validation with solution
		*/

		static function getCaptchaURL($page) {

			$form = self::get_div('<form', $page);

			$inputValues = self::get_pattern_repeat(array('<input', 'value="', '"'), $form);
			$inputNames = self::get_pattern_repeat(array('<input', 'name="', '"'), $form);

			$img = self::get_pattern(array('<img', 'src="', '"'), $form);

			if ($img == '') {
				return '';
			}

			$solve = self::solveCaptcha($img);

			$postfields = array();

			foreach ($inputValues as $key => $value) {
				$postfields[] = $inputNames[$key]."=".urlencode($value);
			}

			$postfields[] = "field-keywords=".strtoupper($solve);

			$amazon_domain = self::get_pattern(array('www.amazon.', '/'), $page);

			$url = "https://www.amazon.".$amazon_domain."/errors/validateCaptcha?".implode("&", $postfields);

			return $url;
		}


		/**
		* Pass through the URL of captcha image. Returns the letters in captcha.
		*
		* @param string $imageLocation 		The URL of the image
		*
		* @return string 					The captcha letters
		*/

		static function solveCaptcha($imageLocation) {
			$img = file_get_contents($imageLocation);
			$base = pathinfo($imageLocation);
			file_put_contents(public_path().'/Captcha/'.$base['basename'],$img); 
			$image = new \Imagick(public_path().'/Captcha/'.$base['basename']);
			//$image = new \Imagick($imageLocation);
			$threshold = 90;

			do {
				$histogram = self::getHistogram($image, 2, $threshold);

				$start = -1;
				$letters = array();

				foreach ($histogram as $key => $h) {

					if ((!$h[0]) && ($start == -1)) $start = 0;

					if (($h[0]) && (!$start)) $start = $key;

					if ((!$h[0]) && ($start > 0)) {
						$letters[] = array_slice($histogram, $start, $key - $start);
						$start = 0;
					}
				}

				$threshold += 5;

			} while ((sizeof($letters) > 6) && ($threshold < 200));

			if ($start > 0) $letters[] = array_slice($histogram, $start, 199 - $start);



			$solved = array();

			foreach ($letters as $key => $captcha) {

				$letterPattern = ($key % 2) ? self::$oddLetterPattern : self::$evenLetterPattern;

				$bestLetter = "";
				$bestLetterDifference = 100000;

				foreach ($letterPattern as $letter => $histogram) {

					$letterDifference = 0;

					foreach ($histogram[0] as $x => $y) {
						if (isset($captcha[$x])) {
							$letterDifference += abs($captcha[$x][0] - $y);
						}
					}

					foreach ($histogram[1] as $x => $y) {
						if (isset($captcha[$x])) {
							$letterDifference += abs($captcha[$x][1] - $y)*5;
						}
					}

					if ($letterDifference < $bestLetterDifference) {
						$bestLetterDifference = $letterDifference;
						$bestLetter = $letter;
					}
				}



				$solved[$key] = $bestLetter;
			}
			unlink(public_path().'/Captcha/'.$base['basename']);
			return implode("", $solved);


		}


		/**
		* Returns count of black pixels as well as stripes in each vertical column of pixels
		*
		* @param string $image 			Imagick image
		* @param int $space_threshold 	The max black pixels to count a vertical column as a space between letters
		* @param int $threshold 		The RGB threshold for a "white pixel"
		*
		* @return array 				The histogram
		*/

		static function getHistogram($image, $space_threshold, $threshold = 90) {

			$histogram = array();

			for ($x = 0; $x < $image->getImageWidth(); $x++) {

				$countColumn = self::countColumn($image, $x, $threshold);

				$blackCount = $countColumn[0];
				$stripeCount = $countColumn[1];

				if ($blackCount > $space_threshold) $histogram[$x] = array($blackCount, $stripeCount);
				else $histogram[$x] = array(0, $stripeCount);
			}

			return $histogram;

		}


		/**
		* Counts black pixels and stripes in a column of pixels
		*
		* @param string $image 		Imagick image
		* @param int $x 			The x value of the image column
		* @param int $threshold 	The RGB threshold for a "white pixel"
		*
		* @return array 			array(countBlack, countStripe)
		*/

		static function countColumn($image, $x, $threshold) {

			if ($x > $image->getImageWidth()) return array(0, 0);

			$countStripe = 0;
			$countBlack = 0;
			$white = 1;

			for ($y = 0; $y < $image->getImageHeight(); $y++) {

				$isWhite = self::isWhite($image, $x, $y, $threshold);

				if ($isWhite != $white) {
					$countStripe++;
					$white = 1 - $white;
				}

				$countBlack += (1 - $isWhite);
			}

			return array($countBlack, $countStripe);

		}


		/**
		* Checks if a pixel is white
		*
		* @param string $image 		Imagick image
		* @param int $x 			The x value of the image column
		* @param int $y 			The y value of the image column
		* @param int $threshold 	The RGB threshold for a "white pixel"
		*
		* @return int 				1 - white, 0 - not white
		*/

		static function isWhite($image, $x, $y, $threshold = 90) {

			if ($x > $image->getImageWidth()) return 0;
			if ($y > $image->getImageHeight()) return 0;

			$pixel = $image->getImagePixelColor($x, $y)->getColor();

			if (($pixel['r'] > $threshold) &&
				($pixel['g'] > $threshold) &&
				($pixel['b'] > $threshold)) return 1;

			return 0;
		}


		static function drawImage($image) {

			for ($y = 0; $y < $image->getImageHeight(); $y++) {
				for ($x = 0; $x < 200; $x++) {
					if (!self::isWhite($image, $x, $y)) echo "1,";
					else echo "0,";
				}

				echo "<br>";
			}
		}

	}

?>