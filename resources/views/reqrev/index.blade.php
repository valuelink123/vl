@extends('layouts.layout')
@section('label', 'Auto Request Review')
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
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th{
padding:8px 5px;
vertical-align:middle;
}
th,td,td>span {
    font-size:12px !important;
	text-align:center;
	font-family:Arial, Helvetica, sans-serif;}
.progress-bar.green-sharp,.progress-bar.red-haze,.progress-bar.blue-sharp{
color:#000 !important;
}
table{ 
table-layout:fixed; 
}
table .head{ 
text-align:center;
vertical-align:middle;
background:#fff2cc;
font-weight:bold;
}
td.strategy_s,td.keyword_s{       
text-overflow:ellipsis; 
-moz-text-overflow: ellipsis; 
overflow:hidden;      
white-space: nowrap;      
}  
.table-bordered, .table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
    border: 1px solid #ccc;
}
.portlet.light {
    padding:0;
}
.pagination {
    float: right;
}
.dropdown-menu{
	min-width:96px;
}
    </style>
	<div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
							
							
					<div class="table-toolbar">
                    <form role="form" action="{{url('reqrev')}}" method="GET">
                        <div class="row">
                       
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]">
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}" <?php if(in_array($user_id,array_get($_REQUEST,'user_id',[]))) echo 'selected'; ?>>{{$user_name}}</option>
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
											if(array_get($_REQUEST,'bgbu')==($team->bg.'_')) $selected = 'selected';
											
											if($bg!=$team->bg) echo '<option value="'.$team->bg.'_" '.$selected.'>'.$team->bg.'</option>';	
											$bg=$team->bg;
											$selected = '';
											if(array_get($_REQUEST,'bgbu')==($team->bg.'_'.$team->bu)) $selected = 'selected';
											if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'" '.$selected.'>'.$team->bg.' - '.$team->bu.'</option>';
										} ?>
                                    </select>
						</div>	
						

						 <div class="col-md-1">
						<select class="form-control form-filter input-sm" name="site" id="site">
									<option value="">Select Site</option>
                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}" <?php if($v==array_get($_REQUEST,'site')) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="status" id="status">
									<option value="">Select Status</option>
                                     <option value="enabled" <?php if('enabled'==array_get($_REQUEST,'status')) echo 'selected'; ?>>Enabled</option>
									 <option value="disabled" <?php if('disabled'==array_get($_REQUEST,'status')) echo 'selected'; ?>>Disabled</option>
                                    </select>
						</div>
						
				
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="sku" placeholder="SKU OR Description" value ="{{array_get($_REQUEST,'sku')}}">
                                       
						</div>
						
						<div class="col-md-1">
							
										<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>
						
						<div class="col-md-4" style="font-size:12px;">
							系统每日18:00对设置为Enabled的所有Asin, 查询对应15天前更新为Shipped的订单, 排除当时已有差feedback, 退款, 重发的, 全部列入自动索评任务.<br />
							索评失败原因 1.已经发过索评 REVIEW_REQUEST_ALREADY_SENT 2.未到或超出索评时间范围 REVIEW_REQUEST_OUTSIDE_TIME_WINDOW
									
                        </div>
						
						
						
						</div>	
			

                    </form>
                </div>
				
					<form action="{{\Request::getRequestUri()}}" method="POST">
                    <div class="table-container">
					<div class="row">
						<div class="col-md-2">
							<div class="btn-group">
								<button type="button" class="btn btn-sm green-meadow">批量操作</button>
								<button type="button" class="btn btn-sm green-meadow dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
									<i class="fa fa-angle-down"></i>
								</button>
								<ul class="dropdown-menu" role="menu">
								
									<?php 
									$status_arr=['Enable'=>'green-meadow','Disable'=>'red-sunglo'];
									foreach ($status_arr as $k=>$v){
									?>
									<li>
									
									<button type="submit" name="BatchUpdate" class="btn btn-sm {{$v}}" value="{{$k}}">{{$k}}</button>
									
									</li>
									<li class="divider"> </li>
									<?php } ?>
							
								</ul>
							</div>
						</div>
						<div class="col-md-5">

							<button type="button" class="btn btn-sm green-meadow">索评总量 : {{intval($sum->success+$sum->failed)}}</button>
							<button type="button" class="btn btn-sm red-sunglo">索评成功 : {{intval($sum->success)}}</button>
							<button type="button" class="btn btn-sm yellow-crusta">索评失败 : {{intval($sum->failed)}}</button>
						</div>
						<div class="col-md-5 pull-right" >{{ $datas->appends($_REQUEST)->links() }} </div>
					</div>
					
					
					
					
					<table class="table table-striped table-bordered table-hover">
					  <thead>
					  <tr class="head" >
					    <td width="3%"><input type="checkbox" class="group-checkable" /></td>
						<td width="10%">Asin</td>
						<td width="10%">站点</td>
						<td width="10%">Sku</td>
						<td width="9%">Seller</td>
						<td width="9%">BU</td>
						<td width="9%">BG</td>
						<td width="10%">索评总量</td>
						<td width="10%">索评成功</td>
						<td width="10%">异常率</td>
						<td width="10%">状态</td>
					  </tr>

					  </thead>
					  <tbody>
					  @foreach ($datas as $data)
					  <tr>
					    <td>
						<input type="checkbox" name="asin_site[]" value="{{$data->asin.'_'.$data->site}}" />
						</td>
						<td>{{$data->asin}}</td>
						<td>{{$data->site}}</td>
						<td>{{$data->sku}}</td>
						<td>{{array_get($users,$data->sap_seller_id,$data->sap_seller_id)}}</td>
						<td>{{$data->bu}}</td>
						<td>{{$data->bg}}</td>
						<td>{{intval($data->success+$data->failed)}}</td>
						<td>{{intval($data->success)}}</td>
						<td>{{($data->success+$data->failed)?round($data->failed/($data->success+$data->failed),4)*100:0}}%</td>
						<td><?php echo $data->id?'<span class="badge badge-success">Enabled</span>':'<span class="badge badge-danger">Disabled</span>';?></td>
	
						<td></td>
					  </tr>
					  @endforeach
					  </tbody>
					</table>
					
					{{ $datas->appends($_REQUEST)->links() }}   
                    </div>
					</form>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>
<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>    


<script>


jQuery(document).ready(function() {
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		format: 'yyyy',
		startView: 2, 
		maxViewMode: 2,
		minViewMode:2,
		orientation: 'bottom',
		autoclose: true
	});
	$('.group-checkable',$('.table')).change(function() {
		var set = $('.table').find('tbody > tr > td:nth-child(1) input[type="checkbox"]');
		var checked = $(this).prop("checked");
		$(set).each(function() {
			$(this).prop("checked", checked);
		});
	});
});


</script>


@endsection
