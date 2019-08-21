<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Asin;
use App\User;
use App\Star;
use App\Starhistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Illuminate\Http\Response;
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
	
        return view('star/index',['date_from'=>$date_from ,'date_to'=>$date_to,  'users'=>$this->getUsers()]);

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
			asin.item_status,asin.asin_status,asin.seller,asin.review_user_id as user_id,asin.item_no,asin.star,
			listing.price,listing.status as listing_status,listing.coupon_p,listing.coupon_n,
			pre_listing.price as pre_price,pre_listing.status as pre_listing_status,pre_listing.coupon_p as pre_coupon_p,pre_listing.coupon_n as pre_coupon_n'))
			->leftJoin( DB::raw("(select * from star_history where create_at = '".$date_to."') as pre_star") ,function($q){
				$q->on('star.asin', '=', 'pre_star.asin')
					->on('star.domain', '=', 'pre_star.domain');
			})
			->leftJoin( DB::raw("(select * from listing_history where date = '".$date_to."') as pre_listing") ,function($q){
				$q->on('star.asin', '=', 'pre_listing.asin')
					->on('star.domain', '=', 'pre_listing.domain');
			})
			->leftJoin( DB::raw("(select * from listing_history where date = '".$date_from."') as listing") ,function($q){
				$q->on('star.asin', '=', 'listing.asin')
					->on('star.domain', '=', 'listing.domain');
			})
			->leftJoin( DB::raw("(select max(star) as star,max(item_no) as item_no,max(seller) as seller,max(review_user_id) as review_user_id,max(item_status) as item_status, min(case when status = 'S' Then '0' else status end) as asin_status,asin,site from asin where length(asin)=10 group by asin,site) as asin") ,function($q){
				$q->on('star.asin', '=', 'asin.asin')
					->on('star.domain', '=', 'asin.site');
			});
		
		if(!Auth::user()->can(['asin-rating-show-all'])){ 
            $customers = $customers->where('asin.review_user_id',$this->getUserId());
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

		
		if(array_get($_REQUEST,'rating_status')){
			if($_REQUEST['rating_status']=='Above')  $customers = $customers->where('star.average_score','>=','asin.star');
			if($_REQUEST['rating_status']=='Below')  $customers = $customers->where('star.average_score','<','asin.star');
            
        }
		if(Auth::user()->can(['asin-rating-show-all'])){
			if(array_get($_REQUEST,'user_id')){
				$customers = $customers->whereIn('asin.review_user_id',$_REQUEST['user_id']);
			}
		}
		if(array_get($_REQUEST,'site')){
			$customers = $customers->whereIn('star.domain',$_REQUEST['site']);
		}
		
		if(array_get($_REQUEST,'item_status')){
			$customers = $customers->where('asin.item_status',intval($_REQUEST['item_status'])-1);
		}
		
		if(array_get($_REQUEST,'asin_status')){
			$customers = $customers->where('asin.asin_status',($_REQUEST['asin_status']=='S'?'0':$_REQUEST['asin_status']));
		}
		
		if(array_get($_REQUEST,'listing_status')){
			$customers = $customers->where('listing.status',intval($_REQUEST['listing_status'])-1);
		}
		
		if(array_get($_REQUEST,'price_status')){
			$customers = $customers->whereRaw('listing.price '.array_get($_REQUEST,'price_status').' pre_listing.price');
		}
		
		if(array_get($_REQUEST,'coupon_than') && array_get($_REQUEST,'coupon_type') && array_get($_REQUEST,'coupon_value')){
			$customers = $customers->where('listing.'.array_get($_REQUEST,'coupon_type'),array_get($_REQUEST,'coupon_than'),array_get($_REQUEST,'coupon_value'));
		}
		
		if(array_get($_REQUEST,'star_from')) $customers = $customers->where('star.average_score','>=',round(array_get($_REQUEST,'star_from'),1));
		if(array_get($_REQUEST,'star_to')) $customers = $customers->where('star.average_score','<=',round(array_get($_REQUEST,'star_to'),1));

		$orderby = DB::raw("(star.total_star_number -( case when pre_star.total_star_number>0 then pre_star.total_star_number else 0 end))");
        $sort = 'asc';

				
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==0) $orderby = 'star.asin';
            if($_REQUEST['order'][0]['column']==1) $orderby = 'asin.asin_status';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'asin.item_no';
			if($_REQUEST['order'][0]['column']==3) $orderby = 'asin.item_status';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'asin.seller';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'asin.review_user_id';
			if($_REQUEST['order'][0]['column']==7) $orderby = DB::raw("(star.total_star_number -( case when pre_star.total_star_number>0 then pre_star.total_star_number else 0 end))");
			if($_REQUEST['order'][0]['column']==8) $orderby = DB::raw("(star.average_score -( case when pre_star.average_score>0 then pre_star.average_score else 0 end))");
			if($_REQUEST['order'][0]['column']==9) $orderby = DB::raw("((star.five_star_number+star.four_star_number) -( case when (pre_star.five_star_number+pre_star.four_star_number)>0 then (pre_star.five_star_number+pre_star.four_star_number) else 0 end))");
			if($_REQUEST['order'][0]['column']==10) $orderby = DB::raw("((star.one_star_number+star.two_star_number+star.three_star_number) -( case when (pre_star.one_star_number+pre_star.two_star_number+pre_star.three_star_number)>0 then (pre_star.one_star_number+pre_star.two_star_number+pre_star.three_star_number) else 0 end))");
			if($_REQUEST['order'][0]['column']==11) $orderby = 'asin.star';
			if($_REQUEST['order'][0]['column']==17) $orderby = 'listing.price';
			if($_REQUEST['order'][0]['column']==18) $orderby = 'listing.coupon_p';
			if($_REQUEST['order'][0]['column']==19) $orderby = 'listing.coupon_n';
			
			
			if($_REQUEST['order'][0]['column']==20) $orderby = 'star.total_star_number';
			if($_REQUEST['order'][0]['column']==21) $orderby = 'star.average_score';
			if($_REQUEST['order'][0]['column']==22) $orderby = 'star.one_star_number';
			if($_REQUEST['order'][0]['column']==23) $orderby = 'star.two_star_number';
			if($_REQUEST['order'][0]['column']==24) $orderby = 'star.three_star_number';
			if($_REQUEST['order'][0]['column']==25) $orderby = 'star.four_star_number';
			if($_REQUEST['order'][0]['column']==26) $orderby = 'star.five_star_number';
			
			
			if($_REQUEST['order'][0]['column']==29) $orderby = 'pre_listing.price';
			if($_REQUEST['order'][0]['column']==30) $orderby = 'pre_listing.coupon_p';
			if($_REQUEST['order'][0]['column']==31) $orderby = 'pre_listing.coupon_n';
			
			if($_REQUEST['order'][0]['column']==32) $orderby = 'pre_star.total_star_number';
			if($_REQUEST['order'][0]['column']==33) $orderby = 'pre_star.average_score';
			if($_REQUEST['order'][0]['column']==34) $orderby = 'pre_star.one_star_number';
			if($_REQUEST['order'][0]['column']==35) $orderby = 'pre_star.two_star_number';
			if($_REQUEST['order'][0]['column']==36) $orderby = 'pre_star.three_star_number';
			if($_REQUEST['order'][0]['column']==37) $orderby = 'pre_star.four_star_number';
			if($_REQUEST['order'][0]['column']==38) $orderby = 'pre_star.five_star_number';
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
								
			if(	$ordersList[$i]['total_star_number']>0){				
				$my_average_score = floor((($ordersList[$i]['one_star_number'] + 2*$ordersList[$i]['two_star_number']+3*$ordersList[$i]['three_star_number']+4*$ordersList[$i]['four_star_number']+5*$ordersList[$i]['five_star_number'])/$ordersList[$i]['total_star_number'])*10)/10;
			}else{
				$my_average_score=0;
			}
			
			if($my_average_score >1.1){
				$decrease =  ceil(($ordersList[$i]['one_star_number'] + 2*$ordersList[$i]['two_star_number']+3*$ordersList[$i]['three_star_number']+4*$ordersList[$i]['four_star_number']+5*$ordersList[$i]['five_star_number']-($my_average_score-0.1)*$ordersList[$i]['total_star_number'])/($my_average_score-1.1));
			}else{
				$decrease = 0;
			
			}
			
			
			if($my_average_score <4.9){
				$increase = ceil( (($my_average_score+0.1)*$ordersList[$i]['total_star_number'] - $ordersList[$i]['one_star_number'] - 2*$ordersList[$i]['two_star_number'] -3*$ordersList[$i]['three_star_number']-4*$ordersList[$i]['four_star_number']-5*$ordersList[$i]['five_star_number'])/(4.9-$my_average_score) );

			}else{
				$increase=0;
			}					
					
			if( $ordersList[$i]['average_score'] >$ordersList[$i]['star'] ) $diff_star =  "<span class=\"label label-sm label-success\">Normal</span>";
			if( $ordersList[$i]['average_score'] <$ordersList[$i]['star'] ) $diff_star = "<span class=\"label label-sm label-danger\">Danger</span>";
			if( $ordersList[$i]['average_score'] ==$ordersList[$i]['star'] ) $diff_star = "<span class=\"label label-sm label-warning\">Warning</span>";		
			$records["data"][] = array(
				'<a href="https://'.$ordersList[$i]['domain'].'/dp/'.$ordersList[$i]['asin'].'" target="_blank">'.$ordersList[$i]['asin'].'</a>',
				$ordersList[$i]['asin_status']?$ordersList[$i]['asin_status']:'S',
				$ordersList[$i]['item_no'],
				($ordersList[$i]['item_status'])?'<span class="btn btn-success btn-xs">Reserved</span>':'<span class="btn btn-danger btn-xs">Eliminate</span>',
				$ordersList[$i]['seller'],
				array_get($users_array,intval(array_get($ordersList[$i],'user_id')),''),
				$ordersList[$i]['domain'],
				$diff_total_star_number,
				$diff_average_score,
				$diff_positive,
				$diff_negative,
				$ordersList[$i]['star'],
				$diff_star,
				$increase,
				$decrease,
				$ordersList[$i]['create_at'],
				($ordersList[$i]['listing_status']==2)?'<span class="btn btn-success btn-xs">Available</span>':(($ordersList[$i]['listing_status']==1)?'<span class="btn btn-warning btn-xs">UnAvailable</span>':'<span class="btn btn-danger btn-xs">Down</span>'),
				($ordersList[$i]['price']>$ordersList[$i]['pre_price'])?'<span class="btn btn-danger btn-xs">'.round($ordersList[$i]['price'],2).'</span>':( ($ordersList[$i]['price']<$ordersList[$i]['pre_price'])?'<span class="btn btn-success btn-xs">'.round($ordersList[$i]['price'],2).'</span>':round($ordersList[$i]['price'],2)),
				($ordersList[$i]['coupon_p']>$ordersList[$i]['pre_coupon_p'])?'<span class="btn btn-danger btn-xs">'.round($ordersList[$i]['coupon_p'],2).'</span>':( ($ordersList[$i]['coupon_p']<$ordersList[$i]['pre_coupon_p'])?'<span class="btn btn-success btn-xs">'.round($ordersList[$i]['coupon_p'],2).'</span>':round($ordersList[$i]['coupon_p'],2)),
				($ordersList[$i]['coupon_n']>$ordersList[$i]['pre_coupon_n'])?'<span class="btn btn-danger btn-xs">'.round($ordersList[$i]['coupon_n'],2).'</span>':( ($ordersList[$i]['coupon_n']<$ordersList[$i]['pre_coupon_n'])?'<span class="btn btn-success btn-xs">'.round($ordersList[$i]['coupon_n'],2).'</span>':round($ordersList[$i]['coupon_n'],2)),
				$ordersList[$i]['total_star_number'],
				$ordersList[$i]['average_score'],
				$ordersList[$i]['one_star_number'],
				$ordersList[$i]['two_star_number'],
				$ordersList[$i]['three_star_number'],
				$ordersList[$i]['four_star_number'],
				$ordersList[$i]['five_star_number'],
				$ordersList[$i]['pre_create_at'],
				$ordersList[$i]['pre_listing_status'],
				$ordersList[$i]['pre_price'],
				$ordersList[$i]['pre_coupon_p'],
				$ordersList[$i]['pre_coupon_n'],
				$ordersList[$i]['pre_total_star_number'],
				$ordersList[$i]['pre_average_score'],
				$ordersList[$i]['pre_one_star_number'],
				$ordersList[$i]['pre_two_star_number'],
				$ordersList[$i]['pre_three_star_number'],
				$ordersList[$i]['pre_four_star_number'],
				$ordersList[$i]['pre_five_star_number'],
				
				
				
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
	
	
	
    public function checkAccount($request){
        $id = ($request->get('id'))?($request->get('id')):0;

        $seller_account = Review::where('review',$request->get('review'))->where('site',$request->get('site'))->where('id','<>',$id)
            ->first();
        if($seller_account) return true;
        return false;
    }

}