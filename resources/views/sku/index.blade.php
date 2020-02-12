@extends('layouts.layout')
@section('label', 'Daily Sales Report')
@section('content')
<link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
<style>
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;
	}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 10px 2px;
}
th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
.progress-bar.green-sharp,.progress-bar.red-haze,.progress-bar.blue-sharp{
color:#000 !important;
}
table{ 
table-layout:fixed; 
}
td.strategy_s,td.keyword_s,td.ranking_s{       
text-overflow:ellipsis; 
-moz-text-overflow: ellipsis; 
overflow:hidden; 
white-space: nowrap;      
}  
.table-head{padding-right:17px;background-color:#f3f4f6;color:#000;}
.table-body{width:100%; max-height:550px;overflow-y:scroll;}
.table-head table,.table-body table{width:100%;}
table .head{ 
text-align:center;
vertical-align:middle;
background:#fff2cc;
font-weight:bold;
}
.table{margin-bottom:0px;}
.widget-thumb .widget-thumb-body .widget-thumb-body-stat {font-size:20px;}
.widget-thumb .widget-thumb-wrap .widget-thumb-icon{width:50px;height:50px;line-height:30px;}
.widget-thumb .widget-thumb-heading{color:#666; margin-bottom:10px;}
.dashboard-stat2 { margin-bottom:0px;margin-top: 8px;}
.dashboard-stat2 .display {
    margin-bottom: 10px;
}
.dashboard-stat2 .display .number h3 {

    font-size: 20px;
    font-weight: bold;
}
.dashboard-stat2 .display .number h3 > small {
    font-size: 14px;
}
    </style>
    <h1 class="page-title font-red-intense"> Daily Sales Report
        
    </h1>
	
	
	<div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
							
							
					<div class="table-toolbar">
                    <form role="form" action="{{url('skus')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">

                        <div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_start" placeholder="Date" value="{{$date_start}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
						
						<div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_end" placeholder="Date" value="{{$date_end}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
                       
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]">
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}" <?php if(in_array($user_id,$s_user_id)) echo 'selected'; ?>>{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="bgbu">
                                        <option value="">Select BGBU</option>
										<?php 
										$bg='';
										foreach($teams as $team){ 
											$selected = '';
											if($bgbu==($team->bg.'_')) $selected = 'selected';
											
											if($bg!=$team->bg) echo '<option value="'.$team->bg.'_" '.$selected.'>'.$team->bg.'</option>';	
											$bg=$team->bg;
											$selected = '';
											if($bgbu==($team->bg.'_'.$team->bu)) $selected = 'selected';
											if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'" '.$selected.'>'.$team->bg.' - '.$team->bu.'</option>';
										} ?>
                                    </select>
						</div>	
						

						 <div class="col-md-1">
						<select class="form-control form-filter input-sm" name="site" id="site">
									<option value="">Select Site</option>
                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}" <?php if($v==$s_site) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						 <div class="col-md-1">
						<select class="form-control form-filter input-sm" name="level" id="level">
									<option value="">Level</option>
										<option value="S" <?php if('S'==$s_level) echo 'selected'; ?>>S</option>
                                        <option value="A" <?php if('A'==$s_level) echo 'selected'; ?>>A</option>
										<option value="B" <?php if('B'==$s_level) echo 'selected'; ?>>B</option>
										<option value="C" <?php if('C'==$s_level) echo 'selected'; ?>>C</option>
										<option value="D" <?php if('D'==$s_level) echo 'selected'; ?>>D</option>
                                    </select>
						</div>
						
						<div class="col-md-1">
						<input type="text" class="form-control form-filter input-sm" name="sku" placeholder="SKU OR ASIN" value ="{{array_get($_REQUEST,'sku')}}">
                                       
						</div>
						<div class="col-md-1">
							
										<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>
						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						
						
						
						
						
						
						
							
						
					</div>

                    </form>
					@permission('sales-report-export')
					<button id="vl_list_export" class="btn sbold blue"> Export
                                    <i class="fa fa-download"></i>
                          </button>
						  @endpermission
                </div>
                    <div class="table-container">
					{{ $datas->appends(['date_start' => $date_start,'date_end' => $date_end,'site' => $s_site,'user_id' => $s_user_id,'level' => $s_level,'bgbu' => $bgbu,'sku' => $sku])->links() }} 
					
					
					@foreach ($datas as $data)
						<div class="table-head">
						<table class="table table-bordered ">
 
						 <colgroup>
			<col width="9%"></col>
			<col width="7%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="7%"></col>
			<col width="7%"></col>
			<col width="10%"></col>
			</colgroup>	
																	
						<?php 
						$curr_date = date('Ymd',strtotime($date_end));
						if($data->target_sales>0){
							$complete_sales = round($data->sales/$data->target_sales*100,2);
						}elseif($data->target_sales<0){
							$complete_sales = round((2-$data->sales/$data->target_sales)*100,2);
						}else{
							$complete_sales =0;
						}
						
						if($data->target_amount>0){
							$complete_amount = round($data->amount/$data->target_amount*100,2);
						}elseif($data->target_amount<0){
							$complete_amount = round((2-$data->amount/$data->target_amount)*100,2);
						}else{
							$complete_amount =0;
						}
						
						if($data->target_profit>0){
							$complete_profit = round($data->profit/$data->target_profit*100,2);
						}elseif($data->target_profit<0){
							$complete_profit = round((2-$data->profit/$data->target_profit)*100,2);
						}else{
							$complete_profit =0;
						}
						?>
						  <tr class="head">
							<td style="font-weight: bold;">ASIN</td>
							<td style="font-weight: bold;">Site</td>
							<td style="font-weight: bold;">SKU</td>
							<td style="font-weight: bold;">Status</td>
							<td style="font-weight: bold;">Level</td>
							<td style="font-weight: bold;">BG</td>
							<td style="font-weight: bold;">BU</td>
							<td style="font-weight: bold;">Seller</td>
							<td colspan="3" style="font-weight: bold;"> Main Keywords </td>
							<td colspan="4" style="font-weight: bold;">Description</td>
						  </tr>
						  <tr>
							<td style="word-wrap: break-word;"><a href="https://{{$data->site}}/dp/{{strip_tags(str_replace('&nbsp;','',$data->asin))}}" target="_blank">{{strip_tags(str_replace('&nbsp;','',$data->asin))}}</a></td>
							<td>{{strtoupper(substr(strrchr($data->site, '.'), 1))}}</td>
							<td>{!!str_replace(',','<br />',$data->item_code)!!}</td>
							<td>{!!($data->status)?'<span class="btn btn-success btn-xs">Reserved</span>':'<span class="btn btn-danger btn-xs">Eliminate</span>'!!}</td>
							<td>{{((($data->pro_status) === '0')?'S':$data->pro_status)}}</td>
							
							<td >{{$data->bg}}</td>
							<td >{{$data->bu}}</td>
							<td > {{array_get($users,$data->sap_seller_id,$data->sap_seller_id)}} </td>
							<td colspan="3" class="keyword_s"><a class="sku_keywords" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$curr_date}}-keywords" data-pk="{{$data->site.'-'.$data->asin.'-'.$curr_date}}-keywords" data-type="text"> {{$data->last_keywords}} </a></td>
							
							
							<td colspan="4">{!!str_replace(',','<br />',$data->item_name)!!}</td>
						  </tr>
						  
						  
						  
						  </table>
						  </div>
	
						  <div class="table-head">
						  <table class="table table-bordered">
						  <colgroup>
			<col width="9%"></col>
			<col width="7%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="7%"></col>
			<col width="7%"></col>
			<col width="10%"></col>
			</colgroup>	
			<tr class="head">
							<td style="font-weight: bold;">Date</td>
							<td style="font-weight: bold;">Rank</td>
							<td style="font-weight: bold;">Rating</td>
							<td style="font-weight: bold;">Reviews</td>
							<td style="font-weight: bold;">Sales</td>
							<td style="font-weight: bold;">Price</td>
							<td style="font-weight: bold;">Sessions</td>
							<td style="font-weight: bold;">Conversion</td>
							<td style="font-weight: bold;">FBA</td>
							<td style="font-weight: bold;">FBA Tran</td>
							<td style="font-weight: bold;">FBM</td>
							<td style="font-weight: bold;">Total</td>
							<td style="font-weight: bold;">FBA Keep</td>
							<td style="font-weight: bold;">Total Keep</td>
							<td style="font-weight: bold;">Strategy</td>
						  </tr>
						  </table>
						  </div>
						  <div class="table-body">
						  <table class="table table-bordered">
						  <colgroup>
			<col width="9%"></col>
			<col width="7%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="7%"></col>
			<col width="7%"></col>
			<col width="10%"></col>
			</colgroup>	
						<?php 
						$sales_data = $price_data = [];
						$sales_sum = $price_sum =0;
						$week_end=date('Ymd',strtotime($date_start));
						$week=date('Ymd',strtotime($date_end));
						while($week>=$week_end){
						$details = $data->details;
						if(isset($details[$week]['sales'])){
							$sales_data[] = intval($details[$week]['sales']);
							$sales_sum+=intval($details[$week]['sales']);
						}
						if(isset($details[$week]['price'])){
							$price_data[]  = round($details[$week]['price'],2);
							$price_sum+=round($details[$week]['price'],2);
						}
						?>
						  <tr>
						  	<td>{{$week}}</td>
							<td class="ranking_s"><a class="sku_ranking" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-ranking" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-ranking" data-type="text">{{array_get($data->details,$week.'.ranking')}} </a></td>
							
							<td><a class="sku_rating" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-rating" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-rating" data-type="text"> {{array_get($data->details,$week.'.rating')}} </a></td>
							
					
							<td><a class="sku_review" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-review" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-review" data-type="text"> {{array_get($data->details,$week.'.review')}}</a></td>
							
						 
							<td><a class="sku_sales" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-sales" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-sales" data-type="text"> {{array_get($data->details,$week.'.sales')}}</a></td>
							
							<td><a class="sku_price" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-price" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-price" data-type="text"> {{array_get($data->details,$week.'.price')}}</a></td>
							
							<td><a class="sku_flow" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-flow" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-flow" data-type="text"> {{array_get($data->details,$week.'.flow')}} </a></td>
						
							<td><a class="sku_conversion" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-conversion" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-conversion" data-type="text"> {{array_get($data->details,$week.'.conversion')}} </a></td>
							
							<td><a class="sku_fba_stock" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_stock" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_stock" data-type="text"> {{array_get($data->details,$week.'.fba_stock')}} </a></td>
							
							<td><a class="sku_fba_transfer" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_transfer" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_transfer" data-type="text"> {{array_get($data->details,$week.'.fba_transfer')}} </a></td>
							
							<td><a class="sku_fbm_stock" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fbm_stock" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fbm_stock" data-type="text"> {{array_get($data->details,$week.'.fbm_stock')}} </a></td>
							
							<td><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-total_stock"> {!!intval(array_get($data->details,$week.'.fba_stock',0)+array_get($data->details,$week.'.fbm_stock',0)+array_get($data->details,$week.'.fba_transfer',0))!!} </span></td>
							
							<td><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-fba_keep"> {!!(array_get($data->details,$week.'.sales',0)!=0)?round(intval(array_get($data->details,$week.'.fba_stock',0))/(array_get($data->details,$week.'.sales',0)),2):'∞'!!} </span></td>
							
							<td><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-total_keep"> {!!(array_get($data->details,$week.'.sales',0)!=0)?round((intval(array_get($data->details,$week.'.fba_stock',0))+intval(array_get($data->details,$week.'.fbm_stock',0))+intval(array_get($data->details,$week.'.fba_transfer',0)))/(array_get($data->details,$week.'.sales',0)),2):'∞'!!} </span></td>
							
							<td class="strategy_s"><a class="sku_strategy" title="{{array_get($data->details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.strategy')}}" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-strategy" data-placement="left"  data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-strategy" data-type="text"> {{array_get($data->details,$week.'.strategy')}} </a></td>
							
						  </tr>
						  <?php
						  		$week = date('Ymd',strtotime($week)-86400);
							}
							?>
						  </table>
						</div>
						<div class="table-head" style="margin-bottom:50px;">
						<table class="table table-bordered">
						    <tr>
						  	<td colspan="15">
							<div class="col-md-12">
								<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;"> 
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>Avg.Sales</small>
								<h3 class="font-green-sharp">
									<span data-counter="counterup">{{count($sales_data)>0?round($sales_sum/count($sales_data),2):0}}</span>
									<small class="font-green-sharp">Pcs</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title font-green-sharp">{{count($sales_data)>0?min($sales_data):0}}</div>
								<div class="status-number font-red-haze">{{count($sales_data)>0?max($sales_data):0}}</div>
							</div>
						</div>
					</div>
				</div>
				
								
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>Avg.Price</small>
								<h3 class="font-red-haze">
									<span data-counter="counterup">{{count($price_data)>0?round($price_sum/count($price_data),2):0}}</span>
									<small class="font-red-haze"></small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success red-haze">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title font-green-sharp">{{count($price_data)>0?min($price_data):0}}</div>
								<div class="status-number font-red-haze">{{count($price_data)>0?max($price_data):0}}</div>
							</div>
						</div>
					</div>
				</div>
				
								<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>Sales Target</small>
								<h3 class="font-blue-sharp">
									<span data-counter="counterup">{{$complete_sales}}%</span>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: {{$complete_sales}}%;" class="progress-bar progress-bar-success blue-sharp">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title font-green-sharp"> {{$data->sales}} </div>
								<div class="status-number font-red-haze">{{$data->target_sales}}</div>
							</div>
						</div>
					</div>
				</div>
								<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
						
							<div class="number">
							<small>Amount Target</small>
								<h3 class="font-purple-soft">
									<span data-counter="counterup">{{$complete_amount}}%</span>
									<small class="font-purple-soft"></small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: {{$complete_amount}}%;" class="progress-bar progress-bar-success purple-soft">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title font-green-sharp">
								
								 {{$data->amount}}
								
								</div>
								<div class="status-number font-red-haze">{{$data->target_amount}}</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;"> 
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>Profit Target</small>
								<h3 class="font-green-sharp">
									<span data-counter="counterup">
									{{$complete_profit}}%</span>
									
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: {{$complete_profit}}%;" class="progress-bar progress-bar-success green-sharp">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title font-green-sharp"> {{$data->profit}} </div>
								<div class="status-number font-red-haze"> {{$data->target_profit}} </div>
							</div>
						</div>
					</div>
				</div>
				</div>
							
							</td>
						  </tr>
						</table>
						</div>
                        @endforeach
						
                               {{ $datas->appends(['date_start' => $date_start,'date_end' => $date_end,'site' => $s_site,'user_id' => $s_user_id,'level' => $s_level,'bgbu' => $bgbu,'sku' => $sku])->links() }}   



                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>
	
	
