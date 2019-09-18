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
    line-height: 12px;
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
    font-size: 14px;
}
.portlet.light > .portlet-title > .caption > .caption-subject {
    font-size: 12px;
}
table{ 
table-layout:fixed; 
}
td.text_s{       
text-overflow:ellipsis; 
-moz-text-overflow: ellipsis; 
overflow:hidden;      
white-space: nowrap;      
} 
textarea.form-control{width:400px!important;}
.editable-pre-wrapped {
    white-space: nowrap !important;   
}
</style>
<link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
<div class="row" >
<div class="col-lg-12 col-xs-12 col-sm-12">
<div class="portlet light ">
			<div class="col-md-1">
				<div style="width:100px;height:100px;border: 15px solid #f36a5a;border-radius:50% !important;text-align:center;line-height:70px;margin:auto;margin-top: 13px" >
					<span id="tichengxianshi" style="cursor:pointer;" data-value="12312.14" class="fa fa-eye-slash"></span>
				</div>
			</div>
			<div class="col-md-8">
				<div class="col-md-10 col-md-offset-1">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;"> 
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>E.VALUE</small>
								<h3 class="font-green-sharp">
									<span data-counter="counterup" data-value="7800">7800</span>
									<small class="font-green-sharp">¥</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">
									<span class="sr-only">100% progress</span>
								</span>
							</div>
							<div class="status">
								<div class="status-title"> FULL : 294 </div>
								<div class="status-number"> PROMO : 19 </div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>SALES</small>
								<h3 class="font-red-haze">
									<span data-counter="counterup" data-value="1349">1349</span>
									<small class="font-red-haze">¥</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 85%;" class="progress-bar progress-bar-success red-haze">
									<span class="sr-only">85% change</span>
								</span>
							</div>
							<div class="status">
								<div class="status-title"> RING RATIO : 5% </div>
								<div class="status-number"> </div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>UNITS</small>
								<h3 class="font-blue-sharp">
									<span data-counter="counterup" data-value="567">567</span>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 45%;" class="progress-bar progress-bar-success blue-sharp">
									<span class="sr-only">45% grow</span>
								</span>
							</div>
							<div class="status">
								<div class="status-title"> RING RATIO : 5% </div>
								<div class="status-number">  </div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;">
					<div class="dashboard-stat2 ">
						<div class="display">
						
							<div class="number">
							<small>AVG.PRICE</small>
								<h3 class="font-purple-soft">
									<span data-counter="counterup" data-value="276">276</span>
									<small class="font-purple-soft">¥</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 57%;" class="progress-bar progress-bar-success purple-soft">
									<span class="sr-only">56% change</span>
								</span>
							</div>
							<div class="status">
								<div class="status-title"> STOCK VALUE : 123123 </div>
								<div class="status-number">  </div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="width:20%;"> 
					<div class="dashboard-stat2 ">
						<div class="display">
							<div class="number">
								<small>INVENTORY</small>
								<h3 class="font-green-sharp">
									<span data-counter="counterup" data-value="7800">7800</span>
									<small class="font-green-sharp">PCS</small>
								</h3>
								
							</div>
							
						</div>
						<div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success green-sharp">
									<span class="sr-only">100% progress</span>
								</span>
							</div>
							<div class="status">
								<div class="status-title"> STOCK VALUE : 123123 </div>
								<div class="status-number">  </div>
							</div>
						</div>
					</div>
				</div>
				</div>
			</div>
			
            <div class="col-md-3" style="    padding-top: 10px !important;">
				
							
					<form role="form" action="{{url('home')}}" method="GET">
                        {{ csrf_field() }}

                        <div class="form-group col-md-5" >
                            
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date" placeholder="Date" value="{{$date_from}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
						<div class="form-group col-md-5 col-md-offset-2" >
                            
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date" placeholder="Date" value="{{$date_to}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
                       
						
						<div class="form-group col-md-5">
						<select class="mt-multiselect btn btn-default form-control form-filter input-sm " data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id" id="user_id">

								@foreach ($users as $user_id=>$user_name)
									<option value="{{$user_id}}" <?php if($user_id==$s_user_id) echo 'selected'; ?>>{{$user_name}}</option>
								@endforeach
							</select>
						
						</div>
						
						<div class="form-group col-md-5 col-md-offset-2">
						<select class="form-control form-filter input-sm" name="bgbu">
                                        <option value="">BG && BU</option>
										<?php 
										$bg='';
										foreach($teams as $team){ 
											$selected = '';
											if($bgbu==($team->bg.'_')) $selected = 'selected';
											
											if($bg!=$team->bg) echo '<option value="'.$team->bg.'_" '.$selected.'>'.$team->bg.'</option>';	
											$bg=$team->bg;
											$selected = '';
											if($bgbu==($team->bg.'_'.$team->bu)) $selected = 'selected';
											if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'" '.$selected.'>'.$team->bg.' - '.$team->bu.'</option>';
										} ?>
                                    </select>
						</div>	
						
						

						<div class="form-group col-md-12">
							
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
	
</div>


<div class="row">
	<div class="col-lg-12 col-xs-12 col-sm-12">
		<div class="portlet light ">
			<div class="portlet-title tabbable-line">
			<div class="caption">
				<i class=" icon-social-twitter font-dark hide"></i>
				<span class="caption-subject font-dark bold uppercase">MY Listings</span>
				
			</div>
			
			<div class="pull-right">
				<a href="{{url('star')}}">View More&gt;&gt;</a>
			</div>
			
		</div>
			<div class="portlet-body">
				
					<table class="table table-hover">
						
							<tr class="uppercase">
								<th > ASIN </th>
								<th> SKU </th>
								<th> UNITS </th>
								<th> SALES </th>
								<th> AVG.PRICE </th>
								<th >FBA</th>
								<th> OUTSTOCK DATE </th>
								<th> RATING </th>
								<th> SESSIONS <font style="color:#ff0000;"><i  class="fa fa-info-circle popovers" data-container="body" onclick=" " data-trigger="hover" data-placement="top" data-html="true" data-content="Average data from {{date('Y-m-d',strtotime('-10days'))}} to {{date('Y-m-d',strtotime('-4days'))}}"></i></font></th>
								<th> CR <font style="color:#ff0000;"><i  class="fa fa-info-circle popovers" data-container="body" onclick=" " data-trigger="hover" data-placement="top" data-html="true" data-content="Average data from {{date('Y-m-d',strtotime('-10days'))}} to {{date('Y-m-d',strtotime('-4days'))}}"></i></font></th>
								
								<th> KEYWORD RANKING </th>
								<th> BSR </th>
								<th> SKU E.VALUE </th>
								<th> SKU BONUS </th>
							</tr>
						
						
						<tr>
							
							<td>
								<a href="javascript:;" class="primary-link">1234567890</a>
							</td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td>
								<span class="bold theme-font">80%</span>
							</td>
						</tr>
						
						<tr>
							
							<td>
								<a href="javascript:;" class="primary-link">1234567890</a>
							</td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td>
								<span class="bold theme-font">80%</span>
							</td>
						</tr>
						
						<tr>
							
							<td>
								<a href="javascript:;" class="primary-link">1234567890</a>
							</td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td>
								<span class="bold theme-font">80%</span>
							</td>
						</tr>
						
						<tr>
							
							<td>
								<a href="javascript:;" class="primary-link">1234567890</a>
							</td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td>
								<span class="bold theme-font">80%</span>
							</td>
						</tr>
						
						<tr>
							
							<td>
								<a href="javascript:;" class="primary-link">1234567890</a>
							</td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td> $345 </td>
							<td> 45 </td>
							<td> 124 </td>
							<td>
								<span class="bold theme-font">80%</span>
							</td>
						</tr>
						
				</table>
				
			</div>
		</div>
	</div>
</div>			
<div class="row">					
<div class="col-lg-12 col-xs-12 col-sm-12">
	<div class="portlet light ">
		<div class="portlet-title tabbable-line">
			<div class="caption">
				<i class=" icon-social-twitter font-dark hide"></i>
				<span class="caption-subject font-dark bold uppercase">MY TASKS</span>
				
			</div>
			
					<div class="pull-right">
						<a href="{{url('task')}}">View More&gt;&gt;</a>
					</div>
		</div>
		<div class="portlet-body">
			
			<table class="table table-hover" id="tasks_list">
			
			
			<tr class="uppercase">
				<th width="2%"><font style="color:#ff0000;"><i  class="fa fa-info-circle popovers" data-container="body" onclick="" data-trigger="hover" data-placement="right" data-html="true" data-content="Click to complete the task quickly"></i></font></th>
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
				<td class="text_s"><a class="task_request primary-link" title="Task Details" href="javascript:;" id="{{$task['id']}}-request" data-pk="{{$task['id']}}-request" data-type="textarea" data-placement="right">{{$task['request']}}</a></td>
				<td class="text_s"><a class="task_response" title="Task Response" href="javascript:;" id="{{$task['id']}}-response" data-pk="{{$task['id']}}-response" data-type="textarea" data-placeholder="Your response here..." >{{$task['response']}}</a></td>
				<td><a class="task_complete_date" title="Due To" href="javascript:;" id="{{$task['id']}}-complete_date" data-pk="{{$task['id']}}-complete_date" data-type="date" data-viewformat="yyyy-mm-dd" data-placement="right">{{$task['complete_date']}}</a></td>
				<td>{{array_get($users,$task['request_user_id'])}}</td>
				<td  class="text_s">
				<a class="task_priority" title="Priority" href="javascript:;" id="{{$task['id']}}-priority" data-pk="{{$task['id']}}-priority" data-type="text" data-placement="left">{{$task['priority']}}</a>
				</td>
			</tr>
			@endforeach
			</table>
		</div>
	</div>
</div>
</div>
							

    

<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>    
<script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/form-editable.min.js" type="text/javascript"></script>
<script type="text/javascript">
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
		autoclose: true,
	});
	
	
	
	
	
	
	lineoption = {
		title : {
			text: 'Estimated Commission, paid according to actual calculation',

		},
		tooltip : {
			trigger: 'axis'
		},
		legend: {
			data:['Commission']
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
				data : ['Jan.','Feb.','Mar.','Apr.','May.','Jun.','Jul.','Aug.','Sep.','Oct.','Nov.','Dec.']
			}
		],
		yAxis : [
			{
				type : 'value'
			}
		],
		series : [
			{
				name:'Commission',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:[1123, 1221, 2211, 5451, 2620, 8301, 7101]
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
					var str='<tr id="task_'+redata.id+'"><td><input type="checkbox" class="task_quick_complete" id="'+redata.id+'" /></a></td><td class="text_s"><a class="task_request primary-link" title="Task Details" href="javascript:;" id="'+redata.id+'-request" data-pk="'+redata.id+'-request" data-type="textarea" data-placement="right">'+redata.request+'</a></td><td class="text_s"><a class="task_response" title="Task Response" href="javascript:;" id="'+redata.id+'-response" data-pk="'+redata.id+'-response" data-type="textarea" data-placeholder="Your response here..." >'+((redata.response)?redata.response:"N/A")+'</a></td><td><a class="task_complete_date" title="Due To" href="javascript:;" id="'+redata.id+'-complete_date" data-pk="'+redata.id+'-complete_date" data-type="date" data-viewformat="yyyy-mm-dd" data-placement="right">'+redata.complete_date+'</a></td><td>'+redata.request_user+'</td><td  class="text_s"><a class="task_priority" title="Priority" href="javascript:;" id="'+redata.id+'-priority" data-pk="'+redata.id+'-priority" data-type="text" data-placement="left">'+redata.priority+'</a></td></tr>';
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
