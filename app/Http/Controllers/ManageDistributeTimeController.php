<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;

class ManageDistributeTimeController extends Controller
{
	
	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}

	public function safetyStockDays(Request $req)
    {
        $usersIdName = $this->getUsersIdName();
        $skuStatuses = getSkuStatuses();

        if ($req->isMethod('GET')) {
            return view('manageDistributeTime.safetyStockDays', compact('usersIdName', 'skuStatuses'));
        }

        $sql = 'select any_value(t0.id) as id, t0.sku, t0.marketplace_id, any_value(t0.status) as status, any_value(t0.level) as level, any_value(t0.sap_seller_bg) as bg, any_value(t0.sap_seller_bu) as bu, any_value(t0.safe_quantity) as safe_quantity, any_value(t0.maintainer) as maintainer, any_value(t0.maintain_time) as maintain_time, any_value(t1.description) as description, any_value(t2.daily_sales) as daily_sales from sap_sku_sites as t0
                LEFT JOIN 
                (select sku, description from sap_skus) as t1
                ON t0.sku = t1.sku
                LEFT JOIN
                (select sum(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2) as daily_sales, marketplaceid, sku from asins group by sku, marketplaceid) as t2
                ON t0.sku = t2.sku and t0.marketplace_id = t2.marketplaceid 
                group by t0.sku, t0.marketplace_id 
                order by daily_sales desc;
                ';

        $data = DB::connection('amazon')->select($sql);
        $data = json_decode(json_encode($data),true);

        $siteCode = array_flip($this->getSiteMarketplaceId());
        foreach ($data as $key => $val) {
            $data[$key]['site'] = array_get($siteCode,$val['marketplace_id']);
            $data[$key]['status'] = array_get($skuStatuses,$val['status']);
            $data[$key]['daily_sales'] = $this->twoDecimal($val['daily_sales']);
            $data[$key]['maintainer'] = array_get($usersIdName,$val['maintainer']);
        }

        return compact('data');
    }

    public function updateSafetyStockDays(Request $req){
	    $updateData = array();
        $updateData['maintainer'] = intval(Auth::user()->id);
        $updateData['maintain_time'] = date('Y-m-d H:i:s');
        $updateData['safe_quantity'] = intval($req->input('safe_quantity'));

        $ids = $req->input('ids');
        $data = DB::connection('amazon')->table('sap_sku_sites')->whereIn('id', $ids)->select('sku','marketplace_id')->get();
        $data = json_decode(json_encode($data),true);

        DB::beginTransaction();
        foreach ($data as $k=>$v){
            DB::connection('amazon')->table('sap_sku_sites')->where('sku','=',$v['sku'])->where('marketplace_id','=',$v['marketplace_id'])->update($updateData);
        }
        DB::commit();
    }

    public function exportSafetyStockDays(Request $req){
        $ids = $req->input('ids');
        //有勾选
        if($ids){
            $sql = 'select min(t0.id) as id, t0.sku, t0.marketplace_id, any_value(t0.status) as status, any_value(t0.level) as level, any_value(t0.sap_seller_bg) as bg, any_value(t0.sap_seller_bu) as bu, any_value(t0.safe_quantity) as safe_quantity, any_value(t0.maintainer) as maintainer, any_value(t0.maintain_time) as maintain_time, any_value(t1.description) as description, any_value(t2.daily_sales) as daily_sales 
                    from 
                    (select * from sap_sku_sites where id in (' . $ids .')) as t0
                    LEFT JOIN 
                    (select sku, description from sap_skus) as t1
                    ON t0.sku = t1.sku
                    LEFT JOIN
                    (select sum(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2) as daily_sales, marketplaceid, sku from asins group by sku, marketplaceid) as t2
                    ON t0.sku = t2.sku and t0.marketplace_id = t2.marketplaceid 
                    group by t0.sku, t0.marketplace_id 
                    order by daily_sales desc;
                    ';
            $data = DB::connection('amazon')->select($sql);
            $data = json_decode(json_encode($data),true);
        }
        //没有勾选，导出全部数据
        else{
            $whereInsql = '1=1';
            if($req->input('site')) $whereInsql .= ' and marketplace_id="' . array_get($this->getSiteMarketplaceId(), $req->input('site')) . '"';
            if($req->input('status')) $whereInsql .= ' and status=' . $req->input('status');
            if($req->input('level')) $whereInsql .= ' and level="' . $req->input('site') . '"';
            if($req->input('bg')) $whereInsql .= ' and bg="' . $req->input('bg') . '"';
            if($req->input('bu')) $whereInsql .= ' and bu="' . $req->input('bu') . '"';
            if($req->input('maintainer')) $whereInsql .= ' and maintainer=' . $req->input('maintainer');
            $keyword = $req->input('keyword');
            if($keyword){
                //关键词只搜索sku,description两列。前端是搜索的所有列。
                $whereInsql .= ' and (sku like "%' . $keyword . '%" or description like "%' . $keyword . '%")';
            }
            $sql = 'select * from 
            (select any_value(t0.id) as id, t0.sku, t0.marketplace_id, any_value(t0.status) as status, any_value(t0.level) as level, any_value(t0.sap_seller_bg) as bg, any_value(t0.sap_seller_bu) as bu, any_value(t0.safe_quantity) as safe_quantity, any_value(t0.maintainer) as maintainer, any_value(t0.maintain_time) as maintain_time, any_value(t1.description) as description, any_value(t2.daily_sales) as daily_sales from sap_sku_sites as t0
            LEFT JOIN
        (select sku, description from sap_skus) as t1
            ON t0.sku = t1.sku
            LEFT JOIN
        (select sum(sales_4_weeks/28*0.5+sales_2_weeks/14*0.3+sales_1_weeks/7*0.2) as daily_sales, marketplaceid, sku from asins group by sku, marketplaceid) as t2
            ON t0.sku = t2.sku and t0.marketplace_id = t2.marketplaceid 
            group by t0.sku, t0.marketplace_id 
            order by daily_sales desc) as t3
            where ' . $whereInsql;

            $data = DB::connection('amazon')->select($sql);
            $data = json_decode(json_encode($data),true);
        }

        $siteCode = array_flip($this->getSiteMarketplaceId());
        $skuStatuses = getSkuStatuses();
        $usersIdName = $this->getUsersIdName();
        $arrayData = array();
        $headArray = array('SKU','站点','物料描述','SKU状态','SKU等级','BG','BU','加权日均','安全库存(天)','维护人','维护日期');
        $arrayData[] = $headArray;
        foreach ($data as $key=>$val){
            $arrayData[] = array(
                $val['sku'],
                array_get($siteCode,$val['marketplace_id']),
                $val['description'],
                array_get($skuStatuses,$val['status']),
                $val['level'],
                $val['bg'],
                $val['bu'],
                $this->twoDecimal($val['daily_sales']),
                $val['safe_quantity'],
                array_get($usersIdName,$val['maintainer']),
                $val['maintain_time']
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
            header('Content-Disposition: attachment;filename="Export_SafetyStockDays.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

	public function fba(Request $req)
	{
        $estimatedMonthToFba = $this->getEstimatedMonthToFba();
        $usersIdName = $this->getUsersIdName();

        if ($req->isMethod('GET')) {
            return view('manageDistributeTime.fba', compact('estimatedMonthToFba','usersIdName'));
        }

        $data = DB::connection('amazon')->table('fba_fc_transfer_time')->get();
        $data = json_decode(json_encode($data),true);

        foreach ($data as $key => $val) {
            $data[$key]['estimated_month_to_fba'] = array_get($estimatedMonthToFba, $val['estimated_month_to_fba']);
            $data[$key]['maintainer'] = array_get($usersIdName,$val['maintainer']);
        }

        return compact('data');

	}

	public function updateFba(Request $req){
        $updateData = array();
        $updateData['maintainer'] = intval(Auth::user()->id);
        $updateData['maintain_time'] = date('Y-m-d H:i:s');
        $updateData['transfer_time'] = intval($req->input('transfer_time'));

        $id = $req->input('id');
        DB::connection('amazon')->table('fba_fc_transfer_time')->where('id','=', $id)->update($updateData);
    }

    public function exportFba(Request $req){
        $data = DB::connection('amazon')->table('fba_fc_transfer_time')->get();
        $data = json_decode(json_encode($data),true);

        $estimatedMonthToFba = $this->getEstimatedMonthToFba();
        $usersIdName = $this->getUsersIdName();
        $arrayData = array();
        $headArray = array('站点','预计到FBA月份','FBA上架时效(天)','维护人','维护日期');
        $arrayData[] = $headArray;
        foreach ($data as $key=>$val){
            $arrayData[] = array(
                $val['site'],
                array_get($estimatedMonthToFba,$val['estimated_month_to_fba']),
                $val['transfer_time'],
                array_get($usersIdName,$val['maintainer']),
                $val['maintain_time']
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
            header('Content-Disposition: attachment;filename="Export_SafetyStockDays.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }

    public function getEstimatedMonthToFba(){
        return array('0'=>'7月', '1'=>'11月', '2'=>'12月', '3'=>'其他月份');
    }


	public function fbm(Request $req)
	{
	    $sapFactoryCodes = $this->getAllSapFactoryCodes();
        $usersIdName = $this->getUsersIdName();
        if ($req->isMethod('GET')) {
            return view('manageDistributeTime.fbm', compact('sapFactoryCodes','usersIdName'));
        }

        $data = DB::connection('amazon')->table('fbm_fba_transfer_time')->get();
        $data = json_decode(json_encode($data),true);

        foreach ($data as $key => $val) {
            $data[$key]['maintainer'] = array_get($usersIdName,$val['maintainer']);
        }

        return compact('data');
	}

    public function updateFbm(Request $req){
        $updateData = array();
        $updateData['maintainer'] = intval(Auth::user()->id);
        $updateData['maintain_time'] = date('Y-m-d H:i:s');
        $updateData['transfer_time'] = intval($req->input('transfer_time'));

        $id = $req->input('id');
        DB::connection('amazon')->table('fbm_fba_transfer_time')->where('id','=', $id)->update($updateData);
    }

    public function exportFbm(Request $req){
        $data = DB::connection('amazon')->table('fbm_fba_transfer_time')->get();
        $data = json_decode(json_encode($data),true);

        $usersIdName = $this->getUsersIdName();
        $arrayData = array();
        $headArray = array('站点','出库工厂','收货工厂','调拨时效(天) ','维护人','维护日期');
        $arrayData[] = $headArray;
        foreach ($data as $key=>$val){
            $arrayData[] = array(
                $val['site'],
                $val['sap_factory_code_outbound'],
                $val['sap_factory_code_inbound'],
                $val['transfer_time'],
                array_get($usersIdName,$val['maintainer']),
                $val['maintain_time']
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
            header('Content-Disposition: attachment;filename="Export_SafetyStockDays.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }


	public function initialTableFbmFbaTransferTime(){
        DB::beginTransaction();
        //插入所有非HK的数据
        $siteSapFactoryCodes = $this->getSiteSapFactoryCodes();
        foreach ($siteSapFactoryCodes as $k=>$v){
            foreach ($v as $k1=>$v1){
                foreach ($v as $k2=>$v2){
                    if($v2 !=$v1) {
                        $updateData = array();
                        $updateData['site'] = $k;
                        $updateData['sap_factory_code_outbound'] = $v1;
                        $updateData['sap_factory_code_inbound'] = $v2;
                        DB::connection('amazon')->table('fbm_fba_transfer_time')->insert($updateData);
                    }
                }
            }
        }

        //插入HK的数据
        $allSapFactoryCodes = $this->getAllSapFactoryCodes();
        $hk = array('HK01', 'HK03');
        foreach($hk as $v){
            foreach ($allSapFactoryCodes as $k2=>$v2){
                if(substr($v2, 0, 2) == 'HK') continue;
                $updateData = array();
                $updateData['site'] = 'HK';
                $updateData['sap_factory_code_outbound'] = $v;
                $updateData['sap_factory_code_inbound'] = $v2;
                DB::connection('amazon')->table('fbm_fba_transfer_time')->insert($updateData);
            }
        }

        DB::commit();
    }

    //从sap_sku_sites表中获取的工厂代码。另外香港站未做处理：marketplace_id：HK01SZ6  HK03CHK3
	public function getSiteSapFactoryCodes(){
        $data = array(
            'US'=>array('US01','US02','US04'),
            'CA'=>array('CA01','CA02'),
            'UK'=>array('UK01','UK02'),
            'FR'=>array('FR01','FR02'),
            'DE'=>array('GR01','GR02','GR04'),
            'IT'=>array('IT01','IT02'),
            'ES'=>array('ES01','ES02'),
            'JP'=>array('JP01','JP02'),
            //'HK'=>array('HK01','HK03')
        );
        return $data;

    }

    //包含HK01，HK03
    public function getAllSapFactoryCodes(){
	    return array('US01','US02','US04','CA01','CA02','UK01','UK02','FR01','FR02','GR01','GR02','GR04','IT01','IT02','ES01','ES02','JP01','JP02', 'HK01','HK03');
    }

	public function internationalTransportTime(Request $req){
        $factoryCodes = array();
        $factory_code = DB::connection('amazon')->table('international_transport_time')->orderBy('factory_code', 'asc')->distinct()->get(['factory_code']);
        foreach ($factory_code as $key=>$val){
            $factoryCodes[] = $val->factory_code;
        }
        $logisticsProviders = array();
        $logistics_provider = DB::connection('amazon')->table('international_transport_time')->orderBy('logistics_provider', 'asc')->distinct()->get(['logistics_provider']);
        foreach ($logistics_provider as $key=>$val){
            $logisticsProviders[] = $val->logistics_provider;
        }
        $transportModes = $this->getTransportModes();

        $regions = array();
        $region = DB::connection('amazon')->table('international_transport_time')->orderBy('region', 'asc')->distinct()->get(['region']);
        foreach ($region as $key=>$val){
            $regions[] = $val->region;
        }
        $usersIdName = $this->getUsersIdName();
        $isDefault = $this->getIsDefault();

        if ($req->isMethod('GET')) {
            return view('manageDistributeTime.internationalTransportTime', compact('factoryCodes','logisticsProviders','transportModes','regions','usersIdName'));
        }

        $data = DB::connection('amazon')->table('international_transport_time')->get();
        $data = json_decode(json_encode($data),true);

        foreach ($data as $key => $val) {
            $data[$key]['transport_mode'] = array_get($transportModes,$val['transport_mode_code']);
            $data[$key]['maintainer'] = array_get($usersIdName,$val['maintainer']);
            $data[$key]['is_default'] = array_get($isDefault,$val['is_default'], '');
        }
        return compact('data');

	}

	public function upload(Request $request){
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
                                if(array_get($data,'A')!='工厂' || array_get($data,'B')!='物流商' || array_get($data,'C')!='运输方式' || array_get($data,'D')!='地区'){
                                    die('Customer profile import template error');
                                }
                            }

                            //地区可以为空。excel表中单元格为空时，读取的值为''。
                            if($key>1 && array_get($data,'A') && array_get($data,'B') && array_get($data,'C')){
                                $exists = DB::connection('amazon')->table('international_transport_time')->where('factory_code',trim($data['A']))->where('logistics_provider',trim($data['B']))->where('transport_mode_code',trim($data['C']))->where('region',trim($data['D']))->first();
                                if($exists){
                                    $update_result = DB::connection('amazon')->table('international_transport_time')->where('factory_code',trim($data['A']))->where('logistics_provider',trim($data['B']))->where('transport_mode_code',trim($data['C']))->where('region',trim($data['D']))->update(array(
                                        'etd'=>intval(trim($data['E'])),
                                        'eta'=>intval(trim($data['F'])),
                                        'clearance_days'=>intval(trim($data['G'])),
                                        'delivery_days'=>intval(trim($data['H'])),
                                        'fbm_sign_in_days'=>intval(trim($data['I'])),
                                        'total_days'=>intval(trim($data['E']))+intval(trim($data['F']))+intval(trim($data['G']))+intval(trim($data['H']))+intval(trim($data['I'])),
                                        'maintainer'=>Auth::user()->id,
                                        'maintain_time'=>date('Y-m-d H:i:s')

                                    ));
                                    if ($update_result) {
                                        $successCount++;
                                    }
                                }else{
                                    $insert_result = DB::connection('amazon')->table('international_transport_time')->insert(
                                        array(
                                            'factory_code'=>trim($data['A']),
                                            'logistics_provider'=>trim($data['B']),
                                            'transport_mode_code'=>trim($data['C']),
                                            'region'=>trim($data['D']),
                                            'etd'=>intval(trim($data['E'])),
                                            'eta'=>intval(trim($data['F'])),
                                            'clearance_days'=>intval(trim($data['G'])),
                                            'delivery_days'=>intval(trim($data['H'])),
                                            'fbm_sign_in_days'=>intval(trim($data['I'])),
                                            'total_days'=>intval(trim($data['E']))+intval(trim($data['F']))+intval(trim($data['G']))+intval(trim($data['H']))+intval(trim($data['I'])),
                                            'maintainer'=>Auth::user()->id,
                                            'maintain_time'=>date('Y-m-d H:i:s')
                                        )
                                    );
                                    if ($insert_result) {
                                        $addCount++;
                                    } else {
                                        $errorCount++;
                                    }
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
        return redirect('manageDistributeTime/internationalTransportTime');
    }

    public function updateTransportTime(Request $req){
	    $id = $req->input('id');
        $row = DB::connection('amazon')->table('international_transport_time')->where('id','=', $id)->first();
        if(!$row) exit;
        $etd = intval($row->etd);
        $eta = intval($row->eta);
        $clearance_days = intval($row->clearance_days);
        $delivery_days = intval($row->delivery_days);
        $fbm_sign_in_days = intval($row->fbm_sign_in_days);
        $total_days = $etd + $eta + $clearance_days + $delivery_days + $fbm_sign_in_days;

        $updateData = array();
        if(isset($_POST['etd']) && $_POST['etd']){
            $updateData['etd'] = intval($_POST['etd']);
            $updateData['total_days'] = $total_days - $etd + intval($_POST['etd']);
        }else if(isset($_POST['eta']) && $_POST['eta']){
            $updateData['eta'] = intval($_POST['eta']);
            $updateData['total_days'] = $total_days - $eta + intval($_POST['eta']);
        }else if(isset($_POST['clearance_days']) && $_POST['clearance_days']){
            $updateData['clearance_days'] = intval($_POST['clearance_days']);
            $updateData['total_days'] = $total_days - $clearance_days + intval($_POST['clearance_days']);
        }else if(isset($_POST['delivery_days']) && $_POST['delivery_days']){
            $updateData['delivery_days'] = intval($_POST['delivery_days']);
            $updateData['total_days'] = $total_days - $delivery_days + intval($_POST['delivery_days']);
        }else if(isset($_POST['fbm_sign_in_days']) && $_POST['fbm_sign_in_days']){
            $updateData['fbm_sign_in_days'] = intval($_POST['fbm_sign_in_days']);
            $updateData['total_days'] = $total_days - $fbm_sign_in_days + intval($_POST['fbm_sign_in_days']);
        }else if(isset($_POST['is_default']) && $_POST['is_default']){
            $updateData['is_default'] = $_POST['is_default'];
        }else{
            //...
        }

        $updateData['maintainer'] = intval(Auth::user()->id);
        $updateData['maintain_time'] = date('Y-m-d H:i:s');
        DB::connection('amazon')->table('international_transport_time')->where('id','=', $id)->update($updateData);
    }

    public function batchUpdateTransportTime(Request $req){
        $ids = $req->input('ids');
        $input_id = $req->input('input_id');
        $input_value = intval($req->input('input_value'));
        DB::beginTransaction();
        for($i=0; $i<count($ids); $i++){
            $id = $ids[$i];
            $row = DB::connection('amazon')->table('international_transport_time')->where('id','=', $id)->first();
            //if(!$row) exit;
            $etd = intval($row->etd);
            $eta = intval($row->eta);
            $clearance_days = intval($row->clearance_days);
            $delivery_days = intval($row->delivery_days);
            $fbm_sign_in_days = intval($row->fbm_sign_in_days);
            $total_days = $etd + $eta + $clearance_days + $delivery_days + $fbm_sign_in_days;

            $updateData = array();
            $updateData[$input_id] = $input_value;
            //$$input_id: $etd, $eta, $clearance_days, $delivery_days, $fbm_sign_in_days
            $updateData['total_days'] = $total_days - $$input_id + $input_value;
            $updateData['maintainer'] = intval(Auth::user()->id);
            $updateData['maintain_time'] = date('Y-m-d H:i:s');
            DB::connection('amazon')->table('international_transport_time')->where('id','=', $id)->update($updateData);
        }

        DB::commit();

    }

    public function exportTransportTime(Request $req){
        $ids = $req->input('ids');
        //有勾选
        if($ids){
            $idArray = explode(',',$ids);
            $data = DB::connection('amazon')->table('international_transport_time')->whereIn('id', $idArray)->get();
            $data = json_decode(json_encode($data),true);
        }
        //没有勾选，导出全部数据
        else{
            $data = DB::connection('amazon')->table('international_transport_time');
            if($req->input('factory_code')){
                $data = $data->where('factory_code', '=', $req->input('factory_code'));
            }
            if($req->input('logistics_provider')){
                $data = $data->where('logistics_provider', '=', $req->input('logistics_provider'));
            }
            if($req->input('transport_mode_code')){
                $data = $data->where('transport_mode_code', '=', $req->input('transport_mode_code'));
            }
            if($req->input('region')){
                $data = $data->where('region', '=', $req->input('region'));
            }
            if($req->input('maintainer')){
                $data = $data->where('maintainer', '=', $req->input('maintainer'));
            }
            $data = $data->get();
            $data = json_decode(json_encode($data),true);
        }

        $usersIdName = $this->getUsersIdName();
        $transportModes = $this->getTransportModes();
        $isDefault = $this->getIsDefault();
        $arrayData = array();
        $headArray = array('工厂','物流商','运输方式代码','运输方式','地区','ETD','ETA','清关日期','派送日期','FBM签收日期','总时效','维护人','维护时间','是否默认');
        $arrayData[] = $headArray;
        foreach ($data as $key=>$val){
            $arrayData[] = array(
                $val['factory_code'],
                $val['logistics_provider'],
                $val['transport_mode_code'],
                array_get($transportModes,$val['transport_mode_code']),
                $val['region'],
                $val['etd'],
                $val['eta'],
                $val['clearance_days'],
                $val['delivery_days'],
                $val['fbm_sign_in_days'],
                $val['total_days'],
                array_get($usersIdName,$val['maintainer']),
                $val['maintain_time'],
                array_get($isDefault,$val['is_default']),
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
            header('Content-Disposition: attachment;filename="Export_International_Transport_Time.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();
    }


    //保留2位小数
    public function twoDecimal($num){
        return sprintf("%.2f",$num);
    }

    public function getTransportModes(){
        return array(
            '10'=>'空运',
            '20'=>'海运',
            '30'=>'空运&海运',
            '40'=>'快递',
            '50'=>'铁路',
            '60'=>'陆路',
            '70'=>'海运+卡车',
            '80'=>'海运＋快递',
            '90'=>'美森快船'
        );
    }

    //在functions.php中 英国 为'GB'，为了与前端的UK保持一致，这里另外写了个方法，改成'UK'， 前端会用UK来筛选。
    function getSiteMarketplaceId(){
        return array(
            'US' =>'ATVPDKIKX0DER',
            'CA' =>'A2EUQ1WTGCTBG2',
            'MX' =>'A1AM78C64UM0Y8',
            'UK' =>'A1F83G8C2ARO7P',
            'DE' =>'A1PA6795UKMFR9',
            'FR' =>'A13V1IB3VIYZZH',
            'IT' =>'APJ6JRA9NG5V4',
            'ES' =>'A1RKKUPIHCS9HS',
            'JP' =>'A1VC38T7YXB528'
        );
    }

    public function getIsDefault(){
        return array('1'=>'Y','2'=>'N');
    }

}
 