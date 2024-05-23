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
		$date_from = date('Y-m-d',strtotime('-90 days'));
		$date_to = date('Y-m-d',strtotime('-60 days'));
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

				$linkage1 = isset($categoryData[$val['linkage1']]) ? $categoryData[$val['linkage1']] : '';
				$linkage2 = isset($categoryData[$val['linkage2']]) ? $categoryData[$val['linkage2']] : '';
				$linkage3 = isset($categoryData[$val['linkage3']]) ? $categoryData[$val['linkage3']] : '';
				$data[$key]['question_type'] = $linkage1.'/'.$linkage2.'/'.$linkage3;
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
		if(array_get($ins,'bg') || array_get($ins,'bu')){
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
		
		$sql = "select SQL_CALC_FOUND_ROWS t1.id as id,t1.created_at as date,name,t1.email as email,phone,country,`from`,name,gender,facebook_name,amazon_order_id,brand,review,linkage1,linkage2,linkage3,sku,asin,item_no,item_group
			FROM cs_crm as t1 
		  	left join cs_crm_item on t1.id = cs_crm_item.cscrm_id 
			where $where 
			group by t1.id 
			order by id desc";
		// echo $sql;exit;
		return $sql;
	}
	
	
	//导出数据
	public function export(Request $request)
	{
		set_time_limit(0);
		$date_from = $_GET['date_from'];
		$date_to = $_GET['date_to'];
		$where = " t1.created_at >= '".$date_from." 00:00:00' and t1.created_at <= '".$date_to." 23:59:59'";
		$sql = "select t1.id as id,t1.created_at as date,name,t1.email as email,phone,country,`from`,name,gender,facebook_name,amazon_order_id,brand,review,linkage1,linkage2,linkage3,sku,asin,item_no,item_group
			FROM cs_crm as t1 
		  	left join cs_crm_item on t1.id = cs_crm_item.cscrm_id 
			where $where 
			group by t1.id 
			order by id desc";
		// echo $sql;exit;
		$data = DB::connection('cs')->select($sql);
		$categoryData = $this->getCategoryType();
		$arrayData = array();
		$headArray = array('ID','Date','Email','Name','Phone','Country','From','Gender','Facebook','OrderId','Brand','Review','Question','Sku','Asin','ItemNo','ItemGroup');
		$arrayData[] = $headArray;
		if($data){
			$data = json_decode(json_encode($data),true);
			
			foreach($data as $key=>$val){
				$linkage1 = isset($categoryData[$val['linkage1']]) ? $categoryData[$val['linkage1']] : '';
				$linkage2 = isset($categoryData[$val['linkage2']]) ? $categoryData[$val['linkage2']] : '';
				$linkage3 = isset($categoryData[$val['linkage3']]) ? $categoryData[$val['linkage3']] : '';
				$question = $linkage1.'/'.$linkage2.'/'.$linkage3;
				$arrayData[] = [
					$val['id'],
					$val['date'],
					$val['email'],
					$val['name'],
					$val['phone'],
					$val['country'],
					$val['from'],
					$val['gender'],
					$val['facebook_name'],
					$val['amazon_order_id'],
					$val['brand'],
					$val['review'],
					$question,
					$val['sku'],
					$val['asin'],
					$val['item_no'],
					$val['item_group'],
				];
		
			}
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
            header('Content-Disposition: attachment;filename="Export_CSCRM.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
	}

}