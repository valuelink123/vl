<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RsgProduct;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use App\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
class MrpController extends Controller
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

   	public function list(Request $req)
    {
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		
		$date = (intval(array_get($search,'date'))>0)?intval(array_get($search,'date')):90;
		if ($req->isMethod('GET')) {
			return view('mrp/index', ['date' => $date,'bgs'=>$this->getBgs(),'bus'=>$this->getBus()]);
		}
		//搜索相关
		$searchField = array('bg'=>'a.bg','bu'=>'a.bu','site'=>'a.marketplace_id','sku'=>'a.sku','sku_level'=>'a.status','sku_status'=>'a.skus_status','sku_level'=>'a.status','sap_seller_id'=>'a.sap_seller_id');
		
		$where = $this->getSearchWhereSql($search,$searchField);

		if(array_get($search,'keyword')){
			$where .=" and (a.asin='".array_get($search,'keyword')."' or a.sku='".array_get($search,'keyword')."')";
		}
		$orderby = $this->dtOrderBy($req);
		$sql = $this->getSql($where,$date,$orderby);
		if($req['length'] != '-1'){
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}
		//print_r($sql);die();
		$data = DB::connection('amazon')->select($sql);
		$data = $this->getReturnData(json_decode(json_encode($data),true));
		$recordsTotal = $recordsFiltered = (DB::connection('amazon')->select('SELECT FOUND_ROWS() as count'))[0]->count;
		return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    /*
     * 点击编辑可查看参数和编辑某些参数
     */
    public function edit(Request $request)
    {
		if(!Auth::user()->can(['rsgproducts-show'])) die('Permission denied -- rsgproducts-show');
		$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : '';
		$rule= RsgProduct::where('id',$id)->first()->toArray();

		if(!$rule){
			$request->session()->flash('error_message','Rsg Product not Exists');
			return redirect('rsgproducts');
		}
		$rule['product_summary'] = json_decode(str_replace("'",'"',$rule['product_summary']),TRUE);
		if($rule['product_summary']){
			$rule['product_summary'] = implode("\n",$rule['product_summary']);
		}
		$rule['product_content'] = htmlspecialchars($rule['product_content']);
		return view('rsgproducts/edit',['rule'=>$rule]);
    }
	/*
	 * 编辑页面点击提交，保存可更新的数据
	 */
    public function update(Request $request)
    {
		if(!Auth::user()->can(['rsgproducts-update'])) die('Permission denied -- rsgproducts-update');
		$id = isset($_POST['id']) && $_POST['id'] ? $_POST['id'] : '';

        $rule = RsgProduct::where('id',$id)->first();
		if(!$rule){
			$request->session()->flash('error_message','Rsg Product not Exists');
			return redirect('rsgproducts');
		}


        if(isset($_POST['order_status'])){
			$rule->order_status = $_POST['order_status'];
		}
		if(isset($_POST['order_status'])){
			$rule->order_status = $_POST['order_status'];
		}
		if(isset($_POST['sales_target_reviews'])){
			$rule->sales_target_reviews = intval($_POST['sales_target_reviews']);
			$rule->sales_target_reviews_set = intval($_POST['sales_target_reviews']);
		}

        if ($rule->save()) {
            $request->session()->flash('success_message','Set Rsg Product Success');
            return redirect('rsgproducts');
        } else {
            $request->session()->flash('error_message','Set Rsg Product Failed');
            return redirect()->back()->withInput();
        }
    }

    /*
     * 下载数据
     */
    public function export()
	{
		
    }

	/*
	 * 得到sql查询语句
	 *
	 */
	public function getSql($where,$date=90,$orderby='daily_sales desc')
	{
		if($orderby){
			$orderby = " order by {$orderby} ";
		}else{
			$orderby = " order by daily_sales desc";
		}
		$date_to = date('Y-m-d',strtotime('+'.$date.'days'));
		$date_from = date('Y-m-d');
		$sql = "
        SELECT SQL_CALC_FOUND_ROWS
        	a.*,(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2) as daily_sales,
afn_sellable,afn_reserved,quantity from (select asin,marketplace_id,any_value(sku) as sku,any_value(status) as status,
any_value(sku_status) as sku_status,any_value(sap_seller_id) as sap_seller_id, 
any_value(sap_seller_bg) as bg,any_value(sap_seller_bu) as bu from sap_asin_match_sku group by asin,marketplace_id) as a
left join asins as b on a.asin=b.asin and a.marketplace_id=b.marketplaceid
left join (select asin,marketplace_id,sum(quantity_last) as quantity from asin_sales_plans where date>='$date_from' and date<='$date_to' group by asin,marketplace_id) as c
on a.asin=c.asin and a.marketplace_id=c.marketplace_id
			where 1 = 1 {$where} 
			{$orderby} ";
		return $sql;
	}

	/*
	 * 得到处理后的表格数据
	 */
	public function getReturnData($data) {

		$siteCode = array_flip(getSiteCode());
		$sellers = getUsers('sap_seller');
		foreach ($data as $key => $val) {
			$data[$key]['asin'] = $val['asin'];
			$data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
			$data[$key]['sku'] = $val['sku'];
			$data[$key]['status'] = $val['status'];
			$data[$key]['seller'] = array_get($sellers,$val['sap_seller_id']);
			$data[$key]['daily_sales'] = round($val['daily_sales'],2);
			$data[$key]['quantity'] = $val['quantity'];
			$data[$key]['fba_stock'] = $val['afn_sellable']+$val['afn_reserved'];
			$data[$key]['fba_stock_keep'] = 0;
			$data[$key]['fba_transfer'] = 0;
			$data[$key]['fbm_stock'] = 0;
			$data[$key]['stock_keep'] = 0;
			$data[$key]['sz'] = 0;
			$data[$key]['in_make'] = 0;
			$data[$key]['out_stock'] = 0;
			$data[$key]['out_stock_date'] = 0;
			$data[$key]['unsalable'] = 0;
			$data[$key]['unsalable_date'] = 0;
			$data[$key]['stock_score'] = 0;
			$data[$key]['expected_distribution'] = 0;
			$data[$key]['action'] = '<a class="badge badge-success" href=""><i class="fa fa-hand-o-up"></i></a>';
		}
		return $data;
	}



}