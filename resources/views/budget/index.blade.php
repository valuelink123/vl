@extends('layouts.layout')
@section('label', 'Sales Budget')
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
<?php 
	$bg='';
	$quarters_arr=['1','2','3','4'];
?>
	<div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
							
							
					<div class="table-toolbar">
                    <form role="form" action="{{url('budgets')}}" method="GET">
                        <div class="row">

                        <div class="col-md-2">
                            <select class="form-control form-filter input-sm" name="year_from" id="year_from">
                                        @foreach (getBudgetQuarter() as $k=>$v)
                                            <option value="{{$v}}" <?php if($v==$year_from) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
                                    </select>
                        </div>


                        <div class="col-md-2">
                        	<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-action-onchange="true" name="quarter_from[]" id="quarter_from[]">
                            @foreach ($quarters_arr as $v)
                                <option value="{{$v}}" <?php if(in_array($v,$quarter_from)) echo 'selected'; ?>>Quarter{{$v}}</option>
                            @endforeach
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
						
						<div class="col-md-2">
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
						<select class="form-control form-filter input-sm" name="sku_status" id="sku_status">
									<option value="">Sku Status</option>
										@foreach ($sku_status as $k=>$v)
                                            <option value="{{$k+1}}" <?php if($k+1==array_get($_REQUEST,'sku_status')) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-2">
						<select class="form-control form-filter input-sm" name="b_status" id="b_status">
								<option value="">Budget Status</option>
									@foreach (getBudgetStageArr() as $k=>$v)
								<option value="{{$k+1}}" <?php if($k+1==array_get($_REQUEST,'b_status')) echo 'selected'; ?>>{{$v}}</option>
							@endforeach
						</select>
						</div>

						</div>
						 <div class="row" style="margin-top:20px;">
						<div class="col-md-2">
                            <select class="form-control form-filter input-sm" name="year_to" id="year_to">
                                @foreach (getBudgetQuarter() as $k=>$v)
                                    <option value="{{$v}}" <?php if($v==$year_to) echo 'selected'; ?>>{{$v}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                        	<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-action-onchange="true"  name="quarter_to[]" id="quarter_to[]">
                            @foreach ($quarters_arr as $v)
                                <option value="{{$v}}" <?php if(in_array($v,$quarter_to)) echo 'selected'; ?>>Quarter{{$v}}</option>
                            @endforeach
                        	</select>
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
								foreach($teams as $team){ 
									$selected = '';
									if(array_get($_REQUEST,'bgbu')==($team->bg.'_')) $selected = 'selected';
									
									if($bg!=$team->bg) echo '<option value="'.$team->bg.'_" '.$selected.'>'.$team->bg.'</option>';	
									$bg=$team->bg;
									$selected = '';
									if(array_get($_REQUEST,'bgbu')==($team->bg.'_'.$team->bu)) $selected = 'selected';
									if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'" '.$selected.'>'.$team->bg.' - '.$team->bu.'</option>';
								} 
								?>
                            </select>
						</div>	
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="sku" placeholder="SKU! More Use , Separate" value ="{{array_get($_REQUEST,'sku')}}">
                                       
						</div>
						
						<div class="col-md-2 ">
							
							<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>
						
						
						
						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						 	<div class="col-md-2">
                                <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy">
                                    <input type="text" class="form-control" readonly name="year_current"  value="{{array_get($_REQUEST,'year_current')??date('Y')}}">
                                    <span class="input-group-btn">
                                        <button class="btn  default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>

							<div class="col-md-2">
								<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-action-onchange="true"  name="quarter_current[]" id="quarter_current[]">
								@foreach ($quarters_arr as $v)
									<option value="{{$v}}" <?php if(in_array($v,$quarter_current)) echo 'selected'; ?>>Quarter{{$v}}</option>
								@endforeach
								</select>
							</div>
						
						
						
						
							
						
					</div>

                    </form>
					<div class="col-md-1  col-md-offset-9">

								<button id="vl_list_export" class="btn blue"> Export
                                    <i class="fa fa-download"></i>
                                </button>
									
                        </div>
                        <div class="col-md-1">
							<a data-target="#ajax" data-toggle="modal" href="{{ url('/budgets/create')}}"><button class="btn red"> Add New
									<i class="fa fa-plus"></i>
								</button>
							</a>
						</div>
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
									$color_arr=['0'=>'red-sunglo','1'=>'yellow-crusta','2'=>'purple-plum','3'=>'blue-hoki','4'=>'blue-madison','5'=>'green-meadow'];
									foreach (getBudgetRuleForRole() as $k=>$v){
									if(count(getBudgetRuleForRole())==2 && $k==1) break; //不允许销售批量提交，因为需要单个计算
									?>
									<li>
									
									<button type="submit" name="BatchUpdate" class="btn btn-sm {{array_get($color_arr,$k)}}" value="{{$k}}">{{$v}}</button>
									
									</li>
									<li class="divider"> </li>
									<?php } ?>
							
								</ul>
							</div>
						</div>
						<div class="col-md-5">
							<?php 
							$color_arr=['0'=>'red-sunglo','1'=>'yellow-crusta','2'=>'purple-plum','3'=>'blue-hoki','4'=>'blue-madison','5'=>'green-meadow'];
							foreach (getBudgetStageArr() as $k=>$v){ 
							?>
							
							
							<button type="button" class="btn btn-sm {{array_get($color_arr,$k)}}">{{$v}} {{array_get($finish,$k,0)}}</button>
							<?php } ?>
						</div>
						<div class="col-md-5 pull-right" >{{ $datas->appends($_REQUEST)->links() }} </div>
					</div>

					<table class="table table-striped table-bordered table-hover">
					 <colgroup>
						<col width="2%"></col>
						<col width="6%"></col>
						<col width="9%"></col>
						<col width="4%"></col>
						<col width="4%"></col>
						<col width="4%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="5%"></col>
						<col width="6%"></col>
						</colgroup>
					  <thead>
					  <tr class="head" >
					    <td rowspan="2" width="3%">
                           <input type="checkbox" class="group-checkable" /></td>
						<td rowspan="2" width="6%">SKU</td>
						<td rowspan="2" width="8%">描述</td>
						<td rowspan="2" width="3%">站点</td>
						<td rowspan="2" width="5%">状态</td>
						<td rowspan="2" width="3%">等级</td>
						<td rowspan="2" width="5%">期初库存</td>
						<td colspan="4" width="20%">{{$year_from}}预算   
							<span class="badge badge-danger" id="ToggleCompare" style="cursor: pointer;">切换环比 实际/预算</span>
						</td>
						<td colspan="4" width="20%" class="showCurrent">实际完成</td>
						<td colspan="4" width="20%" class="showCurrent">环比</td>

						<td colspan="4" width="20%" class="showCompare" style="display: none;">{{$year_to.'预算'}}</td>
						<td colspan="4" width="20%" class="showCompare" style="display: none;">环比</td>

						
						<td rowspan="2" width="6%">状态</td>
					  </tr>
					  <tr class="head" >
						<td width="5%">销量</td>
						<td width="5%">销售额</td>
						<td width="5%">利润率</td>
						<td width="5%">经济效益</td>

						<td width="5%" class="showCurrent">销量</td>
						<td width="5%" class="showCurrent">销售额</td>
						<td width="5%" class="showCurrent">利润率</td>
						<td width="5%" class="showCurrent">经济效益</td>

						<td width="5%" class="showCurrent">销量</td>
						<td width="5%" class="showCurrent">销售额</td>
						<td width="5%" class="showCurrent">利润率</td>
						<td width="5%" class="showCurrent">经济效益</td>

						<td width="5%" class="showCompare" style="display: none;">销量</td>
						<td width="5%" class="showCompare" style="display: none;">销售额</td>
						<td width="5%" class="showCompare" style="display: none;">利润率</td>
						<td width="5%" class="showCompare" style="display: none;">经济效益</td>

						<td width="5%" class="showCompare" style="display: none;">销量</td>
						<td width="5%" class="showCompare" style="display: none;">销售额</td>
						<td width="5%" class="showCompare" style="display: none;">利润率</td>
						<td width="5%" class="showCompare" style="display: none;">经济效益</td>

						

					  </tr>
					  </thead>
					  <tbody>
					  @foreach ($datas as $data)
					  
					  <?php
					  $qty_z = $data->qty1-$data->qty2;
					  $amount_z = $data->amount1-$data->amount2;
					  $profit_z = (($data->amount1==0)?0:round($data->economic1/$data->amount1,4)*100)-(($data->amount2==0)?0:round($data->economic2/$data->amount2,4)*100);
					  $economic_z = $data->economic1-$data->economic2;


					  $qty_c = $data->qty1-$data->qty3;
					  $amount_c = $data->amount1-$data->amount3;
					  $profit_c = (($data->amount1==0)?0:round($data->economic1/$data->amount1,4)*100)-(($data->amount3==0)?0:round($data->economic3/$data->amount3,4)*100);
					  $economic_c = $data->economic1-$data->economic3;
					   ?>
					  <tr>
					    <td>
						@if(in_array($data->budget_status,array_keys(getBudgetRuleForRole())) && $data->budget_status>=1)
						<input type="checkbox" name="budget_id[]" value="{{$data->budget_id}}" />
						@endif
						</td>
						<td style="overflow: hidden;text-overflow:ellipsis;white-space: nowrap;"><a href="{{url('/budgets/edit?sku='.$data->sku.'&site='.$data->site.'&year='.$year.'&quarter='.$quarter)}}">{{$data->sku}}</a></td>
						<td style="overflow: hidden;text-overflow:ellipsis;white-space: nowrap;">{{$data->description}}</td>
						<td>{{(strtoupper(substr($data->site,-2))=='OM')?'US':strtoupper(substr($data->site,-2))}}</td>
						<td>{{array_get(getSkuStatuses(),$data->status)}}</td>
						<td>{{($data->level=='0')?'S':$data->level}}</td>
						<td>{{$data->stock}}</td>
						<td>{{$data->qty1}}</td>
						<td>{{$data->amount1}}</td>
						<td>{{($data->amount1==0)?0:round($data->economic1/$data->amount1,4)*100}}%</td>
						<td>{{$data->economic1}}</td>

						<td class="showCurrent">{{$data->qty3}}</td>
						<td class="showCurrent">{{$data->amount3}}</td>
						<td class="showCurrent">{{($data->amount3==0)?0:round($data->economic3/$data->amount3,4)*100}}%</td>
						<td class="showCurrent">{{$data->economic3}}</td>
						<td class="showCurrent"><span class="{{($qty_c>0)?'red':'green'}}">{{$qty_c}}</span></td>
						<td class="showCurrent"><span class="{{($amount_c>0)?'red':'green'}}">{{round($amount_c,2)}}</span></td>
						<td class="showCurrent"><span class="{{($profit_c>0)?'red':'green'}}">{{round($profit_c,2)}}%</span></td>
						<td class="showCurrent"><span class="{{($economic_c>0)?'red':'green'}}">{{round($economic_c,2)}}</span></td>


						<td class="showCompare" style="display: none;">{{$data->qty2}}</td>
						<td class="showCompare" style="display: none;">{{$data->amount2}}</td>
						<td class="showCompare" style="display: none;">{{($data->amount2==0)?0:round($data->economic2/$data->amount2,4)*100}}%</td>
						<td class="showCompare" style="display: none;">{{$data->economic2}}</td>
						<td class="showCompare" style="display: none;"><span class="{{($qty_z>0)?'red':'green'}}">{{$qty_z}}</span></td>
						<td class="showCompare" style="display: none;"><span class="{{($amount_z>0)?'red':'green'}}">{{round($amount_z,2)}}</span></td>
						<td class="showCompare" style="display: none;"><span class="{{($profit_z>0)?'red':'green'}}">{{round($profit_z,2)}}%</span></td>
						<td class="showCompare" style="display: none;"><span class="{{($economic_z>0)?'red':'green'}}">{{round($economic_z,2)}}</span></td>

						
	
						<td><a href="{{url('/budgets/edit?sku='.$data->sku.'&site='.$data->site.'&year='.$year.'&quarter='.$quarter)}}">{{array_get(getBudgetStageArr(),($data->budget_status)??0)}}</a></td>
					  </tr>
					  @endforeach
					  
					   <tr class="head">
					    <td colspan="6" style="text-align:right"> 合计：</td>
						<td>{{$sum->stock}}</td>
						<td>{{$sum->qty1}}</td>
						<td>{{$sum->amount1}}</td>
						<td>{{($sum->amount1==0)?0:round($sum->economic1/$sum->amount1,4)*100}}%</td>
						<td>{{$sum->economic1}}</td>

						<td class="showCurrent">{{$sum->qty3}}</td>
						<td class="showCurrent">{{$sum->amount3}}</td>
						<td class="showCurrent">{{($sum->amount3==0)?0:round($sum->economic3/$sum->amount3,4)*100}}%</td>
						<td class="showCurrent">{{$sum->economic3}}</td>
						<td class="showCurrent"><span class="{{($sum->qty1-$sum->qty3>0)?'red':'green'}}">{{$sum->qty1-$sum->qty3}}</span></td>
						<td class="showCurrent"><span class="{{($sum->amount1-$sum->amount3>0)?'red':'green'}}">{{round($sum->amount1-$sum->amount3,2)}}</span></td>
						<td class="showCurrent"><span class="{{((($sum->amount1==0)?0:round($sum->economic1/$sum->amount1,4)*100)-(($sum->amount3==0)?0:round($sum->economic3/$sum->amount3,4)*100)>0)?'red':'green'}}">{{(($sum->amount1==0)?0:round($sum->economic1/$sum->amount1,4)*100)-(($sum->amount3==0)?0:round($sum->economic3/$sum->amount3,4)*100)}}%</span></td>
						<td class="showCurrent"><span class="{{($sum->economic1-$sum->economic3>0)?'red':'green'}}">{{round($sum->economic1-$sum->economic3,2)}}</span></td>


						<td class="showCompare" style="display: none;">{{$sum->qty2}}</td>
						<td class="showCompare" style="display: none;">{{$sum->amount2}}</td>
						<td class="showCompare" style="display: none;">{{($sum->amount2==0)?0:round($sum->economic2/$sum->amount2,4)*100}}%</td>
						<td class="showCompare" style="display: none;">{{$sum->economic2}}</td>
						<td class="showCompare" style="display: none;"><span class="{{($sum->qty1-$sum->qty2>0)?'red':'green'}}">{{$sum->qty1-$sum->qty2}}</span></td>
						<td class="showCompare" style="display: none;"><span class="{{($sum->amount1-$sum->amount2>0)?'red':'green'}}">{{round($sum->amount1-$sum->amount2,2)}}</span></td>
						<td class="showCompare" style="display: none;"><span class="{{((($sum->amount1==0)?0:round($sum->economic1/$sum->amount1,4)*100)-(($sum->amount2==0)?0:round($sum->economic2/$sum->amount2,4)*100)>0)?'red':'green'}}">{{(($sum->amount1==0)?0:round($sum->economic1/$sum->amount1,4)*100)-(($sum->amount2==0)?0:round($sum->economic2/$sum->amount2,4)*100)}}%</span></td>
						<td class="showCompare" style="display: none;"><span class="{{($sum->economic1-$sum->economic2>0)?'red':'green'}}">{{round($sum->economic1-$sum->economic2,2)}}</span></td>

						
	
						<td></td>
					  </tr>
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

<div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" >
			<div class="modal-body" >
				<img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
				<span>Loading... </span>
			</div>
		</div>
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
		startView:2,    
		maxViewMode: 2,
		minViewMode:2,    
		autoclose: true
	});
	$('#ToggleCompare').click(function(){
		if($('.showCurrent').is(":hidden")){
			$('.showCompare').hide();
			$('.showCurrent').show();
		}else{
			$('.showCurrent').hide();
			$('.showCompare').show();	
		}
		
	});
	$('.group-checkable',$('.table')).change(function() {
		var set = $('.table').find('tbody > tr > td:nth-child(1) input[type="checkbox"]');
		var checked = $(this).prop("checked");
		$(set).each(function() {
			$(this).prop("checked", checked);
		});
	});
});

$(function() {
    $("#ajax").on("hidden.bs.modal",function(){
        $(this).find('.modal-content').html('<div class="modal-body"><img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"><span>Loading... </span></div>');
    });
	
	$("#vl_list_export").click(function(){
		location.href='/budgets/export?user_id='+(($("select[name='user_id[]']").val())?$("select[name='user_id[]']").val():'')+'&year_from='+$("select[name='year_from']").val()+'&bgbu='+$("select[name='bgbu']").val()+'&site='+$("select[name='site']").val()+'&level='+$("select[name='level']").val()+'&sku_status='+$("select[name='sku_status']").val()+'&b_status='+$("select[name='b_status']").val()+'&sku='+$('input[name="sku"]').val();
	});
});


</script>


@endsection
