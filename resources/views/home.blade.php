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
    padding: 20px 5px;
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
.table thead tr th {
    font-size: 11px;
}
</style>
<div class="row" >
<div class="col-lg-12 col-xs-12 col-sm-12">
<div class="portlet light ">
			
			<div class="col-md-9">
				<div class="col-md-2">
				<div style="width: 140px;height: 140px;border: 20px solid #f36a5a;border-radius:50% !important;text-align:center;line-height: 100px;margin:auto;font-size: 18px;" >
					<span id="tichengxianshi" style="cursor:pointer;" data-value="{{round(array_get($total_info,'economic',0),2)}}">{{round(array_get($total_info,'economic',0),2)}}</span>
				</div>
				</div>
				<div class="col-md-10">
				<?php
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
				$ap = array_get($total_info,'income',0);
				$hb_ap = array_get($hb_total_info,'income',0);
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
				$ap = intval(array_get($total_info,'refund',0));
				$hb_ap = intval(array_get($hb_total_info,'refund',0));
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
							<small>Refund</small>
								<h3 class="font-purple-soft">
									<span data-counter="counterup" >{{round($ap/10000,2)}} 万</span>
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
								<div class="status-number">{{round($hb_ap/10000,2)}} 万</div>
							</div>
						</div>
					</div>
				</div>

				<?php
				$class=$sign="";
				$ap = intval(array_get($total_info,'shipped',0));
				$hb_ap = intval(array_get($hb_total_info,'shipped',0));
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
									<small class="font-green-sharp">PCS</small>
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
				$ap = array_get($total_info,'shipped',0)?round(array_get($total_info,'income',0)/array_get($total_info,'shipped'),2):0;
				$hb_ap = array_get($hb_total_info,'shipped',0)?round(array_get($hb_total_info,'income',0)/array_get($hb_total_info,'shipped'),2):0;
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
								<small>Avg.Price</small>
								<h3 class="font-green-sharp">
									<span data-counter="counterup" >
									{{$ap}}</span>
									<small class="font-purple-soft">¥</small>
								</h3>
								
							</div>
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">
									
								</span>
							</div>
							<div class="status">
								<div class="status-title {{$class}}"> {{$sign.$ap_hb_ap}}% </div>
								<div class="status-number">{{$hb_ap}}</div>
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
						<select class="mt-multiselect btn btn-default form-control form-filter input-sm " data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="sap_seller_id" id="sap_seller_id">
								<option value="">All Sellers</option>
								@foreach ($users as $sap_seller_id=>$user_name)
									<option value="{{$sap_seller_id}}" <?php if($sap_seller_id==$selected_sap_seller_id) echo 'selected'; ?>>{{$user_name}}</option>
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
						
						<div class="form-group col-md-5">
						<select class="form-control form-filter input-sm" name="sku_status" id="sku_status">
								<option value="">All Status</option>
								@foreach ($sku_statuses as $k=>$v)
									<option value="{{$k}}" <?php if($k===$selected_sku_status) echo 'selected'; ?>>{{$v}}</option>
								@endforeach
							</select>
						
						</div>
						
						<div class="form-group col-md-5 col-md-offset-2">
						<input class="form-control form-filter input-sm" name="keywords" id="keywords" value="{{$selected_keywords}}" placeholder="SKU">
						</div>	

						<div class="form-group col-md-12">
							
							<button type="submit" class="btn blue" id="data_search">Search</button>
							
							
							<button type="button" class="btn green pull-right" id="data_export" >Export Details</button>
									
                        </div>

                    </form>
			</div>
			</div>
			</div>
</div>	

<div class="row">
	<div class="col-md-12">
	<div class="portlet light ">
		<div class="portlet-title tabbable-line">
			<div class="caption">
				<i class=" icon-social-twitter font-dark hide"></i>
				<span class="caption-subject font-dark bold uppercase">My Listings</span>
				
			</div>
			
			<div class="pull-right">
				
			</div>
		</div>
		<div class="portlet-body">
		<table class="table" id="manage_account">
			<thead>
			<tr>
				<th> No. </th>
				<th> Asin </th>
				<th> Site </th>
				<th> Sku </th>
				<th> Status</th>
				<th> Sales </th>
				<th> Refund </th>
				<th> Sales Units </th>
				<th> Return Units </th>
				<th> Mcf Units </th>
				<th> Units/D </th>
				<th> Avg.Price</th>
				<th> Coupon </th>
				<th> Deal </th>
				<th> Cpc </th>
				<th> E.Val </th>
				<th> Bonus </th>
			</tr>
			</thead>
			<tbody>
			<?php $i=0;?>
			@foreach ($datas as $data)
				<tr class="odd gradeX">
					<td>
						{{$i++}}
					</td>		
					<td>
						{{$data['asin']}}
					</td>
					<td>
						{{array_get(getSiteUrl(),$data['marketplace_id'],$data['marketplace_id'])}}
					</td>
					<td>
						{{$data['sku']}}
					</td>
					<td>
						{{array_get(getSkuStatuses(),$data['sku_status'])}}
					</td>
					<td>
						{{round($data['income'],2)}}
					</td>
					<td>
						{{round($data['refund'],2)}}
					</td>			
					<td>
						{{intval($data['shipped'])}}
					</td>
					<td>
						{{intval($data['return'])}}
					</td>
					<td>
						{{intval($data['replace'])}}
					</td>
					<td>
						{{round(($data['replace']+$data['shipped'])/((strtotime($date_to)-strtotime($date_from))/86400+1),2)}}
					</td>		

					<td>
						{{($data['shipped']!=0)?round($data['income']/$data['shipped'],2):0}}
					</td>
					
					<td>
						0
					</td>
					<td>
						0
					</td>
					<td>
						0
					</td>
					<td>
						{{round($data['economic'],2)}}
					</td>
					<td>
						0
					</td>
				</tr>
			@endforeach



			</tbody>
		</table>
		</div>
		</div>
	</div>
</div>
							

    
<script type="text/javascript">
$(function() {
	var TableDatatablesManaged = function () {
            var initTable = function () {
			var table = $('#manage_account');
			table.dataTable({
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

				"bStateSave": false,
				"pageLength": 20,
				"pagingType": "simple",
				"columnDefs": [
					{
						"className": "dt-right",
					}
				],
				"info":false,
				"searching":false,
				"lengthChange":false,
				"order": [
					[15, "desc"]
				]
			});

		}
		return {
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
		location.href='/homeexport?date_from='+$("input[name='date_from']").val()+'&date_to='+$("input[name='date_to']").val()+'&sap_seller_id='+$("select[name='sap_seller_id']").val()+'&bgbu='+$("select[name='bgbu']").val();
	});
});
</script>
@endsection
