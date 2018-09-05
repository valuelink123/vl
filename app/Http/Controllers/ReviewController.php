<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Asin;
use App\User;
use App\Review;
use App\Accounts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class ReviewController extends Controller
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
	 
	public function upload( Request $request )
	{	
		if($request->isMethod('POST')){  
            $file = $request->file('importFile');  
  			if($file){
            if($file->isValid()){  
  
                $originalName = $file->getClientOriginalName();  
                $ext = $file->getClientOriginalExtension();  
                $type = $file->getClientMimeType();  
                $realPath = $file->getRealPath();  
                $newname = date('Y-m-d-H-i-S').'-'.uniqid().'.'.$ext;  
				$newpath = '/uploads/reviewUpload/'.date('Ymd').'/';
				$inputFileName = public_path().$newpath.$newname;
  				$bool = $file->move(public_path().$newpath,$newname);

				if($bool){
					//echo $inputFileName;
					//echo substr(strrchr($inputFileName, '.'), 1);
					//$inputFileType = $this->getExtension($inputFileName);

					//$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					//$reader->setInputEncoding('utf-8');
					//$spreadsheet = $reader->load($inputFileName);
					$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
					//$spreadsheet  
					$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
					
					$successCount = $addCount = $errorCount = 0;
					$status_array = array_flip(getReviewStatus());
					foreach($importData as $key => $data){
						
						if($key>1 && array_get($data,'F') && array_get($data,'E')){
							if(array_get($data,'A')){
								if(date('Ymd',strtotime(array_get($data,'A')))<19990101){
									$errorCount++;
									continue;
								}
							}
							if(array_get($data,'L')){
								if(!array_get($status_array,array_get($data,'L'))){
									$errorCount++;
									continue;
								}
							}
							$exists = Review::where('review',$data['F'])->where('site',$data['E'])->first();
							if($exists){
								//$seller_account = Review::where('review',$data['F'])->where('site',$data['E'])->first();
								$exists->review = $data['F'];
								$exists->asin = array_get($data,'C');
								$exists->site = array_get($data,'E');
								if(array_get($data,'I')) $exists->review_content = array_get($data,'I');
								$exists->sellersku = array_get($data,'D');
								if(array_get($data,'A')) $exists->date = date('Y-m-d',strtotime(array_get($data,'A')));
								if(array_get($data,'H')) $exists->rating = intval(array_get($data,'H'));
								if(array_get($data,'B')) $exists->amazon_account = array_get($data,'B');
								if(array_get($data,'G')) $exists->reviewer_name = array_get($data,'G');
								if(array_get($data,'K')) $exists->amazon_order_id = array_get($data,'K');
								if(array_get($data,'J')) $exists->buyer_email = array_get($data,'J');
								
								if(array_get($data,'L')){
									$exists->status = array_get($status_array,array_get($data,'L'))?array_get($status_array,array_get($data,'L')):1;
									if($exists->status>1) $exists->edate = date('Y-m-d');
					
								}
								if(array_get($data,'M'))  $exists->content = array_get($data,'M');
								if(array_get($data,'N'))  $exists->etype = array_get($data,'N');
								if(array_get($data,'O'))  $exists->epoint = array_get($data,'O');
								if(array_get($data,'P'))  $exists->edescription = array_get($data,'P');
								if(array_get($data,'Q'))  $exists->seller_id = array_get($data,'Q');
								if ($exists->save()) {
									$successCount++;
								} else {
									$errorCount++;
								}
							}else{
								$seller_account = new Review;
								$seller_account->review = $data['F'];
								$seller_account->asin = array_get($data,'C');
								$seller_account->site = array_get($data,'E');
								if(array_get($data,'I')) $seller_account->review_content = array_get($data,'I');
								$seller_account->sellersku = array_get($data,'D');
								if(array_get($data,'A')) $seller_account->date = date('Y-m-d',strtotime(array_get($data,'A')));
								if(array_get($data,'H')) $seller_account->rating = intval(array_get($data,'H'));
								if(array_get($data,'B')) $seller_account->amazon_account = array_get($data,'B');
								if(array_get($data,'G')) $seller_account->reviewer_name = array_get($data,'G');
								$seller_account->status = 1;
								if(array_get($data,'K')) $seller_account->amazon_order_id = array_get($data,'K');
								if(array_get($data,'J')) $seller_account->buyer_email = array_get($data,'J');
								if(array_get($data,'L')){
									$seller_account->status = array_get($status_array,array_get($data,'L'))?array_get($status_array,array_get($data,'L')):1;
									if($seller_account->status>1) $seller_account->edate = date('Y-m-d');
					
								}
								if(array_get($data,'M'))  $seller_account->content = array_get($data,'M');
								if(array_get($data,'N'))  $seller_account->etype = array_get($data,'N');
								if(array_get($data,'O'))  $seller_account->epoint = array_get($data,'O');
								if(array_get($data,'P'))  $seller_account->edescription = array_get($data,'P');
								if(array_get($data,'Q'))  $seller_account->seller_id = array_get($data,'Q');
								if ($seller_account->save()) {
									$addCount++;
								} else {
									$errorCount++;
								}
							}
						}
					}
					$request->session()->flash('success_message','Import Review Data Success! '.$successCount.' covered  '.$addCount.' added  '.$errorCount.'  Errors');
				}else{
					$request->session()->flash('error_message','Upload Review Failed');
				}          
            } 
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
			} 
        } 
		return redirect('review');
	
	}
	
	
	public function export(Request $request){
		$date_from=date('Y-m-d',strtotime('-30 days'));		
		$date_to=date('Y-m-d');	
		
		$customers = DB::table('review')
			->select('review.*','asin.brand','asin.brand_line','asin.seller','asin.review_user_id','asin.item_no','asin.status as asin_status')
			->leftJoin('asin',function($q){
				$q->on('review.asin', '=', 'asin.asin')
					->on('review.site', '=', 'asin.site')
					->on('review.sellersku', '=', 'asin.sellersku');
			});
		
		if(!Auth::user()->admin){
            $customers = $customers->where('asin.review_user_id',$this->getUserId());
        }
		

        
		
		if(array_get($_REQUEST,'asin_status')){
            $customers = $customers->whereIn('asin.status',explode(',',array_get($_REQUEST,'asin_status')));
        }
		if(Auth::user()->admin){
			if(array_get($_REQUEST,'user_id')){
				$customers = $customers->whereIn('asin.review_user_id',explode(',',array_get($_REQUEST,'user_id')));
			}
		}
		
		
		if(array_get($_REQUEST,'date_from')) $date_from= array_get($_REQUEST,'date_from');
		if(array_get($_REQUEST,'date_to')) $date_to= array_get($_REQUEST,'date_to');
		$customers = $customers->where('date','>=',$date_from);
		$customers = $customers->where('date','<=',$date_to);

		if(array_get($_REQUEST,'follow_status')){
            $customers = $customers->whereIn('review.status',explode(',',array_get($_REQUEST,'follow_status')));
        }
		
		if(array_get($_REQUEST,'rating')){
            $customers = $customers->where('review.rating',$_REQUEST['rating']);
        }

		if(array_get($_REQUEST,'keywords')){
            //$customers = $customers->where('subject', 'like', '%'.$_REQUEST['subject'].'%');
            $keywords = array_get($_REQUEST,'keywords');
            $customers = $customers->where(function ($query) use ($keywords) {

                $query->where('brand_line'  , 'like', '%'.$keywords.'%')
                        ->orwhere('item_no', 'like', '%'.$keywords.'%')
                        ->orwhere('seller', 'like', '%'.$keywords.'%')
                        ->orwhere('amazon_account', 'like', '%'.$keywords.'%')
						->orwhere('reviewer_name', 'like', '%'.$keywords.'%')
						 ->orwhere('review.asin', 'like', '%'.$keywords.'%')
						  ->orwhere('review.sellersku', 'like', '%'.$keywords.'%')
						  ->orwhere('review', 'like', '%'.$keywords.'%')
						  ->orwhere('amazon_order_id', 'like', '%'.$keywords.'%')
						   ->orwhere('buyer_email', 'like', '%'.$keywords.'%')
						  ->orwhere('etype', 'like', '%'.$keywords.'%');

            });
        }

		
		
		$orderby = 'date';
        $sort = 'desc';
		
		
		
		
        $reviews =  $customers->orderBy($orderby,$sort)->get();
		
		$reviewsLists =json_decode(json_encode($reviews), true);
		$arrayData = array();

		$headArray[] = 'Review Date';
		$headArray[] = 'Account';
		$headArray[] = 'Asin';
		$headArray[] = 'SellerSku';
		$headArray[] = 'Site';
		$headArray[] = 'ReviewID';
		$headArray[] = 'Reviewer Name';
		$headArray[] = 'Rating';
		$headArray[] = 'Review Content';
		$headArray[] = 'Buyer Email';
		$headArray[] = 'Amazon OrderId';
		$headArray[] = 'Review Status';
		$headArray[] = 'Follow up progress';
		$headArray[] = 'Question Type';
		$headArray[] = 'Problem Point';
		$headArray[] = 'Remark';
		$headArray[] = 'Follow up Date';
		$headArray[] = 'Asin Status';
		$headArray[] = 'Brand Line';
		$headArray[] = 'Item NO.';
		$headArray[] = 'Seller';
		$headArray[] = 'User';
		$headArray[] = 'SellerID';

		$arrayData[] = $headArray;
		$users_array = $this->getUsers();
		$asin_status_array =  getAsinStatus();
		$follow_status_array = getReviewStatus();
		foreach ( $reviewsLists as $review){
            $arrayData[] = array(
               	$review['date'],
				$review['amazon_account'],
				$review['asin'],
				$review['sellersku'],
				$review['site'],
				$review['review'],
				$review['reviewer_name'],
				$review['rating'],
				strip_tags($review['review_content']),
				$review['buyer_email'],
				$review['amazon_order_id'],
				array_get($follow_status_array,empty(array_get($review,'status'))?0:array_get($review,'status'),''),
				strip_tags($review['content']),
				$review['etype'],
				$review['epoint'],
				strip_tags($review['edescription']),
				$review['edate'],
				array_get($asin_status_array,empty(array_get($review,'asin_status'))?0:array_get($review,'asin_status')),
				$review['brand_line'],
				$review['item_no'],
				$review['seller'],
				
				array_get($users_array,intval(array_get($review,'review_user_id')),''),
				$review['seller_id']
				
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
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//¸æËßä¯ÀÀÆ÷Êä³ö07ExcelÎÄ¼þ
			header('Content-Disposition: attachment;filename="Export_review.xlsx"');//¸æËßä¯ÀÀÆ÷Êä³öä¯ÀÀÆ÷Ãû³Æ
			header('Cache-Control: max-age=0');//½ûÖ¹»º´æ
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
	
	
    public function index()
    {   
	

		$date_from=date('Y-m-d',strtotime('-90 days'));		
		$date_to=date('Y-m-d');	
		
		
		$asin_status_array = getAsinStatus();
		$follow_status_array = getReviewStatus();
        return view('review/index',['date_from'=>$date_from ,'date_to'=>$date_to, 'asin_status'=>$asin_status_array,'follow_status'=>$follow_status_array, 'users'=>$this->getUsers()]);

    }

    public function get()
    {
		$date_from=date('Y-m-d',strtotime('-30 days'));		
		$date_to=date('Y-m-d');	
		
		$customers = DB::table('review')
			->select('review.*','asin.brand','asin.brand_line','asin.seller','asin.review_user_id','asin.item_no','asin.status as asin_status')
			->leftJoin('asin',function($q){
				$q->on('review.asin', '=', 'asin.asin')
					->on('review.site', '=', 'asin.site')
					->on('review.sellersku', '=', 'asin.sellersku');
			});
		
		if(!Auth::user()->admin){
            $customers = $customers->where('asin.review_user_id',$this->getUserId());
        }
		

        
		
		if(array_get($_REQUEST,'asin_status')){
            $customers = $customers->whereIn('asin.status',array_get($_REQUEST,'asin_status'));
        }
		if(Auth::user()->admin){
			if(array_get($_REQUEST,'user_id')){
				$customers = $customers->whereIn('asin.review_user_id',array_get($_REQUEST,'user_id'));
			}
		}
		
		
		if(array_get($_REQUEST,'date_from')) $date_from= array_get($_REQUEST,'date_from');
		if(array_get($_REQUEST,'date_to')) $date_to= array_get($_REQUEST,'date_to');
		$customers = $customers->where('date','>=',$date_from);
		$customers = $customers->where('date','<=',$date_to);
		

		if(array_get($_REQUEST,'follow_status')){
            $customers = $customers->whereIn('review.status',array_get($_REQUEST,'follow_status'));
        }
		
		if(array_get($_REQUEST,'rating')){
            $customers = $customers->where('review.rating',$_REQUEST['rating']);
        }

		if(array_get($_REQUEST,'keywords')){
            //$customers = $customers->where('subject', 'like', '%'.$_REQUEST['subject'].'%');
            $keywords = array_get($_REQUEST,'keywords');
            $customers = $customers->where(function ($query) use ($keywords) {

                $query->where('brand_line'  , 'like', '%'.$keywords.'%')
                        ->orwhere('item_no', 'like', '%'.$keywords.'%')
                        ->orwhere('seller', 'like', '%'.$keywords.'%')
                        ->orwhere('amazon_account', 'like', '%'.$keywords.'%')
						->orwhere('reviewer_name', 'like', '%'.$keywords.'%')
						 ->orwhere('review.asin', 'like', '%'.$keywords.'%')
						  ->orwhere('review.sellersku', 'like', '%'.$keywords.'%')
						  ->orwhere('review', 'like', '%'.$keywords.'%')
						  ->orwhere('amazon_order_id', 'like', '%'.$keywords.'%')
						   ->orwhere('buyer_email', 'like', '%'.$keywords.'%')
						  ->orwhere('etype', 'like', '%'.$keywords.'%');

            });
        }

		
		
		$orderby = 'date';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
			 if($_REQUEST['order'][0]['column']==0) $orderby = 'negative_value';
            if($_REQUEST['order'][0]['column']==1) $orderby = 'item_no';
			if($_REQUEST['order'][0]['column']==2) $orderby = 'asin';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'date';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'rating';
            if($_REQUEST['order'][0]['column']==5) $orderby = 'status';
            if($_REQUEST['order'][0]['column']==6) $orderby = 'amazon_account';
            if($_REQUEST['order'][0]['column']==7) $orderby = 'amazon_order_id';
			if($_REQUEST['order'][0]['column']==8) $orderby = 'buyer_email';
			if($_REQUEST['order'][0]['column']==10) $orderby = 'reviewer_name';
			if($_REQUEST['order'][0]['column']==11) $orderby = 'edate';
			if($_REQUEST['order'][0]['column']==12) $orderby = 'review_user_id';
            $sort = $_REQUEST['order'][0]['dir'];
			
			
        }
		
		
		
		
        $reviews =  $customers->orderBy($orderby,$sort)->get()->toArray();
		$ordersList =json_decode(json_encode($reviews), true);
		
	
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
		$asin_status_array =  getAsinStatus();
		$follow_status_array = getReviewStatus();
        for($i = $iDisplayStart; $i < $end; $i++) {

			$records["data"][] = array(
				$ordersList[$i]['negative_value'],
				$ordersList[$i]['asin'],
				$ordersList[$i]['item_no'],
				$ordersList[$i]['date'],
				$ordersList[$i]['rating'],
				array_get($follow_status_array,$ordersList[$i]['status'],''),
				$ordersList[$i]['amazon_account'],
				$ordersList[$i]['amazon_order_id'],
				$ordersList[$i]['buyer_email'],
				strip_tags($ordersList[$i]['content']),

				$ordersList[$i]['reviewer_name'],
				$ordersList[$i]['edate'],
				array_get($users_array,intval(array_get($ordersList[$i],'review_user_id')),''),				
				(($ordersList[$i]['warn']>0)?'<i class="fa fa-warning" title="Contains dangerous words"></i>&nbsp;&nbsp;&nbsp;':'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;').'<a href="https://'.$ordersList[$i]['site'].'/gp/customer-reviews/'.$ordersList[$i]['review'].'" target="_blank" class="btn btn-success btn-xs"> View </a>'.'<a href="/review/'.$ordersList[$i]['id'].'/edit" target="_blank" class="btn btn-danger btn-xs"><i class="fa fa-search"></i> Edit </a>'
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
		$order = array();
		if(array_get($review,'amazon_order_id') && array_get($review,'seller_id')){
            $order = DB::table('amazon_orders')->where('SellerId', array_get($review,'seller_id'))->where('AmazonOrderId', array_get($review,'amazon_order_id'))->first();
            if($order) $order->item = DB::table('amazon_orders_item')->where('SellerId', array_get($review,'seller_id'))->where('AmazonOrderId', array_get($review,'amazon_order_id'))->get();
        }
		$return['users'] = $this->getUsers();
		$return['review'] = $review;
		$return['sellerids'] = $this->getSellerIds();
		$return['accounts'] = $this->getAccounts();
		if($order) $return['order'] = $order;
        return view('review/edit',$return);

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
			'reviewer_name' => 'required|string',
            'review_content' => 'required|string',
            'status' => 'required|int',
        ]);
		if($request->get('status')==6 && !($request->get('creson'))){
            $request->session()->flash('error_message','Set Review Failed, Set Review Closed Must Fill Closed Reson !.');
            return redirect()->back()->withInput();
            die();
        }
		
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
		$seller_account->seller_id = $request->get('seller_id');
        $seller_account->date = $request->get('date');
        $seller_account->rating = $request->get('rating');
        $seller_account->amazon_account = $request->get('amazon_account');
		$seller_account->reviewer_name = $request->get('reviewer_name');
		$seller_account->status = $request->get('status');
		if($request->get('status')==6) $seller_account->creson = $request->get('creson');
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
			'reviewer_name' => 'required|string',
            'review_content' => 'required|string',
            'status' => 'required|int',
        ]);
		
		if($request->get('status')==6 && !($request->get('creson'))){
            $request->session()->flash('error_message','Set Review Failed, Set Review Closed Must Fill Closed Reson !.');
            return redirect()->back()->withInput();
            die();
        }
		
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
		$seller_account->seller_id = $request->get('seller_id');
        $seller_account->date = $request->get('date');
        $seller_account->rating = $request->get('rating');
        $seller_account->amazon_account = $request->get('amazon_account');
		$seller_account->reviewer_name = $request->get('reviewer_name');
		$seller_account->status = $request->get('status');
		if($request->get('status')==6) $seller_account->creson = $request->get('creson');
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
	
	function getExtension($inputFileName)
    {
        $ext = substr(strrchr($inputFileName, '.'), 1);
		echo $ext;
        if (!$ext) {
            return null;
        }

        switch (strtolower($ext)) {
            case 'xlsx': // Excel (OfficeOpenXML) Spreadsheet
            case 'xlsm': // Excel (OfficeOpenXML) Macro Spreadsheet (macros will be discarded)
            case 'xltx': // Excel (OfficeOpenXML) Template
            case 'xltm': // Excel (OfficeOpenXML) Macro Template (macros will be discarded)
                return 'Xlsx';
            case 'xls': // Excel (BIFF) Spreadsheet
            case 'xlt': // Excel (BIFF) Template
                return 'Xls';
            case 'ods': // Open/Libre Offic Calc
            case 'ots': // Open/Libre Offic Calc Template
                return 'Ods';
            case 'slk':
                return 'Slk';
            case 'xml': // Excel 2003 SpreadSheetML
                return 'Xml';
            case 'gnumeric':
                return 'Gnumeric';
            case 'htm':
            case 'html':
                return 'Html';
            case 'csv':
                // Do nothing
                // We must not try to use CSV reader since it loads
                // all files including Excel files etc.
                return 'Csv';
            default:
                return null;
        }
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

}