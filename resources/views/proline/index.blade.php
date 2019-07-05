@extends('layouts.layout')
@section('label', 'Product Line Report')
@section('content')
<style>
        .form-control {
            height: 29px;
        }
		.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}


th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
    </style>
    <h1 class="page-title font-red-intense"> Product Line Report
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
							
							
					<div class="table-toolbar">
                    <form role="form" action="{{url('proline')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						
						<div class="col-md-2">
                            <select name="bgbu" class="form-control form-filter input-sm">
										<option value="">All BG && BU</option>
										<option value="-">[Empty]</option>
										<?php 
										$bg='';
										foreach($teams as $team){ 	
											$bg=$team->bg;
											if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'">'.$team->bg.' - '.$team->bu.'</option>';
										}?>
									</select>
                        </div>
                        <div class="col-md-2">
                            <select name="sap_seller_id" class="form-control form-filter input-sm">
										<option value="">All Sellers</option>
										@foreach ($users as $sap_seller_id=>$user_name)
											<option value="{{$sap_seller_id}}">{{$user_name}}</option>
										@endforeach
									</select>
                        </div>
                        <div class="col-md-2">
                            
                                <select name="sap_site_id" class="form-control form-filter input-sm">
										<option value="">All Site</option>
										@foreach (matchSapSiteCode() as $k=>$v)
											<option value="{{$v}}">{{$k}}</option>
										@endforeach
									</select>

                           
                        </div>
                        <div class="col-md-2">
                            
                                <input type="text" class="form-control form-filter input-sm" name="sku" placeholder='SKU'>

                        </div>

						
						
						<div class="col-md-2">
						<button type="button" class="btn blue" id="data_search">Search</button>
                                       
						</div>	
						</div>

                    </form>
					
                </div>
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover" id="datatable_ajax_sp">
                            <thead>
                            <tr role="row" >
								<td style="background:#c6e7ff">SKU</td>
								<td colspan="2" style="background:#e2efda">负责团队</td>
								<td colspan="6" style="background:#b4c6e7;text-align:left">产品分级</td>
								<td colspan="2" style="background:#e2efda">品牌</td>
								<td colspan="7" style="background:#b4c6e7;text-align:left">规则参数</td>
								<td colspan="3" style="background:#e2efda">供应商</td>
								<td colspan="7" style="background:#b4c6e7;text-align:left">产品状态</td>
								<td colspan="2" style="background:#e2efda">页面排名</td>
								<td colspan="2" style="background:#b4c6e7">评论星级</td>
								<td colspan="17" style="background:#e2efda;text-align:left">成本 & 利润</td>
								<td colspan="8" style="background:#b4c6e7;text-align:left">库存</td>
								<td colspan="3" style="background:#e2efda;">销售端</td>
								<td colspan="6" style="background:#b4c6e7;text-align:left">前一个月销售情况</td>
								<td colspan="6" style="background:#e2efda;text-align:left">前二个月销售情况</td>
								<td colspan="6" style="background:#b4c6e7;text-align:left">前三个月销售情况</td>
								<td  style="background:#e2efda">Strategy</td>
							</tr>
							 <tr role="row" class="heading">
							 	<th  style="background-color:#c6e7ff;width:50px;" >SKU</th>
								<th  style="background-color:#e2efda;width:60px;">产品经理</th>
								<th  style="background-color:#e2efda;width:60px;">产品负责人</th>
								<th  style="background-color:#b4c6e7;width:60px;" >一级分类</th>
								<th  style="background-color:#b4c6e7;width:40px;">大类</th>
								<th  style="background-color:#b4c6e7;width:40px;">品线</th>
								<th  style="background-color:#b4c6e7;width:60px;">物料组描述</th>
								<th  style="background-color:#b4c6e7;width:50px;">物料组</th>
								<th  style="background-color:#b4c6e7;width:50px;">品线等级</th>
								<th  style="background-color:#e2efda;width:60px;">在使用品牌</th>
								<th  style="background-color:#e2efda;width:60px;">待使用品牌</th>
								<th  style="background-color:#b4c6e7;width:40px;">型号</th>
								<th  style="background-color:#b4c6e7;width:40px;">规格</th>
								<th  style="background-color:#b4c6e7;width:50px;">是否配件</th>
								<th  style="background-color:#b4c6e7;width:40px;">长</th>
								<th  style="background-color:#b4c6e7;width:40px;">宽</th>
								<th  style="background-color:#b4c6e7;width:40px;">高</th>
								<th  style="background-color:#b4c6e7;width:50px;">尺寸类型</th>
								<th  style="background-color:#e2efda;width:100px;">名称</th>
								<th  style="background-color:#e2efda;width:60px;">代码</th>
								<th  style="background-color:#e2efda;width:50px;">是否合作</th>
								<th  style="background-color:#b4c6e7;width:40px;">状态</th>
								<th  style="background-color:#b4c6e7;width:40px;">等级</th>
								<th  style="background-color:#b4c6e7;width:80px;">备注</th>
								<th  style="background-color:#b4c6e7;width:60px;">Asin</th>
								<th  style="background-color:#b4c6e7;width:60px;">站点</th>
								<th  style="background-color:#b4c6e7;width:100px;">物料描述</th>
								<th  style="background-color:#b4c6e7;width:100px;">核心关键字</th>
								<th  style="background-color:#e2efda;width:40px;">页面</th>
								<th  style="background-color:#e2efda;width:40px;">排名</th>
								<th  style="background-color:#b4c6e7;width:40px;">数量</th>
								<th  style="background-color:#b4c6e7;width:40px;">星级</th>
								<th  style="background-color:#e2efda;width:40px;">日均</th>
								<th  style="background-color:#e2efda;width:40px;">汇率</th>					
								<th  style="background-color:#e2efda;width:50px;">现售价</th>
								<th  style="background-color:#e2efda;width:50px;">佣金比例</th>
								<th  style="background-color:#e2efda;width:50px;">平台佣金</th>
								<th  style="background-color:#e2efda;width:50px;">不含税成本</th>
								<th  style="background-color:#e2efda;width:50px;">税率</th>
								<th  style="background-color:#e2efda;width:50px;">含税成本</th>
								<th  style="background-color:#e2efda;width:50px;">淡季仓储</th>
								<th  style="background-color:#e2efda;width:50px;">旺季仓储</th>
								<th  style="background-color:#e2efda;width:50px;">捡配费 </th>
								<th  style="background-color:#e2efda;width:60px;">实际外仓成本</th>
								<th  style="background-color:#e2efda;width:60px;">总成本</th>
								<th  style="background-color:#e2efda;width:60px;">SAP业务利润</th>
								<th  style="background-color:#e2efda;width:60px;">SAP业务利润率</th>
								<th  style="background-color:#e2efda;width:50px;">异常率</th>
								<th  style="background-color:#e2efda;width:60px;">实际SAP利润率</th>
								<th  style="background-color:#b4c6e7;width:50px;">FBA</th>
								<th  style="background-color:#b4c6e7;width:50px;">FBM</th>
								<th  style="background-color:#b4c6e7;width:50px;">在途</th>
								<th  style="background-color:#b4c6e7;width:50px;">深仓</th>
								<th  style="background-color:#b4c6e7;width:50px;">未交</th>
								<th  style="background-color:#b4c6e7;width:50px;">总库存</th>
								<th  style="background-color:#b4c6e7;width:60px;">库存维持天数</th>
								<th  style="background-color:#b4c6e7;width:60px;">库存总金额</th>
								<th  style="background-color:#e2efda;width:40px;">BG</th>
								<th  style="background-color:#e2efda;width:40px;">BU</th>
								<th  style="background-color:#e2efda;width:50px;">销售员</th>
								<th  style="background-color:#b4c6e7;width:50px;">售价</th>
								<th  style="background-color:#b4c6e7;width:50px;">业务利润率</th>
								<th  style="background-color:#b4c6e7;width:50px;">营销费用率
								<th  style="background-color:#b4c6e7;width:60px;">业务净利润率</th>
								<th  style="background-color:#b4c6e7;width:50px;">销量</th>
								<th  style="background-color:#b4c6e7;width:50px;">销售额</th>
								<th  style="background-color:#e2efda;width:50px;">售价</th>
								<th  style="background-color:#e2efda;width:50px;">业务利润率</th>
								<th  style="background-color:#e2efda;width:50px;">营销费用率</th>
								<th  style="background-color:#e2efda;width:60px;">业务净利润率</th>
								<th  style="background-color:#e2efda;width:50px;">销量</th>
								<th  style="background-color:#e2efda;width:50px;">销售额</th>
								<th  style="background-color:#b4c6e7;width:50px;">售价</th>
								<th  style="background-color:#b4c6e7;width:50px;">业务利润率</th>
								<th  style="background-color:#b4c6e7;width:50px;">营销费用率</th>
								<th  style="background-color:#b4c6e7;width:60px;">业务净利润率 </th>
								<th  style="background-color:#b4c6e7;width:50px;">销量</th>
								<th  style="background-color:#b4c6e7;width:50px;">销售额</th>
								<th  style="background-color:#e2efda;width:100px;">营销策略</th>
								
                            </tr>
                            
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




