<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inbox;
use App\Sendbox;
use App\Accounts;
use Illuminate\Support\Facades\Session;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Illuminate\Http\Response;
use App\Models\NonCtg;
use App\Models\Ctg;
use App\Models\B1g1;
use App\Models\Cashback;
class SendController extends Controller
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
		if(!Auth::user()->can(['sendbox-show'])) die('Permission denied -- sendbox-show');
        return view('send/index',['users'=>$this->getUsers()]);
    }

    public function create(Request $request)
    {
		if(!Auth::user()->can(['compose'])) die('Permission denied -- compose');
		$accounts = Accounts::get()->toArray();
        $accounts_array = $type_array =  array();
        foreach($accounts as $account){
            $accounts_array[$account['id']] = $account['account_email'];
			$type_array[$account['account_email']] = $account['type'];
        }
        return view('send/add',['accounts'=>$accounts_array,'request'=>$request,'accounts_type'=>$type_array]);
    }

    public function getAccounts(){
        $accounts = Accounts::get()->toArray();
        $accounts_array = array();
        foreach($accounts as $account){
            $accounts_array[$account['id']] = $account['account_email'];
        }
        return $accounts_array;
    }
	
	
	public function gAccounts(){
        $accounts = Accounts::get()->toArray();
        $accounts_array = array();
        foreach($accounts as $account){
            $accounts_array[strtolower($account['account_email'])] = $account['account_name'];
        }
        return $accounts_array;
    }

    public function deletefile($filename){
        try {
            $filename = base64_decode($filename);
            \File::delete(public_path().$filename);
            $success = new \stdClass();
            $success->{md5($filename)} = true;
            return \Response::json(array('files'=> array($success)), 200);
        } catch(\Exception $exception){
            // Return error
            return \Response::json($exception->getMessage(), 400);
        }
    }
	
	public function destroy(Request $request,$id)
    {
		$email = Sendbox::where('id',$id)->first();
        if(!Auth::user()->admin){
            $email->where('user_id',$this->getUserId());
        }
		
        $email = Sendbox::where('id',$id)->where('status','Draft');
		if(!Auth::user()->admin){
            $email = $email->where('user_id',$this->getUserId());
        }
		$result = $email->delete();
		if($result){
        $request->session()->flash('success_message','Delete Draft Success');
		}else{
		$request->session()->flash('error_message','Delete Draft Failed');
		}
        return redirect()->back()->withInput();
    }
	
	
	public function changeStatus(Request $request,$id)
    {
		$id =intval($id);
		$email = Sendbox::where('id',$id)->where('status','Waiting');
        if(!Auth::user()->admin){
            $email->where('user_id',$this->getUserId());
        }
        $result = $email->update(['status'=>'Draft']);
		if($result){
        	$request->session()->flash('success_message','Withdraw Success');
		}else{
			$request->session()->flash('error_message','Withdraw Failed');
		}
        return redirect()->back()->withInput();
    }


    public function batchUpdate()
    {
        set_time_limit(0);
        DB::beginTransaction();
        try{
            $submitCount = count($_REQUEST["id"]);
            $emails = Sendbox::whereIn('id',$_REQUEST["id"]); 
            if(array_get($_REQUEST,"confirmStatus") == 'Waiting'){
                $successCount = $emails->whereRaw("((status='Waiting' and error_count>0) or status='Draft') ")->update(
                    [
                        'status'=>'Waiting',
                        'error'=>NULL,
                        'error_count'=>0,
                    ]
                );
            }
            if(array_get($_REQUEST,"confirmStatus") == 'Draft'){
                $successCount = $emails->where('status','Waiting')->update(
                    [
                        'status'=>'Draft',
                        'error'=>NULL,
                        'error_count'=>0,
                    ]
                );
            }
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $successCount.'条记录更新成功!<BR>'; 
            if($submitCount - $successCount>0) $records["customActionMessage"] .= ($submitCount - $successCount).'条记录更新失败: 非自有记录或状态已改变!';     
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);  
    }
	
    public function store(Request $request)
    {
		if(!Auth::user()->can(['compose'])) die('Permission denied -- compose');
        $file = $request->file('files');
        if($file) {
            try {
                $file_name = $file[0]->getClientOriginalName();
                $file_size = $file[0]->getSize();
                $file_ex = $file[0]->getClientOriginalExtension();
                $newname = $file_name ;
                $newpath = '/uploads/'.date('Ym').'/'.date('d').'/'.date('His').rand(100,999).intval(Auth::user()->id).'/';
                $file[0]->move(public_path().$newpath,$newname);
            } catch (\Exception $exception) {
                $error = array(
                    'name' => $file[0]->getClientOriginalName(),
                    'size' => $file[0]->getSize(),
                    'error' => $exception->getMessage(),
                );
                // Return error
                return \Response::json($error, 400);
            }

            // If it now has an id, it should have been successful.
            if (file_exists(public_path().$newpath.$newname)) {
                $newurl = $newpath . $newname;
                $success = new \stdClass();
                $success->name = $newname;
                $success->size = $file_size;
                $success->url = $newurl;
                $success->thumbnailUrl = $newurl;
                $success->deleteUrl = url('send/deletefile/' . base64_encode($newpath . $newname));
                $success->deleteType = 'get';
                $success->fileID = md5($newpath . $newname);
                return \Response::json(array('files' => array($success)), 200);
            } else {
                return \Response::json('Error', 400);
            }
            return \Response::json('Error', 400);
        }

        $this->validate($request, [
            'from_address' => 'required|string',
            'to_address' => 'required|string',
            'subject' => 'required|string',
            'content' => 'required|string',
            'user_id' => 'required|int',
        ]);
		if($request->get('fileid')){
			$up_attachs = $request->get('fileid');
			foreach( $up_attachs as $up_attach){
				if (!file_exists(public_path().$up_attach)){
					$request->session()->flash('error_message','Attachments does not exist, please re-upload!');
            		return redirect()->back()->withInput();
				}
			}
			$attachs = serialize($request->get('fileid'));
		}
		$to_address_array = explode(';',str_replace("；",";",$request->get('to_address')));

		foreach($to_address_array as $to_address){
			if(trim($to_address)){
				if($request->get('sendbox_id')){
					$sendbox = Sendbox::findOrFail($request->get('sendbox_id'));
				}else{
					$sendbox = new Sendbox;
				}
				$sendbox->user_id = intval(Auth::user()->id);
				$sendbox->from_address = trim($request->get('from_address'));
				$sendbox->to_address = substr(trim($to_address),0,99);
				$sendbox->subject = $request->get('subject');
				$content = $request->get('content');
				//去nonctg那边查看是否有此邮箱的数据(因为nonctg那边有现成的用户名)，如果有此邮箱的数据，把{CUSTOMER_NAME}替换成用户姓名
				$dataRow = NonCtg::selectRaw('name')->where('email',$sendbox->to_address)->limit(1)->first();
				if($dataRow){
					$name = $dataRow['name'];
				}else{
					$name = '';
				}
				//去ctg那边查看是否有此邮箱的数据(因为ctg那边有现成的用户名)，如果有此邮箱的数据，把{CUSTOMER_NAME}替换成用户姓名
				if(empty($name)){
					$dataRow = Ctg::selectRaw('name')->where('email',$sendbox->to_address)->limit(1)->first();
					if($dataRow){
						$name = $dataRow['name'];
					}else{
						$dataRow = B1g1::selectRaw('name')->where('email',$sendbox->to_address)->limit(1)->first();
						if($dataRow){
							$name = $dataRow['name'];
						}else{
							$dataRow = Cashback::selectRaw('name')->where('email',$sendbox->to_address)->limit(1)->first();
							if($dataRow){
								$name = $dataRow['name'];
							}else{
								$name = '';
							}
						}
					}
				}
				//去CRM那边查看是否有此邮箱的数据(因为CRM那边有现成的用户名)，如果有此邮箱的数据，把{CUSTOMER_NAME}替换成用户姓名
				if(empty($name)) {
					$dataRow = DB::table('client_info')->where('email', $sendbox->to_address)->first();
					if ($dataRow) {
						$name = $dataRow->name;
					} else {
						$name = '';
					}
				}

				$content = str_replace("{CUSTOMER_NAME}",$name,$content);
				$sendbox->subject = str_replace("{CUSTOMER_NAME}",$name,$sendbox->subject);

				$sendbox->text_html = $content;
				$sendbox->date = date('Y-m-d H:i:s');
				$sendbox->plan_date = ($request->get('plan_date'))?strtotime($request->get('plan_date')):0;
				$sendbox->status = $request->get('asDraft')?'Draft':'Waiting';
				$sendbox->inbox_id = $request->get('inbox_id')?intval($request->get('inbox_id')):0;
				$sendbox->warn = $request->get('warn')?intval($request->get('warn')):0;
				$sendbox->ip = $_SERVER["REMOTE_ADDR"];
				$sendbox->attachs = ($request->get('fileid'))?$attachs:Null;
				$sendbox->error = NULL;
				$sendbox->error_count = 0;
				$sendbox->save();

				NonCtg::where('email',$sendbox->to_address)->where('processor',intval(Auth::user()->id))->where('status',0)->update(array('status'=>4));
			}
		}
        
        
        if ($sendbox->id) {
            $request->session()->flash('success_message','Save Email Success');
            if($request->get('inbox_id')){
				if(!$request->get('asDraft')){
                	Inbox::where('id',intval($request->get('inbox_id')))->update(['reply'=>2]);
				}
                return redirect('inbox/'.$request->get('inbox_id'));
            }else{
                return redirect('send/'.$sendbox->id);
            }
            //return redirect('inbox/'.$request->get('inbox_id'));
        } else {
            $request->session()->flash('error_message','Set Email Failed');
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
		if(!Auth::user()->can(['sendbox-show'])) die('Permission denied -- sendbox-show');
        $email = Sendbox::where('id',$id)->first();

        $email->toArray();
		$email_from_history = Inbox::where('date','<',$email['date'])->where('from_address',$email['to_address'])->where('to_address',$email['from_address'])
        ->take(10)->orderBy('date','desc')->get()->toArray();
		
        $email_to_history = Sendbox::where('date','<',$email['date'])->where('from_address',$email['from_address'])->where('to_address',$email['to_address'])->take(10)->orderBy('date','desc')->get()->toArray();
		
        $email_history[strtotime($email['date'])] = $email;
		
		$account = Accounts::where('account_email',$email['from_address'])->first();
		$account_type = '';
		if($account){
			$account_type = $account->type;
		}

		
		$amazon_order_id='';
		$i=0;
		foreach($email_from_history as $mail){
			$i++;
			if($i==1){
				$amazon_order_id=$mail['amazon_order_id'];
				$amazon_seller_id=$mail['amazon_seller_id'];
				$email['mark']=$mail['mark'];
				$email['sku']=$mail['sku'];
				$email['asin']=$mail['asin'];
				$email['etype']=$mail['etype'];
				$email['remark']=$mail['remark'];
				$email['reply']=$mail['reply'];
				$email['from_name']=$mail['from_name'];
			}
            $key = strtotime($mail['date']);
            while(key_exists($key,$email_history)){
                $key--;
            }
            $email_history[$key] = $mail;
        }

        foreach($email_to_history as $mail){
            $key = strtotime($mail['date']);
            while(key_exists($key,$email_history)){
                $key--;
            }
            $email_history[$key] = $mail;
        }
        krsort($email_history);
		
		$order=array();
		if($amazon_order_id){
			if(!$amazon_seller_id) $amazon_seller_id = Accounts::where('account_email',$email['from_address'])->value('account_sellerid');
            $order = DB::table('amazon_orders')->where('SellerId', $amazon_seller_id)->where('AmazonOrderId', $amazon_order_id)->first();
            if($order) $order->item = DB::table('amazon_orders_item')->where('SellerId', $amazon_seller_id)->where('AmazonOrderId', $amazon_order_id)->get();
        }
		return view('send/view',['email_history'=>$email_history,'order'=>$order,'email'=>$email,'users'=>$this->getUsers(),'accounts'=>$this->gAccounts(),'account_type'=>$account_type]);
    }
    public function get()
    {
        /*
   * Paging
   */
		if(!Auth::user()->can(['sendbox-show'])) die('Permission denied -- sendbox-show');
        $orderby = 'date';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'from_address';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'to_address';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'subject';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'date';
            $sort = $_REQUEST['order'][0]['dir'];
        }
        /*
        if (isset($_REQUEST["customActionType"]) && $_REQUEST["customActionType"] == "group_action") {
            Inbox::where('user_id',$this->getUserId())->whereIN('id',$_REQUEST["id"])->update(['reply'=>$_REQUEST["customActionName"]]);

            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Group action successfully has been completed. Well done!"; // pass custom message(useful for getting status of group actions)
        }
        */

            $customers = new Sendbox;



        if(array_get($_REQUEST,'status')){
            if(array_get($_REQUEST,'status')=='Waiting'){
                $customers = $customers->where('status',$_REQUEST['status'])->where('error_count',0);
            }
            if(array_get($_REQUEST,'status')=='Error'){
                $customers = $customers->where('status','Waiting')->where('error_count','>',0);
            }
            if(array_get($_REQUEST,'status')=='Send' || array_get($_REQUEST,'status')=='Draft'){
                $customers = $customers->where('status',$_REQUEST['status']);
            }
        }
		
		if(array_get($_REQUEST,'user_id')){
            $customers = $customers->where('user_id',$_REQUEST['user_id']);
        }
        if(array_get($_REQUEST,'from_address')){
            $customers = $customers->where('from_address', 'like', '%'.$_REQUEST['from_address'].'%');
        }
        if(array_get($_REQUEST,'to_address')){
            $customers = $customers->where('to_address', 'like', '%'.$_REQUEST['to_address'].'%');
        }

        if(array_get($_REQUEST,'subject')){
            $customers = $customers->where('subject', 'like', '%'.$_REQUEST['subject'].'%');
        }
        if(array_get($_REQUEST,'date_from')){
            $customers = $customers->where('date','>=',$_REQUEST['date_from'].' 00:00:00');
        }
        if(array_get($_REQUEST,'date_to')){
            $customers = $customers->where('date','<=',$_REQUEST['date_to'].' 23:59:59');
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
		$users = $this->getUsers();

        foreach ( $customersLists as $customersList){
            if($customersList['send_date']){
                $status = '<span class="label label-sm label-success">'.$customersList['send_date'].'</span> ';
            }elseif($customersList['status']=='Waiting' && $customersList['error_count']>0){
                $status = '<span class="label label-sm label-danger">Error</span> ';
            }else{
                $status = '<span class="label label-sm label-warning">'.$customersList['status'].'</span> ';
            }
            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList['id'].'"/><span></span></label>',
                $customersList['from_address'],
                $customersList['to_address'],
                '<a href="/send/'.$customersList['id'].'" style="color:#333;" target="_blank">'.$customersList['subject'].'</a>',
                $customersList['date'],
				array_get($users,$customersList['user_id']),
                $status,

				'<a href="/send/'.$customersList['id'].'" target="_blank">
                                        <button type="submit" class="btn btn-success btn-xs">View</button>
                                    </a>'.(($customersList['status']=='Draft')?'
                                    <form action="'.url('send/'.$customersList['id']).'" method="POST" style="display: inline;">
                                        '.method_field('DELETE').'
                                        '.csrf_field().'
                                        <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                    </form>':''),
            );
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }

}