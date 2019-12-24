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
    </style>
	<div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">	
                    <div class="table-container">
					
					<div class="row" >
						<div class="col-md-2">
						<a href="{{$remember_list_url}}"><button type="button" class="btn btn-sm green-meadow">返回列表</button></a>
						</div>
						<div class="col-md-10">
						<form action="{{url('budgets/upload')}}" method="post" enctype="multipart/form-data" class="pull-right">
						<div class=" pull-left">

							<a href="{{ url('/uploads/BudgetsUpload/data.csv')}}" >Import Template
                                </a>	
						</div>
						<div class="pull-left">
							{{ csrf_field() }}
								 <input type="file" name="importFile"  />
								 <input type="hidden" name="budget_id" value="{{$budget_id}}"  />
						</div>
						<div class=" pull-left">
							<button type="submit" class="btn blue btn-sm" id="data_search">Upload</button>

						</div>
						</div>
						</form>
					</div>
					<table class="table table-striped table-bordered table-hover">
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
						<td>{{$base_data['status']}}</td>
						<td>{{($base_data['level']=='0')?'S':$base_data['level']}}
						<input type="hidden" id ="cold_storagefee"  value="{{$base_data['cold_storagefee']}}" />
						<input type="hidden" id ="hot_storagefee"  value="{{$base_data['hot_storagefee']}}" />
						
						</td>
						<td><a class="budgetskus_cost editable" title="成本" href="javascript:;" id="{{$budget_id}}-cost" data-pk="{{$budget_id}}-cost" data-type="text" data-placement="bottom">{{round($base_data['cost'],2)}}</a></td>
						<td><span id = "tax">{{$base_data['tax']}}</span></td>
						<td><span id = "headshipfee">{{$base_data['headshipfee']}}</span></td>
						<td><a class="budgetskus_common_fee editable" title="佣金比率%" href="javascript:;" id="{{$budget_id}}-common_fee" data-pk="{{$budget_id}}-common_fee" data-type="text" data-placement="bottom">{{$base_data['common_fee']*100}}</a>%</td>
						<td><a class="budgetskus_pick_fee editable" title="拣配费" href="javascript:;" id="{{$budget_id}}-pick_fee" data-pk="{{$budget_id}}-pick_fee" data-type="text" data-placement="bottom">{{$base_data['pick_fee']}}</a></td>
						<td><a class="budgetskus_exception editable" title="异常率%" href="javascript:;" id="{{$budget_id}}-exception" data-pk="{{$budget_id}}-exception" data-type="text" data-placement="bottom">{{$base_data['exception']*100}}</a>%</td>
						<td><span id = "rate">{{$rate}}</span></td>
						<td><a class="budgetskus_stock editable" title="期初库存" href="javascript:;" id="{{$budget_id}}-stock" data-pk="{{$budget_id}}-stock" data-type="text" data-placement="bottom">{{$base_data['stock']}}</a></td>
						<td>{{array_get(getUsers('sap_seller'),$base_data['sap_seller_id'],$base_data['sap_seller_id'])}}</td>
						<td>{{$base_data['description']}}</td>
						<td><a class="budget_remark" title="备注" href="javascript:;" id="{{$budget_id}}-remark" data-pk="{{$budget_id}}-remark" data-type="textarea" data-placement="bottom" data-placeholder="Your response here...">{{$budget->remark}}</a></td>
						<td><a class="budget_status" href="javascript:;" data-placement="left" id="{{$budget_id}}-status" data-type="select" data-pk="{{$budget_id}}-status" data-value="{{($budget->status)??0}}">{{array_get(getBudgetStageArr(),($budget->status)??0)}}</a>
						</td>
					  </tr>
					  </tbody>
					</table>
					<table class="table table-striped table-bordered table-hover">
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
						<td rowspan="2" width="4%">周</td>
						<td rowspan="2" width="8%">日期</td>
						<td colspan="6" width="28%">{{$year}}年销售预算</td>
						<td rowspan="2" width="5%">销售预测</td>
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
						<td width="5%">收入</td>
						<td width="5%">成本</td>
						<td width="5%">毛利</td>
						<td width="5%">佣金</td>
						<td width="5%">操作费</td>
						<td width="5%">合计</td>
					  </tr>
					  </thead>
					  <tbody>
					  
					  <?php 
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
						<td>0</td>
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
					  <?php } ?>
					  
					  <tr class="head">
						<td colspan="8">合计：</td>
						<td>0</td>
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
					  </tr>
					  </tbody>
					</table>
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
        <?php foreach(getBudgetRuleForRole() as $k=>$v) {?>
		stages.push({
			value: "<?php echo $k?>",
			text: "<?php echo $v?>"
		});
		<?php }?>
		$('.budget_status').editable({
            inputclass: 'form-control input-medium',
            source: stages,
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
			success: function (status) {
				$('.budget_status').data('value',status);
				initBudgettables();
			}
        });
		$('.budget_remark').editable({
			emptytext:'N/A'
		});
		initBudgettables();
		initEndStock('<?php echo $budget_id?>-');
	}
	var initBudgettables = function() {
		var budget_status = $('.budget_status').data('value');
		var is_seller = false;
		<?php if($base_data['sap_seller_id']==Auth::user()->sap_seller_id){ ?>
		is_seller = true;
		<?php } ?>
		
		$('.sku_ranking').editable({
			emptytext:'N/A'
		});		
		$('.sku_price,.sku_qty,.sku_pro_price,.sku_pro_qty,.sku_pro_per,.budgetskus_cost,.budgetskus_common_fee,.budgetskus_pick_fee,.budgetskus_exception,.budgetskus_stock').editable({
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
		
		if(budget_status==0 && is_seller){
			option='enable';
		}else{
			option='disable';
		}
		$('.sku_ranking,.sku_price,.sku_qty,.sku_pro_price,.sku_pro_qty,.sku_pro_per,.budgetskus_cost,.budgetskus_common_fee,.budgetskus_pick_fee,.budgetskus_exception,.budgetskus_stock').editable(option);
		
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
			<?php if($base_data['status']=='淘汰'){ ?>
			endStock = stock;
			<?php }else{ ?>
			endStock = stock>n_stock?stock:n_stock;
			<?php }?>
			
			endStock = endStock>0?endStock:0;
			$('#'+budget_id+'-'+(i)+'-stock_end').val(endStock);
			//平均库存
			avgStock[i] = parseInt((startStock+endStock)/2);
			startStock = endStock;
			var week_line_amountfee = parseFloat(cost*avgStock[i]*0.00375).toFixed(2);
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
		var exception = parseFloat($('#'+budget_id+'-exception').text())/100;
		var stock = parseInt($('#'+budget_id+'-stock').text());
		var qty = parseInt($('#'+budget_id+'-'+week_id+'-qty').text());
		var promote_qty = parseInt($('#'+budget_id+'-'+week_id+'-promote_qty').text());
		var price = parseFloat($('#'+budget_id+'-'+week_id+'-price').text());
		var promote_price = parseFloat($('#'+budget_id+'-'+week_id+'-promote_price').text());
		var promotion = parseFloat($('#'+budget_id+'-'+week_id+'-promotion').text())/100;
		var week_line_qty = parseInt(qty+promote_qty);
		var week_line_income = parseFloat((qty*price+promote_qty*promote_price)*(1-exception)*rate).toFixed(2);
		var week_line_cost = parseFloat((qty+promote_qty)*cost).toFixed(2);
		var week_line_commonfee = parseFloat(week_line_income*common_fee+0.2*week_line_income/(1-exception)*common_fee*exception);
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
