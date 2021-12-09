<?php

namespace App\Http\Controllers;

use \DrewM\MailChimp\MailChimp;
use Illuminate\Http\Request;
use App\RsgRequest;
use App\RsgProduct;
use Illuminate\Support\Facades\Session;
use App\Accounts;
use App\User;
use App\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use DB;
use PayPal\PayPalAPI\TransactionSearchReq;
use PayPal\PayPalAPI\TransactionSearchRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Artisan;

class RsgrequestsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */

	use \App\Traits\Mysqli;
	use \App\Traits\DataTables;

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
		if(!Auth::user()->can(['rsgrequests-show'])) die('Permission denied -- rsgrequests-show');
		$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';

		$submit_date_from=date('Y-m-d',strtotime('-90 days'));
		$submit_date_to=date('Y-m-d');

		return view('rsgrequests/index',['submit_date_from'=>$submit_date_from ,'submit_date_to'=>$submit_date_to,'users'=>$this->getUsers(),'email'=>$email]);

    }
	
	public function get(Request $request)
    {
		if(!Auth::user()->can(['rsgrequests-show'])) die('Permission denied -- rsgrequests-show');

		$order_column = $request->input('order.0.column','1');
		if($order_column == 13){
			$orderby = 'updated_at';
		}else if($order_column == 1){
			$orderby = 'created_at';
		}else{
			$orderby = 'created_at';
		}

        $sort = $request->input('order.0.dir','desc');
		$channelKeyVal = getRsgRequestChannel();

		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));

		//搜索时间范围
		$submit_date_from = isset($search['submit_date_from']) && $search['submit_date_from'] ? $search['submit_date_from'] : date('Y-m-d',strtotime('- 90 days'));
		$submit_date_to = isset($search['submit_date_to']) && $search['submit_date_to'] ? $search['submit_date_to'] : date('Y-m-d');

		$datas= RsgRequest::leftJoin('rsg_products',function($q){
				$q->on('rsg_requests.product_id', '=', 'rsg_products.id');
			})->leftjoin('client_info', 'rsg_requests.customer_email', '=', 'client_info.email')->leftjoin('client', 'client.id', '=', 'client_info.client_id')->leftJoin(DB::raw("(select asin,site,max(sap_seller_id) as sap_seller_id,max(bg) as bg,max(bu) as bu from asin group by asin,site) as asin"),function($q){
				$q->on('rsg_products.asin', '=', 'asin.asin')
				  ->on('rsg_products.site', '=', 'asin.site');
			})
			->where('rsg_requests.created_at','>=',$submit_date_from.' 00:00:00')->where('rsg_requests.created_at','<=',$submit_date_to.' 23:59:59')->with('logs');

		if(!Auth::user()->can('rsgrequests-show-all')) {
			if (Auth::user()->seller_rules) {
				$rules = explode("-", Auth::user()->seller_rules);
				if (array_get($rules, 0) != '*') $datas = $datas->where('bg', array_get($rules, 0));
				if (array_get($rules, 1) != '*') $datas = $datas->where('bu', array_get($rules, 1));
			} elseif (Auth::user()->sap_seller_id) {
				$datas = $datas->where('sap_seller_id', Auth::user()->sap_seller_id);
			} else {

			}
		}

		if(isset($search['status']) && $search['status']){
			$statusArr = array($search['status']);
			if($search['status'] == '-1'){
				$statusArr = array(3,4,5,6,7,8);//-1为选择了all pending状态
			}
			$datas = $datas->whereIn('step', $statusArr);
		}

		if(isset($search['channel']) && $search['channel'] != '-1'){
			$datas = $datas->where('channel', $search['channel']);
		}
		if(isset($search['facebook_group']) && $search['facebook_group']){
			$datas = $datas->where('facebook_group', intval($search['facebook_group']));
		}
		if(isset($search['processor']) && $search['processor']){
			$datas = $datas->where('rsg_requests.processor', $search['processor']);
		}
		if(isset($search['user_id']) && $search['user_id']){
			$datas = $datas->where('rsg_products.user_id', $search['user_id']);
		}
		if(isset($search['crmType']) && $search['crmType']){
			$datas = $datas->where('client.type', $search['crmType']);
		}
		if(isset($search['site']) && $search['site']){
			$datas = $datas->where('rsg_products.site','like', '%'.$search['site'].'%');
		}
		if(isset($search['keyword']) && $search['keyword']){
			$keyword = $search['keyword'];
			$datas = $datas->where(function($query)use($keyword){
				$query->where('facebook_name','like','%'.$keyword.'%')
					->orWhere('customer_email', 'like', '%'.$keyword.'%')
					->orWhere('client_info.encrypted_email', 'like', '%'.$keyword.'%')
					->orWhere('customer_paypal_email', 'like', '%'.$keyword.'%')
					->orWhere('review_url', 'like', '%'.$keyword.'%')
					->orWhere('rsg_products.asin', 'like', '%'.$keyword.'%');
			});
		}

		//求符合条件的状态统计数目，需查出所有符合条件的数据，然后进行累计统计
		$staticStatus = array(
			'submit_paypal' => 0,
			'waiting_payment' => 0,
			'submit_order_id' => 0,
			'check_order_id' => 0,
			'submit_review_id' => 0,
			'check_review_id' => 0,
			'completed' => 0,
			'closed' => 0,
			'charge_back' => 0,
			'check_customer' => 0,
			'reject' => 0,
			'all_pending' => 0,
			'all_requests' => 0,
		);
		$config = array(
			'submit_paypal' => array(3),
			'waiting_payment' => array(4),
			'submit_order_id' => array(5),
			'check_order_id' => array(6),
			'submit_review_id' => array(7),
			'check_review_id' => array(8),

			'completed' => array(9),
			'closed' => array(10),
			'charge_back' => array(11),
			'check_customer' => array(1),
			'reject' => array(2),

			'all_pending' => array(3,4,5,6,7,8),
			'all_requests' => array(1,2,3,4,5,6,7,8,9,10,11),
		);
		$iTotalRecords = $datas->count();
		$countData = $datas->get(['rsg_requests.step'])->toArray();
		foreach($countData as $key=>$val){
//统计各个状态的数量值
			foreach($config as $k=>$v){
				if(in_array($val['step'],$v)){
					$staticStatus[$k] = $staticStatus[$k] + 1;
				}
			}
		}

        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
		$lists =  $datas->orderBy($orderby,$sort)->offset($iDisplayStart)->limit($iDisplayLength)->get(['rsg_requests.*','rsg_products.asin','rsg_products.site','rsg_products.seller_id','rsg_products.user_id','client_info.facebook_name','client_info.encrypted_email','client_info.facebook_group','client.rsg_status','client.rsg_status_explain'])->toArray();

		$fbgroupConfig = getFacebookGroup();
		$users= $this->getUsers();
		//Check Customer(1)，Submit Paypal(3)，Check Paypal(4)

		//funded那一列=star功能的Price-Coupon,所以查出帖子的price和coupon_n等信息
		$starData = $this->getStarData();

		$rsgStatusArr = getCrmRsgStatusArr();
		foreach ( $lists as $key=>$list){
			$lists[$key]['step'] = '<span class="badge badge-success">'.array_get(getStepStatus(),$list['step']).'</span>';
			if(in_array($list['step'],array(1,3,4))){
				$explain = isset($rsgStatusArr[$list['rsg_status_explain']]) ? $rsgStatusArr[$list['rsg_status_explain']]['vop'] : $list['rsg_status_explain'];
				$explain_step = $explain;
				if($list['rsg_status']==1){
					//邮箱后面显示红色圆圈
					$lists[$key]['customer_email'] = ($list['encrypted_email']??$list['customer_email']).'<div class="unavailable" title="'.$explain.'"></div>';
					//下面是关于step后面显示的红色打叉和绿色打钩的处理（rsg申请自动审核状态）
					//当rsg_status_explain=6的时候，还要看rsg request的状态
					if($list['rsg_status_explain']==6){
						//6，看rsg request的状态，当条数据之前的数据的状态有未完成的状态的时候，就为红色x显示
						$noComplete = DB::table('rsg_requests')->where('customer_email',$list['customer_email'])->where('created_at','<',$list['created_at'])->whereNotIn('step',array(2,9,10))->get(['id'])->toArray();
						if($noComplete){
							$lists[$key]['step'] .= '<div class="fa red fa-times pull-right" title="'.$explain_step.'"></div>';
						}else{
							//7，当帖子为不在线（帖子列表中listing参数不为Available）的状态也标红显示（status!=2，红色打叉显示
							$yestoday = date('Y-m-d',strtotime("-1 day"));
							$listingStatus = RsgProduct::where('created_at','>=',$yestoday)->where('asin',$list['asin'])->where('site',$list['site'])->orderBy('created_at','desc')->value('seller_id');
							if(strlen($listingStatus) < 10){
								$explain_step = isset($rsgStatusArr[7]) ? $rsgStatusArr[7]['vop'] : 7;
								$lists[$key]['step'] .= '<div class="fa red fa-times pull-right" title="'.$explain_step.'"></div>';
							}else{
								$lists[$key]['step'] .= '<div class="fa green fa-check pull-right"></div>';
							}
						}
					}
				}else{
					//邮箱后面显示绿色圆圈,step后面显示绿色打钩
					$lists[$key]['customer_email'] = ($list['encrypted_email']??$list['customer_email']).'<div class="available"></div>';
					$lists[$key]['step'] .= '<div class="fa green fa-check pull-right"></div>';
				}

			}else{
				$lists[$key]['customer_email'] =($list['encrypted_email']??$list['customer_email']);
			}
			
			$lists[$key]['channel'] = isset($channelKeyVal[$list['channel']]) ? $channelKeyVal[$list['channel']] : '';
			$lists[$key]['asin_link'] = '<a href="https://'.array_get($list,'site').'/dp/'.array_get($list,'asin').'" target="_blank">'.$list['asin'].'</a>';
			$lists[$key]['funded'] = sprintf("%.2f",(isset($starData[$list['asin'].'_'.$list['site']]) ? $starData[$list['asin'].'_'.$list['site']] : $list['transfer_amount'])).' '.$list['transfer_currency'];
			$lists[$key]['review_url'] = '<div style="width: 200px;word-wrap: break-word;text-align: center;">'.$list['review_url'].'<BR><span class="text-danger">'.$list['transaction_id'].'</span></div>';
			$lists[$key]['sales'] = isset($users[$list['user_id']]) ? $users[$list['user_id']] : $list['user_id'];
			$lists[$key]['group'] = isset($fbgroupConfig[$list['facebook_group']]) ? $fbgroupConfig[ $list['facebook_group']] : '';
			$lists[$key]['processor'] = array_get($users,$list['processor']);
			$lists[$key]['action'] = '<a data-target="#ajax" data-toggle="modal" href="'.url('rsgrequests/'.$list['id'].'/edit').'" class="badge badge-success"> View </a> <a class="btn btn-danger btn-xs" href="'.url('rsgrequests/process?email='.($list['encrypted_email']??$list['customer_email'])).'" target="_blank">Process</a>';
			$updateHistory = [];
			$count = count($list['logs']);
			for($i=0; $i<$count-1; $i++){
				$rsgRequestsLog = $list['logs'][$i];
				$rsgRequestsLogNext = $list['logs'][$i+1];
				$updated_by = array_get($users, $rsgRequestsLog['updated_by']);
				$updated_at = $rsgRequestsLog['updated_at'];
				$step = array_get(getStepStatus(),$rsgRequestsLog['step']);
				$channel = array_get(getRsgRequestChannel(),$rsgRequestsLog['channel']);
				$facebook_group = array_get(getFacebookGroup(),$rsgRequestsLog['facebook_group']);
				$updateHistory[$i]['updated_by'] = $updated_by;
				$updateHistory[$i]['updated_at'] = $updated_at;

				if($rsgRequestsLog['product_id'] != $rsgRequestsLogNext['product_id']){
					$updateHistory[$i]['product'] = $rsgRequestsLog['product_id'];

				}
				if($rsgRequestsLog['step'] != $rsgRequestsLogNext['step']){
					$updateHistory[$i]['current step'] = $step;
				}
				if($rsgRequestsLog['customer_paypal_email'] != $rsgRequestsLogNext['customer_paypal_email']){
					$updateHistory[$i]['paypal email'] = $rsgRequestsLog['customer_paypal_email'];
				}
				if($rsgRequestsLog['transfer_amount'] != $rsgRequestsLogNext['transfer_amount']){
					$updateHistory[$i]['transfer amount'] = $rsgRequestsLog['transfer_amount'];
				}
				if($rsgRequestsLog['transfer_currency'] != $rsgRequestsLogNext['transfer_currency']){
					$updateHistory[$i]['transfer currency'] = $rsgRequestsLog['transfer_currency'];
				}
				if($rsgRequestsLog['amazon_order_id'] != $rsgRequestsLogNext['amazon_order_id']){
					$updateHistory[$i]['amazon order id'] = $rsgRequestsLog['amazon_order_id'];
				}
				if($rsgRequestsLog['review_url'] != $rsgRequestsLogNext['review_url']){
					$updateHistory[$i]['review ID'] = $rsgRequestsLog['review_url'];
				}
				if($rsgRequestsLog['transaction_id'] != $rsgRequestsLogNext['transaction_id']){
					$updateHistory[$i]['remark'] = $rsgRequestsLog['transaction_id'];
				}
				if($rsgRequestsLog['star_rating'] != $rsgRequestsLogNext['star_rating']){
					$updateHistory[$i]['star rating'] = $rsgRequestsLog['star_rating'];
				}
				if($rsgRequestsLog['channel'] != $rsgRequestsLogNext['channel']){
					$updateHistory[$i]['channel'] = $channel;
				}
				if($rsgRequestsLog['facebook_name'] != $rsgRequestsLogNext['facebook_name']){
					$updateHistory[$i]['facebook name'] = $rsgRequestsLog['facebook_name'];
				}
				if($rsgRequestsLog['facebook_group'] != $rsgRequestsLogNext['facebook_group']){
					$updateHistory[$i]['facebook group'] = $facebook_group;
				}
			}
			
			$logStr = '';
			if(count($updateHistory) == 0){
				$logStr = 'No update history';
			}else{
				foreach($updateHistory as $k => $v){
					foreach($v as $k2 => $v2){
						if($k2 == 'updated_by' || $k2 == 'updated_at') continue;
						$logStr.='<div><span>'.array_get($v,'updated_by').' updated the '.$k2.' to '.$v2.'</span><span>'.array_get($v,'updated_at').'</span></div>';
					}
				}
			}

			$lists[$key]['updated_at'] ='<i  class="fa fa-info-circle popovers" data-container="body" onclick="" data-trigger="hover" data-placement="left" data-html="true" data-content="'.$logStr.'"></i>'.$lists[$key]['updated_at'];
		}

        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;
		return compact('data', 'recordsTotal', 'recordsFiltered','staticStatus');
    }

    public function insertRsgRequestsLogFirstRecord($ruleClone){
        $updateRsgRequestsLog = $this->getRsgRequestsLogArray($ruleClone);
        $updateRsgRequestsLog['facebook_name'] = $ruleClone->facebook_name;
        $updateRsgRequestsLog['facebook_group'] = $ruleClone->facebook_group;
        $updateRsgRequestsLog['updated_by'] = $ruleClone->processor;
        DB::connection('amazon')->table('rsg_requests_log')->insert($updateRsgRequestsLog);
    }

    public function insertRsgRequestsLog($rule){
        $updateRsgRequestsLog = $this->getRsgRequestsLogArray($rule);
        if(isset($_REQUEST['facebook_group']) && $_REQUEST['facebook_group']){
        $updateRsgRequestsLog['facebook_group'] = (int)$_REQUEST['facebook_group'];
        }
        if(isset($_REQUEST['facebook_name']) && $_REQUEST['facebook_name']){
            $updateRsgRequestsLog['facebook_name'] = $_REQUEST['facebook_name'];
        }

        $updateRsgRequestsLog['updated_by'] = intval(Auth::user()->id);
        DB::connection('amazon')->table('rsg_requests_log')->insert($updateRsgRequestsLog);
    }

    public function getRsgRequestsLogArray($rule){
        $updateRsgRequestsLog = array();
        $updateRsgRequestsLog['request_id'] = $rule->id;
        $updateRsgRequestsLog['product_id'] = $rule->product_id;
        $updateRsgRequestsLog['customer_email'] = $rule->customer_email;
        $updateRsgRequestsLog['step'] = $rule->step;
        $updateRsgRequestsLog['customer_paypal_email'] = $rule->customer_paypal_email;
        $updateRsgRequestsLog['transfer_amount'] = $rule->transfer_amount;
        $updateRsgRequestsLog['transfer_currency'] = $rule->transfer_currency;
        $updateRsgRequestsLog['amazon_order_id'] = $rule->amazon_order_id;
        $updateRsgRequestsLog['review_url'] = $rule->review_url;
        $updateRsgRequestsLog['transaction_id'] = $rule->transaction_id;
        $updateRsgRequestsLog['star_rating'] = $rule->star_rating;
        $updateRsgRequestsLog['channel'] = $rule->channel;
        $updateRsgRequestsLog['created_at'] = $rule->created_at;
        $updateRsgRequestsLog['updated_at'] = $rule->updated_at;

        return $updateRsgRequestsLog;
    }

    public function updateHistory(Request $req){
        $request_id = $req->input('request_id');
        $rsgRequestsLogArray = DB::connection('amazon')->table('rsg_requests_log')->where('request_id', '=', $request_id)->orderBy('updated_at', 'desc')->get();
        $rsgRequestsLogArray = json_decode(json_encode($rsgRequestsLogArray),true);
        $output = '';
        $users = $this->getUsers();
        $count = count($rsgRequestsLogArray);
        $productIds = array();
        for($i=0; $i<$count; $i++){
            $productIds[] = $rsgRequestsLogArray[$i]['product_id'];
        }
        $productIdNames = $this->getProductIdNames($productIds);
        $updateHistory = array();

        for($i=0; $i<$count-1; $i++){
            $rsgRequestsLog = $rsgRequestsLogArray[$i];
            $rsgRequestsLogNext = $rsgRequestsLogArray[$i+1];
            $updated_by = array_get($users, $rsgRequestsLog['updated_by']);
            $updated_at = $rsgRequestsLog['updated_at'];
            $product_name = array_get($productIdNames, $rsgRequestsLog['product_id']);
            $step = array_get(getStepStatus(),$rsgRequestsLog['step']);
            $channel = array_get(getRsgRequestChannel(),$rsgRequestsLog['channel']);
            $facebook_group = array_get(getFacebookGroup(),$rsgRequestsLog['facebook_group']);

            $updateHistory[$i]['updated_by'] = $updated_by;
            $updateHistory[$i]['updated_at'] = $updated_at;

            if($rsgRequestsLog['product_id'] != $rsgRequestsLogNext['product_id']){
                $updateHistory[$i]['product'] = $product_name;
            }
            if($rsgRequestsLog['step'] != $rsgRequestsLogNext['step']){
                $updateHistory[$i]['current step'] = $step;
            }
            if($rsgRequestsLog['customer_paypal_email'] != $rsgRequestsLogNext['customer_paypal_email']){
                $updateHistory[$i]['paypal email'] = $rsgRequestsLog['customer_paypal_email'];
            }
            if($rsgRequestsLog['transfer_amount'] != $rsgRequestsLogNext['transfer_amount']){
                $updateHistory[$i]['transfer amount'] = $rsgRequestsLog['transfer_amount'];
            }
            if($rsgRequestsLog['transfer_currency'] != $rsgRequestsLogNext['transfer_currency']){
                $updateHistory[$i]['transfer currency'] = $rsgRequestsLog['transfer_currency'];
            }
            if($rsgRequestsLog['amazon_order_id'] != $rsgRequestsLogNext['amazon_order_id']){
                $updateHistory[$i]['amazon order id'] = $rsgRequestsLog['amazon_order_id'];
            }
            if($rsgRequestsLog['review_url'] != $rsgRequestsLogNext['review_url']){
                $updateHistory[$i]['review ID'] = $rsgRequestsLog['review_url'];
            }
            if($rsgRequestsLog['transaction_id'] != $rsgRequestsLogNext['transaction_id']){
                $updateHistory[$i]['remark'] = $rsgRequestsLog['transaction_id'];
            }
            if($rsgRequestsLog['star_rating'] != $rsgRequestsLogNext['star_rating']){
                $updateHistory[$i]['star rating'] = $rsgRequestsLog['star_rating'];
            }
            if($rsgRequestsLog['channel'] != $rsgRequestsLogNext['channel']){
                $updateHistory[$i]['channel'] = $channel;
            }
            if($rsgRequestsLog['facebook_name'] != $rsgRequestsLogNext['facebook_name']){
                $updateHistory[$i]['facebook name'] = $rsgRequestsLog['facebook_name'];
            }
            if($rsgRequestsLog['facebook_group'] != $rsgRequestsLogNext['facebook_group']){
                $updateHistory[$i]['facebook group'] = $facebook_group;
            }
        }

        return view('rsgrequests/updateHistory',['updateHistory'=>$updateHistory]);
    }

    public function getProductIdNames($productIds){
        $products = RsgProduct::WhereIn('id', $productIds)->get()->toArray();
        $productIdNames = array();
        foreach ($products as $key => $val){
            $productIdNames[$val['id']] = $val['asin'].'——'.$val['product_name'];
        }

        return $productIdNames;
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

	public function process(Request $req){

		if ($req->isMethod('GET')) {
			$email = array_search($req->input('email'),getEmailToEncryptedEmail())?array_search($req->input('email'),getEmailToEncryptedEmail()):$req->input('email');
			$sendbox_emails = DB::table('sendbox')->where('to_address', $email)->orderBy('date', 'desc')->get(['*',DB::RAW('\''.$req->input('email').'\' as to_address')]);
			$inbox_emails = DB::table('inbox')->where('from_address', $email)->orderBy('date', 'desc')->get(['*',DB::RAW('\''.$req->input('email').'\' as from_address')]);
			$users= $this->getUsers();
			$sendbox_emails = json_decode(json_encode($sendbox_emails), true); // todo
			$inbox_emails = json_decode(json_encode($inbox_emails), true); // todo
			$_emails_array = array();
			foreach($sendbox_emails as $key=>$val){
				$val['subject_link'] = ' <a href="/send/'.$val['id'].'" target="_blank">'.$val['subject'].' </a>';
				$val['user_name'] = array_get($users,array_get($val,'user_id'));
				$val['email_send_date'] = array_get($val,'send_date') ? '<span class="label label-sm label-success">'.array_get($val,'send_date').'</span> ':'<span class="label label-sm label-danger">'.array_get($val,'status').'</span>';
				$_emails_array[] = $val;
			}
			foreach($inbox_emails as $key=>$val){
				$val['subject_link'] = ' <a href="/inbox/'.$val['id'].'" target="_blank">'.$val['subject'].' </a>';
				$val['user_name'] = '客户';
				$val['email_send_date'] = '<span class="label label-sm label-success">'.array_get($val,'date').'</span> ';
				$_emails_array[] = $val;
			}
			//按时间由近至远排序
			$emails = array_sort($_emails_array,'date',$type='desc');
		}
		return view('rsgrequests/process',['emails'=>$emails,'users'=>$users]);
	}

    public function getAccounts(){
        $seller=[];
		$accounts= DB::connection('order')->table('accounts')->where('status',1)->groupBy(['sellername','sellerid'])->get(['sellername','sellerid']);
		$accounts=json_decode(json_encode($accounts), true);
		foreach($accounts as $account){
			$seller[$account['sellerid']]=$account['sellername'];
		}
		return $seller;
    }
	
	public function create(Request $request)
    {
		if(!Auth::user()->can(['rsgrequests-create'])) die('Permission denied -- rsgrequests-create');
		$data['asin'] = isset($_GET['asin']) ? trim($_GET['asin']) : '';
		$data['site'] = isset($_GET['site']) ? trim($_GET['site']) : '';
		$data['productid'] = isset($_GET['productid']) ? trim($_GET['productid']) : '';

        $emails = [];
        $contactBasic = [];
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        if($id != ''){
            $results = DB::select('select email,encrypted_email,facebook_name,facebook_group from client_info where client_id='.$id);
            $results = array_map('get_object_vars', $results);
            $fbgroupConfig = getFacebookGroup();
            foreach($results as $key=>$val){
                $emails[] = $val['encrypted_email'];
                $contactBasic['facebook_name'] = $val['facebook_name'];
                $contactBasic['facebook_group'] = isset($fbgroupConfig[$val['facebook_group']]) ? $val['facebook_group'].' | '.$fbgroupConfig[$val['facebook_group']] : $val['facebook_group'];
            }
        }

        return view('rsgrequests/add',['products'=>self::getproducts(),'data'=>$data, 'emails'=>$emails, 'contactBasic'=>$contactBasic]);
    }


    public function store(Request $request)
    {
        if(!Auth::user()->can(['rsgrequests-create'])) die('Permission denied -- rsgrequests-create');
        $this->validate($request, [
			'step' => 'required|int',
			'product_id' => 'required|int',
			'customer_email' => 'required|email'
        ]);
		
        $rule = new RsgRequest();
		$rule->customer_email = array_search($request->get('customer_email'),getEmailToEncryptedEmail())?array_search($request->get('customer_email'),getEmailToEncryptedEmail()):$request->get('customer_email');
		if(isBlacklistEmail($rule->customer_email)) throw new \Exception('Blacklist email');
		$rule->customer_paypal_email = $request->get('customer_paypal_email');
		$rule->transfer_paypal_account = $request->get('transfer_paypal_account');
		$rule->transaction_id = $request->get('transaction_id');
		$rule->amazon_order_id = $request->get('amazon_order_id');
		$rule->transfer_amount = round($request->get('transfer_amount'),2);
		$rule->transfer_currency = $request->get('transfer_currency');
		$rule->review_url = $request->get('review_url');
        $rule->step = intval($request->get('step'));
		$rule->channel = $request->get('channel');
		$rule->processor = intval(Auth::user()->id);
		if(intval($request->get('product_id'))){
			$rule->product_id = intval($request->get('product_id'));
			$laterProduct= RsgProduct::where('id',$rule->product_id)->first()->toArray();
			if($laterProduct['requested_review']>=$laterProduct['sales_target_reviews']){
				$request->session()->flash('error_message','Set Rsg Request Failed, Daily quantity limit exceeded');
				return redirect()->back()->withInput();
			}
		}
		$rule->star_rating = $request->get('star_rating');
		// $rule->follow = $request->get('follow');
		// $rule->next_follow_date = $request->get('next_follow_date');

        $rule->user_id = intval(Auth::user()->id);
		$rule->auto_send_status = intval( $request->get('auto_send_status'));

		//根据客户的rsg_status判断该客户的邮箱是否可以再次申请rsg产品
		$clientData = DB::table('client')->leftJoin('client_info',function($q){
			$q->on('client.id', '=', 'client_info.client_id');
		})
			->where('client_info.email',$rule->customer_email)->get(['client.rsg_status','client.rsg_status_explain','client.subscribe','client.block'])->first();

		if($clientData && ($clientData->subscribe==0 ||$clientData->block==1)){
			$request->session()->flash('error_message',"Rsg Request Failed! Use ehe email<".$rule->customer_email."> to do rsg is strictly forbidden.");
			return redirect()->back()->withInput();
		}

		if($clientData && $clientData->rsg_status==1){
			//rsg_status=1表示该客户不能申请rsg产品，
			$rsgStatusArr = getCrmRsgStatusArr();
			$explain = isset($rsgStatusArr[$clientData->rsg_status_explain]) ? $rsgStatusArr[$clientData->rsg_status_explain]['vop'] : $clientData->rsg_status_explain;
			$request->session()->flash('error_message',$explain);
			return redirect()->back()->withInput();
		}

		//一个客户对一个产品只能申请一次，可以申请多个不同的产品，但是必须是上个产品complete后才能申请
		$ruleData = $rule->where('customer_email',$rule->customer_email)->where('product_id',$rule->product_id)->take(1)->get()->toArray();
		if($ruleData){
			//该客户已经申请过该产品
			$request->session()->flash('error_message','Rsg Request Failed,One customer cannot test two identical products');

			return redirect()->back()->withInput();
		}
		//检查该客户最近一次申请产品是什么时候，要在上次申请完成后才能再申请
		$customerData = $rule->where('customer_email',$rule->customer_email)->orderBy('updated_at', 'desc')->take(1)->get()->toArray();
		if($customerData){
			// $day = (time()-strtotime($customerData[0]['updated_at']))/86400;
			if(!($customerData[0]['step']==9)){
				$request->session()->flash('error_message',"Rsg Request Failed,Don't complete previous test yet");
				return redirect()->back()->withInput();
			}
		}

		DB::beginTransaction();//开启事务处理

        if ($rule->save()) {
			//查client_info表中是否有此客户的数据，如若有就更新facebook_name和facebook_group字段数据，如若没有就插入客户信息数据到client和client_info表
			$updateClient = array();
			if(isset($_REQUEST['facebook_group']) && $_REQUEST['facebook_group']){
				$updateClient['facebook_group'] = (int)$_REQUEST['facebook_group'];
			}
			if(isset($_REQUEST['facebook_name']) && $_REQUEST['facebook_name']){
				$updateClient['facebook_name'] = $_REQUEST['facebook_name'];
			}
			if($updateClient){
				$data['email'] = $rule->customer_email;
				$data['order_id'] = $rule->amazon_order_id;
				$data['from'] = 'RSG';
				$data['processor'] = intval(Auth::user()->id);
				updateCrm($data,$updateClient);
			}

			//存储在rsg_requests_log表中
            $this->insertRsgRequestsLog($rule);

			$step_to_tags = getStepIdToTags();
			$product= RsgProduct::where('id',$rule->product_id)->first()->toArray();
			$res = RsgProduct::where('id',$rule->product_id)->update(array('requested_review'=>($product['requested_review']+1)));
			if(empty($res)){
				DB::rollBack();
			}
			DB::commit();
			//auto_send_status为0的时候时候才触发自动发信，选NO为1的时候不触发发信
			if($rule->auto_send_status==0){
				$mailchimpData = array(
					'PROIMG'=>$product['product_img'],'PRONAME'=>$product['product_name'],'PROKEY'=>$product['keyword'],'PROPAGE'=>$product['page'],'PROPOS'=>$product['position'],'MARKET'=>str_replace('www.','',$product['site'])
				);
				if($rule->customer_paypal_email) $mailchimpData['PAYPAL'] = $rule->customer_paypal_email;
				if($rule->transfer_amount) $mailchimpData['FUNDED'] = $rule->transfer_amount.' '.$rule->transfer_currency;
				if($rule->amazon_order_id) $mailchimpData['ORDERID'] = $rule->amazon_order_id;
				if($rule->review_url) $mailchimpData['REVIEWURL'] = $rule->review_url;
				self::mailchimp($rule->customer_email,array_get($step_to_tags,$rule->step),[
					'email_address' => $rule->customer_email,
					'status'        => 'subscribed',
					'merge_fields' => $mailchimpData]);
			}
            $request->session()->flash('success_message','Set Rsg Request Success');
			return redirect()->back()->withInput();
        }else{
            $request->session()->flash('error_message','Set Rsg Request Failed');
            return redirect()->back()->withInput();
        }
    }
	
    public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['rsgrequests-show'])) die('Permission denied -- rsgrequests-show');
		$rule= RsgRequest::where('id',$id)->first()->toArray();
        if(!$rule){
            $request->session()->flash('error_message','Rsg Product not Exists');
            return redirect('rsgrequests');
        }
		if(array_get($rule,'customer_paypal_email')) $rule['trans']=self::getTrans(array_get($rule,'customer_paypal_email'));
		$product= RsgProduct::where('id',$rule['product_id'])->first();
		if($product){
			$product = $product->toArray();
			$product['product_name'] = $product['asin'].'——'.$product['product_name'];
		}else{
			$product = array('site'=>'','asin'=>'','seller_id'=>'','product_name'=>'','price'=>'','currency'=>'','keyword'=>'','page'=>'','position'=>'','id'=>'','product_img'=>'');
		}

		//查询该邮箱是否存在于client_info中，查出需要显示的facebook_name和facebook_group
		$rule['facebook_name'] = '';
		$rule['facebook_group'] = '';
		$clientInfo = DB::table('client_info')->where('email',$rule['customer_email'])->get(array('facebook_name','facebook_group','encrypted_email'))->first();
		if($clientInfo){
			$fbgroupConfig = getFacebookGroup();
			$rule['facebook_name'] = $clientInfo->facebook_name;
			$rule['facebook_group'] = isset($fbgroupConfig[ $clientInfo->facebook_group]) ? $clientInfo->facebook_group.' | '.$fbgroupConfig[ $clientInfo->facebook_group] : $clientInfo->facebook_group;
		}
		$rule['customer_email'] = empty($clientInfo)?$rule['customer_email']:$clientInfo->encrypted_email;
        return view('rsgrequests/edit',['rule'=>$rule,'product'=>$product,'products'=>self::getproducts()]);
    }
	
	public function getproducts(){
		$date=date('Y-m-d');
		if(time()-strtotime($date.' 02:00:00') < 0){
			//凌晨到2点之前要显示的是昨天的数据
			$date = date('Y-m-d',strtotime($date)-86400);
		}
		$_products = DB::select("select * from `rsg_products` where `created_at` = '".$date."' and `sales_target_reviews` > `requested_review` and `order_status` != -1  order by `order_status` desc");
		$products = array();
		foreach($_products as $key=>$val){
			$val = (array)$val;
			$products[$val['site']][$key] = $val;
			$products[$val['site']][$key]['product_name'] = $val['asin'].'——'.$val['product_name'];
		}
		return $products;
	}

    public function update(Request $request,$id)
    {
		if(!Auth::user()->can(['rsgrequests-update'])) die('Permission denied -- rsgrequests-update');
        $this->validate($request, [
			'step' => 'required|int',
        ]);
		
        $rule = RsgRequest::findOrFail($id);
        $rsgRequestLog = DB::connection('amazon')->table('rsg_requests_log')->where('request_id', '=',$rule->id)->get();
        //对已有数据，如果第一次更新，当前rsgRequestLog表中没有记录，需要将相应的rsg_requests的记录写入rsgRequestLog表中。
        if(count($rsgRequestLog) == 0){
            $ruleClone = clone $rule;
            $clientInfo = DB::table('client_info')->where('email',$rule->customer_email)->get()->first();
            if($clientInfo){
                $ruleClone->facebook_name = $clientInfo->facebook_name;
                $ruleClone->facebook_group = $clientInfo->facebook_group;
                $this->insertRsgRequestsLogFirstRecord($ruleClone);
            }
        }

		$rule->customer_paypal_email = $request->get('customer_paypal_email');
		$rule->transfer_paypal_account = $request->get('transfer_paypal_account');
		$rule->transaction_id = $request->get('transaction_id');
		$rule->amazon_order_id = $request->get('amazon_order_id');
		$rule->transfer_amount = round($request->get('transfer_amount'),2);
		$rule->transfer_currency = $request->get('transfer_currency');
		$rule->review_url = $request->get('review_url');
        $rule->step = intval($request->get('step'));
		
        //回复变更产品功能
		$product_id = intval($request->get('product_id'));
		if($rule->product_id != $product_id){//修改后的产品id不等于原来的产品id
			//原来的产品已请求数量-1
			$beforeProduct= RsgProduct::where('id',$rule->product_id)->first()->toArray();
			$request_review = $beforeProduct['requested_review']-1 > 0 ? $beforeProduct['requested_review']-1 : 0;
			RsgProduct::where('id',$rule->product_id)->update(array('requested_review'=>$request_review));

			//修改后的产品的已请求数量+1
			$laterProduct= RsgProduct::where('id',$product_id)->first()->toArray();
			if($laterProduct['requested_review']>=$laterProduct['sales_target_reviews']){
				$request->session()->flash('error_message','Set Rsg Request Failed, Daily quantity limit exceeded');
         		return redirect()->back()->withInput();
			}
			
			
			RsgProduct::where('id',$product_id)->update(array('requested_review'=>($laterProduct['requested_review']+1)));

			$rule->product_id = $product_id;//修改后的产品id覆盖
		}
		
		$rule->star_rating = $request->get('star_rating');
		// $rule->follow = $request->get('follow');
		// $rule->next_follow_date = $request->get('next_follow_date');
		$rule->channel = $request->get('channel');
		// $rule->auto_send_status = intval( $request->get('auto_send_status'));

        $rule->user_id = intval(Auth::user()->id);
        if ($rule->save()) {
			//查client_info表中是否有此客户的数据，如若有就更新facebook_name和facebook_group字段数据，如若没有就插入客户信息数据到client和client_info表
			$updateClient = array();
			if(isset($_REQUEST['facebook_group']) && $_REQUEST['facebook_group']){
				$updateClient['facebook_group'] = (int)$_REQUEST['facebook_group'];
			}
			if(isset($_REQUEST['facebook_name']) && $_REQUEST['facebook_name']){
				$updateClient['facebook_name'] = $_REQUEST['facebook_name'];
			}
			if($updateClient){
				$data['email'] = $rule->customer_email;
				$data['order_id'] = $rule->amazon_order_id;
				$data['from'] = 'RSG';
				$data['processor'] = intval(Auth::user()->id);
				updateCrm($data,$updateClient);
			}

			$step_to_tags = getStepIdToTags();
			$product= RsgProduct::where('id',$rule->product_id)->first()->toArray();
			if($rule->auto_send_status==0) {
				$mailchimpData = array(
					'PROIMG' => $product['product_img'], 'PRONAME' => $product['product_name'], 'PROKEY' => $product['keyword'], 'PROPAGE' => $product['page'], 'PROPOS' => $product['position']
				);
				if ($rule->customer_paypal_email) $mailchimpData['PAYPAL'] = $rule->customer_paypal_email;
				if ($rule->transfer_amount) $mailchimpData['FUNDED'] = $rule->transfer_amount . ' ' . $rule->transfer_currency;
				if ($rule->amazon_order_id) $mailchimpData['ORDERID'] = $rule->amazon_order_id;
				if ($rule->review_url) $mailchimpData['REVIEWURL'] = $rule->review_url;
				self::mailchimp($rule->customer_email, array_get($step_to_tags, $rule->step), [
					'email_address' => $rule->customer_email,
					'status' => 'subscribed',
					'merge_fields' => $mailchimpData]);
			}

            //存储在rsg_requests_log表中
            $this->insertRsgRequestsLog($rule);

            $request->session()->flash('success_message','Set Rsg Request Success');
            return redirect('rsgrequests');
        }else{
            $request->session()->flash('error_message','Set Rsg Request Failed');
            return redirect()->back()->withInput();
        }
    }
	
	
	public function mailchimp($customer_email,$tag,$args){
		$MailChimp = new MailChimp(env('MAILCHIMP_KEY', ''));
		//$MailChimp->verify_ssl=false;
		$list_id = env('MAILCHIMP_LISTID', '');
		$subscriber_hash = $MailChimp->subscriberHash($customer_email);	
		$MailChimp->put("lists/$list_id/members/$subscriber_hash", $args);
		if (!$MailChimp->success()) {
			print_r($MailChimp->getLastError());
			print_r($MailChimp->getLastResponse());
			die();
		}
		$MailChimp->post("lists/$list_id/members/$subscriber_hash/tags", [
			'tags'=>[
			['name' => $tag,
			'status' => 'active',]
			]
		]);
		if (!$MailChimp->success()) {
			print_r($MailChimp->getLastError());
			print_r($MailChimp->getLastResponse());
			die();
		}
	}
	
	public function getTrans($customer_paypal_email){
		$payments = DB::connection('amazon')->select("select paypal_account,timestamp,type,payer,transaction_id,payments_history.`status`,gross_amount,gross_amount_currency 
from paypal_accounts left join payments_history on paypal_accounts.id=payments_history.paypal_account_id
where payer='$customer_paypal_email' order by timestamp asc");
		return json_decode(json_encode($payments),true);
	}

	public function export(){
		if(!Auth::user()->can(['rsgrequests-export'])) die('Permission denied -- rsgrequests-export');
		set_time_limit(0);

		$arrayData = array();
		$headArray[] = 'Submit Date';
		$headArray[] = 'Channel';
		$headArray[] = 'Customer Email';
		$headArray[] = 'Request Product';
		$headArray[] = 'Current Step';
		$headArray[] = 'Customer Paypal';
		$headArray[] = 'Funded';
		$headArray[] = 'Amazon OrderID';
		$headArray[] = 'Review Url';
		$headArray[] = 'Remark';
		$headArray[] = 'Star rating';
		// $headArray[] = 'Follow';
		// $headArray[] = 'Next follow date';
		$headArray[] = 'Sales';
		$headArray[] = 'Site';
		$headArray[] = 'Update Date';
		$headArray[] = 'Facebook Name';
		$headArray[] = 'Group';
		$headArray[] = 'Processor';

		$arrayData[] = $headArray;

		$orderby = 'updated_at';
		$sort = 'desc';
		$datas= RsgRequest::leftJoin('rsg_products',function($q){
			$q->on('rsg_requests.product_id', '=', 'rsg_products.id');
		})->leftjoin('client_info', 'rsg_requests.customer_email', '=', 'client_info.email');

		//$datas->count();
		$lists =  $datas->orderBy($orderby,$sort)->get(['rsg_requests.*','rsg_products.asin','rsg_products.site','rsg_products.seller_id','rsg_products.user_id','client_info.facebook_name','client_info.facebook_group','client_info.encrypted_email'])->toArray();

		$users = $this->getUsers();
		$channelKeyVal = getRsgRequestChannel();
		$fbgroupConfig = getFacebookGroup();
		$starData = $this->getStarData();
		foreach ($lists as $key=>$val){

			$arrayData[] = array(
				$val['created_at'],
				isset($channelKeyVal[$val['channel']]) ? $channelKeyVal[$val['channel']] : '',
				$val['encrypted_email']??$val['customer_email'],
				$val['asin'],
				array_get(getStepStatus(),$val['step']),
				$val['customer_paypal_email'],
				// $val['transfer_amount'].$val['transfer_currency'],
				sprintf("%.2f",(isset($starData[$val['asin'].'_'.$val['site']]) ? $starData[$val['asin'].'_'.$val['site']] : $val['transfer_amount'])).' '.$val['transfer_currency'],
				$val['amazon_order_id'],
				$val['review_url'],
				$val['transaction_id'],
				$val['star_rating'],
				// $val['follow'],
				// $val['next_follow_date'],
				isset($users[$val['user_id']]) ? $users[$val['user_id']] : $val['user_id'],
				$val['site'],
				$val['updated_at'],
				//显示facebook_group内容
				$val['facebook_name'],
				isset($fbgroupConfig[$val['facebook_group']]) ? $fbgroupConfig[ $val['facebook_group']] : '',
				array_get($users,$val['processor']),
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
			header('Content-Disposition: attachment;filename="Export_RSG_Requests.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}

	//更新操作
	public function updateAction()
	{
		if(!Auth::user()->can(['rsgrequests-batch-update'])) die('Permission denied -- rsgrequests-batch-update');
		$type = isset($_POST['type']) && $_POST['type'] ? $_POST['type'] : 0;
		$res = 0;
		$ids = array();
		if(isset($_POST['id']) && $_POST['id']){
			foreach($_POST['id'] as $key=>$val){
				$ids[] = $val[0];
			}
		}

		if(isset($_POST['data']) && $_POST['data']) {
			if ($type == 1) {
				//更新processor
				$res = RsgRequest::whereIn('id', $ids)->update(array('processor' => $_POST['data']));
			} elseif ($type == 2) {
				foreach($ids as $id){
					$rule = RsgRequest::findOrFail($id);
					$rsgRequestLog = DB::connection('amazon')->table('rsg_requests_log')->where('request_id', '=',$id)->get();
					//对已有数据，如果第一次更新，当前rsgRequestLog表中没有记录，需要将相应的rsg_requests的记录写入rsgRequestLog表中。
					if(count($rsgRequestLog) == 0){
						$ruleClone = clone $rule;
						$clientInfo = DB::table('client_info')->where('email',$rule->customer_email)->get()->first();
						if($clientInfo){
							$ruleClone->facebook_name = $clientInfo->facebook_name;
							$ruleClone->facebook_group = $clientInfo->facebook_group;
							$this->insertRsgRequestsLogFirstRecord($ruleClone);
						}
					}
					//更新状态
					$res = RsgRequest::where('id', $id)->update(array('step' => $_POST['data']));

					if($res){
						$rule = RsgRequest::findOrFail($id);
						$step_to_tags = getStepIdToTags();
						if($rule->auto_send_status==0) {
							self::mailchimp($rule->customer_email, array_get($step_to_tags, $_POST['data']), []);
						}
						$this->insertRsgRequestsLog($rule);
					}

				}
			}
		}
		echo $res;
	}

	/*
	 * 查出帖子的price和coupon_n等信息，rsgrequest列表的funded那一列=star功能的Price-Coupon,
	 */
	public function getStarData()
	{
		$yestoday = date('Y-m-d',strtotime("-1 day"));
		$sql = "SELECT asin,domain,price,coupon_n
		FROM star_history
		WHERE create_at = '".$yestoday."' group by asin,domain";
		$_starData = $this->queryRows($sql);
		$starData = array();
		foreach($_starData as $key=>$val){
			$starData[$val['asin'].'_'.$val['domain']] = $val['price'] - $val['coupon_n'];
		}
		return $starData;
	}
}
