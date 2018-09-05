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
			asin.brand_line,asin.seller,asin.review_user_id as user_id,asin.item_no,asin.star'))
			->leftJoin( DB::raw("(select * from star_history where create_at = '".$date_to."') as pre_star") ,function($q){
				$q->on('star.asin', '=', 'pre_star.asin')
					->on('star.sellersku', '=', 'pre_star.sellersku')
					->on('star.domain', '=', 'pre_star.domain');
			})
			->leftJoin( 'asin' ,function($q){
				$q->on('star.asin', '=', 'asin.asin')
				->on('star.sellersku', '=', 'asin.sellersku')
					->on('star.domain', '=', 'asin.site');
			});
		
		if(!Auth::user()->admin){
            $customers = $customers->where('asin.review_user_id',$this->getUserId());
        }
		
		


        if(array_get($_REQUEST,'keywords')){
            //$customers = $customers->where('subject', 'like', '%'.$_REQUEST['subject'].'%');
            $keywords = array_get($_REQUEST,'keywords');
            $customers = $customers->where(function ($query) use ($keywords) {

                $query->where('asin.brand_line'  , 'like', '%'.$keywords.'%')
                        ->orwhere('asin.item_no', 'like', '%'.$keywords.'%')
                        ->orwhere('asin.seller', 'like', '%'.$keywords.'%')
						 ->orwhere('star.asin', 'like', '%'.$keywords.'%')
						  ->orwhere('star.sellersku', 'like', '%'.$keywords.'%')
						  ->orwhere('star.domain', 'like', '%'.$keywords.'%');

            });
        }

		
		if(array_get($_REQUEST,'asin_status')){
			if($_REQUEST['asin_status']=='Above')  $customers = $customers->where('star.average_score','>=','asin.star');
			if($_REQUEST['asin_status']=='Below')  $customers = $customers->where('star.average_score','<','asin.star');
            
        }
		if(Auth::user()->admin){
			if(array_get($_REQUEST,'user_id')){
				$customers = $customers->whereIn('asin.review_user_id',$_REQUEST['user_id']);
			}
		}
		
		
		if(array_get($_REQUEST,'star_from')) $customers = $customers->where('star.average_score','>=',round(array_get($_REQUEST,'star_from'),1));
		if(array_get($_REQUEST,'star_to')) $customers = $customers->where('star.average_score','<=',round(array_get($_REQUEST,'star_to'),1));

		$orderby = DB::raw("(star.total_star_number -( case when pre_star.total_star_number>0 then pre_star.total_star_number else 0 end))");
        $sort = 'asc';

				
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'star.asin';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'asin.sellersku';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'asin.item_no';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'asin.seller';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'asin.review_user_id';
			if($_REQUEST['order'][0]['column']==7) $orderby = DB::raw("(star.total_star_number -( case when pre_star.total_star_number>0 then pre_star.total_star_number else 0 end))");
			if($_REQUEST['order'][0]['column']==8) $orderby = DB::raw("(star.average_score -( case when pre_star.average_score>0 then pre_star.average_score else 0 end))");
			if($_REQUEST['order'][0]['column']==9) $orderby = DB::raw("((star.five_star_number+star.four_star_number) -( case when (pre_star.five_star_number+pre_star.four_star_number)>0 then (pre_star.five_star_number+pre_star.four_star_number) else 0 end))");
			if($_REQUEST['order'][0]['column']==10) $orderby = DB::raw("((star.one_star_number+star.two_star_number+star.three_star_number) -( case when (pre_star.one_star_number+pre_star.two_star_number+pre_star.three_star_number)>0 then (pre_star.one_star_number+pre_star.two_star_number+pre_star.three_star_number) else 0 end))");
			if($_REQUEST['order'][0]['column']==11) $orderby = 'asin.star';
			if($_REQUEST['order'][0]['column']==15) $orderby = 'star.create_at';
			if($_REQUEST['order'][0]['column']==16) $orderby = 'star.total_star_number';
			if($_REQUEST['order'][0]['column']==17) $orderby = 'star.average_score';
			if($_REQUEST['order'][0]['column']==18) $orderby = 'star.one_star_number';
			if($_REQUEST['order'][0]['column']==19) $orderby = 'star.two_star_number';
			if($_REQUEST['order'][0]['column']==20) $orderby = 'star.three_star_number';
			if($_REQUEST['order'][0]['column']==21) $orderby = 'star.four_star_number';
			if($_REQUEST['order'][0]['column']==22) $orderby = 'star.five_star_number';
			
			if($_REQUEST['order'][0]['column']==23) $orderby = 'pre_star.create_at';
			if($_REQUEST['order'][0]['column']==24) $orderby = 'pre_star.total_star_number';
			if($_REQUEST['order'][0]['column']==25) $orderby = 'pre_star.average_score';
			if($_REQUEST['order'][0]['column']==26) $orderby = 'pre_star.one_star_number';
			if($_REQUEST['order'][0]['column']==27) $orderby = 'pre_star.two_star_number';
			if($_REQUEST['order'][0]['column']==28) $orderby = 'pre_star.three_star_number';
			if($_REQUEST['order'][0]['column']==29) $orderby = 'pre_star.four_star_number';
			if($_REQUEST['order'][0]['column']==30) $orderby = 'pre_star.five_star_number';
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
				$ordersList[$i]['brand_line'],
				'<a href="https://'.$ordersList[$i]['domain'].'/dp/'.$ordersList[$i]['asin'].'" target="_blank">'.$ordersList[$i]['asin'].'</a>',
				$ordersList[$i]['sellersku'],
				$ordersList[$i]['item_no'],
				
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
				$ordersList[$i]['total_star_number'],
				$ordersList[$i]['average_score'],
				$ordersList[$i]['one_star_number'],
				$ordersList[$i]['two_star_number'],
				$ordersList[$i]['three_star_number'],
				$ordersList[$i]['four_star_number'],
				$ordersList[$i]['five_star_number'],
				$ordersList[$i]['pre_create_at'],
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
	
	public function edit(Request $request,$id)
    {
        //if(!Auth::user()->admin) die();
        $review = Review::where('id',$id)->first()->toArray();
		if(!$review){
            $request->session()->flash('error_message','Review not Exists');
            return redirect('review');
        }
        return view('review/edit',['users'=>$this->getUsers(),'review'=>$review]);
    }

	
	public function create()
    {
        //if(!Auth::user()->admin) die();
        return view('review/add',['users'=>$this->getUsers()]);
    }
	
	
	public function store(Request $request)
    {
        //if(!Auth::user()->admin) die();
		
        $this->validate($request, [
			'review' => 'required|string',
            'asin' => 'required|string',
            'site' => 'required|string',
			'sellersku' => 'required|string',
			'date' => 'required|string',
			'rating' => 'required|int',
            'amazon_account' => 'required|string',
            'review_content' => 'required|string',
            'status' => 'required|int',
        ]);
        if($this->checkAccount($request)){
            $request->session()->flash('error_message','Set Review Failed, this Review has Already exists.');
            return redirect()->back()->withInput();
            die();
        }
		
        $seller_account = new Review;
		$seller_account->review = $request->get('review');
        $seller_account->asin = $request->get('asin');
        $seller_account->site = $request->get('site');
		$seller_account->review_content = $request->get('review_content');
        $seller_account->sellersku = $request->get('sellersku');
        $seller_account->date = $request->get('date');
        $seller_account->rating = $request->get('rating');
        $seller_account->amazon_account = $request->get('amazon_account');
		$seller_account->status = $request->get('status');
		if($request->get('status')>1) $seller_account->edate = date('Y-m-d');
		$seller_account->amazon_order_id = $request->get('amazon_order_id');
		$seller_account->buyer_email = $request->get('buyer_email');
		$seller_account->content = $request->get('content');
		$seller_account->etype = $request->get('etype');
		$seller_account->epoint = $request->get('epoint');
		$seller_account->edescription = $request->get('edescription');
		
        if($request->get('id')>0){
            $seller_account->id = $request->get('id');
        }
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Set Review Success');
            return redirect('review');
        } else {
            $request->session()->flash('error_message','Set Review Failed');
            return redirect()->back()->withInput();
        }
    }



    public function update(Request $request,$id)
    {
        //if(!Auth::user()->admin) die();

        $this->validate($request, [
			'review' => 'required|string',
            'asin' => 'required|string',
            'site' => 'required|string',
			'sellersku' => 'required|string',
			'date' => 'required|string',
			'rating' => 'required|int',
            'amazon_account' => 'required|string',
            'review_content' => 'required|string',
            'status' => 'required|int',
        ]);
        if($this->checkAccount($request)){
            $request->session()->flash('error_message','Set Review Failed, this Review has Already exists.');
            return redirect()->back()->withInput();
            die();
        }
		
        $seller_account = Review::findOrFail($id);;
		$seller_account->review = $request->get('review');
        $seller_account->asin = $request->get('asin');
        $seller_account->site = $request->get('site');
		$seller_account->review_content = $request->get('review_content');
        $seller_account->sellersku = $request->get('sellersku');
        $seller_account->date = $request->get('date');
        $seller_account->rating = $request->get('rating');
        $seller_account->amazon_account = $request->get('amazon_account');
		$seller_account->status = $request->get('status');
		if($request->get('status')>1) $seller_account->edate = date('Y-m-d');
		$seller_account->amazon_order_id = $request->get('amazon_order_id');
		$seller_account->buyer_email = $request->get('buyer_email');
		$seller_account->content = $request->get('content');
		$seller_account->etype = $request->get('etype');
		$seller_account->epoint = $request->get('epoint');
		$seller_account->edescription = $request->get('edescription');
		
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Set Review Success');
            return redirect('review/'.$id.'/edit');
        } else {
            $request->session()->flash('error_message','Set Review Failed');
            return redirect()->back()->withInput();
        }
    }
	
    public function checkAccount($request){
        $id = ($request->get('id'))?($request->get('id')):0;

        $seller_account = Review::where('review',$request->get('review'))->where('site',$request->get('site'))->where('id','<>',$id)
            ->first();
        if($seller_account) return true;
        return false;
    }

}