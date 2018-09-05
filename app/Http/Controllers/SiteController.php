<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inbox;
use App\Sendbox;
use App\Accounts;
use Illuminate\Support\Facades\Session;

use App\User;
use App\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
class SiteController extends Controller
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
        return view('site/index',['users'=>$this->getUsers()]);
		

    }

    function change(Request $request){

       $id = intval($request->get('inbox_id'));
       if($id){
           $inbox = Inbox::findOrFail($id);
           $inbox->reply = intval($request->get('reply'));
           $inbox->etype = $request->get('etype');
           $inbox->remark = $request->get('remark');
		   $inbox->mark = $request->get('mark');
           $inbox->sku = strtoupper($request->get('sku'));
		   $inbox->asin = strtoupper($request->get('asin'));
		   $inbox->epoint = strtoupper($request->get('epoint'));
		   $inbox->item_no = strtoupper($request->get('item_no'));
           $inbox->user_id = intval($request->get('user_id'));

           if ($inbox->save()) {
               $request->session()->flash('success_message','Save Mail Success');
               return redirect('site/'.$id);
           } else {
               $request->session()->flash('error_message','Set Mail Failed');
               return redirect()->back()->withInput();
           }
       }


    }

    public function show($id)
    {

        $email = Inbox::where('id',$id)->first();
        if(!Auth::user()->admin){
            $email->where('user_id',$this->getUserId());
        }
        //$email->toArray();
        if($email){
            $email->read = 1;
            $email->save();
            $email = $email->toArray();
        }else{
            die();
        }
        $email_from_history = Inbox::where('date','<',$email['date'])->where('from_address',$email['from_address'])->where('to_address',$email['to_address'])
        ->take(10)->orderBy('date','desc')->get()->toArray();
        $email_to_history = Sendbox::where('date','<',$email['date'])->where('from_address',$email['to_address'])->where('to_address',$email['from_address'])->take(10)->orderBy('date','desc')->get()->toArray();
        $email_history[strtotime($email['date'])] = $email;

        $email_to = Sendbox::where('inbox_id',$id)->orderBy('date','asc')->get()->toArray();
        $order=array();
        if($email['amazon_order_id']){
            $account = Accounts::where('account_email',$email['to_address'])->first();
            if($account) $order = DB::table('amazon_orders')->where('SellerId', $account['account_sellerid'])->where('AmazonOrderId', $email['amazon_order_id'])->first();
            if($order) $order->item = DB::table('amazon_orders_item')->where('SellerId', $account['account_sellerid'])->where('AmazonOrderId', $email['amazon_order_id'])->get();
        }
        foreach($email_to as $mail){
            $key = strtotime($mail['date']);
            while(key_exists($key,$email_history)){
                $key++;
            }
            $email_history[$key] = $mail;
        }

        foreach($email_from_history as $mail){
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
        return view('site/view',['email_history'=>$email_history,'order'=>$order,'email'=>$email,'users'=>$this->getUsers(),'accounts'=>$this->getAccounts()]);
    }
    public function get(Request $request)
    {
        /*
   * Paging
   */

        $orderby = 'date';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'from_address';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'to_address';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'subject';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'date';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'reply';
            if($_REQUEST['order'][0]['column']==6) $orderby = 'user_id';
            $sort = $_REQUEST['order'][0]['dir'];
        }

        if (isset($_REQUEST["customActionType"]) && $_REQUEST["customActionType"] == "group_action") {
            $updateDate=array();
            if(isset($_REQUEST['replyStatus']) && $_REQUEST['replyStatus']!=''){
                $updateDate['reply'] = $_REQUEST['replyStatus'];
            }
            if(array_get($_REQUEST,"giveUser")){
                $updateDate['user_id'] = array_get($_REQUEST,"giveUser");
            }
            //print_r($_REQUEST["id"]);
           // print_r($_REQUEST['replyStatus']);
            //print_r($_REQUEST['giveUser']);
            //die();
            if(Auth::user()->admin){
                $updatebox = new Inbox;
            }else{
                $updatebox = Inbox::where('user_id',$this->getUserId());
            }
            $updatebox->whereIN('id',$_REQUEST["id"])->update($updateDate);
            //$request->session()->flash('success_message','Group action successfully has been completed. Well done!');
            //$records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
           // $records["customActionMessage"] = "Group action successfully has been completed. Well done!"; // pass custom message(useful for getting status of group actions)
            unset($updateDate);
        }
        if(Auth::user()->admin){
            $customers = new Inbox;
        }else{
            $customers = Inbox::where('user_id',$this->getUserId());
        }

        if(isset($_REQUEST['reply']) && $_REQUEST['reply']!=''){
            $customers = $customers->where('reply', $_REQUEST['reply']);
        }
        if(Auth::user()->admin) {
            if (array_get($_REQUEST, 'user_id')) {
                $customers = $customers->where('user_id', $_REQUEST['user_id']);
            }
        }
		
        if(array_get($_REQUEST,'from_address')){
            $customers = $customers->where('from_address', 'like', '%'.$_REQUEST['from_address'].'%');
        }
        if(array_get($_REQUEST,'to_address')){
            $customers = $customers->where('to_address', 'like', '%'.$_REQUEST['to_address'].'%');
        }
		
		if(array_get($_REQUEST,'mark')){
            $customers = $customers->where('mark', $_REQUEST['mark']);
        }
        if(array_get($_REQUEST,'subject')){
            //$customers = $customers->where('subject', 'like', '%'.$_REQUEST['subject'].'%');
            $keywords = array_get($_REQUEST,'subject');
            $customers = $customers->where(function ($query) use ($keywords) {

                $query->where('subject'  , 'like', '%'.$keywords.'%')
                        ->orwhere('remark', 'like', '%'.$keywords.'%')
                        ->orwhere('sku', 'like', '%'.$keywords.'%')
                        ->orwhere('etype', 'like', '%'.$keywords.'%');

            });
        }
        if(array_get($_REQUEST,'date_from')){
            $customers = $customers->where('date','>=',$_REQUEST['date_from'].' 00:00:00');
        }
        if(array_get($_REQUEST,'date_to')){
            $customers = $customers->where('date','<=',$_REQUEST['date_to'].' 23:59:59');
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
        $rules = $this->getRules();
        $status_list[2] = "<span class=\"label label-sm label-success\">Replied</span>";
        $status_list[1] = "<span class=\"label label-sm label-warning\">Do not need to reply</span>";
        $status_list[0] = "<span class=\"label label-sm label-danger\">Need reply</span>";
        for($i = $iDisplayStart; $i < $end; $i++) {
            $warnText = '';
            if($customersList[$i]['reply']==0){
                if(array_get($rules,$customersList[$i]['rule_id'],'')){
                    $warnText = $this->time_diff(strtotime(date('Y-m-d H:i:s')), strtotime('+ '.array_get($rules,$customersList[$i]['rule_id']),strtotime($customersList[$i]['date'])));
                }
            }

            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList[$i]['id'].'"/><span></span></label>',
                $customersList[$i]['from_address'],
                $customersList[$i]['to_address'],
                (($customersList[$i]['mark'])?'<span class="label label-sm label-danger">'.$customersList[$i]['mark'].'</span> ':'').(($customersList[$i]['sku'])?'<span class="label label-sm label-primary">'.$customersList[$i]['sku'].'</span> ':'').(($customersList[$i]['etype'])?'<span class="label label-sm label-danger">'.$customersList[$i]['etype'].'</span> ':'').'<a href="/inbox/'.$customersList[$i]['id'].'" target="_blank" style="color:#333;">'.(($customersList[$i]['read'])?'':'<strong>').$customersList[$i]['subject'].(($customersList[$i]['read'])?'':'</strong>').'</a>'.(($warnText)?'<span class="label label-sm label-danger">'.$warnText.'</span> ':'').(($customersList[$i]['remark'])?'<BR/><span class="label label-sm label-info">'.$customersList[$i]['remark'].'</span> ':''),
                $customersList[$i]['date'],
                $status_list[$customersList[$i]['reply']],
                $users[$customersList[$i]['user_id']],
                '<a href="/inbox/'.$customersList[$i]['id'].'" class="btn btn-sm btn-outline grey-salsa" target="_blank"><i class="fa fa-search"></i> View </a>',
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

    public function getAccounts(){
        $accounts = Accounts::get()->toArray();
        $accounts_array = array();
        foreach($accounts as $account){
            $accounts_array[strtolower($account['account_email'])] = $account['account_name'];
        }
        return $accounts_array;
    }

    public function getRules(){
        $rules = Rule::get()->toArray();
        $rules_array = array();
        foreach($rules as $rule){
            $rules_array[$rule['id']] = trim($rule['timeout']);
        }
        return $rules_array;
    }

    public function time_diff($timestamp1, $timestamp2)
    {

        if ($timestamp2 <= $timestamp1)
        {
            return 'TimeOut';
        }
        $timediff = $timestamp2 - $timestamp1;
        // 时
        $days = intval($timediff/86400);
        if( $days>0 ) return $days.'Days Left';

        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        if( $hours>0 ) return $hours.'Hours Left';
        // 分
        $remain = $timediff%3600;
        $mins = intval($remain/60);
        if( $mins>0 ) return $mins.'Mins Left';
        // 秒
        $secs = $remain%60;
        if( $secs>0 ) return $secs.'Secs Left';
        return 'TimeOut';
    }

}