<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>    
<script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/form-editable.min.js" type="text/javascript"></script>
<script>


jQuery(document).ready(function() {
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		autoclose: true
	});
});



var FormEditable = function() {

    $.mockjaxSettings.responseTime = 500;

    var initAjaxMock = function() {
        $.mockjax({
            url: '/skus',
			type:'post',
            response: function(settings) {
				console.log(this);
				console.log(settings);
            }
        });
    }

    var initEditables = function() {
        $.fn.editable.defaults.inputclass = 'form-control';
        $.fn.editable.defaults.url = '/skus';
		
		$('.sku_keywords,.sku_strategy,.sku_ranking,.sku_rating,.sku_review,.sku_price,.sku_flow,.sku_conversion').editable({
			emptytext:'N/A'
		});
		$('.sku_sales,.sku_fba_stock,.sku_fbm_stock,.sku_fba_transfer').editable({
			emptytext:'N/A',
			validate: function (value) {
                if (isNaN(value)) {
                    return 'Must be a number';
                }
            },
			success: function (response) { 
				var obj = JSON.parse(response);
				for(var jitem in obj){
					$('#'+jitem).text(obj[jitem]);
				}
			}, 
			error: function (response) { 
				return 'remote error'; 
			} 
		});
    }

    return {
        //main function to initiate the module
        init: function() {

            // inii ajax simulation
            //initAjaxMock();
            // init editable elements
            initEditables();

        }
    };

}();

jQuery(document).ready(function() {
    FormEditable.init();
	
	$("#vl_list_export").click(function(){
		location.href='/dreportexport?sku='+$("input[name='sku']").val()+'&date_start='+$("input[name='date_start']").val()+'&date_end='+$("input[name='date_end']").val()+'&user_id='+(($("select[name='user_id[]']").val())?$("select[name='user_id[]']").val():'')+'&bgbu='+$("select[name='bgbu']").val()+'&site='+$('select[name="site"]').val()+'&level='+$('select[name="level"]').val();
	});
});
</script>


@endsection
