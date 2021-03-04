<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inbox;
use App\Sendbox;
use App\Accounts;
use App\Asin;
use Illuminate\Support\Facades\Session;

use App\User;
use App\Group;
use App\Groupdetail;
use App\Rule;
use App\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use App\Models\TrackLog;
use App\Classes\SapRfcRequest;

class InboxController extends Controller
{
    use \App\Traits\Mysqli;

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
    public function index($type = '')
    {
		if(!Auth::user()->can(['inbox-show'])) die('Permission denied -- inbox-show');
        $fromService = '';
        $currentUserId = '';
        $linkIndex = '';

        return view('inbox/index',['users'=>$this->getUsers(),'groups'=>$this->getGroups(),'type'=>$type,'mygroups'=>$this->getUserGroup(), 'fromService'=>$fromService, 'currentUserId'=>$currentUserId, 'linkIndex'=>$linkIndex]);
   }

    public function fromService(Request $request)
    {
        if(!Auth::user()->can(['inbox-show'])) die('Permission denied -- inbox-show');
        $type = '';
        $fromService = isset($_REQUEST['fromService']) ? $_REQUEST['fromService'] : '';
        $currentUserId = Auth::user()->id;
        $linkIndex = isset($_REQUEST['linkIndex']) ? $_REQUEST['linkIndex'] : '';

        return view('inbox/index',['users'=>$this->getUsers(),'groups'=>$this->getGroups(),'type'=>$type,'mygroups'=>$this->getUserGroup(), 'fromService'=>$fromService, 'currentUserId'=>$currentUserId, 'linkIndex'=>$linkIndex]);
    }


	public function getCategoryJson(Request $request){

		$parent_id = $request->get('parent_id',28);
		$order_by = 'created_at';
		$sort = 'desc';
		$list =  Category::where('category_pid','=',$parent_id)->orderBy($order_by,$sort)->get()->toArray();

		$lists = json_encode(['status'=>'y','data'=>$list],true);
		$d = json_decode($lists,true);
		$ret = ['status'=>'y'];

		foreach($d['data'] as $r){
			if($r['category_pid']==$parent_id){
				$ret['data'][]=$r;
			}
		}
		echo json_encode($ret,true);
	}

	public function getItemGroup(){

		if(array_get($_REQUEST,'item_no')){
			$ItemGroup = new Asin;
			$ItemGroup = $ItemGroup->where('item_no', 'like', '%'.$_REQUEST['item_no'].'%');
			$ItemGroupList = $ItemGroup->limit(1)->get()->toArray();
			if(!empty($ItemGroupList)){
				echo json_encode(['code'=>200,'data'=>$ItemGroupList]);
			}else{
				echo json_encode(['code'=>204]);
			}
		}else{
			echo json_encode(['code'=>204]);
		}

	}

	public function getItem(){

		if(array_get($_REQUEST,'sku')){
			$Item = new Asin;
			$Item = $Item->where('sellersku', 'like', '%'.$_REQUEST['sku'].'%');
			$ItemList = $Item->limit(1)->get()->toArray();
			if(!empty($ItemList)){
				echo json_encode(['code'=>200,'data'=>$ItemList]);
			}else{
				echo json_encode(['code'=>204]);
			}
		}else{
			echo json_encode(['code'=>204]);
		}

	}

