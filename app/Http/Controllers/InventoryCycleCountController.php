<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\InventoryCycleCount;
use App\Models\InventoryCycleCountReason;
use DB;
use App\Models\InventoryCycleCountSapInventory;


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
		$limit = '';
		if($_REQUEST['length'] != '-1'){
			$limit = $this->dtLimit($req);
			$limit = " LIMIT {$limit} ";
		}
		$sql = $this->getSql($search) . $limit;

		$itemData = DB::select($sql);
		$recordsTotal = $recordsFiltered = DB::select('SELECT FOUND_ROWS() as total');
		$recordsTotal = $recordsFiltered = $recordsTotal[0]->total;
		$data = array();

//		$showOrder = Auth::user()->can(['ccp-showOrderList']) ? 1 : 0;//是否有查看详情权限
		$statusArr = InventoryCycleCount::STATUS;
		$total['date'] = '汇总';
		$total['id'] = 0;
		$total['sku'] = $total['describe'] = $total['factory'] = $total['location'] = $total['cost'] = $total['dispose_time'] = $total['confirm_time'] = '-';
		$total['action'] = '-';
		//得到原因
		$reasonData = $this->getReasonData($itemData);
		//求和字段说明，actual_number汇总的实物数量，dispose_before_number汇总的处理前sap库存数量，difference_before_number汇总的处理前差异数量 = 每一列的差异数量的绝对值再求和，dispose_after_number汇总的处理后sap库存数量，difference_after_number汇总的处理后差异数量 = 每一列的差异数量的绝对值再求和，difference_amount汇总的差异金额 = 每一列的差异金额的绝对值求和,account_number账面数量，notaccount_number未过账数量，dispose_after_account_number处理后的账面数量，dispose_after_notaccount_number处理后的未过账数量，未过账数量=处理前数量-账面数量
		$total['actual_number'] = $total['dispose_before_number'] = $total['difference_before_number'] = $total['dispose_after_number'] = $total['difference_after_number'] = $total['difference_amount'] = $total['account_number'] = $total['notaccount_number'] = $total['dispose_after_account_number'] = $total['dispose_after_notaccount_number'] = 0;

		$total['cost'] = $total['status'] = $total['reason'] = '-';
