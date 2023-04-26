<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asin extends Model
{
    //
    protected $table = 'asin';
    protected $guarded = [];
    public $timestamps = false;
    const DOMIN_MARKETPLACEID = [
        'A2Q3Y263D00KWC' => 'mws.amazonservices.com',
        'A2EUQ1WTGCTBG2' => 'mws.amazonservices.ca',
        'A1AM78C64UM0Y8' => 'mws.amazonservices.com.mx',
        'ATVPDKIKX0DER' => 'mws.amazonservices.com',
        'A2VIGQ35RCS4UG' => 'mws.amazonservices.ae',
        'A1PA6795UKMFR9' => 'mws-eu.amazonservices.com',
        'ARBP9OOSHTCHU' => 'mws-eu.amazonservices.com',
        'A1RKKUPIHCS9HS' => 'mws-eu.amazonservices.com',
        'A13V1IB3VIYZZH' => 'mws-eu.amazonservices.com',
        'A1F83G8C2ARO7P' => 'mws-eu.amazonservices.com',
        'A21TJRUUN4KGV' => 'mws.amazonservices.in',
        'APJ6JRA9NG5V4' => 'mws-eu.amazonservices.com',
        'A1805IZSGTT6HS' => 'mws-eu.amazonservices.com',
        'A17E79C6D8DWNP' => 'mws-eu.amazonservices.com',
        'A33AVAJ2PDY3EV' => 'mws-eu.amazonservices.com',
        'A19VAU5U5O7RUS' => 'mws-fe.amazonservices.com',
        'A39IBJ37TRP1C6' => 'mws.amazonservices.com.au',
        'A1VC38T7YXB528' => 'mws.amazonservices.jp',
    ];
    const DOMIN_MARKETPLACEID_SX = [
        'A2Q3Y263D00KWC' => 'BR',
        'A2EUQ1WTGCTBG2' => 'CA',
        'A1AM78C64UM0Y8' => 'MX',
        'ATVPDKIKX0DER' => 'US',
        'A2VIGQ35RCS4UG' => 'AE',
        'A1PA6795UKMFR9' => 'DE',
        'ARBP9OOSHTCHU' => 'EG',
        'A1RKKUPIHCS9HS' => 'ES',
        'A13V1IB3VIYZZH' => 'FR',
        'A1F83G8C2ARO7P' => 'UK',
        'A21TJRUUN4KGV' => 'IN',
        'APJ6JRA9NG5V4' => 'IT',
        'A1805IZSGTT6HS' => 'NL',
        'A17E79C6D8DWNP' => 'SA',
        'A33AVAJ2PDY3EV' => 'TR',
        'A19VAU5U5O7RUS' => 'SG',
        'A39IBJ37TRP1C6' => 'AU',
        'A1VC38T7YXB528' => 'JP',
    ];
    const DOMIN_MARKETPLACEID_URL = [
        'A2EUQ1WTGCTBG2' => 'www.amazon.ca',
        'A1AM78C64UM0Y8' => 'www.amazon.mx',
        'ATVPDKIKX0DER' => 'www.amazon.com',
        'A1PA6795UKMFR9' => 'www.amazon.de',
        'A1RKKUPIHCS9HS' => 'www.amazon.es',
        'A13V1IB3VIYZZH' => 'www.amazon.fr',
        'A1F83G8C2ARO7P' => 'www.amazon.co.uk',
        'APJ6JRA9NG5V4' => 'www.amazon.it',
        'A1VC38T7YXB528' => 'www.amazon.co.jp',
    ];
    const SKU_STATUS_KV = [
        '0'=>'淘汰',
        '1'=>'保留',
        '2'=>'新品',
        '3'=>'配件',
        '4'=>'替换',
        '5'=>'待定',
        '6'=>'停售',
    ];
    const ADMIN_EMAIL =
        array(
            "zouyuanxun@valuelinkcorp.com",
            "wangmengshi@valuelinkcorp.com",
            "wanghong@valuelinkltd.com",
            "shiqingbo@valuelinkltd.com",
			"wulanfang@valuelinkcorp.com",
			"xialu@valuelinkltd.com"
        );
}

