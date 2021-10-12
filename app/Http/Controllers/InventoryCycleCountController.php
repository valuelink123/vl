<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\InventoryCycleCount;
use App\Models\InventoryCycleCountReason;
use DB;


class InventoryCycleCountController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */
	use \App\Traits\DataTables;
	use \App\Traits\Mysqli;

	public $start_date = '';//搜索时间范围的开始时间
	public $end_date = '';//搜索时间范围的结束时间

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
		if(!Auth::user()->can(['inventory-cycle-count-show'])) die('Permission denied -- inventory cycle count show');
		$start_date = date('Y-m-d',strtotime(' -30 day'));
		$end_date = date('Y-m-d');
		$status = InventoryCycleCount::STATUS;
		return view('inventoryCycleCount/index',['start_date'=>$start_date,'end_date'=>$end_date,'status'=>$status]);
	}

	//展示列表数据
	public function list(Request $req)
	{
		$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
		$search = $this->getSearchData(explode('&',$search));
		$where = ' where 1 = 1';
        $from_date = isset($search['from_date']) ? $search['from_date'] : '';//选的时间类型
        $to_date = isset($search['to_date']) ? $search['to_date'] : '';//站点，为marketplaceid
        $sku = isset($search['sku']) ? $search['sku'] : '';//账号id,例如115,137
		$factory = isset($search['factory']) ? $search['factory'] : '';
		$status = isset($search['status']) ? $search['status'] : '';
		if($from_date){
			$where .= " and inventory_cycle_count.date >= '".$from_date."'";
		}
		if($to_date){
			$where .= " and inventory_cycle_count.date <= '".$to_date."'";
		}
		if($sku){
			$sku_arr = explode(';',$sku);
			$sku_str = implode("','",$sku_arr);
			$where .= " and inventory_cycle_count.sku in( '".$sku_str."')";
		}
		if($factory){
			$where .= " and inventory_cycle_count.factory = '".$factory."'";
		}
		if($status){
			$where .= " and inventory_cycle_count.status = {$status}";
		}

		if($_REQUEST['length']){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}

		//left join inventory_cycle_count_reason on inventory_cycle_count.id = inventory_cycle_count_reason.inventory_cycle_count_id
		$sql = "select *  
				from inventory_cycle_count 
				{$where} order by id desc {$limit}";
		$itemData = DB::select($sql);
		$recordsTotal = $recordsFiltered = DB::select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = array();

//		$showOrder = Auth::user()->can(['ccp-showOrderList']) ? 1 : 0;//是否有查看详情权限
		$statusArr = InventoryCycleCount::STATUS;
		$total['date'] = '汇总';
		$total['sku'] = $total['describe'] = $total['factory'] = $total['location'] = $total['cost'] = $total['dispose_time'] = $total['confirm_time'] = '-';
		$total['action'] = '-';
		$total['actual_number'] = 0;//汇总的实物数量
		$total['dispose_before_number'] = 0;//汇总的处理前账面数量
		$total['difference_before_number'] = 0;//汇总的处理前差异数量 = 每一列的差异数量的绝对值再求和
		$total['dispose_after_number'] = 0;//汇总的处理后账面数量
		$total['difference_after_number'] = 0;//汇总的处理后差异数量 = 每一列的差异数量的绝对值再求和
		$total['difference_amount'] = 0;//汇总的差异金额 = 每一列的差异金额的绝对值求和
		$total['cost'] = $total['status'] = '-';
//		$sum_dispose_before_amount = 0;//求和（每一列的账面数量*每一列的产品成本）

		foreach($itemData as $key=>$val){
			$data[$key] = $val = (array)$val;
			//处理后的数量是从sap的接口中获取，当状态改为已确认的时候，处理后的数量才是从数据库里面取dispose_after_number这个字段，不然的话是实时的取sap的数据
			if($val['status'] != 4){
				$data[$key]['dispose_after_number'] = 10;//测试数据
			}

			$data[$key]['describe'] = '-';
//			$data[$key]['cost'] = '1';//测试，产品成本=1
			$data[$key]['difference_before_number'] = $data[$key]['actual_number'] - $data[$key]['dispose_before_number'];//处理前差异数量=实物-处理前数量
//			$data[$key]['difference_amount'] = sprintf("%.2f",$data[$key]['difference_before_number'] * $data[$key]['cost']);//差异金额=差异数量*产品成本
			$data[$key]['difference_before_rate'] = $data[$key]['dispose_before_number']>0 ? sprintf("%.2f",abs($data[$key]['difference_before_number']/$data[$key]['dispose_before_number'])) : '0.00';//最初差异率 =（处理前差异数量/处理前数量）再取绝对值
			$data[$key]['difference_after_number'] = $data[$key]['actual_number'] - $data[$key]['dispose_after_number'];//处理后差异数量=实物-处理后数量
			$data[$key]['difference_after_rate'] = $data[$key]['dispose_after_number']>0 ? sprintf("%.2f",abs($data[$key]['difference_after_number']/$data[$key]['dispose_after_number'])) : '0.00';//处理后差异率 =（处理厚差异数量/处理后数量）再取绝对值

			$data[$key]['status'] = isset($statusArr[$data[$key]['status']]) ? $statusArr[$data[$key]['status']] : $data[$key]['status'];
			$action = '';
			if($val['status']==1){
				$action = '<a href="javascript:void(0);" class="complete_inventory edit-action" data-after-status="2">完成盘点</a>';
			}elseif($val['status']==2){
				$action = '<a href="javascript:void(0);" class="complete_dispose edit-action" data-after-status="3">完成差异处理</a>';
			}elseif($val['status']==3){
				$action = '<a href="javascript:void(0);" class="confirm edit-action" data-after-status="4">确认</a>';
			}
			$data[$key]['action'] = '<div data-id="'.$val['id'].'">'.$action.'
<a target="_blank" href="/inventoryCycleCount/show?id=' . $val['id'] . '">查看明细</a></div>';

			//最后一列的汇总数据相关
			$total['actual_number'] += $data[$key]['actual_number'];//汇总的真实数量
			$total['dispose_before_number'] += $data[$key]['dispose_before_number'];//汇总的处理前数量
			$total['difference_before_number'] += abs($data[$key]['difference_before_number']);//汇总的处理前差异数量

			$total['dispose_after_number'] += $data[$key]['dispose_after_number'];//汇总的处理后数量
			$total['difference_after_number'] += abs($data[$key]['difference_after_number']);//汇总的处理后差异数量
//			$total['difference_amount'] += abs($data[$key]['difference_amount']);
//			$sum_dispose_before_amount += $data[$key]['dispose_before_number'] * $data[$key]['cost'];

		}
//		$total['difference_before_rate'] = $sum_dispose_before_amount > 0 ? sprintf("%.2f",$total['difference_amount']/$sum_dispose_before_amount) : '0.00';//汇总的最初差异率 = 汇总的差异金额/sum(每一列的账面数量*每一列的产品成本)
		$total['difference_before_rate'] = $total['difference_after_rate'] = '-';
		$data[] = $total;
		$data = array_values($data);
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	/*
	 * 导出操作
	 */
	public function export(Request $req)
	{
		$from_date = isset($_REQUEST['from_date']) ? $_REQUEST['from_date'] : '';//选的时间类型
		$to_date = isset($_REQUEST['to_date']) ? $_REQUEST['to_date'] : '';//站点，为marketplaceid
		$sku = isset($_REQUEST['sku']) ? $_REQUEST['sku'] : '';//账号id,例如115,137
		$factory = isset($_REQUEST['factory']) ? $_REQUEST['factory'] : '';
		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
		$where  =' where 1 = 1';
		if($from_date){
			$where .= " and inventory_cycle_count.date >= '".$from_date."'";
		}
		if($to_date){
			$where .= " and inventory_cycle_count.date <= '".$to_date."'";
		}
		if($sku){
			$sku_arr = explode(';',$sku);
			$sku_str = implode("','",$sku_arr);
			$where .= " and inventory_cycle_count.sku in( '".$sku_str."')";
		}
		if($factory){
			$where .= " and inventory_cycle_count.factory = '".$factory."'";
		}
		if($status){
			$where .= " and inventory_cycle_count.status = {$status}";
		}

		$sql = "select inventory_cycle_count.*,inventory_cycle_count_reason.solution as solution,inventory_cycle_count_reason.number as reason_number,inventory_cycle_count_reason.reason as reason,inventory_cycle_count_reason.reason_remark as reason_remark,inventory_cycle_count_reason.status as reason_status,inventory_cycle_count_reason.dispose_time as reason_dispose_time,inventory_cycle_count_reason.dispose_userid as reason_dispose_userid 
				from inventory_cycle_count 
				left join inventory_cycle_count_reason on inventory_cycle_count.id = inventory_cycle_count_reason.inventory_cycle_count_id
				{$where} order by inventory_cycle_count.id desc";
		$itemData = DB::select($sql);
		$data = array();

		$headArray = array('盘点日期','SKU','工厂','库位','处理前数量','实物数量','最初差异数量','最初差异率','处理后数量','处理后差异数量','处理后差异率','状态','原因类别','差异数量','备注具体原因','处理方案','处理时间','处理人');
		$arrayData[] = $headArray;

		$statusArr = InventoryCycleCount::STATUS;
		$total['actual_number'] = 0;//汇总的实物数量
		$total['dispose_before_number'] = 0;//汇总的处理前账面数量
		$total['difference_before_number'] = 0;//汇总的处理前差异数量 = 每一列的差异数量的绝对值再求和
		$total['dispose_after_number'] = 0;//汇总的处理后账面数量
		$total['difference_after_number'] = 0;//汇总的处理后差异数量 = 每一列的差异数量的绝对值再求和
		$total['difference_amount'] = 0;//汇总的差异金额 = 每一列的差异金额的绝对值求和

		$statusReasonArr = InventoryCycleCountReason::STATUS;
		$reasonArr = InventoryCycleCountReason::REASON;
		$userData = $this->getUsersIdName();
		$unique_id = array();
		foreach($itemData as $key=>$val) {
			$data[$key] = $val = (array)$val;
			//处理后的数量是从sap的接口中获取，当状态改为已确认的时候，处理后的数量才是从数据库里面取dispose_after_number这个字段，不然的话是实时的取sap的数据
			if ($val['status'] != 4) {
				$data[$key]['dispose_after_number'] = 10;//测试数据
			}

			$data[$key]['difference_before_number'] = $data[$key]['actual_number'] - $data[$key]['dispose_before_number'];//处理前差异数量=实物-处理前数量
			$data[$key]['difference_before_rate'] = $data[$key]['dispose_before_number'] > 0 ? sprintf("%.2f", abs($data[$key]['difference_before_number'] / $data[$key]['dispose_before_number'])) : '0.00';//最初差异率 =（处理前差异数量/处理前数量）再取绝对值
			$data[$key]['difference_after_number'] = $data[$key]['actual_number'] - $data[$key]['dispose_after_number'];//处理后差异数量=实物-处理后数量
			$data[$key]['difference_after_rate'] = $data[$key]['dispose_after_number'] > 0 ? sprintf("%.2f", abs($data[$key]['difference_after_number'] / $data[$key]['dispose_after_number'])) : '0.00';//处理后差异率 =（处理厚差异数量/处理后数量）再取绝对值
			$data[$key]['status'] = isset($statusArr[$data[$key]['status']]) ? $statusArr[$data[$key]['status']] : $data[$key]['status'];

			//原因相关数据
			//status,0待处理，1已处理
			if ($val['reason_status'] == 0) {
				$data[$key]['dispose_userid'] = '-';
				$data[$key]['dispose_time'] = '-';
			}
			$data[$key]['reason_dispose_userid'] = isset($userData[$val['reason_dispose_userid']]) ? $userData[$val['reason_dispose_userid']] : $val['reason_dispose_userid'];
			$data[$key]['reason_status'] = isset($statusReasonArr[$val['reason_status']]) ? $statusReasonArr[$val['reason_status']] : $val['reason_status'];
			$data[$key]['reason'] = isset($reasonArr[$val['reason']]) ? $reasonArr[$val['reason']] : $val['reason'];

			//最后一列的汇总数据相关
			if (!isset($unique_id[$val['id']])) {
				$total['actual_number'] += $data[$key]['actual_number'];//汇总的真实数量
				$total['dispose_before_number'] += $data[$key]['dispose_before_number'];//汇总的处理前数量
				$total['difference_before_number'] += abs($data[$key]['difference_before_number']);//汇总的处理前差异数量
				$total['dispose_after_number'] += $data[$key]['dispose_after_number'];//汇总的处理后数量
				$total['difference_after_number'] += abs($data[$key]['difference_after_number']);//汇总的处理后差异数量
			}
			$unique_id[$val['id']] = $val['id'];


			//$headArray = array('原因类别','差异数量','备注具体原因','处理方案','处理时间','处理人');
			$arrayData[] = array(
				$data[$key]['date'],
				$data[$key]['sku'],
				$data[$key]['factory'],
				$data[$key]['location'],
				$data[$key]['dispose_before_number'],
				$data[$key]['actual_number'],
				$data[$key]['difference_before_number'],
				$data[$key]['difference_before_rate'],
				$data[$key]['dispose_after_number'],
				$data[$key]['difference_after_number'],
				$data[$key]['difference_after_rate'],
				$data[$key]['status'],
				$data[$key]['reason'],
				$data[$key]['reason_number'],
				$data[$key]['reason_remark'],
				$data[$key]['solution'],
				$data[$key]['reason_dispose_time'],
				$data[$key]['reason_dispose_userid'],
			);
		}
		$arrayData[] = array(
			'汇总',
			'-',
			'-',
			'-',
			$total['dispose_before_number'],
			$total['actual_number'],
			$total['difference_before_number'],
			'-',
			$total['dispose_after_number'],
			$total['difference_after_number'],
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
			'-',
		);

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
			header('Content-Disposition: attachment;filename="导出库存盘点.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
	/*
	 * 主列表数据的额编辑操作
	 */
	public function edit(Request $request)
	{
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : 0;
		$after_status = isset($_REQUEST['status']) && $_REQUEST['status'] ? $_REQUEST['status'] : 0;
		$update['status'] = $after_status;
		//$after_status等于2的时候，改为完成盘点状态的时候，改状态字段
		if($after_status==3){//改为完成差异处理的时候，改状态字段，和dispose_time处理差异时间
			$update['dispose_time'] = date('Y-m-d H:i:s');
		}elseif($after_status==4){//改为确认状态的时候，改状态字段，和confirm_time确认时间
			$update['confirm_time'] = date('Y-m-d H:i:s');
		}
		$res = InventoryCycleCount::where('id',$id)->update($update);
		return array('status'=>$res);
	}
	/*
	 * 查看明细编辑页面
	 */
	public function show(Request $request)
	{
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : 0;
		$data = InventoryCycleCount::where('id',$id)->with('reason')->first();
		if($data){
			$data = $data->toArray();
			//处理后的数量是从sap的接口中获取，当状态改为已确认的时候，处理后的数量才是从数据库里面取dispose_after_number这个字段，不然的话是实时的取sap的数据
			if($data['status'] != 4){
				$data['dispose_after_number'] = 10;//测试数据
			}


			$data['difference_before_number'] = $data['actual_number'] -$data['dispose_before_number'];//处理前差异数量=实物-处理前数量
			$data['difference_before_rate'] = $data['dispose_before_number']>0 ? sprintf("%.2f",abs($data['difference_before_number']/$data['dispose_before_number'])) : '0.00';//最初差异率 =（处理前差异数量/处理前数量）再取绝对值

			$data['difference_after_number'] = $data['actual_number'] - $data['dispose_after_number'];//处理后差异数量=实物-处理后数量
			$data['difference_after_rate'] = $data['dispose_after_number']>0 ? sprintf("%.2f",abs($data['difference_after_number']/$data['dispose_after_number'])) : '0.00';//处理后差异率 =（处理厚差异数量/处理后数量）再取绝对值

			$statusArr = InventoryCycleCount::STATUS;
			$data['status'] = isset($statusArr[$data['status']]) ? $statusArr[$data['status']] : $data['status'];

			$statusReasonArr = InventoryCycleCountReason::STATUS;
			$reasonArr = InventoryCycleCountReason::REASON;
			$userData = $this->getUsersIdName();
			foreach($data['reason'] as $key=>$val){
				//status,0待处理，1已处理
				if($val['status']==0){
					$data['reason'][$key]['action'] = '<div data-id="'.$data['id'].'" data-reasonID="'.$val['id'].'"><a href="javascript:void(0);" class="delete-action">删除</a>
<a href="javascript:void(0);" class="edit-reason-action" data-after-status="1">处理</a></div>';
					$data['reason'][$key]['dispose_userid'] = '-';
					$data['reason'][$key]['dispose_time'] = '-';
				}else{
					$data['reason'][$key]['action'] = '-';
				}
				$data['reason'][$key]['dispose_userid'] = isset($userData[$data['reason'][$key]['dispose_userid']]) ? $userData[$data['reason'][$key]['dispose_userid']] : $data['reason'][$key]['dispose_userid'];
				$data['reason'][$key]['status'] = isset($statusReasonArr[$data['reason'][$key]['status']]) ? $statusReasonArr[$data['reason'][$key]['status']] : $data['reason'][$key]['status'];
				$data['reason'][$key]['reason'] = isset($reasonArr[$data['reason'][$key]['reason']]) ? $reasonArr[$data['reason'][$key]['reason']] : $data['reason'][$key]['reason'];

			}
		}
		return view('inventoryCycleCount/edit',['data'=>$data,'reason'=>InventoryCycleCountReason::REASON]);
	}
	/*
	 * 删除原因操作
	 */
	public function deleteReason(Request $request)
	{
		$id = isset($_REQUEST['reasonID']) && $_REQUEST['reasonID'] ? $_REQUEST['reasonID'] : 0;
		$res = InventoryCycleCountReason::where('id',$id)->delete();
		return array('status'=>$res);
	}
	/*
	 * 添加原因操作
	 */
	public function addReason(Request $request)
	{
		$insert['number'] = isset($_REQUEST['number']) && $_REQUEST['number'] ? $_REQUEST['number'] : 0;
		$insert['reason'] = isset($_REQUEST['reason']) && $_REQUEST['reason'] ? $_REQUEST['reason'] : 0;
		$insert['reason_remark'] = isset($_REQUEST['reason_remark']) && $_REQUEST['reason_remark'] ? $_REQUEST['reason_remark'] : '';
		$insert['solution'] = isset($_REQUEST['solution']) && $_REQUEST['solution'] ? $_REQUEST['solution'] : '';
		$insert['inventory_cycle_count_id'] = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : 0;
		if($insert['inventory_cycle_count_id'] > 0 && $insert['number'] > 0){
			$res = InventoryCycleCountReason::insert($insert);
			return array('status'=>$res);
		}else{
			return array('status'=>0);
		}
	}

	/*
	 * 编辑原因
	 */
	public function editReason(Request $request)
	{
		$id = isset($_REQUEST['reasonID']) && $_REQUEST['reasonID'] ? $_REQUEST['reasonID'] : 0;
		$after_status = isset($_REQUEST['status']) && $_REQUEST['status'] ? $_REQUEST['status'] : 0;
		$update['status'] = $after_status;
		//把状态改为已处理的时候，同时还要修改dispose_userid和dispose_time字段
		if($after_status==1){
			$update['dispose_time'] = date('Y-m-d H:i:s');
			$update['dispose_userid'] = Auth::user()->id;
		}
		$res = InventoryCycleCountReason::where('id',$id)->update($update);
		return array('status'=>$res);
	}
	/*
	 * 下载企管部需要的添加sku模板
	 */
	public function downloadSku()
	{
		$headArray = array('盘点日期','SKU');
		$arrayData[] = $headArray;
		$today = date('Y/m/d');
		$arrayData[] = array($today, '',);

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
			header('Content-Disposition: attachment;filename="导入SKU模板.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
	/*
	 * 导入sku数据
	 */
	public function importSku(Request $request)
	{
		if(!Auth::user()->can(['inventory-cycle-count-addSku'])) die('Permission denied -- inventory-cycle-count-addSku');
		if($request->isMethod('POST')){
			$file = $request->file('import_File');
			if($file){
				if($file->isValid()){
					$ext = $file->getClientOriginalExtension();
					$newname = date('Y-m-d-H-i-s').'-'.uniqid().'.'.$ext;
					$newpath = '/uploads/inventoryCycleCount/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);
					if($bool){
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						$date_arr = array();
						$current_date = date('Y-m-d');
						foreach($importData as $key => $data) {
							if ($key == 1 || empty($data['A']) || empty($data['B'])) {
								unset($importData[$key]);
								continue;
							}
							$date = date('Y-m-d',strtotime($data['A']));
							//导入时，盘点日期需大于等于当前日期(YYYY-MM-DD)
							if($date>=$current_date){
								$date_arr[] = $date;//导入的数据中，所有日期的数组
							}else{
								$request->session()->flash('error_message','导入时，盘点日期需大于等于当前日期');
							}

						}
						//计算导入的数据中有几个日期数据，，每次导入，只允许导入一个日期
						$date_num = count(array_unique($date_arr));
						if($date_num==1) {
							//循环处理插入数据
							foreach ($importData as $key => $data) {
								$date = date('Y-m-d',strtotime($data['A']));
								$insertData = array(
									'date' => $date,
									'sku' => $data['B']
								);
								InventoryCycleCount::updateOrCreate($insertData, []);
								unset($importData[$key]);
							}
						}else{
							$request->session()->flash('error_message','每次导入，只允许导入一个日期');
						}
						$request->session()->flash('success_message','Import Data Success!');
						// return redirect()->back()->withInput();
					}else{
						$request->session()->flash('error_message','Import Data Failed');
						// return redirect()->back()->withInput();
					}
				}else{
					$request->session()->flash('error_message','Import Data Failed,The file is too large');
					// return redirect()->back()->withInput();
				}
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
				// return redirect()->back()->withInput();
			}
		}
		return redirect('/inventoryCycleCount');

	}

	/*
	 * 下载物流部需要的添加真实数量模板
	 */
	public function downloadActualNumber(Request $request)
	{
//		$from_date_download = isset($_REQUEST['from_date_download']) && $_REQUEST['from_date_download'] ? $_REQUEST['from_date_download'] : date('Y-m-d',strtotime(' -30 day'));
//		$to_date_download = isset($_REQUEST['to_date_download']) && $_REQUEST['to_date_download'] ? $_REQUEST['to_date_download'] : date('Y-m-d');
		$today = date('Y-m-d');
		$_data = InventoryCycleCount::where('date','>=',$today)->where('status',1)->get()->toArray();

		$headArray = array('盘点日期','SKU','工厂','库位','实物数量');
		$arrayData[] = $headArray;

		foreach($_data as $key=>$val){
			$arrayData[] = array(
				$val['date'],
				$val['sku'],
				$val['factory'],
				$val['location'],
				$val['dispose_before_number'],
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
			header('Content-Disposition: attachment;filename="导入实物数量模板.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
	/*
	 * 导入真实数量数据
	 */
	public function importActualNumber(Request $request)
	{
		if(!Auth::user()->can(['inventory-cycle-count-addActualNumber'])) die('Permission denied -- inventory-cycle-count-addActualNumber');
		if($request->isMethod('POST')){
			$file = $request->file('import_File');
			if($file){
				if($file->isValid()){
					$ext = $file->getClientOriginalExtension();
					$newname = date('Y-m-d-H-i-s').'-'.uniqid().'.'.$ext;
					$newpath = '/uploads/inventoryCycleCount/'.date('Ymd').'/';
					$inputFileName = public_path().$newpath.$newname;
					$bool = $file->move(public_path().$newpath,$newname);
					if($bool){
						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
						//循环处理插入数据
						foreach($importData as $key => $data){
							if($key==1 || empty($data['A']) || empty($data['B']) || empty($data['D']) || empty($data['E']) || empty($data['F'])){
								unset($importData[$key]);
								continue;
							}
							InventoryCycleCount::where('date',date('Y-m-d',strtotime($data['A'])))->where('sku',$data['B'])->where('factory',$data['D'])->where('location',$data['E'])->where('status',1)->update(['actual_number'=>$data['F']]);
							unset($importData[$key]);
						}

						$request->session()->flash('success_message','Import Data Success!');
					}else{
						$request->session()->flash('error_message','Import Data Failed');
					}
				}else{
					$request->session()->flash('error_message','Import Data Failed,The file is too large');
				}
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
			}
		}
		return redirect('/inventoryCycleCount');
	}

//	/*
//	 * 下载添加原因和解决方案的模板
//	 */
//	public function downloadReason(Request $request)
//	{
//		$from_date_download = isset($_REQUEST['from_date_download']) && $_REQUEST['from_date_download'] ? $_REQUEST['from_date_download'] : date('Y-m-d',strtotime(' -30 day'));
//		$to_date_download = isset($_REQUEST['to_date_download']) && $_REQUEST['to_date_download'] ? $_REQUEST['to_date_download'] : date('Y-m-d');
//		$_data = InventoryCycleCount::leftjoin('inventory_cycle_count_reason','inventory_cycle_count.id','=','inventory_cycle_count_reason.inventory_cycle_count_id')
//		->select('date','sku','factory','location','number','reason','reason_remark','solution' )
//		->where('date','>=',$from_date_download)
//		->where('date','<=',$to_date_download)
//		->where('inventory_cycle_count.status',2)->get()->toArray();
//
//		$headArray = array('盘点日期','SKU','产品描述','工厂','库位','数量','差异原因','备注具体原因','解决方案');
//		$arrayData[] = $headArray;
//
//		foreach($_data as $key=>$val){
//			$arrayData[] = array(
//				$val['date'],
//				$val['sku'],
//				'-',
//				$val['factory'],
//				$val['location'],
//				$val['number'],
//				$val['reason'],
//				$val['reason_remark'],
//				$val['solution'],
//			);
//		}
//
//		if($arrayData){
//			$spreadsheet = new Spreadsheet();
//
//			$spreadsheet->getActiveSheet()
//				->fromArray(
//					$arrayData,  // The data to set
//					NULL,        // Array values with this value will not be set
//					'A1'         // Top left coordinate of the worksheet range where
//				//    we want to set these values (default is A1)
//				);
//			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
//			header('Content-Disposition: attachment;filename="导入差异原因模板.xlsx"');//告诉浏览器输出浏览器名称
//			header('Cache-Control: max-age=0');//禁止缓存
//			$writer = new Xlsx($spreadsheet);
//			$writer->save('php://output');
//		}
//		die();
//	}
//	/*
//	 * 导入原因和解决方案
//	 */
//	public function importReason(Request $request)
//	{
//		if(!Auth::user()->can(['inventory-cycle-count-addReason'])) die('Permission denied -- inventory-cycle-count-addReason');
//		if($request->isMethod('POST')){
//			$file = $request->file('import_File');
//			if($file){
//				if($file->isValid()){
//					$ext = $file->getClientOriginalExtension();
//					$newname = date('Y-m-d-H-i-s').'-'.uniqid().'.'.$ext;
//					$newpath = '/uploads/inventoryCycleCount/'.date('Ymd').'/';
//					$inputFileName = public_path().$newpath.$newname;
//					$bool = $file->move(public_path().$newpath,$newname);
//					if($bool){
//						$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
//						$importData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
//						//循环处理插入数据
//						foreach($importData as $key => $data){
//							if($key==1 || empty($data['A']) || empty($data['B']) || empty($data['D']) || empty($data['E']) || empty($data['F']) || empty($data['G']) || empty($data['H']) || empty($data['I'])){
//								unset($importData[$key]);
//								continue;
//							}
//							$_data = InventoryCycleCount::where('date',date('Y-m-d',strtotime($data['A'])))->where('sku',$data['B'])->where('factory',$data['D'])->where('location',$data['E'])->where('status',2)->first();
//
//							if($_data){
//								$inventory_cycle_count_id = $_data->id;
//							}else{
//								continue;
//							}
//
//							$insertData = array(
//								'inventory_cycle_count_id' => $inventory_cycle_count_id,
//								'number' => $data['F'],
//								'reason' => $data['G'],
//								'reason_remark' => $data['H'],
//								'solution' => $data['I'],
//								'status' =>0,
//								'updated_at' => date('Y-m-d H:i:s'),
//							);
//
//							InventoryCycleCountReason::updateOrCreate(['inventory_cycle_count_id'=>$inventory_cycle_count_id,'reason'=>$insertData['reason']],$insertData);
//							unset($importData[$key]);
//						}
//
//						$request->session()->flash('success_message','Import Data Success!');
//					}else{
//						$request->session()->flash('error_message','Import Data Failed');
//					}
//				}else{
//					$request->session()->flash('error_message','Import Data Failed,The file is too large');
//				}
//			}else{
//				$request->session()->flash('error_message','Please Select Upload File');
//			}
//		}
//		return redirect('/inventoryCycleCount');
//	}
}