    function change(Request $request){

	   if(!Auth::user()->can(['inbox-update'])) die('Permission denied -- inbox-update');
       $id = intval($request->get('inbox_id'));
       if($id){
           $inbox = Inbox::findOrFail($id);
           $inbox->reply = intval($request->get('reply'));
           if($request->get('etype')) $inbox->etype = $request->get('etype');
           if(isset($_REQUEST['remark'])) $inbox->remark = $request->get('remark');
		   if($request->get('mark')) $inbox->mark = $request->get('mark');
           if($request->get('sku')) $inbox->sku = strtoupper($request->get('sku'));
		   if($request->get('asin')) $inbox->asin = strtoupper($request->get('asin'));
		   if($request->get('epoint')) $inbox->epoint = $request->get('epoint');
		   if($request->get('epoint_product')) $inbox->epoint_product = $request->get('epoint_product');
		   if($request->get('epoint_group')) $inbox->epoint_group = $request->get('epoint_group');
		   if($request->get('item_no')) $inbox->item_no = strtoupper($request->get('item_no'));
		   if($request->get('item_group')) $inbox->item_group = $request->get('item_group');

		   if($request->get('linkage1')) $inbox->linkage1 = $request->get('linkage1');
		   if($request->get('linkage2')) $inbox->linkage2 = $request->get('linkage2');
		   if($request->get('linkage3')) $inbox->linkage3 = $request->get('linkage3');
		   if($request->get('linkage4')) $inbox->linkage4 = $request->get('linkage4');
		   if($request->get('linkage5')) $inbox->linkage5 = $request->get('linkage5');

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
							'text'=>$request->get('text'),
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
		if(!Auth::user()->can(['inbox-show'])) die('Permission denied -- inbox-show');
        $email = Inbox::where('id',$id)->first();

        //$email->toArray();
		if($email->user_id == $this->getUserId()){
			$email->read = 1;
            $email->save();
		}

		$email = $email->toArray();
        $client_email = $email['from_address'];
        $client_id_query = DB::table('client_info')->leftJoin('client',function($q){
            $q->on('client.id', '=', 'client_info.client_id');
        })->where('client_info.email',$client_email)->select(['client.id'])->first();

        $latestConversationList = $recentEventsList = array();
        $recentEventsNumbers = array('times_non_ctg'=>0, 'times_ctg'=>0, 'times_rsg'=>0, 'times_negative_review'=>0);
        if($client_id_query){
            $client_id = $client_id_query->id;
            //Latest conversations...
            $latestConversationList = $this->getLatestConversationList($client_id);
            //Recent events...
            $recentEvents = $this->getRecentEvents($client_id);
            $recentEventsNumbers = $recentEvents[0];
            $recentEventsList = $recentEvents[1];
        }
        //RSG Task...
        $rsgProductsController = new RsgproductsController();
        $rsgTaskData = $rsgProductsController->getTableData();

		$email_unread_history = Inbox::where('id','<>',$id)->where('reply',0)->where('from_address',$email['from_address'])->where('to_address',$email['to_address'])->take(10)->orderBy('date','desc')->get();
		
        $email_from_history = Inbox::where('date','<',$email['date'])->where('from_address',$email['from_address'])->where('to_address',$email['to_address'])
        ->take(10)->orderBy('date','desc')->get()->toArray();
        $email_to_history = Sendbox::where('status','<>','Draft')->where('date','<',$email['date'])->where('from_address',$email['to_address'])->where('to_address',$email['from_address'])->take(10)->orderBy('date','desc')->get()->toArray();
        $email_history[strtotime($email['date'])] = &$email;

        $email_to = Sendbox::where('inbox_id',$id)->orderBy('date','asc')->get()->toArray();
        $order=array();
		$account = Accounts::where('account_email',$email['to_address'])->first();
        $account_type = $account ? $account->type : null;

        if($email['amazon_order_id']){
			$amazon_seller_id = $email['amazon_seller_id'];

			if(!$amazon_seller_id){
                $amazon_seller_id = $account ? $account->account_sellerid : null;
			}
            $order = DB::table('amazon_orders')->where('SellerId', $amazon_seller_id)->where('AmazonOrderId', $email['amazon_order_id'])->first();
            if($order) $order->item = DB::table('amazon_orders_item')->where('SellerId', $amazon_seller_id)->where('AmazonOrderId', $email['amazon_order_id'])->get();
        }

		//获取该客户与此邮箱的来往邮件中所有绑定的订单号信息,展示出所有绑定订单的订单详情
		$email_order = Inbox::whereNotNull('amazon_order_id')->whereNotNull('amazon_seller_id')->where('from_address',$email['from_address'])->where('to_address',$email['to_address'])->orderBy('date','desc')->get()->toArray();
		$sap = new SapRfcRequest();
		$orderArr = array();
		foreach($email_order as $key=>$val){
			//获取sap订单信息
			$orderid = $val['amazon_order_id'];
			try{
				$orderArr[$key] = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $orderid]));
				$orderArr[$key]['SellerName'] = Accounts::where('account_sellerid', $orderArr[$key]['SellerId'])->first()->account_name ?? 'No Match';
			} catch (\Exception $e) {

			}

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

