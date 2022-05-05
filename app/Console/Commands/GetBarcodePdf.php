<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;
use App\Classes\SapRfcRequest;

class GetBarcodePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:barcodePdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $barcodeFilePath = storage_path('app/public/barcode');
        if (!file_exists($barcodeFilePath)) {
            mkdir($barcodeFilePath, 0755, true);
        }
        $poList = [];
        $poArray = DB::table('barcode_po_details')->where('pdf_status', 0)->distinct()->get(['purchase_order']);
        $poArray = json_decode(json_encode($poArray), true);
        for ($a = 0; $a < count($poArray); $a++) {
            $poList[] = $poArray[$a]['purchase_order'];
        }

        for ($a = 0; $a < count($poList); $a++) {
            $po = $poList[$a];
            $poDetail = DB::table('barcode_po_details')->where('purchase_order', $po)->first();
            //prevent scheduled tasks overlapping
            if ($poDetail->pdf_status != 0) {
                continue;
            }
            DB::table('barcode_po_details')->where('purchase_order', $po)->update(['pdf_status' => 1]);

            $totalCodeCount = intval($poDetail->sum_quantity * 1.1);
            $bt = DB::table('barcode_scan_record')->where('purchase_order', $po)->pluck('barcode_text');
            $bt = json_decode(json_encode($bt), true);
            //if the barcode generating is not completed yet
            if ($totalCodeCount != count($bt)) {
                DB::table('barcode_po_details')->where('purchase_order', $po)->update(['pdf_status' => 0]);
                continue;
            }

            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            try {
                //'if clause' reserved. The type of barcode paper may be considered.
                if (1 == 1) {
                    $btBulkArray = array_chunk($bt, 900);
                    $startTotal = time();
                    foreach ($btBulkArray as $kIndex => $btBulk) {
                        $start = time();
                        $mpdf = new \Mpdf\Mpdf();
                        $btChunk = array_chunk($btBulk, 5); //每行打印的条码个数
                        $rowNumberTotal = count($btChunk);
                        $html = '<html><style type="text/css">@page {margin-top:12.5mm; margin-left:12.5mm;} .border{width:102px;float:left;margin-left:18.9px;margin-right:9.45px;margin-bottom:9.64px;border:1px solid #000;padding-left:7.53px;padding-right:7.53px;padding-top:5.55px;padding-bottom:3.72px;} .bTextDiv{text-align: center;width:102px; height:11px;} div span{font-size:10px;margin-left:-2px;-webkit-transform:scale(0.8);display:block;}}</style><body style="margin-top:0px; margin-left:10px;font-family: arial;">';
                        $html .= '<p>po: '.$po.'</p>';
                        $mpdf->WriteHTML($html);
                        $row = 0;
                        $html = '';
                        foreach ($btChunk as $chunk) {
                            $row++;
                            $html .= '<div style="margin-top:9.64px;">';
                            foreach ($chunk as $k => $barcodeText) {
                                $barcode = $generator->getBarcode($barcodeText, $generator::TYPE_CODE_93, 1, 20, array(0, 0, 0));
                                $barcode = base64_encode($barcode);
                                $div = '<div class="border" style="margin-left: 9.45px">';
                                if ($k > 0) {
                                    $div = '<div class="border">';
                                }
                                $html .= $div . '<div>
                                          <img style="width: 102px; height:14px;" src="data:image/png;base64,' . $barcode . '"/>
                                          <div class="bTextDiv"><span>SN:' . $barcodeText . '</span></div>
                                     </div>
                                   </div>';

                            }
                            $html .= '<div style="clear:both"></div></div>';
                            if ($row % 18 == 0 && $row % 180 != 0) {
                                $html .= '<div style="page-break-after: always"></div>';
                            }
                            if (($row % 180 == 0 && $row <= $rowNumberTotal) || $row == $rowNumberTotal) {
                                $mpdf->WriteHTML($html);
                                $html = '';
                            }
                        }
                        $html = '</body></html>';
                        $mpdf->WriteHTML($html);

                        $filePath = $barcodeFilePath . '/' . $po . '_' . ($kIndex + 1) . '.pdf';
                        $mpdf->Output($filePath, 'F');
                        unset($mpdf);
                        //echo 'Po No.:' . $po . '_' . ($kIndex + 1) . ', 耗时:' . (time() - $start) . PHP_EOL;
                    }
                    //echo 'Po No.:' . $po . ', 耗时:' . (time() - $startTotal) . PHP_EOL;

                    // Merge the files
                    $pdf = new \Clegginabox\PDFMerger\PDFMerger;
                    for ($m = 0; $m < count($btBulkArray); $m++) {

                        $pdf->addPDF($barcodeFilePath . '/' . $po . '_' . ($m + 1) . '.pdf', 'all');
                    }
                    $pdf->merge('file', $barcodeFilePath . '/' . $po . '.pdf', 'P');

                    for ($m = 0; $m < count($btBulkArray); $m++) {
                        unlink($barcodeFilePath . '/' . $po . '_' . ($m + 1) . '.pdf');
                    }
                    echo 'Po No.:' . $po . ', 包括合并文件，总耗时:' . (time() - $startTotal) . PHP_EOL;

                    DB::table('barcode_po_details')->where('purchase_order', $po)->update(['pdf_status' => 2]);
                }
            } catch (\Exception $e) {
                DB::table('barcode_po_details')->where('purchase_order', $po)->update(['pdf_status' => 0]);
            }
        }
    }
}
