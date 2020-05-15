@extends('layouts.layout')
@section('content')
<style type="text/css">
    div,table{
        font-size: 12px;
    }
    #sales_table{
        width:1501px;
        border: 1px solid #dddddd;
        font-size: 12px;
    }
    #sales_table th{
        text-align: left;
        height: 34px;
        padding-left: 12px;
    }
    #sales_table td{
        text-align: center;
        height: 34px;
    }
    .result_div{
        width: 1501px;
        border: 0px solid #dddddd;
        background-color:#F5F7FA;
        padding: 20px;
    }
    #result_table{
        width:1481px;
        border: 0px solid #dddddd;
        font-size: 12px;
    }
    #result_table td{
        text-align: left;
        height: 25px;
    }
    td input{
        width: 76px;
        height:22px;
        border: 1px solid #dddddd;
    }
    .span_td{
        text-align: left !important;
        padding-left: 12px;
    }
    .cost_div{
        width: 1501px;
        border: 1px solid #dddddd;
        padding: 20px;
    }
    .cost_div input,select{
        width: 205px;
        height:26px;
    }
    .first_row_params input,select{
        width: 205px;
        height:26px;
    }
    .param_cost{
        width:220px;
        float:left;
    }
    .time-btn{
        padding-left: 6px;
        padding-right: 6px;
        padding-top: 3px;
        padding-bottom: 3px;
    }
    /*.analyse-btn{*/
        /*background-color: #63C5D1;*/
        /*font-size: 14px;*/
        /*text-align: center;*/
        /*width: 60px;*/
        /*height: 30px;*/
        /*border-radius: 5px !important;*/
    /*}*/
    /*.save-btn{*/
        /*background-color: #63C5D1;*/
        /*font-size: 14px;*/
        /*text-align: center;*/
        /*width: 100px;*/
        /*height: 30px;*/
        /*border-radius: 5px !important;*/
    /*}*/

    .common-btn{
        background-color: #63C5D1;
        color: #ffffff;
        font-size: 14px;
        text-align: center;
        width: 60px;
        height: 30px;
        border-radius: 5px !important;
    }

</style>

