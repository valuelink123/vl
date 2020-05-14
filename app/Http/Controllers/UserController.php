<?php

namespace App\Http\Controllers;

use App\Sendbox;
use Illuminate\Http\Request;
use App\Accounts;
use Illuminate\Support\Facades\Session;
use App\Asin;
use App\User;
use App\Group;
use App\Inbox;
use App\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class UserController extends Controller
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
	 
	 public function getGroups(){
        $groups = Group::get()->toArray();
        $groups_array = array();
        foreach($groups as $group){
            $groups_array[$group['id']]['group_name'] = $group['group_name'];
            $groups_array[$group['id']]['user_ids'] = explode(",",$group['user_ids']);
        }
        return $groups_array;
    }
	
	public function getUserGroups(){
		$groups = Groupdetail::where('user_id',Auth::user()->id)->get();
		$group_arr = array();
		foreach($groups as $group){
			$group_arr[] = $group->group_id;
		}
        $users = Group::whereIn('id',$group_arr)->get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']]['group_name'] = $user['group_name'];
			$users_array[$user['id']]['user_ids'] = explode(",",$user['user_ids']);
        }
        return $users_array;
    }
	 
	
    public function index(Request $request)
    {
        if(!Auth::user()->can(['users-show'])) die('Permission denied -- users-show');
		$user_id_from = $request->get('user_id_from');
		$user_id_to = $request->get('user_id_to');

		$user_from_arr = explode('_',$user_id_from);
		$user_to_arr = explode('_',$user_id_to);

				
       if(array_get($user_from_arr,1) && array_get($user_from_arr,0) && array_get($user_to_arr,0) && array_get($user_to_arr,1)){
           $result = Inbox::where('user_id',array_get($user_from_arr,1))->where('group_id',array_get($user_from_arr,0))->update(['user_id'=>array_get($user_to_arr,1),'group_id'=>array_get($user_to_arr,0)]);

           if ($result) {
               $request->session()->flash('success_message','Save Mail Success');
           } else {
               $request->session()->flash('error_message','Set Mail Failed');
           }
       }
	   
        $users = User::Where('id','<>',env('SYSTEM_AUTO_REPLY_USER_ID',1))->get();
		$users = User::all();
		foreach($users as $user){
            $users_array[$user->id] = $user->name;
        }
        return view('user/index',['users'=>$users,'users_array'=>$users_array,'groups'=>$this->getGroups()]);

    }

    public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
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
	

    public function destroy(Request $request,$id)
    {
        if(!Auth::user()->can(['users-update'])) die('Permission denied -- users-update');
		//$existMails = Inbox::where('user_id',$id)->first();
		//if($existMails){
		//	$request->session()->flash('error_message','Can not Delete User , There are many mails belong this user!');
		//}else{
			User::where('id',$id)->update(['locked'=>$request->get('locked')]);
			$request->session()->flash('success_message','Delete User Success');
		//}
        return redirect('user');
    }
	
	public function create(Request $request)
    {
		if(!Auth::user()->can(['users-create'])) die('Permission denied -- users-create');
		
		$roles = Role::pluck('display_name','id');
        return view('user/add',compact('roles'));
    }
	
	
	public function store(Request $request)
    {
        if(!Auth::user()->can(['users-create'])) die('Permission denied -- users-create');
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
			'roles'=> 'required|array'
        ]);

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
			'ubg' => $request->get('bg'),
            'ubu' => $request->get('bu'),
			'sap_seller_id' => intval($request->get('sap_seller_id')),
			'admin'=> ($request->get('admin'))?1:0
        ]);
        if ($user) {
			foreach ($request->input('roles') as $key => $value) {
				$user->attachRole($value);
			}
            $request->session()->flash('success_message','Set User Success');
            return redirect('user/'.$user->id.'/edit');
        } else {
            $request->session()->flash('error_message','Set User Failed');
            return redirect()->back()->withInput();
        }

    }
	
    public function edit(Request $request,$id)
    {
       if(!Auth::user()->can(['users-show'])) die('Permission denied -- users-show');
        $user = User::find($id)->toArray();
		
		
		$roles = Role::pluck('display_name','id');
        $userRole = User::find($id)->roles->pluck('id')->toArray();
		
		
        if(!$user){
            $request->session()->flash('error_message','User not Exists');
            return redirect('user');
        }
        return view('user/edit',compact('user','roles','userRole'));
    }

    public function total(Request $request)
    {
       if(!Auth::user()->can(['data-statistics'])) die('Permission denied -- data-statistics');
		
		$date_from = array_get($_REQUEST,'date_from')?array_get($_REQUEST,'date_from'):date('Y-m-d',strtotime('-7day'));
        $date_to = array_get($_REQUEST,'date_to')?array_get($_REQUEST,'date_to'):date('Y-m-d');
		$arrayData= array();
		if (array_get($_REQUEST,'ExportType')) {
            if(array_get($_REQUEST,'ExportType')=='Users'){
				$users=$this->getUsers();
				$user_received_total=array();
				$user_key=array();
				$user_total_r = Inbox::select(DB::raw('count(*) as r_count, user_id,left(date,10) as date'));
		
				if($date_from){
					$user_total_r = $user_total_r->where('date','>=',$date_from.' 00:00:00');
				}
				if($date_to){
					$user_total_r = $user_total_r->where('date','<=',$date_to.' 23:59:59');
				}
				$user_total_r = $user_total_r->groupBy('user_id',DB::raw('left(date,10)'))->get();
				foreach($user_total_r as $r_total){
					$user_received_total[$r_total['user_id']][$r_total['date']]=$r_total['r_count'];
					$user_key[$r_total['user_id']]=1;
				}
		
				$user_send_total=array();
				$user_total_s = Sendbox::select(DB::raw('count(*) as s_count, user_id,left(date,10) as date'));
		
				if($date_from){
					$user_total_s = $user_total_s->where('date','>=',$date_from.' 00:00:00');
				}
				if($date_to){
					$user_total_s = $user_total_s->where('date','<=',$date_to.' 23:59:59');
				}
		
				$user_total_s = $user_total_s->groupBy('user_id',DB::raw('left(date,10)'))->get();
		
				foreach($user_total_s as $s_total){
					$user_send_total[$s_total['user_id']][$s_total['date']]=$s_total['s_count'];
					$user_key[$s_total['user_id']]=1;
				}
				$headArray[] = 'Name';
				for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) {
				   $headArray[] = date('md',$i).' Rec';
				   $headArray[] = date('md',$i).' Send';   
				}
				$headArray[] = 'Total Rec';
				$headArray[] = 'Total Send';
				$arrayData[] = $headArray;
				
				$columns_total_rec = $columns_total_send = array();
				
				foreach ($user_key as $user_id=>$user_value){
						unset($dataArray);
						$line_total_rec = $line_total_send = 0;
						$dataArray[]=array_get($users,$user_id,$user_id);
						
						for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) {
							$columns_total_rec[date('Y-m-d',$i)] = array_get($columns_total_rec,date('Y-m-d',$i),0)+array_get($user_received_total,$user_id.'.'.date('Y-m-d',$i),0);
							$columns_total_send[date('Y-m-d',$i)] = array_get($columns_total_send,date('Y-m-d',$i),0)+array_get($user_send_total,$user_id.'.'.date('Y-m-d',$i),0);
							$line_total_rec+=array_get($user_received_total,$user_id.'.'.date('Y-m-d',$i),0);
							$line_total_send+=array_get($user_send_total,$user_id.'.'.date('Y-m-d',$i),0);
							$dataArray[]=array_get($user_received_total,$user_id.'.'.date('Y-m-d',$i),0);
							$dataArray[]=array_get($user_send_total,$user_id.'.'.date('Y-m-d',$i),0);
						}
						$dataArray[]=$line_total_rec;
						$dataArray[]=$line_total_send;
						$arrayData[] = $dataArray; 
						$columns_total_rec['total'] = array_get($columns_total_rec,'total',0)+$line_total_rec;
						$columns_total_send['total'] = array_get($columns_total_send,'total',0)+$line_total_send;      
				}
				
				$footArray[] = 'Total';
				for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) {
				   $footArray[] = array_get($columns_total_rec,date('Y-m-d',$i),0);
				   $footArray[] = array_get($columns_total_send,date('Y-m-d',$i),0);      
				}
				$footArray[] = array_get($columns_total_rec,'total',0);
				$footArray[] = array_get($columns_total_send,'total',0);
				$arrayData[] = $footArray;

			}
			
			
			if(array_get($_REQUEST,'ExportType')=='Accounts'){
			    $accounts=$this->getAccounts();
				$account_received_total=array();
				$account_key=array();
				$account_total_r = Inbox::select(DB::raw('count(*) as r_count, to_address,left(date,10) as date'));
				if($date_from){
					$account_total_r = $account_total_r->where('date','>=',$date_from.' 00:00:00');
				}
				if($date_to){
					$account_total_r = $account_total_r->where('date','<=',$date_to.' 23:59:59');
				}
				$account_total_r = $account_total_r->groupBy('to_address',DB::raw('left(date,10)'))->get();
		
				foreach($account_total_r as $r_total){
					$account_received_total[$r_total['to_address']][$r_total['date']]=$r_total['r_count'];
					$account_key[$r_total['to_address']]=1;
				}
		
				$account_send_total=array();
				$account_total_s = Sendbox::select(DB::raw('count(*) as s_count, from_address,left(date,10) as date'));
				if($date_from){
					$account_total_s = $account_total_s->where('date','>=',$date_from.' 00:00:00');
				}
				if($date_to){
					$account_total_s = $account_total_s->where('date','<=',$date_to.' 23:59:59');
				}
				$account_total_s = $account_total_s->groupBy('from_address',DB::raw('left(date,10)'))->get();
		
				foreach($account_total_s as $s_total){
					$account_send_total[$s_total['from_address']][$s_total['date']]=$s_total['s_count'];
					$account_key[$s_total['from_address']]=1;
				}
				
				$headArray[] = 'Account';
				for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) {
				   $headArray[] = date('md',$i).' Rec';
				   $headArray[] = date('md',$i).' Send';   
				}
				$headArray[] = 'Total Rec';
				$headArray[] = 'Total Send';
				$arrayData[] = $headArray;
				
				$columns_total_rec = $columns_total_send = array();
				
				foreach ($account_key as $account_mail=>$account_value){
						unset($dataArray);
						$line_total_rec = $line_total_send = 0;
						$dataArray[] = $account_mail.' ('.array_get($accounts,strtolower($account_mail)).')';

						
						for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) {
							$columns_total_rec[date('Y-m-d',$i)] = array_get($columns_total_rec,date('Y-m-d',$i),0)+array_get(array_get($account_received_total,$account_mail)?$account_received_total[$account_mail]:array(),date('Y-m-d',$i),0);
							$columns_total_send[date('Y-m-d',$i)] = array_get($columns_total_send,date('Y-m-d',$i),0)+array_get(array_get($account_send_total,$account_mail)?$account_send_total[$account_mail]:array(),date('Y-m-d',$i),0);
							$line_total_rec+=array_get(array_get($account_received_total,$account_mail)?$account_received_total[$account_mail]:array(),date('Y-m-d',$i),0);
							$line_total_send+=array_get(array_get($account_send_total,$account_mail)?$account_send_total[$account_mail]:array(),date('Y-m-d',$i),0);
							$dataArray[]=array_get(array_get($account_received_total,$account_mail)?$account_received_total[$account_mail]:array(),date('Y-m-d',$i),0);
							$dataArray[]=array_get(array_get($account_send_total,$account_mail)?$account_send_total[$account_mail]:array(),date('Y-m-d',$i),0);
						}
						$dataArray[]=$line_total_rec;
						$dataArray[]=$line_total_send;
						$arrayData[] = $dataArray; 
						$columns_total_rec['total'] = array_get($columns_total_rec,'total',0)+$line_total_rec;
						$columns_total_send['total'] = array_get($columns_total_send,'total',0)+$line_total_send;      
				}
				
				$footArray[] = 'Total';
				for($i = strtotime($date_from); $i <= strtotime($date_to); $i+= 86400) {
				   $footArray[] = array_get($columns_total_rec,date('Y-m-d',$i),0);
				   $footArray[] = array_get($columns_total_send,date('Y-m-d',$i),0);      
				}
				$footArray[] = array_get($columns_total_rec,'total',0);
				$footArray[] = array_get($columns_total_send,'total',0);
				$arrayData[] = $footArray;
			}
			
			
			
			if(array_get($_REQUEST,'ExportType')=='Performance'){
				$problemList = DB::select("select a.*,b.out_count,b.out_date,c.purchasedate,d.brand_line,d.item_no from (select count(*) as in_count,from_address,to_address,min(date) as in_date,max(amazon_order_id) as  amazon_order_id
,max(sku) as  sku
,max(asin) as  asin
,user_id
from inbox 
where date>=:date_from and date<=:date_to group by from_address,to_address,user_id) as a left join 
(select count(*) as out_count,from_address,to_address,max(date) as out_date  
from sendbox 
where status<>'draft' and date>=:date_from_s and date<=:date_to_s group by from_address,to_address) as b on a.from_address=b.to_address and a.to_address=b.from_address

left join amazon_orders as c on a.amazon_order_id = c.amazonorderid
left join asin as d on a.sku=d.sellersku and a.asin=d.asin and CONCAT('www.',c.SalesChannel) =  d.site",['date_from' => $date_from,'date_to' => $date_to,'date_from_s' => $date_from,'date_to_s' => $date_to]);
				$headArray[] = 'From Address';
				$headArray[] = 'To Address';
				$headArray[] = 'Amazon Order ID';
				$headArray[] = 'Purchase Date';
				
				$headArray[] = 'Received Total';
				$headArray[] = 'Earliest Received Date';
				$headArray[] = 'Send Total';
				$headArray[] = 'Latest Send Date';
				$headArray[] = 'Sku';
				$headArray[] = 'Asin';
				$headArray[] = 'Item No.';
				$headArray[] = 'Brand Line';
				$headArray[] = 'User';
				$arrayData[] = $headArray;
				$users=$this->getUsers();
				foreach($problemList as $problem){
					$arrayData[] = [
						$problem->from_address,
						$problem->to_address,
						$problem->amazon_order_id,
						$problem->purchasedate,
						$problem->in_count,
						$problem->in_date,
						$problem->out_count,
						$problem->out_date,
						$problem->sku,
						$problem->asin,
						$problem->item_no,
						$problem->brand_line,
						array_get($users,$problem->user_id,$problem->user_id),
					];
				}
				
			}
			
			if(array_get($_REQUEST,'ExportType')=='Reply'){
				$problemList = DB::select("select b.from_address,b.to_address,b.amazon_order_id,b.sku,b.asin,b.date as f_date,a.date as s_date,a.user_id,c.purchasedate,d.brand_line,d.item_no
 from (select min(date) as date ,user_id,inbox_id from sendbox where inbox_id in (select inbox_id from sendbox where inbox_id<>0 and status='Send' and date>=:date_from and date<=:date_to group by inbox_id) group by user_id,inbox_id) as a 
left join inbox as b on a.inbox_id = b.id
left join amazon_orders as c on b.amazon_order_id = c.amazonorderid
left join asin as d on b.sku=d.sellersku and b.asin=d.asin and CONCAT('www.',c.SalesChannel) =  d.site
where a.date>=:sdate_from and a.date<=:sdate_to
 order by a.date asc",['date_from' => $date_from,'date_to' => $date_to,'sdate_from' => $date_from,'sdate_to' => $date_to]);
				
				$headArray[] = 'From Address';
				$headArray[] = 'To Address';
				$headArray[] = 'Amazon Order ID';
				$headArray[] = 'Purchase Date';
				$headArray[] = 'Received Date';
				$headArray[] = 'Send Date';
				$headArray[] = 'Processing Time ( Hour )';
				$headArray[] = 'Sku';
				$headArray[] = 'Asin';
				$headArray[] = 'Item No.';
				$headArray[] = 'Brand Line';
				$headArray[] = 'User';
				$arrayData[] = $headArray;
				$users=$this->getUsers();
				foreach($problemList as $problem){
					$arrayData[] = [
						$problem->from_address,
						$problem->to_address,
						$problem->amazon_order_id,
						$problem->purchasedate,
						$problem->f_date,
						$problem->s_date,
						round((strtotime($problem->s_date) - strtotime($problem->f_date))/3600,1),
						$problem->sku,
						$problem->asin,
						$problem->item_no,
						$problem->brand_line,
						array_get($users,$problem->user_id,$problem->user_id),
					];
				}
				
			}
			
			
			
			if(array_get($_REQUEST,'ExportType')=='Review'){
				$getList = DB::select("select count(*) as getcount ,review_user_id as user_id from review a left join asin b on a.site=b.site and a.sellersku=b.sellersku and a.asin=b.asin where date>=:date_from and date<=:date_to group by review_user_id",['date_from' => $date_from,'date_to' => $date_to]);
				$finishList = DB::select("select count(*) as finishcount ,a.status,review_user_id as user_id from review a left join asin b on a.site=b.site and a.sellersku=b.sellersku and a.asin=b.asin where edate>=:date_from and edate<=:date_to and a.status in (3,4,5) group by status,review_user_id",['date_from' => $date_from,'date_to' => $date_to]);
				$headArray[] = 'User';
				$headArray[] = 'Negative Reviews';
				$headArray[] = 'Removed';
				$headArray[] = 'Update 4 Stars';
				$headArray[] = 'Update 5 Stars';
				$headArray[] = 'Total';
				$headArray[] = 'Removal ratio';
				$arrayData[] = $headArray;
				$users=$this->getUsers();
				$users_data = array();
				foreach($getList as $getd){
					$users_data[$getd->user_id]['getcount'] = $getd->getcount;
				}
				
				foreach($finishList as $finishd){
					$users_data[$finishd->user_id][$finishd->status] = $finishd->finishcount;
				}
				foreach($users_data as $key=>$val){
					$arrayData[] = [
						array_get($users,$key,$key),
						array_get($val,'getcount',0),
						array_get($val,'3',0),
						array_get($val,'4',0),
						array_get($val,'5',0),
						array_get($val,'3',0)+array_get($val,'4',0)+array_get($val,'5',0),
						array_get($val,'getcount',0)?(round((array_get($val,'3',0)+array_get($val,'4',0)+array_get($val,'5',0))/array_get($val,'getcount',0),2)*100).'%':'100%'
						
					];
				}
				
			}
			
			
			if(array_get($_REQUEST,'ExportType')=='Removal'){
				$seller=[];
				$accounts= DB::connection('order')->table('accounts')->where('status',1)->groupBy(['sellername','sellerid'])->get(['sellername','sellerid']);
				$accounts=json_decode(json_encode($accounts), true);
				foreach($accounts as $account){
					$seller[$account['sellerid']]=$account['sellername'];
				}
				$datas= DB::connection('order')->table('removal_orders')->where('RequestDate','>=',$date_from.' 00:00:00')->where('RequestDate','<=',$date_to.' 23:59:59')->orderBy('RequestDate','asc')->get()->toArray();
				$arrayData[] = ['RequestDate','SellerId','SellerName','OrderId','ServiceSpeed','OrderType','OrderStatus','LastUpdatedDate','Sku','FnSku','Disposition','RequestedQuantity','CancelledQuantity','DisposedQuantity','ShippedQuantity','InProcessQuantity','RemovalFee','Currency'];
				$datas=json_decode(json_encode($datas), true);
				foreach($datas as $key=>$val){
					$arrayData[] = [
						array_get($val,'RequestDate'),
						array_get($val,'SellerId'),
						array_get($seller,array_get($val,'SellerId'),array_get($val,'SellerId')),
						array_get($val,'OrderId'),
						array_get($val,'ServiceSpeed'),
						array_get($val,'OrderType'),
						array_get($val,'OrderStatus'),
						array_get($val,'LastUpdatedDate'),
						array_get($val,'Sku'),
						array_get($val,'FnSku'),
						array_get($val,'Disposition'),
						array_get($val,'RequestedQuantity'),
						array_get($val,'CancelledQuantity'),
						array_get($val,'DisposedQuantity'),
						array_get($val,'ShippedQuantity'),
						array_get($val,'InProcessQuantity'),
						array_get($val,'RemovalFee'),
						array_get($val,'Currency')
					];
				}
				
			}
			
			
			
			if(array_get($_REQUEST,'ExportType')=='Return'){
				$seller=[];
				$accounts= DB::connection('order')->table('accounts')->where('status',1)->groupBy(['sellername','sellerid'])->get(['sellername','sellerid']);
				$accounts=json_decode(json_encode($accounts), true);
				foreach($accounts as $account){
					$seller[$account['sellerid']]=$account['sellername'];
				}
				$datas= DB::connection('order')->table('amazon_returns')->where('ReturnDate','>=',$date_from.'T00:00:00')->where('ReturnDate','<=',$date_to.'T23:59:59')->orderBy('ReturnDate','asc')->get()->toArray();
				$arrayData[] = ['ReturnDate','SellerId','SellerName','AmazonOrderId','LineNum','SellerSKU','ASIN','FNSKU','Title','Quantity','FulfillmentCenterId','DetailedDisposition','Reason','Status','LicensePlateNumber','CustomerComments'];

				$datas=json_decode(json_encode($datas), true);
				foreach($datas as $key=>$val){
					$arrayData[] = [
						array_get($val,'ReturnDate'),
						array_get($val,'SellerId'),
						array_get($seller,array_get($val,'SellerId'),array_get($val,'SellerId')),
						array_get($val,'AmazonOrderId'),
						array_get($val,'LineNum'),
						array_get($val,'SellerSKU'),
						array_get($val,'ASIN'),
						array_get($val,'FNSKU'),
						array_get($val,'Title'),
						array_get($val,'Quantity'),
						array_get($val,'FulfillmentCenterId'),
						array_get($val,'DetailedDisposition'),
						array_get($val,'Reason'),
						array_get($val,'Status'),
						array_get($val,'LicensePlateNumber'),
						array_get($val,'CustomerComments')
					];
				}
				
			}
			
			
			if(array_get($_REQUEST,'ExportType')=='Reimbursements'){
				$seller=[];
				$accounts= DB::connection('order')->table('accounts')->where('status',1)->groupBy(['sellername','sellerid'])->get(['sellername','sellerid']);
				$accounts=json_decode(json_encode($accounts), true);
				foreach($accounts as $account){
					$seller[$account['sellerid']]=$account['sellername'];
				}
				$datas= DB::connection('order')->table('amazon_reimbursements')->where('approvalDate','>=',$date_from.'T00:00:00')->where('approvalDate','<=',$date_to.'T23:59:59')->orderBy('approvalDate','asc')->get()->toArray();
				$arrayData[] = ['approvalDate','SellerId','SellerName','reimbursementId','lineNum','caseId','amazonOrderId','reason','Sku','FnSku','asin','productName','currencyUnit','quantityReimbursedCash','quantityReimbursedInventory','quantityReimbursedTotal','originalReimbursementId','originalReimbursementType','condition','amountPerUnit','amountTotal'];
				$datas=json_decode(json_encode($datas), true);
				foreach($datas as $key=>$val){
					$arrayData[] = [
						array_get($val,'approvalDate'),
						array_get($val,'sellerId'),
						array_get($seller,array_get($val,'sellerId'),array_get($val,'sellerId')),
						array_get($val,'reimbursementId'),
						array_get($val,'lineNum'),
						array_get($val,'caseId'),
						array_get($val,'amazonOrderId'),
						array_get($val,'reason'),
						array_get($val,'sku'),
						array_get($val,'fnsku'),
						array_get($val,'asin'),
						array_get($val,'productName'),
						array_get($val,'currencyUnit'),
						array_get($val,'quantityReimbursedCash'),
						array_get($val,'quantityReimbursedInventory'),
						array_get($val,'quantityReimbursedTotal'),
						array_get($val,'originalReimbursementId'),
						array_get($val,'originalReimbursementType'),
						array_get($val,'condition'),
						array_get($val,'amountPerUnit'),
						array_get($val,'amountTotal')
						
					];
				}
				
			}
			
			
			
			if(array_get($_REQUEST,'ExportType')=='EstimatedSales'){
				$seller=[];
				$datas= DB::connection('amazon')->table('symmetry_asins')->get();
				$arrayData[] = ['Asin','Site','Sku','Sku Group','Date','Estimated Quantity','Estimated Date'];
				foreach($datas as $key=>$val){
					$arrayData[] = [
						$val->asin,
						array_get(getSiteUrl(),$val->marketplace_id,$val->marketplace_id),
						$val->sku,
						$val->sku_group,
						$val->date,
						$val->quantity,
						$val->updated_at
					];
				}
				
			}
			
			
			
			if(array_get($_REQUEST,'ExportType')=='Fees'){
				$seller=[];
				$accounts= DB::connection('order')->table('accounts')->where('status',1)->groupBy(['sellername','sellerid'])->get(['sellername','sellerid']);
				$accounts=json_decode(json_encode($accounts), true);
				foreach($accounts as $account){
					$seller[$account['sellerid']]=$account['sellername'];
				}
				$users=$this->getUsers();
				$sellers=[];
				$sellers_data = DB::select("select users.id,max(bg) as bg,max(bu) as bu from users left join asin on users.sap_seller_id=asin.sap_seller_id where users.sap_seller_id>0 group by users.id");
				foreach($sellers_data as $seller){
					$sellers[$seller->id]['bg']=$seller->bg;
					$sellers[$seller->id]['bu']=$seller->bu;
				}
				
				$spreadsheet = new Spreadsheet();
				$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Ads Fee');
				$spreadsheet->addSheet($myWorkSheet, 0);
				$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Deal Fee');
				$spreadsheet->addSheet($myWorkSheet, 1);
				$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Coupon Fee');
				$spreadsheet->addSheet($myWorkSheet, 2);
				$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Service Fee');
				$spreadsheet->addSheet($myWorkSheet, 3);
				$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Cpc Details Fee');
				$spreadsheet->addSheet($myWorkSheet, 4);

				
				$datas= DB::connection('order')->table('finances_product_ads_payment_event')->where('PostedDate','>=',$date_from.'T00:00:00Z')->where('PostedDate','<=',$date_to.'T23:59:59Z')->orderBy('PostedDate','asc')->get()->toArray();
				$arrayData[] = ['PostedDate','SellerId','SellerName','InvoiceId','Amount','Currency','BG','BU','Sku','User'];
				$datas=json_decode(json_encode($datas), true);
				foreach($datas as $key=>$val){
					$arrayData[] = [
						array_get($val,'PostedDate'),
						array_get($val,'SellerId'),
						array_get($seller,array_get($val,'SellerId'),array_get($val,'SellerId')),
						array_get($val,'InvoiceId'),
						array_get($val,'TransactionValue'),
						array_get($val,'Currency'),
						array_get($sellers,array_get($val,'user_id').'.bg'),
						array_get($sellers,array_get($val,'user_id').'.bu'),
						array_get($val,'sku'),
						array_get($users,array_get($val,'user_id'),array_get($val,'user_id'))
					];
				}
				
				
	
				$spreadsheet->getSheet(0)
					->fromArray(
						$arrayData,  // The data to set
						NULL,        // Array values with this value will not be set
						'A1'         // Top left coordinate of the worksheet range where
									 //    we want to set these values (default is A1)
					);
				$arrayData=[];
				$datas= DB::connection('order')->table('finances_deal_event')->where('PostedDate','>=',$date_from.'T00:00:00Z')->where('PostedDate','<=',$date_to.'T23:59:59Z')->orderBy('PostedDate','asc')->get()->toArray();
				$arrayData[] = ['PostedDate','SellerId','SellerName','DealId','DealDescription','Amount','Currency','BG','BU','Sku','User'];
				$datas=json_decode(json_encode($datas), true);
				foreach($datas as $key=>$val){
					$arrayData[] = [
						array_get($val,'PostedDate'),
						array_get($val,'SellerId'),
						array_get($seller,array_get($val,'SellerId'),array_get($val,'SellerId')),
						array_get($val,'DealId'),
						array_get($val,'DealDescription'),
						array_get($val,'TotalAmount'),
						array_get($val,'Currency'),
						array_get($sellers,array_get($val,'user_id').'.bg'),
						array_get($sellers,array_get($val,'user_id').'.bu'),
						array_get($val,'sku'),
						array_get($users,array_get($val,'user_id'),array_get($val,'user_id'))
					];
				}
				$spreadsheet->getSheet(1)
					->fromArray(
						$arrayData,  // The data to set
						NULL,        // Array values with this value will not be set
						'A1'         // Top left coordinate of the worksheet range where
									 //    we want to set these values (default is A1)
					);
					
				$arrayData=[];
				$datas= DB::connection('order')->table('finances_coupon_event')->where('PostedDate','>=',$date_from.'T00:00:00Z')->where('PostedDate','<=',$date_to.'T23:59:59Z')->orderBy('PostedDate','asc')->get()->toArray();
				$arrayData[] = ['PostedDate','SellerId','SellerName','CouponId','SellerCouponDescription','Amount','Currency','BG','BU','Sku','User'];
				$datas=json_decode(json_encode($datas), true);
				foreach($datas as $key=>$val){
					$arrayData[] = [
						array_get($val,'PostedDate'),
						array_get($val,'SellerId'),
						array_get($seller,array_get($val,'SellerId'),array_get($val,'SellerId')),
						array_get($val,'CouponId'),
						array_get($val,'SellerCouponDescription'),
						array_get($val,'TotalAmount'),
						array_get($val,'Currency'),
						array_get($sellers,array_get($val,'user_id').'.bg'),
						array_get($sellers,array_get($val,'user_id').'.bu'),
						array_get($val,'sku'),
						array_get($users,array_get($val,'user_id'),array_get($val,'user_id'))
					];
				}
				$spreadsheet->getSheet(2)
					->fromArray(
						$arrayData,  // The data to set
						NULL,        // Array values with this value will not be set
						'A1'         // Top left coordinate of the worksheet range where
									 //    we want to set these values (default is A1)
					);
					
					
				$arrayData=[];
				$datas= DB::connection('order')->table('finances_servicefee_event')->where('PostedDate','>=',$date_from.'T00:00:00Z')->where('PostedDate','<=',$date_to.'T23:59:59Z')->orderBy('PostedDate','asc')->get()->toArray();
				$arrayData[] = ['PostedDate','SellerId','SellerName','FeeDescription','Amount','Currency','BG','BU','Sku','User'];
				$datas=json_decode(json_encode($datas), true);
				foreach($datas as $key=>$val){
					$arrayData[] = [
						array_get($val,'PostedDate'),
						array_get($val,'SellerId'),
						array_get($seller,array_get($val,'SellerId'),array_get($val,'SellerId')),
						array_get($val,'Type'),
						array_get($val,'Amount'),
						array_get($val,'Currency'),
						array_get($sellers,array_get($val,'user_id').'.bg'),
						array_get($sellers,array_get($val,'user_id').'.bu'),
						array_get($val,'sku'),
						array_get($users,array_get($val,'user_id'),array_get($val,'user_id'))
					];
				}
				$spreadsheet->getSheet(3)
					->fromArray(
						$arrayData,  // The data to set
						NULL,        // Array values with this value will not be set
						'A1'         // Top left coordinate of the worksheet range where
									 //    we want to set these values (default is A1)
					);
				$arrayData=[];
				$datas= DB::table('aws_report')->where('date','>=',$date_from)->where('date','<=',$date_to)->orderBy('date','asc')->get()->toArray();
				$arrayData[] = ['Date','SellerId','SellerName','marketplace id','campaign name','ad group','cost','sales','profit','orders','acos','impressions','clicks','ctr','cpc','ad conversion rate','default bid','status','BG','BU','Sku','User','Site'];
				$datas=json_decode(json_encode($datas), true);
				foreach($datas as $key=>$val){
					$arrayData[] = [
						array_get($val,'date'),
						array_get($val,'seller_id'),
						array_get($seller,array_get($val,'seller_id'),array_get($val,'seller_id')),
						array_get($val,'marketplace_id'),
						array_get($val,'campaign_name'),
						array_get($val,'ad_group'),
						array_get($val,'cost'),
						array_get($val,'sales'),
						array_get($val,'profit'),
						array_get($val,'orders'),
						array_get($val,'acos'),
						array_get($val,'impressions'),
						array_get($val,'clicks'),
						array_get($val,'ctr'),
						array_get($val,'cpc'),
						array_get($val,'ad_conversion_rate'),
						array_get($val,'default_bid'),
						array_get($val,'state'),
						array_get($sellers,array_get($val,'user_id').'.bg'),
						array_get($sellers,array_get($val,'user_id').'.bu'),
						array_get($val,'sku'),
						array_get($users,array_get($val,'user_id'),array_get($val,'user_id')),
						array_get(getSiteUrl(),$val['marketplace_id'],$val['marketplace_id']),
					];
				}
				$spreadsheet->getSheet(4)
					->fromArray(
						$arrayData,  // The data to set
						NULL,        // Array values with this value will not be set
						'A1'         // Top left coordinate of the worksheet range where
									 //    we want to set these values (default is A1)
					);

				$spreadsheet->setActiveSheetIndex(0);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
				header('Content-Disposition: attachment;filename="Export_'.array_get($_REQUEST,'ExportType').'.xlsx"');//告诉浏览器输出浏览器名称
				header('Cache-Control: max-age=0');//禁止缓存
				$writer = new Xlsx($spreadsheet);
				$writer->save('php://output');
				
				$arrayData=[];
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
				header('Content-Disposition: attachment;filename="Export_'.array_get($_REQUEST,'ExportType').'.xlsx"');//告诉浏览器输出浏览器名称
				header('Cache-Control: max-age=0');//禁止缓存
				$writer = new Xlsx($spreadsheet);
				$writer->save('php://output');
			}
			
			
        return view('user/total',['date_from'=>$date_from,'date_to'=>$date_to]);
    }
	
	
	
	public function etotal(Request $request)
    {
        if(!Auth::user()->can(['product-problem-show'])) die('Permission denied -- product-problem-show');

        $date_from = array_get($_REQUEST,'date_from')?array_get($_REQUEST,'date_from'):date('Y-m-d',strtotime('-7day'));
        $date_to = array_get($_REQUEST,'date_to')?array_get($_REQUEST,'date_to'):date('Y-m-d');
        //print_r($date_from);print_r($date_to);
        $user_received_total=array();
        $user_key=array();
		

		$user_total_r = new Inbox;

			
        
		
        if($date_from){
            $user_total_r = $user_total_r->where('date','>=',$date_from.' 00:00:00');
        }
        if($date_to){
            $user_total_r = $user_total_r->where('date','<=',$date_to.' 23:59:59');
        }
		
        $user_total_r = $user_total_r->whereNotNull('etype')->where('etype','<>','')->select(DB::raw('from_address,to_address,min(date) as date, remark,etype,sku,asin,item_no,epoint,user_id'))->groupBy('from_address','to_address', 'remark','etype','sku','asin','item_no','epoint','user_id')->orderBy('to_address','asc')->orderBy('date','asc')->get();
		//print_r($user_total_r->toSql());
        

        return view('user/etotal',['date_from'=>$date_from,'date_to'=>$date_to,'user_total_r'=>$user_total_r,'users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->can(['users-update'])) die('Permission denied -- users-update');
		$this->validate($request, [
            'name' => 'required|string',
            'password' => 'required_with:password_confirmation|confirmed',
			'roles'=> 'required|array'
        ]);
        $update=array();
        $update['admin'] = ($request->get('admin'))?1:0;
        if($request->get('name')) $update['name'] = $request->get('name');
        if($request->get('password')) $update['password'] = Hash::make(($request->get('password')));
		if($request->get('bg')) $update['ubg'] = $request->get('bg');
		if($request->get('bu')) $update['ubu'] = $request->get('bu');
		if($request->get('sap_seller_id')) $update['sap_seller_id'] = intval($request->get('sap_seller_id'));
			
        $user = User::find($id);
		$result = $user->update($update);

        if ($result) {
			DB::table('role_user')->where('user_id',$id)->delete();
 
         
			foreach ($request->input('roles') as $key => $value) {
				$user->attachRole($value);
			}
            $request->session()->flash('success_message','Set User Success');
            return redirect('user');
        } else {
            $request->session()->flash('error_message','Set User Failed');
            return redirect()->back()->withInput();
        }

    }

    public function profile(Request $request){
        if ($request->getMethod()=='POST')
        {
            $this->validate($request, [
                'name' => 'required|string',
                'current_password' => 'required_with:password,password_confirmation|string',
                'password' => 'required_with:password_confirmation|confirmed',
                //'password_confirmation' => 'required_with:password|string|min:6',
            ]);
            $user = User::findOrFail(Auth::user()->id);

            $result = Hash::check($request->get('current_password'), $user->password);//Auth::validate(['password'=>$request->get('current_password')]);
            if($result){
                $user->name = $request->get('name');
				$user->sap_seller_id = intval($request->get('sap_seller_id'));
				$user->ubg = trim($request->get('bg'));
				$user->ubu = trim($request->get('bu'));
                if($request->get('password')) $user->password = Hash::make(($request->get('password')));
                $user->save();
                $request->session()->flash('success_message', 'Set Profile Success');
            }else{
                $request->session()->flash('error_message','Current Password not Match');
            }

        }
        $profile = User::findOrFail(Auth::user()->id);
        return view('user/profile')->with('profile',$profile);
    }

}