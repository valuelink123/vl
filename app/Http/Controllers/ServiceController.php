<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrackLog;
use App\Models\Ctg;
use App\Sendbox;
use App\Task;
use App\RsgRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PDO;
use DB;
use log;
use App\Classes\SapRfcRequest;
use App\Accounts;
use App\Category;

class ServiceController extends Controller
{
    use \App\Traits\Mysqli;

    /**
     * Create a new controller instance.
     *
     * @return void
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
    public function index(Request $request)
    {
		$date_from = $request->get('date_from')?$request->get('date_from'):(date('Y-m').'-01');
		$date_to = $request->get('date_to')?$request->get('date_to'):date('Y-m-d');
		if($date_from>$date_to) $date_from=$date_to;
		$group_info = getUserGroupDetails();
		$groups = array_get($group_info,'groups',[]);
		$user_id = Auth::user()->id;
		$post_user_ids = array_get($request,'user_id',[]);
		$users = array_get($group_info,'users',[]);
		$user_ids = ($post_user_ids)?$post_user_ids:array_keys($users);
		
		$hb_date_to = date('Y-m-d',strtotime($date_from)-86400);
		$hb_date_from = date('Y-m-d',2*strtotime($date_from)-strtotime($date_to)-86400);
		
		//$user_id=77;
		$details = $dash = [];
		$total_score = 0;
		$channel_score = ['0'=>2,'1'=>3,'2'=>3,'3'=>1,'sg'=>5,'rsg'=>20];
		$emails = Sendbox::select(DB::Raw('count(*) as count,date(send_date) as sdate'))->whereIn('user_id',$user_ids)->where('send_date','>=',$date_from.' 00:00:00')->where('send_date','<=',$date_to.' 23:59:59')->groupBy('sdate')->pluck('count','sdate')->toArray();
		$sendbox_emails_count=0;
		foreach($emails as $k=>$v){
			$details[$k][3] = $v*array_get($channel_score,'3',0);
			$total_score+=$details[$k][3];
            $sendbox_emails_count+=$v;
		}
		
		unset($emails);
		
		$others = TrackLog::select(DB::Raw('count(*) as count,date(created_at) as sdate,channel'))->whereIn('processor',$user_ids)->whereIn('channel',['0','1','2'])->where('type',2)->where('created_at','>=',$date_from.' 00:00:00')->where('created_at','<=',$date_to.' 23:59:59')->groupBy(['sdate','channel'])->get()->toArray();
		$score = 0;
		$tracklog_count=0;
		foreach($others as $val){
			$details[$val['sdate']][$val['channel']] = $val['count']*array_get($channel_score,$val['channel'],0);
			$total_score+=$details[$val['sdate']][$val['channel']];
            $tracklog_count+=$val['count'];
		}
		unset($others);

		$sg = Ctg::select(DB::Raw('date(created_at) as sdate,count(*) as count'))->whereIn('processor',$user_ids)->where('commented',1)->where('created_at','>=',$date_from.' 00:00:00')->where('created_at','<=',$date_to.' 23:59:59')->groupBy(['sdate'])->pluck('count','sdate')->toArray();
        $ctg_count=0;
		foreach($sg as $k=>$v){
			$details[$k]['sg'] = $v*array_get($channel_score,'sg',0);
			$total_score+=$details[$k]['sg'];
			$ctg_count+=$v;
		}
		unset($sg);
		
		$rsg = RsgRequest::select(DB::Raw('date(updated_at) as sdate,count(*) as count'))->whereIn('processor',$user_ids)->where('step',9)->where('updated_at','>=',$date_from.' 00:00:00')->where('updated_at','<=',$date_to.' 23:59:59')->groupBy(['sdate'])->pluck('count','sdate')->toArray();
        $rsg_request_count=0;
		foreach($rsg as $k=>$v){
			$details[$k]['rsg'] = $v*array_get($channel_score,'rsg',0);
			$total_score+=$details[$k]['rsg'];
            $rsg_request_count+=$v;
		}
		unset($rsg);
		
		
		//if(!array_key_exists($today,$details)){
			$dash[0][0]= intval(Sendbox::select(DB::Raw('count(*) as count'))->whereIn('user_id',$user_ids)->where('send_date','>=',$date_from.' 00:00:00')->where('send_date','<=',$date_to.' 23:59:59')->value('count'))+intval(TrackLog::select(DB::Raw('count(*) as count'))->whereIn('processor',$user_ids)->whereIn('channel',['0','1','2'])->where('type',2)->where('created_at','>=',$date_from.' 00:00:00')->where('created_at','<=',$date_to.' 23:59:59')->value('count'));
			
			$dash[2][0]= intval(Ctg::select(DB::Raw('count(*) as count'))->whereIn('processor',$user_ids)->where('commented',1)->where('created_at','>=',$date_from.' 00:00:00')->where('created_at','<=',$date_to.' 23:59:59')->value('count'));
			$dash[3][0]= intval(RsgRequest::select(DB::Raw('count(*) as count'))->whereIn('processor',$user_ids)->where('step',9)->where('updated_at','>=',$date_from.' 00:00:00')->where('updated_at','<=',$date_to.' 23:59:59')->value('count'));	
		//}else{
			//$dash[0][0]=$dash[2][0]=$dash[3][0]=0;
			//foreach(array_get($details,$today) as $k=>$v){
			//	$dash[0][0]+=intval($v/array_get($channel_score,$k));
			//	if($k=='sg') $dash[2][0]+=intval($v/array_get($channel_score,$k));
			//	if($k=='sg') $dash[3][0]+=intval($v/array_get($channel_score,$k));
			//}
			
		//}
		//if(!array_key_exists($yesterday,$details)){
			$dash[0][1]= intval(Sendbox::select(DB::Raw('count(*) as count'))->whereIn('user_id',$user_ids)->where('send_date','>=',$hb_date_from.' 00:00:00')->where('send_date','<=',$hb_date_to.' 23:59:59')->value('count'))+intval(TrackLog::select(DB::Raw('count(*) as count'))->whereIn('processor',$user_ids)->whereIn('channel',['0','1','2'])->where('type',2)->where('created_at','>=',$hb_date_from.' 00:00:00')->where('created_at','<=',$hb_date_to.' 23:59:59')->value('count'));
			
			$dash[2][1]= intval(Ctg::select(DB::Raw('count(*) as count'))->whereIn('processor',$user_ids)->where('commented',1)->where('created_at','>=',$hb_date_from.' 00:00:00')->where('created_at','<=',$hb_date_to.' 23:59:59')->value('count'));
			$dash[3][1]= intval(RsgRequest::select(DB::Raw('count(*) as count'))->whereIn('processor',$user_ids)->where('step',9)->where('updated_at','>=',$hb_date_from.' 00:00:00')->where('updated_at','<=',$hb_date_to.' 23:59:59')->value('count'));	
		/*}else{
			$dash[0][1]=$dash[2][1]=$dash[3][1]=0;
			foreach(array_get($details,$yesterday) as $k=>$v){
				$dash[0][1]+=intval($v/array_get($channel_score,$k));
				if($k=='sg') $dash[2][1]+=intval($v/array_get($channel_score,$k));
				if($k=='sg') $dash[3][1]+=intval($v/array_get($channel_score,$k));
			}
			
		}*/

