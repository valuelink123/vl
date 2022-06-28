<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\SapPurchase;
use App\Classes\SapRfcRequest;

class SyncSapPurchaseOrder extends Command
{
    protected $signature = 'sync:purchase {--afterDate=} {--beforeDate=}';

    public function __construct()
    {
        parent::__construct();

    }

    public function handle()
    {
        $afterDate = $this->option('afterDate')?date('Ymd',strtotime($this->option('afterDate'))):date('Ymd',strtotime('- 2days'));
        $beforeDate = $this->option('beforeDate')?date('Ymd',strtotime($this->option('beforeDate'))):date('Ymd',strtotime('+ 1days'));

        $sap = new SapRfcRequest();

        $data['postdata']['EXPORT']=array('O_MESSAGE'=>'','O_RETURN'=>'');
        $data['postdata']['TABLE']=array('RESULT_TABLE'=>array(0));
        $data['postdata']['IMPORT']=array('I_DATE_S'=>$afterDate,'I_DATE_E'=>$beforeDate);


        $res=$sap->getPurchaseOrderList($data);
        if(array_get($res,'data.O_RETURN')=='S'){
            $lists = array_get($res,'data.RESULT_TABLE');
            foreach($lists as $list){
//                $__EBELN=trim(array_get($list,'EBELN',''));
//                $__BUKRS=trim(array_get($list,'BUKRS',''));
                $__BSART=trim(array_get($list,'BSART',''));
                if($__BSART=='NB') {
                    SapPurchase::updateOrCreate(["EBELN" => trim(array_get($list,'EBELN',''))],
                        [
                            "BUKRS" => trim(array_get($list, 'BUKRS', '')),
                            "BSTYP" => trim(array_get($list, 'BSTYP', '')),
                            "BSART" => trim(array_get($list, 'BSART', '')),
                            "LOEKZ" => trim(array_get($list, 'LOEKZ', '')),
                            "AEDAT" => trim(array_get($list, 'AEDAT', '')),
                            "ERNAM" => trim(array_get($list, 'ERNAM', '')),
                            "LIFNR" => trim(array_get($list, 'LIFNR', '')),
                            "ZTERM" => trim(array_get($list, 'ZTERM', '')),
                            "EKORG" => trim(array_get($list, 'EKORG', '')),
                            "EKGRP" => trim(array_get($list, 'EKGRP', '')),
                            "WAERS" => trim(array_get($list, 'WAERS', '')),
                            "WKURS" => trim(array_get($list, 'WKURS', '')),
                            "BEDAT" => trim(array_get($list, 'BEDAT', '')),
                            "UNSEZ" => trim(array_get($list, 'UNSEZ', '')),
                            "FRGKE" => trim(array_get($list, 'FRGKE', ''))
                        ]
                    );
                }
            }
        }
    }
}