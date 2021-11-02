<?php
/**
 * Created by PhpStorm.
 * Date: 18.10.30
 * Time: 10:05
 */

namespace App\Http\Controllers\Frank;


use App\Accounts;
use App\Classes\SapRfcRequest;
use App\Exceptions\DataInputException;
use App\Models\Ctg;
use App\Models\B1g1;
use App\Models\Cashback;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\TrackLog;
use Illuminate\Support\Facades\Auth;
class CtgController extends Controller {

    use \App\Traits\Mysqli;
    use \App\Traits\DataTables;

    // 不需要登录验证的
    protected static $authExcept = ['import','b1g1import','cashbackimport'];

	/**
     * @throws \App\Traits\MysqliException
     * @throws \App\Traits\DataTablesException
     */
    public function list(Request $req) {
		if(!Auth::user()->can(['ctg-show'])) die('Permission denied -- ctg-show');
        if ($req->isMethod('GET')) {
			
            //目前在职的，而且sap_seller_id不为0
            $userRows = DB::table('users')->where('sap_seller_id', '>', 0)->where('locked', '=',0)->select('id', 'name')->get();
            $selchannel = isset($_REQUEST['channel']) ? $_REQUEST['channel'] : '-1';

            foreach ($userRows as $row) {
                $users[$row->id] = $row->name;
            }
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';

            $bgs = $this->queryFields('SELECT DISTINCT bg FROM asin');
            $bus = $this->queryFields('SELECT DISTINCT bu FROM asin');
            $brands = $this->queryFields('SELECT DISTINCT brand FROM asin');
			$channel = getCtgChannel();

            return view('frank.ctgList', compact('users', 'bgs', 'bus', 'brands','email','channel','selchannel'));
        }


        // query data list

        // 分区条件
        $timeRange = $this->dtTimeRange($req);

        $where = $this->dtWhere(
            $req,
            [
                'processor' => 't2.name',
                'email' => 't1.email',
                'name' => 't1.name',
                'order_id' => 't1.order_id',
                'asins' => 't4.asins',
                'itemCodes' => 't4.itemCodes',
                'itemNames' => 't4.itemNames',
                'sellerskus' => 't4.sellerskus',
                'itemGroups' => 't4.itemGroups',
                'brands' => 't4.brands',
                'bgs' => 't4.bgs',
                'bus' => 't4.bus',
                'phone' => 't1.phone',
                'encrypted_email' => 'encrypted_email'
            ],
            [
                'phone' => 't1.phone',
				'email' => 't1.email',
				'site' => 't3.SalesChannel',
            ],
            [
                // WHERE IN
                'rating' => 't1.rating',
                'processor' => 't1.processor',
                'status' => 't1.status',
                // WHERE FIND_IN_SET
                'crmType' => 's:client.type',
                'bg' => 's:t4.bgs',
                'bu' => 's:t4.bus',
                'brand' => 's:t4.brands',
            ]
        );
        $orderby = $this->dtOrderBy($req);
        $limit = $this->dtLimit($req);

        $where .= $this->getAsinWhere('t4.bgs','t4.bus','t1.processor','ctg-show-all');

        //搜索Review Id的搜索条件   '%"review_id":"12"%'
		$ins = $req->input('search.ins', []);
		if(isset($ins['review_id']) && $ins['review_id']){
			$str = '%"review_id":"'.$ins['review_id'].'"%';
			$where .= " AND t1.steps like '".$str."'";
		}
		//选择的渠道不同，查不同的表，限制不同的条件
		$channel = 0;
		$table = 'ctg';
		$channelKeyVal = getCtgChannel();
		//0=>'CTG',1=>'Cashback',2=>'BOGO',3=>'Non-CTG',4=>'CS-Email',5=>'CS-Chat',6=>'CS-Call'
		if(isset($ins['channel']) && $ins['channel']){
			$channel = $ins['channel'];
		}
		//页面首次加载时，不做内容查询
        if ($channel == -1) {
            $data = [];
            $recordsTotal = $recordsFiltered = 0;
            return compact('data', 'recordsTotal', 'recordsFiltered');
        }
		$channelName = isset($channelKeyVal[$channel]) ? $channelKeyVal[$channel] : 'CTG';
		if($channel==1){
			$table = 'cashback';
		}elseif($channel==2){
			$table = 'b1g1';
		}else{
			$where .= ' and t1.channel = '.$channel;
		}

        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
            t1.created_at,
            t1.name,
            t1.email,
            t1.phone,
            t1.rating,
            t1.commented,
            t1.steps,
            t1.status,
            t1.order_id,
            t2.name AS processor,
            t3.SalesChannel,
            t4.asins,
            t4.itemCodes,
            t4.itemNames,
            t4.sellerskus,
            t4.itemGroups,
            t4.bgs,
            t4.bus,
            t4.brands,
		    facebook_name,
		    facebook_group,
            encrypted_email,
		    t1.review_type,
		    rsg_requests.amazon_order_id as rsg_orderid,
            exception.amazon_order_id as rr_orderid,
		    '{$channelName}' as channel,
		    client.rsg_status as rsg_status,
		    client.rsg_status_explain as rsg_status_explain
        FROM {$table} t1 
        Left join ( 
					select amazon_order_id from rsg_requests group by amazon_order_id 
				) as rsg_requests on rsg_requests.amazon_order_id = t1.order_id 
		LEFT JOIN (
			SELECT amazon_order_id FROM exception GROUP BY amazon_order_id
			) AS exception ON exception.amazon_order_id = t1.order_id
        LEFT JOIN client_info ON client_info.email = t1.email 
        LEFT JOIN client ON client_info.client_id = client.id  
        LEFT JOIN users t2
          ON t2.id = t1.processor
        LEFT JOIN (
          SELECT
            ANY_VALUE(SalesChannel) as SalesChannel,
			ANY_VALUE(MarketPlaceId) as MarketPlaceId,
			ANY_VALUE(SellerId) as SellerId,
			ANY_VALUE(AmazonOrderId) as AmazonOrderId
          FROM ctg_order
            WHERE $timeRange 
            group by AmazonOrderId 
          ) t3
          ON t3.AmazonOrderId = t1.order_id
        LEFT JOIN (
            SELECT
              ANY_VALUE(SellerId) AS SellerId,
		   	  ANY_VALUE(sap_seller_id) as  sap_seller_id,	
              ANY_VALUE(MarketPlaceId) AS MarketPlaceId,
              ANY_VALUE(AmazonOrderId) AS AmazonOrderId,
              GROUP_CONCAT(DISTINCT t4_1.ASIN) AS asins,
              GROUP_CONCAT(DISTINCT t4_1.SellerSKU) AS sellerskus,
              GROUP_CONCAT(DISTINCT fbm_stock.item_name) AS itemNames,
              GROUP_CONCAT(DISTINCT asin.item_no) AS itemCodes,
              GROUP_CONCAT(DISTINCT asin.item_group) AS itemGroups,
              GROUP_CONCAT(DISTINCT asin.bg) AS bgs,
              GROUP_CONCAT(DISTINCT asin.bu) AS bus,
              GROUP_CONCAT(DISTINCT asin.brand) AS brands
            FROM ctg_order_item t4_1
            LEFT JOIN asin
              ON asin.site = t4_1.MarketPlaceSite AND asin.sellersku = t4_1.SellerSKU 
            LEFT JOIN fbm_stock
              ON fbm_stock.item_code = asin.item_no
            WHERE $timeRange
            GROUP BY MarketPlaceId,AmazonOrderId,SellerId
          ) t4
          ON t4.AmazonOrderId = t1.order_id AND t4.MarketPlaceId = t3.MarketPlaceId AND t4.SellerId = t3.SellerId
        WHERE $where
        ORDER BY $orderby
        LIMIT $limit
        ";

        $data = $this->queryRows($sql);
		$fbgroupConfig = getFacebookGroup();
        $rsgStatusArr = getCrmRsgStatusArr();
        foreach($data as $key=>$val){
			$data[$key]['facebook_group'] = isset($fbgroupConfig[$val['facebook_group']]) ? $fbgroupConfig[ $val['facebook_group']] : '';
			if($val['review_type']==1 && $val['rating']<4){//系统给的评论星级，并且是差评，红色底色显示
				$data[$key]['rating'] = '<span class="btn btn-danger btn-xs">'.$val['rating'].'</span>';
			}
			$data[$key]['join_rsg'] = $val['rsg_orderid'] ? 'YES' : 'NO';//是否有参加RSG活动

			$data[$key]['join_rr'] = $val['rr_orderid'] ? 'YES' : 'NO';//是否有创建RR订单
            //点击Batch Send群发邮件时，提取收件人email。
            $data[$key]['email_hidden'] =  ($val['encrypted_email']??$val['email']);

            $explain = isset($rsgStatusArr[$val['rsg_status_explain']]) ? $rsgStatusArr[$val['rsg_status_explain']]['vop'] : $val['rsg_status_explain'];
            if($val['rsg_status']==1) {
                //邮箱后面显示红色圆圈
                $data[$key]['email'] = ($val['encrypted_email']??$val['email']).'<div class="unavailable" title="'.$explain.'"></div>';
            }else{
                //邮箱后面显示绿色圆圈
                $data[$key]['email'] = ($val['encrypted_email']??$val['email']).'<div class="available"></div>';
            }
		}
        $recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');

        return compact('data', 'recordsTotal', 'recordsFiltered');

    }

