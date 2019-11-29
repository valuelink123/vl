<?php
function getAccountTypes(){
    return array(
        'Amazon','Site'
    );
}

function getAsinSites(){
    return array(
        'www.amazon.com','www.amazon.ca','www.amazon.mx','www.amazon.co.uk','www.amazon.fr','www.amazon.de','www.amazon.it','www.amazon.es','www.amazon.co.jp'
    );
}
function getMarks(){
    return array(
        'Follow','Important'
    );
}


function getForbidWords(){
	return json_encode(array(
        'review'
    ));
}

function getClosedReson(){
	return array(
		'Customer refused',
		'Listing abandoned',
		'Listing removed' ,
		'Listing limited By Amazon',
		'Review removed by Amazon',
		'Lack information',
		'No reply',
		'Other'
	);
}
function getWarnWords(){
	return json_encode(array(
        'positive','remove','correct','edit','update','negative','neutral','star'
    ));
}

function getReviewWarnWords(){
	return array(
        'fire','explosion','smoke' ,'burn' ,'explode', 'swell', 'flame', 'spark', 'blow-up', 'blow up', 'blowup'
    );
}

function getEType(){
    return array(
        'Listing description issues','Order issues','Customer experience issues','Manual issues','Transportation issues','Quality issues','Accessories','Invoice','Gift','Other issues'
    );
}

function getAsinStatus(){
    return array('S'=>'MostImportant','A'=>'Important','B'=>'Normal','C'=>'Abandon','D'=>'Unlisted');
}

function getReviewStatus(){
    return array(
        '1'=>'None',
		'2'=>'Wait Reply',
		'3'=>'Removed',
		'4'=>'Update 4 stars',
		'5'=>'Update 5 stars',
		'6'=>'Closed',
		'7'=>'Need Buy',
		'8'=>'Need Delete',
		'9'=>'No contacts',
    );
}

function getCustomerFb(){
    return array(
        '0'=>'None',
		'1'=>'Wait Reply',
		'2'=>'No Reply',
		'3'=>'Unwilling',
    );
}

function getSiteCode(){
     return array(
         'United States' =>'ATVPDKIKX0DER',
         'Canada' =>'A2EUQ1WTGCTBG2',
         'Mexico' =>'A1AM78C64UM0Y8',
         'United Kingdom' =>'A1F83G8C2ARO7P',
         'Germany' =>'A1PA6795UKMFR9',
         'France' =>'A13V1IB3VIYZZH',
         'Italy' =>'APJ6JRA9NG5V4',
         'Spain' =>'A1RKKUPIHCS9HS',
         'Japan' =>'A1VC38T7YXB528'
     );
}

function getSiteConfig(){

    $configUS=array(
        'key_id'=>'AKIAI7UMHLA4P6BPW5AQ',
        'secret_key'=>'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',
        'serviceUrl'=>'https://mws.amazonservices.com'
    );
    $configEU=array(
        'key_id'=>'AKIAIBAZLYIM2J4TS5AA',
        'secret_key'=>'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',
        'serviceUrl'=>'https://mws-eu.amazonservices.com'
    );
    $configJP=array(
        'key_id'=>'AKIAIS5RFQJDH5UFEWSA',
        'secret_key'=>'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',
        'serviceUrl'=>'https://mws.amazonservices.jp'
    );
    return array(
        'ATVPDKIKX0DER' =>$configUS,
        'A2EUQ1WTGCTBG2' =>$configUS,
        'A1AM78C64UM0Y8' =>$configUS,
        'A1F83G8C2ARO7P' =>$configEU,
        'A1PA6795UKMFR9' =>$configEU,
        'A13V1IB3VIYZZH' =>$configEU,
        'APJ6JRA9NG5V4' =>$configEU,
        'A1RKKUPIHCS9HS' =>$configEU,
        'A1VC38T7YXB528' =>$configJP
    );
}

function processResponse($response)
{
    return simplexml_load_string($response->toXML());
}


function getSiteUrl(){
    return array(
        'A2EUQ1WTGCTBG2'=>'amazon.ca',
        'A1PA6795UKMFR9'=>'amazon.de',
        'A1RKKUPIHCS9HS'=>'amazon.es',
        'A13V1IB3VIYZZH'=>'amazon.fr',
        'A21TJRUUN4KGV'=>'amazon.in',
        'APJ6JRA9NG5V4'=>'amazon.it',
        'A1VC38T7YXB528'=>'amazon.co.jp',
        'A1F83G8C2ARO7P'=>'amazon.co.uk',
        'A1AM78C64UM0Y8'=>'amazon.com.mx',
        'ATVPDKIKX0DER'=>'amazon.com'
    );
}

