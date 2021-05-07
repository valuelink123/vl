@extends('layouts.layout')
@section('label', 'Daily Sales Report')
@section('content')
<link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
<style>
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;
	}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 10px 2px;
}
th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
.progress-bar.green-sharp,.progress-bar.red-haze,.progress-bar.blue-sharp{
color:#000 !important;
}
table{ 
table-layout:fixed; 
}
td.strategy_s,td.keyword_s,td.ranking_s{       
text-overflow:ellipsis; 
-moz-text-overflow: ellipsis; 
overflow:hidden; 
white-space: nowrap;      
}  
.table-head{padding-right:17px;background-color:#f3f4f6;color:#000;}
.table-body{width:100%; max-height:550px;overflow-y:scroll;}
.table-head table,.table-body table{width:100%;}
table .head{ 
text-align:center;
vertical-align:middle;
background:#fff2cc;
font-weight:bold;
}
.table{margin-bottom:0px;}
.widget-thumb .widget-thumb-body .widget-thumb-body-stat {font-size:20px;}
.widget-thumb .widget-thumb-wrap .widget-thumb-icon{width:50px;height:50px;line-height:30px;}
.widget-thumb .widget-thumb-heading{color:#666; margin-bottom:10px;}
.dashboard-stat2 { margin-bottom:0px;margin-top: 8px;}
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

.editable-empty, .editable-empty:hover, .editable-empty:focus {
    font-style:normal;
    color: #337ab7;
}

.table-container a {
    text-decoration: none;
    border-bottom: dashed 1px #0088cc;
}
.modal-dialog{
	width:1000px;
}
    </style>
    <h1 class="page-title font-red-intense"> Daily Sales Report
        
    </h1>
	
	
	<div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
							
							
					<div class="table-toolbar">
                    <form role="form" action="{{url('skus')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">

                        <div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control " readonly name="date_start" placeholder="Date" value="{{$date_start}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
						
						<div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" readonly name="date_end" placeholder="Date" value="{{$date_end}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
                       
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]">
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}" <?php if(in_array($user_id,$s_user_id)) echo 'selected'; ?>>{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-1">
						<select class="form-control " name="bgbu">
                                        <option value="">Select BGBU</option>
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
						

						 <div class="col-md-1">
						<select class="form-control " name="site" id="site">
									<option value="">Select Site</option>
                                        @foreach (getSiteCode() as $k=>$v)
                                            <option value="{{$v}}" <?php if($v==$s_site) echo 'selected'; ?>>{{$k}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						 <div class="col-md-1">
						<select class="form-control " name="level" id="level">
									<option value="">Level</option>
										<option value="S" <?php if('S'==$s_level) echo 'selected'; ?>>S</option>
                                        <option value="A" <?php if('A'==$s_level) echo 'selected'; ?>>A</option>
										<option value="B" <?php if('B'==$s_level) echo 'selected'; ?>>B</option>
										<option value="C" <?php if('C'==$s_level) echo 'selected'; ?>>C</option>
										<option value="D" <?php if('D'==$s_level) echo 'selected'; ?>>D</option>
                                    </select>
						</div>
						
						<div class="col-md-1">
						<input type="text" class="form-control " name="sku" placeholder="SKU OR ASIN" value ="{{array_get($_REQUEST,'sku')}}">
                                       
						</div>
						<div class="col-md-1">
							
										<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>
						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						
						
						
						
						
						
						
							
						
					</div>

                    </form>
					

					
						<div class="row">
						<div class="col-md-12">
							<input id="importFile" name="importFile" type="file" style="display:none">
							{{ csrf_field() }}
							<input id="importFileTxt" name="importFileTxt" type="text" class="form-control input-inline">
							<a id="importButton" class="btn red input-inline" >Browse</a>

							<button id="importSubmit" class="btn blue input-inline">Upload</button>
		
							<a href="{{ url('/uploads/dailyReport/dailyReport.xls')}}" class="help-inline" style="margin-top:8px;margin-left:10px;">Template </a>

							@permission('sales-report-export')
							<button id="vl_list_export" class="btn blue input-inline"> Export
								<i class="fa fa-download"></i>
							</button>
							@endpermission
						</div>
						</div>
						
                </div>

				
                    <div class="table-container">
					{{ $datas->appends(['date_start' => $date_start,'date_end' => $date_end,'site' => $s_site,'user_id' => $s_user_id,'level' => $s_level,'bgbu' => $bgbu,'sku' => $sku])->links() }} 
					
					
					@foreach ($datas as $data)
						<div class="table-head">
						<table class="table table-bordered ">
 
						 <colgroup>
			<col width="8%"></col>
			<col width="10%"></col>
			<col width="8%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="7%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="7%"></col>
			<col width="10%"></col>
			</colgroup>	
																	
						  <tr class="head">
							<td style="font-weight: bold;">ASIN</td>
							<td style="font-weight: bold;">Site</td>
							<td style="font-weight: bold;">SKU</td>
							<td style="font-weight: bold;">Status</td>
							<td style="font-weight: bold;">Level</td>
							<td style="font-weight: bold;">BG</td>
							<td style="font-weight: bold;">BU</td>
							<td style="font-weight: bold;">Seller</td>
							<td colspan="4" style="font-weight: bold;"> Last Main Keywords </td>
							<td colspan="4" style="font-weight: bold;">Description</td>
						  </tr>
						  <tr>
							<td style="word-wrap: break-word;"><a href="https://{{array_get(getSiteUrl(),$data->marketplace_id)}}/dp/{{strip_tags(str_replace('&nbsp;','',$data->asin))}}" target="_blank">{{strip_tags(str_replace('&nbsp;','',$data->asin))}}</a></td>
							<td>{{strtoupper(substr(strrchr(array_get(getSiteUrl(),$data->marketplace_id), '.'), 1))}}</td>
							<td>{!!str_replace(',','<br />',$data->sku)!!}</td>
							<td>{!!array_get(getSkuStatuses(),$data->status)!!}</td>
							<td>{{((($data->pro_status) === '0')?'S':$data->pro_status)}}</td>
							
							<td >{{$data->bg}}</td>
							<td >{{$data->bu}}</td>
							<td > {{array_get($users,$data->sap_seller_id,$data->sap_seller_id)}} </td>
							<td colspan="4" class="keyword_s"><a data-target="#ajax" data-toggle="modal" id ="{{$data->asin.$data->marketplace_id.'keywords'}}" href="{{url('skus/keywords/?asin='.$data->asin.'&marketplace_id='.$data->marketplace_id.'&date='.$curr_date)}}"> 
							@if(empty(json_decode($data->last_keywords,true)))
								N/A		
							@else
								@foreach(json_decode($data->last_keywords,true) as $k => $v)						
								{{$k}},&nbsp;&nbsp;
								@endforeach
							@endif
							</a></td>
							
							
							<td colspan="4">{!!str_replace(',','<br />',$data->description)!!}</td>
						  </tr>
						  
						  
						  
						  </table>
						  </div>
	
						  <div class="table-head">
						  <table class="table table-bordered">
						  <colgroup>
						  <col width="8%"></col>
			<col width="10%"></col>
			<col width="8%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="7%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="7%"></col>
			<col width="10%"></col>
			</colgroup>	
			<tr class="head">
							<td style="font-weight: bold;">Date</td>
							<td style="font-weight: bold;">Rank</td>
							<td style="font-weight: bold;">CategoryRank</td>
							<td style="font-weight: bold;">Rating</td>
							<td style="font-weight: bold;">Reviews</td>
							<td style="font-weight: bold;">Sales</td>
							<td style="font-weight: bold;">Price</td>
							<td style="font-weight: bold;">Sessions</td>
							<td style="font-weight: bold;">Conversion %</td>
							<td style="font-weight: bold;">FBA</td>
							<td style="font-weight: bold;">FBA Tran</td>
							<td style="font-weight: bold;">FBM</td>
							<td style="font-weight: bold;">Total</td>
							<td style="font-weight: bold;">FBA Keep</td>
							<td style="font-weight: bold;">Total Keep</td>
							<td style="font-weight: bold;">Strategy</td>
						  </tr>
						  </table>
						  </div>
						  <div class="table-body">
						  <table class="table table-bordered">
						  <colgroup>
						  <col width="8%"></col>
			<col width="10%"></col>
			<col width="8%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="7%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="5%"></col>
			<col width="7%"></col>
			<col width="10%"></col>
			</colgroup>	
						<?php 
						$sales_data = $price_data = [];
						$sales_sum = $price_sum =0;
						$week_end=date('Y-m-d',strtotime($date_start));
						$week=date('Y-m-d',strtotime($date_end));
						$lineNum=0;
						while($week>=$week_end){
						$lineNum++;
						$details = $data->details;
						if(isset($details[$week]['sales'])){
							$sales_data[] = intval($details[$week]['sales']);
							$sales_sum+=intval($details[$week]['sales']);
						}
						if(isset($details[$week]['price'])){
							$price_data[]  = round($details[$week]['price'],2);
							$price_sum+=round($details[$week]['price'],2);
						}
						$key = $data->marketplace_id.':'.$data->asin.':'.$week;
						?>
						  <tr <?php echo ($lineNum%2==0)?'style="background:#ccc;"':''?>>
						  	<td>{{$week}}</td>
							<td>
							<a data-target="#ajax" data-toggle="modal" id ="{{$data->asin.$data->marketplace_id.$week.'ranks'}}" href="{{url('skus/keywords/?asin='.$data->asin.'&marketplace_id='.$data->marketplace_id.'&date='.$week)}}">
							@if(empty(json_decode(array_get($data->details,$week.'.keywords'),true)))
								N/A		
							@else
								@foreach(json_decode(array_get($data->details,$week.'.keywords'),true) as $k => $v)						
								{{$v}} in {{$k}}<BR>
								@endforeach
							@endif
							</a>
							</td>
							<td>
							@if(empty(json_decode(array_get($data->details,$week.'.ranking'),true)))
								N/A		
							@else
								@foreach(json_decode(array_get($data->details,$week.'.ranking'),true) as $k => $v)						
								{{$v}} in {{$k}}<BR>
								@endforeach
							@endif
							</td>
							
							<td>{{round(array_get($data->details,$week.'.rating'),1)}}</td>
							
					
							<td>{{intval(array_get($data->details,$week.'.review'))}}</td>
							
						 
							<td>{{intval(array_get($data->details,$week.'.sales'))}}</td>
							
							<td>{{round(array_get($data->details,$week.'.price'),2)}}</td>
							
							<td><a class="sku_flow" href="javascript:;" id="{{$key}}:flow" data-pk="{{$key}}:flow" data-type="text"> {{array_get($data->details,$week.'.flow')}} </td>
						
							<td><a class="sku_conversion" href="javascript:;" id="{{$key}}:conversion" data-pk="{{$key}}:conversion" data-type="text" title="转化率%" > {{round(array_get($data->details,$week.'.conversion')*100,2)}} </td>
							
							<td>{{intval(array_get($data->details,$week.'.fba_stock'))}} </td>
							
							<td>{{intval(array_get($data->details,$week.'.fba_transfer'))}} </td>
							
							<td>{{intval(array_get($data->details,$week.'.fbm_stock'))}}</td>
							
							<td>{!!intval(array_get($data->details,$week.'.fba_stock',0)+array_get($data->details,$week.'.fbm_stock',0)+array_get($data->details,$week.'.fba_transfer',0))!!} </td>
							
							<td>{!!(array_get($data->details,$week.'.sales',0)!=0)?round(intval(array_get($data->details,$week.'.fba_stock',0))/(array_get($data->details,$week.'.sales',0)),2):'∞'!!} </td>
							
							<td>{!!(array_get($data->details,$week.'.sales',0)!=0)?round((intval(array_get($data->details,$week.'.fba_stock',0))+intval(array_get($data->details,$week.'.fbm_stock',0))+intval(array_get($data->details,$week.'.fba_transfer',0)))/(array_get($data->details,$week.'.sales',0)),2):'∞'!!}</td>
							
							<td class="strategy_s"><a class="sku_strategy" title="{{array_get($data->details,$key.'.strategy')}}" href="javascript:;" id="{{$key}}:strategy" data-placement="left"  data-pk="{{$key}}:strategy" data-type="text"> {{array_get($data->details,$week.'.strategy')}} </a></td>
							
						  </tr>
						  <?php
						  		$week = date('Y-m-d',strtotime($week)-86400);
							}
							?>
						  </table>
						</div>
						
                        @endforeach
						
                               {{ $datas->appends(['date_start' => $date_start,'date_end' => $date_end,'site' => $s_site,'user_id' => $s_user_id,'level' => $s_level,'bgbu' => $bgbu,'sku' => $sku])->links() }}   



                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>

	
<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>    
<script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/form-editable.min.js" type="text/javascript"></script>
<script>


jQuery(document).ready(function() {
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		orientation: 'bottom',
		autoclose: true
	});
});



var FormEditable = function() {

    $.mockjaxSettings.responseTime = 500;

    var initAjaxMock = function() {
        $.mockjax({
            url: '/skus',
			type:'post',
            response: function(settings) {
				console.log(this);
				console.log(settings);
            }
        });
    }

    var initEditables = function() {
        $.fn.editable.defaults.inputclass = 'form-control';
        $.fn.editable.defaults.url = '/skus';
		
		$('.sku_strategy').editable({
			emptytext:'N/A'
		});

		$('.sku_flow,.sku_conversion').editable({
			emptytext:'N/A',
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

    return {
        //main function to initiate the module
        init: function() {

            // inii ajax simulation
            //initAjaxMock();
            // init editable elements
            initEditables();

        }
    };

}();

jQuery(document).ready(function() {
	$("#importButton,#importFileTxt").click(function(){
		$("#importFile").trigger("click");
	});

	$('input[id=importFile]').change(function() {
		$('#importFileTxt').val($(this).val());
	});

	$("#importSubmit").click(function () {
		var fileObj = document.getElementById("importFile").files[0];
		if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
			alert("Please Select File!");
			return false;
		}
		var formFile = new FormData();
		formFile.append("file", fileObj);
		var data = formFile;
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			url: "/skus/upload",
			data: data,
			type: "Post",
			dataType: "json",
			cache: false,
			processData: false,
			contentType: false,
			success: function (result) {
				var html = '<table class="table table-bordered"><tr><td>Asin</td><td>Site</td><td>Date</td><td>Rank</td><td>Sessions</td><td>Conversion%</td><td>Strategy</td><td>Keywords</td></tr>';
				for(var item in result.updateData){
					var row = result.updateData[item];
					var keywords = '';
					if(typeof(row['keywords'])!="undefined"){
						for(var key in row['keywords']){
							keywords += row['keywords'][key]+' in '+key+'<BR>';
						}
					}
					
					html += '<tr><td width="10%">'+((typeof(row['asin'])=="undefined")?'':row['asin'])+
						'</td><td width="10%">'+((typeof(row['site'])=="undefined")?'':row['site'])+
						'</td><td width="10%">'+((typeof(row['date'])=="undefined")?'':row['date'])+
						'</td><td width="10%">'+((typeof(row['ranking'])=="undefined")?'':row['ranking'])+
						'</td><td width="10%">'+((typeof(row['flow'])=="undefined")?'':row['flow'])+
						'</td><td width="10%">'+((typeof(row['conversion'])=="undefined")?'':row['conversion'])+
						'</td><td width="20%">'+((typeof(row['strategy'])=="undefined")?'':row['strategy'])+
						'</td><td width="20%">'+keywords+'</td></tr>';
				}
				html += '</table>';
				if(result.customActionStatus=='OK'){  
					bootbox.dialog({
						message: html,
						title: "Confirm Update Data",
						buttons: {
							Cancel: {
								label: "Cancel",
								className: "btn-default",
								callback: function () {
									
								}
							}
							, OK: {
								label: "OK",
								className: "btn-primary",
								callback: function () {
									toastr.success('Processing...');
									$.ajaxSetup({
										headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
									});
									$.ajax({
										type: "POST",
										dataType: "json",
										url: "{{ url('skus/batchUpdate') }}",
										data: result.updateData,
										success: function (updateResult) {
											if(updateResult.customActionStatus=='OK'){  
												toastr.success(updateResult.customActionMessage);
												location.reload();
											}else{
												toastr.error(updateResult.customActionMessage);
											}
										},
										error: function(updateResult) {
											toastr.error(updateResult.responseText);
										}
									});
								}
							}
						}
					});
				}else{
					toastr.error(data.customActionMessage);
				}
			},
			error: function(result) {
                toastr.error(result.responseText);
			}
		});
	});

    FormEditable.init();
	
	$("#vl_list_export").click(function(){
		location.href='/dreportexport?sku='+$("input[name='sku']").val()+'&date_start='+$("input[name='date_start']").val()+'&date_end='+$("input[name='date_end']").val()+'&user_id='+(($("select[name='user_id[]']").val())?$("select[name='user_id[]']").val():'')+'&bgbu='+$("select[name='bgbu']").val()+'&site='+$('select[name="site"]').val()+'&level='+$('select[name="level"]').val();
	});

	$('#ajax').on('hidden.bs.modal', function (e) {
        $('#ajax .modal-content').html('<div class="modal-body" >Loading...</div>');
    });
});
</script>

<div class="modal fade" id="ajax" role="basic" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content" >
			<div class="modal-body" >
				Loading...
			</div>
		</div>
	</div>
</div>
@endsection
