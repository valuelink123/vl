@extends('layouts.layout')
@section('label', 'Inventory Monitor')
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
					<form action="{{url('mrp/edit')}}" method="get" enctype="multipart/form-data" >
					<div class="col-md-3 ">
                        <div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
                            <span class="input-group-addon">起始日期</span>
                            <input  class="form-control" value="{{$date_from}}" data-options="format:'yyyy-mm-dd'" id="date_from" name="date_from"
                                   autocomplete="off"/>
                        </div>
                        <br>
                    </div>
					<div class="col-md-3">
                        <div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
                            <span class="input-group-addon">结束日期</span>
                            <input  class="form-control" value="{{$date_to}}" data-options="format:'yyyy-mm-dd'" id="date_to" name="date_to"
                                   autocomplete="off"/>
                        </div>
                        <br>
                    </div>
					<div class="col-md-2">
                       
                        <div class="input-group">
                            <span class="input-group-addon">站点</span>
                            <select class="form-control"  id="marketplace_id" name="marketplace_id">
                                <option value="">Select</option>
                                @foreach(getSiteCode() as $key=>$val)
                                    <option value="{!! $val !!}" {{($val == $marketplace_id)?'selected':''}}>{!! $key !!}</option>
                                @endforeach
                            </select>
                        </div>
						 <br>
                    </div>
					<div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Asin/Sku</span>
                            <input  class="form-control" value="{{$keyword}}" id="keyword" name="keyword"
                                   autocomplete="off"/>
                        </div>
                        <br>
                    </div>
					<div class="col-md-2">
                        <div class="input-group">
							<div class="btn-group pull-right" style="margin-right:20px;">
								<button id="search" type="submit" class="btn sbold blue">Search</button>
							</div>
						</div>
                        <br>
						
                    </div>  
					</form>
					<table class="table table-striped table-bordered table-hover tbl1">
					<thead>
					  <tr class="head" >
					  	<td width="10%">帖子</td>
						<td width="5%">帖子状态</td>
						<td width="5%">库存维持天数</td>
						<td width="5%">加权日均</td>
						<td width="5%">销售计划</td>
						<td width="5%">FBA在库</td>
						<td width="5%">FBA在途</td>
						<td width="5%">FBM库存</td>
						<td width="5%">深仓</td>
						<td width="5%">在制</td>
						<td width="5%">缺货天数</td>
						<td width="7%">缺货日</td>
						<td width="5%">滞销天数</td>
						<td width="7%">滞销日</td>
						<td width="5%">库存质量得分</td>
						<td width="5%">预计配货</td>
						<td width="11%">下次补货请求</td>
					  </tr>
					 </thead>
					  <tbody>
					  <?php
					  $t_daily_sales = $t_afn_stock = $t_estimated_afn = $t_mfn_stock = $t_sz_stock = $t_estimated_purchase = $t_out_stock_count = $t_out_stock_date = $t_over_stock_count = $t_over_stock_date = $t_score = $t_dist=  0;
					  $t_afn_stock = 0;
					  ?>
					  @foreach ($asins as $v)
					  <tr class="asins_details">
						<td style="text-align:left">{!!(($v->asin==$asin)?'<span class="badge badge-danger">'.$v->asin.'</span>':'<a href="/mrp/edit?asin='.$v->asin.'&marketplace_id='.$v->marketplace_id.'">'.$v->asin.'</a>')!!}
						
						<a class="pull-right" href="https://{{array_get(getSiteUrl(),$v->marketplace_id)}}/dp/{{$v->asin}}" target="_blank"><i class="fa fa-amazon"></i></a></td>
						<td>{{((intval($v->buybox_sellerid)>=0)?'OnLine':'OffLine')}}</td>
						<td>{{$v->afn_out_stock_date}}</td>
						<td>{{round($v->daily_sales,2)}}</td>
						<td  id="{{$v->asin}}">{{intval($v->quantity)}}</td>
						<td>{{intval($v->afn_sellable+$v->afn_reserved)}}</td>
						<td>{{intval($v->sum_estimated_afn)}}</td>
						<td>{{intval($v->mfn_sellable)}}</td>
						<td>{{intval($v->sz_sellable)}}</td>
						<td>{{intval($v->sum_estimated_purchase)}}</td>
						<td>{{intval($v->out_stock_count)}}</td>
						<td>{{$v->out_stock_date}}</td>
						<td>{{intval($v->over_stock_count)}}</td>
						<td>{{$v->over_stock_date}}</td>
						<td>{{intval($v->out_stock_count)+intval($v->over_stock_count)*3+intval($v->unsafe_count)*4}}</td>
						<td>{{(intval($v->afn_sellable+$v->afn_reserved+$v->mfn_sellable+$v->sum_estimated_afn-$v->sum_quantity_miss)<0?abs(intval($v->afn_sellable+$v->afn_reserved+$v->mfn_sellable+$v->sum_estimated_afn-$v->sum_quantity_miss)):0)}}</td>
						<td>0</td>
						</td>
					  </tr>
					  <?php 
					  	$t_daily_sales+=round($v->daily_sales,2);
						$t_afn_stock+=intval($v->afn_sellable+$v->afn_reserved);
						$t_estimated_afn+=intval($v->sum_estimated_afn);
						$t_mfn_stock=intval($v->mfn_sellable);
						$t_sz_stock=intval($v->sz_sellable);
						$t_estimated_purchase+=intval($v->sum_estimated_purchase);
						$t_out_stock_count+=intval($v->out_stock_count);
						$t_out_stock_date=($t_out_stock_date==0 || $t_out_stock_date>$v->out_stock_date)?$v->out_stock_date:$t_out_stock_date;
						$t_over_stock_count+=intval($v->over_stock_count);
						$t_over_stock_date=($t_over_stock_date==0 || $t_over_stock_date>$v->over_stock_date)?$v->over_stock_date:$t_over_stock_date;
						$t_score+=intval($v->out_stock_count)+intval($v->over_stock_count)*3+intval($v->unsafe_count)*4;
						$t_dist+=(intval($v->afn_sellable+$v->afn_reserved+$v->mfn_sellable+$v->sum_estimated_afn-$v->sum_quantity_miss)<0?abs(intval($v->afn_sellable+$v->afn_reserved+$v->mfn_sellable+$v->sum_estimated_afn-$v->sum_quantity_miss)):0);
					  ?>
					  @endforeach
					  <tr id="asins_total">
						<td colspan="2"> : </td>
						<td>{{(($t_daily_sales==0)?'∞':date('Y-m-d',strtotime('+'.intval($t_afn_stock/$t_daily_sales).'days')))}}</td>
						<td>{{$t_daily_sales}}</td>
						<td>{{$t_sz_stock}}</td>
						<td>{{$t_afn_stock}}</td>
						<td>{{$t_estimated_afn}}</td>
						<td>{{$t_mfn_stock}}</td>
						<td>{{$t_sz_stock}}</td>
						<td>{{$t_estimated_purchase}}</td>
						<td>{{$t_out_stock_count}}</td>
						<td>{{$t_out_stock_date}}</td>
						<td>{{$t_over_stock_count}}</td>
						<td>{{$t_over_stock_date}}</td>
						<td>{{$t_score}}</td>
						<td>{{$t_dist}}</td>
						<td>0</td>
						</td>
					  </tr>
					  </tbody>
					</table>
					
					
					<table class="table table-striped table-bordered table-hover tbl1">
					<thead>
					  <tr class="head" >
					  	<td width="10%">Sku</td>
						<td width="10%">状态</td>
						<td width="8%">等级</td>
						<td width="8%">成本</td>
						<td width="8%">采购周期</td>
						<td width="8%">头程时效</td>
						<td width="8%">MOQ</td>
						<td width="8%">安全库存</td>
						<td width="8%">销售员</td>
						<td width="8%">BG</td>
						<td width="8%">BU</td>
						<td width="8%">计划员</td>
					  </tr>
					 </thead>
					 <tbody>
					  <tr>
						<td>{{$sku_info['sku']}}</td>
						<td>{{array_get(getSkuStatuses(),$sku_info['status'],$sku_info['status'])}}</td>
						<td>{{$sku_info['level']}}</td>
						<td>{{round($sku_info['cost'],2)}}</td>
						<td>{{intval($sku_info['estimated_cycle'])}}</td>
						<td>{{intval($sku_info['international_transport_time'])}}</td>
						<td>{{intval($sku_info['min_purchase_quantity'])}}</td>
						<td>{{$sku_info['safe_quantity']}}</td>
						<td>{{$sku_info['sap_seller_name']}}</td>
						<td>{{$sku_info['sap_seller_bg']}}</td>
						<td>{{$sku_info['sap_seller_bu']}}</td>
						<td>{{$sku_info['planer']}}</td>

						</td>
					  </tr>
					  </tbody>
					</table>
					<div class="row" >
						<div class="col-md-2">
                       
							<div class="input-group">
								<span class="input-group-addon">显示周期</span>
								<select class="form-control"  id="show" name="show">
									<option value="">日</option>
									<option value="week" <?php if($show=='week') echo 'selected';?>>周</option>
									<option value="month" <?php if($show=='month') echo 'selected';?>>月</option>
								</select>
							</div>
							 <br>
						</div>
						
						<div class="col-md-2">
                       
							<div class="input-group">
								<span class="input-group-addon">显示维度</span>
								<select class="form-control"  id="type" name="type">
									<option value="">Asin维度</option>
									<option value="sku" <?php if($type=='sku') echo 'selected';?>>Sku维度</option>
								</select>
							</div>
							 <br>
						</div>
						
						
					</div>
					
					<div class="table-head">
					<table class="table table-striped table-bordered table-hover" style="margin-bottom:0px;">
					<colgroup>
					<col width="8%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="22%"></col>
					</colgroup>
					  <thead>
					  <tr class="head" >
						<td>日期</td>
						<td>销售预测</td>
						<td>初始销售计划</td>
						<td>销售计划</td>
						<td>实际销售</td>
						<td>FBA在库</td>
						<td>预计交深仓</td>
						<td>实际交深仓</td>
						<td>预计FBA上架</td>
						<td>实际FBA上架</td>
						<td>MRP</td>
						<td>备注</td>
					  </tr>
					  </thead>
					 </table>
					</div>
					
					<div class="table-body">
					<table class="table table-striped table-bordered table-hover" style="margin-bottom:0px;">
					<colgroup>
					<col width="8%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="7%"></col>
					<col width="22%"></col>
					</colgroup>

					  <tbody>
					  
					  <?php
					  foreach($sales_plan as $k=>$v){
					  ?>
					  		<tr class="{{ ($k>=$cur_date)?'asin_sales_line_plan':''}}">
								<td>{{$k}}</td>
								<td>{{$v['symmetry']}}</td>
								<td id="{{$k}}--quantity_first">{{$v['plan_first']}}</td>
								
								<td>
								<?php
								if($show=='day' && $type=='asin' && $k>=$cur_date && in_array(intval(array_get($v,'status',0))+1,$dist_status)){
								?>
								<a class="plan editable" title="{{$asin.' '.$k.' Plan'}}" href="javascript:;" id="{{$k}}--quantity_last" data-pk="{{$k}}--quantity_last" data-type="text"> {{$v['plan_last']}} </a>
								<?php
								}else{
								?>
								{{$v['plan_last']}}
								<?php
								}
								?>
								</td>
								<td>
								<a href="/skus?date_start={{$date_from}}&date_end={{$date_to}}&site={{$marketplace_id}}&sku={{$asin}}">{{$v['sold']}}</a></td>
								<td>
								<?php
								if($k==$cur_date){
								?>
								{{$current_stock}}
								<?php
								}else{
								?>
								{{$v['stock']}}
								<?php
								}
								?>
								</td>
								<td>{{($v['estimated_purchase']>0)?$v['estimated_purchase']:''}}</td>
								<td>{{($v['actual_purchase']>0)?$v['actual_purchase']:''}}</td>
								<td>{{($v['estimated_afn']>0)?$v['estimated_afn']:''}}</td>
								<td>{{($v['actual_afn']>0)?$v['actual_afn']:''}}</td>
								<td>{{($v['mrp']>0)?$v['mrp']:''}}</td>
								
								<td>
								<?php
								if($show=='day' && $type=='asin'){
								?>
								<a class="remark editable" title="{{$asin.' '.$k.' Remark'}}" href="javascript:;" id="{{$k}}--remark" data-pk="{{$k}}--remark" data-type="text"> {{$v['remark']}} </a>
								<?php
								}else{
								?>
								{{$v['remark']}}
								<?php
								}
								?>
								</td>
						  	</tr>
					  <?php
					  }
					  ?>
					  
					  
					  </tbody>
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
function changeURLArg(url,arg,arg_val){ 
    var pattern=arg+'=([^&]*)'; 
    var replaceText=arg+'='+arg_val; 
    if(url.match(pattern)){ 
        var tmp='/('+ arg+'=)([^&]*)/gi'; 
        tmp=url.replace(eval(tmp),replaceText); 
        return tmp; 
    }else{ 
        if(url.match('[\?]')){ 
            return url+'&'+replaceText; 
        }else{ 
            return url+'?'+replaceText; 
        } 
    } 
    return url+'\n'+arg+'\n'+arg_val; 
}