function getSiteCur(){
    return array(
        'amazon.ca'=>'CAD',
        'amazon.de'=>'EUR',
        'amazon.es'=>'EUR',
        'amazon.fr'=>'EUR',
        'amazon.in'=>'INR',
        'amazon.it'=>'EUR',
        'amazon.co.jp'=>'JPY',
        'amazon.co.uk'=>'GBP',
        'amazon.com.mx'=>'MXN',
        'amazon.com'=>'USD'
    );
}


function getCurrency(){
    return array(
        'USD','CAD','EUR','INR','JPY','GBP','MXN'
    );
}


function getReportById($client,$id, $sellerId, $auth_token) {
    ob_start();
    $fileHandle = @fopen('php://memory', 'rw+');
    $parameters = array (
        'Merchant' => $sellerId,
        'Report' => $fileHandle,
        'ReportId' => $id,
        'MWSAuthToken' => $auth_token, // Optional
    );
    $request = new \MarketplaceWebService_Model_GetReportRequest($parameters);
    $response = $client->getReport($request);
    $getReportResult = $response->getGetReportResult();
    $responseMetadata = $response->getResponseMetadata();
    rewind($fileHandle);
    $responseStr = stream_get_contents($fileHandle);
    @fclose($fileHandle);
    ob_end_clean();
    return csv_to_array($responseStr, PHP_EOL, "\t");
}


function csv_to_array($string='', $row_delimiter=PHP_EOL, $delimiter = "," , $enclosure = '"' , $escape = "\\" )
{
    $rows = array_filter(explode($row_delimiter, $string));
    $header = NULL;
    $data = array();

    foreach($rows as $row)
    {
        $row = str_getcsv ($row, $delimiter, $enclosure , $escape);

        if(!$header)
            $header = $row;
        else
            $data[] = array_combine($header, $row);
    }

    return $data;
}

function format_num($string){
    $string=trim($string);
    $d = substr($string,-3,1);
    $string = str_replace(array(',','.'),'',$string);
    if($d==',' || $d=='.'){
        $string = substr_replace($string,'.',-2,0);
    }
    return round($string,2);
}


function html2text($str){
    $str = preg_replace("/<style .*?<\\/style>/is", "", $str);
    $str = preg_replace("/<script .*?<\\/script>/is", "", $str);
    $str = preg_replace("/<br \\s*\\/>/i", "", $str);
    $str = preg_replace("/<\\/?p>/i", "", $str);
    $str = preg_replace("/<\\/?td>/i", "", $str);
    $str = preg_replace("/<\\/?div>/i", "", $str);
    $str = preg_replace("/<\\/?blockquote>/i", "", $str);
    $str = preg_replace("/<\\/?li>/i", "", $str);
    $str = preg_replace("/ /i", " ", $str);
    $str = preg_replace("/ /i", " ", $str);
    $str = preg_replace("/&/i", "&", $str);
    $str = preg_replace("/&/i", "&", $str);
    $str = preg_replace("/</i", "<", $str);
    $str = preg_replace("/</i", "<", $str);
    $str = preg_replace("/“/i", '"', $str);
    $str = preg_replace("/&ldquo/i", '"', $str);
    $str = preg_replace("/‘/i", "'", $str);
    $str = preg_replace("/&lsquo/i", "'", $str);
    $str = preg_replace("/'/i", "'", $str);
    $str = preg_replace("/&rsquo/i", "'", $str);
    $str = preg_replace("/>/i", ">", $str);
    $str = preg_replace("/>/i", ">", $str);
    $str = preg_replace("/”/i", '"', $str);
    $str = preg_replace("/&rdquo/i", '"', $str);
    $str = strip_tags($str);
    $str = html_entity_decode($str, ENT_QUOTES, "utf-8");
    $str = preg_replace("/&#.*?;/i", "", $str);
    return $str;
}

function textimage($content){
	$pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";  
	preg_match_all($pattern,$content,$matchContent);  
	if(isset($matchContent[1][0])){  
		$temp=$matchContent[1][0];  
	}else{  
		$temp="./assets/layouts/layout/img/01.jpg";//在相应位置放置一张命名为no-image的jpg图片  
	}
	return $temp;
}

