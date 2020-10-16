@extends('layouts.layout')
@section('label', 'Sales Forecast-22W')
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
.table-body{width:100%; max-height:700px;overflow-y:scroll;}
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
					<div class="col-md-2 ">
                        <div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
                            <span class="input-group-addon">起始日期</span>
                            <input  class="form-control" value="{{$date_from}}" data-options="format:'yyyy-mm-dd'" id="date_from" name="date_from"
                                   autocomplete="off"/>
                        </div>
                        <br>
                    </div>
					<div class="col-md-2">
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
								<span class="input-group-addon">显示维度</span>
								<select class="form-control"  id="type" name="type">
									<option value="">Asin维度</option>
									<option value="sku" <?php if($type=='sku') echo 'selected';?>>Sku维度</option>
								</select>
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
					<div class="row" >

					</div>

					<div class="table-head">
					<table class="table table-striped table-bordered table-hover" style="margin-bottom:0px;">
					  <thead>
					  <tr class="head" >
						<td>日期</td>
						<td>销售预测</td>
						<td>销售计划</td>
						<td>实际销售</td>
					  	<td>完成率</td>
						<td>FBA在库</td>
					    <td>FBA在途</td>
					    <td>预计到达时间</td>
						<td>实际FBA上架</td>
						<td>备注</td>
					  </tr>
					  </thead>
					 </table>
					</div>
					
					<div class="table-body">
					<table class="table table-striped table-bordered table-hover" style="margin-bottom:0px;">
					  <tbody>
					  @foreach($sales_plan as $k=>$v)
					  		<tr class="{{ ($k>=$cur_date)?'asin_sales_line_plan':''}}">
								<td>{{$k}}周<br>{{$v['week_date']}}</td>
								<td>{{$v['symmetry']}}</td>
								<td>{{$v['plan_last']}}</td>
								<td>{{$v['sold']}}</td>
								<td>{{$v['finishing_rate']}}</td>
								<td>
								@if($k==$cur_date){{$current_stock}}@else{{$v['stock']}}@endif
								</td>
								<td>{{$v['estimated_afn']}}</td>
								<td>{{$v['adjustreceived_date']}}</td>
								<td>{{($v['actual_afn']>0)?$v['actual_afn']:''}}</td>
								<td>{!! $v['remark'] !!}</td>
						  	</tr>
						  @endforeach
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

var FormEditable = function() {
	
    $.mockjaxSettings.responseTime = 500;

    $.fn.editable.defaults.inputclass = 'form-control';
    $.fn.editable.defaults.url = '/mrp/update';
	
	
	var params= new Array(6);
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
});
</script>


@endsection