<div>
    <form id="roi_form" action="{{ url('roi') }}" method="post" onsubmit="return validate_form()">
    {{ csrf_field() }}

    <div class="portlet light bordered">
      <div style="width: 1502px; text-align: left; margin: auto;">
        <div style="height: 25px;"></div>
        <div style="font-size: 15px">投入产出表</div>
        <div style="height: 30px;"></div>
        <div class="first_row_params">
            <div style="width:315px; float:left;">
                <div>产品名称</div>
                <input type="text" name="product_name" id="product_name" style="width:300px;" />
            </div>
            <div class="param_cost">
                <div>站点</div>
                <select name="site" id="site">
                    @foreach ($sites as $site)
                        <option value="{{$site}}">{{$site}}</option>
                    @endforeach
                </select>
            </div>
            <div style="width:165px; float:left">
                <div>预计上线时间</div>
                <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                    <input type="text" style="width:125px" class="form-control form-filter input-sm" readonly name="estimated_launch_time" placeholder="Date" value="{{date('Y-m',strtotime('+30days')).'-01'}}" />
                    <span style="width:20px; height:26px" class="input-group-btn">
                        <button class="btn btn-sm default time-btn" type="button">
                            <i class="fa fa-calendar"></i>
                        </button>
                    </span>
                </div>
            </div>
            <div class="param_cost">
                <div>项目编号</div>
                <input type="text" name="project_code" id="project_code" />
            </div>
        </div>
        <div style="clear:both"></div>
        <div style="height: 15px;"></div>
        <div style="font-size:12px; color: #cccccc;">说明：下表的月份是从上市日起的次月起按第一个月算，以12个月为一个周期</div>
        <div style="height: 5px;"></div>
        <div>
            <table id="sales_table" border="1" cellspacing="0" cellpadding="0">
                <tr>
                    <th colspan="2" width="200px" style="text-align: center">项目/时间</th>
                    <th width="100px">第1月</th>
                    <th width="100px">第2月</th>
                    <th width="100px">第3月</th>
                    <th width="100px">第4月</th>
                    <th width="100px">第5月</th>
                    <th width="100px">第6月</th>
                    <th width="100px">第7月</td>
                    <th width="100px">第8月</td>
                    <th width="100px">第9月</th>
                    <th width="100px">第10月</th>
                    <th width="100px">第11月</th>
                    <th width="100px">第12月</th>
                    <th width="100px">合计</th>
                </tr>
                <tr>
                    <td rowspan="4">销售预测</td>
                    <td style="padding-left: 10px; text-align: left;">预计销量</td>
                    <td><input type="text" class="volume_input" name="volume_month_1" id="volume_month_1" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_2" id="volume_month_2" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_3" id="volume_month_3" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_4" id="volume_month_4" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_5" id="volume_month_5" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_6" id="volume_month_6" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_7" id="volume_month_7" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_8" id="volume_month_8" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_9" id="volume_month_9" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_10" id="volume_month_10" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_11" id="volume_month_11" /></td>
                    <td><input type="text" class="volume_input" name="volume_month_12" id="volume_month_12" /></td>
                    <td class="span_td"><span id="total_sales_volume"></span></td>
                </tr>
                <tr>
                    <td style="padding-left: 10px; text-align: left">售价（外币）</td>
                    <!-- fc： foregin currency -->
                    <td><input type="text" class="price_fc_input" name="price_fc_month_1" id="price_fc_month_1" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_2" id="price_fc_month_2" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_3" id="price_fc_month_3" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_4" id="price_fc_month_4" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_5" id="price_fc_month_5" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_6" id="price_fc_month_6" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_7" id="price_fc_month_7" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_8" id="price_fc_month_8" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_9" id="price_fc_month_9" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_10" id="price_fc_month_10" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_11" id="price_fc_month_11" /></td>
                    <td><input type="text" class="price_fc_input" name="price_fc_month_12" id="price_fc_month_12" /></td>
                    <td class="span_td"><span id="average_price_fc"></span></td>

                </tr>
                <tr>
                    <td style="padding-left: 10px; text-align: left">售价RMB</td>
                    <td class="span_td"><span id="price_rmb_month_1"></span></td>
                    <td class="span_td"><span id="price_rmb_month_2"></span></td>
                    <td class="span_td"><span id="price_rmb_month_3"></span></td>
                    <td class="span_td"><span id="price_rmb_month_4"></span></td>
                    <td class="span_td"><span id="price_rmb_month_5"></span></td>
                    <td class="span_td"><span id="price_rmb_month_6"></span></td>
                    <td class="span_td"><span id="price_rmb_month_7"></span></td>
                    <td class="span_td"><span id="price_rmb_month_8"></span></td>
                    <td class="span_td"><span id="price_rmb_month_9"></span></td>
                    <td class="span_td"><span id="price_rmb_month_10"></span></td>
                    <td class="span_td"><span id="price_rmb_month_11"></span></td>
                    <td class="span_td"><span id="price_rmb_month_12"></span></td>
                    <td class="span_td"><span id="average_price_rmb"></span></td>
                </tr>
                <tr>
                    <td style="padding-left: 10px; text-align: left">销售金额</td>
                    <td class="span_td"><span id="sales_amount_month_1"></span></td>
                    <td class="span_td"><span id="sales_amount_month_2"></span></td>
                    <td class="span_td"><span id="sales_amount_month_3"></span></td>
                    <td class="span_td"><span id="sales_amount_month_4"></span></td>
                    <td class="span_td"><span id="sales_amount_month_5"></span></td>
                    <td class="span_td"><span id="sales_amount_month_6"></span></td>
                    <td class="span_td"><span id="sales_amount_month_7"></span></td>
                    <td class="span_td"><span id="sales_amount_month_8"></span></td>
                    <td class="span_td"><span id="sales_amount_month_9"></span></td>
                    <td class="span_td"><span id="sales_amount_month_10"></span></td>
                    <td class="span_td"><span id="sales_amount_month_11"></span></td>
                    <td class="span_td"><span id="sales_amount_month_12"></span></td>
                    <td class="span_td"><span id="total_sales_amount"></span></td>
                </tr>
                <tr>
                    <td colspan="2">推广率</td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_1" id="promo_rate_month_1" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_2" id="promo_rate_month_2" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_3" id="promo_rate_month_3" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_4" id="promo_rate_month_4" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_5" id="promo_rate_month_5" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_6" id="promo_rate_month_6" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_7" id="promo_rate_month_7" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_8" id="promo_rate_month_8" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_9" id="promo_rate_month_9" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_10" id="promo_rate_month_10" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_11" id="promo_rate_month_11" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="promo_rate_month_12" id="promo_rate_month_12" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td class="span_td"><span id="average_promo_rate"></span></td>
                </tr>
                <tr>
                    <td colspan="2">异常率</td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_1" id="exception_rate_month_1" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_2" id="exception_rate_month_2" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_3" id="exception_rate_month_3" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_4" id="exception_rate_month_4" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_5" id="exception_rate_month_5" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_6" id="exception_rate_month_6" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_7" id="exception_rate_month_7" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_8" id="exception_rate_month_8" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_9" id="exception_rate_month_9" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_10" id="exception_rate_month_10" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_11" id="exception_rate_month_11" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                            <input class="promo_exception_input" type="text" name="exception_rate_month_12" id="exception_rate_month_12" style="float: left;border: none;width: 60px;height: 20px;"><span>%</span>
                        </div>
                    </td>
                    <td class="span_td"><span id="average_exception_rate"></span></td>
                </tr>
            </table>
        </div>
        <div style="clear:both"></div>
        <div style="height: 25px;"></div>
        <div class="cost_div">
            <div style="font-size: 14px; float: left;">产品开发及供应链成本</div>
            <div id="expand_icon" style="font-size: 14px; float: right; color:#63C5D1; display: none" onclick="expand_cost_details()">展开 <i class="fa fa-angle-double-down"></i></div>
            <div style="clear:both"></div>

            <div id="cost_details_div" style="display: block">
                <div style="height: 20px;"></div>
                <div style="font-size: 13px;">平台参数</div>
                <div style="height: 10px;"></div>
                <div class="param_cost">
                    <div>平台佣金(%)</div>
                    <div style="border: 1px solid #cccccc;height: 26px;width: 205px;">
                        <input class="int_or_two_digits_input" type="text" name="commission_rate" id="commission_rate" style="float: left;border: none;width: 189px;height: 23px;"><span>%</span>
                    </div>
                </div>
                <div class="param_cost">
                    <div>平台操作费(外币/pcs)</div>
                    <input class="int_or_two_digits_input" type="text" name="unit_operating_fee" id="unit_operating_fee" />
                </div>
                <div style="clear: both"></div>
                <div style="height: 20px;"></div>
                <div style="font-size: 13px">运输参数</div>
                <div style="height: 10px;"></div>
                <div class="param_cost">
                    <div>运输方式</div>
                    <select style="width: 205px;height:28px" name="transport_mode" onchange="change_transport_mode(this)" id="transport_mode">
                        @foreach ($transportModes as $k => $v)
                        <option value="{{$k}}">{{$v}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="param_cost">
                    <div>运输单价</div>
                    <div style="width: 205px;" class="input-group">
                        <input class="int_or_two_digits_input" type="text" name="transport_unit_price" style="width: 157px"/>
                        <span id="unit_price_type" style="height:26px; padding: 4px" class="input-group-addon">元/m<sup>3</sup></span>
                    </div>
                </div>
                <div class="param_cost">
                    <div>运输天数</div>
                    <input class="int_or_two_digits_input" type="text" name="transport_days" id="transport_days" />
                </div>
                <div class="param_cost">
                    <div>关税税率</div>
                    <div style="border: 1px solid #cccccc;height: 26px;width: 205px;">
                        <input class="int_or_two_digits_input" type="text" name="tariff_rate" id="tariff_rate" style="float: left;border: none;width: 189px;height: 23px;"><span>%</span>
                    </div>
                </div>
                <div style="clear: both"></div>
                <div style="height: 20px;"></div>
                <div style="font-size: 13px">采购参数</div>
                <div style="height: 10px;"></div>
                <div class="param_cost">
                    <div>单PCS实重(KG)</div>
                    <input class="int_or_two_digits_input" type="text" name="weight_per_pcs" id="weight_per_pcs" />
                </div>
                <div style="width:220px; float:left;">
                    <div>单PCS体积(cm<sup>3</sup>)</div>
                    <input class="int_or_two_digits_input" type="text" name="volume_per_pcs" id="volume_per_pcs" />
                </div>
                <div class="param_cost">
                    <div>不含税采购价</div>
                    <input class="int_or_two_digits_input" type="text" name="purchase_price" id="purchase_price" />
                </div>
                <div class="param_cost">
                    <div>MOQ(PCS)</div>
                    <input class="int_input" type="text" name="moq" id="moq" />
                </div>
                <div class="param_cost">
                    <div>供应商账期</div>
                    <select name="billing_period_type" id="billing_period_type">
                        @foreach ($billingPeriods as $k => $v)
                        <option value="{{$k}}">{{$v['name']}}</option>
                        @endforeach
                    </select>
                </div>

                <div style="clear: both"></div>
                <div style="height: 20px;"></div>
                <div style="font-size: 13px">固定成本</div>
                <div style="height: 10px;"></div>
                <div class="param_cost">
                    <div>ID费用(元)</div>
                    <input class="int_or_two_digits_input" type="text" name="id_fee" id="id_fee" />
                </div>
                <div class="param_cost">
                    <div>模具费(元)</div>
                    <input class="int_or_two_digits_input" type="text" name="mold_fee" id="mold_fee" />
                </div>
                <div class="param_cost">
                    <div>手板费(元)</div>
                    <input class="int_or_two_digits_input" type="text" name="prototype_fee" id="prototype_fee" />
                </div>
                <div class="param_cost">
                    <div>其他费用(元)</div>
                    <input class="int_or_two_digits_input" type="text" name="other_fixed_cost" id="other_fixed_cost" />
                </div>
                <div style="clear: both"></div>
                <div style="height: 10px;"></div>
                <div class="param_cost">
                    <div>专利费(元)</div>
                    <input class="int_or_two_digits_input" type="text" name="royalty_fee" id="royalty_fee" />
                </div>
                <div class="param_cost">
                    <div>认证费(元)</div>
                    <input class="int_or_two_digits_input" type="text" name="certification_fee" id="certification_fee" />
                </div>
                <div style="clear: both"></div>
                <div style="height: 20px;"></div>
                <div style="display: none">
                    <div style="font-size: 13px">前期开发投入</div>
                    <div style="height: 10px;"></div>
                    <div style="float:left; width: 328px;">
                        <div>项目起止时间</div>
                        <div class="pull-left">
                            <div class="input-group date date-picker pull-left" data-date-format="yyyy-mm-dd">
                                <span style="width:20px; height:26px" class="input-group-btn">
                                    <button class="btn btn-sm default time-btn" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                                <input type="text" style="width:125px" class="form-control form-filter input-sm" readonly name="project_start_date" placeholder="开始日期" value="" />
                            </div>
                            <div style="float:left; width:12px; text-align:center">--</div>
                            <div class="input-group date date-picker pull-left" data-date-format="yyyy-mm-dd">
                                <span style="width:20px; height:26px" class="input-group-btn">
                                    <button class="btn btn-sm default time-btn" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                                <input type="text" style="width:125px" class="form-control form-filter input-sm" readonly name="project_end_date" placeholder="结束日期" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="param_cost">
                        <div>预计项目总人天数量</div>
                        <input class="int_input" type="text" name="estimated_man_day" id="estimated_man_day" value="0"/>
                    </div>
                    <div class="param_cost">
                        <div>预计开发人力成本</div>
                        <input class="int_or_two_digits_input" type="text" name="estimated_labor_cost" id="estimated_labor_cost" value="0"/>
                    </div>
                    <div class="param_cost">
                        <div>差旅费用</div>
                        <input class="int_or_two_digits_input" type="text" name="business_trip_expenses" id="business_trip_expenses" value="0"/>
                    </div>
                    <div class="param_cost">
                        <div>其他成本</div>
                        <input class="int_or_two_digits_input" type="text" name="other_project_cost" id="other_project_cost" value="0"/>
                    </div>
                    <div style="clear: both"></div>
                </div>
                <div style="height: 30px;"></div>
                <div style="font-size: 14px; color:#63C5D1; text-align: center;"><span onclick="fold_cost_details()">收起 <i class="fa fa-angle-double-up"></i></span></div>
            </div>

        </div>
        <div style="height: 25px;"></div>
        <div style="width: 1501px; text-align: center;">
            <button type="button" class="common-btn" style="width:60px" id="analyse-btn">分析</button>
        </div>
        <div style="height: 30px;"></div>
        <div class="result_div">
            <div style="font-size: 14px;">投入产出分析结果</div>
            <div style="height: 15px;"></div>
            <div style="width:1501px">
                <table id="result_table" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="25%">底线价格(外币/元) : <span id="price_floor"></span></td>
                        <td width="25%">库存周转天数(天) : <span id="inventory_turnover_days"></span></td>
                        <td width="25%">项目利润率(%) : <span id="project_profitability"></span></td>
                        <td width="25%">单PCS边际利润(元) : <span id="marginal_profit_per_pcs"></span></td>
                    </tr>
                    <tr>
                        <td>预计投资回收期(月) : <span id="estimated_payback_period"></span></td>
                        <td>资金周转次数(次) : <span id="capital_turnover"></span></td>
                        <td>投资回报率ROI(%) : <span id="roi"></span></td>
                        <td>投资回报额(万元) : <span id="return_amount"></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <div style="height: 25px;"></div>
        <div style="width: 1501px; text-align: center;">
            <button type="submit" class="common-btn" style="width: 100px">保存</button>
        </div>
      </div>
    </div>
    </form>
</div>

<script type="text/javascript">
    var currency_rates = eval(<?php echo json_encode($currency_rates);?>);

    $(function() {
        $('.date-picker').datepicker({
            rtl: App.isRTL(),
            format: 'yyyy-mm-dd',
            orientation: 'bottom',
            autoclose: true,
        });
    });

    function fold_cost_details(){
        $('#cost_details_div').hide();
        $('#expand_icon').show();
    }

    function expand_cost_details(){
        $('#cost_details_div').show();
        $('#expand_icon').hide();
    }

    function change_transport_mode(select_element){
        var transport_mode = select_element.value;
        if(transport_mode == 0){
            $('#unit_price_type').html('元/m<sup>3</sup>');

        }else{
            $('#unit_price_type').html('元/KG');
        }
    }

    function validate_form(){
        var product_name = $('#product_name').val().trim();
        if(product_name == ''){
            //alert("Product name cannot be empty.");
            $('#product_name').focus();
            return false;
        }
        var total_sales_volume = $('#total_sales_volume').text();
        if(total_sales_volume == '' || total_sales_volume == 0){
            alert("Total sales volume cannot be 0.");
            return false;
        }
        var total_sales_amount = $('#total_sales_amount').text();
        if(total_sales_amount == '' || total_sales_amount == '0.00'){
            alert("Total sales amount cannot be 0.");
            return false;
        }
        var tariff_rate = $('#tariff_rate').val();
        if(tariff_rate == '' || tariff_rate == '0.00'){
            alert("Tariff rate cannot be 0.");
            $('#tariff_rate').focus();
            return false;
        }
    }

    $("#analyse-btn").on('click', function() {
        //检查是否存在没填写的输入框
//            $("#input").each(function () {
//                if(this.value.trim() == ''){
//                    this.focus();
//                    return false;
//                }
//            })

        var total_sales_volume = $('#total_sales_volume').text();
        if(total_sales_volume == '' || total_sales_volume == 0){
            alert("total sales volume cannot be 0.");
            return false;
        }
        var total_sales_amount = $('#total_sales_amount').text();
        if(total_sales_amount == '' || total_sales_amount == '0.00'){
            alert("total sales amount cannot be 0.");
            return false;
        }
        var tariff_rate = $('#tariff_rate').val();
        if(tariff_rate == '' || tariff_rate == '0.00'){
            alert("tariff rate cannot be 0.");
            $('#tariff_rate').focus();
            return false;
        }

        $.ajax({
            type: 'post',
            url: '/roi/analyse',
            data:$("#roi_form").serialize(),
            dataType: 'json',
            success: function (res) {
                if (res) {
                    var updateData = res.updateAjaxData;
                    for(var key in updateData){
                        $('#' + key).text(updateData[key]);
                    }
                    return false;
                }
                else {
                    //操作失败
                    alert('Failed');
                }
            },
            error: function (res){
            }
        })

        return false;
    });

    //只允许输入数字
    $('.int_input').on('input', function(){
        var a = $(this).val().replace(/[^\d]/g,'');
        $(this).val(a);
    });
    $('.int_input').on('blur', function(){
        //如果输入的是： 012，输入框失去焦点时，自动变成12
        var a = myParseInt($(this).val());
//        if(a == 0){
//            a = "";
//        }
        $(this).val(a);

    });

    //只能输入整数或者1到2位小数
    $('.int_or_two_digits_input').on('input', function(){
        var a = intOrTwoDigits($(this).val());
        $(this).val(a);
    });
    
    //只允许输入数字，自动计算
    $('.volume_input').on('input', function(){
        var a = $(this).val().replace(/[^\d]/g,'');
        $(this).val(a);

        calculate_results();
    });
    $('.volume_input').on('blur', function(){
        var a = myParseInt($(this).val());
        $(this).val(a);
    });

    //只能输入整数或者1到2位小数，自动计算
    $('.price_fc_input').on('input', function(){
        var a = intOrTwoDigits($(this).val());
        $(this).val(a);

        calculate_results();
    });

    //只能输入整数或者1到2位小数，自动计算
    $('.promo_exception_input').on('input', function(){
        var a = intOrTwoDigits($(this).val());
        $(this).val(a);

        calculate_results();
    });

    //当切换站点时，自动计算
    $('#site').on('change', function(){
        calculate_results();
    });

    function calculate_results(){
        var total_sales_volume = 0;
        var total_promo_amount = 0;
        var total_exception_amount = 0;
        $('.volume_input').each(function(i,val){
            //没有输入值时，输入框的值为"".
            total_sales_volume += myParseInt(val.value.trim());
        });
        $('#total_sales_volume').text(total_sales_volume);

        var sales_amount_month_array=new Array();
        var site = $('#site').val();
        var currency_rate = myParseFloat(currency_rates[site]);
        for(var i=1; i<=12; i++){
            var price_fc_month_i = myParseFloat($('#price_fc_month_' + i).val().trim());
            var volume_month_i = myParseInt($('#volume_month_' + i).val().trim())
            var price_rmb_month_i = currency_rate * price_fc_month_i;
            sales_amount_month_array[i] = volume_month_i * price_rmb_month_i;
            $('#price_rmb_month_' + i).text(myToFixedTwo(price_rmb_month_i));
            $('#sales_amount_month_' + i).text(myToFixedTwo(sales_amount_month_array[i]));

            var promo_rate_month_i = myParseFloat($('#promo_rate_month_' + i).val().trim()) / 100;
            total_promo_amount += sales_amount_month_array[i] * promo_rate_month_i;
            var exception_rate_month_i = myParseFloat($('#exception_rate_month_' + i).val().trim()) / 100;
            total_exception_amount += sales_amount_month_array[i] * exception_rate_month_i;
        }

        var total_sales_amount = 0;
        for(var i=1; i<=12; i++){
            total_sales_amount += sales_amount_month_array[i];
        }
        $('#total_sales_amount').text(myToFixedTwo(total_sales_amount));

        if(total_sales_volume == 0){
            $('#average_price_rmb').text('0.00');
            $('#average_price_fc').text('0.00');
        }else{
            var average_price_rmb = total_sales_amount / total_sales_volume;
            var average_price_fc = average_price_rmb / currency_rate;
            $('#average_price_rmb').text(myToFixedTwo(average_price_rmb));
            $('#average_price_fc').text(myToFixedTwo(average_price_fc));
        }

        if(Math.abs(total_sales_amount) < 0.001){
            $('#average_promo_rate').text('0.00');
            $('#average_exception_rate').text('0.00');
        }else{
            var average_promo_rate = total_promo_amount / total_sales_amount;
            var average_exception_rate = total_exception_amount / total_sales_amount;
            $('#average_promo_rate').text(toPercentage(average_promo_rate));
            $('#average_exception_rate').text(toPercentage(average_exception_rate));
        }
    }

    //只能输入整数或者1到2位小数
    function intOrTwoDigits(v){
        var regStrs = [
            ['^0(\\d+)$', '$1'], //禁止录入整数部分两位以上，但首位为0
            ['[^\\d\\.]+$', ''], //禁止录入任何非数字和点
            ['\\.(\\d?)\\.+', '.$1'], //禁止录入两个以上的点
            ['^(\\d+\\.\\d{2}).+', '$1'] //禁止录入小数点后两位以上
        ];
        for (i = 0; i < regStrs.length; i++) {
            var reg = new RegExp(regStrs[i][0]);
            v = v.replace(reg, regStrs[i][1]);
        }
        return v;
    }
        
    //小数转成百分数
    function toPercentage(num){
        return myToFixedTwo(num*100) + '%';
    }

    function myParseInt(num){
        if(num){
            return parseInt(num);
        }
        return 0;
    }

    function myParseFloat(num){
        if(num){
            return parseFloat(num);
        }
        return parseFloat(0);
    }

    function myToFixedSix(num){
        if(num){
            return parseFloat(num).toFixed(6);
        }
        return parseFloat(0).toFixed(6);
    }

    function myToFixedTwo(num){
        if(num){
            return parseFloat(num).toFixed(2);
        }
        return parseFloat(0).toFixed(2);
    }

</script>

@endsection
