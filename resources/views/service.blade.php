@extends('layouts.layout')

@section('content')
<style>
.widget-thumb .widget-thumb-body .widget-thumb-body-stat {font-size:20px;}
.widget-thumb .widget-thumb-wrap .widget-thumb-icon{width:50px;height:50px;line-height:30px;}
.widget-thumb .widget-thumb-heading{color:#666; margin-bottom:10px;}
.dashboard-stat2 { margin-bottom:0px;margin-top: 8px;}
.col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9{
padding:0px !important;}
.portlet {margin-bottom:0px;}
.portlet.light{padding-top:0px;}
.portlet.light > .portlet-title > .caption{
padding:5px 0;
}
.portlet.light > .portlet-title a{
 font-size:12px;
}
.portlet.light > .portlet-title > .actions {
    padding: 6px 0 6px 0;
}
.portlet.light > .portlet-title{
min-height:30px;
margin-bottom:0px;}
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    padding: 7px;
    line-height: 15px;
	text-align: right;
}
.table td, .table th {
    font-size: 12px;
}
.portlet.light .portlet-body{
padding-top:0px;}
.portlet.light > .portlet-title > .nav-tabs > li > a {
    margin: 0;
    padding: 5px 10px;
    font-size: 13px;
    color: #666;
}
.dashboard-stat2 .display {
    margin-bottom: 10px;
}
.dashboard-stat2 .display .number h3 {

    font-size: 20px;
    font-weight: bold;
}
.dashboard-stat2 .display .number h3 > small {
    font-size: 14px;
}
.primary-link {
    font-size: 12px;
}
.portlet.light > .portlet-title > .caption > .caption-subject {
    font-size: 12px;
}
table{ 
table-layout:fixed; 
}

textarea.form-control{width:400px!important;}

.editable-click, 
a.editable-click, 
a.editable-click:hover {
    text-decoration: none;
    border-bottom: none !important;
}

.dashboard-stat2 .progress-info .status {
    font-size: 12px;
    color: #666;
}
.tongji span{
	margin-left:20px;
	font-size:15px;
}

#fast-search{
	background-color: #FFFFFF;
	width: 543px;
	height: 90px;
	margin: auto;
}
#fast-search .search-type{
	text-align: center;
	width: 85px;
	height: 36px;
	background-color: #FFFFFF;
	cursor: pointer;
	font-size: 14px;
	display: table;
}

#fast-search .search-type-wider{
	width: 118px !important;
}

#fast-search .search-type span{
	display: table-cell;
	vertical-align: middle;
}

#fast-search .active{
	background-color: #3598DC;
	border-radius: 5px 5px 0px 0px !important;
	color:#FFFFFF;
}
.mycustom {
	border: 1px solid #c2cad8;
	position: relative;
	width: 543px;
	height: 38px;
}
.mycustom input[type=text] {
	border: none;
	width: 100%;
	padding-right: 123px;
}
.mycustom .input-group-prepend {
	position: absolute;
	right: 4px;
	top: 4px;
	bottom: 4px;z-index:9;
}
</style>
<link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />

<div class="row">
	<div style="width:100%; background-color: #ffffff">
		<div id="fast-search">
			<div class="pull-right">
				<input type="text" name="searchType" id="searchType" value="0" hidden />
				<div class="search-type pull-left active" type="0"><span>Order ID</span></div>
				<div class="search-type search-type-wider pull-left" type="1"><span>Customer Info</span></div>
				<div class="search-type pull-left" type="2"><span>Parts List</span></div>
				<div class="search-type pull-left" type="3"><span>Inventory</span></div>
				<div class="search-type pull-left" type="4"><span>Manual</span></div>
				<div class="search-type pull-left" type="5"><span>QA</span></div>
			</div>
			<div class="clearfix"></div>
			<div class="input-group mycustom pull-right">
				<input type="text" name="searchTerm" id="searchTerm" class="form-control rounded-0" placeholder="Please enter a search term..." />
				<div class="input-group-prepend">
					<a data-target="#myModal" data-toggle="modal" id="modalLink" href="/task/create">
						<input type="button" value="Search" id="searchBtn" class="btn btn-danger btn-sm rounded-0" />
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row" >
<div class="col-lg-12 col-xs-12 col-sm-12">
	<div class="portlet light ">
		<div class="col-md-9">
			<div class="col-md-2">
			<div style="width: 140px;height: 140px;border: 20px solid #f36a5a;border-radius:50% !important;text-align:center;line-height: 100px;margin:auto;font-size: 18px;" >
				<span id="tichengxianshi" style="cursor:pointer;" data-value="{{intval($total_score)}}" class="fa fa-eye-slash"></span>
			</div>
			</div>
			<div class="col-md-10">
			<?php
			$class=$sign="";
			$ap = intval(array_get($dash,'0.0',0));
			$hb_ap = intval(array_get($dash,'0.1',0));
			if($ap>$hb_ap){
				$class="font-red-haze";
				$sign='+';
			}
			if($ap<$hb_ap){
				$class="font-green-sharp";
			}
			?>
			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:25%;">
				<div class="dashboard-stat2 ">
					<div class="display">
						<div class="number">
							<small>Tickets</small>
							<h3 class="font-green-sharp">
								<span data-counter="counterup">{{$ap}}</span>
								<small class="font-green-sharp"></small>
							</h3>

						</div>

					</div>
					<div class="progress-info">
						<div class="progress">
							<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">

							</span>
						</div>
						<div class="status">
							<div class="status-title {{$class}}"> {{($hb_ap!=0)?$sign.round(($ap-$hb_ap)/$hb_ap*100,2):'0'}}%</div>
							<div class="status-number">Yesterday:{{$hb_ap}}</div>
						</div>
					</div>
				</div>
			</div>

			<?php
			$class=$sign="";
			$ap = intval(array_get($dash,'2.0',0)+array_get($dash,'3.0',0));
			$hb_ap = intval(array_get($dash,'2.1',0)+array_get($dash,'3.1',0));
			if($ap>$hb_ap){
				$class="font-red-haze";
				$sign='+';
			}
			if($ap<$hb_ap){
				$class="font-green-sharp";
			}
			?>

			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:25%;">
				<div class="dashboard-stat2 ">
					<div class="display">
						<div class="number">
							<small>Postive Reviews</small>
							<h3 class="font-red-haze">
								<span data-counter="counterup" >{{$ap}}</span>
								<small class="font-red-haze"></small>
							</h3>

						</div>

					</div>
					<div class="progress-info">
						<div class="progress">
							<span style="width: 100%;" class="progress-bar progress-bar-success red-haze">

							</span>
						</div>
						<div class="status">
							<div class="status-title {{$class}}"> {{($hb_ap!=0)?$sign.round(($ap-$hb_ap)/$hb_ap*100,2):'0'}}%</div>
							<div class="status-number">Yesterday:{{$hb_ap}}</div>
						</div>
					</div>
				</div>
			</div>

			<?php
			$class=$sign="";
			$ap = intval(array_get($dash,'2.0',0));
			$hb_ap = intval(array_get($dash,'2.1',0));
			if($ap>$hb_ap){
				$class="font-red-haze";
				$sign='+';
			}
			if($ap<$hb_ap){
				$class="font-green-sharp";
			}
			?>
			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:25%;">
				<div class="dashboard-stat2 ">
					<div class="display">
						<div class="number">
							<small>SG</small>
							<h3 class="font-blue-sharp">
								<span data-counter="counterup" >{{$ap}}</span>
							</h3>

						</div>

					</div>
					<div class="progress-info">
						<div class="progress">
							<span style="width: 100%;" class="progress-bar progress-bar-success blue-sharp">

							</span>
						</div>
						<div class="status">
							<div class="status-title {{$class}}"> {{($hb_ap!=0)?$sign.round(($ap-$hb_ap)/$hb_ap*100,2):'0'}}% </div>
							<div class="status-number">Yesterday:{{$hb_ap}}</div>
						</div>
					</div>
				</div>
			</div>
			<?php
			$class=$sign="";
			$ap = intval(array_get($dash,'3.0',0));
			$hb_ap = intval(array_get($dash,'3.1',0));
			if($ap>$hb_ap){
				$class="font-red-haze";
				$sign='+';
			}
			if($ap<$hb_ap){
				$class="font-green-sharp";
			}
			?>
			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:25%;">
				<div class="dashboard-stat2 ">
					<div class="display">

						<div class="number">
						<small>RSG</small>
							<h3 class="font-purple-soft">
								<span data-counter="counterup" >{{$ap}}</span>
								<small class="font-purple-soft"></small>
							</h3>

						</div>

					</div>
					<div class="progress-info">
						<div class="progress">
							<span style="width: 100%;" class="progress-bar progress-bar-success purple-soft">

							</span>
						</div>
						<div class="status">
							<div class="status-title {{$class}}">

							 {{($hb_ap!=0)?$sign.round(($ap-$hb_ap)/$hb_ap*100,2):'0'}}%

							</div>
							<div class="status-number">Yesterday:{{$hb_ap}}</div>
						</div>
					</div>
				</div>
			</div>

			</div>
		</div>
		<div class="col-md-3" style="    padding-top: 10px !important;">


				<form role="form" action="{{url('service')}}" method="GET">
					{{ csrf_field() }}

					<div class="form-group col-md-5" >

						<div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
							<input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="Date" value="{{$date_from}}">
							<span class="input-group-btn">
								<button class="btn btn-sm default" type="button">
									<i class="fa fa-calendar"></i>
								</button>
							</span>
						</div>
					</div>
					<div class="form-group col-md-5 col-md-offset-2" >

						<div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
							<input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="Date" value="{{$date_to}}">
							<span class="input-group-btn">
								<button class="btn btn-sm default" type="button">
									<i class="fa fa-calendar"></i>
								</button>
							</span>
						</div>
					</div>


					<div class="form-group col-md-5">
					<select class="mt-multiselect btn btn-default form-control form-filter input-sm " data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id" id="user_id">
							<option value="">All CS</option>
							@foreach ($users as $id=>$user_name)
								<option value="{{$id}}" <?php if($user_id==$id) echo 'selected'; ?>>{{$user_name}}</option>
							@endforeach
						</select>

					</div>

					<div class="form-group col-md-5 col-md-offset-2">
						<button type="submit" class="btn blue" id="data_search">Search</button>
					</div>



				</form>
		</div>
		<div style="clear:both;"></div>
		<div class="col-md-12" id="lineChartDiv" style="display:none;">
			<div id="lineChart" style="width:100%;height:200px;"></div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>