function getSapNumber($str,$decimal=2){
	$decimal=intval($decimal);
	if(is_array($str)){
		$new_str = [];
	    foreach($str as $k=>$v){
			$new_str[$k]=getSapNumber($v,$decimal);
		}
		return $new_str;
	}else{
		if( substr ($str, -1) == '-' ){
			return '-'.round($str,$decimal);
		}else{
			return round($str,$decimal);
		}
	}
	
}

function getComparisonSymbol(){
	return array('>','>=','=','<=','<');
}
function getComparisonfield(){
	return array('Review','FBA Stock Days','Total Stock Days','Total Stock Value','Daily sales','Profit margin');
}
function getFieldtoField(){
	return array(
				'Review'=>'avg_star',
				'FBA Stock Days'=>'fba_stock_keep',
				'Total Stock Days'=>'stock_keep',
				'Total Stock Value'=>'stock_amount',
				'Daily sales'=>'sales',
				'Profit margin'=>'profits',
			);
}
function getFieldtoSort(){
	return array(
				'sales'=>3,
				'avg_star'=>4,
				'profits'=>7,
				'stock_keep'=>11,
				'stock_amount'=>12,
				'fba_stock_keep'=>9,
			);
}

function siteToMarketplaceid(){
	return array(
			 'amazon.com' =>'ATVPDKIKX0DER',
			 'www.amazon.com' =>'ATVPDKIKX0DER',
			 'www.amazon.ca' =>'A2EUQ1WTGCTBG2',
			 'amazon.ca' =>'A2EUQ1WTGCTBG2',
			 'www.amazon.com.mx' =>'A1AM78C64UM0Y8',
			 'amazon.com.mx' =>'A1AM78C64UM0Y8',
			 'www.amazon.co.uk' =>'A1F83G8C2ARO7P',
			 'www.amazon.uk' =>'A1F83G8C2ARO7P',
			 'amazon.co.uk' =>'A1F83G8C2ARO7P',
			 'amazon.uk' =>'A1F83G8C2ARO7P',
			 'amazon.de' =>'A1PA6795UKMFR9',
			 'www.amazon.de' =>'A1PA6795UKMFR9',
			 'amazon.fr' =>'A13V1IB3VIYZZH',
			 'www.amazon.fr' =>'A13V1IB3VIYZZH',
			 'www.amazon.it' =>'APJ6JRA9NG5V4',
			 'amazon.it' =>'APJ6JRA9NG5V4',
			 'www.amazon.es' =>'A1RKKUPIHCS9HS',
			 'amazon.es' =>'A1RKKUPIHCS9HS',
			 'www.amazon.co.jp' =>'A1VC38T7YXB528',
			 'www.amazon.jp' =>'A1VC38T7YXB528',
			 'amazon.co.jp' =>'A1VC38T7YXB528',
			 'amazon.jp' =>'A1VC38T7YXB528'
		 );
}

function getMcfOrderStatus(){
	return array(
		'RECEIVED','INVALID','PLANNING','PROCESSING','CANCELLED','COMPLETE','COMPLETE_PARTIALLED','UNFULFILLABLE'
	);
}

function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
}

function getSapSiteCode(){
	return array(
		'1007'  => 'amazon.com',
		'1008'  => 'amazon.ca',
		'1009'  => 'amazon.de',
		'1010'  => 'amazon.fr',
		'1011'  => 'amazon.it',
		'1012'  => 'amazon.es',
		'1013'  => 'amazon.co.uk',
		'1014'  => 'amazon.co.jp'
	);
}

function getSapFactoryCode(){
	return array(
		'1007'  => 'US01',
		'1008'  => 'CA01',
		'1009'  => 'GR01',
		'1010'  => 'FR01',
		'1011'  => 'IT01',
		'1012'  => 'ES01',
		'1013'  => 'UK01',
		'1014'  => 'JP01'
	);
}

function getStepStatus(){
	return array(
		'1'  => 'Check Customer',
		'2'  => 'Request Reject',
		'3'  => 'Submit Paypal',
		'4'  => 'Check Paypal',
		'5'  => 'Submit Purchase',
		'6'  => 'Check Purchase',
		'7'  => 'Submit Review',
		'8'  => 'Check Review',
		'9'  => 'Completed',
        '10' => 'closed',
        '11' => 'open dispute'
	);
}

function matchSapSiteCode(){
	return array(
		'US'  => '1007',
		'UK'  => '1013',
		'DE'  => '1009',
		'FR'  => '1010',
		'IT'  => '1011',
		'ES'  => '1012',
		'JP'  => '1014'
	);
}

