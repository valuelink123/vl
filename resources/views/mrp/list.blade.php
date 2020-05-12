@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Asin Monitor']])
@endsection
@section('content')
<style>
table.dataTable tbody th, table.dataTable tbody td {
    padding: 4px 5px;
}
table.dataTable thead th, table.dataTable thead td {
    padding: 4px 5px;
}
.table thead tr th {
    font-size: 12px;
}
.table td, .table th {
    font-size: 11px;
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
                            <span class="input-group-addon">Site</span>
                            <select class="form-control"  id="site" name="site">
                                <option value="">Select</option>
                                @foreach(getSiteCode() as $key=>$val)
                                    <option value="{!! $val !!}">{!! $key !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                       
                    </div>
                    <div class="col-md-2">
                         <div class="input-group">
                            <span class="input-group-addon">BG</span>
                            <select class="form-control" id="bg" name="bg">
                                <option value="">Select</option>
                                @foreach($bgs as $bg)
                                    <option value="{!! $bg !!}">{!! $bg !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                       
                    </div>

                    <div class="col-md-2">
                         <div class="input-group">
                            <span class="input-group-addon">BU</span>
                            <select  class="form-control"  id="bu" name="bu">
                                <option value="">Select</option>
                                @foreach($bus as $bu)
                                    <option value="{!! $bu !!}">{!! $bu !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                       
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">Sellers</span>
                            <select  class="form-control"  id="sap_seller_id" name="sap_seller_id">
                                <option value="">Select</option>
                                @foreach(getUsers('sap_seller') as $key=>$val)
                                    <option value="{!! $key !!}">{!! $val !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        
                    </div>

                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon">SkuStatus</span>
                            <select  class="form-control"  id="sku_status" name="sku_status">
                                <option value="">Select</option>
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
                            <span class="input-group-addon">SkuLevel</span>
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
							<button id="export" class="btn sbold blue"> Export
								<i class="fa fa-download"></i>
							</button>
							</div>
							<div class="btn-group pull-right" style="margin-right:20px;">
								<button id="search" class="btn sbold blue">Search</button>
							</div>
						</div>
                    </div> 
					
					
					
                </div>
            </div>

            </form>
			
			<div class="col-md-12">
				<div class="form-upload">
				<form action="{{url('mrp/import')}}" method="post" enctype="multipart/form-data" class="pull-right " >
				<!--<div class=" pull-left">

					<a href="{{ url('/uploads/BudgetsUpload/data.csv')}}">Import Template</a>	
				</div>-->
				<div class="pull-left">
					{{ csrf_field() }}
						 <input type="file" name="importFile"  />
				</div>
				<div class=" pull-left">
					<button type="submit" class="btn blue btn-sm" id="data_search">Upload</button>
				</div>
				
				</form>
				</div>
				
				
			</div>

            </div>
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
						<th> SellerName </th>
                        <th>Asin</th>
                        <th>Site</th>
                        <th>Sku</th>
                        <th>MinPurchase</th>
                        <th>W/Sales</th>
						<th>TotalPlan</th>
						<?php
						for($i=1;$i<=22;$i++){
						?>
                        <th>{{date('Y-m-d',strtotime('+'.$i.' weeks sunday'))}}</th>
                        <?php } ?>
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
                    [10, 50, 100, -1],
                    [10, 50, 100, 'All']
                ],
                "pageLength": 10,
                pagingType: 'bootstrap_extended',
                processing: true,
                ordering:  false,
                //aoColumnDefs: [ { "bSortable": false, "aTargets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,21] }],
                order: [],
                columns: [
					{data: 'seller', name: 'seller'},
                    {data: 'asin', name: 'asin'},
                    {data: 'site', name: 'site'},
                    {data: 'sku', name: 'sku'},
                    {data: 'min_purchase', name: 'min_purchase'},
                    {data: 'week_daily_sales', name: 'week_daily_sales'},
					{data: '22_week_plan_total', name: '22_week_plan_total'},
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
			alert($("#search-form").serialize());
            location.href='/mrp/export?'+$("#search-form").serialize();
            return false;

        });


    </script>

@endsection