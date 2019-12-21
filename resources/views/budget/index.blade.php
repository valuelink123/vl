@extends('layouts.layout')
@section('label', 'Budgets')
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
    font-size:10px !important;
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
    </style>
	<div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
							
							
					<div class="table-toolbar">
                    <form role="form" action="{{url('budgets')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">

                        <div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy">
                                <input type="text" class="form-control form-filter input-sm" readonly name="year" placeholder="Year" value="{{$year}}">
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
                                            <option value="{{$user_id}}" <?php if(in_array($user_id,array_get($_REQUEST,'user_id',[]))) echo 'selected'; ?>>{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-2">
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
						

						 <div class="col-md-2">
						<select class="form-control form-filter input-sm" name="site" id="site">
									<option value="">Select Site</option>
                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}" <?php if($v==array_get($_REQUEST,'site')) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						 <div class="col-md-1">
						<select class="form-control form-filter input-sm" name="level" id="level">
									<option value="">Level</option>
										<option value="S" <?php if('S'==array_get($_REQUEST,'level')) echo 'selected'; ?>>S</option>
                                        <option value="A" <?php if('A'==array_get($_REQUEST,'level')) echo 'selected'; ?>>A</option>
										<option value="B" <?php if('B'==array_get($_REQUEST,'level')) echo 'selected'; ?>>B</option>
										<option value="C" <?php if('C'==array_get($_REQUEST,'level')) echo 'selected'; ?>>C</option>
										<option value="D" <?php if('D'==array_get($_REQUEST,'level')) echo 'selected'; ?>>D</option>
                                    </select>
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="sku" placeholder="SKU OR Description" value ="{{array_get($_REQUEST,'sku')}}">
                                       
						</div>
						<div class="col-md-1">
							
										<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>
						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						
						
						
						
						
						
						
							
						
					</div>

                    </form>
                </div>
                    <div class="table-container">
					{{ $datas->appends($_REQUEST)->links() }} 
					
					<table class="table table-striped table-bordered table-hover">
					 <colgroup>
			<col width="7%"></col>
			<col width="10%"></col>
			<col width="3%"></col>
			<col width="3%"></col>
			<col width="3%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
			<col width="6%"></col>
			</colgroup>
					  <thead>
					  <tr class="head" >
						<td rowspan="2" width="4%">SKU</td>
						<td rowspan="2" width="10%">描述</td>
						<td rowspan="2" width="3%">站点</td>
						<td rowspan="2" width="5%">状态</td>
						<td rowspan="2" width="3%">等级</td>
						<td colspan="5" width="23%">{{$year}}预算</td>
						<td colspan="5" width="23%">{{$year-1}}实际</td>
						<td colspan="5" width="23%">环比</td>
						<td rowspan="2" width="6%">状态</td>
					  </tr>
					  <tr class="head" >
						<td width="4%">销量</td>
						<td width="5%">销售额</td>
						<td width="5%">利润额</td>
						<td width="4%">异常率</td>
						<td width="5%">经济效益</td>
						<td width="4%">销量</td>
						<td width="5%">销售额</td>
						<td width="5%">利润额</td>
						<td width="4%">异常率</td>
						<td width="5%">经济效益</td>
						<td width="4%">销量</td>
						<td width="5%">销售额</td>
						<td width="5%">利润额</td>
						<td width="4%">异常率</td>
						<td width="5%">经济效益</td>
					  </tr>
					  </thead>
					  <tbody>
					  @foreach ($datas as $data)
					  <tr>
						<td><a href="{{url('/budgets/edit?sku='.$data->sku.'&site='.$data->site.'&year='.$year)}}">{{$data->sku}}</a></td>
						<td style="overflow: hidden;text-overflow:ellipsis;white-space: nowrap;">{{$data->description}}</td>
						<td>{{(strtoupper(substr($data->site,-2))=='OM')?'US':strtoupper(substr($data->site,-2))}}</td>
						<td>{{$data->status}}</td>
						<td>{{($data->level=='0')?'S':$data->level}}</td>
						<td>{{$data->qty1}}</td>
						<td>{{$data->amount1}}</td>
						<td>{{$data->profit1}}</td>
						<td>{{$data->exception}}</td>
						<td>{{$data->economic1}}</td>
						<td>{{$data->qty2}}</td>
						<td>{{$data->amount2}}</td>
						<td>{{$data->profit2}}</td>
						<td>{{$data->exception}}</td>
						<td>{{$data->economic2}}</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td><a href="{{url('/budgets/edit?sku='.$data->sku.'&site='.$data->site.'&year='.$year)}}">{{array_get(getBudgetStageArr(),($data->budget_status)??0)}}</a></td>
					  </tr>
					  @endforeach
					  </tbody>
					</table>
					
					{{ $datas->appends($_REQUEST)->links() }}   
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
		format: 'yyyy',
		startView: 2, 
		maxViewMode: 2,
		minViewMode:2,
		orientation: 'bottom',
		autoclose: true
	});
});


</script>


@endsection
