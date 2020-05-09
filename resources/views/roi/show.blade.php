@extends('layouts.layout')
@section('content')
    <style type="text/css">
        div,table{
            font-size: 12px;
        }
        #sales_table{
            width:1501px;
            border: 1px solid #dddddd;
        }
        #sales_table th{
            text-align: left;
            height: 34px;
            padding-left: 12px;
        }
        #sales_table td{
            text-align: left;
            padding-left: 10px;
            height: 34px;
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

    </style>

    <div style="">
        <div class="portlet light bordered">
            <div style="font-size: 15px">投入产出表</div>
            <div style="height: 30px;"></div>
            <div>
                <span style="padding-right: 20px">产品名称: {{$roi['product_name']}}</span>
                <span style="padding-right: 20px">站点: {{$roi['site']}}</span>
                <span style="padding-right: 20px">预计上线时间: {{$roi['estimated_launch_time']}}</span>
                <span style="padding-right: 20px">SKU: {{$roi['sku']}}</span>
                <span style="padding-right: 20px">项目编号: {{$roi['project_code']}}</span>
                <span style="padding-right: 20px">新品规划流程: {{$roi['new_product_planning_process']}}</span>
            </div>
            <div style="clear:both"></div>
            <div style="height: 15px;"></div>
            <div style="font-size:12px; color: #cccccc;">说明：下表的月份是从上市日起的次月起按第一个月算，以12个月为一个周期</div>
            <div style="height: 5px;"></div>
            <div>
                <table id="sales_table" border="1" cellspacing="0" cellpadding="0">
                    <tr>
                        <th colspan="2" width="200px" style="text-align: center">项目/时间</th>
                        <th width="100px">{{$roi['month_1']}}</th>
                        <th width="100px">{{$roi['month_2']}}</th>
                        <th width="100px">{{$roi['month_3']}}</th>
                        <th width="100px">{{$roi['month_4']}}</th>
                        <th width="100px">{{$roi['month_5']}}</th>
                        <th width="100px">{{$roi['month_6']}}</th>
                        <th width="100px">{{$roi['month_7']}}</td>
                        <th width="100px">{{$roi['month_8']}}</td>
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
                        <td><span>{{$roi['total_sales_volume']}}</span></td>
                    </tr>
                    <tr>
                        <td>售价（外币）</td>
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
                        <td><span>{{$roi['average_price_fc']}}</span></td>
                    </tr>
                    <tr>
                        <td>售价RMB</td>
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
                        <td><span>{{$roi['average_price_rmb']}}</span></td>
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
                        <td><span>{{$roi['total_sales_amount']}}</span></td>
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
                        <td><span>{{$roi['average_promo_rate']}}</span></td>

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
                        <td><span>{{$roi['average_exception_rate']}}</span></td>

                    </tr>
                </table>
            </div>
            <div style="clear:both"></div>
            <div style="height: 25px;"></div>


            <div class="result_div">
                <div style="font-size: 14px;">产品开发及供应链成本</div>

                <div style="height: 15px;"></div>
                <div style="width:1501px">
                    <table id="params_cost_table" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td valign="top" width="750px">
                                <div>运输参数</div>
                                <div style="height: 7px;"></div>
                                <div>
                                    <span style="padding-right: 20px">运输方式: {{$roi['transport_mode']}}</span>
                                    <span style="padding-right: 20px">运输单价: {!! $roi['transport_unit_price'] !!}</span>
                                    <span style="padding-right: 20px">运输天数: {{$roi['transport_days']}}</span>
                                    <span style="padding-right: 20px">关税税率: {{$roi['tariff_rate']}}</span>

                                </div>
                                <div style="height: 15px;"></div>
                                <div>采购参数</div>
                                <div style="height: 7px;"></div>
                                <div>
                                    <span style="padding-right: 20px">单PCS实重(KG): {{$roi['weight_per_pcs']}}</span>
                                    <span style="padding-right: 20px">单PCS体积(cm<sup>3</sup>): {{$roi['volume_per_pcs']}}</span>
                                    <span style="padding-right: 20px">不含税采购价: {{$roi['purchase_price']}}</span>
                                    <span style="padding-right: 20px">MOQ(PCS): {{$roi['moq']}}</span>

                                </div>
                                <div style="height: 7px;"></div>
                                <div>供应商账期: {{$roi['billing_period_type']}}</div>


                            </td>
                            <td valign="top" width="750px">
                                <div>开发成本</div>
                                <div style="height: 15px;"></div>
                                <div>
                                    <span style="padding-right: 20px">ID费用(元): {{$roi['id_fee']}}</span>
                                    <span style="padding-right: 20px">模具费(元): {{$roi['mold_fee']}}</span>
                                    <span style="padding-right: 20px">手板费(元): {{$roi['prototype_fee']}}</span>
                                    <span style="padding-right: 20px">其他费用(元): {{$roi['other_fixed_cost']}}</span>

                                </div>
                                <div style="height: 15px;"></div>
                                <div>其他成本</div>
                                <div style="height: 15px;"></div>
                                <div>
                                    <span style="padding-right: 20px">专利费(元): {{$roi['royalty_fee']}}</span>
                                    <span style="padding-right: 20px">认证费(元): {{$roi['certification_fee']}}</span>

                                </div>
                                <div style="height: 15px;"></div>
                                <div>平台参数</div>
                                <div style="height: 15px;"></div>
                                <div>
                                    <span style="padding-right: 20px">平台佣金(%): {{$roi['commission_rate']}}</span>
                                    <span style="padding-right: 20px">平台操作费(外币/pcs): {{$roi['unit_operating_fee']}}</span>

                                </div>
                            </td>

                        </tr>

                    </table>

                </div>
            </div>
            <div style="height: 30px;"></div>
            <div class="result_div">
                <div style="font-size: 14px;">投入产出分析结果</div>

                <div style="height: 15px;"></div>
                <div style="width:1501px">
                    <table id="result_table" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="25%">底线价格(外币/元): {{$roi['price_floor']}}</td>
                            <td width="25%">库存周转天数(天): {{$roi['inventory_turnover_days']}}</td>
                            <td width="25%">项目利润率(%): {{$roi['project_profitability']}}</td>
                            <td width="25%">单PCS边际利润(元): {{$roi['marginal_profit_per_pcs']}}</td>
                        </tr>
                        <tr>
                            <td>预计投资回收期(月): {{$roi['estimated_payback_period']}}</td>
                            <td>资金周转次数(次): {{$roi['capital_turnover']}}</td>
                            <td>投资回报率ROI(%): {{$roi['roi']}}</td>
                            <td>投资回报额(万元): {{$roi['return_amount']}}</td>
                        </tr>

                    </table>

                </div>
            </div>

        </div>


    </div>

    <script type="text/javascript">

        function fold_cost_details(){
//        alert('dd');
//        alert($('#cost_details_div').html);
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

        $(function() {

            $('.date-picker').datepicker({
                rtl: App.isRTL(),
                format: 'yyyy-mm-dd',
                orientation: 'bottom',
                autoclose: true,
            });

            $('td input').attr('disabled', 'disabled');
        });

    </script>

@endsection
