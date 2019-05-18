<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Groupdetail;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
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
    public function index(Request $request)
    {
		$where= $where_star= $where_review='';
		$site = $request->get('site');
		if(Auth::user()->admin){
			$bgbu = $request->get('bgbu');
			$user_id = $request->get('user_id');
			if($bgbu){
				   $bgbu_arr = explode('_',$bgbu);
				   if(array_get($bgbu_arr,0)){
						$where_star.= "and b.bg='".array_get($bgbu_arr,0)."'";
						$where_review.= "and b.bg='".array_get($bgbu_arr,0)."'";
				   }
				   if(array_get($bgbu_arr,1)){
						$where_star.= "and b.bu='".array_get($bgbu_arr,1)."'";
						$where_review.= "and b.bu='".array_get($bgbu_arr,1)."'";
				   }
			}
		}else{
			$bgbu='';
			$user_id = Auth::user()->id;
		}
		
		$date_start = $request->get('date_start')?$request->get('date_start'):date('Y-m-d',strtotime('- 7days'));
		$date_end = $request->get('date_end')?$request->get('date_end'):date('Y-m-d',strtotime('- 1days'));
		if($date_end > date('Y-m-d',strtotime('- 1days'))) $date_end=date('Y-m-d',strtotime('- 1days'));
		if($date_start>$date_end) $date_start = $date_end;
		$asin = $request->get('asin');
		
		if($site){
			$where_star.= "and a.domain='".$site."'";
			$where.= "and b.site='".$site."'";
			$where_review.= "and a.site='".$site."'";
		}
		
		
		if($user_id){
			$where_star.= "and b.user_id=".$user_id;
			$where.= "and b.review_user_id=".$user_id;
			$where_review.= "and a.user_id=".$user_id;

		}
		if($asin){
			$where_star.= "and a.asin='".$asin."'";
			$where.= "and b.asin='".$asin."'";
			$where_review.= "and a.asin='".$asin."'";
		}
		
		$asins = DB::select("select count(*) as count from (select asin,site from asin b where CHAR_LENGTH(asin)=10 ".$where." group by asin,site) as a");
		$returnDate['total_asins']=$asins[0]->count;
		
		$stars = DB::select("select avg(average_score) as avg_rating,sum(five_star_number) as five_star_number 
,sum(four_star_number) as four_star_number
,sum(three_star_number) as three_star_number
,sum(two_star_number) as two_star_number
,sum(one_star_number) as one_star_number,a.create_at
from star_history a
left join (select asin,site,max(bg) as bg,max(bu) as bu,max(review_user_id) as user_id from asin group by asin,site) as b
on a.domain=b.site and a.asin=b.asin
where a.create_at>='".$date_start."' and a.create_at<='".$date_end."' ".$where_star." group by create_at order by create_at asc");

		$star_count = count($stars);
		
		
		$reviews = DB::select("select count(*) as count, sum(negative_value) as sum_importance,
(case 
	when status>1 and status<100 then 99
	else status
	End) as nstatus,date from review a
left join (select asin,site,max(bg) as bg,max(bu) as bu,max(review_user_id) as user_id from asin group by asin,site) as b
on a.site=b.site and a.asin=b.asin
where a.date>='".$date_start."' and a.date<='".$date_end."' ".$where_review." group by nstatus,date order by date asc");
		$returnDate['ntask'] = $returnDate['nimp'] = $returnDate['ctask'] = 0;
		$review_status_arr=[];
		$char_2 = [];
		$char_2_y_arr = $char_2_x_arr = $char_2_y_array =[];
		foreach($reviews as $review){
			$returnDate['ntask']+=$review->count;
			$returnDate['nimp']+=$review->sum_importance;
			if($review->nstatus==99) $returnDate['ctask']+=$review->count;
			if(!in_array($review->date,$char_2_x_arr)) $char_2_x_arr[]=$review->date;
			$char_2[$review->date][$review->nstatus]=$review->count;
		}	
		
		
		$follow_status_array[1]='None';
		$follow_status_array[99]='Closed';
		$steps = DB::table('review_step')->get();
		foreach($steps as $step){
			$follow_status_array[$step->id]=$step->title;
		}
		//die(json_encode($char_2_x_arr));
		$follow_status_arr=[];
		foreach($follow_status_array as $k=>$v){
			foreach($char_2_x_arr as $x_v){
				$char_2_y_array[$k][]=array_get($char_2,$x_v.'.'.$k,0);
			}
			
			$char_2_y_arr[]=array(
				'name'=>$v,
				'type'=>'line',
				'data'=>array_get($char_2_y_array,$k,[])
				);
			$follow_status_arr[]=$v;
		}
		$returnDate['char_2_y_arr'] = $char_2_y_arr;
		$returnDate['chat_2_x'] = $char_2_x_arr;
		$returnDate['follow_status_array'] = $follow_status_arr;
		
		$returnDate['ptask']=($returnDate['ntask'])?round($returnDate['ctask']/$returnDate['ntask']*100,2):0;
		if($star_count>0){
		$returnDate['avg_rating']=round($stars[$star_count-1]->avg_rating,2);
		$returnDate['five_star_number']=$stars[$star_count-1]->five_star_number;
		$returnDate['four_star_number']=$stars[$star_count-1]->four_star_number;
		$returnDate['three_star_number']=$stars[$star_count-1]->three_star_number;
		$returnDate['two_star_number']=$stars[$star_count-1]->two_star_number;
		$returnDate['one_star_number']=$stars[$star_count-1]->one_star_number;
		$returnDate['review_count']=$stars[$star_count-1]->five_star_number+$stars[$star_count-1]->four_star_number+$stars[$star_count-1]->three_star_number+$stars[$star_count-1]->two_star_number+$stars[$star_count-1]->one_star_number;
		
			$returnDate['avg_rating_change']=round(($stars[$star_count-1]->avg_rating)-($stars[0]->avg_rating),2);
			$returnDate['five_star_number_change']=$stars[$star_count-1]->five_star_number - $stars[0]->five_star_number;
			$returnDate['four_star_number_change']=$stars[$star_count-1]->four_star_number - $stars[0]->four_star_number;
			$returnDate['three_star_number_change']=$stars[$star_count-1]->three_star_number - $stars[0]->three_star_number;
			$returnDate['two_star_number_change']=$stars[$star_count-1]->two_star_number - $stars[0]->two_star_number;
			$returnDate['one_star_number_change']=$stars[$star_count-1]->one_star_number - $stars[0]->one_star_number;
			$returnDate['review_count_change']=$returnDate['five_star_number_change']+$returnDate['four_star_number_change']+$returnDate['three_star_number_change']+$returnDate['two_star_number_change']+$returnDate['one_star_number_change'];
		}else{
			$returnDate['avg_rating']=$returnDate['five_star_number']=$returnDate['four_star_number']=$returnDate['three_star_number']=$returnDate['two_star_number']=$returnDate['one_star_number']=$returnDate['review_count']=$returnDate['avg_rating_change']=$returnDate['five_star_number_change']=$returnDate['four_star_number_change']=$returnDate['three_star_number_change']=$returnDate['two_star_number_change']=$returnDate['one_star_number_change']=$returnDate['review_count_change']=0;
		}
		
		$returnDate['chat_1_x'] = $returnDate['chat_1_y_reviewcount'] = $returnDate['chat_1_y_reviewrating'] = $returnDate['chat_2_y_1'] = $returnDate['chat_2_y_2'] = $returnDate['chat_2_y_3'] = $returnDate['chat_2_y_4'] = $returnDate['chat_2_y_5'] = [];
		
		foreach($stars as $star){
			$returnDate['chat_1_x'][]= $star->create_at;
			$returnDate['chat_1_y_reviewcount'][]= $star->five_star_number+$star->four_star_number+$star->three_star_number+$star->two_star_number+$star->one_star_number;
			$returnDate['chat_1_y_reviewrating'][]= round($star->avg_rating,2);
			$returnDate['chat_2_y_1'][]= round($star->one_star_number,2);
			$returnDate['chat_2_y_2'][]= round($star->two_star_number,2);
			$returnDate['chat_2_y_3'][]= round($star->three_star_number,2);
			$returnDate['chat_2_y_4'][]=round($star->four_star_number,2);
			$returnDate['chat_2_y_5'][]=round($star->five_star_number,2);
		}
		
		$returnDate['teams']= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
		$returnDate['users']= $this->getUsers();
		$returnDate['date_start']= $date_start;
		$returnDate['date_end']= $date_end;
		$returnDate['s_user_id']= $user_id;
		$returnDate['bgbu']= $bgbu;
		$returnDate['asin']= $asin;
		$returnDate['s_site']= $site;
        return view('home',$returnDate);

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