function matchMarketplaceSiteCode(){
	return array(
		'ATVPDKIKX0DER'  => '1007',
		'A1F83G8C2ARO7P'  => '1013',
		'A1PA6795UKMFR9'  => '1009',
		'A13V1IB3VIYZZH'  => '1010',
		'APJ6JRA9NG5V4'  => '1011',
		'A1RKKUPIHCS9HS'  => '1012',
		'A1VC38T7YXB528'  => '1014'
	);
}

//批量添加數據，可避免唯一鍵冲突时报错
function batchInsert($table,$data){
    $fields = array_keys(current($data));
	$insertArr = array_chunk($data,200,true);
	foreach($insertArr as $data) {
		$sql = 'insert ignore into ' . $table . ' (`' . join('`,`', $fields) . '`) values';//字段用反引号分隔
		foreach ($data as $key => $val) {
			$sql .= ' ("' . join('","', $val) . '"),';//数据用双引号分隔
		}
		$sql = rtrim($sql, ',');
		\DB::insert($sql);
	}
}

/*
 * 为得到官网的激活质保用户数据所需的配置
 * db为连的数据库的宏
 * dbname为数据库的库名
 * name为此官网的名称，用于插入到non_ctg表中的from字段
 * formid为wp_gf_form表中warranty表单的id
 * fields表示字段的指定关系，例如name是对应meta_key的1值
 * tecbean.com,funavopro.com,volt-cube.com,vacassoart.com这几个品牌官网都归到spacekeybrands.com了；并且funavopro.com,volt-cube.com,vacassoart.com这三个品牌官网都不能再访问了
 */
function getActiveUserConfig()
{
    $config = array(
        array('db'=>'website','dbname'=>'dbpower_co','name'=>'dbpower','formid'=>array(2,4),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>3)),
        array('db'=>'website','dbname'=>'nursal_co','name'=>'nursal','formid'=>array(2),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>3)),
        array('db'=>'website','dbname'=>'spacekey_net','name'=>'spacekey','formid'=>array(2),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>3)),
        array('db'=>'website','dbname'=>'mykoios_com','name'=>'mykoios','formid'=>array(2),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>4)),
        array('db'=>'website','dbname'=>'tenker_co','name'=>'tenker','formid'=>array(2),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>3)),
        array('db'=>'website','dbname'=>'miropure_co','name'=>'miropure','formid'=>array(2,4),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>3)),
        array('db'=>'website','dbname'=>'mooka_co','name'=>'mooka','formid'=>array(2),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>3)),
        array('db'=>'website','dbname'=>'irunme_net','name'=>'irunme','formid'=>array(1),'fields'=>array('name'=>'5.3','email'=>6,'orderid'=>7)),
        array('db'=>'website','dbname'=>'spacekeybrands_com','name'=>'spacekeybrands','formid'=>array(2),'fields'=>array('name'=>'2.3','email'=>3,'orderid'=>4)),
		array('db'=>'natrogix','dbname'=>'natrogix_com','name'=>'natrogix','formid'=>array(17),'fields'=>array('name'=>'4','email'=>1,'orderid'=>3)),

		array('db'=>'drocon','dbname'=>'drocon_co','name'=>'drocon','formid'=>array(2),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>3)),
		array('db'=>'natrogix','dbname'=>'lypsonline_com','name'=>'lypsonline','formid'=>array(2),'fields'=>array('name'=>'1.3','email'=>2,'orderid'=>3)),
		array('db'=>'website','dbname'=>'vipsupport_jp','name'=>'vipsupport','formid'=>array(1),'fields'=>array('name'=>'2.3','email'=>3,'orderid'=>4)),

		array('db'=>'website','dbname'=>'wordpress','name'=>'azeus','entry_table'=>'wp_53_gf_entry','meta_table'=>'wp_53_gf_entry_meta','formid'=>array(3),'fields'=>array('name'=>1,'email'=>3,'orderid'=>2)),//新添加的azeus官网数据
    );

    return $config;
}
/*
 * 判断订单号是否符合亚马逊订单规则
 * 订单号的规则类似为123-1234567-1234567，3个数字-7个数字-7个数字，长度为19
 */
function matchOrderId($orderid)
{
    $p = '/\d{3}\-\d{7}\-\d{7}/';
    preg_match($p, $orderid, $match);
    if($match && strlen($orderid) == 19){
        return true;
    }
    return false;
}
/*
 * non_ctg表的status字段的值与含义对照
 * 0未跟进，1有意向，2无意向，3未回复
 */
function getNonCtgStatusKeyVal()
{
    return array(0=>'Not Followed Up',4=>'following up',1=>'Willing',2=>'Unwilling',3=>'No Reply');
}

