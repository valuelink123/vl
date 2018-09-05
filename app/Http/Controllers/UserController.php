<?php

namespace App\Http\Controllers;

use App\Sendbox;
use Illuminate\Http\Request;
use App\Accounts;
use Illuminate\Support\Facades\Session;

use App\User;
use App\Group;
use App\Inbox;
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
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
	 
	 public function getGroups(){
        $users = Group::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']]['group_name'] = $user['group_name'];
			$users_array[$user['id']]['user_ids'] = explode(",",$user['user_ids']);
        }
        return $users_array;
    }
	
	public function getUserGroups(){
		$groups = Groupdetail::where('user_id',Auth::user()->id)->get();
		$group_arr = array();
		foreach($groupss as $group){
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
        if(!Auth::user()->admin) die();
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
        if(!Auth::user()->admin) die();
		$existMails = Inbox::where('user_id',$id)->first();
		if($existMails){
			$request->session()->flash('error_message','Can not Delete User , There are many mails belong this user!');
		}else{
			User::where('id',$id)->delete();
			$request->session()->flash('success_message','Delete User Success');
		}
        return redirect('user');
    }

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->admin) die();
        $user = User::where('id',$id)->first()->toArray();
        if(!$user){
            $request->session()->flash('error_message','User not Exists');
            return redirect('user');
        }
        return view('user/edit')->with('user',$user);
    }

    public function total(Request $request)
    {
        //if(!Auth::user()->admin) die();
		
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
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//¸æËßä¯ÀÀÆ÷Êä³ö07ExcelÎÄ¼þ
				header('Content-Disposition: attachment;filename="Export_'.array_get($_REQUEST,'ExportType').'.xlsx"');//¸æËßä¯ÀÀÆ÷Êä³öä¯ÀÀÆ÷Ãû³Æ
				header('Cache-Control: max-age=0');//½ûÖ¹»º´æ
				$writer = new Xlsx($spreadsheet);
				$writer->save('php://output');
			}
			
			
        return view('user/total',['date_from'=>$date_from,'date_to'=>$date_to]);
    }
	
	
	
	public function etotal(Request $request)
    {
        //if(!Auth::user()->admin) die();

        $date_from = array_get($_REQUEST,'date_from')?array_get($_REQUEST,'date_from'):date('Y-m-d',strtotime('-7day'));
        $date_to = array_get($_REQUEST,'date_to')?array_get($_REQUEST,'date_to'):date('Y-m-d');
        //print_r($date_from);print_r($date_to);
        $user_received_total=array();
        $user_key=array();
		
		if(Auth::user()->admin){
			$user_total_r = new Inbox;
		}else{
			$user_total_r = Inbox::where('user_id',$this->getUserId());
		}
			
        
		
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
        if(!Auth::user()->admin) die();
        $this->validate($request, [
            'name' => 'required|string',
            'password' => 'required_with:password_confirmation|confirmed',
        ]);
        $update=array();
        $update['admin'] = ($request->get('admin'))?1:0;
        if($request->get('name')) $update['name'] = $request->get('name');
        if($request->get('password')) $update['password'] = Hash::make(($request->get('password')));
        $result = User::where('id',$id)->update($update);
        if ($result) {
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