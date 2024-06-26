<div class="portlet light bordered">
<div class="row">
	<div class="col-md-6"><a href="https://{{$domain}}/dp/{{$asin}}" target="_blank">https://{{$domain}}/dp/{{$asin}}</a></div>
	<div class="col-md-3">
		<div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
			<input type="text" class="form-control form-filter input-sm pie_set" readonly name="asin_from" id="asin_from" placeholder="Date From" value="{{date('Y-m-d',strtotime('-30day'))}}">
			<span class="input-group-btn">
													<button class="btn btn-sm default" type="button">
														<i class="fa fa-calendar"></i>
													</button>
												</span>
		</div>
	</div>
	<div class="col-md-3">
		<div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
			<input type="text" class="form-control form-filter input-sm pie_set" readonly name="asin_to" id="asin_to" placeholder="Date To" value="{{date('Y-m-d')}}">
			<span class="input-group-btn">
													<button class="btn btn-sm default" type="button">
														<i class="fa fa-calendar"></i>
													</button>
												</span>
		</div>
	</div>
	
</div>
</div>
<div id="lineChart" style="height: 500px;width:100%;"></div>
<script type="text/javascript">

$(function() {
	function loadpie(){
	var asin_from = $('#asin_from').val();
	var asin_to = $('#asin_to').val();
	var lineChart = echarts.init(document.getElementById('lineChart'));
	lineChart.clear();
	lineChart.showLoading();
	$.ajax({
		type: "POST",
		cache: false,
		url: '/star/detail',
		dataType: "json",
		data: {'asin_from': asin_from,'asin_to': asin_to,'asin': '<?php echo $asin;?>','domain': '<?php echo $domain;?>'},
		success: function(res) 
		{
			
			var chartX=[]; var chartY1=[];var chartY2=[];var chartY3=[];var chartY4=[];var chartY5=[];var chartY6=[];var chartY7=[];var chartY8=[];var chartY9=[];
			$.each(res, function (k, v) {
				chartX.push(k);
				chartY1.push(v.price);
				chartY2.push(v.sale_price);
				chartY3.push(v.avg_price);
				chartY4.push(v.sales);
				chartY5.push(v.review);
				chartY6.push(v.rating);
				chartY7.push(v.sessions);
				chartY8.push(v.unit_session_percentage);
				chartY9.push(v.bsr);
			});
			
			
			var option = {
				tooltip : {
					trigger: 'axis'
				},
				legend: {
					x: 'left',
					itemWidth:10,
					itemHeight:10,
					
					data:['Price','Sale Price','Avg Price','Sold Qty','Review Count','Rating','Sessions','UnitSessionPercentage','BSR'],
					selected: {
						'Price': false,
						'Sale Price': true,
						'Avg Price': false,
						'Sold Qty': true,
						'Review Count': false,
						'Rating': false,
						'Sessions': false,
						'UnitSessionPercentage': false,
						'BSR': false
					},
					inactiveColor:"#888",
					icon:"roundRect"
				},

				toolbox: {
					show : true,
					orient : 'vertical',
					feature : {
						mark : {show: true},
						dataView : {show: true, readOnly: false},
						magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
						restore : {show: true},
						saveAsImage : {show: true}
					}
				},
				calculable : true,
				xAxis : [
					{
						type : 'category',
						boundaryGap : false,
						data : chartX
					}
				],
				yAxis : [
					{
						type : 'value'
					}
				],
				series : [
					{
						name:'Price',
						type:'line',
						data:chartY1
					},
					{
						name:'Sale Price',
						type:'line',
						data:chartY2
					},
					{
						name:'Avg Price',
						type:'line',

						data:chartY3
					},
					{
						name:'Sold Qty',
						type:'line',
						data:chartY4
					},
					{
						name:'Review Count',
						type:'line',

						data:chartY5
					},
					{
						name:'Rating',
						type:'line',

						data:chartY6
					},
					{
						name:'Sessions',
						type:'line',

						data:chartY7
					},
					{
						name:'UnitSessionPercentage',
						type:'line',

						data:chartY8
					},
					{
						name:'BSR',
						type:'line',

						data:chartY9
					}
				]
			};
			lineChart.hideLoading();
			lineChart.setOption(option,true);
		},
		async: false
	});
}
    $('.date-picker').datepicker({
		rtl: App.isRTL(),
		autoclose: true
	});
	$('.pie_set').change(function(){
		loadpie();
	});
	loadpie();
});



</script>