</div>
		
<div class="row">					
<div class="col-lg-12 col-xs-12 col-sm-12">
	<div class="portlet light ">
		<div class="portlet-title tabbable-line">
			<div class="caption">
				<i class=" icon-social-twitter font-dark hide"></i>
				<span class="caption-subject font-dark bold uppercase pull-left">MY TASKS</span>
				<div class="tongji red pull-left">
					<a class="hyperlink" aIndex="0" href="{{url('/inbox/fromService')}}"><span>inbox（{{$statis['inbox']}}）</span></a>
					<a class="hyperlink" aIndex="1" href="{{url('/inbox/fromService')}}"><span>Time out（{{$statis['timeout']}}）</span></a>
					<a class="hyperlink" aIndex="2" href="{{url('/exception/fromService')}}"><span>R&R Done（{{$statis['done']}}）</span></a>
					<a class="hyperlink" aIndex="3" href="{{url('/exception/fromService')}}"><span>R&R Canceled（{{$statis['calcel']}}）</span></a>
				</div>
				<form id="serviceHyperLink" action="" method="post" target="_blank">
					{{ csrf_field() }}
					<input type="hidden" name="linkIndex" id="linkIndex" value="0">
					<input type="hidden" name="fromService" value="yes" />
				</form>
			</div>
					<div class="pull-right">
						<a href="{{url('task')}}">View More&gt;&gt;</a>
					</div>
		</div>
		<div class="portlet-body">

			<table class="table table-hover" id="tasks_list">
			
			
			<tr class="uppercase">
				<th width="2%"><font style="color:#ccc;"><i  class="fa fa-info-circle popovers" data-container="body" onclick="" data-trigger="hover" data-placement="right" data-html="true" data-content="Click to complete the task quickly"></i></font></th>
				<th width="44%" >Task Details</th>
				<th width="30%"  >Task Response</th>
				<th width="8%" >Due To</th>
				<th width="8%" >By</th>
				<th width="8%" >Priority</th>
			</tr>
			
			@foreach ($tasks as $task)
			<tr id="task_{{$task['id']}}">
				<td><input type="checkbox" class="task_quick_complete" id="{{$task['id']}}" /></a>
				</td>
				<td ><a class="task_request primary-link" title="Task Details" href="javascript:;" id="{{$task['id']}}-request" data-pk="{{$task['id']}}-request" data-type="textarea" data-placement="right">{{$task['request']}}</a></td>
				<td ><a class="task_response" title="Task Response" href="javascript:;" id="{{$task['id']}}-response" data-pk="{{$task['id']}}-response" data-type="textarea" data-placeholder="Your response here...">{{$task['response']}}</a></td>
				<td><a class="task_complete_date" title="Due To" href="javascript:;" id="{{$task['id']}}-complete_date" data-pk="{{$task['id']}}-complete_date" data-type="date" data-viewformat="yyyy-mm-dd" data-placement="right">{{$task['complete_date']}}</a></td>
				<td>{{array_get($users,$task['request_user_id'])}}</td>
				<td >
				<a class="task_priority" title="Priority" href="javascript:;" id="{{$task['id']}}-priority" data-pk="{{$task['id']}}-priority" data-type="text" data-placement="left">{{$task['priority']}}</a>
				</td>
			</tr>
			@endforeach
			</table>
		</div>
	</div>
