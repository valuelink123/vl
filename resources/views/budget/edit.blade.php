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

.table-head{padding-right:17px;background-color:#999;color:#000;}
.table-body{width:100%; max-height:500px;overflow-y:scroll;}
.table-head table,.table-body table{width:100%;}
.table-body table tr:nth-child(2n+1){background-color:#f2f2f2;}
.editable-input textarea.form-control {width:500px;font-size:12px;}
    </style>
	<div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">	
                    <div class="table-container">
					
					<div class="row" >
						<div class="col-md-4">
						<a href="{{($remember_list_url)??url('budgets')}}"><button type="button" class="btn btn-sm green-meadow">返回列表</button></a>
						<div class="btn-group" {{($budget->status>0)?'':'style="display:none;"'}}>
							<button type="button" class="btn btn-sm green-meadow">切换周期</button>
							<button type="button" class="btn btn-sm green-meadow dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<i class="fa fa-angle-down"></i>
							</button>
							<ul class="dropdown-menu" role="menu">
								<li>
									<a href="{{'/budgets/edit?sku='.$base_data['sku'].'&site='.$base_data['site'].'&year='.$year.'&quarter='.$quarter.'&showtype=seasons'}}"> 季度 </a>
								</li>
								<li>
									<a href="{{'/budgets/edit?sku='.$base_data['sku'].'&site='.$base_data['site'].'&year='.$year.'&quarter='.$quarter.'&showtype=months'}}"> 月 </a>
								</li>
								<li>
									<a href="{{'/budgets/edit?sku='.$base_data['sku'].'&site='.$base_data['site'].'&quarter='.$quarter.'&year='.$year}}"> 周 (填写模块)</a>
								</li>
								<li>
									<a href="{{'/budgets/edit?sku='.$base_data['sku'].'&site='.$base_data['site'].'&year='.$year.'&quarter='.$quarter.'&showtype=days'}}"> 日 </a>
								</li>
							</ul>
						</div>
						
						
						</div>
						<div class="col-md-8">
						<div class="form-upload">
						<form action="{{url('budgets/upload')}}" method="post" enctype="multipart/form-data" class="pull-right " >
						<div class=" pull-left">

							<a href="{{ url('/uploads/BudgetsUpload/data.csv')}}">Import Template</a>	
						</div>
						<div class="pull-left">
							{{ csrf_field() }}
								 <input type="file" name="importFile"  />
								 <input type="hidden" name="budget_id" value="{{$budget_id}}"  />
						</div>
						<div class=" pull-left">
							<button type="submit" class="btn blue btn-sm" id="data_search">Upload</button>

						</div>
						</form>
						</div>
						
						
						</div>
						
					</div>
					<table class="table table-striped table-bordered table-hover tbl1">
					<colgroup>
			<col width="4%"></col>
			<col width="8%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="4%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="16%"></col>
			<col width="16%"></col>
			<col width="5%"></col>
			</colgroup>
					<thead>
					  <tr class="head" >
					  	<td width="4%">站点</td>
						<td width="8%">SKU</td>
						<td width="5%">状态</td>
						<td width="5%">等级</td>
						<td width="4%">不含税采购单价(CNY)</td>
						<td width="4%">关税税率</td>
						<td width="4%">头程运费(CNY)</td>
						<td width="5%">佣金比率</td>
						<td width="4%">拣配费(外币)</td>
						<td width="5%">异常率</td>
						<td width="5%">汇率</td>
						<td width="5%">期初库存</td>
						<td width="5%">销售员</td>
						<td width="16%">SKU描述</td>
						<td width="16%">备注</td>
						<td width="5%">预算状态</td>
					  </tr>
					  </thead>
					  <tbody>
					  <tr>
						<td>{{$site_code}}</td>
						<td>{{$base_data['sku']}}</td>
						<td>{{array_get(getSkuStatuses(),$base_data['then_status'])}}</td>
						<td>{{($base_data['then_level']=='0')?'S':$base_data['then_level']}}
						<input type="hidden" id ="cold_storagefee"  value="{{$base_data['cold_storagefee']}}" />
						<input type="hidden" id ="hot_storagefee"  value="{{$base_data['hot_storagefee']}}" />
						
						</td>
						<td><a class="budgetskus_cost editable" title="成本" href="javascript:;" id="{{$budget_id}}-cost" data-pk="{{$budget_id}}-cost" data-type="text" data-placement="bottom">{{round($base_data['then_cost'],2)}}</a></td>
						<td><span id = "tax">{{$base_data['tax']}}</span></td>
						<td><span id = "headshipfee">{{$base_data['headshipfee']}}</span></td>
						<td><a class="budgetskus_common_fee editable" title="佣金比率%" href="javascript:;" id="{{$budget_id}}-common_fee" data-pk="{{$budget_id}}-common_fee" data-type="text" data-placement="bottom">{{$base_data['then_common_fee']*100}}</a>%</td>
						<td><a class="budgetskus_pick_fee editable" title="拣配费" href="javascript:;" id="{{$budget_id}}-pick_fee" data-pk="{{$budget_id}}-pick_fee" data-type="text" data-placement="bottom">{{$base_data['then_pick_fee']}}</a></td>
						<td>{{$base_data['then_exception']*100}}%</td>
						<td><span id = "rate">{{$rate}}</span></td>
						<td><a class="budgetskus_stock editable" title="期初库存" href="javascript:;" id="{{$budget_id}}-stock" data-pk="{{$budget_id}}-stock" data-type="text" data-placement="bottom">{{$base_data['then_stock']}}</a></td>
						<td>{{array_get(getUsers('sap_seller'),$base_data['then_sap_seller_id'],$base_data['then_sap_seller_id'])}}</td>
						<td>{{$base_data['then_description']}}</td>
						<td><a class="budget_remark" title="备注" href="javascript:;" id="{{$budget_id}}-remark" data-pk="{{$budget_id}}-remark" data-type="textarea" data-rows="10" data-placement="left" data-placeholder="Your response here...">{{$budget->remark}}</a></td>
						<td><a class="budget_status" href="javascript:;" data-placement="left" id="{{$budget_id}}-status" data-type="select" data-pk="{{$budget_id}}-status" data-value="{{($budget->status)??0}}">{{array_get(getBudgetStageArr(),($budget->status)??0)}}</a>
						</td>
					  </tr>
					  </tbody>
					</table>
					
					
					<div class="table-head">
					<table class="table table-striped table-bordered table-hover" style="margin-bottom:0px;">
			<colgroup>
			<col width="4%"></col>
			<col width="8%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
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
			</colgroup>
					  <thead>
					  <tr class="head" >
						<td rowspan="2" width="4%">
						@if($showtype=='seasons')
						季
						@elseif($showtype=='months')
						月
						@elseif($showtype=='days')
						日
						@else
						周
						@endif
						</td>
						<td rowspan="2" width="8%">日期</td>
						<td colspan="7" width="33%">{{$year}}Q{{$quarter}} 版本销售预算</td>
						<td rowspan="2" width="5%">销量</td>
						<td colspan="3" width="15%">毛利</td>
						<td colspan="3" width="15%">平台费用</td>
						<td rowspan="2" width="5%">仓储费</td>
						<td rowspan="2" width="5%">推广费</td>
						<td rowspan="2" width="5%">资金占用成本</td>
						<td rowspan="2" width="5%">经济效益</td>
					  </tr>
					  <tr class="head" >
						<td width="5%">排名目标</td>
						<td width="5%">正常售价(外币)</td>
						<td width="4%">正常销量</td>
						<td width="5%">促销价(外币)</td>
						<td width="4%">促销销量</td>
						<td width="5%">推广费比率</td>
						<td width="5%">异常率</td>
						<td width="5%">收入</td>
						<td width="5%">成本</td>
						<td width="5%">毛利</td>
						<td width="5%">佣金</td>
						<td width="5%">操作费</td>
						<td width="5%">合计</td>
					  </tr>
					  </thead>
					 </table>
					</div>
	
					<div class="table-body">
					<table class="table table-striped table-bordered table-hover" style="margin-bottom:0px;">
			<colgroup>
			<col width="4%"></col>
			<col width="8%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="4%"></col>
			<col width="5%"></col>
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
			</colgroup>

					  <tbody>
					  
					  <?php 
					  $s_t_qty=$s_t_income=$s_t_cost=$s_t_common_fee=$s_t_pick_fee=$s_t_storage_fee=$s_t_promotion_fee=$s_t_amount_fee=0;
					  if($showtype=='seasons') {
					  	$i=0;
					  	foreach($datas as $k=>$v){
					  ?>
					  <tr>
						<td>{{++$i}}
						</td>
						<td>{{$year.'年'.(3*($i-1)+1).'月 -- '.$year.'年'.(3*($i-1)+3).'月'}}</td>
						<td>≈{{$v['ranking']}}</td>
						<td>{{($v['qty']!=0)?round($v['amount_n']/$v['qty'],2):0}}</td>
						<td>{{$v['qty']}}</td>
						<td>{{($v['promote_qty']!=0)?round($v['amount_p']/$v['promote_qty'],2):0}}</td>
						<td>{{$v['promote_qty']}}</td>
						<td>{{($v['income']!=0)?round($v['promotion_fee']/$v['income'],4)*100:0}}%</td>
						<td>{{(($v['amount_n']+$v['amount_p'])!=0)?round($v['exception_fee']/($v['amount_n']+$v['amount_p']),4)*100:0}}%</td>
						<td>{{$v['qty']+$v['promote_qty']}}</td>
						<td>{{$v['income']}}</td>
						<td>{{$v['cost']}}</td>
						<td>{{round($v['income']-$v['cost'],2)}}</td>
						<td>{{$v['common_fee']}}</td>
						<td>{{$v['pick_fee']}}</td>
						<td>{{round($v['common_fee']+$v['pick_fee'],2)}}</td>
						<td>{{$v['storage_fee']}}</td>
						<td>{{$v['promotion_fee']}}</td>
						<td>{{$v['amount_fee']}}</td>
						<td>{{round($v['income']-$v['cost']-$v['common_fee']-$v['pick_fee']-$v['storage_fee']-$v['promotion_fee']-$v['amount_fee'],2)}}</td>
					  </tr>
					  <?php 
					  		$s_t_qty+=$v['qty']+$v['promote_qty'];
							$s_t_income+=$v['income'];
							$s_t_cost+=$v['cost'];
							$s_t_common_fee+=$v['common_fee'];
							$s_t_pick_fee+=$v['pick_fee'];
							$s_t_storage_fee+=$v['storage_fee'];
							$s_t_promotion_fee+=$v['promotion_fee'];
							$s_t_amount_fee+=$v['amount_fee'];
					  	} 
					  }elseif($showtype=='months'){
					  
					    $i=0;
					  	foreach($datas as $k=>$v){
					  ?>
					  <tr>
						<td>{{++$i}}
						</td>
						<td>{{$v['month']}}</td>
						<td>≈{{$v['ranking']}}</td>
						<td>{{round($v['price'],2)}}</td>
						<td>{{$v['qty']}}</td>
						<td>{{round($v['promote_price'],2)}}</td>
						<td>{{$v['promote_qty']}}</td>
						<td>{{round($v['promotion']*100,2)}}%</td>
						<td>{{(($v['qty']*$v['price']+$v['promote_qty']*$v['promote_price'])!=0)?round($v['exception_fee']/($v['qty']*$v['price']+$v['promote_qty']*$v['promote_price']),4)*100:0}}%</td>
						<td>{{$v['qty']+$v['promote_qty']}}</td>
						<td>{{$v['income']}}</td>
						<td>{{$v['cost']}}</td>
						<td>{{round($v['income']-$v['cost'],2)}}</td>
						<td>{{$v['common_fee']}}</td>
						<td>{{$v['pick_fee']}}</td>
						<td>{{round($v['common_fee']+$v['pick_fee'],2)}}</td>
						<td>{{$v['storage_fee']}}</td>
						<td>{{$v['promotion_fee']}}</td>
						<td>{{$v['amount_fee']}}</td>
						<td>{{round($v['income']-$v['cost']-$v['common_fee']-$v['pick_fee']-$v['storage_fee']-$v['promotion_fee']-$v['amount_fee'],2)}}</td>
					  </tr>
					  <?php 
					  		$s_t_qty+=$v['qty']+$v['promote_qty'];
							$s_t_income+=$v['income'];
							$s_t_cost+=$v['cost'];
							$s_t_common_fee+=$v['common_fee'];
							$s_t_pick_fee+=$v['pick_fee'];
							$s_t_storage_fee+=$v['storage_fee'];
							$s_t_promotion_fee+=$v['promotion_fee'];
							$s_t_amount_fee+=$v['amount_fee'];
					  	} 
					  }elseif($showtype=='days') {
					  
					  	$i=0;
					  	foreach($datas as $k=>$v){
					  ?>
					  <tr>
						<td>{{++$i}}
						</td>
						<td>{{$v['date']}}</td>
						<td>{{$v['ranking']}}</td>
						<td>{{round($v['price'],2)}}</td>
						<td>{{$v['qty']}}</td>
						<td>{{round($v['promote_price'],2)}}</td>
						<td>{{$v['promote_qty']}}</td>
						<td>{{round($v['promotion']*100,2)}}%</td>
						<td>{{round($v['exception']*100,2)}}%</td>
						<td>{{$v['qty']+$v['promote_qty']}}</td>
						<td>{{$v['income']}}</td>
						<td>{{$v['cost']}}</td>
						<td>{{round($v['income']-$v['cost'],2)}}</td>
						<td>{{$v['common_fee']}}</td>
						<td>{{$v['pick_fee']}}</td>
						<td>{{round($v['common_fee']+$v['pick_fee'],2)}}</td>
						<td>{{$v['storage_fee']}}</td>
						<td>{{$v['promotion_fee']}}</td>
						<td>{{$v['amount_fee']}}</td>
						<td>{{round($v['income']-$v['cost']-$v['common_fee']-$v['pick_fee']-$v['storage_fee']-$v['promotion_fee']-$v['amount_fee'],2)}}</td>
					  </tr>
					  <?php 
					  		$s_t_qty+=$v['qty']+$v['promote_qty'];
							$s_t_income+=$v['income'];
							$s_t_cost+=$v['cost'];
							$s_t_common_fee+=$v['common_fee'];
							$s_t_pick_fee+=$v['pick_fee'];
							$s_t_storage_fee+=$v['storage_fee'];
							$s_t_promotion_fee+=$v['promotion_fee'];
							$s_t_amount_fee+=$v['amount_fee'];
					  	} 
					  
					  }else{
						  $weeks = date("W", mktime(0, 0, 0, 12, 28, $year));
						  for($i=1;$i<=$weeks;$i++){
					  ?>
					  <tr>
						<td>{{$i}}
						<input type="hidden" id ="{{$budget_id.'-'.$i}}-stock_end"  value="0" />
						</td>
						<td>{{date("Ymd", strtotime($year . 'W' . sprintf("%02d",$i))).'-'.date("Ymd", strtotime($year . 'W' . sprintf("%02d",$i))+86400*6)}}</td>
						<td><a class="sku_ranking editable" title="{{$year.' 第'.$i.'周排名'}}" href="javascript:;" id="{{$budget_id.'-'.$i}}-ranking" data-pk="{{$budget_id.'-'.$i}}-ranking" data-type="text">{{array_get($datas,$i.'.ranking')}}</a></td>
						<td><a class="sku_price editable" title="{{$year.' 第'.$i.'周正常售价'}}" href="javascript:;" id="{{$budget_id.'-'.$i}}-price" data-pk="{{$budget_id.'-'.$i}}-price" data-type="text"> {{round(array_get($datas,$i.'.price'),2)}} </a></td>
						<td><a class="sku_qty editable" title="{{$year.' 第'.$i.'周正常销量'}}" href="javascript:;" id="{{$budget_id.'-'.$i}}-qty" data-pk="{{$budget_id.'-'.$i}}-qty" data-type="text"> {{round(array_get($datas,$i.'.qty'))}} </a></td>
						<td><a class="sku_pro_price editable" title="{{$year.' 第'.$i.'周促销售价'}}" href="javascript:;" id="{{$budget_id.'-'.$i}}-promote_price" data-pk="{{$budget_id.'-'.$i}}-promote_price" data-type="text"> {{round(array_get($datas,$i.'.promote_price'),2)}} </a></td>
						<td><a class="sku_pro_qty editable" title="{{$year.' 第'.$i.'周促销销量'}}" href="javascript:;" id="{{$budget_id.'-'.$i}}-promote_qty" data-pk="{{$budget_id.'-'.$i}}-promote_qty" data-type="text"> {{round(array_get($datas,$i.'.promote_qty'))}} </a></td>
						<td><a class="sku_pro_per editable" title="{{$year.' 第'.$i.'周推广费比率%'}}" href="javascript:;" id="{{$budget_id.'-'.$i}}-promotion" data-pk="{{$budget_id.'-'.$i}}-promotion" data-type="text"> {{round(array_get($datas,$i.'.promotion')*100,2)}} </a>%</td>
						<td><a class="sku_exception_per editable" title="{{$year.' 第'.$i.'周异常率%'}}" href="javascript:;" id="{{$budget_id.'-'.$i}}-exception" data-pk="{{$budget_id.'-'.$i}}-exception" data-type="text"> {{round(array_get($datas,$i.'.exception')*100,2)}} </a>%</td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_qty">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_income">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_cost">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_profit">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_commonfee">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_pickfee">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_fee">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_storagefee">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_profee">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_amountfee">0</span></td>
						<td><span id="{{$budget_id.'-'.$i}}-week_line_economic">0</span></td>
					  </tr>
					  <?php 
					  	} 
					  }
					  ?>
					  
					  
					  </tbody>
					</table>
					</div>
					<div class="table-head">
					<table class="table table-striped table-bordered table-hover" style="margin-bottom:50px;">
					<colgroup>
					<col width="4%"></col>
					<col width="8%"></col>
					<col width="5%"></col>
					<col width="5%"></col>
					<col width="4%"></col>
					<col width="5%"></col>
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
					</colgroup>
					  <thead>
					  <tr class="head">
						<td colspan="8">合计：</td>
						<td>0</td>
						<?php if($showtype){ ?>
						<td>{{round($s_t_qty)}}</td>
						<td>{{round($s_t_income,2)}}</td>
						<td>{{round($s_t_cost,2)}}</td>
						<td>{{round($s_t_income-$s_t_cost,2)}}</td>
						<td>{{round($s_t_common_fee,2)}}</td>
						<td>{{round($s_t_pick_fee,2)}}</td>
						<td>{{round($s_t_common_fee+$s_t_pick_fee,2)}}</td>
						<td>{{round($s_t_storage_fee,2)}}</td>
						<td>{{round($s_t_promotion_fee,2)}}</td>
						<td>{{round($s_t_amount_fee,2)}}</td>
						<td>{{round($s_t_income-$s_t_cost-$s_t_common_fee-$s_t_pick_fee-$s_t_storage_fee-$s_t_promotion_fee-$s_t_amount_fee,2)}}</td>
						
						
						<?php }else{ ?>
						<td><span id="{{$budget_id}}-total_qty">0</span></td>
						<td><span id="{{$budget_id}}-total_income">0</span></td>
						<td><span id="{{$budget_id}}-total_cost">0</span></td>
						<td><span id="{{$budget_id}}-total_profit">0</span></td>
						<td><span id="{{$budget_id}}-total_commonfee">0</span></td>
						<td><span id="{{$budget_id}}-total_pickfee">0</span></td>
						<td><span id="{{$budget_id}}-total_fee">0</span></td>
						<td><span id="{{$budget_id}}-total_storagefee">0</span></td>
						<td><span id="{{$budget_id}}-total_profee">0</span></td>
						<td><span id="{{$budget_id}}-total_amountfee">0</span></td>
						<td><span id="{{$budget_id}}-total_economic">0</span></td>
						<?php }?>
					  </tr>
					  </thead>
					 </table>
					</div>
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

var FormEditable = function() {

    $.mockjaxSettings.responseTime = 500;

    $.fn.editable.defaults.inputclass = 'form-control';
    $.fn.editable.defaults.url = '/budget';

    var initBasetables = function() {
		var stages = [];
        <?php 
		$budgetRule = getBudgetRuleForRole();
		if($base_data['then_sap_seller_id'] && $base_data['then_sap_seller_id']==Auth::user()->sap_seller_id && !(($budget->status)??0)) $budgetRule = array_slice(getBudgetStageArr(),0,2);
		foreach($budgetRule as $k=>$v) {?>
		stages.push({
			value: "<?php echo $k?>",
			text: "<?php echo $v?>"
		});
		<?php }?>
		<?php if(in_array(intval(($budget->status)??0),array_keys($budgetRule))) {?>
		$('.budget_status').editable({
            inputclass: 'form-control input-medium',
            source: stages,
			<?php if(!$showtype) {?>
			params: function(params) {
				var weeks = {{date("W", mktime(0, 0, 0, 12, 28, $year))}};
				var budget_id = {{$budget_id}};
				for (let i = 1;i <= weeks;i++){
					params[i+'-week_line_data'] = parseFloat($('#'+budget_id+'-'+i+'-week_line_income').text())+'|'+parseFloat($('#'+budget_id+'-'+i+'-week_line_cost').text())+'|'+parseFloat($('#'+budget_id+'-'+i+'-week_line_commonfee').text())+'|'+parseFloat($('#'+budget_id+'-'+i+'-week_line_pickfee').text())+'|'+parseFloat($('#'+budget_id+'-'+i+'-week_line_profee').text())+'|'+parseFloat($('#'+budget_id+'-'+i+'-week_line_amountfee').text())+'|'+parseFloat($('#'+budget_id+'-'+i+'-week_line_storagefee').text());
					params['total_qty'] = $('#'+budget_id+'-total_qty').text();
					params['total_income'] = $('#'+budget_id+'-total_income').text();
					params['total_cost'] = $('#'+budget_id+'-total_cost').text();
					params['total_commonfee'] = $('#'+budget_id+'-total_commonfee').text();
					params['total_pickfee'] = $('#'+budget_id+'-total_pickfee').text();
					params['total_storagefee'] = $('#'+budget_id+'-total_storagefee').text();
					params['total_profee'] = $('#'+budget_id+'-total_profee').text();
					params['total_amountfee'] = $('#'+budget_id+'-total_amountfee').text();
				}
				return params;
			},
			<?php } ?>
			success: function (status) {
				$('.budget_status').data('value',status);
				initBudgettables();
				if(status==0){
					<?php if($base_data['then_sap_seller_id'] && $base_data['then_sap_seller_id']==Auth::user()->sap_seller_id && $showtype){ ?>
					location.reload();
					<?php } ?>
				}
				
			}
        });
		<?php } ?>
		$('.budget_remark').editable({
			emptytext:'N/A'
		});
		initBudgettables();
		<?php if(!$showtype) {?>
		initEndStock('<?php echo $budget_id?>-');
		<?php } ?>
	}
	var initBudgettables = function() {
		var budget_status = $('.budget_status').data('value');
		var is_seller = false;
		<?php if($base_data['then_sap_seller_id'] && $base_data['then_sap_seller_id']==Auth::user()->sap_seller_id){ ?>
		is_seller = true;
		<?php } ?>
		if(budget_status>0){
			$('.btn-group').show();
		}
		if(budget_status==0 && is_seller){
			option='enable';
			$('.form-upload').show();
		}else{
			option='disable';
			$('.form-upload').hide();
		}
		
		<?php if(!$showtype && $base_data['then_sap_seller_id'] && $base_data['then_sap_seller_id']==Auth::user()->sap_seller_id ) {?>
		$('.sku_ranking').editable({
			emptytext:'N/A'
		});		
		$('.sku_price,.sku_qty,.sku_pro_price,.sku_pro_qty,.sku_pro_per,.sku_exception_per,.budgetskus_cost,.budgetskus_common_fee,.budgetskus_pick_fee,.budgetskus_stock').editable({
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
					initEndStock(jitem);
				}
			}, 
			error: function (response) { 
				return 'remote error'; 
			} 
		});
		
		$('.sku_ranking,.sku_price,.sku_qty,.sku_pro_price,.sku_pro_qty,.sku_pro_per,.sku_exception_per,.budgetskus_cost,.budgetskus_common_fee,.budgetskus_pick_fee,.budgetskus_stock').editable(option);
		<?php } ?>
		
    }
	
	var initEndStock = function(id){
		var id = id.split('-');
		var budget_id = id[0];
		var week_id = id[1];
		var weeks = {{date("W", mktime(0, 0, 0, 12, 28, $year))}};
		if (parseFloat(week_id).toString() == "NaN") {
			for (let i = 1;i <= weeks;i++){
				initLine(budget_id,i);
			}
		}else{
			initLine(budget_id,week_id);
		}
		var stock =  parseInt($('#'+budget_id+'-stock').text());
		var first4WeeksQty = stock;
		var tax = parseFloat($('#tax').text())*0.4;
		var headshipfee = parseFloat($('#headshipfee').text());
		var cold_storagefee = parseFloat($('#cold_storagefee').val());
		var hot_storagefee = parseFloat($('#hot_storagefee').val());
		var cost = parseFloat($('#'+budget_id+'-cost').text());
		cost = parseFloat(cost*(1+tax)+headshipfee).toFixed(2);
		var avgStock=[];
		//前4周销量仓储费用
		for (let i = 1;i <= 4;i++){
			first4WeeksQty+=parseInt($('#'+budget_id+'-'+(i)+'-week_line_qty').text());
	    }
		var total_qty=0; 
		var total_income=0; 
		var total_cost=0; 
		var total_profit=0; 
		var total_commonfee=0; 
		var total_pickfee=0; 
		var total_fee=0; 
		var total_storagefee=0; 
		var total_profee=0; 
		var total_amountfee=0; 
		var total_economic=0;   
			   
		var endStock = 0;
		var startStock = stock;
		for (let i = 1;i <= weeks;i++){
			
			var week_line_qty = parseInt($('#'+budget_id+'-'+i+'-week_line_qty').text());
			var week_line_profit = parseFloat($('#'+budget_id+'-'+i+'-week_line_profit').text());
			var week_line_fee = parseFloat($('#'+budget_id+'-'+i+'-week_line_fee').text());
			var week_line_profee = parseFloat($('#'+budget_id+'-'+i+'-week_line_profee').text());
			var week_line_income = parseFloat($('#'+budget_id+'-'+i+'-week_line_income').text());
			var week_line_cost = parseFloat($('#'+budget_id+'-'+i+'-week_line_cost').text());
			var week_line_commonfee = parseFloat($('#'+budget_id+'-'+i+'-week_line_commonfee').text());
			var week_line_pickfee = parseFloat($('#'+budget_id+'-'+i+'-week_line_pickfee').text());
		
			stock = (stock-week_line_qty>0)?stock-week_line_qty:0;
			var n_stock = 0;
			if(i<=weeks-7){
			   for (let ix = 1;ix <= 7;ix++){
					n_stock+=parseInt($('#'+budget_id+'-'+(i+ix)+'-week_line_qty').text());
			   }
			}else if(i==weeks){
			   n_stock=parseInt(parseInt($('#'+budget_id+'-'+(i)+'-week_line_qty').text())*5.688);  
			}else{
			   for (let ix = i+1;ix <= weeks;ix++){
					n_stock+=parseInt($('#'+budget_id+'-'+(ix)+'-week_line_qty').text());
			   }
			   if(i==weeks-1) n_stock=parseInt(n_stock*5.919);
			   if(i==weeks-2) n_stock=parseInt(n_stock*3.019);
			   if(i==weeks-3) n_stock=parseInt(n_stock*2.038);
			   if(i==weeks-4) n_stock=parseInt(n_stock*1.579);
			   if(i==weeks-5) n_stock=parseInt(n_stock*1.306);
			   if(i==weeks-6) n_stock=parseInt(n_stock*1.127);
			}
			<?php if($base_data['then_status']==1 || $base_data['then_status']==2 || $base_data['then_status']==99){ ?>
			endStock = stock>n_stock?stock:n_stock;
			<?php }else{ ?>
			endStock = stock;
			<?php }?>
			
			endStock = endStock>0?endStock:0;
			$('#'+budget_id+'-'+(i)+'-stock_end').val(endStock);
			//平均库存
			avgStock[i] = parseInt((startStock+endStock)/2);
			startStock = endStock;
			var week_line_amountfee = parseFloat(cost*avgStock[i]*0.00375*1.3).toFixed(2);
			$('#'+budget_id+'-'+(i)+'-week_line_amountfee').text(week_line_amountfee);
			
			if(i<=4){
				var week_line_storagefee = parseFloat(first4WeeksQty*0.656*hot_storagefee).toFixed(2);
			}else{
				var week_line_storagefee = parseFloat(avgStock[i-4]*(i>43?hot_storagefee:cold_storagefee)).toFixed(2);
			}
			$('#'+budget_id+'-'+(i)+'-week_line_storagefee').text(week_line_storagefee);  
			
			var week_line_economic = parseFloat(week_line_profit-week_line_fee-week_line_profee-week_line_amountfee-week_line_storagefee).toFixed(2);
			
			$('#'+budget_id+'-'+(i)+'-week_line_economic').text(week_line_economic);
			
			total_qty+=parseInt(week_line_qty);
			total_income+=parseFloat(week_line_income);
			total_cost+=parseFloat(week_line_cost);
			total_profit+=parseFloat(week_line_profit);
			total_commonfee+=parseFloat(week_line_commonfee);
			total_pickfee+=parseFloat(week_line_pickfee);
			total_fee+=parseFloat(week_line_fee);
			total_storagefee+=parseFloat(week_line_storagefee);
			total_profee+=parseFloat(week_line_profee);
			total_amountfee+=parseFloat(week_line_amountfee);
			total_economic+=parseFloat(week_line_economic);
		}
		
		$('#'+budget_id+'-total_qty').text(total_qty);
		$('#'+budget_id+'-total_income').text(total_income.toFixed(2));
		$('#'+budget_id+'-total_cost').text(total_cost.toFixed(2));
		$('#'+budget_id+'-total_profit').text(total_profit.toFixed(2));
		$('#'+budget_id+'-total_commonfee').text(total_commonfee.toFixed(2));
		$('#'+budget_id+'-total_pickfee').text(total_pickfee.toFixed(2));
		$('#'+budget_id+'-total_fee').text(total_fee.toFixed(2));
		$('#'+budget_id+'-total_storagefee').text(total_storagefee.toFixed(2));
		$('#'+budget_id+'-total_profee').text(total_profee.toFixed(2));
		$('#'+budget_id+'-total_amountfee').text(total_amountfee.toFixed(2));
		$('#'+budget_id+'-total_economic').text(total_economic.toFixed(2));
		
	}

	
	var initLine = function(budget_id,week_id){
		var rate = parseFloat($('#rate').text());
		var tax = parseFloat($('#tax').text())*0.4;
		var headshipfee = parseFloat($('#headshipfee').text());
		var cost = parseFloat($('#'+budget_id+'-cost').text());
		var common_fee = parseFloat($('#'+budget_id+'-common_fee').text())/100;
		var pick_fee = parseFloat(parseFloat($('#'+budget_id+'-pick_fee').text())*rate).toFixed(2);
		cost = parseFloat(cost*(1+tax)+headshipfee).toFixed(2);
		
		
		var stock = parseInt($('#'+budget_id+'-stock').text());
		var qty = parseInt($('#'+budget_id+'-'+week_id+'-qty').text());
		var promote_qty = parseInt($('#'+budget_id+'-'+week_id+'-promote_qty').text());
		var price = parseFloat($('#'+budget_id+'-'+week_id+'-price').text());
		var promote_price = parseFloat($('#'+budget_id+'-'+week_id+'-promote_price').text());
		var promotion = parseFloat($('#'+budget_id+'-'+week_id+'-promotion').text())/100;
		var exception = parseFloat($('#'+budget_id+'-'+week_id+'-exception').text())/100;
		var week_line_qty = parseInt(qty+promote_qty);
		var week_line_income = (exception==1)?0:parseFloat((qty*price+promote_qty*promote_price)*(1-exception)*rate).toFixed(2);
		var week_line_cost = parseFloat((qty+promote_qty)*cost).toFixed(2);
		var week_line_commonfee = (exception==1)?0:parseFloat(week_line_income*common_fee+(0.2*week_line_income/(1-exception)*common_fee*exception));
		var week_line_pickfee = parseFloat(week_line_qty*pick_fee);
		var week_line_profee = parseFloat(week_line_income*promotion).toFixed(2);
		$('#'+budget_id+'-'+week_id+'-week_line_qty').text(week_line_qty);
		$('#'+budget_id+'-'+week_id+'-week_line_income').text(week_line_income);
		$('#'+budget_id+'-'+week_id+'-week_line_cost').text(week_line_cost);
		$('#'+budget_id+'-'+week_id+'-week_line_profit').text(parseFloat(week_line_income-week_line_cost).toFixed(2));
		$('#'+budget_id+'-'+week_id+'-week_line_commonfee').text(week_line_commonfee.toFixed(2));
		$('#'+budget_id+'-'+week_id+'-week_line_pickfee').text(week_line_pickfee.toFixed(2));
		$('#'+budget_id+'-'+week_id+'-week_line_fee').text(parseFloat(week_line_commonfee+week_line_pickfee).toFixed(2));
		$('#'+budget_id+'-'+week_id+'-week_line_profee').text(week_line_profee);
	}

						
    return {
        init: function() {
            initBasetables();
			$('.editable').on('hidden', function(e, reason) {
                if (reason === 'save' || reason === 'nochange') {
                    var $next = $(this).closest('td').next().find('.editable');
					if($next.length==0) $next = $(this).closest('tr').next().find('.editable').first();
					setTimeout(function() {
						$next.editable('show');
					}, 300);
                }
            });

        }
    };

}();

jQuery(document).ready(function() {
    FormEditable.init();

});
</script>


@endsection