/*
 * 得到添加到CRM模块的client等表的来源表的配置
 */
function getCtgCashbackBogoConfig(){
	$config = array(
		array('table'=>'ctg','from'=>'CTG'),
		array('table'=>'b1g1','from'=>'BOGO'),
		array('table'=>'cashback','from'=>'Cashback'),
	);
	return $config;
}

/**
 * 批量更新函数
 * @param $data array 待更新的数据，二维数组格式
 * @param array $params array 值相同的条件，键值对应的一维数组
 * @param string $field string 值不同的条件，默认为id
 * @return bool|string
 */
function batchUpdate($data, $field,$table, $params = [])
{
	if (!is_array($data) || !$field || !is_array($params)) {
		return false;
	}
	$updateArr = array_chunk($data,200,true);
	$res = 0;
	foreach($updateArr as $data) {
		$updates = parseUpdate($data, $field);
		$where = parseParams($params);

		// 获取所有键名为$field列的值，值两边加上单引号，保存在$fields数组中
		// array_column()函数需要PHP5.5.0+，如果小于这个版本，可以自己实现，
		// 参考地址：http://php.net/manual/zh/function.array-column.php#118831
		$fields = array_column($data, $field);
		$fields = implode(',', array_map(function ($value) {
			return "'" . $value . "'";
		}, $fields));

		$sql = sprintf("UPDATE `%s` SET %s WHERE `%s` IN (%s) %s", $table, $updates, $field, $fields, $where);
		$res = \DB::insert($sql);
	}
	return $res;
}

/**
 * 将二维数组转换成CASE WHEN THEN的批量更新条件
 * @param $data array 二维数组
 * @param $field string 列名
 * @return string sql语句
 */
function parseUpdate($data, $field)
{
	$sql = '';
	$keys = array_keys(current($data));
	foreach ($keys as $column) {

		$sql .= sprintf("`%s` = CASE `%s` \n", $column, $field);
		foreach ($data as $line) {
			$sql .= sprintf("WHEN '%s' THEN '%s' \n", $line[$field], $line[$column]);
		}
		$sql .= "END,";
	}

	return rtrim($sql, ',');
}

/**
 * 解析where条件
 * @param $params
 * @return array|string
 */
function parseParams($params)
{
	$where = [];
	foreach ($params as $key => $value) {
		$where[] = sprintf("`%s` = '%s'", $key, $value);
	}

	return $where ? ' AND ' . implode(' AND ', $where) : '';
}


function unsetEmoji($str)
{
	$str = preg_replace_callback(
	'/./u',
	function (array $match) {
	return strlen($match[0]) >= 4 ? '' : $match[0];
	},
	$str);
	$str = str_replace(PHP_EOL, '', $str);
	$str = str_replace('\\x92s', '', $str);
	return $str;
}

//config option的状态：0为显示，1为隐藏。
function getConfigOptionStatus()
{
    return array(0=>'Visible', 1=>"Hidden");
}

//CRM模块可供下拉选择的country
function getCrmCountry()
{
	return array('US','CA','DE','ES','GB','FR','IT','JP','MA');
}

//CRM模块可供下拉选择的type(客户类型)，0默认，1黑名单
function getCrmClientType()
{
    return array(0=>'Default',1=>'Blacklist');
}

//CRM模块可供下拉选择的subscribe(订阅)，0默认，1已订阅
function getCrmSubscribe()
{
    return array(0=>'Default',1=>'Subscribe');
}

//CRM模块可供下拉选择的block(屏蔽)，0默认，1已屏蔽
function getCrmBlock()
{
    return array(0=>'Default',1=>'Block');
}

//CRM模块可供下拉选择的Source
function getCrmFrom()
{
	return array('BOGO','Call','Cashback','Chat','CTG','Email','Facebook','Non-CTG','Purchased','Reveiw','RSG','Others','B2B');
}

//CRM模块可供下拉选择的brand
function getCrmBrand()
{
	return array('DBPOWER','TENKER','KOIOS','SPACEKEY','Mooka','Natrogix','Miropure','NURSAL','DROCON','Runme','OXA','Lyps','SPACEKEYBRANDS','TECBEAN','Babysteps','VIPSUPPORT','AZEUS');
}

//CRM模块可供下拉选择的订单类型
function getCrmOrderType()
{
	return array(0=>'Default Type',1=>'RSG Type');
}