        $rsgStatusArr = getCrmRsgStatusArr();
        foreach($email_history as $key => $value){
            $from_address = $value['from_address'];
            $to_address = $value['to_address'];
            $email_history[$key]['fromAddressRsgStatusHtml'] = $this->getRsgStatusAttr($value, $from_address, $rsgStatusArr, 'from_address');
            $email_history[$key]['toAddressRsgStatusHtml'] = $this->getRsgStatusAttr($value, $to_address, $rsgStatusArr, 'to_address');
        }

        krsort($email_history);
		$email_change_log = DB::table('inbox_change_log')->where('inbox_id',$id)->whereRaw('user_id <> to_user_id')->orderBy('date','asc')->get();
		$order_by = 'created_at';
		$sort = 'desc';
		$lists =  Category::orderBy($order_by,$sort)->get()->toArray();
		$tree = $this->getTree($lists,28);

        return view('inbox/view',['email_history'=>$email_history,'unread_history'=>$email_unread_history,'order'=>$order,'orderArr'=>$orderArr,'email'=>$email,'users'=>$this->getUsers(),'groups'=>$this->getGroups(),'sellerids'=>$this->getSellerIds(),'accounts'=>$this->getAccounts(),'account_type'=>$account_type,'tree'=>$tree,'email_change_log'=>$email_change_log,'client_email'=>$client_email,'latestConversationList'=>$latestConversationList,'recentEventsList'=>$recentEventsList,'recentEventsNumbers'=>$recentEventsNumbers,'rsgTaskData'=>$rsgTaskData,'rsg_link'=> 'https://rsg.claimgiftsnow.com?user=V'.Auth::user()->id ]);
    }

    public function getRsgStatusAttr($emailDetails, $email, $rsgStatusArr, $fromOrTo){
        $rsgStatusArray = DB::table('client_info')->leftJoin('client',function($q){
            $q->on('client.id', '=', 'client_info.client_id');
        })->where('client_info.email',$email)->select(['rsg_status','rsg_status_explain'])->first();
        $rsgStatusHtml = '';
        //亚马逊客户是不能邀请做RSG的，不显示红色或绿色圆圈。
        if(! ($emailDetails[$fromOrTo] && preg_match("/.+@.*amazon.+/", $emailDetails[$fromOrTo]))){
            if($rsgStatusArray){
                $explain = isset($rsgStatusArr[$rsgStatusArray->rsg_status_explain]) ? $rsgStatusArr[$rsgStatusArray->rsg_status_explain]['vop'] : $rsgStatusArray->rsg_status_explain;
                $rsgStatusHtml = $this->getRsgStatusHtml($rsgStatusArray, $explain);
            }
            else{
                $rsgStatusHtml = '<div class="available"></div>';
            }
        }

        return $rsgStatusHtml;
    }

    //按created_at降序排列
    public function sortCreatedAtDesc($a,$b){
        if($a['created_at'] < $b['created_at']){
            return 1;
        }
    }

    //获取邮件和跟进记录。数据来源参考：CRM show页面Email History选项卡。
    public function getLatestConversationList($client_id){
        $sql = "select b.name as name,b.email as email,b.phone as phone,b.remark as remark,b.country as country,b.`from` as `from`,c.amazon_order_id as order_id
			FROM client_info as b
			left join client_order_info as c on b.id = c.ci_id
			where b.client_id = $client_id
			order by b.id desc ";
        $_data = $this->queryRows($sql);
        $send_to_emails = array();
        foreach ($_data as $val) {
            $send_to_emails[] = $val['email'];
        }

        $sendboxEmails = DB::table('sendbox')->whereIn('to_address', $send_to_emails)->orderBy('date', 'desc')->get();
        $sendboxEmails = json_decode(json_encode($sendboxEmails), true);
        $track_log_array = TrackLog::where('record_id', $client_id)->where('type', 2)->orderBy('created_at', 'desc')->get()->toArray();

        $latestConversationList = array();
        foreach ($sendboxEmails as $val) {
            $latestConversationList[] = $val;
        }
        foreach ($track_log_array as $val) {
            $latestConversationList[] = $val;
        }
        //自定义排序：按创建时间倒序排列
        usort($latestConversationList, array($this, 'sortCreatedAtDesc'));
        //最多显示最新的3条记录。
        $latestConversationList = array_slice($latestConversationList, 0, 3);
        $now = time();
        foreach ($latestConversationList as $key => $val) {
            $diff_seconds = ($now - strtotime($val['created_at']));
            if ($diff_seconds >= 86400) {
                $latestConversationList[$key]['interval'] = floor($diff_seconds / 86400) . 'D';
            } else {
                $latestConversationList[$key]['interval'] = floor($diff_seconds / 3600) . 'h';
            }
            //跟进记录
            if (!isset($val['inbox_id'])) {
                $latestConversationList[$key]['note'] = preg_replace('/<\/?.+?\/?>/','', array_get($val, 'note'));
            }
        }

        return $latestConversationList;
    }

    public function getRecentEvents($client_id)
    {
        //numbers of ctg, rsg, negative_review
        $recentEventsNumbers = DB::table('client')->where('id', $client_id)->select(['times_ctg', 'times_rsg', 'times_negative_review'])->first();
        $recentEventsNumbers = json_decode(json_encode($recentEventsNumbers), true);
        //non-ctg
        $clientEmailArray = DB::table('client_info')->where('client_id', $client_id)->select(['email'])->first();
        $clientEmailArray = json_decode(json_encode($clientEmailArray), true);

        $recentEventsList = array();
        $nonCtgRecords = DB::table('non_ctg')->whereIn('email', $clientEmailArray)->get();
        $nonCtgRecords = json_decode(json_encode($nonCtgRecords), true);
        $sap = new SapRfcRequest();
        foreach ($nonCtgRecords as $key => $val) {
            $sapOrderInfo = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $val['amazon_order_id']]));
            if ($sapOrderInfo) {
                $asin = $sapOrderInfo['orderItems'][0]['ASIN'];
                $marketPlaceSite = $sapOrderInfo['orderItems'][0]['MarketPlaceSite'];
                $nonCtgRecords[$key]['asin'] = $asin;
                $nonCtgRecords[$key]['marketPlaceSite'] = $marketPlaceSite;
                $nonCtgRecords[$key]['event_type'] = 'non_ctg';
                $nonCtgRecords[$key]['created_at'] = $val['date'];
                $recentEventsList[] = $nonCtgRecords[$key];
            }
        }
        //numbers of non_ctg
        $recentEventsNumbers['times_non_ctg'] = count($recentEventsList);

        //ctg(ctg,b1g1,cashback)
        $ctgTables = array('ctg', 'b1g1', 'cashback');
        $clientEmailArrayWithQuotes = array_map(function ($v) {
            return "'" . $v . "'";
        }, $clientEmailArray);
        $clientEmailArrayInSql = '(' . implode(',', $clientEmailArrayWithQuotes) . ')';
        for ($i = 0; $i < count($ctgTables); $i++) {
            $sql = 'select ASIN as asin,MarketPlaceSite as marketPlaceSite,' . $ctgTables[$i] . '.email as email,' . $ctgTables[$i] . '.created_at as created_at from ' . $ctgTables[$i] . ' JOIN ctg_order_item on ' . $ctgTables[$i] . '.order_id=ctg_order_item.AmazonOrderId and ' . $ctgTables[$i] . '.email IN ' . $clientEmailArrayInSql . ';';
            $ctgRecords = $this->queryRows($sql);
            if ($ctgRecords) {
                foreach ($ctgRecords as $key => $val) {
                    $ctgRecords[$key]['event_type'] = 'ctg';
                    $recentEventsList[] = $ctgRecords[$key];
                }
            }
        }

        //rsg
        $sql = 'select asin, site as marketPlaceSite, rsg_requests.customer_email as email, rsg_requests.created_at as created_at from rsg_requests JOIN rsg_products on rsg_requests.product_id=rsg_products.id and rsg_requests.customer_email IN ' . $clientEmailArrayInSql . ';';
        $rsgRecords = $this->queryRows($sql);
        if ($rsgRecords) {
            foreach ($rsgRecords as $key => $val) {
                $rsgRecords[$key]['event_type'] = 'rsg';
                $recentEventsList[] = $rsgRecords[$key];
            }
        }

        //自定义排序：按创建时间倒序排列
        usort($recentEventsList, array($this, 'sortCreatedAtDesc'));
        //最多显示最新的3条记录。
        $recentEventsList = array_slice($recentEventsList, 0, 3);
        $now = time();
        foreach ($recentEventsList as $key => $val) {
            $diff_seconds = ($now - strtotime($val['created_at']));
            if ($diff_seconds >= 86400) {
                $recentEventsList[$key]['interval'] = floor($diff_seconds / 86400) . 'D';
            } else {
                $recentEventsList[$key]['interval'] = floor($diff_seconds / 3600) . 'h';
            }
        }

        return [$recentEventsNumbers, $recentEventsList];
    }

    public function getRsgTaskData(Request $req){
        $site = isset($_POST['site']) && $_POST['site'] ? $_POST['site'] : 'US';
        $data = $this->getRsgTask($site);

        $return['status'] = 0;
        if($data){
            $return['data'] = $data;
            $return['status'] = 1;
        }
        return json_encode($return);
    }

    public function getRsgTask($site){
        $date = $this->getDefaultDate(date('Y-m-d'));
        $ago15day = date('Y-m-d',strtotime($date)-86400*15);

        //限制站点搜索
        $siteArrConfig = getSiteArr()['site'];
        $siteArr = isset($siteArrConfig[$site]) ? $siteArrConfig[$site] : array();
        $siteSql = " and rsg_products.site in('".join($siteArr,"','")."')";
        if($site=='JP'){
            $siteSql .= " and rsg_products.order_status = 1 ";
        }

        $sql = "
                SELECT SQL_CALC_FOUND_ROWS
                    rsg_products.id as id,
                    rsg_products.asin as asin,
                    rsg_products.site as site,
                    rsg_products.seller_id as seller_id,
                    rsg_products.post_status as post_status,
                    rsg_products.post_type as post_type,
                    rsg_products.sales_target_reviews as target_review,
                    rsg_products.requested_review as requested_review,
                    asin.bg as bg,
                    asin.bu as bu,
                    asin.item_no as item_no,
                    asin.seller as seller,
                    asin.id as asin_id,
                    rsg_products.number_of_reviews as review,
                    rsg_products.review_rating as rating,
                    num as unfinished,
                    rsg_products.sku_level as sku_level,
                    rsg_products.product_img as img,
                    rsg_products.order_status as order_status,
                    cast(rsg_products.sales_target_reviews as signed) - cast(rsg_products.requested_review as signed) as task,
                    (status_score * type_score * level_score * rating_score * review_score * days_score) as score
                from
                    rsg_products
                        left join
                    (select 
                        id,
                            case post_status
                                WHEN 1 then 1
                                WHEN 2 then 2
                                ELSE 0
                            END as status_score,
                            case post_type
                                WHEN 1 then 1 * 20
                                WHEN 2 then 0.5 * 20
                                ELSE 0
                            END as type_score,
                            if(stock_days < 60, 0, 1) as days_score,
                            case sku_level
                                WHEN 'S' then 1
                                WHEN 'A' then 0.6
                                WHEN 'B' then 0.2
                                ELSE 0
                            END as level_score,
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
                                ELSE 0
                            END as rating_score,
                            if(site = 'www.amazon.com', case
                                WHEN number_of_reviews < 100 then 10
                                WHEN
                                    number_of_reviews >= 100
                                        and number_of_reviews < 400
                                then
                                    7
                                WHEN
                                    number_of_reviews >= 400
                                        and number_of_reviews < 1000
                                then
                                    4
                                WHEN
                                    number_of_reviews >= 1000
                                        and number_of_reviews < 4000
                                then
                                    1
                                WHEN number_of_reviews >= 4000 then 0
                            END, case
                                WHEN number_of_reviews < 40 then 10
                                WHEN
                                    number_of_reviews >= 40
                                        and number_of_reviews < 100
                                then
                                    7
                                WHEN
                                    number_of_reviews >= 100
                                        and number_of_reviews < 400
                                then
                                    4
                                WHEN
                                    number_of_reviews >= 400
                                        and number_of_reviews <= 1000
                                then
                                    1
                                WHEN number_of_reviews > 1000 then 0
                            END) as review_score
                    from
                        rsg_products
                    where
                        created_at = '".$date."') as rsg_score ON rsg_score.id = rsg_products.id
                        left join
                    asin ON rsg_products.asin = asin.asin
                        and rsg_products.site = asin.site
                        and rsg_products.sellersku = asin.sellersku
                        left join
                    (select 
                        count(*) as num, asin, site
                    from
                        rsg_products
                    left join rsg_requests ON product_id = rsg_products.id
                        and step IN (4 , 5, 6, 7)
                    where
                        rsg_requests.created_at <= '".$date." 23:59:59 ' 
                            and rsg_requests.created_at >= '".$ago15day." 00:00:00 ' 
                    group by asin , site) as rsg ON rsg_products.asin = rsg.asin
                        and rsg_products.site = rsg.site
                where
                    1 = 1 and created_at = '".$date."' 
                        and cast(rsg_products.sales_target_reviews as signed) - cast(rsg_products.requested_review as signed) > 0
                        {$siteSql} ".
            "order by rsg_products.order_status desc , score desc , id desc
                LIMIT 0 , 10        
        ";

        $data = $this->queryRows($sql);
        $i = 1;
        foreach ($data as $key => $val) {
            $data[$key]['rank'] = $i;
            $data[$key]['product'] = '<a target="_blank" href="https://rsg.claimgiftsnow.com/product/detail?id='.$val['asin_id'].'"><img src="'.$val['img'].'" width="50px" height="65px"></a>';
            $data[$key]['asin'] = '<a href="https://' . $val['site'] . '/dp/' . $val['asin'] . '" target="_blank" rel="noreferrer">' . $val['asin'] . '</a>';//asin插入超链接
            $data[$key]['action'] = '<a data-target="#ajax" class="badge badge-success" data-toggle="modal" href="/rsgrequests/create?productid='.$val['id'].'&asin=' . $val['asin'] . '&site=' . $val['site'] . '"> 
                                    <i class="fa fa-hand-o-up"></i></a>';
            if($data[$key]['task']<=0){
                $data[$key]['action'] = '<div class="badge badge-primary">Done</div>';
            }
            $i++;
        }

        return $data;
    }

    public function getDefaultDate($todayDate){
        if(time()-strtotime($todayDate.' 02:30:00') < 0){
            //凌晨到七点半之间要显示的是昨天的数据
            $todayDate = date('Y-m-d',strtotime($todayDate)-86400);
        }
        return $todayDate;
    }

    public function getRsgStatusHtml($rsgStatusArray, $explain){
        //邮箱后面显示绿色圆圈
        $rsgStatus = '<div class="available"></div>';
        if ($rsgStatusArray->rsg_status == 1) {
            //邮箱后面显示红色圆圈
            $rsgStatus = '<div class="unavailable" title="' . $explain . '"></div>';
        }
        return $rsgStatus;
    }

    public function get(Request $request)
    {
        /*
   * Paging
   */
		if(!Auth::user()->can(['inbox-show'])) die('Permission denied -- inbox-show');
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
			if(!Auth::user()->can(['inbox-batch-update'])) die('Permission denied -- inbox-batch-update');
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
        if(Auth::user()->can(['inbox-show-all'])){
            $customers = new Inbox;
        }else{
            $customers = Inbox::whereIn('group_id',array_get($this->getUserGroup(),'groups',array()));
        }
		//if(array_get($_REQUEST,'show_all')=='show_all') $customers = new Inbox;
		
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
        //从service页面的Time out超链接过来的
        if(array_get($_REQUEST,'linkIndex') == 1){
            $customers = $customers->leftJoin(DB::raw('(select id as r_id, if(timeout IS NULL, 0, timeout*3600) as timeout from rules) as new_rules'),function($q){
                $q->on('inbox.rule_id', '=', 'new_rules.r_id');
            })->whereRaw('TIMESTAMPDIFF(SECOND, inbox.date, now()) > new_rules.timeout');
        }

		//if(!Auth::user()->can(['inbox-show-all'])) {
//        	 $customers = $customers->orderByRaw('case when user_id='.Auth::user()->id.' and reply=0 then 0 else 1 end asc');
		//}
		$customers = $customers->orderBy('user_id','asc');
		$iTotalRecords = $customers->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
//        $customers = $customers->leftJoin(DB::raw('(select email, rsg_status, rsg_status_explain from client_info left join client on client_info.client_id = client.id) as t1'),function($q){
//            $q->on('inbox.from_address', '=', 't1.email');
//        });

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
		$status_list[99] = "<span class=\"label label-sm label-warning\">Do not need to reply</span>";
        $status_list[0] = "<span class=\"label label-sm label-danger\">Need reply</span>";
        $rsgStatusArr = getCrmRsgStatusArr();

		foreach ( $customersLists as $customersList){
			$warnText = '';
            if($customersList['reply']==0){
                if(array_get($rules,$customersList['rule_id'],'')){
                    $warnText = $this->time_diff(strtotime(date('Y-m-d H:i:s')), strtotime('+ '.array_get($rules,$customersList['rule_id']),strtotime($customersList['date'])));
                }
            }
//            $explain = isset($rsgStatusArr[$customersList['rsg_status_explain']]) ? $rsgStatusArr[$customersList['rsg_status_explain']]['vop'] : $customersList['rsg_status_explain'];
//            //亚马逊客户是不能邀请做RSG的，不显示红色或绿色圆圈。
//            if($customersList['type'] != 'Site'){
//                $rsgStatus = '';
//            }else{
//                if($customersList['rsg_status']==1) {
//                    //邮箱后面显示红色圆圈
//                    $rsgStatus = '<div class="unavailable" title="'.$explain.'"></div>';
//                }else{
//                    //邮箱后面显示绿色圆圈
//                    $rsgStatus = '<div class="available"></div>';
//                }
//            }
            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList['id'].'"/><span></span></label>',
                
				$customersList['from_address'].'</BR>'.(array_get($customersList,'from_name')?'<span class="label label-sm label-primary">'.array_get($customersList,'from_name').'</span> ':' ').$status_list[$customersList['reply']],
                $customersList['to_address'].'</BR>'.'<span class="label label-sm label-primary">'.array_get($groups,$customersList['group_id'].'.group_name').' - '.array_get($users,$customersList['user_id']).'</span> ',
                (($customersList['mark'])?'<span class="label label-sm label-danger">'.$customersList['mark'].'</span> ':'').(($customersList['sku'])?'<span class="label label-sm label-primary">'.$customersList['sku'].'</span> ':'').(($customersList['etype'])?'<span class="label label-sm label-danger">'.$customersList['etype'].'</span> ':'').'<a href="/inbox/'.$customersList['id'].'" target="_blank" style="color:#333;">'.(($customersList['read'])?'':'<strong>').$customersList['subject'].(($customersList['read'])?'':'</strong>').'</a>'.(($warnText)?'<span class="label label-sm label-danger">'.$warnText.'</span> ':'').(($customersList['remark'])?'<BR/><span class="label label-sm label-info">'.$customersList['remark'].'</span> ':''),
                $customersList['date'],
                
                '<a href="/inbox/'.$customersList['id'].'" class="btn btn-sm btn-outline grey-salsa" target="_blank"><i class="fa fa-search"></i> View </a>',
            );
		}

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function getUsers(){
        //目前在职的.不只是销售人员
        $users = User::where('locked', '=', 0)->get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }
	
	public function getGroups(){
        $groups = Group::get()->toArray();
        $groups_array = array();
        $users_array = $this->getUsers();
        foreach($groups as $group){
            $groups_array[$group['id']]['group_name'] = $group['group_name'];
            $userIds = explode(",",$group['user_ids']);
            $filteredUserIds = array();
            foreach($userIds as $userId){
                //目前在职的.不只是销售人员
                if(isset($users_array[$userId])){
                    $filteredUserIds[] = $userId;
                }
            }
            $groups_array[$group['id']]['user_ids'] = $filteredUserIds;
        }
        return $groups_array;
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

		if(Auth::user()->can(['inbox-show-all'])){
            $groups = Groupdetail::get(['group_id']);
			$group_arr =array();
			foreach($groups as $group){
				$group_arr['groups'][$group->group_id] = $group->group_id;
			}
			$users = Groupdetail::get(['user_id']);
			foreach($users as $user){
				$group_arr['users'][$user->user_id] = $user->user_id;
			}

//            var_dump ($group_arr);
//            exit;

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

	//收件箱里面解绑订单号操作
	public function unbindInboxOrder(Request $request)
	{
		$inboxid = $request->get('inboxid');
		$rerurn['status'] = 0;
		$update = array('amazon_order_id'=>'','amazon_seller_id'=>'','sku'=>'','asin'=>'');
		$re = Inbox::where('id',$inboxid)->update($update);
		if($re){
			$rerurn['status'] = 1;
			$rerurn['msg'] = 'Unbind Success,Auto refresh after 3 seconds';
		}else{
			$rerurn['msg'] = 'Unbind Fail';
		}
		return $rerurn;
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
					
					$res = file_get_contents('http://'.env("SAP_RFC").'/rfc_site.php?appid='.$appkey.'&sellerid='.$sellerid.'&method=getOrder&orderId='.$orderid.'&sign='.$sign);
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
//							'ApiDownloadDate'=>date('Y-m-d H:i:s',strtotime($data['ALOADDATE'].$data['ALOADTIME'])),
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
			echo (json_encode(array('result'=>$re , 'message'=>$message)));
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
                    $basicInfo = array();
					foreach($order->item as $item){
                        $basicInfo['asin'] = $item->ASIN;
                        $basicInfo['SellerSKU'] = $item->SellerSKU;
                         $item_str = '<tr><td><h4>'.$item->ASIN.' ( '.$item->SellerSKU.' )</h4><p> '.$item->Title.' </p> </td><td class="text-center sbold">'.$item->QuantityOrdered.'</td><td class="text-center sbold">'.round($item->ItemPriceAmount/$item->QuantityOrdered,2).'</td><td class="text-center sbold">'.round($item->ShippingPriceAmount,2).' '.(($item->ShippingDiscountAmount)?'( -'.round($item->ShippingDiscountAmount,2).' )':'').'</td> <td class="text-center sbold">'.(($item->PromotionDiscountAmount)?'( -'.round($item->PromotionDiscountAmount,2).' )':'').'</td><td class="text-center sbold">'.round($item->ItemTaxAmount,2).'</td></tr>';
					}
					$site = 'www.'.$order->SalesChannel;
                    $asinInfo = DB::table('asin')->where($basicInfo)->where('site',$site)->get(array('item_no'))->first();
                    $basicInfo['item_no'] = $asinInfo->item_no;
                    if($basicInfo){
                        $return_arr['productBasicInfo'] = $basicInfo;//产品基本信息,ASIN,SellerSKU,item_no
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
			echo (json_encode($return_arr));
		}
	}

	public function getTree($data, $pId)
	{
		$tree = [];
		foreach($data as $k => $v)
		{
			if($v['category_pid'] == $pId)
			{
				//父亲找到儿子
				$v['category_pid'] = $this->getTree($data, $v['id']);
				$tree[] = $v;
				//unset($data[$k]);
			}
		}
		return $tree;
	}

    public function getSubjectType(){
        return Category::where('category_pid',28)->orderBy('created_at','desc')->pluck('category_name','id');
    }


}