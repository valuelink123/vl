@extends('layouts.layout')

@section('content')
<style>
.widget-thumb .widget-thumb-body .widget-thumb-body-stat {font-size:20px;}
.widget-thumb .widget-thumb-wrap .widget-thumb-icon{width:50px;height:50px;line-height:30px;}
.widget-thumb .widget-thumb-heading{color:#666; margin-bottom:10px;}

</style>
<div class="row" >
<div class="col-lg-12 col-xs-12 col-sm-12">
<div class="portlet light ">
			<div class="col-md-3">
				<div id="pieChart" style="width:300px;height:300px;"></div>
			</div>
			
			<div class="col-md-6">
				<div id="lineChart" style="width:100%;height:300px;"></div>
			</div>
            <div class="col-md-3">
				
							
					<form role="form" action="{{url('home')}}" method="GET">
                        {{ csrf_field() }}

                        <div class="form-group col-md-12" style="margin-top:50px;">
                            
                            <div class="input-group date date-picker" data-date-format="yyyy-mm">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date" placeholder="Date" value="{{$date}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
                       
						<?php if(Auth::user()->admin){ ?>
						<div class="form-group col-md-12">
						<select class="form-control form-filter input-sm"  name="user_id" id="user_id">
										<option value="">Select User</option>
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}" <?php if($user_id==$s_user_id) echo 'selected'; ?>>{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="form-group col-md-12">
						<select class="form-control form-filter input-sm" name="bgbu">
                                        <option value="">Select BG && BU</option>
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
						<?php } ?>
						

						<div class="form-group col-md-12">
							
							<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>

                    </form>
			</div>
			<div style="clear:both;"></div>
			</div>
			</div>
</div>
<div class="row">
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
				<div class="icon">
					<i class="icon-pie-chart"></i>
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
				<div class="icon">
					<i class="icon-like"></i>
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
				<div class="icon">
					<i class="icon-basket"></i>
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
				<div class="icon">
					<i class="icon-user"></i>
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
				<div class="icon">
					<i class="icon-pie-chart"></i>
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


<div class="row">
	<div class="col-lg-12 col-xs-12 col-sm-12">
		<div class="portlet light ">
			<div class="portlet-title">
				<div class="caption caption-md">
					<i class="icon-bar-chart font-dark hide"></i>
					<span class="caption-subject font-dark bold uppercase">My Listings</span>
					<span class="caption-helper"></span>
				</div>
				<div class="actions">
					<div class="btn-group btn-group-devided" data-toggle="buttons">
						<button type="button" class="btn btn-circle btn-outline green btn-sm">View More>></button>
					</div>
				</div>
			</div>
			<div class="portlet-body">
				
				<div class="table-scrollable table-scrollable-borderless">
					<table class="table table-hover table-light">
						<thead>
							<tr class="uppercase">
								<th > ASIN </th>
								<th> SKU </th>
								<th> UNITS </th>
								<th> SALES </th>
								<th> AVG.PRICE </th>
								<th > FBA </th>
								<th> OUTSTOCK DATE </th>
								<th> RATING </th>
								<th> SESSIONS </th>
								<th> CONVERSION RATE </th>
								
								<th> KEYWORD RANKING </th>
								<th> BSR </th>
								<th> SKU E.VALUE </th>
								<th> SKU BONUS </th>
							</tr>
						</thead>
						<tbody>
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
						
					</tbody></table>
				</div>
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
			<div class="actions">
					<div class="btn-group btn-group-devided" data-toggle="buttons">
						<button type="button" class="btn btn-circle btn-outline green btn-sm">View More>></button>
					</div>
				</div>
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#tab_actions_new" data-toggle="tab" aria-expanded="true"> New </a>
				</li>
				<li class="">
					<a href="#tab_actions_open" data-toggle="tab" aria-expanded="false"> Opening </a>
				</li>
				<li class="">
					<a href="#tab_actions_under" data-toggle="tab" aria-expanded="false"> Under Review </a>
				</li>
				<li class="">
					<a href="#tab_actions_finish" data-toggle="tab" aria-expanded="false"> Finished </a>
				</li>
				
			</ul>
		</div>
		<div class="portlet-body">
			<div class="tab-content">
				<div class="tab-pane active" id="tab_actions_new">
					<!-- BEGIN: Actions -->
					<div class="mt-actions">
						<div class="mt-action">
							<div class="mt-action-body">
								<div class="mt-action-row">
									<div class="mt-action-info ">
										<div class="mt-action-details ">
											<span class="mt-action-author">Listing优化 </span>
											<p class="mt-action-desc">Dummy text of the printing</p>
										</div>
									</div>
									<div class="mt-action-datetime ">
										<span class="mt-action-date"> Natasha Kim </span>
										<span class="mt-action-dot bg-green"></span>
										<span class="mt=action-time">2019-08-31</span>
									</div>
									<div class="mt-action-buttons ">
										<div class="btn-group btn-group-circle">
											<button type="button" class="btn btn-outline green btn-sm">Process</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="mt-action">
							<div class="mt-action-body">
								<div class="mt-action-row">
									<div class="mt-action-info ">
										<div class="mt-action-details ">
											<span class="mt-action-author">Listing优化</span>
											<p class="mt-action-desc">Dummy text of the printing</p>
										</div>
									</div>
									<div class="mt-action-datetime ">
										<span class="mt-action-date">Natasha Kim</span>
										<span class="mt-action-dot bg-red"></span>
										<span class="mt=action-time">2019-08-31</span>
									</div>
									<div class="mt-action-buttons ">
										<div class="btn-group btn-group-circle">
											<button type="button" class="btn btn-outline green btn-sm">Process</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="mt-action">
							<div class="mt-action-body">
								<div class="mt-action-row">
									<div class="mt-action-info ">
										<div class="mt-action-details ">
											<span class="mt-action-author">Listing优化</span>
											<p class="mt-action-desc">Dummy text of the printing</p>
										</div>
									</div>
									<div class="mt-action-datetime ">
										<span class="mt-action-date">Natasha Kim</span>
										<span class="mt-action-dot bg-green"></span>
										<span class="mt=action-time">2019-08-31</span>
									</div>
									<div class="mt-action-buttons ">
										<div class="btn-group btn-group-circle">
											<button type="button" class="btn btn-outline green btn-sm">Process</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="mt-action">
							<div class="mt-action-body">
								<div class="mt-action-row">
									<div class="mt-action-info ">
										<div class="mt-action-details ">
											<span class="mt-action-author">Listing优化</span>
											<p class="mt-action-desc">Dummy text of the printing</p>
										</div>
									</div>
									<div class="mt-action-datetime ">
										<span class="mt-action-date">Natasha Kim</span>
										<span class="mt-action-dot bg-green"></span>
										<span class="mt=action-time">2019-08-31</span>
									</div>
									<div class="mt-action-buttons ">
										<div class="btn-group btn-group-circle">
											<button type="button" class="btn btn-outline green btn-sm">Process</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="mt-action">
							<div class="mt-action-body">
								<div class="mt-action-row">
									<div class="mt-action-info ">
										<div class="mt-action-details ">
											<span class="mt-action-author">Listing优化</span>
											<p class="mt-action-desc">Dummy text of the printing</p>
										</div>
									</div>
									<div class="mt-action-datetime ">
										<span class="mt-action-date">Natasha Kim</span>
										<span class="mt-action-dot bg-green"></span>
										<span class="mt=action-time">2019-08-31</span>
									</div>
									<div class="mt-action-buttons ">
										<div class="btn-group btn-group-circle">
											<button type="button" class="btn btn-outline green btn-sm">Process</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- END: Actions -->
				</div>
				<div class="tab-pane " id="tab_actions_open">
					
				</div>
				<div class="tab-pane " id="tab_actions_under">
					
				</div>
				<div class="tab-pane " id="tab_actions_finish">
					
				</div>
			</div>
		</div>
	</div>
</div>
</div>
							

    


<script type="text/javascript">
$(function() {
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		format: 'yyyy-mm',
		weekStart: 1,
		autoclose: true,
		startView: 2,
		maxViewMode: 1,
		minViewMode:1,
		forceParse: false,
		language: 'zh-CN'
	});
	
	
	var dataStyle = {
		normal: {
			label: {show:false},
			labelLine: {show:false}
		}
	};
	var placeHolderStyle = {
		normal : {
			color: 'rgba(0,0,0,0)',
			label: {show:false},
			labelLine: {show:false}
		},
		emphasis : {
			color: 'rgba(0,0,0,0)'
		}
	};
	pieoption = {
		title: {
			text: '2222¥',
			subtext: '销售提成',
			x: 'center',
			y: 'center',
			itemGap: 20,
			textStyle : {
				color : 'rgba(30,144,255,0.8)',
				fontFamily : '微软雅黑',
				fontSize : 35,
				fontWeight : 'bolder'
			}
		},
	
		series : [
			{
				name:'1',
				type:'pie',
				clockWise:false,
				radius : [80, 100],
				itemStyle : dataStyle,
				data:[
					{
						value:100,
					},
					{
						value:0,
					}
				]
			}
		]
	};
	var pieChart = echarts.init(document.getElementById('pieChart'));
	pieChart.setOption(pieoption,true);
	
	
	
	lineoption = {
		title : {
			text: '年度提成曲线图',
			x: 'center',
			subtext: '2019年'
		},
		tooltip : {
			trigger: 'axis'
		},
		legend: {
			data:['提成']
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
				name:'提成金额',
				type:'line',
				smooth:true,
				itemStyle: {normal: {areaStyle: {type: 'default'}}},
				data:[1123, 1221, 2211, 5451, 2620, 8301, 7101]
			}
		]
	};
	
	var lineChart = echarts.init(document.getElementById('lineChart'));
	lineChart.setOption(lineoption,true);
                    
});
</script>
@endsection
