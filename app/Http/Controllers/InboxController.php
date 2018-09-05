<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inbox;
use App\Sendbox;
use App\Accounts;
use Illuminate\Support\Facades\Session;

use App\User;
use App\Group;
use App\Groupdetail;
use App\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
class InboxController extends Controller
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
    public function index($type = '')
    {

        return view('inbox/index',['users'=>$this->getUsers(),'groups'=>$this->getGroups(),'type'=>$type,'mygroups'=>$this->getUserGroup()]);
		

    }

    public function create()
    {
		
        return view('account/add');
    }

    function change(Request $request){

       $id = intval($request->get('inbox_id'));
       if($id){
           $inbox = Inbox::findOrFail($id);
           $inbox->reply = intval($request->get('reply'));
           if($request->get('etype')) $inbox->etype = $request->get('etype');
           if($request->get('remark')) $inbox->remark = $request->get('remark');
		   if($request->get('mark')) $inbox->mark = $request->get('mark');
           if($request->get('sku')) $inbox->sku = strtoupper($request->get('sku'));
		   if($request->get('asin')) $inbox->asin = strtoupper($request->get('asin'));
		   if($request->get('epoint')) $inbox->epoint = strtoupper($request->get('epoint'));
		   if($request->get('item_no')) $inbox->item_no = strtoupper($request->get('item_no'));
		   $change_user=false;
           if($request->get('user_id')){
			   $user_str = $request->get('user_id');
			   $user = explode('_',$user_str);
			   if($inbox->user_id != array_get($user,1)){
			   		$change_user=true;
			   }
			   $inbox->group_id = array_get($user,0);
			   $inbox->user_id =  array_get($user,1);
		   }
           if ($inbox->save()) {
		   	    if($change_user){
					DB::table('inbox_change_log')->insert(array(
							'inbox_id'=>$id,
							'to_user_id'=>$inbox->user_id,
							'user_id'=>Auth::user()->id,
							'date'=>date('Y-m-d H:i:s')
						));
			   }	
               $request->session()->flash('success_message','Save Mail Success');
               return redirect('inbox/'.$id);
           } else {
               $request->session()->flash('error_message','Set Mail Failed');
               return redirect()->back()->withInput();
           }
       }


    }
	
	


    public function show($id)
    {
        $email = Inbox::where('id',$id)->first();

        //$email->toArray();
		if($email->user_id == $this->getUserId()){
			$email->read = 1;
            $email->save();
		}

		$email = $email->toArray();
		
		$email_unread_history = Inbox::where('id','<>',$id)->where('reply',0)->where('from_address',$email['from_address'])->where('to_address',$email['to_address'])->take(10)->orderBy('date','desc')->get();
		
        $email_from_history = Inbox::where('date','<',$email['date'])->where('from_address',$email['from_address'])->where('to_address',$email['to_address'])
        ->take(10)->orderBy('date','desc')->get()->toArray();
        $email_to_history = Sendbox::where('status','<>','Draft')->where('date','<',$email['date'])->where('from_address',$email['to_address'])->where('to_address',$email['from_address'])->take(10)->orderBy('date','desc')->get()->toArray();
        $email_history[strtotime($email['date'])] = &$email;

        $email_to = Sendbox::where('inbox_id',$id)->orderBy('date','asc')->get()->toArray();
        $order=array();
		$account = Accounts::where('account_email',$email['to_address'])->first();
		$account_type = $account->type;
        if($email['amazon_order_id']){
			$amazon_seller_id = $email['amazon_seller_id'];
			
			if(!$amazon_seller_id){
            	$amazon_seller_id = $account->account_sellerid;
			}
            $order = DB::table('amazon_orders')->where('SellerId', $amazon_seller_id)->where('AmazonOrderId', $email['amazon_order_id'])->first();
            if($order) $order->item = DB::table('amazon_orders_item')->where('SellerId', $amazon_seller_id)->where('AmazonOrderId', $email['amazon_order_id'])->get();
        }
		$i=0;
        foreach($email_to as $mail){
			$i++;
			if($i==1 && $mail['status']=='Draft'){
				$email['draftId']=$mail['id'];
				$email['draftSubject']=$mail['subject'];
				$email['draftHtml']=$mail['text_html'];
				$email['draftAttachs']=$mail['attachs'];
			}
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
        return view('inbox/view',['email_history'=>$email_history,'unread_history'=>$email_unread_history,'order'=>$order,'email'=>$email,'users'=>$this->getUsers(),'groups'=>$this->getGroups(),'sellerids'=>$this->getSellerIds(),'accounts'=>$this->getAccounts(),'account_type'=>$account_type]);
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
				$user_str = array_get($_REQUEST,"giveUser");
				$user = explode('_',$user_str);
				$updateDate['group_id'] = array_get($user,0);
				$updateDate['user_id'] =  array_get($user,1);

            }
			if(array_get($_REQUEST,"giveMark")){
                $updateDate['mark'] = array_get($_REQUEST,"giveMark");
            }
            //print_r($_REQUEST["id"]);
           // print_r($_REQUEST['replyStatus']);
            //print_r($_REQUEST['giveUser']);
            //die();
            //if(Auth::user()->admin){
            $updatebox = new Inbox;
            //}else{
             //   $updatebox = Inbox::where('user_id',$this->getUserId());
           // }
            $up_result = $updatebox->whereIN('id',$_REQUEST["id"])->update($updateDate);
			if($up_result && array_get($_REQUEST,"giveUser")){
				foreach($_REQUEST["id"] as $up_id){
					DB::table('inbox_change_log')->insert(array(
						'inbox_id'=>$up_id,
						'to_user_id'=>$updateDate['user_id'],
						'user_id'=>Auth::user()->id,
						'date'=>date('Y-m-d H:i:s')
					));
				}
			}
            //$request->session()->flash('success_message','Group action successfully has been completed. Well done!');
            //$records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
           // $records["customActionMessage"] = "Group action successfully has been completed. Well done!"; // pass custom message(useful for getting status of group actions)
            unset($updateDate);
        }
        if(Auth::user()->admin){
            $customers = new Inbox;
        }else{
            $customers = Inbox::whereIn('group_id',array_get($this->getUserGroup(),'groups',array()));
        }
		if(array_get($_REQUEST,'show_all')=='show_all') $customers = new Inbox;
		
		if(array_get($_REQUEST,'mail_type')){
            $customers = $customers->where('type', array_get($_REQUEST,'mail_type'));
        }
        if(isset($_REQUEST['reply']) && $_REQUEST['reply']!=''){
            $customers = $customers->where('reply', $_REQUEST['reply']);
        }
        //if(Auth::user()->admin) {
		
			if (array_get($_REQUEST, 'group_id')) {
				
                $customers = $customers->where('group_id', array_get($_REQUEST, 'group_id'));

            }
            if (array_get($_REQUEST, 'user_id')) {
				$customers = $customers->where('user_id',  array_get($_REQUEST, 'user_id'));
            }
        //}
		
        if(array_get($_REQUEST,'from_address')){
            //$customers = $customers->where('from_address', 'like', '%'.$_REQUEST['from_address'].'%');
			
			$keywords = array_get($_REQUEST,'from_address');
            $customers = $customers->where(function ($query) use ($keywords) {
                $query->where('from_address'  , 'like', '%'.$keywords.'%')
                        ->orwhere('from_name', 'like', '%'.$keywords.'%');

            });
			
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
                        ->orwhere('etype', 'like', '%'.$keywords.'%')
						->orwhere('text_html', 'like', '%'.$keywords.'%')
						->orwhere('text_plain', 'like', '%'.$keywords.'%');

            });
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
        $users = $this->getUsers();
		$groups = $this->getGroups();
        $rules = $this->getRules();
        $status_list[2] = "<span class=\"label label-sm label-success\">Replied</span>";
        $status_list[1] = "<span class=\"label label-sm label-warning\">Do not need to reply</span>";
        $status_list[0] = "<span class=\"label label-sm label-danger\">Need reply</span>";
		
		foreach ( $customersLists as $customersList){
			$warnText = '';
            if($customersList['reply']==0){
                if(array_get($rules,$customersList['rule_id'],'')){
                    $warnText = $this->time_diff(strtotime(date('Y-m-d H:i:s')), strtotime('+ '.array_get($rules,$customersList['rule_id']),strtotime($customersList['date'])));
                }
            }

            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList['id'].'"/><span></span></label>',
                
				$customersList['from_address'].'</BR>'.(array_get($customersList,'from_name')?'<span class="label label-sm label-primary">'.array_get($customersList,'from_name').'</span> ':' ').$status_list[$customersList['reply']],
                $customersList['to_address'].'</BR>'.'<span class="label label-sm label-primary">'.array_get($groups,$customersList['group_id'].'.group_name').' - '.array_get($users,$customersList['user_id']).'</span> ',
                (($customersList['mark'])?'<span class="label label-sm label-danger">'.$customersList['mark'].'</span> ':'').(($customersList['sku'])?'<span class="label label-sm label-primary">'.$customersList['sku'].'</span> ':'').(($customersList['etype'])?'<span class="label label-sm label-danger">'.$customersList['etype'].'</span> ':'').'<a href="/inbox/'.$customersList['id'].'" target="_blank" style="color:#333;">'.(($customersList['read'])?'':'<strong>').$customersList['subject'].(($customersList['read'])?'':'</strong>').'</a>'.(($warnText)?'<span class="label label-sm label-danger">'.$warnText.'</span> ':'').(($customersList['remark'])?'<BR/><span class="label label-sm label-info">'.$customersList['remark'].'</span> ':''),
                $customersList['date'],
                
                '<a href="/inbox/'.$customersList['id'].'" class="btn btn-sm btn-outline grey-salsa" target="_blank"><i class="fa fa-search"></i> View </a>',
            );
		}
        /*for($i = $iDisplayStart; $i < $end; $i++) {
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
		*/


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
	
	public function getpdfinvoice(Request $request,$id){
		$email = Inbox::where('id',$id)->first()->toArray();
		if($email['amazon_order_id']){
            $amazon_seller_id = $email['amazon_seller_id'];
			if(!$amazon_seller_id){
            	$amazon_seller_id = Accounts::where('account_email',$email['to_address'])->value('account_sellerid');
			}
            $order = DB::table('amazon_orders')->where('SellerId', $amazon_seller_id)->where('AmazonOrderId', $email['amazon_order_id'])->first();
            if($order) $order->item = DB::table('amazon_orders_item')->where('SellerId', $amazon_seller_id)->where('AmazonOrderId', $email['amazon_order_id'])->get();
        }
		
									
		
		$title = 'COMMERCIAL INVOICE';
		$from = 'FROM';
		$invoiceno= 'INVOICE NO.';
		$to = 'TO';
		$invoicedate= 'INVOICE DATE';
		$saledate = 'SALE  DATE';
		$gooddes = 'DESCRIPTION OF GOODS';
		$qty = 'QTY.';
		$price= 'NET UNIT PRICE';
		$linetotal = 'LINE NET TOTAL';
		$saletax= 'SALES TAX';
		$shippingfee = 'SHIPPING FEE';
		$promotions ='PROMOTIONS';
		$total = 'TOTAL';
		$currency = '$';
		$taxid='wh1amz+30201503310101';
		$taxpoint = 0;
		$saletaxpoint = 'Tax Rate';
		$taxhtml = '';
		
		
		if(strripos($order->BuyerEmail,'marketplace.amazon.co.uk')!==false){
			$taxid='wh1amz+30201503310101';
			$currency = '£';
			$taxpoint = 0.19;
		}
		
		if(strripos($order->BuyerEmail,'marketplace.amazon.it')!==false){
			$taxid='wh1amz+10201503120110';
			$title = 'COMMERCIAL INVOICE';
			$from = 'FROM';
			$invoiceno= 'INVOICE NO.';
			$to = 'TO';
			$invoicedate= 'INVOICE DATE';
			$saledate = 'SALE  DATE';
			$gooddes = 'DESCRIPTION OF GOODS';
			$qty = 'QTY.';
			$price= 'NET UNIT PRICE';
			$linetotal = 'LINE NET TOTAL';
			$saletax= 'SALES TAX';
			$shippingfee = 'SHIPPING FEE';
			$promotions ='PROMOTIONS';
			$total = 'TOTAL';
			$currency = '€';
			$taxpoint = 0.22;
		

		}
		
		if(strripos($order->BuyerEmail,'marketplace.amazon.fr')!==false){
			$taxid='wh1amz+30201503310101';
			$title = 'COMMERCIAL INVOICE';
			$from = 'FROM';
			$invoiceno= 'INVOICE NO.';
			$to = 'TO';
			$invoicedate= 'INVOICE DATE';
			$saledate = 'SALE  DATE';
			$gooddes = 'DESCRIPTION OF GOODS';
			$qty = 'QTY.';
			$price= 'NET UNIT PRICE';
			$linetotal = 'LINE NET TOTAL';
			$saletax= 'SALES TAX';
			$shippingfee = 'SHIPPING FEE';
			$promotions ='PROMOTIONS';
			$total = 'TOTAL';
			$currency = '€';
			$taxpoint = 0.20;
		}
		
		if(strripos($order->BuyerEmail,'marketplace.amazon.de')!==false){
			$taxid='wh1amz+10201602120106';
			$title = 'HANDELSRECHNUNG';
			$from = 'VON';
			$invoiceno= 'RECHNUNG NR.';
			$to = 'ZU';
			$invoicedate= 'RECHNUNG DATUM';
			$saledate = 'VERKAUFSDATUM';
			$gooddes = 'BERSCHREIBUNG DES PRODUKT';
			$qty = 'QTY.';
			$price= 'NETTO PREIS';
			$linetotal = 'GESAMTPREIS';
			$saletax= 'VERKAUFSTEUER ';
			$shippingfee = 'Versandgebühr';
			$promotions ='Sonderangebote';
			$total = 'ZUSAMMEN';
			$currency = '€';
			$taxpoint = 0.19;
			$saletaxpoint ='Mehrwertsteuersatz';
		}
		
		if(strripos($order->BuyerEmail,'marketplace.amazon.es')!==false){
			$taxid = 'wh1amz+10201611220104';
			$title = 'Factura comercial';
			$from = 'FROM DE';
			$invoiceno= 'Nº de factura';
			$to = 'PARA';
			$invoicedate= 'Fecha de envio';
			$saledate = 'Fecha de venta';
			$gooddes = 'Descripción del producto';
			$qty = 'Cantidad';
			$price= 'Precio unitario';
			$linetotal = 'Precio total';
			$saletax= 'IVA';
			$shippingfee = 'Gastos de envio';
			$promotions ='PROMOCIONES';
			$total = 'TOTAL';
			$currency = '€';
			$taxpoint = 0.21;
			$saletaxpoint ='Tipo de IVA';
		}
		
		
		$linedetails =''; $saletaxvalue = $shippingfeevalue= $promotionsvalue = 0;
		foreach($order->item as $item){ 
			$linedetails.= '
			<tr>
				<td style="padding:10px;font-size:12px;font-family:arial;">'.substr($order->PurchaseDate,0,10).'</td>
				<td style="padding:10px;font-size:12px;font-family:arial;">'.$item->Title.'</td>
				<td style="padding:10px;font-size:12px;font-family:arial;">'.$item->QuantityOrdered.'</td>
				<td style="padding:10px;font-size:12px;font-family:arial;">'.$currency.' '.round($item->ItemPriceAmount/$item->QuantityOrdered,2).'</td>
				<td style="padding:10px;font-size:12px;font-family:arial;">'.$currency.' '.(round($item->ItemPriceAmount,2)+round($item->ItemTaxAmount,2)+round($item->GiftWrapPriceAmount,2)+round($item->GiftWrapTaxAmount,2)).'</td>
			</tr>
			';
			$saletaxvalue+= round($item->ItemTaxAmount,2);
			$shippingfeevalue+= round($item->ShippingPriceAmount,2)+round($item->ShippingTaxAmount,2)-round($item->ShippingDiscountAmount,2);
			$promotionsvalue+= round($item->PromotionDiscountAmount,2);
		}
		if(!$saletaxvalue && $taxpoint>0){
			$saletaxvalue = round($order->Amount*$taxpoint,2);	
		}

		if($saletaxvalue){
			$taxhtml = '<tr>
   
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$saletax.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$currency.'&nbsp;'.$saletaxvalue.'</td>
  </tr><tr>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$saletaxpoint.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$currency.'&nbsp;'.round($saletaxvalue/$order->Amount*100).'%</td>
  </tr>
  		';
		}
		
		$output = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><table width="720px" border="1" cellspacing="0" cellpadding="0" >
  <tr height="80px">
    <td height="80" colspan="5" align="center" style="padding:10px;font-size:16px;font-family:arial;font-weight:bold;">'.$title.'</td>
  </tr>
  <tr>
    <td width="100" style="padding:10px;font-size:12px;font-family:arial;vertical-align:top;line-height:30px;">'.$from.'</td>
    <td colspan="2" style="padding:10px;font-size:12px;font-family:arial;line-height:30px;">
	<p>UK PRINOVA ENTERPRISE LIMITED</p>		
	<p>Add: 88 KINGSWAY LONDON WC2CB 6AA</p>			
	<p>VAT NO. GB125162934</p>		
	</td>
    <td colspan="2" style="padding:10px;font-size:12px;font-family:arial; vertical-align:top;line-height:30px;"><p>'.$invoiceno.'</p>
    <p>'.$taxid.'</p></td>
  </tr>
  
  <tr>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$to.'</td>
    <td colspan="2" style="padding:10px;font-size:12px;font-family:arial;line-height:30px;">
<pre>'.$order->Name.'
'.$order->AddressLine1.' '.$order->AddressLine2.' '.$order->AddressLine3.'
'.$order->City.' '.$order->StateOrRegion.'
'.$order->CountryCode.'
'.$order->PostalCode.'</pre>
	</td>
    <td colspan="2" style="padding:10px;font-size:12px;font-family:arial;vertical-align:top;line-height:30px;"><p>'.$invoicedate.'</p>
    <p>'.substr($order->PurchaseDate,0,10).'</p></td>
  </tr>

  <tr>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$saledate.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$gooddes.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$qty.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$price.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$linetotal.'</td>
  </tr>
  

 '.$linedetails.'
  
  
  
  <tr>
    <td colspan="3" rowspan="'.(($saletaxvalue>0)?5:3).'">&nbsp;</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$shippingfee.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$currency.'&nbsp;'.$shippingfeevalue.'</td>
  </tr>
  '.$taxhtml.'
 
  <tr>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$promotions.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;"> - '.$currency.'&nbsp;'.$promotionsvalue.'</td>
  </tr>
  
  <tr>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$total.'</td>
    <td style="padding:10px;font-size:12px;font-family:arial;">'.$currency.'&nbsp;'.round($order->Amount,2).'</td>
  </tr>
</table>
';
		
		
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8']);
		$mpdf->allow_charset_conversion = true;
		$mpdf->charset_in = 'utf-8';
		$mpdf->WriteHTML($output);
		$mpdf->Output();
		die();
	}
	
	public function getUserGroup(){
	
		if(Auth::user()->admin){
            $groups = Groupdetail::get(['group_id']);
			$group_arr =array();
			foreach($groups as $group){
				$group_arr['groups'][$group->group_id] = $group->group_id;
			}
			$users = Groupdetail::get(['user_id']);
			foreach($users as $user){
				$group_arr['users'][$user->user_id] = $user->user_id;
			}
			return $group_arr;
        }else{
			$user_id = Auth::user()->id;
            $groups = Groupdetail::where('user_id',$user_id)->get(['group_id']);
			$group_arr =array();
			foreach($groups as $group){
				$group_arr['groups'][$group->group_id] = $group->group_id;
			}
			$users = Groupdetail::whereIn('group_id',array_get($group_arr,'groups',array()))->get(['user_id']);
			foreach($users as $user){
				$group_arr['users'][$user->user_id] = $user->user_id;
			}
			return $group_arr;
			
        }
		
		
		
	}
	
	public function getrfcorder(Request $request){
		$orderid = $request->get('orderid');
		$inboxid = $request->get('inboxid');
		$sellerid = $request->get('sellerid');
		$re = 0;
		$message = $sku = $asin = '';
		/*
		$inbox_email = DB::table('inbox')->where('id', $inboxid)->first();
		$account_email = $inbox_email->to_address;
		if(!$sellerid) $sellerid = $inbox_email->amazon_seller_id;
		if(!$sellerid) $sellerid = DB::table('accounts')->where('account_email', $account_email)->where('type','Amazon')->value('account_sellerid');
		*/
		if(!$message){
			$exists = DB::table('amazon_orders')->where('AmazonOrderId', $orderid);
			if($sellerid){
				$exists = $exists->where('SellerId', $sellerid);
			}
			$exists = $exists->first();
			if(!$exists){
				DB::beginTransaction();
				try{
					$appkey = 'site0001';
					$appsecret= 'testsite0001';
					$array['orderId']=$orderid;
					$array['appid']= $appkey;
					$array['method']='getOrder';
					ksort($array);
					$authstr = "";
					foreach ($array as $k => $v) {
						$authstr = $authstr.$k.$v;
					}
					$authstr=$authstr.$appsecret;
					$sign = strtoupper(sha1($authstr));
					
					$res = file_get_contents('http://116.6.105.153:18003/rfc_site.php?appid='.$appkey.'&sellerid='.$sellerid.'&method=getOrder&orderId='.$orderid.'&sign='.$sign);
					$result = json_decode($res,true);
					
					if(array_get($result,'result')){
						$data  = array_get($result,'data',array());
						$order = $orderItemData = array();
						$sellerid = $data['SELLERID'];
						$order= array(
							'SellerId'=>$data['SELLERID'],
							'MarketPlaceId'=>$data['ZMPLACEID'],
							'AmazonOrderId'=>$data['ZAOID'],
							'SellerOrderId'=>$data['ZSOID'],
							'ApiDownloadDate'=>date('Y-m-d H:i:s',strtotime($data['ALOADDATE'].$data['ALOADTIME'])),
							'PurchaseDate'=>date('Y-m-d H:i:s',strtotime($data['PCHASEDATE'].$data['PCHASETIME'])),
							'LastUpdateDate'=>date('Y-m-d H:i:s',strtotime($data['LUPDATEDATE'].$data['LUPDATETIME'])),
							'OrderStatus'=>$data['ORSTATUS'],
							'FulfillmentChannel'=>$data['FCHANNEL'],
							'SalesChannel'=>$data['SCHANNEL'],
							'OrderChannel'=>$data['OCHANNEL'],
							'ShipServiceLevel'=>$data['SHIPLEVEL'],
							'Name'=>$data['ZNAME'],
							'AddressLine1'=>$data['ADDR1'],
							'AddressLine2'=>$data['ADDR2'],
							'AddressLine3'=>$data['ADDR3'],
							'City'=>$data['ZCITY'],
							'County'=>$data['ZCOUNTRY'],
							'District'=>$data['ZDISTRICT'],
							'StateOrRegion'=>$data['ZSOREGION'],
							'PostalCode'=>$data['ZPOSCODE'],
							'CountryCode'=>$data['ZCOUNTRYCODE'],
							'Phone'=>$data['ZPHONE'],
							'Amount'=>$data['ZAMOUNT'],
							'CurrencyCode'=>$data['ZCURRCODE'],
							'NumberOfItemsShipped'=>$data['NISHIPPED'],
							'NumberOfItemsUnshipped'=>$data['NIUNSHIPPED'],
							'PaymentMethod'=>$data['PMETHOD'],
							'BuyerName'=>$data['BUYNAME'],
							'BuyerEmail'=>$data['BUYEMAIL'],
							'ShipServiceLevelCategory'=>$data['SSCATEGORY'],
							'EarliestShipDate'=>($data['ESDATE']>0)?date('Y-m-d H:i:s',strtotime($data['ESDATE'].$data['ESTIME'])):'',
							'LatestShipDate'=>($data['LSDATE']>0)?date('Y-m-d H:i:s',strtotime($data['LSDATE'].$data['LSTIME'])):'',
							'EarliestDeliveryDate'=>($data['EDDATE']>0)?date('Y-m-d H:i:s',strtotime($data['EDDATE'].$data['EDTIME'])):'',
							'LatestDeliveryDate'=>($data['LDDATE']>0)?date('Y-m-d H:i:s',strtotime($data['LDDATE'].$data['LDTIME'])):'',
						);
						foreach($data['O_ITEMS'] as $sdata){
							if(!$sku) $sku = $sdata['ZSSKU'];
							if(!$asin) $asin = $sdata['ZASIN'];
							$orderItemData[]= array(			
									'SellerId'=>$sdata['SELLERID'],
									'MarketPlaceId'=>$sdata['ZMPLACEID'],
									'AmazonOrderId'=>$sdata['ZAOID'],
									'OrderItemId'=>$sdata['ZORIID'],
									'Title'=>$sdata['TITLE'],
									'QuantityOrdered'=>intval($sdata['QORDERED']),
									'QuantityShipped'=>intval($sdata['QSHIPPED']),
									'GiftWrapLevel'=>$sdata['GWLEVEL'],
									'GiftMessageText'=>$sdata['GMTEXT'],
									'ItemPriceAmount'=>round($sdata['IPAMOUNT'],2),
									'ItemPriceCurrencyCode'=>$sdata['IPCCODE'],
									'ShippingPriceAmount'=>round($sdata['SPAMOUNT'],2),
									'ShippingPriceCurrencyCode'=>$sdata['SPCCODE'],
									'GiftWrapPriceAmount'=>round($sdata['GWPAMOUNT'],2),
									'GiftWrapPriceCurrencyCode'=>$sdata['GWPCCODE'],
									'ItemTaxAmount'=>round($sdata['ITAMOUNT'],2),
									'ItemTaxCurrencyCode'=>$sdata['ITCCODE'],
									'ShippingTaxAmount'=>round($sdata['STAMOUNT'],2),
									'ShippingTaxCurrencyCode'=>$sdata['STCCODE'],
									'GiftWrapTaxAmount'=>round($sdata['GWTAMOUNT'],2),
									'GiftWrapTaxCurrencyCode'=>$sdata['GWTCCODE'],
									'ShippingDiscountAmount'=>round($sdata['SDAMOUNT'],2),
									'ShippingDiscountCurrencyCode'=>$sdata['SDCCODE'],
									'PromotionDiscountAmount'=>round($sdata['PDAMOUNT'],2),
									'PromotionDiscountCurrencyCode'=>$sdata['PDCCODE'],
									'PromotionIds'=>$sdata['PROMOID'],
									'CODFeeAmount'=>round($sdata['CFAMOUNT'],2),
									'CODFeeCurrencyCode'=>$sdata['CFCCODE'],
									'CODFeeDiscountAmount'=>round($sdata['CFDAMOUNT'],2),
									'CODFeeDiscountCurrencyCode'=>$sdata['CFDCCODE'],
									'ASIN'=>$sdata['ZASIN'],
									'SellerSKU'=>$sdata['ZSSKU'],
							);
						}
						DB::table('amazon_orders_item')->insert($orderItemData);
						DB::table('amazon_orders')->insert($order);
						DB::commit();
					}else{
						$message = $result['message'];
					}
				} catch (\Exception $e) {
					DB::rollBack();
					$message = $e->getMessage();
				}
			}else{
				$sellerid =  $exists->SellerId;

				$exists_item = DB::table('amazon_orders_item')->where('AmazonOrderId', $orderid);
				if($sellerid){
					$exists_item = $exists_item->where('SellerId', $sellerid);
				}
				$exists_item = $exists_item->first();
				if($exists_item){
					$asin = $exists_item->ASIN;
					$sku = $exists_item->SellerSKU;
				}
			}
		}
		if(!$message){
			$upd = array();
			if($orderid) $upd['amazon_order_id'] = $orderid;
			if($sellerid) $upd['amazon_seller_id'] = $sellerid;
			if($sku) $upd['sku'] = $sku;
			if($asin) $upd['asin'] = $asin;
		
			
			if($inboxid){
				$re = Inbox::where('id',$inboxid)->update($upd);
				if($re){
					$message = 'Get Amazon Order ID Success, Auto refresh after 3 seconds';
				}else{
					$message = 'Get Amazon Order ID Failed';
				}
			}else{
				$message = 'Get Amazon Order ID Success';
			}
			
		}
		
		if($inboxid){
			die(json_encode(array('result'=>$re , 'message'=>$message)));
		
		}else{
			$return_arr['result']=1;
			$return_arr['message']=$message;
			if($sellerid && $orderid){
				$return_arr['sellerid']=$sellerid;
				
				$order = DB::table('amazon_orders')->where('SellerId', $sellerid)->where('AmazonOrderId', $orderid)->first();
            if($order) $order->item = DB::table('amazon_orders_item')->where('SellerId', $sellerid)->where('AmazonOrderId', $orderid)->get();
				if($order){
					$return_arr['buyeremail']=$order->BuyerEmail;
					$item_str='';
					foreach($order->item as $item){ 
						
                         $item_str = '<tr><td><h4>'.$item->ASIN.' ( '.$item->SellerSKU.' )</h4><p> '.$item->Title.' </p> </td><td class="text-center sbold">'.$item->QuantityOrdered.'</td><td class="text-center sbold">'.round($item->ItemPriceAmount/$item->QuantityOrdered,2).'</td><td class="text-center sbold">'.round($item->ShippingPriceAmount,2).' '.(($item->ShippingDiscountAmount)?'( -'.round($item->ShippingDiscountAmount,2).' )':'').'</td> <td class="text-center sbold">'.(($item->PromotionDiscountAmount)?'( -'.round($item->PromotionDiscountAmount,2).' )':'').'</td><td class="text-center sbold">'.round($item->ItemTaxAmount,2).'</td></tr>';
					}
						
				
									
					$return_arr['orderhtml']='<div class="invoice-content-2 bordered">
                        <div class="row invoice-head">
                            <div class="col-md-7 col-xs-6">
                                <div class="invoice-logo">
                                    <h1 class="uppercase">'.$order->AmazonOrderId.'  ( '.array_get($this->getSellerIds(),$order->SellerId).' )</h1>
                                    Buyer Email : '.$order->BuyerEmail.'<BR>
                                    Buyer Name : '.$order->BuyerName.'<BR>
                                    PurchaseDate : '.$order->PurchaseDate.'
                                </div>
                            </div>
                            <div class="col-md-5 col-xs-6">
                                <div class="company-address">
                                    <span class="bold ">'.$order->Name.'</span>
                                    <br> '.$order->AddressLine1.'
                                    <br> '.$order->AddressLine2.'
                                    <br> '.$order->AddressLine3.'
                                    <br> '.$order->City.' '.$order->StateOrRegion.' '.$order->CountryCode.'
                                    <br> '.$order->PostalCode.'
                                </div>
                            </div>
                        </div>
                            <BR><BR>
                        <div class="row invoice-cust-add">
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Seller ID</h4>
                                <p class="invoice-desc">'.$order->SellerId.'   </p>
                            </div>
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Site</h4>
                                <p class="invoice-desc">'.$order->SalesChannel.'</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Fulfillment Channel</h4>
                                <p class="invoice-desc">'.$order->FulfillmentChannel.'</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Ship Service Level</h4>
                                <p class="invoice-desc">'.$order->ShipServiceLevel.'</p>
                            </div>

                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Status</h4>
                                <p class="invoice-desc">'.$order->OrderStatus.'</p>
                            </div>


                        </div>
                        <BR><BR>
                        <div class="row invoice-body">
                            <div class="col-xs-12 table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="invoice-title uppercase">Description</th>
                                        <th class="invoice-title uppercase text-center">Qty</th>
                                        <th class="invoice-title uppercase text-center">Price</th>
                                        <th class="invoice-title uppercase text-center">Shipping</th>
                                        <th class="invoice-title uppercase text-center">Promotion</th>
										<th class="invoice-title uppercase text-center">Tax</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                   	'.$item_str.'
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row invoice-subtotal">
                            <div class="col-xs-6">
                                <h4 class="invoice-title uppercase">Total</h4>
                                <p class="invoice-desc grand-total">'.round($order->Amount,2).' '.$order->CurrencyCode.'</p>
                            </div>
                        </div>

                    </div>';
				}
			}
			die(json_encode($return_arr));
		}
	}

}