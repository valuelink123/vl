<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
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

        return view('roi/index',compact('submit_date_from','submit_date_to','users', 'sites'));
    }

    public function get(Request $request)
    {
        //name current user's id as $currentUserId
        $currentUserId = Auth::user()->id;
        $isUserAdmin = $this->isAdmin($currentUserId);
        $isUserProductDirector = $this->isProductDirector($currentUserId);
        $isUserPlanner = $this->isPlanner($currentUserId);

        $visibleRoiIds = array();
        if($isUserPlanner){
            $visibleRoiIds = DB::connection('amazon')->table('roi')->where('archived_status', 1)->pluck('id');
        }
        else{
            $data = DB::connection('amazon')->table('roi')->get();
            $data = json_decode(json_encode($data),true);
            foreach ($data as $k=>$v){
                $roiId = $v['id'];
                $creatorId = $v['creator'];
                $collaborators = $v['collaborators'];
                $isCreator = $currentUserId == $creatorId;
                if($isCreator || $isUserAdmin || $isUserProductDirector){
                    $visibleRoiIds[] = $roiId;
                    continue;
                }
                $isUserDirectLeader = $this->isDirectLeader($creatorId, $currentUserId);
                $isUserExtendedCollaborators = $this->isExtendedCollaborators($collaborators, $currentUserId);
                if($isUserDirectLeader || $isUserExtendedCollaborators){
                    $visibleRoiIds[] = $roiId;
                }
            }
        }

        $data = DB::connection('amazon')->table('roi')->whereIn('id', $visibleRoiIds);

        $order_column = $request->input('order.0.column','15');
        if($order_column == 13){
            $orderby = 'created_at';
        }else if($order_column == 15){
            $orderby = 'updated_at';
        }

        $sort = $request->input('order.0.dir','desc');
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $search = $this->getSearchData(explode('&',$search));
        //搜索时间范围
        $submit_date_from = isset($search['submit_date_from']) && $search['submit_date_from'] ? $search['submit_date_from'] : date('Y-m-d',strtotime('- 90 days'));
        $submit_date_to = isset($search['submit_date_to']) && $search['submit_date_to'] ? $search['submit_date_to'] : date('Y-m-d');

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
        //
        if(isset($search['keyword']) && $search['keyword']){
            $keyword = $search['keyword'];
            $data = $data->where(function ($query) use ($keyword) {
                $query->where('product_name','like','%'.$keyword.'%')
                    ->orWhere('sku','like','%'.$keyword.'%')
                    ->orWhere('project_code','like','%'.$keyword.'%');
            });
        }

        $iTotalRecords = $data->get()->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        //如果连接了asin表，后面的where的字段要加上表名。例如site：where('roi.site', $search['site'])
        $lists =  $data->orderBy($orderby,$sort)->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        $lists = json_decode(json_encode($lists),true);

        $users= $this->getUsers();

        foreach ($lists as $key=>$list){
            $roiId = $list['id'];
            $show_url = url('roi/'.$roiId);
            $edit_url = url('roi/'.$roiId.'/edit');
            $copy_url = url('roi_copy?id='.$roiId);
            $delete_url = url('roi_delete?id='.$roiId);

            $lists[$key]['product_name'] = '<a href="' . $show_url . '" target="_blank">'.$list['product_name'].'</a>';
            $lists[$key]['project_code'] = '<a href="' . $list['new_product_planning_process'] . '" target="_blank">'.$list['project_code'].'</a>';
            $lists[$key]['total_sales_volume'] = '<div style="text-align: right">'.$list['total_sales_volume'].'</div>';
            $lists[$key]['total_sales_amount'] = '<div style="text-align: right">'.round($list['total_sales_amount']).'</div>';
            $lists[$key]['capital_turnover'] = $list['capital_turnover'] < 0 ? '<div style="text-align: right; font-size: 16px">∞</div>' : '<div style="text-align: right">'.$this->toPercentage($list['capital_turnover']).'</div>';
            $lists[$key]['project_profitability'] = '<div style="text-align: right">'.$this->toPercentage($list['project_profitability']).'</div>';
            $lists[$key]['roi'] = $list['roi'] < 0 ? '<div style="text-align: right; font-size: 16px">∞</div>' : '<div style="text-align: right">'.$this->toPercentage($list['roi']).'</div>';
            $lists[$key]['return_amount'] = '<div style="text-align: right">'.$this->twoDecimal($list['return_amount']/10000).'</div>';
            $lists[$key]['creator'] = array_get($users,$list['creator']);
            $lists[$key]['created_at'] = date('Y-m-d',strtotime($list['created_at']));
            $lists[$key]['updated_by'] = array_get($users,$list['updated_by']);
            $lists[$key]['updated_at'] = date('Y-m-d',strtotime($list['updated_at']));
            $lists[$key]['archived_status'] = $list['archived_status'] == 0 ? '未审核' : '已审核';
            $show_item = '<li><a style="text-align:center" href="' . $show_url . '" >查看详情</a></li>';
            $edit_item = $list['archived_status'] == 0 ? '<li><a style="text-align:center" href="' . $edit_url . '">编辑</a></li>' : '';
            $copy_item = $isUserPlanner ? '' : '<li><a style="text-align:center" href="' . $copy_url .'">复制</a></li>';

            //Admin,产品总监 有归档状态的编辑权限
            $canArchive = false;
            if($isUserAdmin || $isUserProductDirector){
                $canArchive = true;
            }
            $data_sku = '';
            if($list['sku']){
                $data_sku = $list['sku'];
            }
            $data_new_product_planning_process = '';
            if($list['new_product_planning_process']){
                $data_new_product_planning_process = $list['new_product_planning_process'];
            }
            $archived_item = $canArchive ? '<li><a style="text-align:center" href="#" data-toggle="modal" data-target="#archived-modal" data-roi_id="' . $list['id'] . '" data-launch_time="' .$list['estimated_launch_time'] .'" data-sku="' . $data_sku . '" data-new_product_planning_process="' . $data_new_product_planning_process . '">审核</a></li>' : '';

            //创建人及其上级和管理员（Admin）有删除权限，其他 不可删除
            $canDelete = false;
            //已归档的状态是不可删除的
            if($list['archived_status'] == 0){
                $creatorId = $list['creator'];
                $isCreator = $currentUserId == $creatorId;
                $isUserDirectLeader = $this->isDirectLeader($creatorId, $currentUserId);
                if($isCreator || $isUserAdmin || $isUserDirectLeader){
                    $canDelete = true;
                }
            }
            $delete_item = $canDelete ? '<li><a style="text-align:center" onclick="return confirm(\'确定删除?\');" href="' . $delete_url . '">删除</a></li>' : '';

            $lists[$key]['action'] = '<ul class="nav navbar-nav"><li><a href="#" class="dropdown-toggle" style="height:10px; vertical-align:middle; padding-top:0px;" data-toggle="dropdown" role="button">...</a><ul class="dropdown-menu" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-50px, 20px, 0px); min-width: 88px;" role="menu" style="color: #62c0cc8a">' . $show_item . $edit_item . $archived_item . $copy_item . $delete_item . '</ul></li></ul>';
        }

        $recordsTotal = $iTotalRecords;
        $recordsFiltered = $iTotalRecords;
        $data = $lists;

        return compact('data', 'recordsTotal', 'recordsFiltered');
    }

    public function archive(Request $request){
        //Admin,产品总监 有归档状态的编辑权限
        $currentUserId = Auth::user()->id;
        $isUserAdmin = $this->isAdmin($currentUserId);
        $isUserProductDirector = $this->isProductDirector($currentUserId);
        $canArchive = false;
        if($isUserAdmin || $isUserProductDirector){
            $canArchive = true;
        }
        if(!$canArchive) die('Permission denied');

        $roi_id = $request->input("roi_id");
        if($roi_id){
            $updateDBData = array();
            if($request->input("sku")) $updateDBData['sku'] = $request->input("sku");
            if($request->input("launch_time")) $updateDBData['estimated_launch_time'] = $request->input("launch_time");
            if($request->input("new_product_planning_process")) $updateDBData['new_product_planning_process'] = $request->input("new_product_planning_process");
            $updateDBData['archived_status'] = 1;

            DB::beginTransaction();
            if(!DB::connection('amazon')->table('roi')->where('id', '=', $roi_id)->update($updateDBData)){
                $request->session()->flash('error_message','Update Failed.');
                return redirect()->back()->withInput();
            }else{
                return redirect('roi');
            }
            DB::commit();
        }
    }

    public function create(Request $request)
    {
        $sites = $this->getSites();
        $availableUsers = $this->getAvailableUsers();
        $billingPeriods = $this->getBillingPeriods();
        $transportModes = $this->getTransportModes();
        $currency_rates = $this->getCurrencyRates();

        return view('roi/add',compact('sites', 'availableUsers', 'billingPeriods','transportModes','currency_rates'));
    }

    public function export(Request $request)
    {
        //name current user's id as $currentUserId
        $currentUserId = Auth::user()->id;
        $isUserAdmin = $this->isAdmin($currentUserId);
        $isUserProductDirector = $this->isProductDirector($currentUserId);
        $data = DB::connection('amazon')->table('roi')->get();
        $data = json_decode(json_encode($data),true);
        $visibleRoiIds = array();
        foreach ($data as $k=>$v){
            $roiId = $v['id'];
            $creatorId = $v['creator'];
            $collaborators = $v['collaborators'];
            $isCreator = $currentUserId == $creatorId;
            if($isCreator || $isUserAdmin || $isUserProductDirector){
                $visibleRoiIds[] = $roiId;
                continue;
            }
            $isUserDirectLeader = $this->isDirectLeader($creatorId, $currentUserId);
            $isUserExtendedCollaborators = $this->isExtendedCollaborators($collaborators, $currentUserId);
            if($isUserDirectLeader || $isUserExtendedCollaborators){
                $visibleRoiIds[] = $roiId;
            }
        }

        $data = DB::connection('amazon')->table('roi')->whereIn('id', $visibleRoiIds);

        //搜索时间范围
        $submit_date_from = isset($_GET['date_from']) && $_GET['date_from'] ? $_GET['date_from'] : date('Y-m-d',strtotime('- 90 days'));
        $submit_date_to = isset($_GET['date_to']) && $_GET['date_to'] ? $_GET['date_to'] : date('Y-m-d');
        $data = $data->where('roi.created_at','>=',$submit_date_from.' 00:00:00')->where('roi.created_at','<=',$submit_date_to.' 23:59:59')->orderBy('updated_at', 'desc')->get()->toArray();
        $data = json_decode(json_encode($data),true);

        $users= $this->getUsers();

        $arrayData = array();
        $headArray = array('产品名称','项目编号','SKU','站点','预计上线时间','预计年销量','预计年销售额','资金周转次数','项目利润率','投资回报率ROI(%)','投资回报额(万元)','创建人','创建日期','最新修改人','最新修改日期','审核状态');
        $arrayData[] = $headArray;
        foreach ($data as $key=>$val){
            $arrayData[] = array(
                $val['product_name'],
                $val['project_code'],
                $val['sku'],
                $val['site'],
                $val['estimated_launch_time'],
                $val['total_sales_volume'],
                round($val['total_sales_amount']),
                $val['capital_turnover'] < 0 ? '∞' : $this->twoDecimal($val['capital_turnover']),
                $this->toPercentage($val['project_profitability']),
                $val['roi'] < 0 ? '∞' : $this->toPercentage($val['roi']),
                $this->twoDecimal($val['return_amount']/10000),
                array_get($users,$val['creator']),
                date('Y-m-d',strtotime($val['created_at'])),
                array_get($users,$val['updated_by']),
                date('Y-m-d',strtotime($val['updated_at'])),
                $val['archived_status'] == 0 ? '未审核' : '已审核'
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

    public function exportShowPage(Request $request){
        $id = $_GET['id'];
        //name current user's id as $currentUserId
        $currentUserId = Auth::user()->id;
        $isUserAdmin = $this->isAdmin($currentUserId);
        $isUserProductDirector = $this->isProductDirector($currentUserId);
        $data = DB::connection('amazon')->table('roi')->where('id', '=', $id)->first();
        $data = json_decode(json_encode($data),true);
        if(!$data) exit;
        $visible = false;
        $creatorId = $data['creator'];
        $collaborators = $data['collaborators'];
        $isCreator = $currentUserId == $creatorId;
        if($isCreator || $isUserAdmin || $isUserProductDirector){
            $visible = true;
        }
        $isUserDirectLeader = $this->isDirectLeader($creatorId, $currentUserId);
        $isUserExtendedCollaborators = $this->isExtendedCollaborators($collaborators, $currentUserId);
        if($isUserDirectLeader || $isUserExtendedCollaborators){
            $visible = true;
        }
        if(!$visible) die('Permission denied');

        $roi = $this->getCurrentRoi($id);
        $roi = $this->showPageDataFormat($roi);

        $output = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.
            '<style type="text/css">
                #sales_table{
                    width:1501px;
                    border: 1px solid #dddddd;
                }
                #sales_table th{
                    text-align: left;
                    height: 34px;
                    padding-left: 12px;
                    font-size:12px;
                }
                #sales_table td{
                    text-align: left;
                    padding-left: 10px;
                    height: 34px;
                    font-size:12px;
                }
                .result_div{
                    width: 1501px;
                    border: 0px solid #dddddd;
                    background-color:#F5F7FA;
                    padding: 20px;
                }
                #result_table, #params_cost_table{
                    width:1481px;
                    /*border：1px;*/
                    border: 0px solid #dddddd;
                }
                #result_table td{
                    text-align: left;
                    height: 25px;
                }
                td input{
                    width: 75px;
                    height:22px;
                    border: 0px solid #eeeeee;
                }
                .first_row_params input,select{
                    width: 205px;
                    height:26px;
                }
                .common-btn{
                    background-color: #63C5D1;
                    color: #ffffff;
                    font-size: 14px;
                    text-align: center;
                    width: 60px;
                    height: 30px;
                    border-radius: 5px !important;
                }
                .disabled-btn{
                    background-color: #62c0cc8a;
                    color: #ffffffb3;
                    font-size: 14px;
                    text-align: center;
                    width: 60px;
                    height: 30px;
                    border-radius: 5px !important;
                }
                .edit-btn{
                    color: #63C5D1;
                    font-size: 14px;
                    text-align: center;
                    width: 60px;
                    height: 30px;
                    border-radius: 5px !important;
                    border: 1px solid #63C5D1;
                }
                .edit-disabled-btn{
                    color: #62c0cc8a;
                    font-size: 14px;
                    text-align: center;
                    width: 60px;
                    height: 30px;
                    border-radius: 5px !important;
                    border: 1px solid #62c0cc8a;
                }
        
                #archived-modal{
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%,-50%);
                    /*min-width:80%;!*这个比例可以自己按需调节*!*/
                    overflow: visible;
                    bottom: inherit;
                    right: inherit;
                }
                #edit-history-modal{
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%,-50%);
                    /*min-width:80%;!*这个比例可以自己按需调节*!*/
                    overflow: visible;
                    bottom: inherit;
                    right: inherit;
                }
                .highlight_color{
                    color:#63C5D1;
                }
                .grey_color{
                    color:#909399;
                }
                input{
                    border: 1px solid #dddddd;
                }
        
            </style>
                <div style="height: 25px;"></div>
                <div>
                    <div style="font-size: 18px; float: left; font-weight: bold">投入产出表</div>
                </div>
                <div style="clear:both"></div>
                <div style="height: 30px;"></div>
                <div>
                    <span style="padding-right: 20px;">产品名称: <span class="highlight_text">' . $roi['product_name'] . '</span></span>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span style="padding-right: 20px">站点: <span class="highlight_text">' . $roi['site'] . '</span></span>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span style="padding-right: 20px">预计上线时间: <span class="highlight_text">' . $roi['estimated_launch_time'] . '</span></span>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span style="padding-right: 20px">SKU: <span class="highlight_text">' . $roi['sku'] . '</span></span>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span style="padding-right: 20px">项目编号: <span class="highlight_text">' . $roi['project_code'] . '</span></span>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span style="padding-right: 20px"><a href="' . $roi['new_product_planning_process'] . '" target="_blank">新品规划流程</a></span>
                </div>
                <div style="clear:both"></div>
                <div style="height: 15px;"></div>
                <div style="font-size:12px; color: #cccccc;">说明：下表的月份是从上市日起的当月起按第一个月算，以12个月为一个周期</div>
                <div style="height: 5px;"></div>
                <div>
                    <table id="sales_table" border="1" cellspacing="0" cellpadding="0">
                        <tr>
                            <th colspan="2" width="200px" style="text-align: center">项目/时间</th>
                            <th width="100px">' . $roi['month_1'] . '</th>
                            <th width="100px">' . $roi['month_2'] . '</th>
                            <th width="100px">' . $roi['month_3'] . '</th>
                            <th width="100px">' . $roi['month_4'] . '</th>
                            <th width="100px">' . $roi['month_5'] . '</th>
                            <th width="100px">' . $roi['month_6'] . '</th>
                            <th width="100px">' . $roi['month_7'] . '</th>
                            <th width="100px">' . $roi['month_8'] . '</th>
                            <th width="100px">' . $roi['month_9'] . '</th>
                            <th width="100px">' . $roi['month_10'] . '</th>
                            <th width="100px">' . $roi['month_11'] . '</th>
                            <th width="100px">' . $roi['month_12'] . '</th>
                            <th width="100px">合计</th>
                        </tr>
                        <tr>
                            <td rowspan="4" style="padding-left: 0px; text-align: center">销售预测</td>
                            <td style="padding-left: 10px; text-align: left">预计销量</td>
                            <td><span>' . $roi['volume_month_1'] . '</span></td>
                            <td><span>' . $roi['volume_month_2'] . '</span></td>
                            <td><span>' . $roi['volume_month_3'] . '</span></td>
                            <td><span>' . $roi['volume_month_4'] . '</span></td>
                            <td><span>' . $roi['volume_month_5'] . '</span></td>
                            <td><span>' . $roi['volume_month_6'] . '</span></td>
                            <td><span>' . $roi['volume_month_7'] . '</span></td>
                            <td><span>' . $roi['volume_month_8'] . '</span></td>
                            <td><span>' . $roi['volume_month_9'] . '</span></td>
                            <td><span>' . $roi['volume_month_10'] . '</span></td>
                            <td><span>' . $roi['volume_month_11'] . '</span></td>
                            <td><span>' . $roi['volume_month_12'] . '</span></td>
                            <td><span>' . $roi['total_sales_volume'] . '</span></td>
                        </tr>
                        <tr>
                            <td>售价（外币）</td>
                            <td><span>' . $roi['price_fc_month_1'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_2'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_3'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_4'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_5'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_6'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_7'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_8'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_9'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_10'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_11'] .'</span></td>
                            <td><span>' . $roi['price_fc_month_12'] .'</span></td>
                            <td><span>' . $roi['average_price_fc'] .'</span></td>
                        </tr>
                        <tr>
                            <td>售价RMB</td>
                            <td><span>' . $roi['price_rmb_month_1'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_2'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_3'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_4'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_5'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_6'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_7'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_8'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_9'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_10'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_11'] .'</span></td>
                            <td><span>' . $roi['price_rmb_month_12'] .'</span></td>
                            <td><span>' . $roi['average_price_rmb'] .'</span></td>
                        </tr>
                        <tr>
                            <td>销售金额</td>
                            <td><span>' . $roi['sales_amount_month_1'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_2'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_3'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_4'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_5'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_6'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_7'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_8'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_9'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_10'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_11'] .'</span></td>
                            <td><span>' . $roi['sales_amount_month_12'] .'</span></td>
                            <td><span>' . $roi['total_sales_amount'] .'</span></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-left: 0px; text-align: center">推广率</td>
                            <td><span>' . $roi['promo_rate_month_1'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_2'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_3'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_4'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_5'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_6'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_7'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_8'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_9'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_10'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_11'] .'</span></td>
                            <td><span>' . $roi['promo_rate_month_12'] .'</span></td>
                            <td><span>' . $roi['average_promo_rate'] .'</span></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-left: 0px; text-align: center">异常率</td>
                            <td><span>' . $roi['exception_rate_month_1'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_2'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_3'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_4'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_5'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_6'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_7'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_8'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_9'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_10'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_11'] .'</span></td>
                            <td><span>' . $roi['exception_rate_month_12'] .'</span></td>
                            <td><span>' . $roi['average_exception_rate'] .'</span></td>
                        </tr>
                    </table>
                </div>
                <div style="clear:both"></div>
                <div style="height: 25px;"></div>
                <div class="result_div">
                    <div style="font-size: 16px; font-weight: bold">产品开发及供应链成本</div>
                    <div style="height: 20px;"></div>
                    <div style="width:1501px">
                        <table id="params_cost_table" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td valign="top" width="750px">
                                    <div class="bold">采购参数</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">不含税采购价 :</span> <span class="bold">' .$roi['purchase_price'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">MOQ(PCS) :</span> <span class="bold">' .$roi['moq'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">单PCS实重(KG) :</span> <span>' .$roi['weight_per_pcs'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">单PCS体积(cm<sup>3</sup>) :</span> <span>' .$roi['volume_per_pcs'] . '</span></span>
                                    </div>
                                    <div style="clear:both"></div>
                                    <div style="height: 7px;"></div>
                                    <div><span class="grey_color">供应商账期 :</span> <span class="bold">' .$roi['billing_period_type'] . '</span></div>
                                    <div style="height: 15px;"></div>
                                    <div class="bold">平台参数</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">平台佣金(%) :</span> <span>' . $roi['commission_rate'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">平台操作费(外币/pcs) :</span> <span class="bold">' . $roi['unit_operating_fee'] . '</span></span>
                                    </div>
                                </td>
                                <td valign="top" width="750px">
                                    <div class="bold">开发成本</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">ID费用(元) :</span> <span>' . $roi['id_fee'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">模具费(元) :</span> <span>' . $roi['mold_fee'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">手板费(元) :</span> <span>' . $roi['prototype_fee'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">其他费用(元) :</span> <span>' . $roi['other_fixed_cost'] . '</span></span>
                                    </div>
                                    <div style="clear: both;"></div>
                                    <div style="height: 15px;"></div>
                                    <div class="bold">其他成本</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">专利费(元) :</span> <span>' . $roi['royalty_fee'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">认证费(元) :</span> <span>' . $roi['certification_fee'] . '</span></span>
                                    </div>
                                    <div style="clear: both;"></div>
                                    <div style="height: 15px;"></div>
                                    <div class="bold">运输参数</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">运输方式 :</span> <span>' . $roi['transport_mode'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">运输单价 :</span> <span>' . $roi['transport_unit_price'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">运输天数 :</span> <span>' . $roi['transport_days'] . '</span></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="margin-right: 20px; float: left;"><span class="grey_color">关税税率 :</span> <span>' . $roi['tariff_rate'] . '</span></span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div style="height: 30px;"></div>
                <div class="result_div">
                    <div style="font-size: 16px; font-weight: bold">产品开发及供应链成本</div>

                    <div style="height: 20px;"></div>
                    <div style="width:1501px">
                        <table id="result_table" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td><span class="grey_color">投资回报额(万元) :</span> <span class="bold">' . $roi['return_amount'] . '</span></td>
                                <td><span class="grey_color">投资回报率ROI(%) :</span> <span class="bold">' . $roi['roi'] . '</span></td>
                                <td width="25%"><span class="grey_color">项目利润率(%) :</span> <span class="bold">' . $roi['project_profitability'] . '</span></td>
                            </tr>
                            <tr>
                                <td width="25%"><span class="grey_color">底线价格(外币/元) :</span> <span class="bold">' . $roi['price_floor'] . '</span></td>
                                <td width="25%"><span class="grey_color">资金周转次数(次) :</span> <span class="bold">' . $roi['capital_turnover'] . '</span></td>
                                <td><span class="grey_color">库存周转天数(天) :</span> <span class="bold">' . $roi['inventory_turnover_days'] . '</span></td>
                                <td width="25%"><span class="grey_color">单PCS边际利润(元) :</span> <span class="bold">' . $roi['marginal_profit_per_pcs'] . '</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
        ';

        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A3', 'orientation' => 'L', 'tempDir' => '/tmp']);
        //正确显示中文
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        $mpdf->WriteHTML($output);
        $mpdf->Output('ROI_Analysis.pdf', 'D');
        die();
    }

    public function edit(Request $request, $id)
    {
        //name current user's id as $currentUserId
        $currentUserId = Auth::user()->id;
        $isUserAdmin = $this->isAdmin($currentUserId);
        $isUserProductDirector = $this->isProductDirector($currentUserId);
        $data = DB::connection('amazon')->table('roi')->where('id', '=', $id)->first();
        $data = json_decode(json_encode($data),true);
        if(!$data) exit;
        $visible = false;
        $creatorId = $data['creator'];
        $collaborators = $data['collaborators'];
        $isCreator = $currentUserId == $creatorId;
        if($isCreator || $isUserAdmin || $isUserProductDirector){
            $visible = true;
        }
        $isUserDirectLeader = $this->isDirectLeader($creatorId, $currentUserId);
        $isUserExtendedCollaborators = $this->isExtendedCollaborators($collaborators, $currentUserId);
        if($isUserDirectLeader || $isUserExtendedCollaborators){
            $visible = true;
        }
        if(!$visible) die('Permission denied');

        //如果已归档，直接跳转到show页面。(列表页中该条roi记录的操作不会出现"编辑"。如果用户直接用该条记录编辑页的url访问，则需加此判断)
        if($data['archived_status'] == 1){
            return redirect('roi/'.$id);
        }

        $users = $this->getUsers();
        $availableUsers = $this->getAvailableUsers();
        //编辑限制：其中一个用户编辑时，另一个用户只能查看。编辑用户关闭浏览器（标签）前未保存，则过$expiry_time = 70s后其他用户可编辑。
        $roi_id = $id;
        //$expiry_time 略大于edit页面ajax刷新时间60s
        $expiry_time = 70;
        $currentUserId = Auth::user()->id;
        $roi_edit_lock_query = DB::connection('amazon')->table('roi_edit_lock')->where('roi_id','=', $roi_id);
        $roi_edit_lock = $roi_edit_lock_query->first();
        if($roi_edit_lock){
            $roi_edit_lock = json_decode(json_encode($roi_edit_lock),true);
            $editing_user = $roi_edit_lock['editing_user'];
            $refresh_time = intval(strtotime($roi_edit_lock['refresh_time']));
            //when user saved the edit page successfully, editing_user is set to 0
            if($editing_user == 0){
                $roi_edit_lock_query->update(
                  array('editing_user'=>$currentUserId, 'refresh_time'=>date('Y-m-d H:i:s'))
                );
                //go to edit page
            }
            else{
               if(Auth::user()->id  == $editing_user){
                   $roi_edit_lock_query->update(
                       array('refresh_time'=>date('Y-m-d H:i:s'))
                   );
                   //go to edit page
               }
               else{
                  if(time() - $refresh_time < $expiry_time){
                      //show error_message: someone is editing
                      $request->session()->flash('error_message', array_get($users, $editing_user) .' is editing the file.');
                      return redirect('roi/'.$roi_id);
                  }
                  else{
                      $roi_edit_lock_query->update(
                          array('editing_user'=>$currentUserId, 'refresh_time'=>date('Y-m-d H:i:s'))
                      );
                      //go to edit page
                  }
              }
            }
        }
        else{
            DB::connection('amazon')->table('roi_edit_lock')->insert(
                array('roi_id'=>$roi_id, 'editing_user'=>$currentUserId, 'refresh_time'=>date('Y-m-d H:i:s'))
            );
            //go to edit page
        }

        $sites = $this->getSites();
        $billingPeriods = $this->getBillingPeriods();
        $transportModes = $this->getTransportModes();
        $currency_rates = $this->getCurrencyRates();
        $roi = $this->getCurrentRoi($id);
        for($i=1; $i<=12;$i++){
            $roi['promo_rate_month_'.$i] = $this->twoDecimal($roi['promo_rate_month_'.$i] * 100);
            $roi['exception_rate_month_'.$i] = $this->twoDecimal($roi['exception_rate_month_'.$i]* 100);
        }
        $roi['commission_rate'] = $this->twoDecimal($roi['commission_rate'] * 100);
        $roi['tariff_rate'] = $this->twoDecimal($roi['tariff_rate'] * 100);

        //不同的运输方式，显示不同的运输单位（元／立方米，元/KG）
        $transport_mode_int = $roi['transport_mode'];
        $roi['transport_mode'] = array_get($transportModes, $transport_mode_int);
        $transport_unit = '<span>元/m<sup>3</sup></span>';
        if($transport_mode_int == 1 || $transport_mode_int == 2) {
            $transport_unit = '<span>元/KG</span>';
        }
        $roi['transport_unit'] = $transport_unit;

        $eh = explode(";",$roi['edit_history']);
        $edit_history_array = array();
        foreach(array_reverse($eh) as $key => $value){
            $pair = explode(",",$value);
            $edit_history_array[] = array('user_name'=>array_get($users, $pair[0]), 'updated_at'=>$pair[1]);
        }

        return view('roi/edit',compact('sites', 'availableUsers', 'billingPeriods','transportModes', 'roi', 'edit_history_array', 'currency_rates'));
    }

    public function copy(Request $request){
        $id = $request->get('id');
        $data = DB::connection('amazon')->table('roi')->where('id', '=', $id)->first();
        $data = json_decode(json_encode($data),true);
        if(!$data) exit;

        $currentUserId = Auth::user()->id;
        $isUserAdmin = $this->isAdmin($currentUserId);
        $isUserProductDirector = $this->isProductDirector($currentUserId);
        $visible = false;
        $creatorId = $data['creator'];
        $collaborators = $data['collaborators'];
        $isCreator = $currentUserId == $creatorId;
        if($isCreator || $isUserAdmin || $isUserProductDirector){
            $visible = true;
        }
        $isUserDirectLeader = $this->isDirectLeader($creatorId, $currentUserId);
        $isUserExtendedCollaborators = $this->isExtendedCollaborators($collaborators, $currentUserId);
        if($isUserDirectLeader || $isUserExtendedCollaborators){
            $visible = true;
        }
        if(!$visible) die('Permission denied');

        unset($data['id']);
        $data['creator'] = $currentUserId;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $currentUserId;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['edit_history'] = $currentUserId.','.$data['updated_at'];
        $data['collaborators'] = null;
        //无论是否已归档，复制后的状态都设置为未归档,后续需要编辑
        $data['archived_status'] = 0;
        $data['product_name'] =  $data['product_name'].'-copy';

        DB::beginTransaction();
        if(!DB::connection('amazon')->table('roi')->insert($data)){
            $request->session()->flash('error_message','Save Failed.');
            return redirect('roi');
        }else{
            return redirect('roi');
        }
        DB::commit();
    }

    public function deleteRecord(Request $request){
        $id = $request->input('id');
        $roi = DB::connection('amazon')->table('roi')->where('id', '=', $id)->first();
        if(!$roi) exit;
        $roi = json_decode(json_encode($roi),true);
        $currentUserId = Auth::user()->id;
        $isUserAdmin = $this->isAdmin($currentUserId);

        //创建人及其上级和管理员（Admin）有删除权限，其他 不可删除
        $canDelete = false;
        //已归档的状态是不可删除的
        if($roi['archived_status'] == 0){
            $creatorId = $roi['creator'];
            $isCreator = $currentUserId == $creatorId;
            $isUserDirectLeader = $this->isDirectLeader($creatorId, $currentUserId);
            if($isCreator || $isUserAdmin || $isUserDirectLeader){
                $canDelete = true;
            }
        }
        if(!$canDelete) die('Permission denied');

        DB::connection('amazon')->table('roi')->where('id', '=', $id)->delete();
        return redirect('roi');
    }

    public function getCurrentRoi($id){
        $roi = DB::connection('amazon')->table('roi')->where('id', '=', $id)->first();
        $roi = json_decode(json_encode($roi),true);
        for($i=1; $i<=12;$i++){
            $roi['price_fc_month_'.$i] = $this->twoDecimal($roi['price_fc_month_'.$i]);
            $roi['price_rmb_month_'.$i] = $this->twoDecimal($roi['price_fc_month_'.$i] * $roi['currency_rate']);
            $roi['sales_amount_month_'.$i] = round($roi['price_fc_month_'.$i] * $roi['currency_rate'] * $roi['volume_month_'.$i]);
        }
        $roi['average_price_fc'] = $this->twoDecimal($roi['average_price_fc']);
        $roi['average_price_rmb'] = $this->twoDecimal($roi['average_price_rmb']);
        $roi['total_sales_amount'] = round($roi['total_sales_amount']);
        $roi['average_promo_rate'] = $this->toPercentage($roi['average_promo_rate']);
        $roi['average_exception_rate'] = $this->toPercentage($roi['average_exception_rate']);
        $roi['unit_operating_fee'] = $this->twoDecimal($roi['unit_operating_fee']);
        $roi['transport_unit_price'] = $this->twoDecimal($roi['transport_unit_price']);
        $roi['transport_days'] = $this->twoDecimal($roi['transport_days']);
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
        //name current user's id as $currentUserId
        $currentUserId = Auth::user()->id;
        $isUserAdmin = $this->isAdmin($currentUserId);
        $isUserProductDirector = $this->isProductDirector($currentUserId);
        $isUserPlanner = $this->isPlanner($currentUserId);
        $data = DB::connection('amazon')->table('roi')->where('id', '=', $id)->first();
        $data = json_decode(json_encode($data),true);
        if(!$data)  exit;

        $visible = false;
        if($isUserPlanner){
            if($data['archived_status'] == 1){
                $visible = true;
            }
        }
        else{
            $creatorId = $data['creator'];
            $collaborators = $data['collaborators'];
            $isCreator = $currentUserId == $creatorId;
            if($isCreator || $isUserAdmin || $isUserProductDirector){
                $visible = true;
            }
            $isUserDirectLeader = $this->isDirectLeader($creatorId, $currentUserId);
            $isUserExtendedCollaborators = $this->isExtendedCollaborators($collaborators, $currentUserId);
            if($isUserDirectLeader || $isUserExtendedCollaborators){
                $visible = true;
            }
        }

        if(!$visible) die('Permission denied');

        $canArchive = false;
        if($isUserAdmin || $isUserProductDirector){
            $canArchive = true;
        }

        $roi = $this->getCurrentRoi($id);
        $roi = $this->showPageDataFormat($roi);

        $users = $this->getUsers();
        $eh = explode(";",$roi['edit_history']);
        $edit_history_array = array();
        foreach(array_reverse($eh) as $key => $value){
            $pair = explode(",",$value);
            $edit_history_array[] = array('user_name'=>array_get($users, $pair[0]), 'updated_at'=>$pair[1]);
        }

        return view('roi/show', ['roi'=>$roi, 'edit_history_array' => $edit_history_array, 'canArchive'=>$canArchive]);
    }

    public function showPageDataFormat($roi){
        $billingPeriods = $this->getBillingPeriods();
        $transportModes = $this->getTransportModes();
        $transport_mode_int = $roi['transport_mode'];
        $roi['transport_mode'] = array_get($transportModes, $transport_mode_int);
        $transport_unit = '<span>元/m<sup>3</sup></span>';
        if($transport_mode_int == 1 || $transport_mode_int == 2) {
            $transport_unit = '<span>元/KG></span>';
        }
        $roi['transport_unit_price'] = $roi['transport_unit_price'].$transport_unit;
        $roi['billing_period_type'] = $billingPeriods[$roi['billing_period_type']]['name'];
        $estimated_launch_time = $roi['estimated_launch_time'];
        if($estimated_launch_time){
            for($i=1; $i<=12; $i++){
                $roi['month_'.$i] = date("Y-m", strtotime("+".($i-1)." months", strtotime($estimated_launch_time)));
            }
        }

        for($i=1; $i<=12;$i++){
            $roi['promo_rate_month_'.$i] = $this->toPercentage($roi['promo_rate_month_'.$i]);
            $roi['exception_rate_month_'.$i] = $this->toPercentage($roi['exception_rate_month_'.$i]);
        }
        $roi['commission_rate'] = $this->toPercentage($roi['commission_rate']);
        $roi['tariff_rate'] = $this->toPercentage($roi['tariff_rate']);

        if($roi['capital_turnover'] < 0){
            $roi['capital_turnover'] = '∞';
        }
        if($roi['roi'] < 0){
            $roi['roi'] = '∞';
        }

        return $roi;
    }

    public function store(Request $request){
        $updateDBData = $this->getUpdateDBData($request);
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
        $updateDBData = $this->getUpdateDBData($request);
        $currentUserId = Auth::user()->id;
        $updateDBData['updated_by'] = $currentUserId;
        $updateDBData['updated_at'] = date('Y-m-d H:i:s');
        //edit页面表单有隐藏元素roi_id, add页面没有
        $roi_id = $updateDBData['roi_id'];
        unset($updateDBData['roi_id']);

        $edit_history_array = DB::connection('amazon')->table('roi')->where('id', '=', $roi_id)->pluck('edit_history');
        $edit_history = $edit_history_array[0];
        $updateDBData['edit_history'] = $edit_history.';'.$currentUserId.','.$updateDBData['updated_at'];

        DB::beginTransaction();
        if(!DB::connection('amazon')->table('roi')->where('id', '=', $roi_id)->update($updateDBData)){
            $request->session()->flash('error_message','Update Failed.');
            return redirect()->back()->withInput();
        }else{
            $roi_edit_lock = DB::connection('amazon')->table('roi_edit_lock')->where('roi_id','=', $roi_id)->first();
            if($roi_edit_lock){
                DB::connection('amazon')->table('roi_edit_lock')->where('roi_id','=', $roi_id)->update(
                    array('editing_user'=> '0')
                );
            }

            return redirect('roi');
        }
        DB::commit();
    }


    public function analyse(Request $request){
        $updateAjaxData = $this->getUpdateAjaxData($request);
        return json_encode(array('updateAjaxData' => $updateAjaxData));
    }

    public function getUpdateDBData(Request $request){
        //测试用数据： site选择JP; $currency_rate = 0.065767; $early_investment = 3000;
        $currency_rates = $this->getCurrencyRates();
        $site = $request->input('site','US');
        $currency_rate = $currency_rates[$site];

        $update_data = $request->all();
        $update_data['product_name'] = $this->getString($update_data['product_name']);
        $update_data['project_code'] = $this->getString($update_data['project_code']);

        $price_rmb_month_array = $sales_amount_month_array = array();
        $total_sales_volume = $total_sales_amount = 0;
        $total_promo_amount = $total_exception_amount = 0;

        for($i=1; $i<=12; $i++){
            //单个月销量
            $update_data['volume_month_'.$i] = $this->getNumber($update_data['volume_month_'.$i]);
            //单个月售价外币
            $update_data['price_fc_month_'.$i] = $this->getNumber($update_data['price_fc_month_'.$i]);
            //单个月售价RMB
            $price_rmb_month_array[$i] = $update_data['price_fc_month_'.$i] * $currency_rate;
            $sales_amount_month_precise = $update_data['price_fc_month_'.$i] * $currency_rate * $update_data['volume_month_'.$i];
            //单个月销售金额(取整)
            $sales_amount_month_array[$i] = round($sales_amount_month_precise);
            //总销量
            $total_sales_volume += $update_data['volume_month_'.$i];
            //总销售金额(销售收入)
            $total_sales_amount += $sales_amount_month_precise;
            //单个月推广费(百分数转成小数)
            $update_data['promo_rate_month_'.$i] = $this->getNumber($update_data['promo_rate_month_'.$i])/100;
            $promo_amount_month_array[$i] = $update_data['promo_rate_month_'.$i] * $sales_amount_month_array[$i];
            //总推广费(营销费用)
            $total_promo_amount += $promo_amount_month_array[$i];
            //单个月异常费(百分数转成小数)
            $update_data['exception_rate_month_'.$i] = $this->getNumber($update_data['exception_rate_month_'.$i])/100;
            $exception_amount_month_array[$i] = $update_data['exception_rate_month_'.$i] * $sales_amount_month_array[$i];
            //总异常费(退款金额)
            $total_exception_amount += $exception_amount_month_array[$i];
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
        $update_data['purchase_price'] = $this->getNumber($update_data['purchase_price']);
        $purchase_cost = $update_data['purchase_price'] * $total_sales_volume;

        //--物流费用相关--
        //产品实重
        $update_data['weight_per_pcs'] = $this->getNumber($update_data['weight_per_pcs']);
        $product_weight = $update_data['weight_per_pcs'] * $total_sales_volume;
        //单pcs体积
        $update_data['volume_per_pcs'] = $this->getNumber($update_data['volume_per_pcs']);
        //体积
        $product_volume = $update_data['volume_per_pcs'] * $total_sales_volume/1000000;
        //运输单价
        $update_data['transport_unit_price'] = $this->getNumber($update_data['transport_unit_price']);
        //运输方式, 0-海运，1-空运，2-快递
        $transport_mode = $update_data['transport_mode'];
        //物流费用
        $transport_cost = 0;
        if($transport_mode == 0){
            $transport_cost = $update_data['transport_unit_price'] * $product_volume * 1.2;
        }else if($transport_mode == 1){
            $transport_cost = max($product_volume * 1000/6 * 1.2, $product_weight) * $update_data['transport_unit_price'];
        }else if($transport_mode == 2){
            $transport_cost = max($product_volume * 1000/5 * 1.2, $product_weight) * $update_data['transport_unit_price'];
        }

        //--相关税费--
        //关税税率(百分数转成小数)
        $update_data['tariff_rate'] = $this->getNumber($update_data['tariff_rate'])/100;
        $tariff_amount = $purchase_cost * 0.4 * $update_data['tariff_rate'];
        //VAT率
        $vat_rate = ((float)$this->getVatRates()[$site])/100;
        //VAT
        $vat_amount = $net_income / (1+$vat_rate) * $vat_rate;

        //--平台费用--
        //平台佣金率(百分数转成小数)
        $update_data['commission_rate'] = $this->getNumber($update_data['commission_rate'])/100;
        //平台佣金
        $commission_amount = $update_data['commission_rate'] * $total_sales_amount - $total_exception_amount * $update_data['commission_rate'] * 0.8;
        //操作费
        $update_data['unit_operating_fee'] = $this->getNumber($update_data['unit_operating_fee']);
        $operating_fee = $update_data['unit_operating_fee'] * $currency_rate * $total_sales_volume;
        //--仓储费相关--
        //moq
        $update_data['moq'] = $this->getNumber($update_data['moq']);
        //运输天数
        $update_data['transport_days'] = $this->getNumber($update_data['transport_days']);
        $unit_strorage_fee = $this->getUnitStorageFee()[$site];
        //仓储费
        $storage_fee = (($update_data['moq']/2+(7+$update_data['transport_days'])*$total_sales_volume/365)*$update_data['volume_per_pcs']/1000000*($unit_strorage_fee[0]*9+$unit_strorage_fee[1]*3))*$currency_rate;

        //库存周转天数
        $inventory_turnover_days = $update_data['transport_days']+7+$update_data['moq']/(2*$total_sales_volume/365);
        //供应商账期类型
        $billing_period_type = $update_data['billing_period_type'];
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
        //$marginal_profit_rate = $total_marginal_contribution / $total_sales_amount;

        //--固定成本--
        //固定费用小计
        $update_data['id_fee'] = $this->getNumber($update_data['id_fee']);
        $update_data['mold_fee'] = $this->getNumber($update_data['mold_fee']);
        $update_data['prototype_fee'] = $this->getNumber($update_data['prototype_fee']);
        $update_data['other_fixed_cost'] = $this->getNumber($update_data['other_fixed_cost']);
        $update_data['royalty_fee'] = $this->getNumber($update_data['royalty_fee']);
        $update_data['certification_fee'] = $this->getNumber($update_data['certification_fee']);
        $total_fixed_cost = $update_data['id_fee'] + $update_data['mold_fee'] + $update_data['prototype_fee'] + $update_data['other_fixed_cost'] + $update_data['royalty_fee'] + $update_data['certification_fee'];
        //盈亏临界点（销售数量）
        $breakeven_point_sales_volume = $total_fixed_cost / $marginal_profit_per_pcs;
        //预计投资回收期(月)
        $estimated_payback_period = 0;
        $cumulate_volume = 0;
        if($breakeven_point_sales_volume <= $total_sales_volume){
            for($i=1; $i<=12;$i++){
                $cumulate_volume += $update_data['volume_month_'.$i];
                if($cumulate_volume >= $breakeven_point_sales_volume){
                    $estimated_payback_period = $i;
                    break;
                }
            }
        }

        //前期开发投入
        $update_data['estimated_labor_cost'] = $this->getNumber($update_data['estimated_labor_cost']);
        $update_data['business_trip_expenses'] = $this->getNumber($update_data['business_trip_expenses']);
        $update_data['other_project_cost'] = $this->getNumber($update_data['other_project_cost']);
        $early_investment = $update_data['estimated_labor_cost'] + $update_data['business_trip_expenses'] + $update_data['other_project_cost'];
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

        //点保存按钮时，插入或者更新数据到数据库
        //以下百分数要转换成小数保存：单个月推广率，单个月异常率，平均推广率，平均异常率，平台佣金率，关税税率，项目利润率，投资回报率
        //$request->all();获取的参数还包含 _token, _method
        unset($update_data['_token']);
        unset($update_data['_method']);

        $update_data['currency_rate'] = $currency_rate;
        $update_data['total_sales_volume'] = $total_sales_volume;
        $update_data['total_sales_amount'] = $total_sales_amount;
        $update_data['average_price_rmb'] = $average_price_rmb;
        $update_data['average_price_fc'] = $average_price_fc;
        $update_data['average_promo_rate'] = $average_promo_rate;
        $update_data['average_exception_rate'] = $average_exception_rate;
        $update_data['total_fixed_cost'] = $total_fixed_cost;
        $update_data['early_investment'] = $early_investment;
        $update_data['price_floor'] = $price_floor;
        $update_data['inventory_turnover_days'] = $inventory_turnover_days;
        $update_data['project_profitability'] = $project_profitability;
        $update_data['marginal_profit_per_pcs'] = $marginal_profit_per_pcs;
        $update_data['estimated_payback_period'] = $estimated_payback_period;
        $update_data['capital_turnover'] = $capital_turnover;
        $update_data['roi'] = $roi;
        $update_data['return_amount'] = $return_amount;

        return $update_data;
    }

    public function getUpdateAjaxData(Request $request){
        //测试用数据： site选择JP; $currency_rate = 0.065767; $early_investment = 3000;
        $currency_rates = $this->getCurrencyRates();
        $site = $request->input('site','US');
        $currency_rate = $currency_rates[$site];

        $update_data = $request->all();
        $update_data['product_name'] = $this->getString($update_data['product_name']);
        $update_data['project_code'] = $this->getString($update_data['project_code']);

        $price_rmb_month_array = $sales_amount_month_array = array();
        $total_sales_volume = $total_sales_amount = 0;
        $total_promo_amount = $total_exception_amount = 0;

        for($i=1; $i<=12; $i++){
            //单个月销量
            $update_data['volume_month_'.$i] = $this->getNumber($update_data['volume_month_'.$i]);
            //单个月售价外币
            $update_data['price_fc_month_'.$i] = $this->getNumber($update_data['price_fc_month_'.$i]);
            //单个月售价RMB
            $price_rmb_month_array[$i] = $update_data['price_fc_month_'.$i] * $currency_rate;
            $sales_amount_month_precise = $update_data['price_fc_month_'.$i] * $currency_rate * $update_data['volume_month_'.$i];
            //单个月销售金额(取整)
            $sales_amount_month_array[$i] = round($sales_amount_month_precise);
            //总销量
            $total_sales_volume += $update_data['volume_month_'.$i];
            //总销售金额(销售收入)
            $total_sales_amount += $sales_amount_month_precise;
            //单个月推广费(百分数转成小数)
            $update_data['promo_rate_month_'.$i] = $this->getNumber($update_data['promo_rate_month_'.$i])/100;
            $promo_amount_month_array[$i] = $update_data['promo_rate_month_'.$i] * $sales_amount_month_array[$i];
            //总推广费(营销费用)
            $total_promo_amount += $promo_amount_month_array[$i];
            //单个月异常费(百分数转成小数)
            $update_data['exception_rate_month_'.$i] = $this->getNumber($update_data['exception_rate_month_'.$i])/100;
            $exception_amount_month_array[$i] = $update_data['exception_rate_month_'.$i] * $sales_amount_month_array[$i];
            //总异常费(退款金额)
            $total_exception_amount += $exception_amount_month_array[$i];
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
        $update_data['purchase_price'] = $this->getNumber($update_data['purchase_price']);
        $purchase_cost = $update_data['purchase_price'] * $total_sales_volume;

        //--物流费用相关--
        //产品实重
        $update_data['weight_per_pcs'] = $this->getNumber($update_data['weight_per_pcs']);
        $product_weight = $update_data['weight_per_pcs'] * $total_sales_volume;
        //单pcs体积
        $update_data['volume_per_pcs'] = $this->getNumber($update_data['volume_per_pcs']);
        //体积
        $product_volume = $update_data['volume_per_pcs'] * $total_sales_volume/1000000;
        //运输单价
        $update_data['transport_unit_price'] = $this->getNumber($update_data['transport_unit_price']);
        //运输方式, 0-海运，1-空运，2-快递
        $transport_mode = $update_data['transport_mode'];
        //物流费用
        $transport_cost = 0;
        if($transport_mode == 0){
            $transport_cost = $update_data['transport_unit_price'] * $product_volume * 1.2;
        }else if($transport_mode == 1){
            $transport_cost = max($product_volume * 1000/6 * 1.2, $product_weight) * $update_data['transport_unit_price'];
        }else if($transport_mode == 2){
            $transport_cost = max($product_volume * 1000/5 * 1.2, $product_weight) * $update_data['transport_unit_price'];
        }

        //--相关税费--
        //关税税率(百分数转成小数)
        $update_data['tariff_rate'] = $this->getNumber($update_data['tariff_rate'])/100;
        $tariff_amount = $purchase_cost * 0.4 * $update_data['tariff_rate'];
        //VAT率
        $vat_rate = ((float)$this->getVatRates()[$site])/100;
        //VAT
        $vat_amount = $net_income / (1+$vat_rate) * $vat_rate;

        //--平台费用--
        //平台佣金率(百分数转成小数)
        $update_data['commission_rate'] = $this->getNumber($update_data['commission_rate'])/100;
        //平台佣金
        $commission_amount = $update_data['commission_rate'] * $total_sales_amount - $total_exception_amount * $update_data['commission_rate'] * 0.8;
        //操作费
        $update_data['unit_operating_fee'] = $this->getNumber($update_data['unit_operating_fee']);
        $operating_fee = $update_data['unit_operating_fee'] * $currency_rate * $total_sales_volume;
        //--仓储费相关--
        //moq
        $update_data['moq'] = $this->getNumber($update_data['moq']);
        //运输天数
        $update_data['transport_days'] = $this->getNumber($update_data['transport_days']);
        $unit_strorage_fee = $this->getUnitStorageFee()[$site];
        //仓储费
        $storage_fee = (($update_data['moq']/2+(7+$update_data['transport_days'])*$total_sales_volume/365)*$update_data['volume_per_pcs']/1000000*($unit_strorage_fee[0]*9+$unit_strorage_fee[1]*3))*$currency_rate;

        //库存周转天数
        $inventory_turnover_days = $update_data['transport_days']+7+$update_data['moq']/(2*$total_sales_volume/365);
        //供应商账期类型
        $billing_period_type = $update_data['billing_period_type'];
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
        //$marginal_profit_rate = $total_marginal_contribution / $total_sales_amount;

        //--固定成本--
        //固定费用小计
        $update_data['id_fee'] = $this->getNumber($update_data['id_fee']);
        $update_data['mold_fee'] = $this->getNumber($update_data['mold_fee']);
        $update_data['prototype_fee'] = $this->getNumber($update_data['prototype_fee']);
        $update_data['other_fixed_cost'] = $this->getNumber($update_data['other_fixed_cost']);
        $update_data['royalty_fee'] = $this->getNumber($update_data['royalty_fee']);
        $update_data['certification_fee'] = $this->getNumber($update_data['certification_fee']);
        $total_fixed_cost = $update_data['id_fee'] + $update_data['mold_fee'] + $update_data['prototype_fee'] + $update_data['other_fixed_cost'] + $update_data['royalty_fee'] + $update_data['certification_fee'];
        //盈亏临界点（销售数量）
        $breakeven_point_sales_volume = $total_fixed_cost / $marginal_profit_per_pcs;
        //预计投资回收期(月)
        $estimated_payback_period = 0;
        $cumulate_volume = 0;
        if($breakeven_point_sales_volume <= $total_sales_volume){
            for($i=1; $i<=12;$i++){
                $cumulate_volume += $update_data['volume_month_'.$i];
                if($cumulate_volume >= $breakeven_point_sales_volume){
                    $estimated_payback_period = $i;
                    break;
                }
            }
        }

        //前期开发投入
        $update_data['estimated_labor_cost'] = $this->getNumber($update_data['estimated_labor_cost']);
        $update_data['business_trip_expenses'] = $this->getNumber($update_data['business_trip_expenses']);
        $update_data['other_project_cost'] = $this->getNumber($update_data['other_project_cost']);
        $early_investment = $update_data['estimated_labor_cost'] + $update_data['business_trip_expenses'] + $update_data['other_project_cost'];
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
        //以下注释部分，前端已经自动计算
    //    $updateAjaxData['total_sales_volume'] = $total_sales_volume;
    //    $updateAjaxData['average_price_fc'] = $this->twoDecimal($average_price_fc);
    //    $updateAjaxData['average_price_rmb'] = $this->twoDecimal($average_price_rmb);
    //    $updateAjaxData['total_sales_amount'] = round($total_sales_amount);
    //    $updateAjaxData['average_promo_rate'] = $this->toPercentage($average_promo_rate);
    //    $updateAjaxData['average_exception_rate'] = $this->toPercentage($average_exception_rate);
    //    for($i=1; $i<=12; $i++) {
    //        $updateAjaxData['price_rmb_month_' . $i] = $this->twoDecimal($price_rmb_month_array[$i]);
    //        $updateAjaxData['sales_amount_month_' . $i] = $sales_amount_month_array[$i];
    //    }
        $updateAjaxData['price_floor'] = $this->twoDecimal($price_floor);
        $updateAjaxData['inventory_turnover_days'] = $this->twoDecimal($inventory_turnover_days);
        $updateAjaxData['project_profitability'] = $this->toPercentage($project_profitability);
        $updateAjaxData['marginal_profit_per_pcs'] = $this->twoDecimal($marginal_profit_per_pcs);
        $updateAjaxData['estimated_payback_period'] = $estimated_payback_period > 0 ? $estimated_payback_period : '12个月以后';
        $updateAjaxData['capital_turnover'] = $capital_turnover < 0 ? '∞' : $this->twoDecimal($capital_turnover);
        $updateAjaxData['roi'] = $roi < 0 ? '∞' : $this->toPercentage($roi);
        $updateAjaxData['return_amount'] = $this->twoDecimal($return_amount/10000);

        return $updateAjaxData;

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
        //roi表的字段billing_period_type，对应roi_billing_periods表的type_number
        $billingPeriods = DB::connection('amazon')->table('roi_billing_periods')->orderBy('id')->get();
        $billingPeriods = json_decode(json_encode($billingPeriods),true);
        $data = array();
        foreach($billingPeriods as $v){
            $data[$v['type_number']] = array('name'=>$v['name'], 'days'=>$v['days']);
        }

        return $data;

//        $data = array(
//            '0' => array('name'=>'预付20%订金，尾款开3个月银行承兑汇票（见票发货）','days'=>78),
//            '1' => array('name'=>'预付50%订金，50%尾款货到付款','days'=>-15),
//            '2' => array('name'=>'货到开票（3个月银行承兑）','days'=>105),
//            '3' => array('name'=>'预付20%订金，余款月结45天','days'=>42),
//            '4' => array('name'=>'当月结15天，开三个月银行承兑','days'=>120),
//            '5' => array('name'=>'预付30%订金，尾款款到发货','days'=>-9), //新账期表中没有了
//            '6' => array('name'=>'预付30%订金，尾款货到付款','days'=>-9),
//            '7' => array('name'=>'质检合格后货到三个工作日内付款','days'=>3),
//            '8' => array('name'=>'预付30%订金，尾款质检合格收到发票后3个工作日内支付','days'=>3.6),
//            '9' => array('name'=>'预付30%订金，尾款当月结15天并开三个月银行承兑','days'=>75),
//            '10' => array('name'=>'月结30天','days'=>45),
//            '11' => array('name'=>'预付20%订金，尾款货到付款','days'=>-6),
//            '12' => array('name'=>'预付20%订金，余款月结30天','days'=>30),
//            '13' => array('name'=>'预付10%订金，余款月结45天','days'=>51),
//            '14' => array('name'=>'月结60天+3个月银行承兑','days'=>165),
//            '15' => array('name'=>'月结45天+3个月银行承兑','days'=>150),
//            '16' => array('name'=>'预付50%订金，50%尾款款到发货','days'=>-15),
//            '17' => array('name'=>'月结30天+保理80天','days'=>125),
//            '18' => array('name'=>'预付20%订金，尾款款到发货','days'=>-6),
//            '19' => array('name'=>'月结90天','days'=>105),
//            '20' => array('name'=>'月结15天','days'=>30),
//            '21' => array('name'=>'月结30天+3个月银行承兑','days'=>135),
//            '22' => array('name'=>'预付40%订金，60%尾款款到发货','days'=>-12),
//            '23' => array('name'=>'月结45天','days'=>60),
//            '24' => array('name'=>'款到发货','days'=>0),
//            '25' => array('name'=>'月结60天','days'=>75),
//            '26' => array('name'=>'预付20%订金，尾款货到后7个工作日内支付（3个月银行承兑）','days'=>78)
//        );
//        return $data;
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

    //小数转成百分数，保留2位小数
    public function toPercentage($num){
        return sprintf("%.2f",$num*100).'%';
    }

    //处理request->all()中的数值
    public function getNumber($val){
        return $val != null ? $val : 0;
    }
    //处理request->all()中的非数值
    public function getString($val){
        //当输入为" "时，request->all() 取得的值为null, 不是 " "。所以可以不用trim($val)。
        return $val != null ? $val : '';
    }

    public function getUsers(){
        return User::pluck('name','id');
    }

    //目前在职的
    public function getAvailableUsers(){
        return User::where('locked', '=',0)->pluck('name','id');
    }

    public function roiRefreshTime(Request $req){
        $roi_id = $req->input('roi_id');
        $roi_edit_lock = DB::connection('amazon')->table('roi_edit_lock')->where('roi_id','=', $roi_id)->first();
        if($roi_edit_lock){
            $updateData = array();
            $updateData['refresh_time'] = date('Y-m-d H:i:s');
            DB::connection('amazon')->table('roi_edit_lock')->where('roi_id','=', $roi_id)->update($updateData);
        }

        return json_encode(array('msg' => 'refresh successfullly'));
    }

    public function isAdmin($userId){
        $adminUserIds = DB::table('role_user')->where('role_id', '17')->pluck('user_id');
        $adminUserIds = json_decode(json_encode($adminUserIds),true);
        if(in_array($userId, $adminUserIds)){
            return true;
        }
        return false;
    }

    public function isProductDirector($userId){
        $pdUserIds = DB::table('role_user')->where('role_id', '25')->pluck('user_id');
        $pdUserIds = json_decode(json_encode($pdUserIds),true);
        if(in_array($userId, $pdUserIds)){
            return true;
        }
        return false;
    }


    public function isDirectLeader($childId, $checkId)
    {
        $groupIds = DB::table('group_detail')->where('user_id', '=', $childId)->pluck('group_id');
        $groupIds = json_decode(json_encode($groupIds), true);
        $groupDetails = DB::table('group_detail')->whereIn('group_id', $groupIds)->select('user_id', 'leader', 'group_id')->get();
        $groupDetails = json_decode(json_encode($groupDetails), true);
        foreach ($groupDetails as $k => $v) {
            if ($v['user_id'] == $checkId && $v['leader'] == 1) {
                return true;
            }
        }

        return false;
    }

    //$collaborators对应roi的collaborators字段的值
    public function isExtendedCollaborators($collaborators, $checkId){
        if($collaborators){
            $collaboratorsIdArray = explode(',', $collaborators);
            if(in_array($checkId, $collaboratorsIdArray)){
                return true;
            }
            foreach ($collaboratorsIdArray as $k =>$v){
                if($this->isDirectLeader($v, $checkId)){
                    return true;
                }
            }
        }

        return false;
    }

    //计划员
    public function isPlanner($userId){
        //23 计划员，31 计划经理
        $plannerIds = DB::table('role_user')->whereIn('role_id', ['23','31'])->pluck('user_id');
        $plannerIds = json_decode(json_encode($plannerIds), true);
        if(in_array($userId, $plannerIds)){
            return true;
        }
        return false;
    }

}