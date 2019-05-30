<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use App\Classes\SapRfcRequest;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;
use App\Accounts;


class CrmController extends Controller
{
	use \App\Traits\Mysqli;
	use \App\Traits\DataTables;
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
		if(!Auth::user()->can(['crm-show'])) die('Permission denied -- crm-show');
		$date_from=date('Y-m-d',strtotime('-90 days'));
		$date_to=date('Y-m-d');
		return view('crm/index',['date_from'=>$date_from,'date_to'=>$date_to]);
	}

	//ajax获取列表数据
	public function get(Request $request)
	{
		$sql = $this->getCrmSql($request);
		$start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 10);
        $limit =  "{$start},{$length}";
        $sql .= " limit $limit";

		$lists = array();
		$_lists = $this->queryRows($sql);
		if($_lists){
			foreach($_lists as $key=>$val){
				if(!Auth::user()->can(['crm-update'])){
					$action = '<a class="btn btn-danger btn-xs" href="'.url('crm/show?id='.$val['id']).'" target="_blank">Show</a>';
				}else{
					$action = '<a href="'.url('crm/edit?id='.$val['id']).'" target="_blank" class="badge badge-success"> Edit </a> <a class="btn btn-danger btn-xs" href="'.url('crm/show?id='.$val['id']).'" target="_blank">Show</a>';
				}
				$lists[] = array(
					$val['id'],
					$val['date'],
					$val['name'],
					$val['email'],
					$val['phone'],
					$val['country'],
					$val['from'],
					$val['brand'],
					$val['times_ctg'],
					$val['times_rsg'],
					$val['times_negative_review'],
					$val['times_review'],
					$val['order_num'],
					$action

				);
			}
		}

        $recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');

        $records["data"] = $lists;
        $records["draw"] = intval($_REQUEST['draw']);
        $records["recordsTotal"] = $recordsTotal;
        $records["recordsFiltered"] = $recordsFiltered;

        echo json_encode($records);
	}
	/*
	得到列表搜索的sql语句，导出和列表共用一个sql语句,列表就是再加上限制的条数
	 */
	public function getCrmSql($request)
	{
		$order_column = $request->input('order.0.column');
		$orderArray = array(0=>'id',1=>'date',8=>'times_ctg',9=>'times_rsg',10=>'times_negative_review',11=>'times_review',12=>'order_num');

		$orderby = 'id';
		if(isset($orderArray[$order_column])){
			$orderby = $orderArray[$order_column];
		}
        $sort = $request->input('order.0.dir','desc');

        $where = '';
		if($request->input('order_id') && $request->input('order_id')){
			$orderid = $request->input('order_id');
			//求出这个order_id所在的客户id是多少
			$sql = "select b.client_id as id
					from client_info as b
					join client_order_info as c on c.ci_id = b.id
					where amazon_order_id ='$orderid' limit 1";
			$idData = $this->queryRows($sql);
			if($idData){
				$where .= " and a.id = ".$idData[0]['id'];
			}else{
				$where .= ' and 1 !=1 ';
			}

		}
		$date_from=$request->input('date_from')?$request->input('date_from'):date('Y-m-d',strtotime('- 90 days'));
        $date_to=$request->input('date_to')?$request->input('date_to'):date('Y-m-d');
        $where .= " and date >= '$date_from' and date<= '$date_to'";//搜索时间范围内
		$where_son = '';
        //搜索各个字段内
		$searchField1 = array('id','times_ctg','times_rsg','times_negative_review','times_review');
		$searchField2 = array('name','email','phone','country','from','brand');
		foreach($searchField1 as $field){
			if($request->input($field)){
				$value = $request->input($field);
				$where .= " and a.$field = '$value'";
			}
		}
		foreach($searchField2 as $field){
			if($request->input($field)){
				$value = $request->input($field);
				$where_son .= " and t1.$field = '$value'";
			}
		}
		$sql = "select SQL_CALC_FOUND_ROWS a.id as id,a.date as date,c.c_name as name,c.c_email as email,c.c_phone as phone,c.c_country as country,c.`c_from` as `from`,c.c_brand as brand,
a.times_ctg as times_ctg,a.times_rsg as times_rsg,a.times_negative_review as times_negative_review,a.times_review as times_review,if(num>0,num,0) as order_num 
			FROM client as a
			join (
					select count(*) as num,client_id,max(t1.name) as c_name,max(t1.email) as c_email,max(t1.phone) as c_phone,max(t1.country) as c_country,max(t1.`from`) as `c_from`,max(t1.brand) as c_brand 
					from client_info t1
					left join client_order_info as t2 on t1.id = t2.ci_id 
					where 1=1 $where_son 
					group by client_id
			) as c on a.id = c.client_id
			where 1 = 1 $where 
			order by $orderby $sort ";
		return $sql;
	}

	//导出数据
	public function export(Request $request)
	{
		set_time_limit(0);
		if(!Auth::user()->can(['crm-export'])) die('Permission denied -- crm-export');
		$sql = $this->getCrmSql($request);

		$data = $this->queryRows($sql);
		$arrayData = array();
		$headArray = array('ID','Date','Name','Email','Phone','Country','From','Brand','CTG','RSG','Negative Review','Review','Order Number');
        $arrayData[] = $headArray;

		foreach ($data as $key=>$val){
            $arrayData[] = array(
                $val['id'],
                $val['date'],
                $val['name'],
				$val['email'],
                $val['phone'],
                $val['country'],
                $val['from'],
                $val['brand'],
				strval($val['times_ctg']),//数字转化为字符串，不然整数0导出到excel会显示空白
				strval($val['times_rsg']),
				strval($val['times_negative_review']),
				strval($val['times_review']),
				strval($val['order_num']),
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
            header('Content-Disposition: attachment;filename="Export_CRM.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
	}

	//从列表点击edit，进入编辑页面
	public function edit(Request $request)
	{
		if(!Auth::user()->can(['crm-update'])) die('Permission denied -- crm-update');
		$id = $request->input('id');
		$contactInfo = array();
		$contactBasic['id'] = $id;
		if($id){
			$sql = "select b.client_id as id,b.id as info_id,c.id as cid,b.name as name,b.email as email,b.phone as phone,b.country as country,b.`from` as `from`,b.brand as brand,c.amazon_order_id as amazon_order_id
			FROM client_info as b
			left join client_order_info as c on b.id = c.ci_id
			where b.client_id = $id
			order by b.id asc ";
			$_data = $this->queryRows($sql);
			foreach($_data as $key=>$val){
            	//联系人基本信息
				$contactInfo[$val['email']]['info_id'] = $val['info_id'];
				$contactInfo[$val['email']]['email'] = $val['email'];
				$contactInfo[$val['email']]['phone'] = $val['phone'];
				$contactInfo[$val['email']]['amazon_order_id'][$val['cid']] = $val['amazon_order_id'];
				$contactBasic['country'] = $val['country'];
				$contactBasic['name'] = $val['name'];
				$contactBasic['brand'] = $val['brand'];
				$contactBasic['from'] = $val['from'];
			}
		}
		if(!isset($contactBasic['name'])){
			$request->session()->flash('error_message','No Data By Id = '.$id);
			return redirect('/crm');
		}

		return view('crm/edit',['contactInfo'=>$contactInfo,'contactBasic'=>$contactBasic]);
	}
	//添加客户信息
	public function create(Request $request)
	{
		if(!Auth::user()->can(['crm-add'])) die('Permission denied -- crm-add');
		$id = DB::table('client')->max('id')+1;
		return view('crm/add',['id'=>$id]);
	}

	/*
	 * 在编辑页面保存，更新数据库里的该条数据
	 * 添加客户数据
	 */
	public function update(Request $request)
	{
		$data = $_POST;
		$old_id = isset($data['old_id']) ? $data['old_id'] : 0;
		//查填写的客户id是否存在，如果不存在就要新添加记录
		$clientData = DB::table('client')->select('id')->where('id',$data['id'])->get()->toArray();
		if(empty($clientData)){
			if(!DB::table('client')->insert(array('id'=>$data['id'],'date'=>date('Y-m-d')))){
				$request->session()->flash('error_message','Add Failed');
				return redirect()->back()->withInput();
			}
		}
		$insertInfo = array('client_id'=>$data['id'],'name'=>$data['name'],'country'=>$data['country'],'from'=>$data['from'],'brand'=>$data['brand']);
		//查出client_info表的id,把之前的该客户的client_info数据删掉
		$_ciids = DB::table('client_info')->select('id')->where('client_id',$old_id)->get()->toArray();
		$ciids = array();
		foreach($_ciids as $key=>$val){
			$ciids[] = $val->id;
		}
		DB::beginTransaction();
		DB::table('client_info')->whereIn('id',$ciids)->delete();//删除掉client_info表里的该条数据
		DB::table('client_order_info')->whereIn('ci_id',$ciids)->delete();//订单信息，删除之前的

		foreach($_POST['group-data'] as $key=>$val){
			//检查是否有相同的邮箱,email要保持唯一性
			$_email = DB::table('client_info')->where('email',$val['email'])->get()->toArray();
			if($_email){
				DB::rollBack();
				$request->session()->flash('error_message','Save Failed,Same Email By '.$val['email']);
				return redirect()->back()->withInput();
			}
			$insertInfo['email'] = $val['email'];
			$insertInfo['phone'] = $val['phone'];
			$insert['ci_id'] = $res =  DB::table('client_info')->insertGetId($insertInfo);
			if(empty($res)){
				DB::rollBack();
				$request->session()->flash('error_message','Save Email Failed by '.$val['email']);
				return redirect()->back()->withInput();
			}

			foreach($val['order-list'] as $v){
				if($v['amazon_order_id']){
					//判断该order_id是否有，order_id是唯一性的
					$_order = DB::table('client_order_info')->where('amazon_order_id',$v['amazon_order_id'])->get()->toArray();
					if($_order){
						DB::rollBack();
						$request->session()->flash('error_message','Save Failed,,Same Order Id By '.$v['amazon_order_id']);
						return redirect()->back()->withInput();
					}
					//添加现在获取到的
					$insert['amazon_order_id'] = $v['amazon_order_id'];
					$res = DB::table('client_order_info')->insert($insert);
					if(empty($res)){
						DB::rollBack();
						$request->session()->flash('error_message','Save Order Failed By '.$v['amazon_order_id']);
						return redirect()->back()->withInput();
					}
				}
			}
		}
		if($old_id && $old_id!=$data['id']){
			//如果旧的客户id不等于新填写的客户id,删除旧的客户记录
			DB::table('client')->where('id',$old_id)->delete();
		}
		DB::commit();
		if(empty($old_id)){
			//添加界面之后，跳转到列表页
			return redirect('/crm');
		}
		return redirect('crm/edit?id='.$data['id']);
	}

	//从列表点击edit，进入编辑页面
	public function show(Request $request)
	{
		if(!Auth::user()->can(['crm-show'])) die('Permission denied -- crm-show');
		$id = $request->input('id',0);
		if($id){
			$sap = new SapRfcRequest();
			$sql = "select b.name as name,b.email as email,b.phone as phone,b.country as country,b.`from` as `from`,c.amazon_order_id as order_id
			FROM client_info as b
			left join client_order_info as c on b.id = c.ci_id
			where b.client_id = $id
			order by b.id desc ";
			$_data = $this->queryRows($sql);
			$orderArr = array();
			$contactInfo = array();
			$emailArr = $emails = array();
			foreach($_data as $key=>$val){
				//获取sap订单信息
				$orderid = $val['order_id'];
				try{
					$orderArr[$key] = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $orderid]));
					$orderArr[$key]['SellerName'] = Accounts::where('account_sellerid', $orderArr[$key]['SellerId'])->first()->account_name ?? 'No Match';
				} catch (\Exception $e) {

                }

            	//联系人基本信息
				$contactInfo[$val['email']] = array('name'=>$val['name'],'email'=>$val['email'],'phone'=>$val['phone'],'country'=>$val['country']);
				$emails[] = $val['email'];
			}

			$userRows = DB::table('users')->select('id', 'name')->get();

            $users = array();
            foreach ($userRows as $row) {
                $users[$row->id] = $row->name;
            }
			//与联系人的邮箱联系内容
			$emails = DB::table('sendbox')->whereIn('to_address', $emails)->orderBy('date', 'desc')->get();
            $emails = json_decode(json_encode($emails), true); // todo
		}
		return view('crm/show',['orderArr'=>$orderArr, 'contactInfo'=> $contactInfo, 'emails' => $emails,'users'=>$users]);
	}

	/*
	 * 导入excel表格数据到CRM模块
	 */
	public function import( Request $request )
	{
		if(!Auth::user()->can(['crm-import'])) die('Permission denied -- crm-import');
		$addnum = 0;
		if($request->isMethod('POST')){
			$file = $request->file('importFile');
			if($file){
				if($file->isValid()){
					$originalName = $file->getClientOriginalName();
					$ext = $file->getClientOriginalExtension();
					$type = $file->getClientMimeType();
					$realPath = $file->getRealPath();
					$newname = date('Y-m-d-H-i-s').'-'.uniqid().'.'.$ext;
					$newpath = '/uploads/crm/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);

					if($bool){
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						//得到的数据中,A=>name,B=>E-email,C=>phone,D=>Amazon_Order_Id,E=>country,F=>brand,G=>from
						$emailArr = $orderArr = array();
						foreach($importData as $key => $data){
							$emailArr[] = $data['B'];
							$orderArr[] = $data['D'];
						}
						//判断email和oderid是否已经存在，已经存在就提示
						$_email = DB::table('client_info')->whereIn('email',$emailArr)->get(array('email'))->toArray();
						$_order = DB::table('client_order_info')->whereIn('amazon_order_id',$orderArr)->get()->toArray();
						$sameEmail = $sameOrder = array();
						//循环得到相同的邮箱和相同订单号
						foreach($_email as $val){
							$sameEmail[] = $val->email;
						}
						foreach($_order as $val){
							$sameOrder[] = $val->amazon_order_id;
						}
						//忽略掉这些相同的邮箱和相同订单号
						if($sameEmail || $sameOrder){
							foreach($importData as$key=>$data){
								if(in_array($data['B'],$sameEmail) || in_array($data['D'],$sameOrder)){
									unset($importData[$key]);
								}
							}
						}

						//开始插入数据
						DB::beginTransaction();
						$insertOrder = array();
						foreach($importData as $key => $data){
							if($key>1 && array_get($data,'A') && array_get($data,'B')){
								$insertInfo = array(
									'name'=>$data['A'],
									'email'=>$data['B'],
									'phone'=>$data['C'],
									'country'=>$data['E'],
									'brand'=>$data['F'],
									'from'=>empty($data['G']) ? 'Chat' : $data['G'],
								);
								$insertInfo['client_id'] = $res = DB::table('client')->insertGetId(array('date'=>date('Y-m-d')));
								$ci_id = DB::table('client_info')->insertGetId($insertInfo);
								if(empty($res) || empty($ci_id)){
									DB::rollBack();
									$request->session()->flash('error_message','Import Data Failed');
									return redirect()->back()->withInput();
								}
								if(isset($data['D']) && $data['D']){
									$insertOrder[] = array(
										'amazon_order_id' => $data['D'],
										'ci_id' => $ci_id,
									);
								}
								$addnum = $addnum + 1;
							}
						}
						//添加crm的订单信息表
						if($insertOrder){
							batchInsert('client_order_info',$insertOrder);
						}
						DB::commit();
						$request->session()->flash('success_message','Import '.$addnum.' pieces of Data Success!');
					}else{
						$request->session()->flash('error_message','Import Data Failed');
					}
				}
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
			}
		}
		return redirect('crm');
	}

	/*
	 * 下载导入excel表格的模板
	 */
	public function download(Request $request)
	{
		if(!Auth::user()->can(['crm-import'])) die('Permission denied -- crm-import');
		$filepath = 'clients import template.xls';
		$file=fopen($filepath,"r");
		header("Content-type:text/html;charset=utf-8");
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($filepath));
		header("Content-Disposition: attachment; filename=".$filepath);
		echo fread($file,filesize($filepath));
		fclose($file);
	}




}