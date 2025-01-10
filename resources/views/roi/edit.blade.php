@extends('layouts.layout')
@section('crumb')
    <a href="/roi">ROI Analysis</a>
@endsection
@section('content')
    <style type="text/css">
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
        }
        #result_table td{
            text-align: left;
            height: 25px;
        }
        input{
            border: 1px solid #dddddd;
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
        button.dropdown-toggle{
            padding: 2px 12px;
        }
        .bootstrap-select.btn-group .dropdown-menu li.selected a .glyphicon{
            color: blue;
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
                <button type="button" class="disabled-btn" disabled style="width: 80px; margin-right: 10px"><span><i class="fa fa-sign-out"></i></span> 导出</button>
            </div>
            <div style="float: right;">
                <button type="button" class="disabled-btn" disabled style="width: 106px; margin-right: 10px"><span><i class="fa fa-archive"></i></span> 审核通过</button>
            </div>
            <input id="roi_show_link" value="{{ 'www.vleop.com:88/roi/'.$roi['id'] }}"  style="opacity: 0; float: right" readonly>
            <div style="clear:both"></div>
            <div style="height: 20px;"></div>
        </div>
        <div class="col-md-12">

            <form id="roi_form" action="{{ url('/roi/updateRecord') }}" method="post" onsubmit="return validate_form()">
                {{ csrf_field() }}
                {{--{{ method_field('PUT') }}--}}
                <input type="hidden" name="roi_id" id="roi_id" value="{{$roi['id']}}">
                <div class="portlet light bordered" style="text-align: center">
                  <div style="width: 1502px; text-align: left; margin: auto;">
                    <div style="height: 25px;"></div>
                    <div style="font-size: 18px; font-weight: bold">投入产出表</div>
                    <div style="height: 30px;"></div>
                    <div class="first_row_params">
                        <div style="width:200px; float:left;">
                            <div >产品名称
                                <span style="color: #999999;" title="可自定义产品名称，方便同一项目下方案对比区分。如：“乐观-榨汁机”"><i class="fa fa-info-circle"></i></span>
                            </div>
                            <input type="text" name="product_name" id="product_name" style="width:170px;" value="{{$roi['product_name']}}" />
                        </div>
                        <div class="param_cost" style="width:90px;">
                            <div>站点</div>
                            <select name="site" id="site" style="width:60px;">
                                @foreach ($sites as $site)
                                    <option value="{{$site}}" @if($roi['site'] == $site) selected @endif>{{$site}}</option>
                                @endforeach
                            </select>
                        </div>
						<div style="width:90px;float:left;">
							<div>汇率</div>
							<input type="text" name="custom_rate" id="custom_rate" style="width:60px;" value="{{$roi['currency_rate']}}" >
						</div>
                        <div style="width:165px; float:left">
                            <div>预计上线时间
                                <span style="color: #999999;" title="预计新品可正式上线销售的时间"><i class="fa fa-info-circle"></i></span>
                            </div>
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input id="estimated_launch_time" type="text" style="width:125px" class="form-control form-filter input-sm" readonly name="estimated_launch_time" placeholder="Date" value="{{$roi['estimated_launch_time']}}" />
                                <span style="width:20px; height:26px" class="input-group-btn">
                                    <button class="btn btn-sm default time-btn" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div style="width:120px; float:left">
                            <div>项目编号
                                <span style="color: #999999;" title="新品开发项目定义好的项目编号"><i class="fa fa-info-circle"></i></span>
                            </div>
                            <input type="text" name="project_code" id="project_code" value="{{$roi['project_code']}}" style="width:90px;" />
                        </div>
                        <div class="param_cost">
                            <div>选择合作者</div>
                            {{--boostrap-select多选下拉框选中多个值，只会传递最后一个值到后台。这里用一个隐藏的输入框保存多选值--}}
                            <input type="text" id="collaborators" name="collaborators" hidden />
                            <select class="selectpicker show-tick form-control" multiple id="collaboratorsSelect" data-width="205px" title="请选择..." data-selected-text-format="count" data-live-search="true">
                                @foreach ($availableUsers as $key => $value)
                                    <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="param_cost" style="width:130px;">
                            <div>库存周转天数</div>
                            <select name="inventory_turnover_days" style="width:100px;">
                            @foreach ($inventory_turnover_days as $day)
                                <option value="{{$day}}" @if($roi['inventory_turnover_days'] == $day) selected @endif>{{$day}}</option>
                            @endforeach
                            </select>
                        </div>

                        <div class="param_cost">
                            <div>售价(外币)</div>
                            <input type="text" name="sell_price" id="sell_price" style="width:100px;" value="{{$roi['sell_price']}}"/>
                        </div>
                        <div class="" style="margin-left: -100px;float: left;">
                            <div>在库库存维持天数（FBA+FBM）</div>
                            <input class="int_input" type="text" name="Inventory_days" id="Inventory_days" style="width:160px;" value="{{$roi['Inventory_days']}}"/>
                        </div>
                    </div>
                    <div style="clear:both"></div>
                    <div style="height: 15px;"></div>
                    <div style="font-size:12px; color: #cccccc;">说明：下表的月份是从上市日起的当月起按第一个月算，以12个月为一个周期</div>
                    <div style="height: 5px;"></div>
                    <div>
                        <table id="sales_table" border="1" cellspacing="0" cellpadding="0">
                            <tr id="sales_table_th">
                                <th colspan="2" width="200px" style="text-align: center">项目/时间</th>
                                <th width="100px">第1月</th>
                                <th width="100px">第2月</th>
                                <th width="100px">第3月</th>
                                <th width="100px">第4月</th>
                                <th width="100px">第5月</th>
                                <th width="100px">第6月</th>
                                <th width="100px">第7月</th>
                                <th width="100px">第8月</th>
                                <th width="100px">第9月</th>
                                <th width="100px">第10月</th>
                                <th width="100px">第11月</th>
                                <th width="100px">第12月</th>
                                <th width="100px">合计</th>
                            </tr>
                            <tr>
                                <td rowspan="4">销售预测</td>
                                <td style="padding-left: 10px; text-align: left;">预计销量
                                    <span style="color: #999999;" title="预计新品上线后的销量，包含正常销量、推广销量和RSG销量"><i class="fa fa-info-circle"></i></span>
                                </td>
                                <td><input type="text" class="volume_input" name="volume_month_1" id="volume_month_1" value="{{$roi['volume_month_1']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_2" id="volume_month_2" value="{{$roi['volume_month_2']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_3" id="volume_month_3" value="{{$roi['volume_month_3']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_4" id="volume_month_4" value="{{$roi['volume_month_4']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_5" id="volume_month_5" value="{{$roi['volume_month_5']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_6" id="volume_month_6" value="{{$roi['volume_month_6']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_7" id="volume_month_7" value="{{$roi['volume_month_7']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_8" id="volume_month_8" value="{{$roi['volume_month_8']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_9" id="volume_month_9" value="{{$roi['volume_month_9']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_10" id="volume_month_10" value="{{$roi['volume_month_10']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_11" id="volume_month_11" value="{{$roi['volume_month_11']}}" /></td>
                                <td><input type="text" class="volume_input" name="volume_month_12" id="volume_month_12" value="{{$roi['volume_month_12']}}" /></td>
                                <td class="span_td"><span id="total_sales_volume" class="highlight_text">{{$roi['total_sales_volume']}}</span></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 10px; text-align: left">成交价(外币)</td>
                                <!-- fc： foregin currency -->
                                <td><input type="text" class="price_fc_input" name="price_fc_month_1" id="price_fc_month_1" value="{{$roi['price_fc_month_1']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_2" id="price_fc_month_2" value="{{$roi['price_fc_month_2']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_3" id="price_fc_month_3" value="{{$roi['price_fc_month_3']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_4" id="price_fc_month_4" value="{{$roi['price_fc_month_4']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_5" id="price_fc_month_5" value="{{$roi['price_fc_month_5']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_6" id="price_fc_month_6" value="{{$roi['price_fc_month_6']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_7" id="price_fc_month_7" value="{{$roi['price_fc_month_7']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_8" id="price_fc_month_8" value="{{$roi['price_fc_month_8']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_9" id="price_fc_month_9" value="{{$roi['price_fc_month_9']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_10" id="price_fc_month_10" value="{{$roi['price_fc_month_10']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_11" id="price_fc_month_11" value="{{$roi['price_fc_month_11']}}" /></td>
                                <td><input type="text" class="price_fc_input" name="price_fc_month_12" id="price_fc_month_12" value="{{$roi['price_fc_month_12']}}" /></td>
                                <td class="span_td"><span id="average_price_fc" class="highlight_text">{{$roi['average_price_fc']}}</span></td>

                            </tr>
                            <tr>
                                <td style="padding-left: 10px; text-align: left">成交价RMB</td>
                                <td class="span_td"><span id="price_rmb_month_1">{{$roi['price_rmb_month_1']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_2">{{$roi['price_rmb_month_2']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_3">{{$roi['price_rmb_month_3']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_4">{{$roi['price_rmb_month_4']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_5">{{$roi['price_rmb_month_5']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_6">{{$roi['price_rmb_month_6']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_7">{{$roi['price_rmb_month_7']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_8">{{$roi['price_rmb_month_8']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_9">{{$roi['price_rmb_month_9']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_10">{{$roi['price_rmb_month_10']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_11">{{$roi['price_rmb_month_11']}}</span></td>
                                <td class="span_td"><span id="price_rmb_month_12">{{$roi['price_rmb_month_12']}}</span></td>
                                <td class="span_td"><span id="average_price_rmb" class="highlight_text">{{$roi['average_price_rmb']}}</span></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 10px; text-align: left">销售金额</td>
                                <td class="span_td"><span id="sales_amount_month_1">{{$roi['sales_amount_month_1']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_2">{{$roi['sales_amount_month_2']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_3">{{$roi['sales_amount_month_3']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_4">{{$roi['sales_amount_month_4']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_5">{{$roi['sales_amount_month_5']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_6">{{$roi['sales_amount_month_6']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_7">{{$roi['sales_amount_month_7']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_8">{{$roi['sales_amount_month_8']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_9">{{$roi['sales_amount_month_9']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_10">{{$roi['sales_amount_month_10']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_11">{{$roi['sales_amount_month_11']}}</span></td>
                                <td class="span_td"><span id="sales_amount_month_12">{{$roi['sales_amount_month_12']}}</span></td>
                                <td class="span_td"><span id="total_sales_amount" class="highlight_text">{{$roi['total_sales_amount']}}</span></td>
                            </tr>
                            <tr>
                                <td colspan="2">推广率
                                    <span style="color: #999999;" title="推广费用占销售额的比例
包含亚马逊站内广告，站外广告、Deals、测评等费用"><i class="fa fa-info-circle"></i></span>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_1" id="promo_rate_month_1" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_1']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_2" id="promo_rate_month_2" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_2']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_3" id="promo_rate_month_3" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_3']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_4" id="promo_rate_month_4" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_4']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_5" id="promo_rate_month_5" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_5']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_6" id="promo_rate_month_6" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_6']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_7" id="promo_rate_month_7" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_7']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_8" id="promo_rate_month_8" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_8']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_9" id="promo_rate_month_9" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_9']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_10" id="promo_rate_month_10" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_10']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_11" id="promo_rate_month_11" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_11']}}" ><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="promo_rate_month_12" id="promo_rate_month_12" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['promo_rate_month_12']}}" ><span>%</span>
                                    </div>
                                </td>
                             <td class="span_td"><span id="average_promo_rate" class="highlight_text">{{$roi['average_promo_rate']}}</span></td>
                            </tr>
                            <tr>
                                <td colspan="2">异常率
                                    <span style="color: #999999;" title="亚马逊退货、售后等费用占销售额比例"><i class="fa fa-info-circle"></i></span>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_1" id="exception_rate_month_1" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_1']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_2" id="exception_rate_month_2" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_2']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_3" id="exception_rate_month_3" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_3']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_4" id="exception_rate_month_4" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_4']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_5" id="exception_rate_month_5" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_5']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_6" id="exception_rate_month_6" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_6']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_7" id="exception_rate_month_7" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_7']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_8" id="exception_rate_month_8" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_8']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_9" id="exception_rate_month_9" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_9']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_10" id="exception_rate_month_10" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_10']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_11" id="exception_rate_month_11" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_11']}}"><span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="border: 1px solid #dddddd;height: 22px;width: 76px;margin-left: 12px;">
                                        <input class="promo_exception_input" type="text" name="exception_rate_month_12" id="exception_rate_month_12" style="float: left;border: none;width: 60px;height: 20px;" value="{{$roi['exception_rate_month_12']}}"><span>%</span>
                                    </div>
                                </td>
                                <td class="span_td"><span id="average_exception_rate" class="highlight_text">{{$roi['average_exception_rate']}}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div style="clear:both"></div>
                    <div style="height: 25px;"></div>
                    <div class="cost_div">
                        <div style="font-size: 16px; float: left; font-weight: bold">产品开发及供应链成本</div>
                        <div id="expand_icon" style="font-size: 14px; float: right; color:#63C5D1; display: none" onclick="expand_cost_details()">展开 <i class="fa fa-angle-double-down"></i></div>
                        <div style="clear:both"></div>

                        <div id="cost_details_div" style="display: block">
                            <div style="height: 20px;"></div>
                            <div class="bold">平台参数</div>
                            <div style="height: 10px;"></div>
                            <div class="param_cost">
                                <div>平台佣金(%)</div>
                                <div style="border: 1px solid #cccccc;height: 26px;width: 205px;">
                                    <input class="int_or_two_digits_input" type="text" name="commission_rate" id="commission_rate" style="float: left;border: none;width: 189px;height: 23px;" value="{{$roi['commission_rate']}}"><span>%</span>
                                </div>
                            </div>
                            <div class="param_cost">
                                <div>平台操作费(外币/pcs)</div>
                                <input class="int_or_two_digits_input" type="text" name="unit_operating_fee" id="unit_operating_fee" value="{{$roi['unit_operating_fee']}}" />
                            </div>

                            <div class="param_cost">
                                <div>平台</div>
                                <select name="platform" id="platform">
                                    @foreach ($platforms as $k => $v)
                                    <option value="{{$k}}" @if($roi['platform'] == $k) selected @endif>{{$v}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="clear: both"></div>
                            <div style="height: 20px;"></div>
                            <div class="bold">运输参数</div>
                            <div style="height: 10px;"></div>
                            <div class="param_cost">
                                <div>头程运输方式</div>
                                <select style="width: 205px;height:28px; border: 1px solid #dddddd;" name="transport_mode" onchange="change_transport_mode(this)" id="transport_mode">
                                    @foreach ($transportModes as $k => $v)
                                        <option value="{{$k}}" @if($roi['transport_mode'] == $k) selected @endif>{{$v}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="param_cost">
                                <div>头程运输单价</div>
                                <div style="width: 205px;" class="input-group">
                                    <input class="int_or_two_digits_input" type="text" name="transport_unit_price" style="width: 157px" value="{{$roi['transport_unit_price']}}" />
                                    <span id="unit_price_type" style="height:26px; padding: 4px" class="input-group-addon">{!! $roi['transport_unit'] !!}</span>
                                </div>
                            </div>
                            <div class="param_cost">
                                <div>头程运输天数</div>
                                <input class="int_or_two_digits_input" type="text" name="transport_days" id="transport_days" value="{{$roi['transport_days']}}" />
                            </div>
                            <div class="param_cost">
                                <div>关税税率</div>
                                <div style="border: 1px solid #cccccc;height: 26px;width: 205px;">
                                    <input class="int_or_two_digits_input" type="text" name="tariff_rate" id="tariff_rate" style="float: left;border: none;width: 189px;height: 23px;" value="{{$roi['tariff_rate']}}"><span>%</span>
                                </div>
                            </div>
                            <br/><br/><br/><br/>
                            <div class="param_cost">
                                <div>二程运输单价</div>
                                <div style="width: 205px;" class="input-group">
                                    <input class="int_or_two_digits_input" type="text" name="two_transport_unit_price" style="width: 127px" value="{{$roi['two_transport_unit_price']}}"/>
                                    <span style="height:26px; padding: 4px" class="input-group-addon">当地币/pcs</span>
                                </div>
                            </div>
                            <div class="param_cost">
                                <div>二程运输天数</div>
                                <input class="int_or_two_digits_input" type="text" name="two_transport_days" id="two_transport_days" value="{{$roi['two_transport_days']}}"/>
                            </div>
                            <div style="clear: both"></div>
                            <div style="height: 20px;"></div>
                            <div class="bold">采购参数</div>
                            <div style="height: 10px;"></div>
                            <div class="param_cost">
                                <div>单PCS实重(KG)</div>
                                <input class="int_or_two_digits_input" type="text" name="weight_per_pcs" id="weight_per_pcs" value="{{$roi['weight_per_pcs']}}" />
                            </div>
                            <div style="width:220px; float:left;">
                                <div>单PCS体积(cm<sup>3</sup>)</div>
                                <input class="int_or_two_digits_input" type="text" name="volume_per_pcs" id="volume_per_pcs" value="{{$roi['volume_per_pcs']}}" />
                            </div>
                            <div class="param_cost">
                                <div>不含税采购价</div>
                                <input class="int_or_two_digits_input" type="text" name="purchase_price" id="purchase_price" value="{{$roi['purchase_price']}}" />
                            </div>
                            <div class="param_cost">
                                <div>MOQ(PCS)</div>
                                <input class="int_input" type="text" name="moq" id="moq" value="{{$roi['moq']}}" />
                            </div>
                            <div class="param_cost">
                                <div>供应商账期</div>
                                <select name="billing_period_type" id="billing_period_type" style="border: 1px solid #dddddd;">
                                    @foreach ($billingPeriods as $k => $v)
                                        <option value="{{$k}}" @if($roi['billing_period_type'] == $k) selected @endif>{{$v['name']}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="clear: both"></div>
                            <div style="height: 20px;"></div>
                            <div class="bold">固定成本</div>
                            <div style="height: 10px;"></div>
                            <div class="param_cost">
                                <div>ID费用(元)</div>
                                <input class="int_or_two_digits_input" type="text" name="id_fee" id="id_fee" value="{{$roi['id_fee']}}" />
                            </div>
                            <div class="param_cost">
                                <div>模具费(元)</div>
                                <input class="int_or_two_digits_input" type="text" name="mold_fee" id="mold_fee" value="{{$roi['mold_fee']}}" />
                            </div>
                            <div class="param_cost">
                                <div>手板费(元)</div>
                                <input class="int_or_two_digits_input" type="text" name="prototype_fee" id="prototype_fee" value="{{$roi['prototype_fee']}}" />
                            </div>
                            <div class="param_cost">
                                <div>其他费用(元)</div>
                                <input class="int_or_two_digits_input" type="text" name="other_fixed_cost" id="other_fixed_cost" value="{{$roi['other_fixed_cost']}}" />
                            </div>
                            <div style="clear: both"></div>
                            <div style="height: 10px;"></div>
                            <div class="param_cost">
                                <div>专利费(元)</div>
                                <input class="int_or_two_digits_input" type="text" name="royalty_fee" id="royalty_fee" value="{{$roi['royalty_fee']}}" />
                            </div>
                            <div class="param_cost">
                                <div>认证费(元)</div>
                                <input class="int_or_two_digits_input" type="text" name="certification_fee" id="certification_fee" value="{{$roi['certification_fee']}}" />
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
                                            <input type="text" style="width:125px" class="form-control form-filter input-sm" readonly name="project_start_date" placeholder="开始日期" value="{{$roi['project_start_date']}}" />
                                        </div>
                                        <div style="float:left; width:12px; text-align:center">--</div>
                                        <div class="input-group date date-picker pull-left" data-date-format="yyyy-mm-dd">
                                    <span style="width:20px; height:26px" class="input-group-btn">
                                        <button class="btn btn-sm default time-btn" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                            <input type="text" style="width:125px" class="form-control form-filter input-sm" readonly name="project_end_date" placeholder="结束日期" value="{{$roi['project_end_date']}}"  />
                                        </div>

                                    </div>
                                </div>
                                <div class="param_cost">
                                    <div>预计项目总人天数量</div>
                                    <input class="int_input" type="text" name="estimated_man_day" id="estimated_man_day" value="{{$roi['estimated_man_day']}}" />
                                </div>
                                <div class="param_cost">
                                    <div>预计开发人力成本</div>
                                    <input class="int_or_two_digits_input" type="text" name="estimated_labor_cost" value="{{$roi['estimated_labor_cost']}}" />
                                </div>
                                <div class="param_cost">
                                    <div>差旅费用</div>
                                    <input class="int_or_two_digits_input" type="text" name="business_trip_expenses" id="business_trip_expenses" value="{{$roi['business_trip_expenses']}}" />
                                </div>
                                <div class="param_cost">
                                    <div>其他成本</div>
                                    <input class="int_or_two_digits_input" type="text" name="other_project_cost" id="other_project_cost" value="{{$roi['other_project_cost']}}" />
                                </div>
                                <div style="clear: both"></div>
                            </div>
                            <div style="height: 30px;"></div>
                            <div style="font-size: 14px; color:#63C5D1; text-align: center;"><span onclick="fold_cost_details()">收起 <i class="fa fa-angle-double-up"></i></span></div>
                        </div>

                    </div>
                    <div style="height: 25px;"></div>
                    <div style="width: 1501px; text-align: center;">
                        <button type="button" class="common-btn" style="width: 60px" id="analyse-btn">分析</button>
                    </div>
                    <div style="height: 30px;"></div>
                    <div class="result_div">
                        <div style="font-size: 16px; font-weight: bold">投入产出分析结果</div>
                        <div style="height: 15px;"></div>
                        <div style="width:1501px">
                            <table id="result_table" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td><span class="grey_color">投资回收期(月) :</span> <span class="bold" id="estimated_payback_period"></span></td>
                                    <td><span class="grey_color">投资回报额 :</span> <span class="bold" id="return_amount"></span></td>
                                    <td><span class="grey_color">投资回报率 :</span> <span class="bold" id="roi"></span></td>
                                    <td><span class="grey_color">利润率 :</span> <span class="bold" id="project_profitability"></span></td>
                                </tr>

                                <tr>
                                    <td><span class="grey_color">年销售量 :</span> <span class="bold" id="year_sales_volume"></span></td>
                                    <td><span class="grey_color">年销售金额 :</span> <span class="bold" id="year_sales_amount"></span></td>
                                    <td><span class="grey_color">年采购金额 :</span> <span class="bold" id="year_purchase_amount"></span></td>
                                    <td><span class="grey_color">年异常金额 :</span> <span class="bold" id="year_exception_amount"></span></td>
                                </tr>

                                <tr>
                                    <td><span class="grey_color">年推广费 :</span> <span class="bold" id="year_promo"></span></td>
                                    <td><span class="grey_color">年平台佣金 :</span> <span class="bold" id="year_platform_commission"></span></td>
                                    <td><span class="grey_color">年平台操作费 :</span> <span class="bold" id="year_platform_operate"></span></td>
                                    <td><span class="grey_color">年平台仓储费 :</span> <span class="bold" id="year_platform_storage"></span></td>
                                </tr>

                                <tr>
                                    <td><span class="grey_color">年进口税 :</span> <span class="bold" id="year_import_tax"></span></td>
                                    <td><span class="grey_color">年物流费 :</span> <span class="bold" id="year_transport"></span></td>
                                    <td><span class="grey_color">库存周转天数 :</span> <span class="bold" id="inventory_turnover_days"></span></td>
                                    <td><span class="grey_color">资金周转次数 :</span> <span class="bold" id="capital_turnover"></span></td>
                                </tr>

                                <tr>
                                    <td><span class="grey_color">投入资金 :</span> <span class="bold" id="put_cost"></span></td>
                                    <td><span class="grey_color">资金占用成本 :</span> <span class="bold" id="capital_occupy_cost"></span></td>
                                    <td><span class="grey_color">变动成本费用小计 :</span> <span class="bold" id="change_cost"></span></td>
                                    <td><span class="grey_color">边际贡献总额 :</span> <span class="bold" id="contribute_cost_total"></span></td>
                                </tr>

                                <tr>
                                    <td><span class="grey_color">单位平均边际贡献 :</span> <span class="bold" id="marginal_profit_per_pcs"></span></td>
                                    <td><span class="grey_color">固定成本 :</span> <span class="bold" id="total_fixed_cost"></span></td>
                                    <td><span class="grey_color">人力成本 :</span> <span class="bold" id="estimated_labor_cost"></span></td>
                                    <td><span class="grey_color">盈亏临界点(销量) :</span> <span class="bold" id="profit_loss_point"></span></td>
                                </tr>

                                <tr>
                                    <td><span class="grey_color">底限价格 :</span> <span class="bold" id="price_floor"></span></td>
                                </tr>


                            </table>
                        </div>
                    </div>

                    <div style="height: 25px;"></div>
                    <div style="width: 1501px; text-align: center;">
                        <button type="submit" class="common-btn" style="width:100px">保存</button>
                    </div>
                </div>
              </div>
            </form>

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
    var currency_rates = eval(<?php echo json_encode($currency_rates);?>);

	
    function refresh_time() {
        setTimeout(refresh_time, 1000 * 60); //这里的1000表示1秒有1000毫秒
        var roi_id = $('#roi_id').val();
        $.ajax({
            "dataType": 'json',
            "type": "post",
            "url": "/roi_fresh_time",
            "data": {"roi_id": roi_id},
            "success": function (data) {
                //console.log(data);
            }
        });
    }
    //预计上线时间改动的时候，项目/时间显示第n个月的年月，
    $("#estimated_launch_time").change(function(){
        var date = $('#estimated_launch_time').val();
        var data = new Date(date);
        var year = data.getFullYear();
        data.setMonth(data.getMonth()+1, 1)//获取到当前月份,设置月份

        var html = '<th colspan="2" width="200px" style="text-align: center">项目/时间</th>';
        var m = data.getMonth();
        m = m < 10 ? "0" + m : m;
        var y = data.getFullYear();
        if(m==0){
            m = 12;
            y = y-1;
        }
        html += '<th width="100px">'+y+"-"+(m)+'</th>';
        var year_month = '';

        for (var i = 0; i < 11; i++) {
            data.setMonth(data.getMonth() + 1);//每次循环一次 月份值减1
            var m = data.getMonth();
            m = m < 10 ? "0" + m : m;
            var y = data.getFullYear();
            if(m==0){
                m = 12;
                y = y-1;
            }

            year_month = y + "-" + (m);
            html += '<th width="100px">'+year_month+'</th>';
        }
        html += '<th width="100px">合计</th>';
        $('#sales_table_th').html(html);
    })



    $('#collaboratorsSelect').on('changed.bs.select', function(e) {
        var selected_values = $(this).val();
        $('#collaborators').val(selected_values);
    });
	
	$('#site').on('change', function(){
        var site = $(this).val();
        $('#custom_rate').val(myParseFloat(currency_rates[site]));
    });

    $(function() {
        $('.date-picker').datepicker({
            rtl: App.isRTL(),
            format: 'yyyy-mm-dd',
            orientation: 'bottom',
            autoclose: true,
        });

        var collaborators = "{{$roi['collaborators']}}";
        if(collaborators){
            $("#collaboratorsSelect").val(collaborators.split(','));
            $('#collaborators').val(collaborators);
        }

        $("#estimated_launch_time").trigger("change");

        refresh_time();
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

    //实现复制链接功能
    $("#copy-btn").click(function() {
        var roi_show_link = $('#roi_show_link').val();
        var e = document.getElementById("roi_show_link");
        e.select(); // 选择对象
        document.execCommand("Copy"); // 执行浏览器复制命令
    })

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
        var sell_price = $('#sell_price').val().trim();
        if(sell_price == ''){
            $('#sell_price').focus();
            return false;
        }
        var Inventory_days = $('#Inventory_days').val().trim();
        if(Inventory_days == '' || Inventory_days <=0){
            alert("在库库存维持天数（FBA+FBM）必须大于0");
            return false;
        }
    }

    $("#analyse-btn").on('click', function() {
        //检查是否存在没填写的输入框
//            $("input").each(function () {
//                if(this.value == ''){
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
    })

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
    $('#site, #custom_rate').on('change', function(){
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