    public function export(Request $req){
		if(!Auth::user()->can(['ctg-export'])) die('Permission denied -- ctg-export');
        set_time_limit(0);
        $arrayData = array();
        $headArray[] = 'Date';
        $headArray[] = 'Email';
        $headArray[] = 'Customer';
		$headArray[] = 'Order No';
        $headArray[] = 'Item No';
        $headArray[] = 'Item Name';
        $headArray[] = 'Site';
        $headArray[] = 'Asin';
        $headArray[] = 'Seller SKU';
        $headArray[] = 'Brand';
        $headArray[] = 'Item Group';
        $headArray[] = 'Status';
        $headArray[] = 'BG';
        $headArray[] = 'BU';
		$headArray[] = 'Channel';
        $headArray[] = 'Processor';

        $arrayData[] = $headArray;

                // 分区条件
        $timeRange = $this->dtTimeRange($req);
        $where = $this->dtWhere(
            $req,
            [
                'processor' => 't2.name',
                'email' => 't1.email',
                'name' => 't1.name',
                'order_id' => 't1.order_id',
                'asins' => 't4.asins',
                'itemCodes' => 't4.itemCodes',
                'itemNames' => 't4.itemNames',
                'sellerskus' => 't4.sellerskus',
                'itemGroups' => 't4.itemGroups',
                'brands' => 't4.brands',
                'bgs' => 't4.bgs',
                'bus' => 't4.bus',
                'phone' => 't1.phone',
                'encrypted_email' => 'encrypted_email'
            ],
            [
                'phone' => 't1.phone',
				'email' => 't1.email',
				'site' => 't3.SalesChannel',
            ],
            [
                // WHERE IN
                'rating' => 't1.rating',
                'processor' => 't1.processor',
                'status' => 't1.status',
                // WHERE FIND_IN_SET
                'crmType' => 's:client.type',
                'bg' => 's:t4.bgs',
                'bu' => 's:t4.bus',
                'brand' => 's:t4.brands',
            ]
        );
        $where .= $this->getAsinWhere('t4.bgs','t4.bus','t1.processor','ctg-show-all');

        $selectRowJson = $req->input('search.selectRowJson', []);
        $lenSelectedRow = count($selectRowJson);
        $exportType = $req->input('search.exportType', []);
        $whereSelectRow = "";

        if($exportType == 1 || $exportType == 2){
            $whereSelectRow = " AND (";
            for($i = 0; $i < count($selectRowJson); $i++){
                $temp = " (t1.created_at = '{$selectRowJson[$i]['created_at']}' AND t1.order_id = '{$selectRowJson[$i]['order_id']}') ";
                if($i < $lenSelectedRow-1){
                    $temp .= " OR ";
                }
                $whereSelectRow .= $temp;
            }
            $whereSelectRow .= ")";
        }

        $where .= $whereSelectRow;

        //搜索Review Id的搜索条件   '%"review_id":"12"%'
		$ins = $req->input('search.ins', []);
		if(isset($ins['review_id']) && $ins['review_id']){
			$str = '%"review_id":"'.$ins['review_id'].'"%';
			$where .= " AND t1.steps like '".$str."'";
		}
		//选择的渠道不同，查不同的表，限制不同的条件
		$channel = 0;
		$table = 'ctg';
		$channelKeyVal = getCtgChannel();
		//0=>'CTG',1=>'Cashback',2=>'BOGO',3=>'Non-CTG',4=>'CS-Email',5=>'CS-Chat',6=>'CS-Call'
		if(isset($ins['channel']) && $ins['channel']){
			$channel = $ins['channel'];
		}
		//页面首次加载时，不做内容查询
        if ($channel == -1) {
            $data = [];
            $recordsTotal = $recordsFiltered = 0;
            return compact('data', 'recordsTotal', 'recordsFiltered');
        }
		$channelName = isset($channelKeyVal[$channel]) ? $channelKeyVal[$channel] : 'CTG';
		if($channel==1){
			$table = 'cashback';
		}elseif($channel==2){
			$table = 'b1g1';
		}else{
			$where .= ' and t1.channel = '.$channel;
		}

        $sql = "
        SELECT
            t1.created_at,
            t1.name,
            t1.email,
            t1.phone,
            t1.rating,
            t1.commented,
            t1.steps,
            t1.status,
            t1.order_id,
            t2.name AS processor,
            t3.SalesChannel,
            t4.asins,
            t4.itemCodes,
            t4.itemNames,
            t4.sellerskus,
            t4.itemGroups,
            t4.bgs,
            t4.bus,
            t4.brands,
		    facebook_name,
		    facebook_group,
            encrypted_email,
		    t1.review_type,
		    rsg_requests.amazon_order_id as rsg_orderid,
            exception.amazon_order_id as rr_orderid,
		    '{$channelName}' as channel,
		    client.rsg_status as rsg_status,
		    client.rsg_status_explain as rsg_status_explain
        FROM {$table} t1 
        Left join ( 
					select amazon_order_id from rsg_requests group by amazon_order_id 
				) as rsg_requests on rsg_requests.amazon_order_id = t1.order_id 
		LEFT JOIN (
			SELECT amazon_order_id FROM exception GROUP BY amazon_order_id
			) AS exception ON exception.amazon_order_id = t1.order_id
        LEFT JOIN client_info ON client_info.email = t1.email 
        LEFT JOIN client ON client_info.client_id = client.id  
        LEFT JOIN users t2
          ON t2.id = t1.processor
        LEFT JOIN (
          SELECT
            ANY_VALUE(SalesChannel) as SalesChannel,
			ANY_VALUE(MarketPlaceId) as MarketPlaceId,
			ANY_VALUE(SellerId) as SellerId,
			ANY_VALUE(AmazonOrderId) as AmazonOrderId
          FROM ctg_order
            WHERE $timeRange 
            group by AmazonOrderId 
          ) t3
          ON t3.AmazonOrderId = t1.order_id
        LEFT JOIN (
            SELECT
              ANY_VALUE(SellerId) AS SellerId,
		   	  ANY_VALUE(sap_seller_id) as  sap_seller_id,	
              ANY_VALUE(MarketPlaceId) AS MarketPlaceId,
              ANY_VALUE(AmazonOrderId) AS AmazonOrderId,
              GROUP_CONCAT(DISTINCT t4_1.ASIN) AS asins,
              GROUP_CONCAT(DISTINCT t4_1.SellerSKU) AS sellerskus,
              GROUP_CONCAT(DISTINCT fbm_stock.item_name) AS itemNames,
              GROUP_CONCAT(DISTINCT asin.item_no) AS itemCodes,
              GROUP_CONCAT(DISTINCT asin.item_group) AS itemGroups,
              GROUP_CONCAT(DISTINCT asin.bg) AS bgs,
              GROUP_CONCAT(DISTINCT asin.bu) AS bus,
              GROUP_CONCAT(DISTINCT asin.brand) AS brands
            FROM ctg_order_item t4_1
            LEFT JOIN asin
              ON asin.site = t4_1.MarketPlaceSite AND asin.sellersku = t4_1.SellerSKU 
            LEFT JOIN fbm_stock
              ON fbm_stock.item_code = asin.item_no
            WHERE $timeRange
            GROUP BY MarketPlaceId,AmazonOrderId,SellerId
          ) t4
          ON t4.AmazonOrderId = t1.order_id AND t4.MarketPlaceId = t3.MarketPlaceId AND t4.SellerId = t3.SellerId
        WHERE $where
        ORDER BY created_at desc;
        ";

        $data = $this->queryRows($sql);
        foreach ($data as $key=>$val){
            $arrayData[] = array(
                substr($val['created_at'], 0, 10),
                ($val['encrypted_email']??$val['email']),
                $val['name'],
				$val['order_id'],
                $val['itemCodes'],
                $val['itemNames'],
                $val['SalesChannel'],
                $val['asins'],
                $val['sellerskus'],
                $val['brands'],
                $val['itemGroups'],
                $val['status'],
                $val['bgs'],
                $val['bus'],
				$val['channel'],
                $val['processor'],
            );
        }

        if($arrayData){
            $spreadsheet = new Spreadsheet();

            $spreadsheet->getActiveSheet()
                ->fromArray(
                    $arrayData,  // The data to set
                    NULL,        // Array values with this value will not be set
                    'A1'         // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
                );
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
            header('Content-Disposition: attachment;filename="Export_CTG_'.$channelName.'.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            ob_start();
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $xlsData = ob_get_contents();
            ob_end_clean();

            $response = array(
                'op' => 'ok',
                'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
            );

            die(json_encode($response));
        }
    }
    
    public function batchAssignTask(Request $req) {
		if(!Auth::user()->can(['ctg-update'])) die('Permission denied -- ctg-update');
        if (empty($req->input('ctgRows'))) return [true, ''];

		$channelKeyVal = getCtgChannel();
		$channel = isset($_REQUEST['channel']) ? $_REQUEST['channel'] : 0;

        $processor = (int)$req->input('processor');

        $user = User::findOrFail($processor);

        if($channel==1){
			Cashback::where(function ($where) use ($req) {
				foreach ($req->input('ctgRows') as $row) {
					// WHERE GROUP，传二维数组就可以
					$where->orWhere([
						['created_at', $row[0]],
						['order_id', $row[1]],
					]);
				}
			})->update(compact('processor'));
		}elseif($channel==2){
			B1g1::where(function ($where) use ($req) {
				foreach ($req->input('ctgRows') as $row) {
					// WHERE GROUP，传二维数组就可以
					$where->orWhere([
						['created_at', $row[0]],
						['order_id', $row[1]],
					]);
				}
			})->update(compact('processor'));
		}else{
			Ctg::where(function ($where) use ($req) {
				foreach ($req->input('ctgRows') as $row) {
					// WHERE GROUP，传二维数组就可以
					$where->orWhere([
						['created_at', $row[0]],
						['order_id', $row[1]],
					]);
				}
			})->where('channel',$channel)->update(compact('processor'));
		}

        //修改processor后，相应地更新CRM client表里的processor
        $emails = array();
        foreach ($req->input('ctgRows') as $row) {
            $emails[] = $row[2];
        }
        $client_ids = DB::table('client_info')->whereIn('email', $emails)->pluck('client_id')->unique();
        DB::table('client')->whereIn('id', $client_ids)->update(['processor' => $processor,'updated_at'=>date('Y-m-d H:i:s')]);


        // foreach ($req->input('order_ids') as $order_id) {
        //     // 干掉 id 字段，使用表分区
        //     // 要求 select 中包含主键，否则无法保存
        //     $row = Ctg::select('id')->where('order_id', $order_id)->first();
        //     $row->processor = $processor;
        //     $row->save();
        // }

        return [true, $user->name];
    }

    /**
     * @throws DataInputException
     * CTG点击process出现的页面操作
     */
    public function process(Request $req) {
		$channel = isset($_REQUEST['channel']) ? $_REQUEST['channel'] : 0;
		if($channel==1 || $channel==2){
			$wheres = [
				['created_at', $req->input('created_at')],
				['order_id', $req->input('order_id')],
			];

			if($channel==1){
				$ctgRow = Cashback::selectRaw('*')->where($wheres)->limit(1)->first();
			}else{
				$ctgRow = B1g1::selectRaw('*')->where($wheres)->limit(1)->first();
			}
		}else{
			$wheres = [
				['created_at', $req->input('created_at')],
				['order_id', $req->input('order_id')],
				['channel',$channel]
			];

			$ctgRow = Ctg::selectRaw('*')->where($wheres)->limit(1)->first();
		}


        if (empty($ctgRow)) throw new DataInputException('ctg not found');

        if ($req->isMethod('GET')) {
			if(!Auth::user()->can(['ctg-show'])) die('Permission denied -- ctg-show');
            $sap = new SapRfcRequest();

            $order = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $req->input('order_id')]));

