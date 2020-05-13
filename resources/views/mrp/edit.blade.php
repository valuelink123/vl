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
                            <span class="input-group-addon">Date</span>
                            <input  class="form-control" value="{{$date_from}}" data-options="format:'yyyy-mm-dd'" id="date_from" name="date_from"
                                   autocomplete="off"/>
                        </div>
                        <br>
                    </div>
					<div class="col-md-3">
                        <div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
                            <span class="input-group-addon">Date</span>
                            <input  class="form-control" value="{{$date_to}}" data-options="format:'yyyy-mm-dd'" id="date_to" name="date_to"
                                   autocomplete="off"/>
                        </div>
                        <br>
                    </div>
					<div class="col-md-2">
                       
                        <div class="input-group">
                            <span class="input-group-addon">Site</span>
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
					  	<td width="10%">Asin</td>
						<td width="5%">Status</td>
						<td width="5%">StockKeep</td>
						<td width="5%">D/Sales</td>
						<td width="5%">Plan</td>
						<td width="5%">FBAStock</td>
						<td width="5%">FBATran</td>
						<td width="5%">FBM</td>
						<td width="5%">SZ</td>
						<td width="5%">InMake</td>
						<td width="5%">OutStock</td>
						<td width="7%">OutStockDate</td>
						<td width="5%">OverStock</td>
						<td width="7%">OverStockDate</td>
						<td width="5%">StockScore</td>
						<td width="5%">Dist</td>
						<td width="11%">Next Request</td>
					  </tr>
					 </thead>
					  <tbody>
					  @foreach ($asins as $v)
					  <tr class="asins_details">
						<td style="text-align:left">{!!(($v->asin==$asin)?'<span class="badge badge-danger">'.$v->asin.'</span>':'<a href="/mrp/edit?asin='.$v->asin.'&marketplace_id='.$v->marketplace_id.'">'.$v->asin.'</a>')!!}
						
						<a class="pull-right" href="https://{{array_get(getSiteUrl(),$v->marketplace_id)}}/dp/{{$v->asin}}" target="_blank"><i class="fa fa-amazon"></i></a></td>
						<td>{{((intval($v->buybox_sellerid)>=0)?'OnLine':'OffLine')}}</td>
						<td>0</td>
						<td>{{round($v->daily_sales,2)}}</td>
						<td  id="{{$v->asin}}">{{intval($v->quantity)}}</td>
						<td>{{intval($v->afn_sellable+$v->afn_reserved)}}</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						</td>
					  </tr>
					  @endforeach
					  <tr id="asins_total">
						<td colspan="2"> Total: </td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						</td>
					  </tr>
					  </tbody>
					</table>
					
					
					<table class="table table-striped table-bordered table-hover tbl1">
					<thead>
					  <tr class="head" >
					  	<td width="10%">Sku</td>
						<td width="10%">Status</td>
						<td width="8%">Level</td>
						<td width="8%">Cost</td>
						<td width="8%">PurchasingCycle</td>
						<td width="8%">HeadAging</td>
						<td width="8%">MOQ</td>
						<td width="8%">SafeStockDay</td>
						<td width="8%">Seller</td>
						<td width="8%">BG</td>
						<td width="8%">BU</td>
						<td width="8%">Planer</td>
					  </tr>
					 </thead>
					 <tbody>
					  <tr>
						<td>{{$sku_info->sku}}</td>
						<td>{{array_get(getSkuStatuses(),$sku_info->status,$sku_info->status)}}</td>
						<td>{{$sku_info->level}}</td>
						<td>{{round($sku_info->cost,2)}}</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>{{$sku_info->sap_seller_name}}</td>
						<td>{{$sku_info->sap_seller_bg}}</td>
						<td>{{$sku_info->sap_seller_bu}}</td>
						<td>{{$sku_info->planer}}</td>

						</td>
					  </tr>
					  </tbody>
					</table>
					<div class="row" >
						<div class="col-md-2">
                       
							<div class="input-group">
								<span class="input-group-addon">Cycle</span>
								<select class="form-control"  id="show" name="show">
									<option value="">Day</option>
									<option value="week" <?php if($show=='week') echo 'selected';?>>Week</option>
									<option value="month" <?php if($show=='month') echo 'selected';?>>Month</option>
								</select>
							</div>
							 <br>
						</div>
						
						<div class="col-md-2">
                       
							<div class="input-group">
								<span class="input-group-addon">Type</span>
								<select class="form-control"  id="type" name="type">
									<option value="">Asin</option>
									<option value="sku" <?php if($type=='sku') echo 'selected';?>>Sku</option>
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
						<td>Date</td>
						<td>Estimated Sold</td>
						<td>FirstPlan</td>
						<td>LastPlan</td>
						<td>SoldOut</td>
						<td>FBAStock</td>
						<td>Estimated SZ</td>
						<td>Actual SZ</td>
						<td>Estimated FBA</td>
						<td>Actual FBA</td>
						<td>Mrp</td>
						<td>Remark</td>
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
								if($show=='day' && $type=='asin' && $k>=$cur_date){
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
								<td>{{$v['sold']}}</td>
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
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								
								<td>
								<?php
								if($show=='day' && $type=='asin' && $k>=$cur_date){
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
		var t_sales = 0;
		var t_plan = 0;
		var t_stock = 0;
		$(".asins_details").each(function(){
			t_sales += parseInt($(this).find("td").eq(3).text());
			t_plan += parseInt($(this).find("td").eq(4).text());
			t_stock += parseInt($(this).find("td").eq(5).text());
		});	
		$("#asins_total").find("td").eq(2).text(t_sales);
		$("#asins_total").find("td").eq(3).text(t_plan);
		$("#asins_total").find("td").eq(4).text(t_stock);
		
		var d_plan = 0; var d_stock = <?php echo $current_stock;?>;
		$(".asin_sales_line_plan").each(function(){
			d_plan += parseInt($(this).find("td").eq(3).text());
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