function getStepIdToTags(){
	return array(
				'1'  => 'RSG Join',
				'2'  => 'RSG Request Reject',
				'3'  => 'RSG Submit Paypal',
				'4'  => 'RSG Check Paypal',
				'5'  => 'RSG Submit Purchase',
				'6'  => 'RSG Check Purchase',
				'7'  => 'RSG Submit Review Url',
				'8'  => 'RSG Check Review Url',
				'9'  => 'RSG Completed',
				'10'  => 'Closed',
				'11'  => 'Open Dispute'
			);
}

/*
 * 得到收件箱后缀名跟负责人的一一对应关系
 * 主要用于跑CRM模块来自 site inbox模块的数据
 * 此模块数据的跑到CRM后的负责人根据收件箱的品牌官网对应到某个负责人
 */
function getBrandProcessor()
{
	$data = array(
		'azeus.net' => 166,
		'dbpower.co' => 96,
		'tenker.co' => 96,
		'mykoios.com' => 166,
		'spacekey.net' => 83,
		'mooka.co' => 86,
		'natrogix.com' => 208,
		'miropure.co' => 52,
		'miropure.co.uk' => 52,
		'nursal.co' => 67,
		'drocon.co' => 51,
		'irunme.net' => 51,
		'oxalife.com' => 51,
		'hoverstorm.com' => 51,
		'lypsonline.com' => 68,
		'spacekeybrands.com' => 31,
		'tecbean.com' => 92,
		'hotmail.com' => 31,
		'outlook.com' => 31,
		'contactbabysteps.com' => 52,
		'dbpower' => 96,
		'dbpowerpro.com' => 96,
		'doctorhetzner.net' => 166,
		'funavo.co.jp' => 109,
		'funavopro.com' => 83,
		'tenker-vl.com' => 96,
		'tenkersupport.com' => 96,
		'unicdrone.com' => 51,
		'vipsupport.jp' => 109,
		'volt-cube.com' => 123,
		'claimthegift.com' => 31,
		'koiostec.com' => 166,
		'mookapro.com' => 86,
	);

	return $data;
}

/*
 * 得到所有的countryCode对应的仓库
 */
function getCountryCode()
{
	$arr = array(
		'AE'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'AT'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'AU'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'BE'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'BM'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'BR'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'CA'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'CL'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'CO'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'CR'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'CY'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'CZ'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'DE'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'DK'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'ES'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'FR'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'GB'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'GR'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'GU'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'HK'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'HU'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'ID'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'IE'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'IL'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'IN'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'IS'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'IT'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'JM'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'JP'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'KR'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'KW'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'LU'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'MT'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'MX'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'MY'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'NG'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'NL'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'NO'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'NZ'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'PE'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'PH'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'PL'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'PR'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'PT'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'PW'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'QA'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'RO'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'SA'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'SE'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'SG'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'SK'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'TR'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'TT'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'TW'=>array('store'=>array('HK01-SZ6','HK03-CHK3')),
		'UK'=>array('store'=>array('HK01-SZ6','HK03-CHK3','UK02-UK3')),
		'US'=>array('store'=>array('HK01-SZ6','HK03-CHK3','US02-US2')),
		'ZA'=>array('store'=>array('HK01-SZ6','HK03-CHK3'))
	);
	return $arr;
}
/*
 * 得到countrycode为US的StateOrRegion种类
 * 当countrycode选择US的时候，StateOrRegion为固定的下拉选项
 */
function getStateOrRegionByUS()
{
	$arr = array(
		'AA'=>'AA-APO/FPO:Americas','AE'=>'AE-APO/FPO：Europe,Africa,Cannda,Mideast','AK'=>'AK-Alaska',
		'AL'=>'AL-Alabama','AP'=>'AP-APO/FPO：Asia,Pacific','AR'=>'AR-Arkansas',
		'AZ'=>'AZ-Arizona','CA'=>'CA-California','CO'=>'CO-Colorado',
		'CT'=>'CT-Connecticut','DC'=>'DC-District of Columbia','DE'=>'DE-Delaware',
		'FL'=>'FL-Florida','GA'=>'GA-Georgia','HI'=>'HI-Hawaii',
		'IA'=>'IA-Iowa','ID'=>'ID-Idaho','IL'=>'IL-Illinois',
		'IN'=>'IN-Indiana','KS'=>'KS-Kansas','KY'=>'KY-Kentucky',
		'LA'=>'LA-Louisiana','MA'=>'MA-Massachusetts','MD'=>'MD-Maryland',
		'ME'=>'ME-Maine','MI'=>'MI-Michigan','MN'=>'MN-Minnesota',
		'MO'=>'MO-Missouri','MS'=>'MS-Mississippi','MT'=>'MT-Montana',
		'NC'=>'NC-North Carolina','ND'=>'ND-North Dakota','NE'=>'NE-Nebraska',
		'NH'=>'NH-New Hampshire','NJ'=>'NJ-New Jersey','NM'=>'NM-New Mexico',
		'NV'=>'NV-Nevada','NY'=>'NY-New York','OH'=>'OH-Ohio',
		'OK'=>'OK-Oklahoma','OR'=>'OR-Oregon','PA'=>'PA-Pennsylvania',
		'RI'=>'RI-Rhode Island','SC'=>'SC-South Carolina','SD'=>'SD-South Dakota',
		'TN'=>'TN-Tennessee','TX'=>'TX-Texas','UT'=>'UT-Utah',
		'VA'=>'VA-Virginia','VT'=>'VT-Vermont','WA'=>'WA-Washington',
		'WI'=>'WI-Wisconsin','WV'=>'WV-West Virginia','WY'=>'WY-Wyoming',
	);
	return $arr;
}