var FormEditable = function() {
	
    $.mockjaxSettings.responseTime = 500;

    $.fn.editable.defaults.inputclass = 'form-control';
    $.fn.editable.defaults.url = '/mrp/update';
	
	
	var params= new Array(5);
	params['asin'] = "<?php echo $asin?>";
	params['marketplace_id'] = "<?php echo $marketplace_id?>";
	params['sku'] = "<?php echo $sku?>";
	params['date_from'] = "<?php echo $date_from?>";
	params['date_to'] = "<?php echo $date_to?>";

    var initBasetables = function() {
		
		$('.remark').editable({
			emptytext:'N/A',
			params: params
		});
		
		$('.plan').editable({
			emptytext:'0',
			validate: function (value) {
				if (isNaN(value)) {
					return 'Must be a number';
				}
			},
			params: params,
			success: function (response) { 
				var obj = JSON.parse(response);
				for(var jitem in obj){
					$('#'+jitem).text(obj[jitem]);
				}
				flushTable();
			}, 
			error: function (response) { 
				return 'remote error'; 
			} 
		});
		flushTable();
	};
	
	
	var flushTable = function(){
		var t_plan = 0;
		$(".asins_details").each(function(){
			t_plan += parseInt($(this).find("td").eq(4).text()==''?0:$(this).find("td").eq(4).text());
		});	
		$("#asins_total").find("td").eq(3).text(t_plan);
		var d_plan = 0; var d_stock = <?php echo $current_stock;?>;
		$(".asin_sales_line_plan").each(function(){
			d_plan += (parseInt($(this).find("td").eq(3).text()==''?0:$(this).find("td").eq(3).text()) - parseInt($(this).find("td").eq(8).text()==''?0:$(this).find("td").eq(8).text()));
			if(d_stock-d_plan<0){
				$(this).find("td").eq(5).html("<span class='badge badge-danger'>OutStock</span>");
			}else{
				$(this).find("td").eq(5).text(d_stock-d_plan);
			}	
		});	
	};

						
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
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		autoclose: true
    });
    FormEditable.init();
	$("#show, #type").change(function(){  
		location.href = changeURLArg(location.href,this.id,$(this).val());
    }); 
});
</script>


@endsection