		//得到需要统计的数量
		$statis = $this->getNoreplyData();
		$statis = $statis + $this->getRRData();
		
		$tasks = Task::where('response_user_id',Auth::user()->id)->where('stage','<',3)->take(10)->orderBy('priority','desc')->get()->toArray();
        return view('service',compact('total_score','groups','post_user_ids','tasks','dash','details','users','date_from','date_to','user_id','statis'));

    }

    public function fastSearch(Request $req){
        $searchType = $req->input('searchType', 0);
        $searchTerm = trim($req->input('searchTerm', ''));

        if($searchTerm != '') {
            //Order ID
            if ($searchType == 0) {
//            	订单信息从亚马逊数据的那个库读取订单信息和订单明细的信息
				$account = $order = $orderItems = array();
				$_account = DB::connection('amazon')->table('seller_accounts')->get();//卖家账号信息
				$_order = DB::connection('amazon')->table('orders')->where('amazon_order_id',$searchTerm)->get();//订单信息
				$_orderItems = DB::connection('amazon')->table('order_items')->where('amazon_order_id',$searchTerm)->get();//订单详情
				foreach($_account as $ak=>$av){
					$account[$av->id] = (array)$av;
				}
				foreach($_orderItems as $oik=>$oiv){
					$orderItems[$oiv->seller_account_id.'_'.$oiv->amazon_order_id][] = (array)$oiv;
				}
				foreach($_order as $ok=>$ov){
					$_key = $ov->seller_account_id.'_'.$ov->amazon_order_id;
					$order[$_key] = (array)$ov;
					if(isset($orderItems[$_key]) && $orderItems[$_key]){
						$order[$_key]['orderItems']= $orderItems[$_key];
					}
					if(isset($account[$ov->seller_account_id]) && $account[$ov->seller_account_id]){
						$order[$_key]['sellerName']= $account[$ov->seller_account_id]['label'];
						$order[$_key]['sellerId']= $account[$ov->seller_account_id]['mws_seller_id'];
					}
				}
				if(!$order){
					return 'No matching order...';
				}
				return view('service.orderinfo', ['orders' => $order]);

            }
            //Customer Info, 可按邮箱，电话，［facebook帐号］，paypal帐号查找。
            //多个客户可能有同样的facebook name, 只会展示其中的一个客户。所以先排除按facebook帐号搜索。
            else if ($searchType == 1) {
                //rsg_requests查看paypal帐号（customer_paypal_email）
                $emails = DB::table('rsg_requests')->where('customer_paypal_email', $searchTerm)->pluck('customer_email');
                if(count($emails) > 0){
                    $ids = DB::table('client_info')->where('email',$emails[0])->pluck('client_id');
                }
                else{
                    $ids = DB::table('client_info')->where('email',$searchTerm)->orWhere('phone',$searchTerm)->pluck('client_id');
                }

                if(count($ids) == 0){
                    return 'No matching customer...';
                }
                $id = $ids[0];

                $forwardUrl = "/crm/show?id=".$id;
                return $this->getScriptForward($forwardUrl);
            }

            //Parts List, Inventory
            else if ($searchType == 2 || $searchType == 3){
                $forwardUrl = "/kms/partslist?search=".$searchTerm;
                return $this->getScriptForward($forwardUrl);
            }
            //Manual
            else if ($searchType == 4){
                $forwardUrl = "/kms/usermanual?search=".$searchTerm;
                return $this->getScriptForward($forwardUrl);
            }
            //QA
            else if ($searchType == 5){
                $forwardUrl = "/question?knowledge_type=&group=ALL&item_group=ALL&keywords=". $searchTerm;
                return $this->getScriptForward($forwardUrl);
            }

        }
    }

    public function getScriptForward($forwardUrl){
        $scriptForward = "<script type='text/javascript'>window.location.href='$forwardUrl';</script>";
        return $scriptForward;
    }

}