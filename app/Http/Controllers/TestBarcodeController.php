<?php 

namespace App\Http\Controllers;

use Picqer\Barcode\BarcodeGeneratorPNG;

class TestBarcodeController extends Controller
{

	public function __construct()
	{

	}


	public function index(){

		$generator = new BarcodeGeneratorPNG();
	//	var_dump($generator);
	//	die();
		$barcodeText=$_REQUEST[ 'bc' ]; 
		
		$width=intval($_REQUEST[ 'width' ]);
		$high= intval($_REQUEST[ 'high' ]);  

		$barcode = $generator->getBarcode($barcodeText, $generator::TYPE_CODE_93, $width, $high, array(0, 0, 0));
                $barcode = base64_encode($barcode);
		
		$html='<img style="width: 102px; height:14px" src="data:image/png;base64,' . $barcode . '"/>';

		echo $html;

	}



}

?>