/*
 * CTG的channel枚举
 */
function getCtgChannel()
{
	return array(0=>'CTG',1=>'Cashback',2=>'BOGO',3=>'Non-CTG',4=>'CS-Email',5=>'CS-Chat',6=>'CS-Call');
}

/*
 * rsgrequest的channel枚举
 */
function getRsgRequestChannel()
{
	return array(0=>'Web',1=>'CS team',2=>'EDM',3=>'Facebook',4=>'Sales',5=>'Customer Referral');
}

/*
 * Facebook Group的配置内容,用于下拉选择客户对应的Facebook Group组别
 */
function getFacebookGroup()
{
	$arr = array(
		1=>'CTG Premium Product Review Club | Alpha',
		2=>'CTG Premium Product Review Club | Bravo',
		3=>'CTG Premium Product Review Club | Charlie',
		4=>'CTG Premium Product Review Club | Delta',
		5=>'CTG Premium Product Review Club | Echo',
		6=>'CTG Premium Product Review Club | Foxtrot',
		7=>'CTG Premium Product Review Club | Golf',
		8=>'CTG Premium Product Review Club | Hotel',
		9=>'CTG Premium Product Review Club | India',
		10=>'CTG Premium Product Review Club | Juliet',
		11=>'CTG Premium Product Review Club | Kilo',
		12=>'CTG Premium Product Review Club | Lima',
		13=>'CTG Premium Product Review Club | Mike',
		14=>'CTG Premium Product Review Club | November',
		15=>'CTG Premium Product Review Club | Oscar',
		16=>'CTG Premium Product Review Club | Papa',
		17=>'CTG Premium Product Review Club | Quebec',
		18=>'CTG Premium Product Review Club | Romeo',
		19=>'CTG Premium Product Review Club | Sierra',
		20=>'CTG Premium Product Review Club | Tango',
		21=>'CTG Premium Product Review Club | Uniform',
		22=>'CTG Premium Product Review Club | Victor',
		23=>'CTG Premium Product Review Club | Whiskey',
		24=>'CTG Premium Product Review Club | X-ray',
		25=>'CTG Premium Product Review Club | Yankee',
		26=>'CTG Premium Product Review Club | Zulu',
		27=>'CTG Premium Product Review Club | Apple',
		28=>'CTG Premium Product Review Club | Banana',
		29=>'CTG Premium Product Review Club | Cherry',
		30=>'CTG Premium Product Review Club | Date',
		31=>'CTG Premium Product Review Club | Eggplant',
		32=>'CTG Premium Product Review Club | Fig',
		33=>'CTG Premium Product Review Club | Grape',
		34=>'CTG Premium Product Review Club | Honeydew',
		35=>'CTG Premium Product Review Club | Iceberg Lettuce',
		36=>'CTG Premium Product Review Club | Jackfruit',
		37=>'CTG Premium Product Review Club | Kiwi',
		38=>'CTG Premium Product Review Club | Lemon',
		39=>'CTG Premium Product Review Club | Mango',
		40=>'CTG Premium Product Review Club | Nut',
		41=>'CTG Premium Product Review Club | Olive',
		42=>'CTG Premium Product Review Club | Peach',
		43=>'CTG Premium Product Review Club | Quince',
		44=>'CTG Premium Product Review Club | Radish',
		45=>'CTG Premium Product Review Club | Strawberry',
		46=>'CTG Premium Product Review Club | Tomato',
		47=>'CTG Premium Product Review Club | Vanilla',
		48=>'CTG Premium Product Review Club | Watermelon',
		49=>'CTG Premium Product Review Club | Yam',
		50=>'CTG Premium Product Review Club | Zucchini',
	);
	return $arr;
}