</div>
</div>

<div class="modal fade bs-modal-lg" id="myModal" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" >
			<div class="modal-body" >
			</div>
		</div>
	</div>
</div>

<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>    
<script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/form-editable.min.js" type="text/javascript"></script>

<script type="text/javascript">

$(function(){
	$(".hyperlink").click(function(){
		var href = $(this).attr("href");
		var aIndex = $(this).attr("aIndex");
		$('#linkIndex').val(aIndex);
		$('#serviceHyperLink').attr("action", href).submit();
		return false;
	});
})


$('#searchBtn').click(function(){
	//当搜索类型为QA时，$("#searchType").val()的值为5，页面跳转到知识中心，回退网页到service页面，这时Order ID处于active状态，但此时$("#searchType").val()的值仍为5. 所以不能用var searchType = $("#searchType").val()
	//var searchType = $("#searchType").val();
	var searchType = $('#fast-search .active').attr('type');
	var searchTerm = $('#searchTerm').val().trim();

    if(searchTerm == '') {
        return false;
    }
	//将搜索内容中的''（空格）全部替换成'+'，否则会出现问题
    var searchTerm = searchTerm.replace(/\s+/g, '+');
    //当搜索类型为QA时，页面跳转到含有搜索内容的知识中心页面。
    if(searchType == 5){
		var knowledgeUrl = "/question?knowledge_type=&group=ALL&item_group=ALL&keywords=" + searchTerm;
		window.location.href=knowledgeUrl;
		return false;
	}
	$('.modal-content').html('Please wait...');
	$('#modalLink').attr('href', '/service/fastSearch?searchType='+searchType+'&searchTerm='+searchTerm);
});

$('#fast-search .search-type').click(function() {
	$('#fast-search .search-type').removeClass('active');
	$(this).addClass('active');
	var value = $(this).attr('type');
	$('#searchType').val(value);
});

