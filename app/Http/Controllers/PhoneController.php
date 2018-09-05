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
        return view('phone/index');
    }
	public function get(Request $request)
    {

        $orderby = 'date';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'phone';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'buyer_email';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'amazon_order_id';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'date';
            $sort = $_REQUEST['order'][0]['dir'];
        }
		
        $customers = new Phone;
		if(array_get($_REQUEST,'phone')){
            $customers = $customers->where('phone', 'like', '%'.$_REQUEST['phone'].'%');
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
	
    public function create()
    {
        return view('phone/add',['users'=>$this->getUsers(),'accounts'=>$this->getAccounts(),'groups'=>$this->getGroups(),'sellerids'=>$this->getSellerIds()]);
    }

	
    public function store(Request $request)
    {
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
        //if(!Auth::user()->admin) die();
        Phone::where('id',$id)->delete();
        $request->session()->flash('success_message','Delete Phone Message Success');
        return redirect('phone');
    }

    public function edit(Request $request,$id)
    {
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