function getTaskTypeArr()
{
	return array(1=>'Listing优化',2=>'库存调拨',3=>'星级提升',4=>'邮件跟进',5=>'广告优化','6'=>'帖子排名','99'=>'其他');
}

function getTaskStageArr()
{
	return array(0=>'New',1=>'Opening',2=>'Under Review',3=>'Finished');
}

/*
 * JP，US，EU各个站点所归属的site
 * FBM-FMA的物流周期【JP=10天，EU=12天，US=20天】(fbmfba_days)
 * 上架时效的物流周期【JP=2天，EU=3天，US=3天】(fbmfba_shelfing)
 */
function getSiteArr()
{
	$arr['site'] = [
		'JP' => array('www.amazon.co.jp'),
		'US' => array('www.amazon.com','www.amazon.ca'),
		'EU' => array('www.amazon.co.uk','www.amazon.de','www.amazon.it','www.amazon.es','www.amazon.fr'),
	];
	$arr['fbmfba_days'] = array('JP'=>10,'US'=>20,'EU'=>12);
	$arr['fbmfba_shelfing'] = array('JP'=>2,'US'=>3,'EU'=>3);
	return $arr;
}
/*
 * 得到FBM的良品仓有哪些仓库
 */
function getGoodStoreHouse()
{
	$goodStoreHouse = array('US02_US2','US02_US6','JP02_CJP2','GR02_CGR2','GR04_GR4','UK02_UK3','CZ02_CZ2');
	return $goodStoreHouse;
}

/*
 * 得到站点与站点简写的键值对
 */
function getSiteShort()
{
	$arr = array('www.amazon.co.jp'=>'jp','www.amazon.com'=>'us','www.amazon.ca'=>'ca','www.amazon.co.uk'=>'uk','www.amazon.de'=>'de','www.amazon.it'=>'it','www.amazon.es'=>'es','www.amazon.fr'=>'fr');
	return $arr;
}

/*
 * 得到LabelType的键值对
 */
function getLabelType()
{
	$arr = array(0=>'Active Warranty',1=>'BOGO',2=>'50% Cashback',3=>'up to 100% cashback');
	return $arr;
}
/*
 * 得到调拨申请的审核状态的键值对
 */
function getReplyAduitStatus()
{
	$statusArr = array(0=>'-',1=>'Rejected By BU',2=>'Approved By BU',3=>'Rejected By BG',4=>'Approved By BG',5=>'Rejected By VP',6=>'Approved By VP',7=>'Rejected By plan',8=>'Approved By plan');
	return $statusArr;
}


function getShipRate()
{
	$arr = [
	'US'=>['default'=>'1890'],
	'UK'=>['default'=>'1470'],
	'DE'=>['default'=>'2170'],
	'FR'=>['default'=>'2170'],
	'ES'=>['default'=>'2310'],
	'IT'=>['default'=>'2310'],
	'JP'=>[
		'default'=>'1330',
		'HA0618'=>'2534',
		'HA0697'=>'2534',
		'CS0517'=>'2534',
		'CS0510'=>'2534',
		'CS0548'=>'2534',
		'CS0656'=>'2534'
	],
	'CA'=>['default'=>'2660']
	];
	return $arr;
}

/*
 * 得到帖子状态值的含义跟给的分数
 */
function getPostStatus()
{
	$arr = array(
		1=>array('name'=>'Active','score'=>1),
		2=>array('name'=>'Pushing','score'=>2),
		3=>array('name'=>'Unavailable','score'=>0),
		4=>array('name'=>'Abandoned','score'=>0),
	);
	return $arr;
}

/*
 * 得到帖子类型值的含义跟给的分数
 */
function getPostType()
{
	$arr = array(
		1=>array('name'=>'Main','score'=>1),
		2=>array('name'=>'Secondary','score'=>0.5),
		3=>array('name'=>'Other','score'=>0),
	);
	return $arr;
}
/*
 * 产品的权重排序状态对应值
 */
function getProductOrderStatus()
{
	return array(0=>'默认',1=>'增加权重','-1'=>'降低权重');
}
/*
 * 产品的权重排序状态对应值
 */
function getSkuLevel()
{
	return array('S','A','B','C','D');
}
/*
 * sku状态对应值
 */
function getSkuStatus()
{
	return array('New','Normal','Obsoleted','Replaced','Observing','Accessories','Gift','Packaging');
}