//		$sum_dispose_before_amount = 0;//求和（每一列的账面数量*每一列的产品成本）
		foreach($itemData as $key=>$val){
			$data[$key] = $val = (array)$val;

			if(isset($reasonData[$val['id']])){
				$_reason = mb_substr($reasonData[$val['id']],0,20);
				$reason = '<span title="'.$reasonData[$val['id']].'">'.$_reason.'</span>';
			}else{
				$reason = '-';
			}
			$data[$key]['reason'] = $reason;
//			$data[$key]['cost'] = '1';//测试，产品成本=1
			$data[$key]['difference_before_number'] = $data[$key]['actual_number'] - $data[$key]['account_number'];//处理前差异数量=实物-处理前账面数量
//			$data[$key]['difference_amount'] = sprintf("%.2f",$data[$key]['difference_before_number'] * $data[$key]['cost']);//差异金额=差异数量*产品成本
			$data[$key]['difference_before_rate'] = $data[$key]['account_number']>0 ? abs(sprintf("%.2f",abs($data[$key]['difference_before_number']/$data[$key]['account_number']))) : '0.00';//最初差异率 =（处理前差异数量/处理前账面数量）再取绝对值
			$data[$key]['difference_after_number'] = $data[$key]['actual_number'] - $data[$key]['dispose_after_account_number'];//处理后差异数量=实物-处理后账面数量
			$data[$key]['difference_after_rate'] = $data[$key]['dispose_after_account_number']>0 ? abs(sprintf("%.2f",abs($data[$key]['difference_after_number']/$data[$key]['dispose_after_account_number']))) : '0.00';//处理后差异率 =（处理厚差异数量/处理后数量）再取绝对值
			//notaccount_number,未过账数量=处理前sap数量-处理前账面数量
			$data[$key]['notaccount_number'] =$data[$key]['dispose_before_number'] - $data[$key]['account_number'];
			$data[$key]['dispose_after_notaccount_number'] =$data[$key]['dispose_after_number'] - $data[$key]['dispose_after_account_number'];

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
			$total['dispose_before_number'] += $data[$key]['dispose_before_number'];//汇总的处理前sap库存数量
			$total['difference_before_number'] += abs($data[$key]['difference_before_number']);//汇总的处理前差异数量

			$total['dispose_after_number'] += $data[$key]['dispose_after_number'];//汇总的处理后sap库存数量
			$total['difference_after_number'] += abs($data[$key]['difference_after_number']);//汇总的处理后差异数量
//			$total['difference_amount'] += abs($data[$key]['difference_amount']);
//			$sum_dispose_before_amount += $data[$key]['dispose_before_number'] * $data[$key]['cost'];
			$total['notaccount_number'] += abs($data[$key]['notaccount_number']);
			$total['dispose_after_notaccount_number'] += abs($data[$key]['dispose_after_notaccount_number']);
			$total['account_number'] += $data[$key]['account_number'];
			$total['dispose_after_account_number'] += $data[$key]['dispose_after_account_number'];

			//最后把处理前sap库存数量和处理后sap库存数量，加上批注，鼠标移动到就添加已发货已收货等信息
			if($data[$key]['dispose_before_number']){
				$remark = '总已发货数:'.$data[$key]['before_MENGE1'].'；'.'总已收货数:'.$data[$key]['before_MENGE2'].'；'.'期初库存数:'.$data[$key]['before_MSTOCK1'].'；'.'期末库存数:'.$data[$key]['before_MSTOCK2'].'；'.'非限制使用的估价的库存:'.$data[$key]['before_LABST'];
				$data[$key]['dispose_before_number'] = '<div title="'.$remark.'">'.$data[$key]['dispose_before_number'].'</div>';
			}
			if($data[$key]['dispose_after_number']){
				$remark = '总已发货数:'.$data[$key]['after_MENGE1'].'；'.'总已收货数:'.$data[$key]['after_MENGE2'].'；'.'期初库存数:'.$data[$key]['after_MSTOCK1'].'；'.'期末库存数:'.$data[$key]['after_MSTOCK2'].'；'.'非限制使用的估价的库存:'.$data[$key]['after_LABST'];
				$data[$key]['dispose_after_number'] = '<div title="'.$remark.'">'.$data[$key]['dispose_after_number'].'</div>';
			}
		}
