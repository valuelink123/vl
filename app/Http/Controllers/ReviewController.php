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
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upload( Request $request )
    {
        if(!Auth::user()->can(['review-import'])) die('Permission denied -- review-import');
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
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
                        $importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                        $successCount = $addCount = $errorCount = 0;
                        foreach($importData as $key => $data){
                            if($key==1){
                                if(array_get($data,'A')!='site' || array_get($data,'B')!='customer id' || array_get($data,'C')!='review id'){
                                    die('Customer profile import template error');
                                }
                            }
                            if($key>1 && array_get($data,'A') && array_get($data,'B') && array_get($data,'C')){

                                if(array_get($data,'B')!='not_match'){
                                    $exists = DB::table('customers')->where('customer_id',trim($data['B']))->where('site',trim($data['A']))->first();
                                    if($exists){
                                        $update_result = DB::table('customers')->where('site',trim($data['A']))->where('customer_id',trim($data['B']))->update(array(
                                            'email'=>trim($data['D']),
                                            'phone'=>trim($data['E']),
                                            'other'=>trim($data['F']),
                                            'last_update_date'=>date('Y-m-d')
                                        ));
                                        if ($update_result) {
                                            $successCount++;
                                        }
                                    }else{
                                        $insert_result = DB::table('customers')->insert(
                                            array(
                                                'site'=>trim($data['A']),
                                                'customer_id'=>trim($data['B']),
                                                'email'=>trim($data['D']),
                                                'phone'=>trim($data['E']),
                                                'other'=>trim($data['F']),
                                                'last_update_date'=>date('Y-m-d')
                                            )
                                        );
                                        if ($insert_result) {
                                            $addCount++;
                                        } else {
                                            $errorCount++;
                                        }
                                    }
                                    DB::table('review')->where('site',trim($data['A']))->where('customer_id',trim($data['B']))->where('status',102)->update(['status'=>103]);

                                }else{
                                    $exists = DB::table('review_customers')->where('review',trim($data['C']))->where('site',trim($data['A']))->first();
                                    if($exists){
                                        $update_result = DB::table('review_customers')->where('site',trim($data['A']))->where('review',trim($data['C']))->update(array(
                                            'email'=>trim($data['D']),
                                            'phone'=>trim($data['E']),
                                            'other'=>trim($data['F']),
                                            'last_update_date'=>date('Y-m-d')
                                        ));
                                        if ($update_result) {
                                            $successCount++;
                                        }
                                    }else{
                                        $insert_result = DB::table('review_customers')->insert(
                                            array(
                                                'site'=>trim($data['A']),
                                                'review'=>trim($data['C']),
                                                'email'=>trim($data['D']),
                                                'phone'=>trim($data['E']),
                                                'other'=>trim($data['F']),
                                                'last_update_date'=>date('Y-m-d')
                                            )
                                        );
                                        if ($insert_result) {
                                            $addCount++;
                                        } else {
                                            $errorCount++;
                                        }
                                    }
                                    DB::table('review')->where('site',trim($data['A']))->where('review',trim($data['C']))->where('status',102)->update(['status'=>103]);

                                }

                            }
                        }
                        $request->session()->flash('success_message','Import Customer Data Success! '.$successCount.' covered  '.$addCount.' added  '.$errorCount.'  Errors');
                    }else{
                        $request->session()->flash('error_message','Upload Customer Failed');
                    }
                }
            }else{
                $request->session()->flash('error_message','Please Select Upload File');
            }
        }
        return redirect('review');

    }


    public function export(Request $request){

        if(!Auth::user()->can(['review-export'])) die('Permission denied -- review-export');
        set_time_limit(0);
        $date_from=date('Y-m-d',strtotime('-90 days'));
        $date_to=date('Y-m-d');

		//定义订单物料对照关系
		$_asinsMapping=array();
		//定义销售的asin
		$_asins=array();

		//获得当前登陆用户
		$_curr_user=Auth::User();

		//从Amazon库获得物料对照关系
		$_asinsMappingSelect=DB::connection('amazon')->table('sap_asin_match_sku')
			->select('sap_asin_match_sku.asin','sap_asin_match_sku.seller_sku','sap_asin_match_sku.sku','asins.title','asins.images')
			->leftJoin('asins',function($q){
				$q->on('sap_asin_match_sku.asin', '=', 'asins.asin')
					->on('sap_asin_match_sku.marketplace_id', '=', 'asins.marketplaceid');
			});

		$_asinsMappingSelect=$_asinsMappingSelect->whereRaw('length(sap_asin_match_sku.asin)=10') ;


		//是否属于营销员（销售人员）
		$_isSales=false;
		$_toSelect=false;
		if($_curr_user->seller_rules) {
			$rules = explode("-", trim($_curr_user->seller_rules));
			if (array_get($rules, 0) != '*') {
				$_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_bg',array_get($rules, 0)) ;
				$_isSales=true;
			}
			if (array_get($rules, 1) != '*') {
				$_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_bu',array_get($rules, 1)) ;
				$_isSales=true;
			}
		}else {
			if($_curr_user->sap_seller_id>0) {
				$_isSales=true;
				$_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_id', $_curr_user->sap_seller_id);
			}
		}

		if(array_get($_REQUEST,'bgbu')) {
			$bgbu = explode("_", trim(array_get($_REQUEST,'bgbu')));
			if(array_get($bgbu, 0)){
				$_toSelect=true;
				$_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_bg',array_get($bgbu, 0)) ;
			}
			if(array_get($bgbu, 1)){
				$_toSelect=true;
				$_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_bu',array_get($bgbu, 1)) ;
			}


		}


		//TODO: 这里需要把获得的user_id转化为SAP的sap_seller_id,才能获得正确的值
		if(array_get($_REQUEST,'user_id')){
			$_sapUserIds=DB::table('users')->select('sap_seller_id')->whereIn('id',explode(',',array_get($_REQUEST,'user_id')))->where('locked',0)->get()->toArray();
			$_toSelect=true;
			if($_sapUserIds) {
				$sapUserIds = array();
				foreach($_sapUserIds as $key=>$val){
					$sapUserIds[] = $val->sap_seller_id;
				}
				$_asinsMappingSelect = $_asinsMappingSelect->whereIn('sap_asin_match_sku.sap_seller_id',$sapUserIds);
			}
		}

		if(array_get($_REQUEST,'asin_status')){
			$_toSelect=true;
			$_asinsMappingSelect = $_asinsMappingSelect->whereIn('sap_asin_match_sku.status',explode(',',array_get($_REQUEST,'asin_status')));
		}

		//关键字查询，只模糊查询asin\seller_sku\sku(item_no)
		if(array_get($_REQUEST,'keywords')){
			$_toSelect=true;
			$keywords=trim(array_get($_REQUEST,'keywords'));
			$_asinsMappingSelect = $_asinsMappingSelect->where(function ($query) use ($keywords) {
				$query->where('sap_asin_match_sku.asin', 'like', '%'.$keywords.'%')
					->orwhere('sap_asin_match_sku.seller_sku', 'like', '%'.$keywords.'%')
					->orwhere('sap_asin_match_sku.sku', 'like', '%'.$keywords.'%');
			});
		}


		//判断是销售人员，则查询自己组织以及下级的物料对照关系
		$asinMatchSkuData = array();
		$_asinsMapping = $_asinsMappingSelect->get();
		if($_asinsMapping){
			foreach ($_asinsMapping as $_asin){
				if($_asin){
					$_asins[$_asin->asin]=$_asin->asin;
					$asinMatchSkuData[$_asin->asin]['item_no'] = $_asin->sku;
					$asinMatchSkuData[$_asin->asin]['title'] = $_asin->title;
					$_images = explode(',',$_asin->images);
					$asinMatchSkuData[$_asin->asin]['img'] = current($_images);
				}
			}
		}


		//查询评价表
		$_reviewsSelect=DB::table('review');
		if($_asins) {
			$_reviewsSelect = $_reviewsSelect->whereIn('asin', $_asins);
		}


		if(array_get($_REQUEST,'site')) {
			$_reviewsSelect = $_reviewsSelect->whereIn('site',explode(',',array_get($_REQUEST,'site')));
		}


		if(array_get($_REQUEST,'follow_status')) {
			$_reviewsSelect = $_reviewsSelect->whereIn('follow_status',explode(',',array_get($_REQUEST,'follow_status')));
		}

		if(array_get($_REQUEST,'date_from')) {
			$_reviewsSelect = $_reviewsSelect->where('date', '>=', array_get($_REQUEST,'date_from'));
		}

		if(array_get($_REQUEST,'date_to')){
			$_reviewsSelect = $_reviewsSelect->where('date', '<=', array_get($_REQUEST,'date_to'));
		}

		if(array_get($_REQUEST,'rating')){
			$_reviewsSelect = $_reviewsSelect->where('rating', array_get($_REQUEST,'rating'));
		}

		if(array_get($_REQUEST,'vp')){
			$_reviewsSelect = $_reviewsSelect->where('vp', array_get($_REQUEST,'vp'));
		}

		if(array_get($_REQUEST,'del')){
			$_reviewsSelect = $_reviewsSelect->where('is_delete', array_get($_REQUEST,'del'));
		}


		if(array_get($_REQUEST,'rc')){
			if(array_get($_REQUEST,'rc')==1){
				$_reviewsSelect = $_reviewsSelect->whereRaw('review.rating=review.updated_rating');
			}
			if(array_get($_REQUEST,'rc')==2){
				$_reviewsSelect = $_reviewsSelect->whereRaw('review.rating<>review.updated_rating');
			}
		}


		//当np=1是为差评，星级=[1,2,3],当np=2是好评，星级=[4,5]
		if(array_get($_REQUEST,'np')){
			switch (array_get($_REQUEST,'np')) {
				case 1:
					$_reviewsSelect = $_reviewsSelect->whereIn('rating', [1,2,3]);
					break;
				case 2:
					$_reviewsSelect = $_reviewsSelect->whereIn('rating', [4,5]);
					break;
			}
		}
		$reviews=$_reviewsSelect->orderBy('date','desc')->get()->toArray();
		$reviewsLists =json_decode(json_encode($reviews), true);

        $arrayData = array();
        $headArray[] = 'Review Date';
        $headArray[] = 'Site';
        $headArray[] = 'Asin';
        $headArray[] = 'Sku';
        $headArray[] = 'Image';
        $headArray[] = 'Title';
        $headArray[] = 'Rating';
        $headArray[] = 'Review Content';
        $headArray[] = 'Review Content CN';
        $headArray[] = 'Status';
        $headArray[] = 'Question Type';
        $headArray[] = 'Comment';

        $arrayData[] = $headArray;
        $users_array = $this->getUsers();
        $asin_status_array =  getAsinStatus();
        $follow_status_array = getReviewStatus();
        $steps = DB::table('review_step')->get();
        foreach($steps as $step){
            $follow_status_array[$step->id]=$step->title;
        }

        foreach ( $reviewsLists as $review){
            $fol_arr= unserialize($review['follow_content'])?unserialize($review['follow_content']):array();
			$rating_chstr =$date_chstr = '';
			if($review['updated_rating']>0 && $review['updated_rating']!=$review['rating']){
				if($review['updated_rating']>$review['rating']){
					$rating_chstr = $review['updated_rating'];
					if($review['updated_date']) $date_chstr = $review['updated_date'];
				}else{
					$rating_chstr = $review['updated_rating'];
					if($review['updated_date']) $date_chstr = $review['updated_date'];
				}
			}
			//求item_no
			$item = '';
			if(isset($asinMatchSkuData[$review['asin']]['item_no'])){
				$item = $asinMatchSkuData[$review['asin']]['item_no'];
			}
			//求image
			$img = '';
			if(isset($asinMatchSkuData[$review['asin']]['img'])){
				$img = 'https://images-na.ssl-images-amazon.com/images/I/'.$asinMatchSkuData[$review['asin']]['img'];
			}
			$title = '';
			if(isset($asinMatchSkuData[$review['asin']]['title'])){
				$title = $asinMatchSkuData[$review['asin']]['title'];
			}
			//有些记录的site字段为空
			$site = '';
			$siteUrl = 'www.amazon.com';
			if($review['site']){
				$site = array_get(array_flip($this->getSitePairs()), $review['site'],'US');
				$siteUrl = $review['site'];
			}

            $arrayData[] = array(
                $review['date'].' '.$date_chstr,
				$site,
                $review['asin'],
				$item,
				$img,
				$title,
				$review['rating'].' '.$rating_chstr,
				strip_tags($review['review_content']),
				strip_tags($review['review_content_cn']),
				strip_tags(array_get($fol_arr,$review['status'].'.do_content')),
				$review['etype'],
				$review['remark'],
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
            header('Content-Disposition: attachment;filename="Export_review.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }


    public function index()
    {

        if(!Auth::user()->can(['review-show'])) die('Permission denied -- review-show');
        $date_from=date('Y-m-d',strtotime('-90 days'));
        $date_to=date('Y-m-d');
        $_REQUEST['keywords'] = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';

        $asin_status_array = getAsinStatus();
        $follow_status_array = getReviewStatus();
        $steps = DB::table('review_step')->get();
        foreach($steps as $step){
            $follow_status_array[$step->id]=$step->title;
        }
        $teams= DB::select('select bg,bu from asin group by bg,bu ORDER BY BG ASC,BU ASC');
        return view('review/index',['date_from'=>$date_from ,'date_to'=>$date_to, 'asin_status'=>$asin_status_array,'follow_status'=>$follow_status_array, 'users'=>$this->getUsers(),'teams'=>$teams]);

    }

    public function get()
    {
        if (isset($_REQUEST["customActionType"])) {
            if(!Auth::user()->can(['review-batch-update'])) die('Permission denied -- review-batch-update');
            $updateDate=array();
            //修改指派人
            if($_REQUEST["customActionType"] == "group_action"){
                if(array_get($_REQUEST,"giveReviewUser")){
                    $updateDate['user_id'] = array_get($_REQUEST,"giveReviewUser");
                }
                $updatebox = new Review;

                if($updateDate) $updatebox->whereIN('id',$_REQUEST["id"])->update($updateDate);
                unset($updateDate);
                foreach($_REQUEST["id"] as $up_id){
                    DB::table('review_change_log')->insert(array(
                        'review_id'=>$up_id,
                        'to_user_id'=>array_get($_REQUEST,"giveReviewUser"),
                        'user_id'=>Auth::user()->id,
                        'date'=>date('Y-m-d H:i:s')
                    ));
                }
            }
            //批量修改状态
            if($_REQUEST["customActionType"] == "status_action"){
                if(array_get($_REQUEST,"giveReviewStatus")){
                    $updateDate['status'] = array_get($_REQUEST,"giveReviewStatus");
                }
                $updatebox = new Review;

                if($updateDate) $updatebox->whereIN('id',$_REQUEST["id"])->update($updateDate);
                unset($updateDate);
            }

        }


        //定义订单物料对照关系
        $_asinsMapping=array();
        //定义销售的asin
        $_asins=array();

        //获得当前登陆用户
        $_curr_user=Auth::User();

        //从Amazon库获得物料对照关系
        $_asinsMappingSelect=DB::connection('amazon')->table('sap_asin_match_sku')
            ->select('sap_asin_match_sku.asin','sap_asin_match_sku.seller_sku','sap_asin_match_sku.sku','asins.title','asins.images')
            ->leftJoin('asins',function($q){
                $q->on('sap_asin_match_sku.asin', '=', 'asins.asin')
                    ->on('sap_asin_match_sku.marketplace_id', '=', 'asins.marketplaceid');
            });

        $_asinsMappingSelect=$_asinsMappingSelect->whereRaw('length(sap_asin_match_sku.asin)=10') ;


        //是否属于营销员（销售人员）
        $_isSales=false;
        $_toSelect=false;
        if($_curr_user->seller_rules) {
            $rules = explode("-", trim($_curr_user->seller_rules));
            if (array_get($rules, 0) != '*') {
                $_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_bg',array_get($rules, 0)) ;
                $_isSales=true;
            }
            if (array_get($rules, 1) != '*') {
                $_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_bu',array_get($rules, 1)) ;
                $_isSales=true;
            }
        }else {
            if($_curr_user->sap_seller_id>0) {
                $_isSales=true;
                $_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_id', $_curr_user->sap_seller_id);
            }
        }

        if(array_get($_REQUEST,'bgbu')) {
            $bgbu = explode("_", trim(array_get($_REQUEST,'bgbu')));
            if(array_get($bgbu, 0)){
                $_toSelect=true;
                $_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_bg',array_get($bgbu, 0)) ;
            }
            if(array_get($bgbu, 1)){
                $_toSelect=true;
                $_asinsMappingSelect=$_asinsMappingSelect->where('sap_asin_match_sku.sap_seller_bu',array_get($bgbu, 1)) ;
            }


        }


        //TODO: 这里需要把获得的user_id转化为SAP的sap_seller_id,才能获得正确的值
        if(array_get($_REQUEST,'user_id')){
            $_sapUserIds=DB::table('users')->select('sap_seller_id')->whereIn('id',array_get($_REQUEST,'user_id'))->where('locked',0)->get()->toArray();
            $_toSelect=true;
            if($_sapUserIds) {
                $sapUserIds = array();
                foreach($_sapUserIds as $key=>$val){
                    $sapUserIds[] = $val->sap_seller_id;
                }
                $_asinsMappingSelect = $_asinsMappingSelect->whereIn('sap_asin_match_sku.sap_seller_id',$sapUserIds);
            }
        }

        if(array_get($_REQUEST,'asin_status')){
            $_toSelect=true;
            $_asinsMappingSelect = $_asinsMappingSelect->whereIn('sap_asin_match_sku.status',array_get($_REQUEST,'asin_status'));
        }

        //关键字查询，只模糊查询asin\seller_sku\sku(item_no)
        if(array_get($_REQUEST,'keywords')){
            $_toSelect=true;
            $keywords=trim(array_get($_REQUEST,'keywords'));
            $_asinsMappingSelect = $_asinsMappingSelect->where(function ($query) use ($keywords) {
                $query->where('sap_asin_match_sku.asin', 'like', '%'.$keywords.'%')
                    ->orwhere('sap_asin_match_sku.seller_sku', 'like', '%'.$keywords.'%')
                    ->orwhere('sap_asin_match_sku.sku', 'like', '%'.$keywords.'%');
            });
        }


        //判断是销售人员，则查询自己组织以及下级的物料对照关系
        $asinMatchSkuData = array();
        $_asinsMapping = $_asinsMappingSelect->get();
        if($_asinsMapping){
            foreach ($_asinsMapping as $_asin){
                if($_asin){
                    $_asins[$_asin->asin]=$_asin->asin;
                    $asinMatchSkuData[$_asin->asin]['item_no'] = $_asin->sku;
                    $asinMatchSkuData[$_asin->asin]['title'] = $_asin->title;
                    $_images = explode(',',$_asin->images);
                    $asinMatchSkuData[$_asin->asin]['img'] = current($_images);
                }
            }
        }


        //查询评价表
        $_reviewsSelect=DB::table('review');
        if($_asins) {
            $_reviewsSelect = $_reviewsSelect->whereIn('asin', $_asins);
        }


        if(array_get($_REQUEST,'site')) {
            $_reviewsSelect = $_reviewsSelect->whereIn('site',array_get($_REQUEST,'site'));
        }


        if(array_get($_REQUEST,'follow_status')) {
            $_reviewsSelect = $_reviewsSelect->whereIn('follow_status',array_get($_REQUEST,'follow_status'));
        }

        if(array_get($_REQUEST,'date_from')) {
            $_reviewsSelect = $_reviewsSelect->where('date', '>=', array_get($_REQUEST,'date_from'));
        }

        if(array_get($_REQUEST,'date_to')){
            $_reviewsSelect = $_reviewsSelect->where('date', '<=', array_get($_REQUEST,'date_to'));
        }

        if(array_get($_REQUEST,'rating')){
            $_reviewsSelect = $_reviewsSelect->where('rating', array_get($_REQUEST,'rating'));
        }

        if(array_get($_REQUEST,'vp')){
            $_reviewsSelect = $_reviewsSelect->where('vp', array_get($_REQUEST,'vp'));
        }

        if(array_get($_REQUEST,'del')){
            $_reviewsSelect = $_reviewsSelect->where('is_delete', array_get($_REQUEST,'del'));
        }


        if(array_get($_REQUEST,'rc')){
            if(array_get($_REQUEST,'rc')==1){
                $_reviewsSelect = $_reviewsSelect->whereRaw('review.rating=review.updated_rating');
            }
            if(array_get($_REQUEST,'rc')==2){
                $_reviewsSelect = $_reviewsSelect->whereRaw('review.rating<>review.updated_rating');
            }
        }


        //当np=1是为差评，星级=[1,2,3],当np=2是好评，星级=[4,5]
        if(array_get($_REQUEST,'np')){
            switch (array_get($_REQUEST,'np')) {
                case 1:
                    $_reviewsSelect = $_reviewsSelect->whereIn('rating', [1,2,3]);
                    break;
                case 2:
                    $_reviewsSelect = $_reviewsSelect->whereIn('rating', [4,5]);
                    break;
            }
        }
        $reviews=$_reviewsSelect->orderBy('date','desc')->get()->toArray();
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
        $steps = DB::table('review_step')->get();
        foreach($steps as $step){
            $follow_status_array[$step->id]=$step->title;
        }

        for($i = $iDisplayStart; $i < $end; $i++) {
            $rating_chstr =$date_chstr = '';
            if($ordersList[$i]['updated_rating']>0 && $ordersList[$i]['updated_rating']!=$ordersList[$i]['rating']){
                if($ordersList[$i]['updated_rating']>$ordersList[$i]['rating']){
                    $rating_chstr = '<span class="badge badge-danger"><i class="fa fa-angle-double-up"></i> '.$ordersList[$i]['updated_rating'].'</span>';
                    if($ordersList[$i]['updated_date']) $date_chstr = '<span class="badge badge-danger">'.$ordersList[$i]['updated_date'].'</span>';
                }else{
                    $rating_chstr = '<span class="badge badge-success"><i class="fa fa-angle-double-down"></i>'.$ordersList[$i]['updated_rating'].'</span>';
                    if($ordersList[$i]['updated_date']) $date_chstr = '<span class="badge badge-success">'.$ordersList[$i]['updated_date'].'</span>';
                }
            }
            //有些记录的site字段为空
            $site = '';
            $siteUrl = 'www.amazon.com';
            if($ordersList[$i]['site']){
                $site = array_get(array_flip($this->getSitePairs()), $ordersList[$i]['site'],'US');
                $siteUrl = $ordersList[$i]['site'];
            }
//            $siteUrl = array_get($ordersList[$i], 'site', "www.amazon.com");
            $reviewUrl = 'https://'.$siteUrl.'/gp/customer-reviews/'.$ordersList[$i]['review'];
            $viewItem = '<li><a href="' . $reviewUrl . '" target="_blank"> View </a></li>';
            $resolveItem = '<li><a href="/review/'.$ordersList[$i]['id'].'/edit" target="_blank"><i class="fa fa-search"></i> Resolve </a></li>';

            //求item_no
            $item = '';
            if(isset($asinMatchSkuData[$ordersList[$i]['asin']]['item_no'])){
                $item = $asinMatchSkuData[$ordersList[$i]['asin']]['item_no'];
            }
            //求item_name
            $item_name = '';
            if(isset($asinMatchSkuData[$ordersList[$i]['asin']]['title'])){
                $item_name = '<img style="width:100px;height:100px;" src="https://images-na.ssl-images-amazon.com/images/I/'.$asinMatchSkuData[$ordersList[$i]['asin']]['img'].'" title="'.$asinMatchSkuData[$ordersList[$i]['asin']]['title'].'">';
            }

            $fol_arr= unserialize($ordersList[$i]['follow_content'])?unserialize($ordersList[$i]['follow_content']):array();
            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$ordersList[$i]['id'].'"/><span></span></label>',
                $ordersList[$i]['date'].' '.$date_chstr,
                $site,
                '<a href="https://'.$siteUrl.'/dp/'.$ordersList[$i]['asin'].'" target="_blank">'.$ordersList[$i]['asin'].'</a><br>'.$item,
                $item_name,
                $ordersList[$i]['rating'].' '.$rating_chstr,
                '<div class="text"><a href="'.$reviewUrl.'" target="_blank"><i class="fa fa-external-link"></i></a><span title="'.strip_tags($ordersList[$i]['review_content']).'">'.strip_tags($ordersList[$i]['review_content']).'</span></div>',
                '<div class="text"><span title="'.strip_tags($ordersList[$i]['review_content_cn']).'">'.strip_tags($ordersList[$i]['review_content_cn']).'</span></div>',
                strip_tags(array_get($fol_arr,$ordersList[$i]['status'].'.do_content')),
                $ordersList[$i]['etype'],
                $ordersList[$i]['remark'],
                (($ordersList[$i]['warn']>0)?'<i class="fa fa-warning" title="Contains dangerous words"></i>&nbsp;&nbsp;&nbsp;':'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;').'<ul class="nav navbar-nav"><li><a href="#" class="dropdown-toggle" style="height:10px; vertical-align:middle; padding-top:0px;" data-toggle="dropdown" role="button">...</a><ul class="dropdown-menu" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-50px, 20px, 0px); min-width: 88px;" role="menu" style="color: #62c0cc8a">' . $viewItem . $resolveItem . '</ul></li></ul>',
                $ordersList[$i]['id']
            );
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);

    }

    public function updateContentCN(Request $request){
        $id = $request->input('id');
        $newContent = $request->input('newContent');
        $row = DB::table('review')->where('id','=', $id)->first();
        if(!$row) exit;
        $updateData = array('review_content_cn' => $newContent);
        DB::table('review')->where('id','=', $id)->update($updateData);
        return json_encode(array('review_content_cn' => $newContent));
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

    public function edit(Request $request,$id)
    {
        if(!Auth::user()->can(['review-show'])) die('Permission denied -- review-show');
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

        $customer=[];
        if(array_get($review,'customer_id') && array_get($review,'site')){
            $customer_obj = DB::table('customers')->where('site', array_get($review,'site'))->where('customer_id', array_get($review,'customer_id'))->first();
            $customer = json_decode(json_encode($customer_obj), true);
        }
        $review_info=[];
        if(array_get($review,'review') && array_get($review,'site')){
            $review_info_obj = DB::table('review_customers')->where('site', array_get($review,'site'))->where('review', array_get($review,'review'))->first();
            $review_info = json_decode(json_encode($review_info_obj), true);
        }
        $return['customer'] = $customer;
        $return['review_info'] = $review_info;
        $return['users'] = $this->getUsers();
        $return['steps'] = DB::table('review_step')->get();
        $return['review'] = $review;
        $return['remark'] = array_get($review,'remark');
        $return['sellerids'] = $this->getSellerIds();
        $return['accounts'] = $this->getAccounts();
        $encrypted_email = (DB::table('client_info')->where('email',$review['buyer_email'])->value('encrypted_email'))??$review['buyer_email'];
        $return['emails'] = DB::table('sendbox')->where('to_address', array_get($review,'buyer_email'))->orderBy('date','desc')->get(['*',DB::RAW('\''.$encrypted_email.'\' as to_address')]);
        $return['emails'] =json_decode(json_encode($return['emails']), true);
        $review['buyer_email'] = $encrypted_email;

        if($order) $return['order'] = $order;
        return view('review/edit',$return);

    }

    public function update(Request $request,$id)
    {
        if(!Auth::user()->can(['review-update'])) die('Permission denied -- review-update');

        if($request->get('status')==6 && !($request->get('creson'))){
            $request->session()->flash('error_message','Set Review Failed, Set Review Closed Must Fill Closed Reson !.');
            return redirect()->back()->withInput();
            die();
        }

        $seller_account = Review::findOrFail($id);;

        $seller_account->seller_id = $request->get('rebindordersellerid');
        $seller_account->amazon_order_id = $request->get('rebindorderid');
        $seller_account->buyer_email = (DB::table('client_info')->where('encrypted_email',$request->get('buyer_email'))->value('email'))??$request->get('buyer_email');
        $seller_account->buyer_phone = $request->get('buyer_phone');
        $seller_account->etype = $request->get('etype');
        $seller_account->remark = $request->get('remark');
        $seller_account->edate = date('Y-m-d');
        $seller_account->nextdate = $request->get('nextdate');
        $seller_account->commented = $request->get('commented');
        $seller_account->customer_feedback = $request->get('customer_feedback');
        $do_ids = $request->get('do_id');

        if($do_ids){
            asort($do_ids);
            $do_log = [];
            foreach($do_ids as $k=>$do_id){
                if($do_id=='X'){
                    $seller_account->status = $request->get('status');
                    if($request->get('status')==6) $seller_account->creson = $request->get('creson');
                    $do_log[$do_id]['do_date']=$request->get('do_date_'.$do_id);
                    $do_log[$do_id]['do_content']=$request->get('status');
                }else{
                    $do_log[$do_id]['do_date']=$request->get('do_date_'.$do_id);
                    $do_log[$do_id]['do_content']=$request->get('valuelink_follow_content_'.$do_id);
                    $seller_account->status = $do_id;
                }
            }
            $seller_account->follow_content = serialize($do_log);
        }

        if(Auth::user()->admin && $request->get('user_id')){
            if($seller_account->user_id!=$request->get('user_id')){
                DB::table('review_change_log')->insert(array(
                    'review_id'=>$id,
                    'to_user_id'=>$request->get('user_id'),
                    'user_id'=>Auth::user()->id,
                    'date'=>date('Y-m-d H:i:s')
                ));
            }
            $seller_account->user_id = $request->get('user_id');
        }
        if ($seller_account->save()) {
            $request->session()->flash('success_message','Set Review Success');
            return redirect('review/'.$id.'/edit');
        } else {
            $request->session()->flash('error_message','Set Review Failed');
            return redirect()->back()->withInput();
        }
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

    public function getSitePairs(){
        return array(
            'CA' => 'www.amazon.ca',
            'JP' => 'www.amazon.co.jp',
            'UK' => 'www.amazon.co.uk',
            'US' => 'www.amazon.com',
            'DE' => 'www.amazon.de',
            'ES' => 'www.amazon.es',
            'FR' => 'www.amazon.fr',
            'IT' => 'www.amazon.it'
        );
    }

}