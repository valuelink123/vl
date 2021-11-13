@extends('layouts.layout')
@section('crumb')
    <a href="/roi">ROI Analysis</a>
@endsection
@section('content')
    <style type="text/css">
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

    <div class="row">
        <div class="col-md-12">
            <div style="height: 20px;"></div>
            <div style="float: right;">
                <button type="button" class="common-btn" data-target="#edit-history-modal" data-toggle="modal" style="width: 104px;"><span><i class="fa fa-history"></i></span> 编辑历史</button>
            </div>
            <div style="float: right;">
                <button type="button" class="common-btn" id="copy-btn" style="width: 104px; margin-right: 10px"><span><i class="fa fa-copy"></i></span> 复制链接</button>
            </div>
            <div style="float: right;">
                <button type="button" class="common-btn" id="export-btn" style="width: 80px; margin-right: 10px"><span><i class="fa fa-sign-out"></i></span> 导出</button>
            </div>
            <div style="float: right;">
                @if(!$canArchive)
                <button type="button" class="disabled-btn" disabled style="width: 106px; margin-right: 10px"><span><i class="fa fa-archive"></i></span> 审核通过</button>
                @else
                <button type="button" class="common-btn" id="archived-btn" data-target="#archived-modal" data-toggle="modal" data-roi_id="{{$roi['id']}}" data-launch_time="{{$roi['estimated_launch_time']}}" data-sku="{{$roi['sku']}}" data-new_product_planning_process="{{$roi['new_product_planning_process']}}" style="width: 106px; margin-right: 10px"><span><i class="fa fa-archive"></i></span> 审核通过</button>
                @endif
            </div>
            <input id="roi_show_link" value="{{ 'www.vleop.com:88/roi/'.$roi['id'] }}"  style="opacity: 0; float: right" readonly>
            <div style="clear:both"></div>
            <div style="height: 20px;"></div>
        </div>
        <div class="col-md-12">
            <div class="portlet light bordered">
              <div style="width: 1502px; text-align: left; margin: auto;">
                <div style="height: 25px;"></div>
                <div>
                    <div style="font-size: 18px; float: left; font-weight: bold">投入产出表</div>
                    @if($roi['archived_status'] == 0)
                    <div style="font-size: 15px; float: right">
                        <a href="{{ url('roi/'.$roi['id'].'/edit') }}"><button type="button" class="edit-btn" style="width: 80px; margin-right: 10px"><span><i class="fa fa-edit"></i></span> 编辑</button></a>
                    </div>
                    @else
                    <div style="font-size: 15px; float: right">
                        <button type="button" class="edit-disabled-btn" disabled style="width: 80px; margin-right: 10px"><span><i class="fa fa-edit"></i></span> 编辑</button>
                    </div>
                    @endif
                </div>
                <div style="clear:both"></div>
                <div style="height: 30px;"></div>
                <div>
                    <span style="padding-right: 20px">产品名称: <span class="highlight_text">{{$roi['product_name']}}</span></span>
                    <span style="padding-right: 20px">站点: <span class="highlight_text">{{$roi['site']}}</span></span>
                    <span style="padding-right: 20px">预计上线时间<span style="color: #999999;" title="预计新品可正式上线销售的时间"><i class="fa fa-info-circle"></i></span>: <span class="highlight_text">{{$roi['estimated_launch_time']}}</span></span>
                    <span style="padding-right: 20px">SKU<span style="color: #999999;" title="在SAP中新建的产品物料号"><i class="fa fa-info-circle"></i></span>: <span class="highlight_text">{{$roi['sku']}}</span></span>
                    <span style="padding-right: 20px">售价(外币)： <span class="highlight_text">{{$roi['sell_price']}}</span></span>
                    <span style="padding-right: 20px">项目编号<span style="color: #999999;" title="新品开发项目定义好的项目编号"><i class="fa fa-info-circle"></i></span>: <span class="highlight_text">{{$roi['project_code']}}</span></span>
                    <span style="padding-right: 20px"><a href="{{$roi['new_product_planning_process']}}" target="_blank">查看新品规划流程</a><span style="color: #999999;" title="OA中新品规划流程的页面链接"><i class="fa fa-info-circle"></i></span></span>

                </div>
                <div style="clear:both"></div>
                <div style="height: 15px;"></div>
                <div style="font-size:12px; color: #cccccc;">说明：下表的月份是从上市日起的当月起按第一个月算，以12个月为一个周期</div>
                <div style="height: 5px;"></div>
                <div>
                    <table id="sales_table" border="1" cellspacing="0" cellpadding="0">
                        <tr id="sales_table_th">
                            <th colspan="2" width="200px" style="text-align: center">项目/时间</th>
                            <th width="100px">{{$roi['month_1']}}</th>
                            <th width="100px">{{$roi['month_2']}}</th>
                            <th width="100px">{{$roi['month_3']}}</th>
                            <th width="100px">{{$roi['month_4']}}</th>
                            <th width="100px">{{$roi['month_5']}}</th>
                            <th width="100px">{{$roi['month_6']}}</th>
                            <th width="100px">{{$roi['month_7']}}</th>
                            <th width="100px">{{$roi['month_8']}}</th>
                            <th width="100px">{{$roi['month_9']}}</th>
                            <th width="100px">{{$roi['month_10']}}</th>
                            <th width="100px">{{$roi['month_11']}}</th>
                            <th width="100px">{{$roi['month_12']}}</th>
                            <th width="100px">合计</th>
                        </tr>
                        <tr>
                            <td rowspan="4" style="padding-left: 0px; text-align: center">销售预测</td>
                            <td style="padding-left: 10px; text-align: left">预计销量</td>
                            <td><span>{{$roi['volume_month_1']}}</span></td>
                            <td><span>{{$roi['volume_month_2']}}</span></td>
                            <td><span>{{$roi['volume_month_3']}}</span></td>
                            <td><span>{{$roi['volume_month_4']}}</span></td>
                            <td><span>{{$roi['volume_month_5']}}</span></td>
                            <td><span>{{$roi['volume_month_6']}}</span></td>
                            <td><span>{{$roi['volume_month_7']}}</span></td>
                            <td><span>{{$roi['volume_month_8']}}</span></td>
                            <td><span>{{$roi['volume_month_9']}}</span></td>
                            <td><span>{{$roi['volume_month_10']}}</span></td>
                            <td><span>{{$roi['volume_month_11']}}</span></td>
                            <td><span>{{$roi['volume_month_12']}}</span></td>
                            <td><span class="highlight_text">{{$roi['total_sales_volume']}}</span></td>
                        </tr>
                        <tr>
                            <td>成交价(外币)</td>
                            <td><span>{{$roi['price_fc_month_1']}}</span></td>
                            <td><span>{{$roi['price_fc_month_2']}}</span></td>
                            <td><span>{{$roi['price_fc_month_3']}}</span></td>
                            <td><span>{{$roi['price_fc_month_4']}}</span></td>
                            <td><span>{{$roi['price_fc_month_5']}}</span></td>
                            <td><span>{{$roi['price_fc_month_6']}}</span></td>
                            <td><span>{{$roi['price_fc_month_7']}}</span></td>
                            <td><span>{{$roi['price_fc_month_8']}}</span></td>
                            <td><span>{{$roi['price_fc_month_9']}}</span></td>
                            <td><span>{{$roi['price_fc_month_10']}}</span></td>
                            <td><span>{{$roi['price_fc_month_11']}}</span></td>
                            <td><span>{{$roi['price_fc_month_12']}}</span></td>
                            <td><span class="highlight_text">{{$roi['average_price_fc']}}</span></td>
                        </tr>
                        <tr>
                            <td>成交价RMB</td>
                            <td><span>{{$roi['price_rmb_month_1']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_2']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_3']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_4']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_5']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_6']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_7']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_8']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_9']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_10']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_11']}}</span></td>
                            <td><span>{{$roi['price_rmb_month_12']}}</span></td>
                            <td><span class="highlight_text">{{$roi['average_price_rmb']}}</span></td>
                        </tr>
                        <tr>
                            <td>销售金额</td>
                            <td><span>{{$roi['sales_amount_month_1']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_2']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_3']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_4']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_5']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_6']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_7']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_8']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_9']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_10']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_11']}}</span></td>
                            <td><span>{{$roi['sales_amount_month_12']}}</span></td>
                            <td><span class="highlight_text">{{$roi['total_sales_amount']}}</span></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-left: 0px; text-align: center">推广率</td>
                            <td><span>{{$roi['promo_rate_month_1']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_2']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_3']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_4']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_5']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_6']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_7']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_8']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_9']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_10']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_11']}}</span></td>
                            <td><span>{{$roi['promo_rate_month_12']}}</span></td>
                            <td><span class="highlight_text">{{$roi['average_promo_rate']}}</span></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-left: 0px; text-align: center">异常率</td>
                            <td><span>{{$roi['exception_rate_month_1']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_2']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_3']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_4']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_5']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_6']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_7']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_8']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_9']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_10']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_11']}}</span></td>
                            <td><span>{{$roi['exception_rate_month_12']}}</span></td>
                            <td><span class="highlight_text">{{$roi['average_exception_rate']}}</span></td>

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
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">不含税采购价 :</span> <span class="bold">{{$roi['purchase_price']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">MOQ(PCS) :</span> <span class="bold">{{$roi['moq']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">单PCS实重(KG) :</span> <span>{{$roi['weight_per_pcs']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">单PCS体积(cm<sup>3</sup>) :</span> <span>{{$roi['volume_per_pcs']}}</span></div>
                                    </div>
                                    <div style="clear:both"></div>
                                    <div style="height: 7px;"></div>
                                    <div><span class="grey_color">供应商账期 :</span> <span class="bold">{{$roi['billing_period_type']}}</span></div>
                                    <div style="height: 15px;"></div>
                                    <div class="bold">平台参数</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">平台佣金(%) :</span> <span>{{$roi['commission_rate']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">平台操作费(外币/pcs) :</span> <span class="bold">{{$roi['unit_operating_fee']}}</span></div>
                                    </div>
                                </td>
                                <td valign="top" width="750px">
                                    <div class="bold">开发成本</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">ID费用(元) :</span> <span>{{$roi['id_fee']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">模具费(元) :</span> <span>{{$roi['mold_fee']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">手板费(元) :</span> <span>{{$roi['prototype_fee']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">其他费用(元) :</span> <span>{{$roi['other_fixed_cost']}}</span></div>
                                    </div>
                                    <div style="clear: both;"></div>
                                    <div style="height: 15px;"></div>
                                    <div class="bold">其他成本</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">专利费(元) :</span> <span>{{$roi['royalty_fee']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">认证费(元) :</span> <span>{{$roi['certification_fee']}}</span></div>
                                    </div>
                                    <div style="clear: both;"></div>
                                    <div style="height: 15px;"></div>
                                    <div class="bold">运输参数</div>
                                    <div style="height: 7px;"></div>
                                    <div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">运输方式 :</span> <span>{{$roi['transport_mode']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">运输单价 :</span> <span>{!! $roi['transport_unit_price'] !!}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">运输天数 :</span> <span>{{$roi['transport_days']}}</span></div>
                                        <div style="margin-right: 20px; float: left;"><span class="grey_color">关税税率 :</span> <span>{{$roi['tariff_rate']}}</span></div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>
                <div style="height: 30px;"></div>
                <div class="result_div">
                    <div style="font-size: 16px; font-weight: bold">投入产出分析结果</div>

                    <div style="height: 15px;"></div>
                    <div style="width:1501px">
                        <table id="result_table" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td><span class="grey_color">投资回收期(月) :</span> <span class="bold" id="estimated_payback_period">{{$roi['estimated_payback_period']}}</span></td>
                                <td><span class="grey_color">投资回报额 :</span> <span class="bold" id="return_amount">{{$roi['return_amount']}}</span></td>
                                <td><span class="grey_color">投资回报率 :</span> <span class="bold" id="roi">{{$roi['roi']}}</span></td>
                                <td><span class="grey_color">利润率 :</span> <span class="bold" id="project_profitability">{{$roi['project_profitability']}}</span></td>
                            </tr>

                            <tr>
                                <td><span class="grey_color">年销售量 :</span> <span class="bold" id="total_sales_volume">{{$roi['total_sales_volume']}}</span></td>
                                <td><span class="grey_color">年销售金额 :</span> <span class="bold" id="total_sales_amount">{{$roi['total_sales_amount']}}</span></td>
                                <td><span class="grey_color">年采购金额 :</span> <span class="bold" id="year_purchase_amount">{{$roi['year_purchase_amount']}}</span></td>
                                <td><span class="grey_color">年异常金额 :</span> <span class="bold" id="year_exception_amount">{{$roi['year_exception_amount']}}</span></td>
                            </tr>

                            <tr>
                                <td><span class="grey_color">年推广费 :</span> <span class="bold" id="year_promo">{{$roi['year_promo']}}</span></td>
                                <td><span class="grey_color">年平台佣金 :</span> <span class="bold" id="year_platform_commission">{{$roi['year_platform_commission']}}</span></td>
                                <td><span class="grey_color">年平台操作费 :</span> <span class="bold" id="year_platform_operate">{{$roi['year_platform_operate']}}</span></td>
                                <td><span class="grey_color">年平台仓储费 :</span> <span class="bold" id="year_platform_storage">{{$roi['year_platform_storage']}}</span></td>
                            </tr>

                            <tr>
                                <td><span class="grey_color">年进口税 :</span> <span class="bold" id="year_import_tax">{{$roi['year_import_tax']}}</span></td>
                                <td><span class="grey_color">年物流费 :</span> <span class="bold" id="year_transport">{{$roi['year_transport']}}</span></td>
                                <td><span class="grey_color">库存周转天数 :</span> <span class="bold" id="inventory_turnover_days">{{$roi['inventory_turnover_days']}}</span></td>
                                <td><span class="grey_color">资金周转次数 :</span> <span class="bold" id="capital_turnover">{{$roi['capital_turnover']}}</span></td>
                            </tr>

                            <tr>
                                <td><span class="grey_color">投入资金 :</span> <span class="bold" id="put_cost">{{$roi['put_cost']}}</span></td>
                                <td><span class="grey_color">资金占用成本 :</span> <span class="bold" id="capital_occupy_cost">{{$roi['capital_occupy_cost']}}</span></td>
                                <td><span class="grey_color">变动成本费用小计 :</span> <span class="bold" id="change_cost">{{$roi['change_cost']}}</span></td>
                                <td><span class="grey_color">边际贡献总额 :</span> <span class="bold" id="contribute_cost_total">{{$roi['contribute_cost_total']}}</span></td>
                            </tr>

                            <tr>
                                <td><span class="grey_color">单位平均边际贡献 :</span> <span class="bold" id="marginal_profit_per_pcs">{{$roi['marginal_profit_per_pcs']}}</span></td>
                                <td><span class="grey_color">固定成本 :</span> <span class="bold" id="total_fixed_cost">{{$roi['total_fixed_cost']}}</span></td>
                                <td><span class="grey_color">人力成本 :</span> <span class="bold" id="estimated_labor_cost">{{$roi['estimated_labor_cost']}}</span></td>
                                <td><span class="grey_color">盈亏临界点(销量) :</span> <span class="bold" id="profit_loss_point">{{$roi['profit_loss_point']}}</span></td>
                            </tr>

                            <tr>
                                <td><span class="grey_color">底限价格 :</span> <span class="bold" id="price_floor">{{$roi['price_floor']}}</span></td>
                            </tr>


                        </table>
                    </div>
                </div>
              </div>
            </div>
        </div>

    </div>
    <div class="modal fade" id="archived-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document" style="width:480px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">审核</h4>
                </div>
                <div class="modal-body">
                    <form id="archive_form" action="{{ url('/roi_archive') }}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" id="roi_id" name="roi_id" value="" />
                        <div>SKU<span style="color: #999999;" title="在SAP中新建的产品物料号"><i class="fa fa-info-circle"></i></span></div>
                        <input type="text" id="sku" name="sku" style="width: 448px; height: 29px;" value="" />
                        <div style="height: 10px;"></div>
                        <div>预计上线时间</div>
                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                        <span style="width:20px; height:26px" class="input-group-btn">
                            <button class="btn btn-sm default time-btn" type="button">
                                <i class="fa fa-calendar"></i>
                            </button>
                        </span>
                            <input type="text" id="launch_time" name="launch_time" style="width: 415px; height: 29px;" class="form-control form-filter input-sm" placeholder="Date" readonly />
                        </div>
                        <div style="height: 10px;"></div>
                        <div>新品规划流程<span style="color: #999999;" title="OA中新品规划流程的页面链接"><i class="fa fa-info-circle"></i></span></div>
                        <input type="text" id="new_product_planning_process" name="new_product_planning_process" style="width: 435px; height: 29px;" value="" placeholder="http://" />
                        <div style="height: 30px;"></div>
                        <div style="float: right;">
                            <button type="submit" class="common-btn" id="" style="width: 80px">确定</button>
                        </div>
                        <div style="float: right;">
                            <button type="button" class="common-btn" data-dismiss="modal" style="margin-right: 10px;">取消</button>
                        </div>
                        <div style="clear:both"></div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="edit-history-modal" tabindex="-2" role="dialog" aria-labelledby="historyLabel">
        <div class="modal-dialog" role="document" style="width:362px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="historyLabel">编辑历史</h4>
                </div>
                <div class="modal-body">
                  <div style="width: 330px; height: 260px; overflow-y: auto;" >
                    <table border="1" style="width: 330px; border: 0px solid #eeeeee;">
                        <tr style="height: 26px; background-color: #eeeeee;">
                            <th width="50%">编辑者</th>
                            <th>编辑时间</th>
                        </tr>
                        <tr>
                        @foreach($edit_history_array as $key => $val)
                        <tr style="height: 26px;">
                            <td>{{$val['user_name']}}</td>
                            <td>{{$val['updated_at']}}</td>
                        @endforeach
                        </tr>
                    </table>
                  </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">

        $('#archived-modal').on("show.bs.modal", function(e){
            var roi_id = $(e.relatedTarget).data('roi_id');
            var launch_time = $(e.relatedTarget).data('launch_time');
            var sku = $(e.relatedTarget).data('sku');
            var new_product_planning_process = $(e.relatedTarget).data('new_product_planning_process');
            $('#roi_id').val(roi_id);
            $('#launch_time').val(launch_time);
            $('#sku').val(sku);
            $('#new_product_planning_process').val(new_product_planning_process);
        })

       function change_transport_mode(select_element){
            var transport_mode = select_element.value;
            if(transport_mode == 0){
                $('#unit_price_type').html('元/m<sup>3</sup>');

            }else{
                $('#unit_price_type').html('元/KG');
            }
        }

        $(function() {

            $('.date-picker').datepicker({
                rtl: App.isRTL(),
                format: 'yyyy-mm-dd',
                orientation: 'bottom',
                autoclose: true,
            });

            //如果不加以下代码，点击模态框里的date-picker后，模态框里的所有input元素的值都被清空。
            $('.date-picker').on('show', function(event) {
                event.stopPropagation();
            });

            $('td input').attr('disabled', 'disabled');
        });

        //实现复制链接功能
        $("#copy-btn").click(function() {
            var roi_show_link = $('#roi_show_link').val();
            var e = document.getElementById("roi_show_link");
            e.select(); // 选择对象
            document.execCommand("Copy"); // 执行浏览器复制命令
        })

        //实现导出功能
        $("#export-btn").click(function() {
            location.href='/roi_export_show_page?id=' + '{{$roi['id']}}';
            return false;
        })

    </script>

@endsection
