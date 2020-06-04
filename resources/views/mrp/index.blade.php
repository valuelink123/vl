@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Inventory Monitor']])
@endsection
@section('content')
<style>
.table thead tr th,.table thead tr td,.table td, .table th{
	font-size:11px;
	white-space: nowrap;
	text-align:left;
}
table.dataTable thead th, table.dataTable thead td {
    padding: 8px 10px;
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
                            <span class="input-group-addon">天数</span>
                            <input  class="form-control" value="{!! $date !!}" id="date" name="date"
                                   autocomplete="off"/>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">Site</span>
                            <select class="form-control"  id="site" name="site">
                                <option value="">Select</option>
                                @foreach(getSiteCode() as $key=>$val)
                                    <option value="{!! $val !!}">{!! $key !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">库存维持天数</span>
							<div>
								<div class="col-md-6" style="padding:0px;">
									<input class="form-control" value="" id="stockkeep_from" name="stockkeep_from" autocomplete="off"/>
								</div>
								<div class="col-md-6" style="padding:0px;">
									<input class="form-control" value="" id="stockkeep_to" name="stockkeep_to" autocomplete="off"/>
								</div>
							</div>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">BG</span>
                            <select class="form-control" id="bg" name="bg">
                                <option value="">Select</option>
                                @foreach($bgs as $bg)
                                    <option value="{!! $bg !!}">{!! $bg !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">缺货天数</span>
                            <div>
								<div class="col-md-6" style="padding:0px;">
									<input class="form-control" value="" id="outstock_from" name="outstock_from" autocomplete="off"/>
								</div>
								<div class="col-md-6" style="padding:0px;">
									<input class="form-control" value="" id="outstock_to" name="outstock_to" autocomplete="off"/>
								</div>
							</div>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">BU</span>
                            <select  class="form-control"  id="bu" name="bu">
                                <option value="">Select</option>
                                @foreach($bus as $bu)
                                    <option value="{!! $bu !!}">{!! $bu !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">库存状态</span>
                            <select  class="form-control"  id="stock_status" name="stock_status">
                                <option value="">Select</option>
                                @foreach(getStockStatus() as $key=>$val)
                                    <option value="{!! $key !!}">{!! $val !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <div class="input-group">
                            <span class="input-group-addon">销售员</span>
                            <select  class="form-control"  id="sap_seller_id" name="sap_seller_id">
                                <option value="">Select</option>
                                @foreach(getUsers('sap_seller') as $key=>$val)
                                    <option value="{!! $key !!}">{!! $val !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Sku状态</span>
                            <select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" id="sku_status" name="sku_status">
                                @foreach(getSkuStatuses() as $key=>$val)
                                    <option value="{!! $key !!}">{!! $val !!}</option>
                                @endforeach
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
                            <span class="input-group-addon">Sku等级</span>
                            <select class="form-control"  id="sku_level" name="sku_level">
                                <option value="">Select</option>
                                @foreach(getSkuLevel() as $key=>$val)
                                    <option value="{!! $val !!}">{!! $val !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
						<div class="input-group">
							<div class="btn-group pull-right">
							<button id="export" class="btn sbold blue"> 导出
								<i class="fa fa-download"></i>
							</button>
							</div>
							<div class="btn-group pull-right" style="margin-right:20px;">
								<button id="search" class="btn sbold blue">查询</button>
							</div>
						</div>
                    </div>  
                </div>
            </div>

            </form>

            </div>
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
                        <th>Asin</th>
                        <th>站点</th>
                        <th>Sku</th>
                        <th>状态</th>
                        <th>销售员</th>
                        <th>加权日均</th>
						<th>销售计划</th>
                        <th>FBA库存</th>
                        <th>库存维持</th>
                        <th>FBA在途</th>
                        <th>FBM库存</th>
                        <th>海外库存维持</th>
                        <th>深仓</th>
                        <th>在制</th>
                        <th>缺货天数</th>
                        <th>缺货日</th>
                        <th>滞销天数</th>
                        <th>滞销日</th>
                        <th>库存质量得分</th>
                        <th>预计配货</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>
    <script>

        let $theTable = $(thetable)

        var initTable = function () {
            $theTable.dataTable({
                searching: false,
				
                serverSide: true,
                "lengthMenu": [
                    [20, 50, 100, -1],
                    [20, 50, 100, 'All']
                ],
                "pageLength": 20,
                pagingType: 'bootstrap_extended',
                processing: true,
                ordering:  false,
                //aoColumnDefs: [ { "bSortable": false, "aTargets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,21] }],
                columns: [
                    {data: 'asin', name: 'asin'},
                    {data: 'site', name: 'site'},
                    {data: 'sku', name: 'sku'},
                    {data: 'status', name: 'status'},
                    {data: 'seller', name: 'seller'},
                    {data: 'daily_sales', name: 'daily_sales'},
					{data: 'quantity', name: 'quantity'},
                    {data:'fba_stock',name:'fba_stock'},
                    {data:'fba_stock_keep',name:'fba_stock_keep'},
                    {data: 'fba_transfer', name: 'fba_transfer'},
                    {data:'fbm_stock',name:'fbm_stock'},
                    {data:'stock_keep',name:'stock_keep'},
                    {data:'sz',name:'sz'},
                    {data:'in_make',name:'in_make'},
                    {data:'out_stock',name:'out_stock'},
                    {data:'out_stock_date',name:'out_stock_date'},
                    {data:'unsalable',name:'unsalable'},
                    {data:'unsalable_date',name:'unsalable_date'},
                    {data:'stock_score',name:'stock_score'},
                    {data:'expected_distribution',name:'expected_distribution'},
                    {data:'action',name:'action'},
                ],
                ajax: {
                    type: 'POST',
                    url: location.href,
                    data:  {search: $("#search-form").serialize()}
                }
            })
        }


        initTable();
        let dtApi = $theTable.api();


        //点击提交按钮重新绘制表格，并将输入框中的值赋予检索框
        $('#search').click(function () {
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtApi.ajax.reload();
            return false;
        });

        //下载数据
        $("#export").click(function(){
            location.href='/mrp/asinexport?'+$("#search-form").serialize();
            return false;

        });


    </script>

@endsection