            $order['SellerName'] = Accounts::where('account_sellerid', $order['SellerId'])->first()->account_name ?? 'No Match';


            


            //目前在职的，而且sap_seller_id不为0
            $userRows = DB::table('users')->where('sap_seller_id', '>', 0)->where('locked', '=',0)->select('id', 'name')->get();
            foreach ($userRows as $row) {
                $users[$row->id] = $row->name;
            }

            //得到跟进记录(toArray转换成数组)
			$trackLogData = array();
			if(isset($ctgRow['nonctg_id'])){
				$trackLogData = TrackLog::where('type',0)->where('record_id',$ctgRow['nonctg_id'])->orderBy('created_at', 'desc')->get()->toArray();
				foreach($trackLogData as $k=>$v){
					$trackLogData[$k]['note'] = nl2br($v['note']);
				}
			}
			//查询该邮箱是否存在于client_info中，查出需要显示的facebook_name和facebook_group
			$clientInfo = DB::table('client_info')->where('email',$ctgRow['email'])->get(array('facebook_name','facebook_group','encrypted_email'))->first();

			if($clientInfo){
				$fbgroupConfig = getFacebookGroup();
				$steps = json_decode($ctgRow['steps'],true);
				$steps['facebook_name'] = $clientInfo->facebook_name;
				$steps['facebook_group'] = isset($fbgroupConfig[ $clientInfo->facebook_group]) ? $fbgroupConfig[ $clientInfo->facebook_group] : '';
				$ctgRow['steps'] = json_encode($steps);
				$emails = DB::table('sendbox')->where('to_address', $ctgRow['email'])->orderBy('date', 'desc')->get(['*',DB::RAW('\''.$clientInfo->encrypted_email.'\' as to_address')]);
			}else{
				$emails = DB::table('sendbox')->where('to_address', $ctgRow['email'])->orderBy('date', 'desc')->get(['*']);
			}

