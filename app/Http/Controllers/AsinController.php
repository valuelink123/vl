<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Asin;
use Illuminate\Support\Facades\Session;

use App\User;
use App\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class AsinController extends Controller
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
	
	public function getUsers(){
        //目前在职的（locked=0）销售人员（sap_seller_id>0）
        $users = User::where('sap_seller_id', '>', 0)->where('locked', '=', 0)->get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }
	
	public function getGroups(){
        $users = Group::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['group_name'];
        }
        return $users_array;
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user()->can(['asin-table-show'])) die('Permission denied -- asin-table-show');
		$sites = Asin::select('site')->groupBy('site')->get()->toArray();
        return view('asin/index',['users'=>$this->getUsers(),'groups'=>$this->getGroups(),'sites'=>$sites]);

    }


	
	public function export(Request $request){
		if(!Auth::user()->can(['asin-table-export'])) die('Permission denied -- asin-table-export');
		$orderby = 'asin';
        $sort = 'asc';
  
        if (array_get($_REQUEST, 'status')=='Unmatched') {
		$customers = Asin::select('fba_stock.seller_id','fba_stock.asin','fba_stock.seller_sku as sellersku','asin.id','asin.site','asin.item_no','asin.brand','asin.seller','asin.review_user_id','asin.group_id','asin.brand_line','asin.status','asin.star','asin.item_model','asin.bg','asin.bu','asin.store','asin.item_group','asin.sap_seller_id','asin.sap_site_id','asin.sap_store_id','asin.sap_warehouse_id','asin.sap_factory_id','asin.sap_shipment_id','asin.asin_last_update_date','asin.sales_28_22','asin.sales_21_15','asin.sales_14_08','asin.sales_07_01','asin.item_status','asin.sku_ranking','asin.sku_rating','asin.sku_review','asin.sku_price','asin.sku_sales','asin.last_keywords','asin.sku_strategy')
		->rightJoin('fba_stock',function($q){
				$q->on('fba_stock.asin', '=', 'asin.asin')
					->on('fba_stock.seller_sku', '=', 'asin.sellersku');
			})->whereNull('asin.id')->whereRaw('fba_stock+fba_transfer>0');
		}else{
		
			$customers = new Asin;
		}
		
		if (array_get($_REQUEST, 'group_id')) {
			if($_REQUEST['group_id']=='empty'){
				$customers = $customers->where(function ($query)  {
								$query->whereNull('group_id')
										->orwhere('group_id', '');
							});
			}else{
				$customers = $customers->where('group_id', $_REQUEST['group_id']);
			}
		}
		
		if (array_get($_REQUEST, 'review_user_id')) {
			if($_REQUEST['review_user_id']=='empty'){
				$customers = $customers->where(function ($query)  {
								$query->whereNull('review_user_id')
										->orwhere('review_user_id', '');
							});
			}else{
				$customers = $customers->where('review_user_id', $_REQUEST['review_user_id']);
			}
			
		}
		
		
		if (array_get($_REQUEST, 'site')) {
			$customers = $customers->whereIn('site', explode(',',$_REQUEST['site']));
		}
		
		if (array_get($_REQUEST, 'status') && array_get($_REQUEST, 'status')!='Unmatched') {
			$customers = $customers->where('status', $_REQUEST['status']);
		}
		
        if(array_get($_REQUEST,'item_no')){
            $customers = $customers->where('item_no', 'like', '%'.$_REQUEST['item_no'].'%');
        }
		
		if(array_get($_REQUEST,'item_model')){
            $customers = $customers->where('item_model', 'like', '%'.$_REQUEST['item_model'].'%');
        }
        if(array_get($_REQUEST,'asin')){
            $customers = $customers->where(((array_get($_REQUEST, 'status')=='Unmatched')?'fba_stock.asin':'asin'), 'like', '%'.$_REQUEST['asin'].'%');
        }
		
		 if(array_get($_REQUEST,'sellersku')){
            $customers = $customers->where(((array_get($_REQUEST, 'status')=='Unmatched')?'fba_stock.seller_sku':'sellersku'), 'like', '%'.$_REQUEST['sellersku'].'%');
        }
		if(array_get($_REQUEST,'item_group')){
            $customers = $customers->where('item_group', 'like', '%'.$_REQUEST['item_group'].'%');
        }
		if(array_get($_REQUEST,'brand_line')){
            $customers = $customers->where('brand_line', 'like', '%'.$_REQUEST['brand_line'].'%');
        }
		if(array_get($_REQUEST,'seller')){
            $customers = $customers->where('seller', 'like', '%'.$_REQUEST['seller'].'%');
        }
		if(array_get($_REQUEST,'bg')){
            $customers = $customers->where('bg', 'like', '%'.$_REQUEST['bg'].'%');
        }
		if(array_get($_REQUEST,'bu')){
            $customers = $customers->where('bu', 'like', '%'.$_REQUEST['bu'].'%');
        }
		
        $customersLists =  $customers->orderBy($orderby,$sort)->get()->toArray();

		$arrayData = array();
		$headArray[] = 'Site';
		$headArray[] = 'Asin';
		$headArray[] = 'SellerSku';
		$headArray[] = 'ItemNo.';
		$headArray[] = 'Model';
		$headArray[] = 'Status';
		$headArray[] = 'Item Group';
		$headArray[] = 'Brand';
		$headArray[] = 'Brand Line';
		$headArray[] = 'Seller';
		$headArray[] = 'BG';
		$headArray[] = 'BU';
		$headArray[] = 'Store';
		$headArray[] = 'Group';
		$headArray[] = 'Review User';
		
		$arrayData[] = $headArray;
		$users = $this->getUsers();
		$groups = $this->getGroups();
		$asin_status_array =  getAsinStatus();
		
		foreach ( $customersLists as $customersList){

            $arrayData[] = array(
               
				$customersList['site'],
               $customersList['asin'],
				
				$customersList['sellersku'],
                $customersList['item_no'],
				$customersList['item_model'],
				array_get($asin_status_array,empty($customersList['status'])?0:$customersList['status']),
                $customersList['item_group'],
				$customersList['brand'],
				$customersList['brand_line'],
				$customersList['seller'],
				$customersList['bg'],
				$customersList['bu'],
				$customersList['store'],
                array_get($groups,($customersList['group_id'])?$customersList['group_id']:0),
				array_get($users,($customersList['review_user_id'])?$customersList['review_user_id']:0),
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
			header('Content-Disposition: attachment;filename="Export_'.array_get($_REQUEST,'ExportType').'.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
	
	public function get(Request $request)
    {
        /*
   * Paging
   */
		if(!Auth::user()->can(['asin-table-show'])) die('Permission denied -- asin-table-show');
        $orderby = 'asin';
        $sort = 'asc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'site';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'asin';
			if($_REQUEST['order'][0]['column']==3) $orderby = 'sellersku';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'item_no';
			if($_REQUEST['order'][0]['column']==5) $orderby = 'item_model';
			if($_REQUEST['order'][0]['column']==6) $orderby = 'status';
            if($_REQUEST['order'][0]['column']==7) $orderby = 'item_group';
            if($_REQUEST['order'][0]['column']==8) $orderby = 'brand_line';
			if($_REQUEST['order'][0]['column']==9) $orderby = 'seller';
            if($_REQUEST['order'][0]['column']==10) $orderby = 'bg';
			if($_REQUEST['order'][0]['column']==11) $orderby = 'bu';
			if($_REQUEST['order'][0]['column']==12) $orderby = 'group_id';
			if($_REQUEST['order'][0]['column']==13) $orderby = 'review_user_id';
            $sort = $_REQUEST['order'][0]['dir'];
        }

        if (isset($_REQUEST["customActionType"]) && $_REQUEST["customActionType"] == "group_action") {
			if(!Auth::user()->can(['asin-table-batch-update'])) die('Permission denied -- asin-table-batch-update');
            $updateDate=array();
            if(array_get($_REQUEST,"giveUser")){
                $updateDate['group_id'] = array_get($_REQUEST,"giveUser");
            }
			if(array_get($_REQUEST,"giveReviewUser")){
                $updateDate['review_user_id'] = array_get($_REQUEST,"giveReviewUser");
            }
			if(array_get($_REQUEST,"giveStar")){
                $updateDate['star'] = round(array_get($_REQUEST,"giveStar"),1);
            }
			$updatebox = new Asin;
			if($updateDate) $updatebox->whereIN('id',$_REQUEST["id"])->update($updateDate);
            unset($updateDate);
        }
        
		if (array_get($_REQUEST, 'status')=='Unmatched') {
		$customers = Asin::select('fba_stock.seller_id','fba_stock.asin','fba_stock.seller_sku as sellersku','asin.id','asin.site','asin.item_no','asin.brand','asin.seller','asin.review_user_id','asin.group_id','asin.brand_line','asin.status','asin.star','asin.item_model','asin.bg','asin.bu','asin.store','asin.item_group','asin.sap_seller_id','asin.sap_site_id','asin.sap_store_id','asin.sap_warehouse_id','asin.sap_factory_id','asin.sap_shipment_id','asin.asin_last_update_date','asin.sales_28_22','asin.sales_21_15','asin.sales_14_08','asin.sales_07_01','asin.item_status','asin.sku_ranking','asin.sku_rating','asin.sku_review','asin.sku_price','asin.sku_sales','asin.last_keywords','asin.sku_strategy')
		->rightJoin('fba_stock',function($q){
				$q->on('fba_stock.asin', '=', 'asin.asin')
					->on('fba_stock.seller_sku', '=', 'asin.sellersku');
			})->whereNull('asin.id')->whereRaw('fba_stock+fba_transfer>0');
		}else{
		
			$customers = new Asin;
		}
		if (array_get($_REQUEST, 'group_id')) {
			if($_REQUEST['group_id']=='empty'){
				$customers = $customers->where(function ($query)  {
								$query->whereNull('group_id')
										->orwhere('group_id', '');
							});
			}else{
				$customers = $customers->where('group_id', $_REQUEST['group_id']);
			}
		}
		
		if (array_get($_REQUEST, 'review_user_id')) {
			if($_REQUEST['review_user_id']=='empty'){
				$customers = $customers->where(function ($query)  {
								$query->whereNull('review_user_id')
										->orwhere('review_user_id', '');
							});
			}else{
				$customers = $customers->where('review_user_id', $_REQUEST['review_user_id']);
			}
			
		}
		
		if (array_get($_REQUEST, 'site.0')) {
			$customers = $customers->whereIn('site', array_get($_REQUEST, 'site'));
		}
		
		if (array_get($_REQUEST, 'status') && array_get($_REQUEST, 'status')!='Unmatched') {
			$customers = $customers->where('status', $_REQUEST['status']);
		}
		
        if(array_get($_REQUEST,'item_no')){
            $customers = $customers->where('item_no', 'like', '%'.$_REQUEST['item_no'].'%');
        }
		
		if(array_get($_REQUEST,'item_model')){
            $customers = $customers->where('item_model', 'like', '%'.$_REQUEST['item_model'].'%');
        }
        if(array_get($_REQUEST,'asin')){
            $customers = $customers->where(((array_get($_REQUEST, 'status')=='Unmatched')?'fba_stock.asin':'asin'), 'like', '%'.$_REQUEST['asin'].'%');
        }
		
		 if(array_get($_REQUEST,'sellersku')){
            $customers = $customers->where(((array_get($_REQUEST, 'status')=='Unmatched')?'fba_stock.seller_sku':'sellersku'), 'like', '%'.$_REQUEST['sellersku'].'%');
        }
		if(array_get($_REQUEST,'item_group')){
            $customers = $customers->where('item_group', 'like', '%'.$_REQUEST['item_group'].'%');
        }
		if(array_get($_REQUEST,'brand_line')){
            $customers = $customers->where('brand_line', 'like', '%'.$_REQUEST['brand_line'].'%');
        }
		if(array_get($_REQUEST,'seller')){
            $customers = $customers->where('seller', 'like', '%'.$_REQUEST['seller'].'%');
        }
		if(array_get($_REQUEST,'bg')){
            $customers = $customers->where('bg', 'like', '%'.$_REQUEST['bg'].'%');
        }
		if(array_get($_REQUEST,'bu')){
            $customers = $customers->where('bu', 'like', '%'.$_REQUEST['bu'].'%');
        }
        $customersList =  $customers->orderBy($orderby,$sort)->get()->toArray();

        $iTotalRecords = count($customersList);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);

        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;
        $users = $this->getUsers();
		$groups = $this->getGroups();
		$asin_status_array =  getAsinStatus();
        for($i = $iDisplayStart; $i < $end; $i++) {
            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList[$i]['id'].'"/><span></span></label>',
                $customersList[$i]['site'],
				
                '<a href="https://'.$customersList[$i]['site'].'/dp/'.$customersList[$i]['asin'].'" target="_blank">'.$customersList[$i]['asin'].'</a>',
				
				$customersList[$i]['sellersku'],
                $customersList[$i]['item_no'],
				$customersList[$i]['item_model'],
				array_get($asin_status_array,empty($customersList[$i]['status'])?0:$customersList[$i]['status']),
                $customersList[$i]['item_group'],
				$customersList[$i]['brand_line'],
				$customersList[$i]['seller'],
				$customersList[$i]['bg'],
				$customersList[$i]['bu'],
                array_get($groups,($customersList[$i]['group_id'])?$customersList[$i]['group_id']:0),
				array_get($users,($customersList[$i]['review_user_id'])?$customersList[$i]['review_user_id']:0),
                '<a href="/asin/'.$customersList[$i]['id'].'/edit" class="btn btn-sm btn-outline grey-salsa" target="_blank"><i class="fa fa-search"></i> Edit </a>',
            );
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }
	




    public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['asin-table-show'])) die('Permission denied -- asin-table-show');
        $asin = Asin::where('id',$id)->first()->toArray();
        if(!$asin){
            $request->session()->flash('error_message','Asin not Exists');
            return redirect('asin');
        }
        return view('asin/edit',['users'=>$this->getUsers(),'groups'=>$this->getGroups(),'asin'=>$asin]);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->can(['asin-table-update'])) die('Permission denied -- asin-table-update');
        $seller_account = Asin::findOrFail($id);
		$seller_account->star = round($request->get('star'),1);
        $seller_account->group_id = $request->get('group_id');
		$seller_account->review_user_id = $request->get('review_user_id');
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Set Asin Success');
            return redirect('asin');
        } else {
            $request->session()->flash('error_message','Set Asin Failed');
            return redirect()->back()->withInput();
        }
    }

    public function checkAccount($request){
        $id = ($request->get('id'))?($request->get('id')):0;

        $seller_account = Asin::where('asin',$request->get('asin'))->where('site',$request->get('site'))->where('sellersku',$request->get('sellersku'))->where('id','<>',$id)
            ->first();
        if($seller_account) return true;
        return false;
    }

	public function getAsinBySku(Request $request)   
    {	
		$sku = $request->get('sku');

        $asins = Asin::where('item_no','LIKE','%'.$sku.'%')->groupBy(['asin'])->get(['asin'])->toArray();
		
        echo json_encode($asins);
		
	}
}