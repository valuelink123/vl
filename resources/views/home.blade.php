@extends('layouts.layout')

@section('content')
<style>
.widget-thumb .widget-thumb-body .widget-thumb-body-stat {font-size:20px;}
.widget-thumb .widget-thumb-wrap .widget-thumb-icon{width:50px;height:50px;line-height:30px;}
.widget-thumb .widget-thumb-heading{color:#666; margin-bottom:10px;}
.dashboard-stat2 { margin-bottom:0px;margin-top: 8px;}
.col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9{
padding:0px !important;}
.portlet {margin-bottom:0px;}
.portlet.light{padding-top:0px;}
.portlet.light > .portlet-title > .caption{
padding:5px 0;
}
.portlet.light > .portlet-title a{
 font-size:12px;
}
.portlet.light > .portlet-title > .actions {
    padding: 6px 0 6px 0;
}
.portlet.light > .portlet-title{
min-height:30px;
margin-bottom:0px;}
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    padding: 7px;
    line-height: 15px;
	text-align: right;
}
.table td, .table th {
    font-size: 11px;
}
.portlet.light .portlet-body{
padding-top:0px;}
.portlet.light > .portlet-title > .nav-tabs > li > a {
    margin: 0;
    padding: 5px 10px;
    font-size: 13px;
    color: #666;
}
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
.primary-link {
    font-size: 12px;
}
.portlet.light > .portlet-title > .caption > .caption-subject {
    font-size: 12px;
}
table{ 
table-layout:fixed; 
}
.row{ background:#fff;}
textarea.form-control{width:400px!important;}

.editable-click, 
a.editable-click, 
a.editable-click:hover {
    text-decoration: none;
    border-bottom: none !important;
}

.dashboard-stat2 .progress-info .status {
    font-size: 12px;
    color: #666;
}
.pagination > li > a, .pagination > li > span{padding:3px;}
.dataTables_wrapper .dataTables_paginate .paginate_button{padding:0px;margin: 0em 1em}
</style>
<link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
<div class="row" >
<div class="col-lg-12 col-xs-12 col-sm-12">
<div class="portlet light ">
			
			<div class="col-md-9">
				<div class="col-md-2">
				<div style="width: 140px;height: 140px;border: 20px solid #f36a5a;border-radius:50% !important;text-align:center;line-height: 100px;margin:auto;font-size: 18px;" >
					<span id="tichengxianshi" style="cursor:pointer;" data-value="{{round(array_get($total_info,'bonus',0),2)}}" class="fa fa-eye-slash"></span>
				</div>
				</div>
				<div class="col-md-10">
				<?php
				$status_arr=['2'=>'New','1'=>'Reserved','0'=>'Eliminated'];
				$time_rate = date('d',strtotime('-2days'))/date('t',strtotime('-2days'));
				$class=$sign="";
				$ap = array_get($total_info,'economic',0);
				$hb_ap = array_get($hb_total_info,'economic',0);
				
				$ap_hb_ap = ($hb_ap!=0)?round(($ap-$hb_ap)/$hb_ap*100,2):'0';
				if($ap_hb_ap>=0){
					$class="font-red-haze";
					$sign='+';
				}else{
					$class="font-green-sharp";
				}
				?>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;"> 
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>E.VALUE</small>
								<h3 class="font-green-sharp">
									<span data-counter="counterup">{{round($ap/10000,2)}} 万</span>
									<small class="font-green-sharp">¥</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title {{$class}}"> {{$sign.$ap_hb_ap}}%</div>
								<div class="status-number">{{round($hb_ap/10000,2)}} 万</div>
							</div>
						</div>
					</div>
				</div>
				
				<?php
				$class=$sign="";
				$ap = array_get($total_info,'amount',0);
				$hb_ap = array_get($hb_total_info,'amount',0);
				$ap_hb_ap = ($hb_ap!=0)?round(($ap-$hb_ap)/$hb_ap*100,2):'0';
				if($ap_hb_ap>=0){
					$class="font-red-haze";
					$sign='+';
				}else{
					$class="font-green-sharp";
				}
				?>
				
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>SALES</small>
								<h3 class="font-red-haze">
									<span data-counter="counterup" >{{round($ap/10000,2)}} 万</span>
									<small class="font-red-haze">¥</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success red-haze">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title {{$class}}"> {{$sign.$ap_hb_ap}}%</div>
								<div class="status-number">{{round($hb_ap/10000,2)}} 万</div>
							</div>
						</div>
					</div>
				</div>
				
				<?php
				$class=$sign="";
				$ap = intval(array_get($total_info,'sales',0));
				$hb_ap = intval(array_get($hb_total_info,'sales',0));
				$ap_hb_ap = ($hb_ap!=0)?round(($ap-$hb_ap)/$hb_ap*100,2):'0';
				if($ap_hb_ap>=0){
					$class="font-red-haze";
					$sign='+';
				}else{
					$class="font-green-sharp";
				}
				?>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>UNITS</small>
								<h3 class="font-blue-sharp">
									<span data-counter="counterup" >{{$ap}}</span>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success blue-sharp">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title {{$class}}"> {{$sign.$ap_hb_ap}}% </div>
								<div class="status-number">{{$hb_ap}}</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				$class=$sign="";
				$ap = (array_get($total_info,'sales',0)>0)?round(array_get($total_info,'amount',0)/array_get($total_info,'sales',0),2):0;
				$hb_ap = (array_get($hb_total_info,'sales',0)>0)?round(array_get($hb_total_info,'amount',0)/array_get($hb_total_info,'sales',0),2):0;
				$ap_hb_ap = ($hb_ap!=0)?round(($ap-$hb_ap)/$hb_ap*100,2):'0';
				if($ap_hb_ap>=0){
					$class="font-red-haze";
					$sign='+';
				}else{
					$class="font-green-sharp";
				}
				?>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
						
							<div class="number">
							<small>AVG.PRICE</small>
								<h3 class="font-purple-soft">
									<span data-counter="counterup" >{{$ap}}</span>
									<small class="font-purple-soft">¥</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success purple-soft">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title {{$class}}">
								
								 {{$sign.$ap_hb_ap}}%
								
								</div>
								<div class="status-number">{{$hb_ap}}</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;"> 
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>INVENTORY</small>
								<h3 class="font-green-sharp">
									<span data-counter="counterup" >
									{{round(intval($fbm_stock_info[0]->fbm_total_stock + $fba_stock_info[0]->fba_total_stock)/10000,2)}} 万</span>
									<small class="font-green-sharp">PCS</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title"> STOCK VALUE : {{round(($fbm_stock_info[0]->fbm_total_amount + $fba_stock_info[0]->fba_total_amount)/10000,2)}} 万 </div>
								<div class="status-number">  </div>
							</div>
						</div>
					</div>
				</div>
				</div>
			</div>
			
            <div class="col-md-3" style="    padding-top: 10px !important;">
				
							
					<form role="form" action="{{url('home')}}" method="GET">
                        {{ csrf_field() }}

                        <div class="form-group col-md-5" >
                            
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="Date" value="{{$date_from}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
						<div class="form-group col-md-5 col-md-offset-2" >
                            
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="Date" value="{{$date_to}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
                       	
						
						@if (Auth::user()->seller_rules)
						
						<div class="form-group col-md-5">
						<select class="mt-multiselect btn btn-default form-control form-filter input-sm " data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id" id="user_id">
								<option value="">All Sellers</option>
								@foreach ($users as $user_id=>$user_name)
									<option value="{{$user_id}}" <?php if($user_id==$s_user_id) echo 'selected'; ?>>{{$user_name}}</option>
								@endforeach
							</select>
						
						</div>
						
						<div class="form-group col-md-5 col-md-offset-2">
						<select class="form-control form-filter input-sm" name="bgbu">
                                        <option value="">BG && BU</option>
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
						
						@endif

						<div class="form-group col-md-12">
							
							<button type="submit" class="btn blue" id="data_search">Search</button>
							
							
							<button type="button" class="btn green pull-right" id="data_export" >Show Details</button>
									
                        </div>

                    </form>
			</div>
			<div style="clear:both;"></div>
			<div class="col-md-12" id="lineChartDiv" style="display:none;">
				<div id="lineChart" style="width:100%;height:200px;"></div>
			</div>
			<div style="clear:both;"></div>
			</div>
			</div>
</div>
<div class="row">
	
</div>


<div class="row">
	<div class="col-lg-12 col-xs-12 col-sm-12">
		<div class="portlet light ">
			<div class="portlet-title tabbable-line">
			<div class="caption">
				<i class=" icon-social-twitter font-dark hide"></i>
				<span class="caption-subject font-dark bold uppercase">MY Listings</span>
				
			</div>
			
			<div class="pull-right">
				<a href="{{url('home/asins?date_from='.$date_from.'&date_to='.$date_to.'&s_user_id='.$s_user_id.'&bgbu='.$bgbu)}}">View More&gt;&gt;</a>
			</div>
			
		</div>
			<div class="portlet-body">
				
					<table class="table table-hover">
						
							<tr>
								<th style="text-align: left;" width="11%">Asin</th>
								<th style="text-align: left;" width="6%">Sku</th>
								<th style="text-align: left;" width="5%">Status</th>
								<th width="6%">Sales</th>
								<th width="4%">Units</th>
								<th width="5%">Units/D</th>
								
								<th width="5%">AvgPrice</th>
								<th width="5%">Instock</th>
								<th width="5%">Reserved</th>
								<th width="6%">Outstock</th>
								<th width="4%">FBM</th>
								<th width="6%">Rating</th>
								<th width="5%">Sess<font style="color:#ccc;"><i  class="fa fa-info-circle popovers" data-container="body" onclick=" " data-trigger="hover" data-placement="top" data-html="true" data-content="Average data from {{date('Y-m-d',strtotime('-10days'))}} to {{date('Y-m-d',strtotime('-4days'))}}"></i></font></th>
								<th width="5%">CR% <font style="color:#ccc;"><i  class="fa fa-info-circle popovers" data-container="body" onclick=" " data-trigger="hover" data-placement="top" data-html="true" data-content="Average data from {{date('Y-m-d',strtotime('-10days'))}} to {{date('Y-m-d',strtotime('-4days'))}}"></i></font></th>
								
								<th width="5%">Rank</th>
								<th width="5%">Bsr<font style="color:#ccc;"><i  class="fa fa-info-circle popovers" data-container="body" onclick=" " data-trigger="hover" data-placement="top" data-html="true" data-content="Average data from {{date('Y-m-d',strtotime('-10days'))}} to {{date('Y-m-d',strtotime('-4days'))}}"></i></font></th>
								<th width="6%">SkuE.Val</th>
								<th width="6%">SkuBonus</th>
							</tr>
						
						@foreach ($asins as $asin)
						
						<?php 

						$sales = ((((array_get($asin,'sales_07_01')??array_get($asin,'sales_14_08'))??array_get($asin,'sales_21_15'))??array_get($asin,'sales_28_22'))??0)/7 ;?>
						<tr>
							<td style="text-align: left;">
								<a href="https://{{array_get($asin,'site')}}/dp/{{array_get($asin,'asin')}}" class="primary-link" target="_blank">{{array_get($asin,'asin')}}</a> <a href="/seller/{{array_get($asin,'asin')}}/{{array_get(siteToMarketplaceid(),array_get($asin,'site'))}}"><i class="fa fa-tasks"></i></a>
							</td >
							<td style="text-align: left;"> {{array_get($asin,'item_no')}} </td>
							<td style="text-align: left;"> {{array_get($asin,'sku_status')}} </td>
							<td> {{array_get($asin,'amount',0)}} </td>
							<td><a href="/skus?site={{array_get($asin,'site')}}&sku={{array_get($asin,'asin')}}">{{array_get($asin,'sales',0)}} </a></td>
							<td> {{intval(array_get($asin,'sales')/((strtotime($date_to)-strtotime($date_from))/86400+1))}} </td>
							
							<td> {{(array_get($asin,'sales')>0)?round(array_get($asin,'amount')/array_get($asin,'sales'),2):0}} </td>
							<td><a href="/kms/partslist?search={{array_get($asin,'asin')}}"> {{array_get($asin,'fba_stock',0)}} </a></td>
							<td> <a href="/kms/partslist?search={{array_get($asin,'asin')}}">{{array_get($asin,'fba_transfer',0)}} </a></td>
							<td> {{($sales>0)?date('Y-m-d',strtotime('+'.intval((array_get($asin,'fba_stock',0)+array_get($asin,'fba_transfer',0))/$sales).' days')):'∞'}} </td>
							<td><a href="/kms/partslist?search={{array_get($asin,'asin')}}"> {{array_get($asin,'fbm_stock',0)}}</a> </td>
							<td> {{array_get($asin,'rating')}} ({{array_get($asin,'review_count')}})</td>
							<td> {{intval(array_get($asin,'sessions'))}} </td>
							<td> {{round(array_get($asin,'unit_session_percentage'),2)}} </td>
							<td> {{array_get($asin,'sku_ranking')}} </td>
							<td> {{intval(array_get($asin,'bsr'))}} </td>
							
							<td> {{intval(array_get($asin,'economic',0))}} </td>
							<td>
								{{intval(array_get($asin,'bonus',0))}}
							</td>
						</tr>
						@endforeach
						
						
						
				</table>
				
			</div>
		</div>
	</div>
</div>			
<div class="row">					
<div class="col-lg-12 col-xs-12 col-sm-12">
	<div class="portlet light ">
		<div class="portlet-title tabbable-line">
			<div class="caption">
				<i class=" icon-social-twitter font-dark hide"></i>
				<span class="caption-subject font-dark bold uppercase">MY TASKS</span>
				
			</div>
			
					<div class="pull-right">
						<a href="{{url('task')}}">View More&gt;&gt;</a>
					</div>
		</div>
		<div class="portlet-body">

			<table class="table table-hover" id="tasks_list">
			
			
			<tr class="uppercase">
				<th width="2%"><font style="color:#ccc;"><i  class="fa fa-info-circle popovers" data-container="body" onclick="" data-trigger="hover" data-placement="right" data-html="true" data-content="Click to complete the task quickly"></i></font></th>
				<th width="44%" >Task Details</th>
				<th width="30%"  >Task Response</th>
				<th width="8%" >Due To</th>
				<th width="8%" >By</th>
				<th width="8%" >Priority</th>
			</tr>
			
			@foreach ($tasks as $task)
			<tr id="task_{{$task['id']}}">
				<td><input type="checkbox" class="task_quick_complete" id="{{$task['id']}}" /></a>
				</td>
				<td ><a class="task_request primary-link" title="Task Details" href="javascript:;" id="{{$task['id']}}-request" data-pk="{{$task['id']}}-request" data-type="textarea" data-placement="right">{{$task['request']}}</a></td>
				<td ><a class="task_response" title="Task Response" href="javascript:;" id="{{$task['id']}}-response" data-pk="{{$task['id']}}-response" data-type="textarea" data-placeholder="Your response here...">{{$task['response']}}</a></td>
				<td><a class="task_complete_date" title="Due To" href="javascript:;" id="{{$task['id']}}-complete_date" data-pk="{{$task['id']}}-complete_date" data-type="date" data-viewformat="yyyy-mm-dd" data-placement="right">{{$task['complete_date']}}</a></td>
				<td>{{array_get($users,$task['request_user_id'])}}</td>
				<td >
				<a class="task_priority" title="Priority" href="javascript:;" id="{{$task['id']}}-priority" data-pk="{{$task['id']}}-priority" data-type="text" data-placement="left">{{$task['priority']}}</a>
				</td>
			</tr>
			@endforeach
			</table>
		</div>
	</div>
</div>
</div>

<div class="row">
	<div class="col-md-6">
	<div class="portlet light ">
		<div class="portlet-title tabbable-line">
			<div class="caption">
				<i class=" icon-social-twitter font-dark hide"></i>
				<span class="caption-subject font-dark bold uppercase">Budget completion rate</span>
				
			</div>
			
			<div class="pull-right">
				
			</div>
		</div>
		<div class="portlet-body">
		<table class="table" id="manage_account">
			<thead>
			<tr>
				<th> Sku </th>
				<th> Status</th>
				<th> Units </th>
				<th> Sales</th>
				<th> E.Val </th>
				<th> Units </th>
				<th> Sales </th>
				<th> E.Val </th>
			</tr>
			</thead>
			<tbody>
			@foreach ($month_budget as $sku_budget)
			
				<?php
				
				if($sku_budget['amount_target']<0){
					$amount_per = round(2-$sku_budget['amount']/$sku_budget['amount_target'],4);
				}elseif($sku_budget['amount_target']>0){
					$amount_per =round($sku_budget['amount']/$sku_budget['amount_target'],4);
				}else{
					$amount_per =0;
				}
				
				if($sku_budget['sales_target']<0){
					$sales_per = round(2-$sku_budget['sales']/$sku_budget['sales_target'],4);
				}elseif($sku_budget['sales_target']>0){
					$sales_per =round($sku_budget['sales']/$sku_budget['sales_target'],4);
				}else{
					$sales_per =0;
				}
				
				if($sku_budget['economic_target']<0){
					$profit_per = round(2-$sku_budget['economic']/$sku_budget['economic_target'],4);
				}elseif($sku_budget['economic_target']>0){
					$profit_per =round($sku_budget['economic']/$sku_budget['economic_target'],4);
				}else{
					$profit_per =0;
				}
				?>
				<tr class="odd gradeX">
					<td>
						{{$sku_budget['sku']}}
					</td>
					<td>
						{{array_get($status_arr,$sku_budget['status'])}}
					</td>
					<td>
						{{$sku_budget['sales']}}
					</td>
					<td>
						{{$sku_budget['amount']}}
					</td>
					<td>
						{{$sku_budget['economic']}}
					</td>
					<td>
						{{$sku_budget['sales_target']}}
						<span class="{{($sales_per>=$time_rate)?'font-red-haze':'font-green-sharp'}}">{{$sales_per*100}}%</span>
					</td>
					<td>
						{{$sku_budget['amount_target']}}
						<span class="{{($amount_per>=$time_rate)?'font-red-haze':'font-green-sharp'}}">{{$amount_per*100}}%</span>
					</td>
					<td>
						{{$sku_budget['economic_target']}}
						<span class="{{($profit_per>=$time_rate)?'font-red-haze':'font-green-sharp'}}">{{$profit_per*100}}%</span>
					</td>
				</tr>
			@endforeach



			</tbody>
		</table>
		</div>
		</div>
	</div>
</div>
							

    

<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>    
<script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/form-editable.min.js" type="text/javascript"></script>
<script type="text/javascript">
var FormEditable = function() {

    $.mockjaxSettings.responseTime = 500;

    var initAjaxMock = function() {
        $.mockjax({
            url: '/taskajaxupdate',
			type:'post',
            response: function(settings) {
				console.log(this);
				console.log(settings);
            }
        });
    }

    var initEditables = function() {
        $.fn.editable.defaults.inputclass = 'form-control';
        $.fn.editable.defaults.url = '/taskajaxupdate';
		$('.task_request,.task_response').editable({
			emptytext:'N/A'
		});
		var sellers = [];
        <?php foreach($users as $k=>$v) {?>
		sellers.push({
			value: "<?php echo $k?>",
			text: "<?php echo $v?>"
		});
		<?php }?>

        $('.task_response_user_id').editable({
            inputclass: 'form-control input-medium',
            source: sellers
        });
		
		$('.task_request_user_id').editable({
            inputclass: 'form-control input-medium',
            source: sellers
        });
		
		var stages = [];
        <?php foreach(getTaskStageArr() as $k=>$v) {?>
		stages.push({
			value: "<?php echo $k?>",
			text: "<?php echo $v?>"
		});
		<?php }?>
		$('.task_stage').editable({
            inputclass: 'form-control input-medium',
            source: stages
        });
		
		$('.task_priority').editable({
			emptytext:'0',
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
		
		$('.task_complete_date').editable({
            format: 'yyyy-mm-dd',
            viewformat: 'yyyy-mm-dd',
        });
    }

    return {
        init: function() {
            initEditables();
        }
    };

}();
$(function() {
	var TableDatatablesManaged = function () {

            var initTable = function () {

			var table = $('#manage_account');

			// begin first table
			table.dataTable({

				// Internationalisation. For more info refer to http://datatables.net/manual/i18n
				"language": {
					"aria": {
						"sortAscending": ": activate to sort column ascending",
						"sortDescending": ": activate to sort column descending"
					},
					"emptyTable": "No data available in table",
					"info": "Showing _START_ to _END_ of _TOTAL_ records",
					"infoEmpty": "No records found",
					"infoFiltered": "(filtered1 from _MAX_ total records)",
					"lengthMenu": "Show _MENU_",
					"search": "Search:",
					"zeroRecords": "No matching records found",
					"paginate": {
						"previous":"Prev",
						"next": "Next",
						"last": "Last",
						"first": "First"
					}
				},

				"bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
				// set the initial value
				"pageLength": 5,
				"pagingType": "simple",
				"columnDefs": [

					{
						"className": "dt-right",
						//"targets": [2]
					}
				],
				"info":false,
				"searching":false,
				"lengthChange":false,
				"order": [
					[4, "desc"]
				] // set first column as a default sort by asc
			});

		}


		return {

			//main function to initiate the module
			init: function () {
				if (!jQuery().dataTable) {
					return;
				}

				initTable();
			}

		};

	}();



	TableDatatablesManaged.init();
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		format: 'yyyy-mm-dd',
		orientation: 'bottom',
		autoclose: true,
	});
	
	$("#data_export").click(function(){
		location.href='/homeexport?date_from='+$("input[name='date_from']").val()+'&date_to='+$("input[name='date_to']").val()+'&user_id='+$("select[name='user_id']").val()+'&bgbu='+$("select[name='bgbu']").val();
	});
	
	
	
	var date_data = [];
	var comm_data = [];
	<?php 
	
	foreach($daily_info as $k=>$v) {?>
	date_data.push('<?php echo $k?>');
	comm_data.push('<?php echo $v?>');
	<?php }?>
	console.log(date_data);
	console.log(comm_data);
	lineoption = {
		title : {
			text: 'Estimated Commission, paid according to actual calculation',

		},
		tooltip : {
			trigger: 'axis'
		},
		legend: {
			data:['Commission']
		},
		grid: {
			x:40,
			y:40,
			x2:40,
			y2:40,
		},
		calculable : true,
		xAxis : [
			{
				type : 'category',
				boundaryGap : false,
				data : date_data
			}
		],
		yAxis : [
			{
				type : 'value'
			}
		],
		series : [
			{
				name:'Commission',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:comm_data
			}
		]
	};
	
	var lineChart = echarts.init(document.getElementById('lineChart'));
	lineChart.setOption(lineoption,true);
	$("#tichengxianshi").click(function(){

		if($(this).hasClass('fa-eye-slash')){
			$(this).removeClass('fa-eye-slash');
			$(this).removeClass('fa');	
			$(this).html($(this).data('value'));
			$(this).text($(this).data('value'));	
			$("#lineChartDiv").slideDown();
			lineChart.resize();
		}else{
			$(this).addClass('fa-eye-slash');
			$(this).addClass('fa');	
			$(this).html('');
			$(this).text('');		
			$("#lineChartDiv").slideUp();
		}
	});
	
	$(".task_quick_complete").on('click',function(){
	
		var task_id = $(this).attr('id');
		var exist_ids = [];
		$(".task_quick_complete").each(function(){
			exist_ids.push($(this).attr('id'));
		});
		exist_ids.join(',');
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('taskajaxupdate') }}",
			data: "name="+task_id+"-stage&value=3&exist_ids="+exist_ids,
			success: function (redata) {
				$("#task_"+task_id).remove();
				if(redata.id){
					var str='<tr id="task_'+redata.id+'"><td><input type="checkbox" class="task_quick_complete" id="'+redata.id+'" /></a></td><td><a class="task_request primary-link" title="Task Details" href="javascript:;" id="'+redata.id+'-request" data-pk="'+redata.id+'-request" data-type="textarea" data-placement="right">'+redata.request+'</a></td><td><a class="task_response" title="Task Response" href="javascript:;" id="'+redata.id+'-response" data-pk="'+redata.id+'-response" data-type="textarea" data-placeholder="Your response here..." >'+((redata.response)?redata.response:"N/A")+'</a></td><td><a class="task_complete_date" title="Due To" href="javascript:;" id="'+redata.id+'-complete_date" data-pk="'+redata.id+'-complete_date" data-type="date" data-viewformat="yyyy-mm-dd" data-placement="right">'+redata.complete_date+'</a></td><td>'+redata.request_user+'</td><td><a class="task_priority" title="Priority" href="javascript:;" id="'+redata.id+'-priority" data-pk="'+redata.id+'-priority" data-type="text" data-placement="left">'+redata.priority+'</a></td></tr>';
					$("#tasks_list").append(str);
					FormEditable.init();
				}
			},
			error: function(data) {
				alert("error:"+data.responseText);
			}
		});
	});
	
	
	lineChart.resize();
    FormEditable.init();
});
</script>
@endsection