var FormEditable = function() {

    $.mockjaxSettings.responseTime = 500;

    var initAjaxMock = function() {
        $.mockjax({
            url: '/taskajaxupdate',
			type:'post',
            response: function(settings) {
				console.log(this);
				console.log(settings);
            }
        });
    }

    var initEditables = function() {
        $.fn.editable.defaults.inputclass = 'form-control';
        $.fn.editable.defaults.url = '/taskajaxupdate';
		$('.task_request,.task_response').editable({
			emptytext:'N/A'
		});
		var sellers = [];
        <?php foreach($users as $k=>$v) {?>
		sellers.push({
			value: "<?php echo $k?>",
			text: "<?php echo $v?>"
		});
		<?php }?>

        $('.task_response_user_id').editable({
            inputclass: 'form-control input-medium',
            source: sellers
        });
		
		$('.task_request_user_id').editable({
            inputclass: 'form-control input-medium',
            source: sellers
        });
		
		var stages = [];
        <?php foreach(getTaskStageArr() as $k=>$v) {?>
		stages.push({
			value: "<?php echo $k?>",
			text: "<?php echo $v?>"
		});
		<?php }?>
		$('.task_stage').editable({
            inputclass: 'form-control input-medium',
            source: stages
        });
		
		$('.task_priority').editable({
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
		
		$('.task_complete_date').editable({
            format: 'yyyy-mm-dd',
            viewformat: 'yyyy-mm-dd',
        });
    }

    return {
        init: function() {
            initEditables();
        }
    };

}();
$(function() {
	
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		format: 'yyyy-mm-dd',
		orientation: 'bottom',
		autoclose: true,
	});
	
	
	
	var date_data = [];
	var comm_data = [];
	var email_data = [];
	var chat_data = [];
	var messager_data = [];
	var call_data = [];
	var sg_data = [];
	var rsg_data = [];
	<?php 
	foreach($details as $k=>$v) {?>
	date_data.push('<?php echo $k?>');
	comm_data.push('<?php echo intval(array_get($v,'0',0)+array_get($v,'1',0)+array_get($v,'2',0)+array_get($v,'3',0)+array_get($v,'sg',0)+array_get($v,'rsg',0))?>');
	email_data.push('<?php echo intval(array_get($v,'3'))?>');
	chat_data.push('<?php echo intval(array_get($v,'0'))?>');
	messager_data.push('<?php echo intval(array_get($v,'2'))?>');
	call_data.push('<?php echo intval(array_get($v,'1'))?>');
	sg_data.push('<?php echo intval(array_get($v,'sg'))?>');
	rsg_data.push('<?php echo intval(array_get($v,'rsg'))?>');
	<?php }?>
	lineoption = {
		title : {
			text: 'Score History',

		},
		tooltip : {
			trigger: 'axis'
		},
		legend: {
			x: 'right',
			itemWidth:10,
			itemHeight:10,
			data:['Score','Email','Chat','Messager','Call','Sg','Rsg'],
			selected: {
				'Score': true,
				'Email': false,
				'Chat': false,
				'Messager': false,
				'Call': false,
				'Sg': false,
				'Rsg': false
			},
			inactiveColor:"#888",
			icon:"roundRect",
			selectedMode: 'single'
		},
		grid: {
			x:40,
			y:40,
			x2:40,
			y2:40,
		},
		calculable : true,
		xAxis : [
			{
				type : 'category',
				boundaryGap : false,
				data : date_data
			}
		],
		yAxis : [
			{
				type : 'value'
			}
		],
		series : [
			{
				name:'Score',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:comm_data
			},
			{
				name:'Email',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:email_data
			},
			{
				name:'Chat',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:chat_data
			},
			{
				name:'Messager',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:messager_data
			}
			,
			{
				name:'Call',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:call_data
			},
			{
				name:'Sg',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:sg_data
			},
			{
				name:'Rsg',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:rsg_data
			}
		]
	};
	
	var lineChart = echarts.init(document.getElementById('lineChart'));
	lineChart.setOption(lineoption,true);
	$("#tichengxianshi").click(function(){

		if($(this).hasClass('fa-eye-slash')){
			$(this).removeClass('fa-eye-slash');
			$(this).removeClass('fa');	
			$(this).html($(this).data('value'));
			$(this).text($(this).data('value'));	
			$("#lineChartDiv").slideDown();
			lineChart.resize();
		}else{
			$(this).addClass('fa-eye-slash');
			$(this).addClass('fa');	
			$(this).html('');
			$(this).text('');		
			$("#lineChartDiv").slideUp();
		}
	});
	
	$(".task_quick_complete").on('click',function(){
	
		var task_id = $(this).attr('id');
		var exist_ids = [];
		$(".task_quick_complete").each(function(){
			exist_ids.push($(this).attr('id'));
		});
		exist_ids.join(',');
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('taskajaxupdate') }}",
			data: "name="+task_id+"-stage&value=3&exist_ids="+exist_ids,
			success: function (redata) {
				$("#task_"+task_id).remove();
				if(redata.id){
					var str='<tr id="task_'+redata.id+'"><td><input type="checkbox" class="task_quick_complete" id="'+redata.id+'" /></a></td><td><a class="task_request primary-link" title="Task Details" href="javascript:;" id="'+redata.id+'-request" data-pk="'+redata.id+'-request" data-type="textarea" data-placement="right">'+redata.request+'</a></td><td><a class="task_response" title="Task Response" href="javascript:;" id="'+redata.id+'-response" data-pk="'+redata.id+'-response" data-type="textarea" data-placeholder="Your response here..." >'+((redata.response)?redata.response:"N/A")+'</a></td><td><a class="task_complete_date" title="Due To" href="javascript:;" id="'+redata.id+'-complete_date" data-pk="'+redata.id+'-complete_date" data-type="date" data-viewformat="yyyy-mm-dd" data-placement="right">'+redata.complete_date+'</a></td><td>'+redata.request_user+'</td><td><a class="task_priority" title="Priority" href="javascript:;" id="'+redata.id+'-priority" data-pk="'+redata.id+'-priority" data-type="text" data-placement="left">'+redata.priority+'</a></td></tr>';
					$("#tasks_list").append(str);
					FormEditable.init();
				}
			},
			error: function(data) {
				alert("error:"+data.responseText);
			}
		});
	});
	
	
	lineChart.resize();
    FormEditable.init();
});
</script>
@endsection
