<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReturnAnalysisController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;
	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}

	/**
	 * Show the application dashboard
	 */
	public function returnAnalysis()
	{
		if($_POST){
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
			//搜索条件如下：from_date,to_date
			$where = " where return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			$sql = "select concat(reason,'~',ta.`status`,'~',`condition`) as rsc,tb.sku,sum(quantity) as quantity 
				from(
					select t1.seller_sku,t1.asin,reason,`status`,detailed_disposition as `condition`,mws_seller_id,mws_marketplaceid,quantity
					from amazon_returns as t1
					left join seller_accounts as t2 on t1.seller_account_id=t2.id
				    {$where}
				) as ta
				left join sap_asin_match_sku as tb on (ta.asin=tb.asin and ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.mws_marketplaceid=tb.marketplace_id)
				group by reason,ta.`status`,`condition`,tb.sku";

			if($req['length'] != '-1'){//等于-1时为查看全部的数据
				$limit = $this->dtLimit($req);
				$sql .= " LIMIT {$limit} ";
			}

			$datas = DB::connection('amazon')->select($sql);
			$data = json_decode(json_encode($datas),true);
			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
			$accounts = $this->getAccountInfo();//得到账号机的信息
			foreach($data as $key=>$val) {
				$data[$key]['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
				$data[$key]['date'] = 'Return:'.$val['return_date'];
			}
			return compact('data', 'recordsTotal', 'recordsFiltered');
		}
		$data['fromDate'] = date('Y-m-d', time() - 2 * 86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
		return view('analysis/return', ['data' => $data]);
	}

	//asin维度分析
	public function asinAnalysis(Request $req)
	{
		if($_POST){
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
			//搜索条件如下：from_date,to_date
			$where_return = " return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			$where_refund = " where posted_date >= '".$search['from_date']." 00:00:00' and posted_date <= '".$search['to_date']." 23:59:59'";

			if(isset($search['account']) && $search['account']){
				$where_refund.= " and t1.current_seller_account_id in (".$search['account'].")";
			}
			$where = ' where 1 = 1 ';
			if(isset($search['asin']) && $search['asin']){
				$where.= " and tb.asin = '".$search['asin']."'";
			}

			$sql="select SQL_CALC_FOUND_ROWS tb.asin,ta.seller_account_id,sum(quantity_shipped) as refund_quantity,sum(tc.quantity) as return_quantity
				from (
						select amazon_order_id,current_seller_account_id as seller_account_id,current_marketplace_id as marketplace_id,seller_sku,
						any_value(quantity_shipped) as quantity_shipped,any_value(mws_seller_id) as mws_seller_id
						from finances_refund_events as t1 
						left join seller_accounts as t2 on t1.current_seller_account_id=t2.id 
						{$where_refund}
						group by amazon_order_id,seller_sku,current_seller_account_id,current_marketplace_id
				) as ta 
				left join sap_asin_match_sku as tb on (ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.marketplace_id=tb.marketplace_id)
				left join amazon_returns as tc on {$where_return} and  tb.asin=tc.asin and ta.seller_account_id=tc.seller_account_id 
				{$where}
				group by tb.asin,ta.seller_account_id";

			if($req['length'] != '-1'){//等于-1时为查看全部的数据
				$limit = $this->dtLimit($req);
				$sql .= " LIMIT {$limit} ";
			}

			$datas = DB::connection('amazon')->select($sql);
			$data = json_decode(json_encode($datas),true);
			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
			$accounts = $this->getAccountInfo();//得到账号机的信息
			foreach($data as $key=>$val) {
				$data[$key]['refund_quantity'] = $val['refund_quantity']>0 ? $val['refund_quantity'] : 0;
				$data[$key]['return_quantity'] = $val['return_quantity']>0 ? $val['return_quantity'] : 0;
				$data[$key]['account'] = isset($accounts[$val['seller_account_id']]) ? $accounts[$val['seller_account_id']]['label'] : $val['seller_account_id'];
			}
			return compact('data', 'recordsTotal', 'recordsFiltered');
		}
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['fromDate'] = date('Y-m-d', time() - 2 * 86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
		return view('analysis/asin', ['data' => $data]);
	}

	//sku维度分析
	public function skuAnalysis(Request $req)
	{
		if($_POST){
			$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
			$search = $this->getSearchData(explode('&',$search));
			//搜索条件如下：from_date,to_date
			$where_return = " where return_date >= '".$search['from_date']." 00:00:00' and return_date <= '".$search['to_date']." 23:59:59'";
			$where_refund = " where posted_date >= '".$search['from_date']." 00:00:00' and posted_date <= '".$search['to_date']." 23:59:59'";

			if(isset($search['account']) && $search['account']){
				$where_refund.= " and t1.current_seller_account_id in (".$search['account'].")";
			}

			$where = ' where 1 = 1 ';
			if(isset($search['sku']) && $search['sku']){
				$where.= " and tb.sku = '".$search['sku']."'";
			}

			$sql="select SQL_CALC_FOUND_ROWS tb.sku,sum(quantity_shipped) as refund_quantity,sum(tc.quantity)  as return_quantity
				from (
						select amazon_order_id,current_seller_account_id as seller_account_id,current_marketplace_id as marketplace_id,seller_sku,
						any_value(quantity_shipped) as quantity_shipped,any_value(mws_seller_id) as mws_seller_id
						from finances_refund_events as t1 
						left join seller_accounts as t2 on t1.current_seller_account_id=t2.id 
						{$where_refund}
						group by amazon_order_id,seller_sku,current_seller_account_id,current_marketplace_id
				) as ta 
				left join sap_asin_match_sku as tb on (ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.marketplace_id=tb.marketplace_id)
				left join (
						select tb.sku,sum(quantity) as quantity 
						from(
							select t1.seller_sku,t1.asin,reason,`status`,detailed_disposition as `condition`,mws_seller_id,mws_marketplaceid,quantity
							from amazon_returns as t1
							left join seller_accounts as t2 on t1.seller_account_id=t2.id
							{$where_return}
						) as ta
						left join sap_asin_match_sku as tb on (ta.asin=tb.asin and ta.seller_sku=tb.seller_sku and ta.mws_seller_id=tb.seller_id and ta.mws_marketplaceid=tb.marketplace_id)
						group by tb.sku
				) as tc on tc.sku=tb.sku 
				{$where}
				group by tb.sku";

			if($req['length'] != '-1'){//等于-1时为查看全部的数据
				$limit = $this->dtLimit($req);
				$sql .= " LIMIT {$limit} ";
			}

			$datas = DB::connection('amazon')->select($sql);
			$data = json_decode(json_encode($datas),true);
			$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
			$accounts = $this->getAccountInfo();//得到账号机的信息
			foreach($data as $key=>$val) {
				$data[$key]['return_quantity'] = $val['return_quantity']>0 ? $val['return_quantity'] : 0;
				$data[$key]['refund_quantity'] = $val['refund_quantity']>0 ? $val['refund_quantity'] : 0;

			}
			return compact('data', 'recordsTotal', 'recordsFiltered');
		}
		$data['account'] = $this->getAccountInfo();//得到账号机的信息
		$data['fromDate'] = date('Y-m-d', time() - 2 * 86400);//开始日期,默认查最近三天的数据
		$data['toDate'] = date('Y-m-d');//结束日期
		return view('analysis/sku', ['data' => $data]);
	}
}