@extends('layouts.layout')

@section('content')
<style>
.widget-thumb .widget-thumb-body .widget-thumb-body-stat {font-size:20px;}
.widget-thumb .widget-thumb-wrap .widget-thumb-icon{width:50px;height:50px;line-height:30px;}
.widget-thumb .widget-thumb-heading{color:#666; margin-bottom:10px;}

</style>
<div class="row">

</div>

    <div class="row">
			
            <div class="panel panel-default">
				<div class="row widget-row">
						<div style="margin: 20px;">
							
<form role="form" action="{{url('home')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">

                        <div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_start" placeholder="Date From" value="{{$date_start}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_end" placeholder="Date To" value="{{$date_end}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
						<?php if(Auth::user()->admin){ ?>
						<div class="col-md-2">
						<select class="form-control form-filter input-sm"  name="user_id" id="user_id">
										<option value="">Select User</option>
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}" <?php if($user_id==$s_user_id) echo 'selected'; ?>>{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-2">
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

						

						
						 <div class="col-md-2">
						<select class="form-control form-filter input-sm" name="site" id="site">
									<option value="">Select Site</option>
                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}" <?php if($v==$s_site) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						
						
						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						
						
						
						
						
						
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="asin" placeholder="Asin" value ="{{array_get($_REQUEST,'asin')}}">
                                       
						</div>	
						<div class="col-md-1">
							
										<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>
					</div>

                    </form>
						</div>
					
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading">Asin Count</h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-green icon-bulb"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">&nbsp;</span>
										<span class="widget-thumb-body-stat">{{$total_asins}}</span>									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading">Avg Rating</h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-red icon-layers"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">{!!$avg_rating_change!!}</span>
										<span class="widget-thumb-body-stat">{{$avg_rating}}</span>									
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading">Negative Task</h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-purple icon-screen-desktop"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">&nbsp;</span>
										<span class="widget-thumb-body-stat">{{$ntask}}</span>		
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading">Negative Importance</h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-blue icon-bar-chart"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">&nbsp;</span>
										<span class="widget-thumb-body-stat">{{$nimp}}</span>	
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading">Negative Closed</h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-blue icon-bar-chart"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">&nbsp;</span>
										<span class="widget-thumb-body-stat">{{$ctask}}</span>	
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading">Completion ratio</h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-blue icon-bar-chart"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">&nbsp;</span>
										<span class="widget-thumb-body-stat">{{$ptask}} %</span>	
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
					</div>
					
					
					
					
					<div class="row widget-row">
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading">Review Count</h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-green icon-bulb"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">{!!$review_count_change!!}</span>
										<span class="widget-thumb-body-stat">{{$review_count}}</span>	
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading"><i class="fa fa-star"></i></h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-red icon-layers"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">{!!$one_star_number_change!!}</span>
										<span class="widget-thumb-body-stat">{{$one_star_number}}</span>	
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading"><i class="fa fa-star"></i><i class="fa fa-star"></i></h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-purple icon-screen-desktop"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">{!!$two_star_number_change!!}</span>
										<span class="widget-thumb-body-stat">{{$two_star_number}}</span>	
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-blue icon-bar-chart"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">{!!$three_star_number_change!!}</span>
										<span class="widget-thumb-body-stat">{{$three_star_number}}</span>	
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-blue icon-bar-chart"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">{!!$four_star_number_change!!}</span>
										<span class="widget-thumb-body-stat">{{$four_star_number}}</span>	
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
						
						<div class="col-md-2">
							<!-- BEGIN WIDGET THUMB -->
							<div class="widget-thumb widget-bg-color-white  margin-bottom-20 bordered">
								<h4 class="widget-thumb-heading"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></h4>
								<div class="widget-thumb-wrap">
									<i class="widget-thumb-icon bg-blue icon-bar-chart"></i>
									<div class="widget-thumb-body">
										<span class="widget-thumb-subtitle">{!!$five_star_number_change!!}</span>
										<span class="widget-thumb-body-stat">{{$five_star_number}}</span>		
									</div>
								</div>
							</div>
							<!-- END WIDGET THUMB -->
						</div>
					</div>	
                    <div id="review" style="width:100%;height:400px; margin-top:50px;"></div>
					
					
					
					<div id="step" style="width:100%;height:400px; margin-top:50px;"></div>

            </div>
    </div>


<script type="text/javascript">
	$(function() {
	// 基于准备好的dom，初始化echarts实例
		var myChart = echarts.init(document.getElementById('review'));
	
		// 指定图表的配置项和数据
		option = {
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:['Review Count','Avg Review Rating','Rating 1','Rating 2','Rating 3','Rating 4','Rating 5']
    },
    toolbox: {
        show : true,
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
            data : {!!json_encode($chat_1_x)!!} 
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : [
        {
            name:'Review Count',
            type:'line',
            data:{{json_encode($chat_1_y_reviewcount)}}
        },
        {
            name:'Avg Review Rating',
            type:'line',

            data:{{json_encode($chat_1_y_reviewrating)}}
        },
        {
            name:'Rating 1',
            type:'line',

            data:{{json_encode($chat_2_y_1)}}
        },
        {
            name:'Rating 2',
            type:'line',

            data:{{json_encode($chat_2_y_2)}}
        },
        {
            name:'Rating 3',
            type:'line',

            data:{{json_encode($chat_2_y_3)}}
        },
        {
            name:'Rating 4',
            type:'line',

            data:{{json_encode($chat_2_y_4)}}
        },
        {
            name:'Rating 5',
            type:'line',

            data:{{json_encode($chat_2_y_5)}}
        }
    ]
};
	
		// 使用刚指定的配置项和数据显示图表。
		myChart.setOption(option);
		
		
				
		
		
		
		
		var myChart2 = echarts.init(document.getElementById('step'));
	
		// 指定图表的配置项和数据
		option2 = {
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:{!!json_encode($follow_status_array)!!}
    },
    toolbox: {
        show : true,
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
            data : {!!json_encode($chat_2_x)!!}
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : {!!json_encode($char_2_y_arr)!!}

};
	
		// 使用刚指定的配置项和数据显示图表。
		myChart2.setOption(option2);
		$('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
	});
</script>
@endsection
