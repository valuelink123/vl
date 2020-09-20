@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>['MWS dashboard']])
@endsection
<style>
	.total-data-table{
		width:100%;
		margin-bottom: 20px;
		/*border-color: #676464;*/
	}
	.top-total-data .weight td{
		font-size:30px;
		font-weight:400;
	}
	.total-data-table td{
		padding: 7px;
		line-height: 15px;
		/*border-left: 1px solid #676464;*/
		/*border-right: 1px solid #676464;*/
		font-size: 11px;
	}
	.date-search-table{
		width:60%;
		margin-bottom: 20px;

	}
	.date-search-table td{
		border:1px solid #676464;
		padding: 7px 12px;
		margin: 11px 20px 0px 0px;
		text-align:center;
	}
	.date-search-table .active{
		background-color:#CD4D00;
		color:#ffffff;
		border:1px solid #CD4D00;
	}
    #datatable th{
        text-align:center;
    }

</style>
@section('content')
	@include('frank.common')
	<div class="row">
        <div class="top portlet light">
        <form id="search-form">
            <input type="hidden" name="date_type" value="">
            <div class="search portlet light">
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">Site</span>
                        <select  style="width:100%;height:35px;" id="site" onchange="getAccountBySite()" name="site">
                            @foreach($site as $value)
                                <option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">Account</span>
                        <select  style="width:100%;height:35px;" id="account" name="account">
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">BGBU</span>
                        <select  style="width:100%;height:35px;" id="bgbu" name="bgbu">
                            <option value="">Select</option>
                            @foreach($bgbu as $value)
                                <option value="{{ $value->bg }}_{{$value->bu}}">{{ $value->bg }}_{{$value->bu}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            </form>
        </div>
		<div class="top portlet light">
			<table class="top-total-data total-data-table">
				<tr>
					<td>SALES</td>
					<td>UNITS</td>
					<td>ORDERS</td>
					<td>AVG.PRICE</td>
{{--					<td>NET BEFORE COG | <span class="net-before-cog">64%</span></td>--}}
{{--					<td>NET PROFIT | <span class="net-profit">64%</span></td>--}}
				</tr>
				<tr class="weight second">
					<td>$<span class="sales">0</span></td>
					<td><span class="units">0</span></td>
					<td><span class="orders">0</span></td>
					<td>$<span class="avgPrice">0</span></td>
{{--					<td>$<span class="before-cog">366</span></td>--}}
{{--					<td>$<span class="profit">566</span></td>--}}
				</tr>
				<tr class="third">
					<td>NET REVENUE:$<span class="revenue">0</span></td>
					<td>FULL:<span class="unitsFull">0</span> | PROMO:<span class="unitsPromo">0</span></td>
					<td>FULL:<span class="ordersFull">0</span>|PROMO:<span class="ordersPromo">0</span></td>
{{--					<td>STOCK VALUE:$<span class="stock-value">0.23</span></td>--}}
					<td></td>
{{--					<td>COG:$<span class="cog">33</span></td>--}}
{{--					<td>PPC:$<span class="ppc">363</span> | ROI:<span class="roi">0%</span></td>--}}
				</tr>
			</table>

			<table class="date-search date-search-table">
				<tr>
					<td class="date_type active" data-value="1">TODAY</td>
					<td class="date_type" data-value="2">YESTERDAY</td>
					<td class="date_type" data-value="3">LAST 7 DAYS</td>
					<td class="date_type" data-value="4">WEEK TO DATE</td>
					<td class="date_type" data-value="5">LAST 30 DAYS</td>
					<td class="date_type" data-value="6">MONTH TO DATE</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="row">
		<div class="top portlet light">
			<table class="table table-striped table-bordered" id="datatable">
                <thead>
    				<tr>
    					<th></th>
    					<th>PRODUCT</th>
    					<th>ASIN</th>
    					<th>SALES</th>
    					<th>UNITS</th>
    					<th>ORDERS</th>
    {{--					<th>FEES</th>--}}
    {{--					<th>REFUNDS</th>--}}
    {{--					<th>NET PROFIT</th>--}}
    {{--					<th>SESSIONS</th>--}}
    {{--					<th>CONVERSION PATE</th>--}}
    {{--					<th>INVENTORY</th>--}}
    					<th>AVG.UNITS PER DAY</th>
    {{--					<th>STOCK OUT DATE</th>--}}
    {{--					<th>BSR</th>--}}
    				</tr>
                </thead>
                <tbody></tbody>
			</table>
		</div>
	</div>

	<script>



        // let $theTable = $(thetable)


            $('#datatable').dataTable({
                searching: false,//关闭搜索
                serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                ordering:false,
                "pageLength": 10, // default record count per page
                "lengthMenu": [
                    [10, 20,50,],
                    [10, 20,50,] // change per page values here
                ],
                // pagingType: 'bootstrap_extended',
                processing: true,
                columns: [
                    {data: 'image',name:'image'},
                    {data: 'title',name:'title'},
                    {data: 'asin',name:'asin'},
                    {data: 'sales',name:'sales'},
                    {data: 'units',name:'units'},
                    {data: 'orders',name:'orders'},
                    {data: 'avg_units',name:'avg_units'},
                ],
                ajax: {
                    type: 'POST',
                    url: '/ccp/list',
                    data:  {search: $("#search-form").serialize()}
                }
            })


        // initTable();
        // let dtApi = $theTable.api();
        // $(selector).dataTable().api();

		$('.date-search .date_type').click(function(){
		    $('.date-search .date_type').removeClass('active');
		    $(this).addClass('active');
			var value = $(this).attr('data-value');
			$('input[name="date_type"]').val(value);
			$.ajax({
				type: 'post',
				url: '/ccp/showTotal',
				data: {date_type:value},
				dataType:'json',
				success: function(res) {
					$('.total-data-table .sales').text(res.sales);
                    $('.total-data-table .units').text(res.units);
                    $('.total-data-table .orders').text(res.orders);
                    $('.total-data-table .avgPrice').text(res.avgPrice);
                    $('.total-data-table .revenue').text(res.revenue);
                    $('.total-data-table .unitsFull').text(res.unitsFull);
                    $('.total-data-table .unitsPromo').text(res.unitsPromo);
                    $('.total-data-table .ordersFull').text(res.ordersFull);
                    $('.total-data-table .ordersPromo').text(res.ordersPromo);
				}
			});
			//改变下面表格的数据内容
            dtapi = $('#datatable').dataTable().api();
            dtapi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtapi.ajax.reload();

		})
        function getAccountBySite(){
		    var marketplaceid = $('#site option:selected').val();
		    console.log(marketplaceid);
            $.ajax({
                type: 'post',
                url: '/ccp/showAccountBySite',
                data: {marketplaceid:marketplaceid},
                dataType:'json',
                success: function(res) {
					if(res.status==1){
						var html = '';
                        $.each(res.data,function(i,item) {
                            html += '<option value="'+item.id+'">'+item.label+'</option>';
                        })
						console.log(html);
						$('#account').html(html);
					}else{
					    alert('请先选择站点');
					}
                }
            });

		}

        $(function(){
            // 根据搜索时间区域，调用点击事件，展示上部分的统计数据
            $(".date-search .active").trigger("click");
        })
	</script>

@endsection