<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Asin;
use Illuminate\Support\Facades\Session;

use App\User;
use App\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;


class RoiController extends Controller
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

    public function index()
    {
        $submit_date_from=date('Y-m-d',strtotime('-90 days'));
        $submit_date_to=date('Y-m-d');
        $users = $this->getUsers();
        $sites = $this->getSites();

        $limit_bg = $limit_bu = $limit_sap_seller_id = $limit_review_user_id='';
        $sumwhere = '1=1';
        if (Auth::user()->seller_rules) {
            $rules = explode("-", Auth::user()->seller_rules);
            if (array_get($rules, 0) != '*'){
                $limit_bg = array_get($rules, 0);
            }else{
            }
            if (array_get($rules, 1) != '*'){
                $limit_bu = array_get($rules, 1);
            }
        } elseif (Auth::user()->sap_seller_id) {
            $limit_sap_seller_id = Auth::user()->sap_seller_id;
        } else {
            $limit_review_user_id = Auth::user()->id;
        }
        if($limit_bg){
            $sumwhere.=" and bg='$limit_bg'";
        }
        if($limit_bu){
            $sumwhere.=" and bu='$limit_bu'";
        }
        if($limit_sap_seller_id){
            $sumwhere.=" and sap_seller_id='$limit_sap_seller_id'";
        }
        if($limit_review_user_id){
            $sumwhere.=" and review_user_id='$limit_review_user_id'";
        }
        $teams = DB::select("select bg,bu from asin where $sumwhere group by bg,bu ORDER BY BG ASC,BU ASC");

        return view('roi/index',compact('submit_date_from','submit_date_to','users', 'sites','teams'));
    }

    public function get(Request $request)
    {
        $order_column = $request->input('order.0.column','1');
        if($order_column == 1){
            $orderby = 'id';
        }else if($order_column == 12){
            $orderby = 'created_at';
        }else if($order_column == 14){
            $orderby = 'updated_at';
        }

        $sort = $request->input('order.0.dir','desc');
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&',$search));
        //搜索时间范围
        $submit_date_from = isset($search['submit_date_from']) && $search['submit_date_from'] ? $search['submit_date_from'] : date('Y-m-d',strtotime('- 90 days'));
        $submit_date_to = isset($search['submit_date_to']) && $search['submit_date_to'] ? $search['submit_date_to'] : date('Y-m-d');

        $data = DB::connection('amazon')->table('roi');
        //如果连接了asin表，where的字段要加上表名。例如site：where('roi.site', $search['site'])
        $data = $data->where('roi.created_at','>=',$submit_date_from.' 00:00:00')->where('roi.created_at','<=',$submit_date_to.' 23:59:59');

        //todo: bgbu.


        if(isset($search['user_id']) && $search['user_id']) {
            $user_id = $search['user_id'];
            $data = $data->where(function ($query) use ($user_id) {
                $query->where('creator', $user_id)
                    ->orWhere('updated_by', $user_id);
            });
        }
        if(isset($search['site']) && $search['site']){
            $data = $data->where('site', $search['site']);
        }
        if(isset($search['archived_status']) && $search['archived_status'] != '-1'){
            $data = $data->where('archived_status', $search['archived_status']);
        }
        //目前keyword只查询产品名称
        if(isset($search['keyword']) && $search['keyword']){
            $data = $data->where('product_name','like','%'.$search['keyword'].'%');
        }

        $iTotalRecords = $data->get()->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        //如果连接了asin表，后面的where的字段要加上表名。例如site：where('roi.site', $search['site'])
        $lists =  $data->orderBy($orderby,$sort)->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $lists = array_map('get_object_vars', $lists);

        $users= $this->getUsers();

        foreach ($lists as $key=>$list){

            $lists[$key]['roi_id'] = $list['id'];
            $lists[$key]['product_name_sku'] = '<div>'.$list['product_name'].'</div>'.'<div>'.$list['sku'].'</div>';
            $lists[$key]['total_sales_volume'] = '<div style="text-align: right">'.$list['total_sales_volume'].'</div>';
            $lists[$key]['total_sales_amount'] = '<div style="text-align: right">'.round($list['total_sales_amount']).'</div>';
            $lists[$key]['capital_turnover'] = '<div style="text-align: right">'.$this->twoDecimal($list['capital_turnover']).'</div>';
            $lists[$key]['project_profitability'] = '<div style="text-align: right">'.$this->toPercentage($list['project_profitability']).'</div>';
            $lists[$key]['roi'] = '<div style="text-align: right">'.$this->toPercentage($list['roi']).'</div>';
            $lists[$key]['return_amount'] = '<div style="text-align: right">'.$this->twoDecimal($list['return_amount']/10000).'</div>';
            $lists[$key]['creator'] = array_get($users,$list['creator']);
            $lists[$key]['created_at'] = date('Y-m-d',strtotime($list['created_at']));
            $lists[$key]['updated_by'] = array_get($users,$list['updated_by']);
            $lists[$key]['updated_at'] = date('Y-m-d',strtotime($list['updated_at']));
            $lists[$key]['archived_status'] = $list['archived_status'] == 0 ? '未归档' : '已归档';
            $edit_url = url('roi/'.$list['id']);
            $show_url = url('roi/'.$list['id'].'/edit');
            $lists[$key]['action'] = '<ul class="nav navbar-nav"><li><a href="#" class="dropdown-toggle" style="height:10px; vertical-align:middle; padding-top:0px;" data-toggle="dropdown" role="button">...</a><ul class="dropdown-menu" role="menu" style="background-color: #cccccc"><li><a href="' . $edit_url . '" >查看详情</a></li><li><a href="' . $show_url . '">编辑</a></li></ul></li></ul>';
//        <div>
//            <ul class="nav navbar-nav">
//                <li>
//                    <a href="#" class="dropdown-toggle" style="height:10px; vertical-align:middle; padding-top:0px;" data-toggle="dropdown" role="button">...</a>
//                    <ul class="dropdown-menu" role="menu" style="background-color: #cccccc">
//                        <li><a href="{{ $edit_url }}">查看详情</a></li>
//                        <li><a href="{{ $show_url }}">编辑</a></li>
//                    </ul>
//                </li>
//            </ul>
//        </div>
        }

        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;
        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function create(Request $request)
    {

        $sites = $this->getSites();
        $billingPeriods = $this->getBillingPeriods();
        $transportModes = $this->getTransportModes();
        return view('roi/add',compact('sites','billingPeriods','transportModes'));
    }

    public function export(Request $request)
    {
        //搜索时间范围
        $submit_date_from = isset($_GET['date_from']) && $_GET['date_from'] ? $_GET['date_from'] : date('Y-m-d',strtotime('- 90 days'));
        $submit_date_to = isset($_GET['date_to']) && $_GET['date_to'] ? $_GET['date_to'] : date('Y-m-d');

        $data = DB::connection('amazon')->table('roi');
        $data = $data->where('roi.created_at','>=',$submit_date_from.' 00:00:00')->where('roi.created_at','<=',$submit_date_to.' 23:59:59')->get()->toArray();
        $data = array_map('get_object_vars', $data);;

        $users= $this->getUsers();

        $arrayData = array();
        $headArray = array('ID','产品名称/SKU','站点','预计上线时间','预计年销量','预计年销售额','资金周转次数','项目利润率','投资回报率ROI(%)','投资回报额(万元)','创建人','创建日期','最新修改人','最新修改日期','归档状态');
        $arrayData[] = $headArray;
        foreach ($data as $key=>$val){
            $arrayData[] = array(
                $val['id'],
                $val['product_name']. PHP_EOL . $val['sku'],
                $val['site'],
                $val['estimated_launch_time'],
                $val['total_sales_volume'],
                round($val['total_sales_amount']),
                $this->twoDecimal($val['capital_turnover']),
                $this->toPercentage($val['project_profitability']),
                $this->toPercentage($val['roi']),
                $this->twoDecimal($val['return_amount']/10000),
                array_get($users,$val['creator']),
                date('Y-m-d',strtotime($val['created_at'])),
                array_get($users,$val['updated_by']),
                date('Y-m-d',strtotime($val['updated_at'])),
                $val['archived_status'] == 0 ? '未归档' : '已归档'
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
            header('Content-Disposition: attachment;filename="Export_ROI.xlsx"');//告诉浏览器输出浏览器名称
            header('Cache-Control: max-age=0');//禁止缓存
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        die();

    }

    public function edit(Request $request, $id)
    {
        $sites = $this->getSites();
        $billingPeriods = $this->getBillingPeriods();
        $transportModes = $this->getTransportModes();
        $roi = $this->getCurrentRoi($id);
        return view('roi/edit',compact('sites','billingPeriods','transportModes', 'roi'));
    }

    public function getCurrentRoi($id){
        $roi = DB::connection('amazon')->table('roi')->where('id', '=', $id)->get()->toArray();
        $roi = array_map('get_object_vars', $roi)[0];
        for($i=1; $i<=12;$i++){
            $roi['price_fc_month_'.$i] = $this->twoDecimal($roi['price_fc_month_'.$i]);
            $roi['price_rmb_month_'.$i] = $this->twoDecimal($roi['price_fc_month_'.$i] * $roi['currency_rate']);
            $roi['sales_amount_month_'.$i] = round($roi['price_fc_month_'.$i] * $roi['currency_rate'] * $roi['volume_month_'.$i]);
            $roi['promo_rate_month_'.$i] = $this->toPercentage($roi['promo_rate_month_'.$i]);
            $roi['exception_rate_month_'.$i] = $this->toPercentage($roi['exception_rate_month_'.$i]);
        }
        $roi['average_price_fc'] = $this->twoDecimal($roi['average_price_fc']);
        $roi['average_price_rmb'] = $this->twoDecimal($roi['average_price_rmb']);
        $roi['total_sales_amount'] = round($roi['total_sales_amount']);
        $roi['average_promo_rate'] = $this->toPercentage($roi['average_promo_rate']);
        $roi['average_exception_rate'] = $this->toPercentage($roi['average_exception_rate']);
        $roi['commission_rate'] = $this->toPercentage($roi['commission_rate']);
        $roi['unit_operating_fee'] = $this->twoDecimal($roi['unit_operating_fee']);
        $roi['transport_unit_price'] = $this->twoDecimal($roi['transport_unit_price']);
        $roi['transport_days'] = $this->twoDecimal($roi['transport_days']);
        $roi['tariff_rate'] = $this->toPercentage($roi['tariff_rate']);
        $roi['weight_per_pcs'] = $this->twoDecimal($roi['weight_per_pcs']);
        $roi['volume_per_pcs'] = $this->twoDecimal($roi['volume_per_pcs']);
        $roi['purchase_price'] = $this->twoDecimal($roi['purchase_price']);

        $roi['id_fee'] = $this->twoDecimal($roi['id_fee']);
        $roi['mold_fee'] = $this->twoDecimal($roi['mold_fee']);
        $roi['prototype_fee'] = $this->twoDecimal($roi['prototype_fee']);
        $roi['other_fixed_cost'] = $this->twoDecimal($roi['other_fixed_cost']);
        $roi['royalty_fee'] = $this->twoDecimal($roi['royalty_fee']);
        $roi['certification_fee'] = $this->twoDecimal($roi['certification_fee']);

        $roi['estimated_labor_cost'] = $this->twoDecimal($roi['estimated_labor_cost']);
        $roi['business_trip_expenses'] = $this->twoDecimal($roi['business_trip_expenses']);
        $roi['other_project_cost'] = $this->twoDecimal($roi['other_project_cost']);

        $roi['price_floor'] = $this->twoDecimal($roi['price_floor']);
        $roi['inventory_turnover_days'] = $this->twoDecimal($roi['inventory_turnover_days']);
        $roi['project_profitability'] = $this->toPercentage($roi['project_profitability']);
        $roi['marginal_profit_per_pcs'] = $this->twoDecimal($roi['marginal_profit_per_pcs']);
        $roi['capital_turnover'] = $this->twoDecimal($roi['capital_turnover']);
        $roi['roi'] = $this->toPercentage($roi['roi']);
        $roi['return_amount'] = $this->twoDecimal($roi['return_amount']/10000);

        return $roi;
    }

    public function show(Request $request, $id)
    {
        $sites = $this->getSites();
        $billingPeriods = $this->getBillingPeriods();
        $transportModes = $this->getTransportModes();
        $roi_id = $id;
        $roi = $this->getCurrentRoi($id);
        $transport_mode_int = $roi['transport_mode'];
        $roi['transport_mode'] = $transportModes[$transport_mode_int];
        $transport_unit = '<span>元/m<sup>3</sup></span>';
        if($transport_mode_int == 1 || $transport_mode_int == 2) {
            $transport_unit = '<span>元/KG></span>';
        }
        $roi['transport_unit_price'] = $roi['transport_unit_price'].$transport_unit;
        $roi['billing_period_type'] = $billingPeriods[$roi['billing_period_type']]['name'];
        $estimated_launch_time = $roi['estimated_launch_time'];
        if($estimated_launch_time){
            $day = substr($estimated_launch_time,-2);
            //$day <= 15
            $year_month = substr($estimated_launch_time, 0,7);
            if($day > 15){
                $year_month = date("Y-m", strtotime("+1 months", strtotime($year_month)));
            }
            for($i=1; $i<=12; $i++){
                $roi['month_'.$i] = $year_month;
            }
        }

        return view('roi/show',compact('sites','billingPeriods','transportModes', 'roi_id', 'roi'));
    }

    public function store(Request $request){
        $updateData = $this->getUpdateData($request);
        $updateDBData = $updateData['updateDBData'];

        $user_id = Auth::user()->id;
        $updateDBData['creator'] = $user_id;
        $updateDBData['created_at'] = date('Y-m-d H:i:s');
        $updateDBData['updated_by'] = $user_id;
        $updateDBData['updated_at'] = $updateDBData['created_at'];
        $updateDBData['edit_history'] = $user_id.','.$updateDBData['created_at'];

        DB::beginTransaction();
        if(!DB::connection('amazon')->table('roi')->insert($updateDBData)){
            $request->session()->flash('error_message','Save Failed.');
            return redirect()->back()->withInput();
        }else{
            return redirect('roi');
        }
        DB::commit();
    }

    public function update(Request $request, $id){

    }

    public function updateRecord(Request $request){

        $updateData = $this->getUpdateData($request);
        $updateDBData = $updateData['updateDBData'];

        $user_id = Auth::user()->id;
        $updateDBData['updated_by'] = $user_id;
        $updateDBData['updated_at'] = date('Y-m-d H:i:s');
        //edit页面表单有隐藏元素roi_id, add页面没有
        $roi_id = $updateDBData['roi_id'];
        unset($updateDBData['roi_id']);

        $edit_history_array = DB::connection('amazon')->table('roi')->where('id', '=', $roi_id)->pluck('edit_history');
        $edit_history = $edit_history_array[0];
        $updateDBData['edit_history'] = $edit_history.';'.$user_id.','.$updateDBData['updated_at'];

        DB::beginTransaction();
        if(!DB::connection('amazon')->table('roi')->where('id', '=', $roi_id)->update($updateDBData)){
            $request->session()->flash('error_message','Update Failed.');
            return redirect()->back()->withInput();
        }else{
            return redirect('roi');
        }
        DB::commit();
    }


    public function analyse(Request $request){
        $updateData = $this->getUpdateData($request);
        $updateAjaxData = $updateData['updateAjaxData'];

        return json_encode(array('updateAjaxData' => $updateAjaxData));
    }

    public function getUpdateData(Request $request){
        //测试用数据： site选择JP; $currency_rate = 0.065767; $early_investment = 3000;
        $currency_rates = $this->getCurrencyRates();
        $site = $request->input('site','US');
        $currency_rate = $currency_rates[$site];

        $volume_month_array = $price_fc_month_array = $price_rmb_month_array = $sales_amount_month_array = array();
        $total_sales_volume = $total_sales_amount = 0;
        $total_promo_amount = $total_exception_amount = 0;

        for($i=1; $i<=12; $i++){
            //单个月销量
            $volume_month_array[$i] = $request->input('volume_month_'.$i);
            //单个月售价外币
            $price_fc_month_array[$i] = $request->input('price_fc_month_'.$i);
            //单个月售价RMB
            $price_rmb_month_array[$i] = $price_fc_month_array[$i] * $currency_rate;
            $sales_amount_month_precise = $price_fc_month_array[$i] * $currency_rate * $volume_month_array[$i];
            //单个月销售金额(取整)
            $sales_amount_month_array[$i] = round($sales_amount_month_precise);
            //总销量
            $total_sales_volume = $total_sales_volume + $volume_month_array[$i];
            //总销售金额(销售收入)
            $total_sales_amount = $total_sales_amount + $sales_amount_month_precise;
            //单个月推广费(百分数转成小数)
            $promo_amount_month_array[$i] = ((float)$request->input('promo_rate_month_'.$i))/100 * $sales_amount_month_array[$i];
            //总推广费(营销费用)
            $total_promo_amount = $total_promo_amount + $promo_amount_month_array[$i];
            //单个月异常费(百分数转成小数)
            $exception_amount_month_array[$i] = ((float)$request->input('exception_rate_month_'.$i))/100 * $sales_amount_month_array[$i];
            //总异常费(退款金额)
            $total_exception_amount = $total_exception_amount + $exception_amount_month_array[$i];
        }

        //平均售价RMB
        $average_price_rmb = $total_sales_amount / $total_sales_volume;
        //平均售价外币
        $average_price_fc = $average_price_rmb / $currency_rate;
        //平均推广率
        $average_promo_rate = $total_promo_amount / $total_sales_amount;
        //平均异常率
        $average_exception_rate = $total_exception_amount / $total_sales_amount;

        /*
         计算得出结果
        */
        //净收入
        $net_income =  $total_sales_amount - $total_exception_amount;
        //采购成本
        $purchase_cost = $request->input('purchase_price') * $total_sales_volume;

        //--物流费用相关--
        //产品实重
        $product_weight = $request->input('weight_per_pcs') * $total_sales_volume;
        //单pcs体积
        $volume_per_pcs = $request->input('volume_per_pcs');
        //体积
        $product_volume = $volume_per_pcs * $total_sales_volume/1000000;
        //运输单价
        $transport_unit_price = $request->input('transport_unit_price');
        //运输方式, 0-海运，1-空运，2-快递
        $transport_mode = $request->input('transport_mode');
        //物流费用
        $transport_cost = 0;
        if($transport_mode == 0){
            $transport_cost = $transport_unit_price * $product_volume * 1.2;
        }else if($transport_mode == 1){
            $transport_cost = max($product_volume * 1000/6 * 1.2, $product_weight) * $transport_unit_price;
        }else if($transport_mode == 2){
            $transport_cost = max($product_volume * 1000/5 * 1.2, $product_weight) * $transport_unit_price;
        }

        //--相关税费--
        //关税税率(百分数转成小数)
        $tariff_rate = ((float)$request->input('tariff_rate'))/100;
        $tariff_amount = $purchase_cost * 0.4 * $tariff_rate;
        //VAT率
        $vat_rate = ((float)$this->getVatRates()[$site])/100;
        //VAT
        $vat_amount = $net_income / (1+$vat_rate) * $vat_rate;

        //--平台费用--
        //平台佣金率(百分数转成小数)
        $commission_rate = ((float)$request->input('commission_rate'))/100;
        //平台佣金
        $commission_amount = $commission_rate * $total_sales_amount - $total_exception_amount * $commission_rate * 0.8;
        //操作费
        $operating_fee = $request->input('unit_operating_fee') * $currency_rate * $total_sales_volume;

        //--仓储费相关--
        //moq
        $moq = $request->input('moq');
        //运输天数
        $transport_days = $request->input('transport_days');
        $unit_strorage_fee = $this->getUnitStorageFee()[$site];
        //仓储费
        $storage_fee = (($moq/2+(7+$transport_days)*$total_sales_volume/365)*$volume_per_pcs/1000000*($unit_strorage_fee[0]*9+$unit_strorage_fee[1]*3))*$currency_rate;

        //库存周转天数
        $inventory_turnover_days = $transport_days+7+$moq/(2*$total_sales_volume/365);
        //供应商账期类型
        $billing_period_type = $request->input('billing_period_type');
        //供应商账期
        $billing_days = $this->getBillingPeriods()[$billing_period_type]['days'];
        //资金周转次数
        $capital_turnover = 365/($inventory_turnover_days-$billing_days+14);
        //投入资金
        $invest_capital = ((($inventory_turnover_days-$billing_days+14)*$total_sales_volume/365)*($purchase_cost+$transport_cost+$tariff_amount)/$total_sales_volume);
        //资金占用成本
        $capital_cost = $invest_capital * 0.18;
        //变动成本费用小计
        $variable_cost =  $purchase_cost + $transport_cost + ($tariff_amount + $vat_amount) + ($commission_amount + $operating_fee) + $total_promo_amount + $storage_fee + $capital_cost;
        //边际贡献总额
        $total_marginal_contribution = $net_income - $variable_cost;
        //单位边际贡献(单PCS边际利润)
        $marginal_profit_per_pcs = $total_marginal_contribution / $total_sales_volume;
        //边际贡献率
        $marginal_profit_rate = $total_marginal_contribution / $total_sales_amount;

        //--固定成本--
        //固定费用小计
        $total_fixed_cost = $request->input('id_fee') + $request->input('mold_fee') + $request->input('prototype_fee') + $request->input('other_fixed_cost') + $request->input('royalty_fee') + $request->input('certification_fee');
        //盈亏临界点（销售数量）
        $breakeven_point_sales_volume = $total_fixed_cost / $marginal_profit_per_pcs;
        //预计投资回收期(月)
        $estimated_payback_period = 0;
        $cumulate_volume = 0;
        if($breakeven_point_sales_volume <= $total_sales_volume){
            for($i=1; $i<=12;$i++){
                $cumulate_volume += $volume_month_array[$i];
                if($cumulate_volume >= $breakeven_point_sales_volume){
                    $estimated_payback_period = $i;
                    break;
                }
            }
        }

        //前期开发投入
        $early_investment = $request->input('estimated_labor_cost') + $request->input('business_trip_expenses') + $request->input('other_project_cost');
        //投资回报额
        $return_amount = $total_marginal_contribution - $total_fixed_cost/2 - $early_investment/2;
        //投资回报率
        $roi = $return_amount / $invest_capital;
        //项目利润率
        $project_profitability = $return_amount / $total_sales_amount;
        //底限价格
        $numerator = ($total_fixed_cost/(2*$total_sales_volume)+($storage_fee+$tariff_amount+$transport_cost+$operating_fee+$purchase_cost+$capital_cost)/$total_sales_volume)*(1+$vat_rate)/$currency_rate;
        $denominator = (1-$commission_amount/$total_sales_amount-$total_promo_amount/$total_sales_amount-$total_exception_amount/$total_sales_amount)*(1+$vat_rate)-(1-$total_exception_amount/$total_sales_amount)*$vat_rate;
        $price_floor = $numerator / $denominator;

        //点击"分析"按钮时，返回的数组，用于ajax异步更新页面数据
        $updateAjaxData = array();
        $updateAjaxData['total_sales_volume'] = $total_sales_volume;
        $updateAjaxData['average_price_fc'] = $this->twoDecimal($average_price_fc);
        $updateAjaxData['average_price_rmb'] = $this->twoDecimal($average_price_rmb);
        $updateAjaxData['total_sales_amount'] = round($total_sales_amount);
        $updateAjaxData['average_promo_rate'] = $this->toPercentage($average_promo_rate);
        $updateAjaxData['average_exception_rate'] = $this->toPercentage($average_exception_rate);
        for($i=1; $i<=12; $i++) {
            $updateAjaxData['price_rmb_month_' . $i] = $this->twoDecimal($price_rmb_month_array[$i]);
            $updateAjaxData['sales_amount_month_' . $i] = $sales_amount_month_array[$i];
        }
        $updateAjaxData['price_floor'] = $this->twoDecimal($price_floor);
        $updateAjaxData['inventory_turnover_days'] = $this->twoDecimal($inventory_turnover_days);
        $updateAjaxData['project_profitability'] = $this->toPercentage($project_profitability);
        $updateAjaxData['marginal_profit_per_pcs'] = $this->twoDecimal($marginal_profit_per_pcs);
        $updateAjaxData['estimated_payback_period'] = $estimated_payback_period > 0 ? $estimated_payback_period : '12个月以后';
        $updateAjaxData['capital_turnover'] = $this->twoDecimal($capital_turnover);
        $updateAjaxData['roi'] = $this->toPercentage($roi);
        $updateAjaxData['return_amount'] = $this->twoDecimal($return_amount/10000);


        //点保存按钮时，插入或者更新数据到数据库
        //以下百分数要转换成小数保存：单个月推广率，单个月异常率，平均推广率，平均异常率，平台佣金率，关税税率，项目利润率，投资回报率
        //$request->all();获取的参数还包含 _token, _method
        $updateDBData = $request->all();
        unset($updateDBData['_token']);
        unset($updateDBData['_method']);

        for($i=1; $i<=12; $i++){
            $updateDBData['promo_rate_month_'.$i] = ((float)$request->input('promo_rate_month_'.$i))/100;
            $updateDBData['exception_rate_month_'.$i] = ((float)$request->input('exception_rate_month_'.$i))/100;
        }
        $updateDBData['average_promo_rate'] = $average_promo_rate;
        $updateDBData['average_exception_rate'] = $average_exception_rate;
        $updateDBData['commission_rate'] = $commission_rate;
        $updateDBData['tariff_rate'] = $tariff_rate;
        $updateDBData['project_profitability'] = $project_profitability;
        $updateDBData['currency_rate'] = $currency_rate;

        $updateDBData['total_sales_volume'] = $total_sales_volume;
        $updateDBData['total_sales_amount'] = $total_sales_amount;
        $updateDBData['average_price_rmb'] = $average_price_rmb;
        $updateDBData['average_price_fc'] = $average_price_fc;
        $updateDBData['average_promo_rate'] = $average_promo_rate;
        $updateDBData['average_exception_rate'] = $average_exception_rate;
        $updateDBData['total_fixed_cost'] = $total_fixed_cost;
        $updateDBData['early_investment'] = $early_investment;
        $updateDBData['price_floor'] = $price_floor;
        $updateDBData['inventory_turnover_days'] = $inventory_turnover_days;
        $updateDBData['project_profitability'] = $project_profitability;
        $updateDBData['marginal_profit_per_pcs'] = $marginal_profit_per_pcs;
        $updateDBData['estimated_payback_period'] = $estimated_payback_period;
        $updateDBData['capital_turnover'] = $capital_turnover;
        $updateDBData['roi'] = $roi;
        $updateDBData['return_amount'] = $return_amount;

        return array('updateAjaxData'=>$updateAjaxData, 'updateDBData'=>$updateDBData);

    }

    //站点
    public function getSites(){
        $data = array('US', 'CA', 'UK', 'DE', 'JP','FR','ES','IT');
        return $data;
    }

    //仓储费
    public function getUnitStorageFee(){
        $data = array(
            //array(0,1). 0: 淡季仓储费unit_low_season_storage_fee; 1: 旺季仓储费unit_peak_season_storage_fee
            'US' => array(24.37, 84.76),
            'CA' => array(20.00, 28.00),
            'UK' => array(22.95, 32.14),
            'DE' => array(26.00, 36.00),
            'JP' => array(5070.00, 9000.00),
            'FR' => array(26.00, 36.00),
            'ES' => array(26.00, 36.00),
            'IT' => array(26.00, 36.00)
        );

        return $data;
    }


    //VAT税率表
    public function getVatRates(){
        $data = array(
            'FR'=>'20%',
            'DE'=>'19%',
            'IT'=>'22%',
            'ES'=>'21%',
            'UK'=>'20%',
            'US'=>'0%',
            'JP'=>'0%',
            'CA'=>'0%'
        );

        return $data;
    }

    //账期表
    public function getBillingPeriods(){
        //数据库中对应的字段billing_period_type，存储值为0，1，2...
        $data = array(
            '0' => array('name'=>'预付20%订金，尾款开3个月银行承兑汇票（见票发货）','days'=>78),
            '1' => array('name'=>'预付50%订金，50%尾款货到付款','days'=>-15),
            '2' => array('name'=>'货到开票（3个月银行承兑）','days'=>105),
            '3' => array('name'=>'预付20%订金，余款月结45天','days'=>42),
            '4' => array('name'=>'当月结15天，开三个月银行承兑','days'=>120),
            '5' => array('name'=>'预付30%订金，尾款款到发货','days'=>-9),
            '6' => array('name'=>'预付30%订金，尾款货到付款','days'=>-9),
            '7' => array('name'=>'质检合格后货到三个工作日内付款','days'=>3),
            '8' => array('name'=>'预付30%订金，尾款质检合格收到发票后3个工作日内支付','days'=>3.6),
            '9' => array('name'=>'预付30%订金，尾款当月结15天并开三个月银行承兑','days'=>75),
            '10' => array('name'=>'月结30天','days'=>45),
            '11' => array('name'=>'预付20%订金，尾款货到付款','days'=>-6),
            '12' => array('name'=>'预付20%订金，余款月结30天','days'=>30),
            '13' => array('name'=>'预付10%订金，余款月结45天','days'=>51),
            '14' => array('name'=>'月结60天+3个月银行承兑','days'=>165),
            '15' => array('name'=>'月结45天+3个月银行承兑','days'=>150),
            '16' => array('name'=>'预付50%订金，50%尾款款到发货','days'=>-15),
            '17' => array('name'=>'月结30天+保理80天','days'=>125),
            '18' => array('name'=>'预付20%订金，尾款款到发货','days'=>-6),
            '19' => array('name'=>'月结90天','days'=>105),
            '20' => array('name'=>'月结15天','days'=>30),
            '21' => array('name'=>'月结30天+3个月银行承兑','days'=>135),
            '22' => array('name'=>'预付40%订金，60%尾款款到发货','days'=>-12),
            '23' => array('name'=>'月结45天','days'=>60),
            '24' => array('name'=>'款到发货','days'=>0),
            '25' => array('name'=>'月结60天','days'=>75),
            '26' => array('name'=>'预付20%订金，尾款货到后7个工作日内支付（3个月银行承兑）','days'=>78)
        );

        return $data;
    }

    //从currency_rates表中获取各站点汇率
    public function getCurrencyRates(){
        $data = DB::connection('amazon')->table('currency_rates')->pluck('rate','currency');
        $currency_rates = array();
        $currency_rates['US'] = $data['USD'];
        $currency_rates['CA'] = $data['CAD'];
        $currency_rates['UK'] = $data['GBP'];
        $currency_rates['DE'] = $data['EUR'];
        $currency_rates['JP'] = $data['JPY'];
        $currency_rates['FR'] = $data['EUR'];
        $currency_rates['ES'] = $data['EUR'];
        $currency_rates['IT'] = $data['EUR'];

        return $currency_rates;
    }

    public function getTransportModes(){
        $data = array('0'=>'海运', '1'=>'空运', '2'=>'快递');
        return $data;
    }

    //保留2位小数
    public function twoDecimal($num){
        return sprintf("%.2f",$num);
    }

    //数值转成百分数，保留2位小数
    public function toPercentage($num){
        return sprintf("%.2f",$num*100).'%';
    }

    public function getUsers(){
        //目前在职的销售人员
        return User::where('locked', '=', 0)->where('sap_seller_id','>',0)->pluck('name','id');
    }




}