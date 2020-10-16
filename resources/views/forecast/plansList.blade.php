@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>['Plans Forecast-22W']])
@endsection
@section('content')
	<link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
	<style>
		.table thead tr th,.table thead tr td,.table td, .table th{
			font-size:11px;
			white-space: nowrap;
			text-align:left;
		}
		table.dataTable thead th, table.dataTable thead td {
			padding: 8px 10px;
		}
		input[type=checkbox], input[type=radio]{
			margin:0px;
		}
		.DTFC_Cloned{
			margin-top:1px !important;
		}
	</style>

	<link rel="stylesheet" href="/js/chosen/chosen.min.css"/>
	<script src="/js/chosen/chosen.jquery.min.js"></script>

	<div class="portlet light bordered">
		<div class="portlet-body">
			<form id="search-form">
				<div class="table-toolbar" id="thetabletoolbar">
					<div class="row">
						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">站点</span>
								<select class="form-control"  id="site" name="site">
									<option value="">Select</option>
									@foreach(getSiteCode() as $key=>$val)
										<option value="{!! $val !!}">{!! $key !!}</option>
									@endforeach
								</select>
							</div>
							<br>
							<div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
								<span class="input-group-addon">起始日期</span>
								<input  class="form-control" value="{{$date}}" data-options="format:'yyyy-mm-dd'" id="date" name="date" autocomplete="off"/>
							</div>
							<br>

						</div>
						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">显示维度</span>
								<select class="form-control"  id="type" name="type">
									<option value="">Asin维度</option>
									<option value="sku">Sku维度</option>
								</select>
							</div>
							<br>
							<div class="input-group">
								<span class="input-group-addon">Asin/Sku</span>
								<input class="form-control" value="" id="keyword" name="keyword" autocomplete="off"/>

							</div>
						</div>

						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">SKU状态</span>
								<select  class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true"  id="sku_status" name="sku_status">
									@foreach(getSkuStatuses() as $key=>$val)
										<option value="{!! $key !!}">{!! $val !!}</option>
									@endforeach
								</select>
							</div>
							<br>
							<div class="input-group">
								<span class="input-group-addon">Sku等级</span>
								<select class="form-control"  id="sku_level" name="sku_level">
									<option value="">Select</option>
									@foreach(getSkuLevel() as $key=>$val)
										<option value="{!! $val !!}">{!! $val !!}</option>
									@endforeach
								</select>
							</div>

						</div>
						<div class="col-md-2">
							<br>
							<div class="input-group">
								<div class="btn-group pull-right">
									@permission('plans-forecast-export')
									<button id="export" class="btn sbold blue"> 导出
										<i class="fa fa-download"></i>
									</button>
									@endpermission
								</div>
								<div class="btn-group pull-right" style="margin-right:20px;">
									<button id="search" class="btn sbold blue">查询</button>
								</div>
							</div>
						</div>



					</div>
				</div>

			</form>

			<div class="col-md-12">
				@permission('plans-forecast-add')
				<div class="form-upload">
					<form action="{{url('plansforecast/import')}}" method="post" enctype="multipart/form-data" class="pull-right " style="width:500px;" >
						<div class="col-md-4"  >
							<a href="/mrp/download" >Import Template
							</a>
						</div>

						<div class="pull-left">
							{{ csrf_field() }}
							<input type="file" name="importFile"  />
						</div>
						<div class=" pull-left">
							<button type="submit" class="btn blue btn-sm" id="data_search">上传</button>
						</div>

					</form>
				</div>
				@endpermission
			</div>

		</div>
		<div class="table-container" style="">
			<div style="position: relative;">
				<div class="btn-group" style="position: absolute;left: 150px; z-index: 999;top:30px">
					<button type="button" class="btn btn-sm green-meadow" onclick="updateStatusAjax()">确认</button>
				</div>
			</div>
			<table class="table table-striped table-bordered" id="thetable">
				<thead>
				<tr>
					<th><input type="checkbox" id="selectAll" /></th>
					<th>Asin</th>
					<th>站点</th>
					<th>Sku</th>
					<th>最小起订量</th>
					<th>加权周销量</th>
					<th>可售库存</th>
					<th>22周计划销量</th>
					<th>Status</th>
					@foreach($weekDate as $key=>$val)
						<th class="week_end_date">{!! $val !!}</th>
					@endforeach
				</tr>
				</thead>
				<tbody></tbody>
			</table>

		</div>
	</div>
	</div>
	<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>
	<script src="/assets/pages/scripts/form-editable.min.js" type="text/javascript"></script>
	<script>

        let $theTable = $(thetable)

        var initTable = function () {
            $theTable.dataTable({
                searching: false,
                serverSide: true,
                "autoWidth":true,
                "lengthMenu": [
                    [20, 50, 100, -1],
                    [20, 50, 100, 'All']
                ],
                "pageLength": 20,
                pagingType: 'bootstrap_extended',
                processing: true,
                ordering:  false,
                //aoColumnDefs: [ { "bSortable": false, "aTargets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,21] }],
                order: [],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'asin', name: 'asin'},
                    {data: 'site', name: 'site'},
                    {data: 'sku', name: 'sku'},
                    {data: 'min_purchase', name: 'min_purchase'},
                    {data: 'week_daily_sales', name: 'week_daily_sales'},
                    {data: 'total_sellable', name: 'total_sellable'},
                    {data: '22_week_plan_total', name: '22_week_plan_total'},
                    {data: 'status_name', name: 'status_name'},
                    {data:'0_week_plan',name:'0_week_plan'},
                    {data:'1_week_plan',name:'1_week_plan'},
                    {data:'2_week_plan',name:'2_week_plan'},
                    {data:'3_week_plan',name:'3_week_plan'},
                    {data:'4_week_plan',name:'4_week_plan'},
                    {data:'5_week_plan',name:'5_week_plan'},
                    {data:'6_week_plan',name:'6_week_plan'},
                    {data:'7_week_plan',name:'7_week_plan'},
                    {data:'8_week_plan',name:'8_week_plan'},
                    {data:'9_week_plan',name:'9_week_plan'},
                    {data:'10_week_plan',name:'10_week_plan'},
                    {data:'11_week_plan',name:'11_week_plan'},
                    {data:'12_week_plan',name:'12_week_plan'},
                    {data:'13_week_plan',name:'13_week_plan'},
                    {data:'14_week_plan',name:'14_week_plan'},
                    {data:'15_week_plan',name:'15_week_plan'},
                    {data:'16_week_plan',name:'16_week_plan'},
                    {data:'17_week_plan',name:'17_week_plan'},
                    {data:'18_week_plan',name:'18_week_plan'},
                    {data:'19_week_plan',name:'19_week_plan'},
                    {data:'20_week_plan',name:'20_week_plan'},
                    {data:'21_week_plan',name:'21_week_plan'},
                    {data:'22_week_plan',name:'22_week_plan'}
                ],
                ajax: {
                    type: 'POST',
                    url: location.href,
                    data:  {search: $("#search-form").serialize()},

                },
                scrollY:        false,
                scrollX:        true,
                fixedColumns:   {
                    leftColumns:9,
                    rightColumns: 0,
                    "drawCallback": function(){
                        $(".DTFC_Cloned input[id='selectAll']").on('change',function(e) {
                            $(".DTFC_Cloned input[name='checkedInput']").prop("checked", this.checked);
                        });
                    }

                },
                "fnDrawCallback": function (oSettings) {

                    $.mockjaxSettings.responseTime = 500;
                    $.fn.editable.defaults.inputclass = 'form-control';
                    $.fn.editable.defaults.url = '/plansforecast/weekupdate';
                    $('.week_plan').editable({
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
                            }
                        },
                        error: function (response) {
                            return 'remote error';
                        }
                    });

                }
            })
        }



        initTable();
        let dtApi = $theTable.api();
        jQuery(document).ready(function() {

            $('#search').click(function () {
                dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
                dtApi.ajax.reload();
                //获取周数和日期数据改用ajax获取,避免php数据和js数据不一致问题
                $.ajax({
                    type: "POST",
                    url: "/get22WeekDate",
                    data: {date: $("#date").val()},
                    success: function (data){
                        $(".week_end_date").each(function(index){
                            $(this).html(data[index]);
                        })
                    }
                });
                return false;
            });

//下载数据
            $("#export").click(function(){
                location.href='/plansforecast/export?'+$("#search-form").serialize();
                return false;

            });
            $('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
        });
        function updateStatusAjax(){
            let chk_value = '';
            $(".DTFC_Cloned input[name='checkedInput']:checked").each(function (index,value) {
                if(chk_value != ''){
                    chk_value = chk_value + ',' + $(this).val()
                }else{
                    chk_value = chk_value + $(this).val()
                }
            });
            if(chk_value == ""){
                alert('请先选择需要确认的数据!')
            }else{
                $.ajax({
                    type: "POST",
                    url: "/plansforecast/updateStatus",
                    data: {
                        asinlist: chk_value,
                        date: $("#date").val(),
                    },
                    success: function (res) {
                        toastr.success("确认成功！");
                        dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
                        dtApi.ajax.reload();
                    },
                    error: function(err) {
                        toastr.error("err");
                    }
                });

            }
        }
	</script>

@endsection