//		$total['difference_before_rate'] = $sum_dispose_before_amount > 0 ? sprintf("%.2f",$total['difference_amount']/$sum_dispose_before_amount) : '0.00';//汇总的最初差异率 = 汇总的差异金额/sum(每一列的账面数量*每一列的产品成本)
		$total['difference_before_rate'] = $total['difference_after_rate'] = '-';
		$data[] = $total;
		$data = array_values($data);
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

	public function getReasonData($itemData)
	{
		$ids_str = '';
		foreach($itemData as $key=>$val){
			$ids_str .= $val->id.',';
		}
		$reasonData = array();
		if($ids_str) {
			$reasonSql = "SELECT inventory_cycle_count_reason.*
						FROM inventory_cycle_count
						LEFT JOIN inventory_cycle_count_reason ON inventory_cycle_count.id = inventory_cycle_count_reason.inventory_cycle_count_id 
						WHERE inventory_cycle_count.id in (" . rtrim($ids_str, ',') . ") 
						and inventory_cycle_count_reason.id is not null";
			$_reasonData = DB::select($reasonSql);
			$_reasonData = json_decode(json_encode($_reasonData), true);
			$reasonArr = InventoryCycleCountReason::REASON;
			foreach ($_reasonData as $key => $val) {
				$_reason = isset($reasonArr[$val['reason']]) ? $reasonArr[$val['reason']] : $val['reason'];
				$reason = '数量:' . $val['number'] . ',原因:' . $_reason . ',备注具体原因:' . $val['reason_remark'] . ',处理方案:' . $val['solution'] . ';';
				$reasonData[$val['inventory_cycle_count_id']] = isset($reasonData[$val['inventory_cycle_count_id']]) ? $reasonData[$val['inventory_cycle_count_id']] . $reason : $reason;
			}
		}
		return $reasonData;
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

	public function getSql($search)
	{
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

		$sql = "select SQL_CALC_FOUND_ROWS inventory_cycle_count.*,
before_data.MENGE1 as before_MENGE1,before_data.MENGE2 as before_MENGE2,before_data.MSTOCK1 as before_MSTOCK1,before_data.MSTOCK2 as before_MSTOCK2,before_data.LABST as before_LABST,
after_data.MENGE1 as after_MENGE1,after_data.MENGE2 as after_MENGE2,after_data.MSTOCK1 as after_MSTOCK1,after_data.MSTOCK2 as after_MSTOCK2,after_data.LABST as after_LABST 
				from inventory_cycle_count 
left join (select * from inventory_cycle_count_sapinventory where type = 'BEFORE') as before_data on inventory_cycle_count.id = before_data.inventory_cycle_count_id
left join (select * from inventory_cycle_count_sapinventory where type = 'AFTER') as after_data on inventory_cycle_count.id = after_data.inventory_cycle_count_id
{$where} order by id desc ";
		return $sql;
	}
	/*
	 * 主列表数据的编辑操作
	 */
	public function edit(Request $request)
	{
		$id = isset($_REQUEST['id']) && $_REQUEST['id'] ? $_REQUEST['id'] : 0;
		$id = explode(",",trim($id,','));
		$after_status = isset($_REQUEST['status']) && $_REQUEST['status'] ? $_REQUEST['status'] : 0;
		$update['status'] = $after_status;
		$_data = InventoryCycleCount::whereIn('id',$id)->where('status',$after_status-1)->get()->toArray();
		$res = 0;
		$msg = '失败';
		if(count($_data)==count($id)) {
			//$after_status等于2的时候，改为完成盘点状态的时候，改状态字段
			if ($after_status == 3) {//改为完成差异处理的时候，改状态字段，和dispose_time处理差异时间
				$update['dispose_time'] = date('Y-m-d H:i:s');
			} elseif ($after_status == 4) {//改为确认状态的时候，改状态字段，和confirm_time确认时间
				$update['confirm_time'] = date('Y-m-d H:i:s');
				//改为确认状态的时候，还要更改一下处理后数量改为最新的数量
				foreach ($_data as $key => $val) {
					$skus = array(array('MATNR' => $val['sku']));
					$start_date = $end_date = date('Y-m-d', strtotime($val['date']));
					$sap_inventory_data = $this->getSkuInventoryBySapApi($skus, $start_date, $end_date);
					$_sapdata = $sap_inventory_data[$val['sku']][$val['factory'] . '_' . $val['location']];
					$insertSapData = array(
						'inventory_cycle_count_id' => $val['id'],
						'date' => $val['date'],
						'sku' => $val['sku'],
						'factory' => $val['factory'],
						'location' => $val['location'],
						'type' => 'AFTER',
						'BUKRS' => $_sapdata['BUKRS'],
						'MENGE1' => intval($_sapdata['MENGE1']),
						'MENGE2' => $_sapdata['MENGE2'],
						'MSTOCK1' => $_sapdata['MSTOCK1'],
						'MSTOCK2' => $_sapdata['MSTOCK2'],
						'LABST' => $_sapdata['LABST'],
						'LFGJA' => $_sapdata['LFGJA'],
						'LFMON' => $_sapdata['LFMON'],
					);
					InventoryCycleCountSapInventory::updateOrCreate(['inventory_cycle_count_id' => $val['id'], 'type' => 'AFTER'], $insertSapData);
					$updateData['dispose_after_number'] = $_sapdata['MSTOCK2'];//处理后数量
					InventoryCycleCount::where('id', $val['id'])->update($updateData);
				}
			}
			$res = InventoryCycleCount::whereIn('id', $id)->update($update);
			if($res){
				$msg = '成功';
			}
		}else{
			$msg = '所选择的数据不能同时改为此状态';
		}
		return array('status'=>$res,'msg'=>$msg);
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
//			if($data['status'] != 4){
//				$data['dispose_after_number'] = 10;//测试数据
//			}
			$data['difference_before_number'] = $data['actual_number'] -$data['dispose_before_number'];//处理前差异数量=实物-处理前数量
			$data['difference_before_rate'] = $data['dispose_before_number']>0 ? sprintf("%.2f",abs($data['difference_before_number']/$data['dispose_before_number'])) : '0.00';//最初差异率 =（处理前差异数量/处理前数量）再取绝对值

			$data['difference_after_number'] = $data['actual_number'] - $data['dispose_after_number'];//处理后差异数量=实物-处理后数量
			$data['difference_after_rate'] = $data['dispose_after_number']>0 ? sprintf("%.2f",abs($data['difference_after_number']/$data['dispose_after_number'])) : '0.00';//处理后差异率 =（处理厚差异数量/处理后数量）再取绝对值
			//notaccount_number,未过账数量=处理前sap数量-处理前账面数量
			$data['notaccount_number'] =$data['dispose_before_number'] - $data['account_number'];
			$data['dispose_after_notaccount_number'] =$data['dispose_after_number'] - $data['dispose_after_account_number'];

			$data['difference_before_number'] = $data['actual_number'] - $data['account_number'];//处理前差异数量=实物-处理前账面数量
			$data['difference_before_rate'] = $data['account_number']>0 ? abs(sprintf("%.2f",abs($data['difference_before_number']/$data['account_number']))) : '0.00';//最初差异率 =（处理前差异数量/处理前账面数量）再取绝对值
			$data['difference_after_number'] = $data['actual_number'] - $data['dispose_after_account_number'];//处理后差异数量=实物-处理后账面数量
			$data['difference_after_rate'] = $data['dispose_after_account_number']>0 ? abs(sprintf("%.2f",abs($data['difference_after_number']/$data['dispose_after_account_number']))) : '0.00';//处理后差异率 =（处理厚差异数量/处理后数量）再取绝对值

			$statusArr = InventoryCycleCount::STATUS;
			$data['status_name'] = isset($statusArr[$data['status']]) ? $statusArr[$data['status']] : $data['status'];

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
						$skus = [];
						$_date = '';
						foreach($importData as $key => $data) {
							if ($key == 1 || empty($data['A']) || empty($data['B'])) {
								unset($importData[$key]);
								continue;
							}
							$date = date('Y-m-d',strtotime($data['A']));
							$_date = date('Ymd',strtotime($data['A']));
							$date_arr[$date] = $date;//导入的数据中，所有日期的数组
							//导入时，盘点日期需大于等于当前日期(YYYY-MM-DD)
//							if($date >= $current_date){
//								$date_arr[$date] = $date;//导入的数据中，所有日期的数组
//							}else{
//								$request->session()->flash('error_message','导入时，盘点日期需大于等于当前日期');
//								return redirect()->back()->withInput();
//							}
							$skus[] = array('MATNR'=>$data['B']);
						}
						$sap_inventory_data = $this->getSkuInventoryBySapApi($skus,$_date,$_date);
						//计算导入的数据中有几个日期数据，，每次导入，只允许导入一个日期
						$date_num = count($date_arr);
						if($date_num==1) {
							//循环处理插入数据
							foreach ($importData as $key => $data) {
								$date = date('Y-m-d',strtotime($data['A']));
								if(isset($sap_inventory_data[$data['B']])){
									foreach($sap_inventory_data[$data['B']] as $skey=>$sval){
										//MSTOCK1和MSTOCK2不含有负号(-)
										if($sval['WERKS'] && $sval['LGORT'] && $sval['MSTOCK1']>0 && $sval['MSTOCK2']>0 && !(strstr($sval['MSTOCK1'], '-')) && !(strstr($sval['MSTOCK2'], '-'))) {
											$insertData = array(
												'date' => $date,
												'sku' => $data['B'],
												'factory' => $sval['WERKS'],
												'location' => $sval['LGORT'],
											);
											if ($date < $current_date) {
												$insertData['dispose_before_number'] = $sval['MSTOCK2'];
											}
											$res = InventoryCycleCount::updateOrCreate($insertData, []);
											if ($date < $current_date && $res && isset($res['id'])) {//导入的日期小于当前日期，就把sap接口数据插入表中inventory_cycle_count_sapinventory
												$insertSapData = array(
													'inventory_cycle_count_id' => $res['id'],
													'date' => $res['date'],
													'sku' => $res['sku'],
													'factory' => $res['factory'],
													'location' => $res['location'],
													'type' => 'BEFORE',
													'BUKRS' => $sval['BUKRS'],
													'MENGE1' => intval($sval['MENGE1']),
													'MENGE2' => $sval['MENGE2'],
													'MSTOCK1' => $sval['MSTOCK1'],
													'MSTOCK2' => $sval['MSTOCK2'],
													'LABST' => $sval['LABST'],
													'LFGJA' => $sval['LFGJA'],
													'LFMON' => $sval['LFMON'],
												);
												InventoryCycleCountSapInventory::updateOrCreate($insertSapData, []);
											}
										}
									}
								}
								unset($importData[$key]);
							}
						}else{
							$request->session()->flash('error_message','每次导入，只允许导入一个日期');
							return redirect()->back()->withInput();
						}
						$request->session()->flash('success_message','Import Data Success!');
						 return redirect()->back()->withInput();
					}else{
						$request->session()->flash('error_message','Import Data Failed');
						 return redirect()->back()->withInput();
					}
				}else{
					$request->session()->flash('error_message','Import Data Failed,The file is too large');
					 return redirect()->back()->withInput();
				}
			}else{
				$request->session()->flash('error_message','Please Select Upload File');
				 return redirect()->back()->withInput();
			}
		}
		return redirect('/inventoryCycleCount');
	}
	/*
	 * 下载物流部需要的添加真实数量模板
	 */
	public function downloadActualNumber(Request $request)
	{
		$_data = InventoryCycleCount::where('status',1)->get()->toArray();
		$excel_name = "导入实物数量模板.xlsx";
		$excel_field = array('date'=>'盘点日期','sku'=>'SKU','factory'=>'工厂','location'=>'库位','actual_number'=>'实物数量');
		$this->downloadExcel($_data,$excel_field,$excel_name);
	}
	/*
	 * 下载财务部需要的添加账面数量模板
	 */
	public function downloadAccountNumber(Request $request)
	{
		$_data = InventoryCycleCount::where('status',1)->get()->toArray();
		$excel_name = "导入账面数量模板.xlsx";
		$excel_field = array('date'=>'盘点日期','sku'=>'SKU','factory'=>'工厂','location'=>'库位','account_number'=>'账面数量');
		$this->downloadExcel($_data,$excel_field,$excel_name);
	}
	/*
	 * 下载财务部需要添加的处理之后的账面数量模板
	 */
	public function downloadDisposeAfterAccountNumber(Request $request)
	{
		$_data = InventoryCycleCount::where('status',1)->get()->toArray();
		$excel_name = "导入处理后账面数量模板.xlsx";
		$excel_field = array('date'=>'盘点日期','sku'=>'SKU','factory'=>'工厂','location'=>'库位','dispose_after_account_number'=>'账面数量');
		$this->downloadExcel($_data,$excel_field,$excel_name);
	}
	/*
	 * 下载excel模板的公共方法
	 */
	public function downloadExcel($_data,$excel_field,$excel_name)
	{
		$headArray = array();
		foreach($excel_field as $field=>$name){
			$headArray[] = $name;
		}
		$arrayData[] = $headArray;
		foreach($_data as $key=>$val){
			$downloadData = array();
			foreach($excel_field as $field=>$name){
				$downloadData[] = $val[$field];
			}
			$arrayData[] = $downloadData;
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
			header('Content-Disposition: attachment;filename="'.$excel_name.'"');//告诉浏览器输出浏览器名称
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
		$this->importNumber($request,'actual_number');
		return redirect('/inventoryCycleCount');
	}
	/*
	 * 财务部导入账面数量数据
	 */
	public function importAccountNumber(Request $request)
	{
		if(!Auth::user()->can(['inventory-cycle-count-addAccountNumber'])) die('Permission denied -- inventory-cycle-count-addAccountNumber');
		$this->importNumber($request,'account_number');
		return redirect('/inventoryCycleCount');
	}
	/*
	 * 财务部导入处理后的账面数量数据
	 */
	public function importDisposeAfterAccountNumber(Request $request)
	{
		if(!Auth::user()->can(['inventory-cycle-count-addAccountNumber'])) die('Permission denied -- inventory-cycle-count-addAccountNumber');
		$this->importNumber($request,'dispose_after_account_number');
		return redirect('/inventoryCycleCount');
	}
	/*
	 * 导入数量
	 */
	public function importNumber($request,$number_type)
	{
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
							if($key==1 || empty($data['A']) || empty($data['B']) || empty($data['D']) || empty($data['E']) || empty($data['C'])){
								unset($importData[$key]);
								continue;
							}
							InventoryCycleCount::where('date',date('Y-m-d',strtotime($data['A'])))->where('sku',$data['B'])->where('factory',$data['C'])->where('location',$data['D'])->where('status',1)->update([$number_type=>$data['E']]);
							unset($importData[$key]);
						}

						return $request->session()->flash('success_message','Import Data Success!');
					}else{
						return $request->session()->flash('error_message','Import Data Failed');
					}
				}else{
					return $request->session()->flash('error_message','Import Data Failed,The file is too large');
				}
			}else{
				return $request->session()->flash('error_message','Please Select Upload File');
			}
		}
		return true;
	}

	/*
	 * 处理后的数量是从sap的接口中获取，当状态改为已确认的时候，处理后的数量才是从数据库里面取dispose_after_number这个字段，不然的话是实时的取sap的数据
	 */
	public function getDisposeAfterNumber($val,$sap_inventory_data)
	{
		$dispose_after_number = $val['dispose_after_number'];
		if($val['status'] != 4){
			//从sap接口中获取库存数据
			if(isset($sap_inventory_data[$val['sku'].'_'.$val['date']])){
				$_sap_inventory_data = $sap_inventory_data[$val['sku'].'_'.$val['date']];
			}else{
				$skus = array(array('MATNR'=>$val['sku']));
				$start_date = $end_date = date('Y-m-d',strtotime($val['date']));
				$sap_inventory_data[$val['sku'].'_'.$val['date']] = $_sap_inventory_data = $this->getSkuInventoryBySapApi($skus,$start_date,$end_date);
			}
			if(isset($_sap_inventory_data[$val['sku']][$val['factory'].'_'.$val['location']])){
				$dispose_after_number = $_sap_inventory_data[$val['sku']][$val['factory'].'_'.$val['location']]['MSTOCK2'];
			}
		}
		return ['dispose_after_number'=>$dispose_after_number,'sap_inventory_data'=>$sap_inventory_data];
	}

	public function getSapApiSign($array)
	{
		ksort($array);
		$authstr = "";
		foreach ($array as $k => $v) {
			$authstr = $authstr.$k.$v;
		}
		$authstr=$authstr.env("SAP_SECRET");
		$sign = strtoupper(sha1($authstr));
		return $sign;
	}
}