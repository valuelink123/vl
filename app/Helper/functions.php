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

function getSapNumber($str,$decimal){
	if( substr ($str, -1) == '-' ){
		return '-'.round($str,$decimal);
	}else{
		return round($str,$decimal);
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
    return array(0=>'Not Followed Up',1=>'Willing',2=>'Unwilling',3=>'No Reply');
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

//CRM模块可供下拉选择的country
function getCrmCountry()
{
	return array('US','CA','DE','ES','GB','FR','IT','JP','MA');
}

//CRM模块可供下拉选择的Source
function getCrmFrom()
{
	return array('BOGO','Call','Cashback','Chat','CTG','Email','Facebook','Non-CTG','Purchased','Reveiw','RSG','Others');
}

//CRM模块可供下拉选择的brand
function getCrmBrand()
{
	return array('DBPOWER','TENKER','KOIOS','SPACEKEY','Mooka','Natrogix','Miropure','NURSAL','DROCON','Runme','OXA','Lyps','SPACEKEYBRANDS','TECBEAN','Babysteps','VIPSUPPORT');
}

//CRM模块可供下拉选择的订单类型
function getCrmOrderType()
{
	return array(0=>'Default Type',1=>'RSG Type');
}