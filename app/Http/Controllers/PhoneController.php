<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Phone;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use App\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PhoneController extends Controller
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
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		if(!Auth::user()->can(['callmessage-show'])) die('Permission denied -- callmessage-show');
        return view('phone/index');
    }
	public function get(Request $request)
    {
        //取出所有用户的id=>name的映射数组
        $users=$this->getUsers();

        $orderby = 'date';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'phone';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'buyer_email';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'amazon_order_id';
            if($_REQUEST['order'][0]['column']==6) $orderby = 'date';
            $sort = $_REQUEST['order'][0]['dir'];
        }
		
        $customers = new Phone;

        //新添加的搜索选项（创建人姓名，buyer_email，amazon_order_id，content）
        $searchField = array('phone','buyer_email','amazon_order_id','content');
        foreach($searchField as $field){
            if(array_get($_REQUEST,$field)){
                $customers = $customers->where($field, 'like', '%'.$_REQUEST[$field].'%');
            }
        }

        if(array_get($_REQUEST,'user_name')){
            $username = trim($_REQUEST['user_name']);
            $userId = in_array($username,$users) ? array_search($username,$users) : $username;
            $customers = $customers->where('user_id', $userId);
        }
		
        if(array_get($_REQUEST,'date_from')){
            $customers = $customers->where('date','>=',$_REQUEST['date_from'].' 00:00:00');
        }
        if(array_get($_REQUEST,'date_to')){
            $customers = $customers->where('date','<=',$_REQUEST['date_to'].' 23:59:59');
        }
		if(!Auth::user()->admin) {
        	 $customers = $customers->orderByRaw('case when user_id='.Auth::user()->id.' then 0 else 1 end asc');
		}
		$iTotalRecords = $customers->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
		$customersLists =  $customers->orderBy($orderby,$sort)->skip($iDisplayStart)->take($iDisplayLength)->get()->toArray();
        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

		
		foreach ( $customersLists as $customersList){

            $records["data"][] = array(
                $customersList['id'],
                $customersList['phone'],
				$customersList['buyer_email'],
				$customersList['amazon_order_id'],
				$customersList['content'],
                isset($users[$customersList['user_id']]) ? $users[$customersList['user_id']] : '未知',
				$customersList['date'],
				
                
                '<a href="'.url('phone/'.$customersList['id'].'/edit').'">
					<button type="submit" class="btn btn-success btn-xs">Edit</button>
				</a>
				<form action="'.url('phone/'.$customersList['id']).'" method="POST" style="display: inline;">
					'.method_field('DELETE').'
					'.csrf_field().'
					<button type="submit" class="btn btn-danger btn-xs">Delete</button>
				</form>',
            );
		}



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    //导出功能
    public function export(Request $request){
	
		if(!Auth::user()->can(['callmessage-export'])) die('Permission denied -- callmessage-export');
        $customers = new Phone;

        //取出所有用户的id=>name的映射数组
        $users=$this->getUsers();

        $searchField = array('phone','buyer_email','amazon_order_id','content');
        foreach($searchField as $field){
            if(array_get($_REQUEST,$field)){
                $customers = $customers->where($field, 'like', '%'.$_REQUEST[$field].'%');
            }
        }

        if(array_get($_REQUEST,'user_name')){
            $username = trim($_REQUEST['user_name']);
            $userId = in_array($username,$users) ? array_search($username,$users) : $username;
            $customers = $customers->where('user_id', $userId);
        }

        if(array_get($_REQUEST,'date_from')){
            $customers = $customers->where('date','>=',$_REQUEST['date_from'].' 00:00:00');
        }
        if(array_get($_REQUEST,'date_to')){
            $customers = $customers->where('date','<=',$_REQUEST['date_to'].' 23:59:59');
        }
        //按时间倒序排序，查出所有数据
        $customersLists =  $customers->orderBy('date','desc')->get()->toArray();

        $headArray[] = 'Phone Number';
        $headArray[] = 'Buyer Email';
        $headArray[] = 'Amazon OrderID';
        $headArray[] = 'Call Notes';
        $headArray[] = 'Creator';
        $headArray[] = 'Date';

        // 导出表格的数据为$arrayData
        $arrayData[] = $headArray;
        foreach ($customersLists as $key=>$val){
            $arrayData[] = array(
                $val['phone'],
                $val['buyer_email'],
                $val['amazon_order_id'],
                $val['content'],
                isset($users[$val['user_id']]) ? $users[$val['user_id']] : '未知',
                $val['date'],
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
            header('Content-Disposition: attachment;filename="Export_Phone.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }
	
    public function create()
    {
		if(!Auth::user()->can(['callmessage-create'])) die('Permission denied -- callmessage-create');
        return view('phone/add',['users'=>$this->getUsers(),'accounts'=>$this->getAccounts(),'groups'=>$this->getGroups(),'sellerids'=>$this->getSellerIds()]);
    }

	
    public function store(Request $request)
    {
		if(!Auth::user()->can(['callmessage-create'])) die('Permission denied -- callmessage-create');
        $this->validate($request, [
            'phone' => 'required|string',
            'content' => 'required|string',
        ]);
        $rule = new Phone;
        $rule->phone = $request->get('phone');
        $rule->content = $request->get('content');
        $rule->amazon_order_id = $request->get('rebindorderid');
        $rule->buyer_email = $request->get('buyer_email');
        $rule->sku = $request->get('sku');
		$rule->remark = $request->get('remark');
		$rule->etype = $request->get('etype');
		$rule->asin = $request->get('asin');
		$rule->item_no = $request->get('item_no');
		$rule->epoint = $request->get('epoint');
        $rule->date = date('Y-m-d H:i:s');
		if($request->get('rebindordersellerid')){
			$account_email = $this->getSellerIdsEmail();
			$rule->seller_id = $request->get('rebindordersellerid');
			$rule->seller_email = array_get($account_email,$request->get('rebindordersellerid'));	
		}
        $rule->user_id = $this->getUserId();
		
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Phone Success');
            return redirect('phone');
        } else {
            $request->session()->flash('error_message','Set Phone Failed');
            return redirect()->back()->withInput();
        }
    }


    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->can(['callmessage-delete'])) die('Permission denied -- callmessage-delete');
        Phone::where('id',$id)->delete();
        $request->session()->flash('success_message','Delete Phone Message Success');
        return redirect('phone');
    }

    public function edit(Request $request,$id)
    {
		if(!Auth::user()->can(['callmessage-show'])) die('Permission denied -- callmessage-show');
        $phone= Phone::where('id',$id)->first()->toArray();
        if(!$phone){
            $request->session()->flash('error_message','Phone Message not Exists');
            return redirect('phone');
        }
		$order = array();
		if(array_get($phone,'amazon_order_id') && array_get($phone,'seller_id')){
            $order = DB::table('amazon_orders')->where('SellerId', array_get($phone,'seller_id'))->where('AmazonOrderId', array_get($phone,'amazon_order_id'))->first();
            if($order) $order->item = DB::table('amazon_orders_item')->where('SellerId', array_get($phone,'seller_id'))->where('AmazonOrderId', array_get($phone,'amazon_order_id'))->get();
        }
		
        return view('phone/edit',['phone'=>$phone,'users'=>$this->getUsers(),'accounts'=>$this->getAccounts(),'groups'=>$this->getGroups(),'sellerids'=>$this->getSellerIds(),'order'=>($order)?$order:array()]);
    }

    public function update(Request $request,$id)
    {
		if(!Auth::user()->can(['callmessage-update'])) die('Permission denied -- callmessage-update');
       $this->validate($request, [
            'phone' => 'required|string',
            'content' => 'required|string',
        ]);
        $rule = Phone::findOrFail($id);
        $rule->phone = $request->get('phone');
        $rule->content = $request->get('content');
        $rule->amazon_order_id = $request->get('rebindorderid');
        $rule->buyer_email = $request->get('buyer_email');
        $rule->sku = $request->get('sku');
		$rule->remark = $request->get('remark');
		$rule->etype = $request->get('etype');
		$rule->asin = $request->get('asin');
		$rule->item_no = $request->get('item_no');
		$rule->epoint = $request->get('epoint');
		if($request->get('rebindordersellerid')){
			$account_email = $this->getSellerIdsEmail();
			$rule->seller_id = $request->get('rebindordersellerid');
			$rule->seller_email = array_get($account_email,$request->get('rebindordersellerid'));	
		}
        $rule->user_id = $this->getUserId();
        if ($rule->save()) {
            $request->session()->flash('success_message','Set Phone Success');
            return redirect('phone');
        } else {
            $request->session()->flash('error_message','Set Phone Failed');
            return redirect()->back()->withInput();
        }
    }
	
	
	public function getUsers(){
        $users = User::get()->toArray();
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
            $users_array[$user['id']]['group_name'] = $user['group_name'];
			$users_array[$user['id']]['user_ids'] = explode(",",$user['user_ids']);
        }
        return $users_array;
    }

    public function getAccounts(){
        $accounts = Accounts::get()->toArray();
        $accounts_array = array();
        foreach($accounts as $account){
            $accounts_array[strtolower($account['account_email'])] = $account['account_name'];
        }
        return $accounts_array;
    }
	
	public function getSellerIds(){
        $accounts = Accounts::where('type','Amazon')->get()->toArray();
        $accounts_array = array();
        foreach($accounts as $account){
            $accounts_array[$account['account_sellerid']] = $account['account_name'];
        }
        return $accounts_array;
    }
	
	
	public function getSellerIdsEmail(){
        $accounts = Accounts::where('type','Amazon')->get()->toArray();
        $accounts_array = array();
        foreach($accounts as $account){
            $accounts_array[$account['account_sellerid']] = strtolower($account['account_email']);
        }
        return $accounts_array;
    }

}