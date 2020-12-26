<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class McforderController extends Controller
{
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
        if(!Auth::user()->can(['mcforders'])) die('Permission denied -- mcforders');
		$date_from=date('Y-m-d',strtotime('-90 days'));
		$date_to=date('Y-m-d');
		//country下拉选择框
		$country= DB::connection('amazon')->select('SELECT DISTINCT country_code FROM amazon_mcf_orders');
		foreach($country as $key=>$val){
			$country[$key] = $val->country_code;
		}
        return view('mcforder/index',['date_from'=>$date_from ,'date_to'=>$date_to,'country'=>$country,'accounts'=>self::getSellerId()]);
    }


	public function getSellerId(){
		$seller=[];
		$accounts= DB::connection('amazon')->table('seller_accounts')->whereNull('deleted_at')->groupby(['id','label'])->pluck('label','id');
		return $accounts;
	}

    public function show($id)
    {
		if(!Auth::user()->can(['mcforders'])) die('Permission denied -- mcforders');
        $mcf_order = DB::connection('amazon')->table('amazon_mcf_orders')->find($id);
		if($mcf_order){
			$mcf_order->items = DB::connection('amazon')->table('amazon_mcf_orders_item')->where('seller_account_id',$mcf_order->seller_account_id)->where('seller_fulfillment_order_id',$mcf_order->seller_fulfillment_order_id)->get();
			$mcf_order->shipments = DB::connection('amazon')->table('amazon_mcf_shipment_item')->where('seller_account_id',$mcf_order->seller_account_id)->where('seller_fulfillment_order_id',$mcf_order->seller_fulfillment_order_id)->get();
			$mcf_order->packages = DB::connection('amazon')->table('amazon_mcf_shipment_package')->where('seller_account_id',$mcf_order->seller_account_id)->where('seller_fulfillment_order_id',$mcf_order->seller_fulfillment_order_id)->get();
		}else{
			die();
		}
		return view('mcforder/view',['order'=>$mcf_order,'accounts'=>self::getSellerId()]);
    }
    public function get(Request $request)
    {

        if(!Auth::user()->can(['mcforders'])) die('Permission denied -- mcforders');
		$orderby = $request->input('order.0.column',1);
		if($orderby==6){
			$orderby = 'status_updated_date_time';
		}else{
			$orderby = 'displayable_order_date_time';
		}
        $sort = $request->input('order.0.dir','desc');

        $date_from=$request->input('date_from')?$request->input('date_from'):date('Y-m-d',strtotime('- 90 days'));
        $date_to=$request->input('date_to')?$request->input('date_to'):date('Y-m-d');

		$datas= DB::connection('amazon')->table('amazon_mcf_orders')->where('displayable_order_date_time','>=',$date_from.' 00:00:00')->where('displayable_order_date_time','<=',$date_to.' 23:59:59');

        if($request->input('sellerid')){
            $datas = $datas->where('seller_account_id', $request->input('sellerid'));
        }

		if($request->input('order_id')){
            $datas = $datas->where('seller_fulfillment_order_id', $request->input('order_id'));
        }
		if($request->input('status')){
            $datas = $datas->where('fulfillment_order_status', $request->input('status'));
        }
		if($request->input('name')){
            $datas = $datas->where('name', $request->input('name'));
        }
		//country下拉选择框
		if($request->input('country')){
			$datas = $datas->where('country_code', $request->input('country'));
		}

        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
		$lists =  $datas->orderBy($orderby,$sort)->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;
		$accounts = $this->getSellerId();
		$lists=json_decode(json_encode($lists), true);

        //得到asin,sku,bg,bu,seller信息的数据处理
        $detail_sql = "select CONCAT(seller_accounts.id,'_',sap_asin_match_sku.seller_sku) as sai_ss,sap_asin_match_sku.asin as asin, sap_asin_match_sku.sku as sku,sap_seller_id,sap_seller_bg,sap_seller_bu
                FROM sap_asin_match_sku
                left join seller_accounts on seller_accounts.mws_seller_id = sap_asin_match_sku.seller_id
                and seller_accounts.mws_marketplaceid=sap_asin_match_sku.marketplace_id";
        $_detailData = DB::connection('amazon')->select($detail_sql);
        $_detailData=json_decode(json_encode($_detailData), true);
        $detailData = array();
        foreach($_detailData as $dk=>$dv){
            $detailData[$dv['sai_ss']] = $dv;
        }
        //得到sap_sell_id跟用户名称的对照关系
        $sapSeller = getUsers('sap_seller');
        $sapSeller=json_decode(json_encode($sapSeller), true);

		foreach ( $lists as $list){
            $asin = $sku = $bg = $bu = $seller = '';
            //通过seller_skus和seller_account_id转换得到asin,sku,bg,bu,seller
            $_sellerskus = array_filter(explode(';',$list['seller_skus']));//L5HPC0102FBA*1;NUHPC0095US*1;L5HPC0030FBA*1;L5HPC0037FBA*2;
            foreach($_sellerskus as $sk=>$sv){
                $sellersku = array_filter(explode('*',$sv));
                $sellersku = current($sellersku);
                $sai_ss = $list['seller_account_id'].'_'.$sellersku;
                if(isset($detailData[$sai_ss])){
                    $asin .= $detailData[$sai_ss]['asin'].'<br/>';
                    $sku .= $detailData[$sai_ss]['sku'].'<br/>';
                    $bg .= $detailData[$sai_ss]['sap_seller_bg'].'<br/>';
                    $bu .= $detailData[$sai_ss]['sap_seller_bu'].'<br/>';
                    $seller .= isset($sapSeller[$detailData[$sai_ss]['sap_seller_id']]) ? $sapSeller[$detailData[$sai_ss]['sap_seller_id']] : $detailData[$sai_ss]['sap_seller_id'].'<br/>';
                }
            }

            $records["data"][] = array(
                $list['displayable_order_date_time'],
				array_get($accounts,$list['seller_account_id']),
                // $asin ? $asin : '-',
                // $sku ? $sku : '-',
				$list['seller_fulfillment_order_id'],
				$list['name'],
				$list['country_code'],
				$list['fulfillment_order_status'],
                // $bg ? $bg : '-',
                // $bu ? $bu : '-',
                // $seller ? $seller : '-',
				$list['status_updated_date_time'],
				'<a href="/mcforder/'.$list['id'].'" target="_blank">
					<button type="submit" class="btn btn-success btn-xs">View</button>
				</a>'
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    //导出功能
	public function mcforderExport(Request $request)
	{
		if(!Auth::user()->can(['mcforders-report'])) die('Permission denied -- mcforders-report');
        $date_from = isset($_GET['date_from']) ? $_GET['date_from']:date('Y-m-d',strtotime('- 90 days'));
        $date_to = isset($_GET['date_to']) ? $_GET['date_to']:date('Y-m-d');
        $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $sellerid = isset($_GET['sellerid']) ? $_GET['sellerid'] : '';
        $country = isset($_GET['country']) ? $_GET['country'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';

        $datas= DB::connection('amazon')->table('amazon_mcf_orders')->where('displayable_order_date_time','>=',$date_from.' 00:00:00')->where('displayable_order_date_time','<=',$date_to.' 23:59:59');
        if($sellerid!==''){
            $datas = $datas->where('seller_account_id', $sellerid);
        }
        if($order_id!==''){
            $datas = $datas->where('seller_fulfillment_order_id', $order_id);
        }
        if($status!==''){
            $datas = $datas->where('fulfillment_order_status', $status);
        }
        if($name!==''){
            $datas = $datas->where('name', $name);
        }
        if($country!==''){
            $datas = $datas->where('country_code', $country);
        }

        $lists =  $datas->get()->toArray();
        $accounts = $this->getSellerId();//账号id跟账号名称之间的对应关系
        $lists=json_decode(json_encode($lists), true);
        $queries = DB::connection('amazon')->getQueryLog(); // 获取查询日志

        //得到asin,sku,bg,bu,seller信息的数据处理
        $detail_sql = "select CONCAT(seller_accounts.id,'_',sap_asin_match_sku.seller_sku) as sai_ss,sap_asin_match_sku.asin as asin, sap_asin_match_sku.sku as sku,sap_seller_id,sap_seller_bg,sap_seller_bu
                FROM sap_asin_match_sku
                left join seller_accounts on seller_accounts.mws_seller_id = sap_asin_match_sku.seller_id
                and seller_accounts.mws_marketplaceid=sap_asin_match_sku.marketplace_id";
        $_detailData = DB::connection('amazon')->select($detail_sql);
        $_detailData=json_decode(json_encode($_detailData), true);
        $detailData = array();
        foreach($_detailData as $dk=>$dv){
            $detailData[$dv['sai_ss']] = $dv;
        }
        //得到sap_sell_id跟用户名称的对照关系
        $sapSeller = getUsers('sap_seller');
        $sapSeller=json_decode(json_encode($sapSeller), true);

        //表头
        $headArray[] = 'Order Date';
        $headArray[] = 'Account';
        // $headArray[] = 'Asin';
        // $headArray[] = 'Sku';
        $headArray[] = 'Order Id';
        $headArray[] = 'Name';
        $headArray[] = 'Country';
        $headArray[] = 'Status';
        // $headArray[] = 'BG';
        // $headArray[] = 'BU';
        // $headArray[] = 'Seller';
        $headArray[] = 'Last Update';

        $arrayData[] = $headArray;

        foreach ( $lists as $list){
            $asin = $sku = $bg = $bu = $seller = '';
            //通过seller_skus和seller_account_id转换得到asin,sku,bg,bu,seller
            $_sellerskus = array_filter(explode(';',$list['seller_skus']));//L5HPC0102FBA*1;NUHPC0095US*1;L5HPC0030FBA*1;L5HPC0037FBA*2;
            foreach($_sellerskus as $sk=>$sv){
                $sellersku = array_filter(explode('*',$sv));
                $sellersku = current($sellersku);
                $sai_ss = $list['seller_account_id'].'_'.$sellersku;
                if(isset($detailData[$sai_ss])){
                    $asin .= $detailData[$sai_ss]['asin'].';';
                    $sku .= $detailData[$sai_ss]['sku'].';';
                    $bg .= $detailData[$sai_ss]['sap_seller_bg'].';';
                    $bu .= $detailData[$sai_ss]['sap_seller_bu'].';';
                    $seller .= isset($sapSeller[$detailData[$sai_ss]['sap_seller_id']]) ? $sapSeller[$detailData[$sai_ss]['sap_seller_id']] : $detailData[$sai_ss]['sap_seller_id'].';';
                }
            }
            $arrayData[] = array(
                $list['displayable_order_date_time'],
                array_get($accounts,$list['seller_account_id']),
                // $asin ? $asin : '-',
                // $sku ? $sku : '-',
                $list['seller_fulfillment_order_id'],
                $list['name'],
                $list['country_code'],
                $list['fulfillment_order_status'],
                // $bg ? $bg : '-',
                // $bu ? $bu : '-',
                // $seller ? $seller : '-',
                $list['status_updated_date_time'],
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
            header('Content-Disposition: attachment;filename="Export_Mcf_Order.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
	}

}