            $emails = json_decode(json_encode($emails), true); // todo
            $ctgRow['email'] = empty($clientInfo)?$ctgRow['email']:$clientInfo->encrypted_email;

            return view('frank.ctgProcess', compact('ctgRow', 'users', 'trackLogData','order', 'emails'));

        }

		if(!Auth::user()->can(['ctg-update'])) die('Permission denied -- ctg-update');
        // Update

        $updates = [];

        if ($req->has('processor')) {
            $updates['processor'] = (int)$req->input('processor');
        }


		$updateClient = array();//新添加facebook_group和facebook_name
        if ($req->has('steps')) {
            $updates['status'] = $req->input('status');
            $updates['commented'] = $req->input('commented');
            if($updates['commented']){
                $updates['processor'] = Auth::user()->id;
            }
			$steps = $req->input('steps');
			if(isset($steps['facebook_group']) && $steps['facebook_group']){
				$updateClient['facebook_group'] = (int)$steps['facebook_group'];
				unset($steps['facebook_group']);
			}
			if(isset($steps['facebook_name']) && $steps['facebook_name']){
				$updateClient['facebook_name'] = $steps['facebook_name'];
				unset($steps['facebook_name']);
			}
            $updates['steps'] = json_encode($steps);
        }

		//查client_info表中是否有此客户的数据，如若有就更新facebook_name和facebook_group字段数据，如若没有就插入客户信息数据到client和client_info表
		if($updateClient){
			$ctgRow['from'] = 'CTG';
			updateCrm($ctgRow,$updateClient);
		}

        $ctgRow->where($wheres)->update($updates);

        return [true];
    }

    /*
     * 添加CTG数据（点击add进入到ctg添加页面）
     */
	public function create(Request $req)
	{
		if(!Auth::user()->can(['ctg-add'])) die('Permission denied -- ctg-add');
		$channel = getCtgChannel();
        $emails = [];
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
        if($id != ''){
            $emails = DB::table('client_info')->where('client_id', $id)->pluck('encrypted_email');
        }

		return view('frank/ctgAdd', compact(['channel','emails']));
	}
	/*
	 * 添加ctg数据操作
	 */
	public function store(Request $req)
	{

		if(!Auth::user()->can(['ctg-add'])) die('Permission denied -- ctg-add');
        if(isBlacklistEmail($req->get('email'))) throw new \Exception('Blacklist email');
		$data['name'] = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		$data['email'] = array_search($req->get('email'),getEmailToEncryptedEmail())?array_search($req->get('email'),getEmailToEncryptedEmail()):$req->get('email');
		$data['note'] = isset($_REQUEST['note']) ? $_REQUEST['note'] : '';
		$channel= isset($_REQUEST['channel']) ? $_REQUEST['channel'] : '';
		$data['order_id'] = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
		$data['processor'] = Auth::user()->id;

        $updates = [];
        $review_id = isset($_REQUEST['review_id']) ? $_REQUEST['review_id'] : '';
        $updates['commented'] = 1;
        $updates['status'] = 'Confirm Review';
        $steps_array = array('commented'=>"1", 'review_id'=>$review_id, 'shipment_id'=>'', 'facebook_name'=>'','facebook_group'=>'','track_notes'=>[]);
        $updates['steps'] = json_encode($steps_array);

		$res = 0;
		$msg = '';
		try{
			if($channel==1){
				$res = Cashback::add($data);
                $ctgRow = Cashback::selectRaw('*')->orderBy('created_at','desc')->limit(1)->first();
			}elseif($channel==2){
				$res = B1g1::add($data);
                $ctgRow = B1g1::selectRaw('*')->orderBy('created_at','desc')->limit(1)->first();
			}else{
				$data['channel'] = $channel;
				$res = Ctg::add($data);
                $ctgRow = Ctg::selectRaw('*')->orderBy('created_at','desc')->limit(1)->first();
            }

            $wheres = [
                ['created_at', $ctgRow['created_at']],
                ['order_id', $ctgRow['order_id']],
            ];
            $ctgRow->where($wheres)->update($updates);

		} catch (\Exception $e) {
			$msg = str_replace("For help, please mail to support@claimthegift.com", "", $e->getMessage());
		}

		if ($res) {
			return redirect('ctg/list?channel='.$channel);
		} else {
			$req->session()->flash('error_message',$msg);
			return redirect()->back()->withInput();
		}
	}



    /**
     * 提交 CTG 数据
     * 由 claimthegift.com 调用
     * 加密方式及密码都是写好的
     *
     * @throws \App\Exceptions\HypocriteException
     */
    public function import(Request $req) {

        $binStr = $req->getContent();

        $json = openssl_decrypt($binStr, 'AES-256-CFB', 'frank-is-ok', OPENSSL_RAW_DATA, 'mnoefpaghijbcdkl');

        Ctg::add(json_decode($json, true));

        return [true];
    }
	
	public function b1g1import(Request $req) {

        $binStr = $req->getContent();

        $json = openssl_decrypt($binStr, 'AES-256-CFB', 'frank-is-ok', OPENSSL_RAW_DATA, 'mnoefpaghijbcdkl');

        B1g1::add(json_decode($json, true));

        return [true];
    }
	
	public function cashbackimport(Request $req) {
        $binStr = $req->getContent();
        $json = openssl_decrypt($binStr, 'AES-256-CFB', 'frank-is-ok', OPENSSL_RAW_DATA, 'mnoefpaghijbcdkl');

        Cashback::add(json_decode($json, true));

        return [true];
    }

}
