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
class RsgproductsController extends Controller
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
		if(!Auth::user()->can(['rsgproducts-show'])) die('Permission denied -- rsgproducts-show');
		$todayDate = $this->getDefaultDate(date('Y-m-d'));
		if ($req->isMethod('GET')) {
			return view('rsgproducts/index', ['date' => $todayDate,'bgs'=>$this->getBgs(),'bus'=>$this->getBus()]);
		}
		//搜索相关
		$searchField = array('date'=>'rsg_products.created_at','asin'=>'rsg_products.asin','bg'=>'asin.bg','bu'=>'asin.bu','site'=>'rsg_products.site','item_no'=>'asin.item_no','post_type'=>'rsg_products.post_type','post_status'=>'rsg_products.post_status','order_status'=>'rsg_products.order_status','sku_level'=>'rsg_products.sku_level','sku_status'=>'skus_status.status');
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$leftskus = 0;//$leftskus为是否关联查询skus_status表的开关，因为关联查询后速度就变慢
		if($search['sku_status']){
			$leftskus = 1;
		}
		$where = $where_product = $this->getSearchWhereSql($search,$searchField);

		$date = $search['date'];
		$orderby = $this->dtOrderBy($req);
		$sql = $this->getSql($leftskus,$where,$where_product,$date,$orderby);

		if($req['length'] != '-1'){
			$limit = $this->dtLimit($req);
			$sql .= " LIMIT {$limit} ";
		}

		$data = $this->queryRows($sql);
		$data = $this->getReturnData($leftskus,$data,$date,$todayDate);

		$recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');
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
		if(!Auth::user()->can(['rsgproducts-export'])) die('Permission denied -- rsgproducts export');
		$todayDate = date('Y-m-d');
		$date = $_REQUEST['date'];
		$where = " and created_at = '".$date."' ";

		$sql = $this->getSql(0,$where,$where,$date);

		$data = $this->queryRows($sql);
		$data = $this->getReturnData(0,$data,$date,$todayDate);
		$arrayData = array();
		$headArray = array('Rank','Score','Weight Status','Product','Site','Asin','Type','Status','Item No','Level','SKU Status','Rating','Reviews','BG','BU','Seller','Unfinished','Target','Achieved','Task');
		$arrayData[] = $headArray;
		foreach ($data as $key=>$val){
			$arrayData[] = array(
				$val['rank'],
				$val['score'],
				$val['order_status'],
				$val['img'],
				$val['site'],
				$val['basic_asin'],
				$val['type'],
				$val['status'],
				$val['item_no'],
				$val['sku_level'],
				$val['sku_status'],
				$val['rating'],
				$val['review'],
				$val['bg'],
				$val['bu'],
				$val['seller'],
				$val['unfinished'],
				$val['target_review'],
				$val['requested_review'],
				$val['task'],
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
			header('Content-Disposition: attachment;filename="Export_Rsg_Product.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

    /*
     * rsgtask任务列表  只展示排名前十的数据
     */
    public function rsgtask(Request $req)
	{
		if(!Auth::user()->can(['rsgproducts-rsgtask'])) die('Permission denied -- rsgproducts rsgtask');
		$date = $todayDate = $this->getDefaultDate(date('Y-m-d'));

		$where = " and created_at = '".$date."' ";
		$where_product = " and created_at = '".$date."' and cast(rsg_products.sales_target_reviews as signed) - cast(rsg_products.requested_review as signed) > 0 ";

		//限制站点搜索
		$siteArrConfig = getSiteArr()['site'];
		$site = isset($_POST['site']) && $_POST['site'] ? $_POST['site'] : 'US';
		$siteArr = isset($siteArrConfig[$site]) ? $siteArrConfig[$site] : array();
		$where_product .= " and rsg_products.site in('".join($siteArr,"','")."')";
		if($site=='JP'){
			$where_product .= " and rsg_products.order_status = 1 ";
		}

		$sql = $this->getSql(0,$where,$where_product,$date);
		$sql .= ' LIMIT 0,10';

		$data = $this->queryRows($sql);
		$data = $this->getReturnData(0,$data,$date,$todayDate,'task');
		if($_POST){
			$return['status'] = 0;
			if($data){
				$return['data'] = $data;
				$return['status'] = 1;
			}
			return json_encode($return);
		}
		return view('rsgproducts/task',['data'=>$data]);
	}

	/*
	 * 得到sql查询语句
	 *
	 */
	public function getSql($leftskus,$where,$where_product,$date='',$orderby='')
	{
		if($orderby){
			$orderby = " order by {$orderby} ";
		}else{
			$orderby = " order by rsg_products.order_status desc,score desc,id desc ";
		}
		$ago15day = date('Y-m-d',strtotime($date)-86400*15);
		$field = $joinSkus = ' ';
		if($leftskus == 1){
			$field = ',skus_status.status as sku_status ';
			$joinSkus = ' left join skus_status on asin.item_no = skus_status.sku and asin.site = skus_status.site ';
		}
		$sql = "
        SELECT SQL_CALC_FOUND_ROWS
        	rsg_products.id as id,rsg_products.asin as asin,rsg_products.site as site,rsg_products.post_status as post_status,rsg_products.post_type as post_type,rsg_products.sales_target_reviews as target_review,rsg_products.requested_review as requested_review,asin.bg as bg,asin.bu as bu,asin.item_no as item_no,asin.seller as seller,asin.id as asin_id,rsg_products.number_of_reviews as review,rsg_products.review_rating as rating, num as unfinished,rsg_products.sku_level as sku_level, rsg_products.product_img as img,rsg_products.order_status as order_status,cast(rsg_products.sales_target_reviews as signed) - cast(rsg_products.requested_review as signed) as task,(status_score*type_score*level_score*rating_score*review_score)  as score {$field} 
            from rsg_products  
            left join (
				select id,
					case post_status
						WHEN 1 then 1
						WHEN 2 then 2
						ELSE 0 END as status_score,
					case post_type
						WHEN 1 then 1*20
						WHEN 2 then 0.5*20
						ELSE 0 END as type_score,
					case sku_level
						WHEN 'S' then 1
						WHEN 'A' then 0.6
						WHEN 'B' then 0.2
						ELSE 0 END as level_score,
					case review_rating
						WHEN 5 then 1
						WHEN 4.9 then 1
						WHEN 4.8 then 2
						WHEN 4.7 then 4
						WHEN 4.6 then 2
						WHEN 4.5 then 1
						WHEN 4.4 then 1
						WHEN 4.3 then 3
						WHEN 4.2 then 5
						WHEN 4.1 then 4
						WHEN 0 then 1
						ELSE 0 END as rating_score,
					if(site='www.amazon.com',
						case 
							WHEN number_of_reviews < 100 then 10
							WHEN number_of_reviews >= 100 and number_of_reviews < 400 then 7
							WHEN number_of_reviews >= 400 and number_of_reviews < 1000 then 4
							WHEN number_of_reviews >= 1000 and number_of_reviews < 4000 then 1
							WHEN number_of_reviews >= 4000 then 0
							END,
						case 
							WHEN number_of_reviews < 40 then 10
							WHEN number_of_reviews >= 40 and number_of_reviews < 100 then 7
							WHEN number_of_reviews >= 100 and number_of_reviews < 400 then 4
							WHEN number_of_reviews >= 400 and number_of_reviews <= 1000 then 1 
							WHEN number_of_reviews > 1000 then 0
							END 
					)as review_score 
				from rsg_products 
				where created_at = '".$date."'  
			) as rsg_score on rsg_score.id=rsg_products.id 
		  	left join  asin ON rsg_products.asin=asin.asin and rsg_products.site=asin.site and rsg_products.sellersku = asin.sellersku 
        	left join (
        		select count(*) as num,asin,site 
				from rsg_products 
				left join rsg_requests on product_id = rsg_products.id and step IN(4,5,6,7) 
				where rsg_requests.created_at <= '".$date." 23:59:59 ' and rsg_requests.created_at >='".$ago15day." 00:00:00 ' 
				group by asin,site 
        	) as rsg on rsg_products.asin=rsg.asin and rsg_products.site=rsg.site 
        	{$joinSkus} 
			where 1 = 1 {$where_product} 
			{$orderby} ";
		return $sql;
	}

	/*
	 * 得到处理后的表格数据
	 */
	public function getReturnData($leftskus,$data,$date='',$todayDate='',$action='') {
		$siteShort = getSiteShort();
		$postStatus = getPostStatus();
		$postType = getPostType();
		$productOrderStatus = getProductOrderStatus();
		//sku状态信息,任务列表的时候不关联查询skus_status表，因此用此种方法获取sku状态信息，因为关联查询此表速度会慢很多，产品列表是因为要搜索status，所以需要关联查询
		if (empty($leftskus)){
			$sapSiteCode = getSapSiteCode();
			$sku_sql = "select sku,sap_site_id,any_value(status) as sku_status from skus_status group by sku,sap_site_id";
			$_skuData = $this->queryRows($sku_sql);
			$skuData = array();
			foreach($_skuData as $key=>$val){
				$site = isset($sapSiteCode[$val['sap_site_id']]) ? 'www.'.$sapSiteCode[$val['sap_site_id']] : $val['sap_site_id'];
				$skuData[$val['sku'].'_'.$site]['sku_status'] = $val['sku_status'];
			}
		}

		$i = 1;
		foreach ($data as $key => $val) {
			$data[$key]['rank'] = $i;
			if(!isset($val['sku_status'])){
				$data[$key]['sku_status'] = isset($skuData[$val['item_no'].'_'.$val['site']]) ? $skuData[$val['item_no'].'_'.$val['site']]['sku_status'] : '';
			}
			$data[$key]['unfinished'] = $val['unfinished'] > 0 ? $val['unfinished'] : 0;
			$data[$key]['basic_asin'] = $val['asin'];
			$data[$key]['product'] = '<a target="_blank" href="https://rsg.claimthegift.com/product/detail?id='.$val['asin_id'].'"><img src="'.$val['img'].'" width="50px" height="65px"></a>';
			$data[$key]['site'] = isset($siteShort[$val['site']]) ? $siteShort[$val['site']] : $val['site'];
			$data[$key]['asin'] = '<a href="https://' . $val['site'] . '/dp/' . $val['asin'] . '" target="_blank" rel="noreferrer">' . $val['asin'] . '</a>';//asin插入超链接
			$data[$key]['type'] = isset($postType[$val['post_type']]) ? $postType[$val['post_type']]['name'] : $val['post_type'];//post_type
			$data[$key]['status'] = isset($postStatus[$val['post_status']]) ? $postStatus[$val['post_status']]['name'] : $val['post_status'];
			$data[$key]['requested_review'] = $val['requested_review'];
			$data[$key]['action'] = '<a data-target="#ajax" class="badge badge-success" data-toggle="modal" href="/rsgrequests/create?productid='.$val['id'].'&asin=' . $val['asin'] . '&site=' . $val['site'] . '"> 
                                    <i class="fa fa-hand-o-up"></i></a>';
			if($data[$key]['task']<=0){
				$data[$key]['action'] = '<div class="badge badge-primary">Done</div>';
			}
			if ($action != 'task') {
				$data[$key]['action'] .= '<a href="' . url('rsgproducts/edit?id=' . $val['id'] . '') . '" target="_blank" class="badge badge-success"> Edit </a>';
			}

			$data[$key]['order_status'] = isset($productOrderStatus[$val['order_status']]) ? $productOrderStatus[$val['order_status']] : $val['order_status'];

			if ($date != $todayDate) {
				$data[$key]['action'] = '-';
			}
			$i++;
		}
		return $data;
	}

	/*
	 * 得到默认的查询数据日期
	 * 因为脚本数据是早上7点更新，所以在凌晨到早上7点之间显示当天的数据的时候会显示空白
	 * 因此凌晨到七点半之间要显示的是昨天的数据
	 */
	public function getDefaultDate($todayDate)
	{
		if(time()-strtotime($todayDate.' 07:30:00') < 0){
			//凌晨到七点半之间要显示的是昨天的数据
			$todayDate = date('Y-m-d',strtotime($todayDate)-86400);
		}
		return $todayDate;
	}


}