<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Asin;
use App\User;
use App\Star;
use App\Starhistory;
use App\Listinghistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Illuminate\Http\Response;
use App\RsgProduct;
class StarController extends Controller
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
		if(!Auth::user()->can(['asin-rating-show'])) die('Permission denied -- asin-rating-show');
		$date_from=date('Y-m-d',strtotime('-1 days'));	
		$date_to=date('Y-m-d',strtotime('-2 days'));		
		$teams= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
        return view('star/index',['date_from'=>$date_from ,'date_to'=>$date_to,  'users'=>$this->getUsers(),'teams'=>$teams]);

    }

    public function get()
    {
		$date_from=date('Y-m-d',strtotime('-1 days'));	
		$date_to=date('Y-m-d',strtotime('-2 days'));		
		if(array_get($_REQUEST,'date_from')) $date_from= array_get($_REQUEST,'date_from');
		if(array_get($_REQUEST,'date_to')) $date_to= array_get($_REQUEST,'date_to');
		$customers = DB::table( DB::raw("(select * from star_history where create_at = '".$date_from."') as star") )
			->select(DB::raw('star.* ,
			pre_star.one_star_number as pre_one_star_number,
			pre_star.two_star_number as pre_two_star_number,
			pre_star.three_star_number as pre_three_star_number,
			pre_star.four_star_number as pre_four_star_number,
			pre_star.five_star_number as pre_five_star_number,
			pre_star.total_star_number as pre_total_star_number,
			pre_star.average_score as pre_average_score,
			pre_star.create_at as pre_create_at,
			
			pre_star.coupon_p as pre_coupon_p,
			pre_star.coupon_n as pre_coupon_n,
			pre_star.price as pre_price,
			pre_star.sales as pre_sales,
			pre_star.amount as pre_amount,
			pre_star.sessions as pre_sessions,
			pre_star.unit_session_percentage as pre_unit_session_percentage,
			pre_star.bsr as pre_bsr,
			pre_star.status as pre_status,

			asin.item_status,asin.asin_status,asin.seller,asin.bg,asin.bu,asin.sap_seller_id as sap_seller_id,asin.review_user_id as review_user_id,asin.item_no,asin.star,asin.post_status,asin.post_type'))
			->leftJoin( DB::raw("(select * from star_history where create_at = '".$date_to."') as pre_star") ,function($q){
				$q->on('star.asin', '=', 'pre_star.asin')
					->on('star.domain', '=', 'pre_star.domain');
			})
			->leftJoin( DB::raw("(select max(star) as star,max(item_no) as item_no,max(bg) as bg,max(bu) as bu,max(seller) as seller,max(sap_seller_id) as sap_seller_id,max(review_user_id) as review_user_id,max(item_status) as item_status, min(case when status = 'S' Then '0' else status end) as asin_status,asin,site,max(post_status) as post_status,max(post_type) as post_type from asin where length(asin)=10 group by asin,site) as asin") ,function($q){
				$q->on('star.asin', '=', 'asin.asin')
					->on('star.domain', '=', 'asin.site');
			})->whereNotNull('asin.asin_status');
		
		if(!Auth::user()->can('asin-rating-show-all')) {
			if (Auth::user()->seller_rules) {
				$rules = explode("-", Auth::user()->seller_rules);
				if (array_get($rules, 0) != '*') $customers = $customers->where('asin.bg', array_get($rules, 0));
				if (array_get($rules, 1) != '*') $customers = $customers->where('asin.bu', array_get($rules, 1));
			} elseif (Auth::user()->sap_seller_id) {
				$datas = $datas->where('asin.sap_seller_id', Auth::user()->sap_seller_id);
			} else {
				$datas = $datas->where('asin.review_user_id', Auth::user()->id);
			}
		}
		


        if(array_get($_REQUEST,'keywords')){
            //$customers = $customers->where('subject', 'like', '%'.$_REQUEST['subject'].'%');
            $keywords = array_get($_REQUEST,'keywords');
            $customers = $customers->where(function ($query) use ($keywords) {

                $query->where('asin.item_no', 'like', '%'.$keywords.'%')
                        ->orwhere('asin.seller', 'like', '%'.$keywords.'%')
						 ->orwhere('star.asin', 'like', '%'.$keywords.'%')
						  ->orwhere('star.domain', 'like', '%'.$keywords.'%');

            });
        }
		
		
		if(array_get($_REQUEST,'bgbu')){
			   $bgbu = array_get($_REQUEST,'bgbu');
			   $bgbu_arr = explode('_',$bgbu);
			   if(array_get($bgbu_arr,0)) $customers = $customers->where('asin.bg',array_get($bgbu_arr,0));
			   if(array_get($bgbu_arr,1)) $customers = $customers->where('asin.bu',array_get($bgbu_arr,1));
		}
		
		if(array_get($_REQUEST,'rating_status')){
			if($_REQUEST['rating_status']=='Above')  $customers = $customers->where('star.average_score','>=','asin.star');
			if($_REQUEST['rating_status']=='Below')  $customers = $customers->where('star.average_score','<','asin.star');
            
        }
		if(Auth::user()->can(['asin-rating-show-all'])){
			if(array_get($_REQUEST,'user_id')){
				$customers = $customers->whereIn('asin.sap_seller_id',$_REQUEST['user_id']);
			}
		}
		if(array_get($_REQUEST,'site')){
			$customers = $customers->whereIn('star.domain',$_REQUEST['site']);
		}
		
		if(array_get($_REQUEST,'item_status')){
			$customers = $customers->where('asin.item_status',intval($_REQUEST['item_status'])-1);
		}
		
		// if(array_get($_REQUEST,'asin_status')){
		// 	$customers = $customers->where('asin.asin_status',($_REQUEST['asin_status']=='S'?'0':$_REQUEST['asin_status']));
		// }

		//帖子状态和帖子类型搜索
		if(array_get($_REQUEST,'post_status')){
			$customers = $customers->where('asin.post_status',($_REQUEST['post_status']));
		}
		if(array_get($_REQUEST,'post_type')){
			$customers = $customers->where('asin.post_type',($_REQUEST['post_type']));
		}
		
		if(array_get($_REQUEST,'listing_status')){
			$customers = $customers->where('star.status',intval($_REQUEST['listing_status'])-1);
		}
		
		if(array_get($_REQUEST,'price_status')){
			$customers = $customers->whereRaw('star.price '.array_get($_REQUEST,'price_status').' pre_star.price');
		}
		
		if(array_get($_REQUEST,'coupon_than') && array_get($_REQUEST,'coupon_type') && array_get($_REQUEST,'coupon_value')){
			$customers = $customers->where('star.'.array_get($_REQUEST,'coupon_type'),array_get($_REQUEST,'coupon_than'),array_get($_REQUEST,'coupon_value'));
		}
		
		if(array_get($_REQUEST,'star_from')) $customers = $customers->where('star.average_score','>=',round(array_get($_REQUEST,'star_from'),1));
		if(array_get($_REQUEST,'star_to')) $customers = $customers->where('star.average_score','<=',round(array_get($_REQUEST,'star_to'),1));

		$orderby = 'asin.asin';
        $sort = 'asc';

				
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==0) $orderby = 'star.asin';
            if($_REQUEST['order'][0]['column']==1) $orderby = 'asin.post_type';
			if($_REQUEST['order'][0]['column']==2) $orderby = 'asin.post_status';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'asin.item_no';
			if($_REQUEST['order'][0]['column']==4) $orderby = 'asin.item_status';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'asin.seller';
			if($_REQUEST['order'][0]['column']==6) $orderby = 'star.domain';
			
			if($_REQUEST['order'][0]['column']==7) $orderby = DB::raw("(star.total_star_number -( case when pre_star.total_star_number>0 then pre_star.total_star_number else 0 end))");
			if($_REQUEST['order'][0]['column']==8) $orderby = DB::raw("(star.average_score -( case when pre_star.average_score>0 then pre_star.average_score else 0 end))");
			if($_REQUEST['order'][0]['column']==9) $orderby = DB::raw("((star.five_star_number+star.four_star_number) -( case when (pre_star.five_star_number+pre_star.four_star_number)>0 then (pre_star.five_star_number+pre_star.four_star_number) else 0 end))");
			if($_REQUEST['order'][0]['column']==10) $orderby = DB::raw("((star.one_star_number+star.two_star_number+star.three_star_number) -( case when (pre_star.one_star_number+pre_star.two_star_number+pre_star.three_star_number)>0 then (pre_star.one_star_number+pre_star.two_star_number+pre_star.three_star_number) else 0 end))");
			if($_REQUEST['order'][0]['column']==11) $orderby = 'asin.star';
			
			if($_REQUEST['order'][0]['column']==14) $orderby = 'star.status';
			if($_REQUEST['order'][0]['column']==15) $orderby = 'star.price';
			if($_REQUEST['order'][0]['column']==16) $orderby = 'star.coupon_p';
			if($_REQUEST['order'][0]['column']==17) $orderby = 'star.coupon_n';
			
			
			if($_REQUEST['order'][0]['column']==18) $orderby = 'star.total_star_number';
			if($_REQUEST['order'][0]['column']==19) $orderby = 'star.average_score';
			if($_REQUEST['order'][0]['column']==20) $orderby = 'star.one_star_number';
			if($_REQUEST['order'][0]['column']==21) $orderby = 'star.two_star_number';
			if($_REQUEST['order'][0]['column']==22) $orderby = 'star.three_star_number';
			if($_REQUEST['order'][0]['column']==23) $orderby = 'star.four_star_number';
			if($_REQUEST['order'][0]['column']==24) $orderby = 'star.five_star_number';
			
			if($_REQUEST['order'][0]['column']==26) $orderby = 'pre_star.status';
			if($_REQUEST['order'][0]['column']==27) $orderby = 'pre_star.price';
			if($_REQUEST['order'][0]['column']==28) $orderby = 'pre_star.coupon_p';
			if($_REQUEST['order'][0]['column']==29) $orderby = 'pre_star.coupon_n';
			
			if($_REQUEST['order'][0]['column']==30) $orderby = 'pre_star.total_star_number';
			if($_REQUEST['order'][0]['column']==31) $orderby = 'pre_star.average_score';
			if($_REQUEST['order'][0]['column']==32) $orderby = 'pre_star.one_star_number';
			if($_REQUEST['order'][0]['column']==33) $orderby = 'pre_star.two_star_number';
			if($_REQUEST['order'][0]['column']==34) $orderby = 'pre_star.three_star_number';
			if($_REQUEST['order'][0]['column']==35) $orderby = 'pre_star.four_star_number';
			if($_REQUEST['order'][0]['column']==36) $orderby = 'pre_star.five_star_number';
            $sort = $_REQUEST['order'][0]['dir'];
			
			
        }
		
        $ordersList =  $customers->orderBy($orderby,$sort)->get()->toArray();
		$ordersList =json_decode(json_encode($ordersList), true);
		
	
        $iTotalRecords = count($ordersList);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);

        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

		$postStatus = getPostStatus();//帖子状态状态对应值
		$postType = getPostType();//帖子类型状态对应值
		
		$users_array = $this->getUsers();
        for($i = $iDisplayStart; $i < $end; $i++) {
		
			$result = $ordersList[$i]['total_star_number']-$ordersList[$i]['pre_total_star_number'];
			if( $result >0 ) $diff_total_star_number =  "<span class=\"label label-sm label-success\">".$result."</span>";
			if( $result <0 ) $diff_total_star_number =  "<span class=\"label label-sm label-danger\">".$result."</span>";
			if( $result ==0 ) $diff_total_star_number =  "--";
								
			$result = $ordersList[$i]['average_score']-$ordersList[$i]['pre_average_score'];
			if( $result >0 ) $diff_average_score =  "<span class=\"label label-sm label-success\">".$result."</span>";
			if( $result <0 ) $diff_average_score = "<span class=\"label label-sm label-danger\">".$result."</span>";
			if( $result ==0 ) $diff_average_score = "--";
			
			$result = $ordersList[$i]['five_star_number']-$ordersList[$i]['pre_five_star_number']+$ordersList[$i]['four_star_number']-$ordersList[$i]['pre_four_star_number'];
			if( $result >0 ) $diff_positive =  "<span class=\"label label-sm label-success\">".$result."</span>";
			if( $result <0 ) $diff_positive = "<span class=\"label label-sm label-danger\">".$result."</span>";
			if( $result ==0 ) $diff_positive = "--";
			
			$result = $ordersList[$i]['three_star_number']-$ordersList[$i]['pre_three_star_number']+$ordersList[$i]['two_star_number']-$ordersList[$i]['pre_two_star_number']+$ordersList[$i]['one_star_number']-$ordersList[$i]['pre_one_star_number'];
			if( $result >0 ) $diff_negative = "<span class=\"label label-sm label-success\">".$result."</span>";
			if( $result <0 ) $diff_negative = "<span class=\"label label-sm label-danger\">".$result."</span>";
			if( $result ==0 ) $diff_negative = "--";
								
			
					
			if( $ordersList[$i]['average_score'] >$ordersList[$i]['star'] ) $diff_star =  "<span class=\"label label-sm label-success\">Normal</span>";
			if( $ordersList[$i]['average_score'] <$ordersList[$i]['star'] ) $diff_star = "<span class=\"label label-sm label-danger\">Danger</span>";
			if( $ordersList[$i]['average_score'] ==$ordersList[$i]['star'] ) $diff_star = "<span class=\"label label-sm label-warning\">Warning</span>";		
			$records["data"][] = array(
				'<a data-target="#ajax" data-toggle="modal" href="'.url('star/show/'.$ordersList[$i]['asin'].'/'.$ordersList[$i]['domain']).'">'.$ordersList[$i]['asin'].'</a>',

				isset($postType[$ordersList[$i]['post_type']]['name']) ? $postType[$ordersList[$i]['post_type']]['name'] : $ordersList[$i]['post_type'],//帖子类型
				isset($postStatus[$ordersList[$i]['post_status']]['name']) ? $postStatus[$ordersList[$i]['post_status']]['name'] : $ordersList[$i]['post_status'],//帖子状态

				// $ordersList[$i]['asin_status']?$ordersList[$i]['asin_status']:'S',
				$ordersList[$i]['item_no'],
				($ordersList[$i]['item_status'])?'<span class="btn btn-success btn-xs">Reserved</span>':'<span class="btn btn-danger btn-xs">Eliminate</span>',
				$ordersList[$i]['seller'],
				$ordersList[$i]['domain'],
				$diff_total_star_number,
				$diff_average_score,
				$diff_positive,
				$diff_negative,
				$ordersList[$i]['star'],
				$diff_star,

				$ordersList[$i]['create_at'],
				($ordersList[$i]['status']==2)?'<span class="btn btn-success btn-xs">Available</span>':(($ordersList[$i]['status']==1)?'<span class="btn btn-warning btn-xs">UnAvailable</span>':'<span class="btn btn-danger btn-xs">Down</span>'),
				($ordersList[$i]['price']>$ordersList[$i]['pre_price'])?'<span class="btn btn-danger btn-xs">'.round($ordersList[$i]['price'],2).'</span>':( ($ordersList[$i]['price']<$ordersList[$i]['pre_price'])?'<span class="btn btn-success btn-xs">'.round($ordersList[$i]['price'],2).'</span>':round($ordersList[$i]['price'],2)),
				($ordersList[$i]['coupon_p']>$ordersList[$i]['pre_coupon_p'])?'<span class="btn btn-danger btn-xs">'.round($ordersList[$i]['coupon_p'],2).'</span>':( ($ordersList[$i]['coupon_p']<$ordersList[$i]['pre_coupon_p'])?'<span class="btn btn-success btn-xs">'.round($ordersList[$i]['coupon_p'],2).'</span>':round($ordersList[$i]['coupon_p'],2)),
				($ordersList[$i]['coupon_n']>$ordersList[$i]['pre_coupon_n'])?'<span class="btn btn-danger btn-xs">'.round($ordersList[$i]['coupon_n'],2).'</span>':( ($ordersList[$i]['coupon_n']<$ordersList[$i]['pre_coupon_n'])?'<span class="btn btn-success btn-xs">'.round($ordersList[$i]['coupon_n'],2).'</span>':round($ordersList[$i]['coupon_n'],2)),
				$ordersList[$i]['total_star_number'],
				round($ordersList[$i]['average_score'],2),
				$ordersList[$i]['one_star_number'],
				$ordersList[$i]['two_star_number'],
				$ordersList[$i]['three_star_number'],
				$ordersList[$i]['four_star_number'],
				$ordersList[$i]['five_star_number'],
				$ordersList[$i]['pre_create_at'],
				($ordersList[$i]['pre_status']==2)?'<span class="btn btn-success btn-xs">Available</span>':(($ordersList[$i]['pre_status']==1)?'<span class="btn btn-warning btn-xs">UnAvailable</span>':'<span class="btn btn-danger btn-xs">Down</span>'),
				round($ordersList[$i]['pre_price'],2),
				round($ordersList[$i]['pre_coupon_p'],2),
				round($ordersList[$i]['pre_coupon_n'],2),
				$ordersList[$i]['pre_total_star_number'],
				round($ordersList[$i]['pre_average_score'],2),
				$ordersList[$i]['pre_one_star_number'],
				$ordersList[$i]['pre_two_star_number'],
				$ordersList[$i]['pre_three_star_number'],
				$ordersList[$i]['pre_four_star_number'],
				$ordersList[$i]['pre_five_star_number'],

				'<a class="btn btn-success editAction" data-asin="'.$ordersList[$i]['asin'].'" data-domain="'.$ordersList[$i]['domain'].'" data-postStatus="'.$ordersList[$i]['post_status'].'" data-postType="'.$ordersList[$i]['post_type'].'" href="javascript:void(0)">Edit</a>'//添加编辑操作

			);
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);

    }
	
	public function getUsers(){
        $users = User::where('sap_seller_id','>',0)->get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['sap_seller_id']] = $user['name'];
        }
        return $users_array;
    }
	
	
	
    public function checkAccount($request){
        $id = ($request->get('id'))?($request->get('id')):0;

        $seller_account = Review::where('review',$request->get('review'))->where('site',$request->get('site'))->where('id','<>',$id)
            ->first();
        if($seller_account) return true;
        return false;
    }

	public function show($asin,$domain){
		return view('star/edit',['asin'=>$asin ,'domain'=>$domain]);
	}
	
	public function detail(){
		$asin_from= array_get($_REQUEST,'asin_from');
		$asin_to= array_get($_REQUEST,'asin_to');
		$pie_type= array_get($_REQUEST,'pie_type');
		$asin= array_get($_REQUEST,'asin');
		$domain= array_get($_REQUEST,'domain');
		$datas = Starhistory::where('asin',$asin)->where('domain',$domain)->where('create_at','<=',$asin_to)->where('create_at','>=',$asin_from)->get()->toArray();
		foreach($datas as $data){
			$returnData[$data['create_at']]['review']= round($data['total_star_number'],2);
			$returnData[$data['create_at']]['rating']= round($data['average_score'],2);
			$returnData[$data['create_at']]['price']= round($data['price'],2);
			$returnData[$data['create_at']]['sales']= round($data['sales'],2);
			$returnData[$data['create_at']]['sale_price']= round($data['price']-$data['coupon_n'],2);
			$returnData[$data['create_at']]['avg_price']= ($data['sales']>0)?round($data['amount']/$data['sales'],2):0;
			$returnData[$data['create_at']]['sessions']= round($data['sessions'],2);
			$returnData[$data['create_at']]['unit_session_percentage']= round($data['unit_session_percentage'],2);
			$returnData[$data['create_at']]['bsr']= round($data['bsr'],2);
		}
		echo json_encode($returnData);
		
	}

	/*
	 * 更新帖子状态和帖子类型
	 */
	public function updatePost()
	{
		$asin = isset($_POST['asin']) && $_POST['asin'] ? $_POST['asin'] : '';
		$domain = isset($_POST['domain']) && $_POST['domain'] ? $_POST['domain'] : '';

		$updateData = $updateProduct = array();
		if(isset($_POST['post-status']) && $_POST['post-status']){
			$updateData['post_status'] = $updateProduct['post_status'] = $_POST['post-status'];
			if($updateData['post_status']==2){
				$updateData['push_date'] = date('Y-m-d H:i:s');
			}
		}
		if(isset($_POST['post-type']) && $_POST['post-type']){
			$updateData['post_type'] = $updateProduct['post_type'] = $_POST['post-type'];
		}
		$res = 1;
		if(Asin::where('asin',$asin)->where('site',$domain)->update($updateData)){
			//更新rsg产品表的帖子状态和帖子类型
			$date = date('Y-m-d');
			$updateProduct['updated_at'] = date('Y-m-d H:i:s');
			RsgProduct::where('asin',$asin)->where('site',$domain)->where('created_at',$date)->update($updateProduct);
		}
		echo $res;
	}
}