<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\SapRfcRequest;
use App\Models\NonCtg;
use DB;
use App\User;
use App\Accounts;
use App\Models\TrackLog;
use App\Models\Ctg;
use App\Exceptions\DataInputException;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NonctgController extends Controller
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
		parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		if(!Auth::user()->can(['non-ctg-show'])) die('Permission denied -- non-ctg-show');
        $users = array();
        $userRows = DB::table('users')->select('id', 'name')->get();
        foreach ($userRows as $row) {
            $users[$row->id] = $row->name;
        }

        $bgs = $this->queryFields('SELECT DISTINCT bg FROM asin');
        $bus = $this->queryFields('SELECT DISTINCT bu FROM asin');

        $statusKeyVal = getNonCtgStatusKeyVal();
        return view('nonctg/index', ['status' => $statusKeyVal, 'users' => $users, 'bgs' => $bgs, 'bus' => $bus,]);
    }
    //列表的ajax请求数据,post请求
    public function get(Request $request)
    {
        $statusKeyVal = getNonCtgStatusKeyVal();
        $where = $this->dtWhere(
            $request,
            [
                'email' => 't1.email',
                'name' => 't1.name',
                'order_id' => 't1.amazon_order_id',
                'asins' => 't1.asin',
                'item_group'=>'t3.item_group',
                'item_no'=>'t3.item_no',
                'from' => 't1.from',
                'sales' => 't3.seller',
            ],
            [],
            [
                // WHERE IN
                'processor' => 't1.processor',
                'status' => 't1.status',
                // WHERE FIND_IN_SET
                'bg' => 's:t3.bg',
                'bu' => 's:t3.bu',
            ],
            'date'
        );

		$where .= $this->getAsinWhere('t3.bg','t3.bu','t1.processor','non-ctg-show-all');

        $orderby = $this->dtOrderBy($request);
        $limit = $this->dtLimit($request);

        $sql = "SELECT SQL_CALC_FOUND_ROWS t1.id,t1.date,t1.email,t1.email,t1.name,t1.amazon_order_id as order_id,t1.asin,t3.item_group,t3.item_no,t1.from,t1.status,t2.name AS processor,t3.seller,t3.bg,t3.bu,t1.saleschannel as saleschannel,t3.site as site,t1.sellersku as sellersku,rsg_requests.amazon_order_id as rsg_orderid    
        FROM non_ctg t1 
	  	Left join rsg_requests on rsg_requests.amazon_order_id = t1.amazon_order_id 
        LEFT JOIN users t2 ON t2.id = t1.processor
        LEFT JOIN asin t3 ON t1.asin = t3.asin and t3.site = CONCAT('www.',t1.saleschannel) and t1.sellersku = t3.sellersku  
        where $where
        ORDER BY $orderby
        LIMIT $limit";

        $data = $this->queryRows($sql);

        $recordsTotal = $recordsFiltered = $this->queryOne('SELECT FOUND_ROWS()');

        foreach($data as $key=>$val){
            $data[$key]['status'] = isset($statusKeyVal[$val['status']]) ? $statusKeyVal[$val['status']] : $val['status'];
            $data[$key]['site'] = empty($val['site']) ? 'www.'.$val['saleschannel'] : $val['site'];
			$data[$key]['join_rsg'] = $val['rsg_orderid'] ? 'YES' : 'NO';//是否有参加RSG活动
        }
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    /*
     * nonctg功能的指派任务，可以把某个任务指派给其他成员
     */
    public function batchAssignTask(Request $req) {
		if(!Auth::user()->can(['non-ctg-update'])) die('Permission denied -- non-ctg-update');
        if (empty($req->input('ctgRows'))) return [true, ''];

        $processor = (int)$req->input('processor');

        $user = User::findOrFail($processor);

        NonCtg::where(function ($where) use ($req) {
            foreach ($req->input('ctgRows') as $row) {
                // WHERE GROUP，传二维数组就可以
                $where->orWhere([
                    ['id', $row[0]]
                ]);
            }
        })->update(compact('processor'));
        return [true, $user->name];
    }

    /**
     * @throws DataInputException
     * NON-CTG点击process出现的页面操作
     */
    public function process(Request $req) {
		
        $wheres = [
            ['id', $req->input('id')],
            ['amazon_order_id', $req->input('order_id')]
        ];

        $dataRow = NonCtg::selectRaw('*')->where($wheres)->limit(1)->first();

        if (empty($dataRow)) throw new DataInputException('nonctg not found');
        $id = $recordId = $req->input('id');

        if ($req->isMethod('GET')) {
			if(!Auth::user()->can(['non-ctg-show'])) die('Permission denied -- non-ctg-show');
            $sap = new SapRfcRequest();

            $order = SapRfcRequest::sapOrderDataTranslate($sap->getOrder(['orderId' => $req->input('order_id')]));

            $order['SellerName'] = Accounts::where('account_sellerid', $order['SellerId'])->first()->account_name ?? 'No Match';


            $emails = DB::table('sendbox')->where('to_address', $dataRow['email'])->orderBy('date', 'desc')->get();
            $emails = json_decode(json_encode($emails), true); // todo


            $userRows = DB::table('users')->select('id', 'name')->get();

            $users = array();
            foreach ($userRows as $row) {
                $users[$row->id] = $row->name;
            }

            //得到跟进记录(toArray转换成数组)
            $trackLogData = TrackLog::where('type',0)->where('record_id',$id)->orderBy('created_at', 'desc')->get()->toArray();
            foreach($trackLogData as $k=>$v){
                $trackLogData[$k]['note'] = nl2br($v['note']);
            }

            return view('nonctg.process', ['ctgRow' => $dataRow, 'trackLogData'=>$trackLogData,'users' => $users, 'order' => $order, 'emails' => $emails,'status'=>getNonCtgStatusKeyVal()]);

        }

		if(!Auth::user()->can(['non-ctg-update'])) die('Permission denied -- non-ctg-update');
        // process操作页面点击保存
        $updates = [];
        $status = 0;
        if ($req->has('status')) {
            $updates['status'] = $status = $req->input('status');
        }
        if ($req->has('processor')) {
            $updates['processor'] = $dataRow['processor'] = (int)$req->input('processor');
        }
        if ($req->has('gift_sku')) {
            $updates['gift_sku'] = $dataRow['gift_sku'] = $req->input('gift_sku');
        }
        $dataRow->where($wheres)->update($updates);//更新non-ctg表数据内容
        $trackNote = isset($_REQUEST['track_note']) ? $_REQUEST['track_note'] : '';
        $way = $req->has('way') ? $req->has('way') : '0';//$way=1为页面普通提交，0为ajax提交
        //状态为有意向的时候，把此条non-ctg数据转移到ctg表中
        $ctgres = 0;
        if($status==1){
            if(empty($dataRow['gift_sku'])){$dataRow['gift_sku'] = 0;}
            $ctgData = array('processor' => $dataRow['processor'], 'order_id' => $dataRow['amazon_order_id'], 'gift_sku' => $dataRow['gift_sku'], 'name' => $dataRow['name'],'email' => $dataRow['email'], 'note'=>'','nonctg_id'=>$id,'channel'=>3);
            //添加该订单的时候还要添加相对应的订单信息到ctg_order和ctg_order_item表中
            $Ctg = new Ctg();
            $ctgres = $Ctg->add($ctgData);
            if($ctgres){
                //添加到ctg数据后，需要把non-ctg的该条数据删除
                NonCtg::where('id',$id)->delete();
                // TrackLog::where(array('type'=>0,'record_id'=>$id))->update(array('type'=>1,'record_id'=>$ctgid));
            }
        }
        //填写了跟进内容的时候,保存到跟进记录日志表，然后列表显示该跟进记录
        if($trackNote) {
            $data = array('type' => 0, 'record_id' => $id, 'email' => $dataRow['email'], 'note' => $trackNote);
            $TrackLog = new TrackLog();
            $TrackLog->add($data);
        }
        //把此条数据non-ctg移到了ctg表后，跳转到non-ctg列表
        if($way==1){
            if($ctgres){
                return redirect('/nonctg');
            }else{
                return redirect('/nonctg/process?order_id='.$dataRow['amazon_order_id'].'&id='.$id);
            }
        }
        return [true];
    }

	/*
	 * nonctg模块的下载功能
	 */
	public function export(Request $request)
	{
		$statusKeyVal = getNonCtgStatusKeyVal();

		$arrayData = array();
		$headArray[] = 'Date';
		$headArray[] = 'Email';
		$headArray[] = 'Name';
		$headArray[] = 'Order Id';
		$headArray[] = 'Asin';
		$headArray[] = 'SalesChannel';
		$headArray[] = 'Sellersku';
		$headArray[] = 'Item Group';
		$headArray[] = 'Item No';
		$headArray[] = 'From';
		$headArray[] = 'Status';
		$headArray[] = 'BG';
		$headArray[] = 'BU';
		$headArray[] = 'Sales';
		$headArray[] = 'Processor';

		$arrayData[] = $headArray;

		$where = ' where 1 = 1 '.$this->getAsinWhere('t3.bg','t3.bu','t1.processor','non-ctg-show-all');

		$sql = "SELECT SQL_CALC_FOUND_ROWS t1.id,t1.date,t1.email,t1.email,t1.name,t1.amazon_order_id as order_id,t1.asin,t3.item_group,t3.item_no,t1.from,t1.status,t2.name AS processor,t3.seller,t3.bg,t3.bu,t1.saleschannel as saleschannel,t3.site as site,t1.sellersku as sellersku 
        FROM non_ctg t1
        LEFT JOIN users t2 ON t2.id = t1.processor
        LEFT JOIN asin t3 ON t1.asin = t3.asin and t3.site = CONCAT('www.',t1.saleschannel) and t1.sellersku = t3.sellersku  
        {$where} ";

		$data = $this->queryRows($sql);
		foreach ($data as $key=>$val){
			$arrayData[] = array(
				$val['date'],
				$val['email'],
				$val['name'],
				$val['order_id'],
				$val['asin'],
				$val['saleschannel'],
				$val['sellersku'],
				$val['item_group'],
				$val['item_no'],
				$val['from'],
				isset($statusKeyVal[$val['status']]) ? $statusKeyVal[$val['status']] : $val['status'],
				$val['bg'],
				$val['bu'],
				$val['seller'],
				$val['processor'],
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
			header('Content-Disposition: attachment;filename="Export_Non-CTG.xlsx"');//告诉浏览器输出浏览器名称
			header('Cache-Control: max-age=0');//禁止缓存
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
		return compact('data', 'recordsTotal', 'recordsFiltered');
	}

}