<script>
    var TableDatatablesAjax = function () {
		var initPickers = function () {
            //init date pickers
            $('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
        }
        var initTable = function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
            });
            var grid = new Datatable();

            grid.init({
                src: $("#datatable_ajax_sp"),
                onSuccess: function (grid, response) {
                    // grid:        grid object
                    // response:    json object of server side ajax response
                    // execute some code after table records loaded
                },
                onError: function (grid) {
                    // execute some code on network or other general error
                },
                onDataLoad: function(grid) {
                    // execute some code on ajax data load
                    //alert('123');
                    //alert($("#subject").val());
                    //grid.setAjaxParam("subject", $("#subject").val());

                },
                loadingMessage: 'Loading...',
                dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

                    // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                    // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js).
                    // So when dropdowns used the scrollable div should be removed.
                    //"dom": "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>>",
					"autoWidth":true,
					"ordering": false,
                    "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                   // "aoColumnDefs": [ { "bSortable": false, "aTargets": [4,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30] }],
                    "lengthMenu": [
                        [10, 50, 100, -1],
                        [10, 50, 100, 'All'] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page
                    "ajax": {
                        "url": "{{ url('proline/get')}}", // ajax source
                    },
                    "order": [
                        [1, "desc"]
                    ],// set first column as a default sort by asc
					<?php if(Auth::user()->can(['proline-export'])){ ?>
					buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'proline' }
                    ],
					<?php }else{?>
											
					buttons: [],
					
					<?php } ?>
					
					scrollY:        450,
                    scrollX:        true,
					

					fixedColumns:   {
						leftColumns:1,
						rightColumns: 0
					},
					"dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
                }
            });
            grid.setAjaxParam("sku", $("input[name='sku']").val());
            grid.setAjaxParam("bgbu", $("select[name='bgbu']").val());
			grid.setAjaxParam("sap_seller_id", $("select[name='sap_seller_id']").val());
			grid.setAjaxParam("sap_site_id", $("select[name='sap_site_id']").val());
            grid.getDataTable().ajax.reload(null,false);
            //grid.clearAjaxParams();
        }


        return {

            //main function to initiate the module
            init: function () {
				initPickers();
                initTable();
            }

        };

    }();

$(function() {
    TableDatatablesAjax.init();
	$('#data_search').on('click',function(){
		var dttable = $('#datatable_ajax_sp').dataTable();
	    dttable.fnClearTable(); //清空一下table
	    dttable.fnDestroy(); //还原初始化了的datatable
		TableDatatablesAjax.init();
	});
});


</script>


@endsection
