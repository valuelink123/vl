<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use App\Classes\SapRfcRequest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;
use App\Accounts;
use App\User;
use App\ConfigOption;
use App\Category;
use App\Models\TrackLog;
use App\RsgRequest;

class CscrmController extends Controller
{
	use \App\Traits\Mysqli;
	use \App\Traits\DataTables;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */

	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		if(!Auth::user()->can(['cs-crm-show'])) die('Permission denied -- cs-crm-show');
//		$users = User::getUsers();
		$bgs = $this->queryFields('SELECT DISTINCT bg FROM asin');
		$bus = $this->queryFields('SELECT DISTINCT bu FROM asin');
		//获取country,from,brand
		$countrys = getCountry();
		$froms = getFrom();
		$brands = getBrand();
		$date_from=date('Y-m-d',strtotime('-30 days'));
		$date_to=date('Y-m-d');
		$categoryData = $this->getCategoryType();
		return view('cscrm/index',['date_from'=>$date_from,'date_to'=>$date_to,'bgs'=>$bgs,'bus'=>$bus,'countrys'=>$countrys,'froms'=>$froms,'brands'=>$brands,'categoryData'=>$categoryData]);
	}

	//ajax获取列表数据
	public function get(Request $request)
	{
		$sql = $this->getCscrmSql($request);
		$limit = $this->dtLimit($request);
		$sql .= ' LIMIT '.$limit;
		$data = DB::connection('cs')->select($sql);
		$totalData = DB::connection('cs')->select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $totalData[0]->total;

		//得到question_type数据的id对应的类型名称
		//category_name
		$categoryData = $this->getCategoryType();
		if($data){
			$data = json_decode(json_encode($data),true);
			foreach($data as $key=>$val){
				$data[$key]['email_hidden'] = $data[$key]['encrypted_email'] = $val['email'];
				$item = DB::connection('cs')->table('cs_crm_item')->where('cscrm_id',$val['id'])->where('email',$val['email'])->get();
				$data[$key]['amazon_order_id'] = $data[$key]['brand'] = $data[$key]['review'] = $data[$key]['status'] = $data[$key]['sku'] = $data[$key]['asin'] = $data[$key]['item_no'] = $data[$key]['item_group'] = $data[$key]['question_type'] = $data[$key]['bg'] = $data[$key]['bu'] = '';
				foreach($item as $item_value){
					$data[$key]['amazon_order_id'] .= $item_value->amazon_order_id.'<br>';
					$data[$key]['brand'] .= $item_value->brand.'<br>';
					$data[$key]['review'] .= $item_value->review.'<br>';
					$data[$key]['status'] .= $item_value->status.'<br>';
					$data[$key]['sku'] .= $item_value->sku.'<br>';
					$data[$key]['asin'] .= $item_value->asin.'<br>';
					$data[$key]['item_no'] .= $item_value->item_no.'<br>';
					$data[$key]['item_group'] .= $item_value->item_group.'<br>';
					$linkage1 = isset($categoryData[$item_value->linkage1]) ? $categoryData[$item_value->linkage1] : '';
					$linkage2 = isset($categoryData[$item_value->linkage2]) ? $categoryData[$item_value->linkage2] : '';
					$linkage3 = isset($categoryData[$item_value->linkage3]) ? $categoryData[$item_value->linkage3] : '';
					$data[$key]['question_type'] .= $linkage1.'/'.$linkage2.'/'.$linkage3.'<br>';
					//查隐藏邮箱
					$encrypted_email = DB::table('client_info')->where('email',$val['email'])->first();
					if($encrypted_email){
						$encrypted_email = $encrypted_email->encrypted_email;
						$data[$key]['encrypted_email'] = $encrypted_email;
					}
					//查bgbu
					if($item_value->asin){
						$asinData = DB::table('asin')->where('asin',$item_value->asin)->first();
						if($asinData){
							$data[$key]['bg'] .= $asinData->bg.'<br>';
							$data[$key]['bu'] .= $asinData->bu.'<br>';
						}
					}

				}
			}
		}
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	得到列表搜索的sql语句，导出和列表共用一个sql语句,列表就是再加上限制的条数
	 */
	public function getCscrmSql($request)
	{
		$where = $this->dtWhere(
			$request,
			[
//				 'email' => 't1.email',
				// 'name' => 'c.name',
				// 'phone' => 'c.phone',
//				 'amazon_order_id' => 'amazon_order_id',
			],
			[
				'email' => 't1.email',
				'amazon_order_id' => 'amazon_order_id',
				'item_group' => 'item_group',
				'item_no' => 'item_no',
				'asin' => 'asin',
			],
			[
				// WHERE IN
//				'processor' => 't1.processor',
				 'from' => '`from`',
				 'brand' => 'brand',
				 'country' => 'country',
				// WHERE FIND_IN_SET
				'linkage1' => 'linkage1',
//				'bg' => 's:b.bg',
//				'bu' => 's:b.bu',
			],
			'created_at'
		);

		$ins = $request->input('search.ins', []);
		if($ins['bg'] || $ins['bu']){
			$asins = DB::table('asin');
			if($ins['bg']){
				$asins = $asins->whereIn('bg',$ins['bg']);
			}
			if($ins['bu']){
				$asins = $asins->whereIn('bu',$ins['bu']);
			}
			$asins = $asins->get();
			$asin_str = '';
			foreach($asins as $val){
				$asin_str .= "'".$val->asin."',";
			}
			$asin_str = '('.rtrim($asin_str,',').')';
			$where .= " and asin in ".$asin_str;
		}
		
		$sql = "select SQL_CALC_FOUND_ROWS t1.id as id,t1.created_at as date,name,t1.email as email,phone,country,`from`,name,gender,facebook_name 
			FROM cs_crm as t1 
		  	left join cs_crm_item on t1.id = cs_crm_item.cscrm_id 
			where $where 
			group by t1.id 
			order by id desc";
		// echo $sql;exit;
		return $sql;
	}

}