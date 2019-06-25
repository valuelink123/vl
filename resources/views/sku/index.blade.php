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
td.strategy_s,td.keyword_s{       
text-overflow:ellipsis; 
-moz-text-overflow: ellipsis; 
overflow:hidden;      
white-space: nowrap;      
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
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_start" placeholder="Date" value="{{$date_start}}">
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
						
						<div class="col-md-2">
						<select class="form-control form-filter input-sm" name="bgbu">
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
						

						 <div class="col-md-2">
						<select class="form-control form-filter input-sm" name="site" id="site">
									<option value="">Select Site</option>
                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}" <?php if($v==$s_site) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						 <div class="col-md-1">
						<select class="form-control form-filter input-sm" name="level" id="level">
									<option value="">Level</option>
										<option value="S" <?php if('S'==$s_level) echo 'selected'; ?>>S</option>
                                        <option value="A" <?php if('A'==$s_level) echo 'selected'; ?>>A</option>
										<option value="B" <?php if('B'==$s_level) echo 'selected'; ?>>B</option>
										<option value="C" <?php if('C'==$s_level) echo 'selected'; ?>>C</option>
										<option value="D" <?php if('D'==$s_level) echo 'selected'; ?>>D</option>
                                    </select>
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="sku" placeholder="SKU OR ASIN" value ="{{array_get($_REQUEST,'sku')}}">
                                       
						</div>
						<div class="col-md-1">
							
										<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>
						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						
						
						
						
						
						
						
							
						
					</div>

                    </form>
					@permission('sales-report-export')
					<button id="vl_list_export" class="btn sbold blue"> Export
                                    <i class="fa fa-download"></i>
                          </button>
						  @endpermission
                </div>
                    <div class="table-container">
					{{ $datas->appends(['date_start' => $date_start,'site' => $s_site,'user_id' => $s_user_id,'level' => $s_level,'bgbu' => $bgbu,'sku' => $sku])->links() }} 
					
					<?php
						$tmp_time_s = date('Ym',strtotime($date_start));
						$tmp_time_c = date('Ym',strtotime('+ 8hours'));
						if($tmp_time_s > $tmp_time_c) $time_rate=0;
						if($tmp_time_s < $tmp_time_c) $time_rate=1;
						if($tmp_time_s == $tmp_time_c){
							$time_rate=round(date('j',strtotime('+ 8hours'))/date('t',strtotime('+ 8hours')),2);
						} 
					?>
					@foreach ($datas as $data)
						<table id="user" class="table table-striped table-bordered table-hover">
 
						 <colgroup>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="4%"></col>
			<col width="4%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="6%"></col>
			<col width="20%"></col>
			</colgroup>	
																	
						<?php 
						$curr_date = date('Ymd',strtotime($date_start));
						$d_number = (date('w',strtotime($date_start))==0)?6:(date('w',strtotime($date_start))-1);
						
						$target_sold = round(array_get($oa_data,str_replace('.','',$data->site).'-'.$data->item_code.'.xiaol'.date('n',strtotime($date_start)),0),2);
						if($target_sold>0){
							$complete_sold = round(array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VV001',0)/$target_sold*100,2);
						}elseif($target_sold<0){
							$complete_sold = round((2-array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VV001',0)/$target_sold)*100,2);
						}else{
							$complete_sold =0;
						}
						
						$target_sales = round(array_get($oa_data,str_replace('.','',$data->site).'-'.$data->item_code.'.xiaose'.date('n',strtotime($date_start)),0),2);
						if($target_sales>0){
							$complete_sales = round(array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VSRHJ',0)/$target_sales*100,2);
						}elseif($target_sales<0){
							$complete_sales = round((2-array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VSRHJ',0)/$target_sales)*100,2);
						}else{
							$complete_sales =0;
						}
						
						
						$target_pro = round(array_get($oa_data,str_replace('.','',$data->site).'-'.$data->item_code.'.yewlr'.date('n',strtotime($date_start)),0),2);
						if($target_pro>0){
							$complete_pro = round(array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VVVVV',0)/$target_pro*100,2);
						}elseif($target_pro<0){
							$complete_pro = round((2-array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VVVVV',0)/$target_pro)*100,2);
						}else{
							$complete_pro =0;
						}
						?>
							<tr >
							<td width="5%" style="font-weight: bold;">SKU</td>
							<td width="5%"  style="font-weight: bold;">Seller</td>
							<td width="5%" style="font-weight: bold;">BG</td>
							<td width="5%" style="font-weight: bold;">BU</td>
							<td colspan="2" width="12%" style="font-weight: bold;">Link</td>
							<td width="6%" style="font-weight: bold;">Site</td>
							<td colspan="3" width="18%" style="font-weight: bold;"> Main Keywords </td>
							<td width="6%" style="font-weight: bold;">Status</td>
							<td width="6%" style="font-weight: bold;">Level</td>
							<td colspan="3"width="32%" style="font-weight: bold;">Description</td>
						  </tr>
						  <tr>
							<td rowspan="16" style="word-wrap: break-word;">{{$data->item_code}}</td>
							<td rowspan="16"> {{array_get($users,$data->sap_seller_id,$data->sap_seller_id)}} </td>
							<td rowspan="16">{{$data->bg}}</td>
							<td rowspan="16">{{$data->bu}}</td>
							<td colspan="2"><a href="https://{{$data->site}}/dp/{{strip_tags(str_replace('&nbsp;','',$data->asin))}}" target="_blank">{{strip_tags(str_replace('&nbsp;','',$data->asin))}}</a></td>
							<td>{{strtoupper(substr(strrchr($data->site, '.'), 1))}}</td>
							<td colspan="3" class="keyword_s"><a class="sku_keywords" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$curr_date}}-keywords" data-pk="{{$data->site.'-'.$data->asin.'-'.$curr_date}}-keywords" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$curr_date.'.keywords')?array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$curr_date.'.keywords'):array_get($last_keywords,str_replace('.','',$data->site).'-'.$data->asin)}} </a></td>
							<td>{!!($data->status)?'<span class="btn btn-success btn-xs">Reserved</span>':'<span class="btn btn-danger btn-xs">Eliminate</span>'!!}</td>
							<td>{{((($data->pro_status) === '0')?'S':$data->pro_status)}}</td>
							<td colspan="3">{{$data->item_name}}</td>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Index </td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'background:#ddeef7;':'');
							?>

							<td width="6%" style="font-weight: bold;{!!$style!!}">{{date('Y-m-d',strtotime($date_start)+(-($i)*3600*24))}}</td>
							<?php
							}
							?>
							<td width="20%" rowspan="5" >
							<div class="display">
								<div class="number">
									<h3 class="font-blue-sharp">
										<span>Sold Qty</span>
									</h3>
								</div>
							</div>
							<div class="progress-info row">
								<div class="col-md-3"> Target </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: 100%;" class="progress-bar progress-bar-success blue-sharp">
										{{$target_sold}}
									</span>
								</div>
								</div>
								<div class="col-md-3"></div>
								<div class="clearfix"></div>
							</div>
							
							<div class="progress-info row">
								<div class="col-md-3"> Time </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: {{$time_rate*100}}%;" class="progress-bar progress-bar-success green-sharp">
										{{$time_rate*100}}%
									</span>
								</div>
								</div>
								<div class="col-md-3" style="padding:0"> {{$time_rate*100}}% </div>
								<div class="clearfix"></div>
							</div>
							
							<div class="progress-info row">
								<div class="col-md-3"> Completed </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: {{($complete_sold>100)?100:$complete_sold}}%;" class="progress-bar progress-bar-success red-haze">
										{{array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VV001',0)}}
									</span>
								</div>
								</div>
								<div class="col-md-3" style="padding:0"> {{$complete_sold}}% </div>
								<div class="clearfix"></div>
							</div>
							</td>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Ranking</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_ranking" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-ranking" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-ranking" data-type="text">{{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.ranking')}} </a></td>
							<?php
							}
							?>
							
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Rating</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_rating" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-rating" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-rating" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.rating')}} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Review</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_review" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-review" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-review" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.review')}}</a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Sales</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_sales" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-sales" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-sales" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.sales')}}</a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Price</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_price" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-price" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-price" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.price')}}</a></td>
							<?php
							}
							?>
							<td rowspan="5">
							<div class="display">
								<div class="number">
									<h3 class="font-blue-sharp">
										<span>Sales Amount</span>
									</h3>
								</div>
							</div>
							<div class="progress-info row">
								<div class="col-md-3"> Target </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: 100%;" class="progress-bar progress-bar-success blue-sharp">
										{{array_get($oa_data,str_replace('.','',$data->site).'-'.$data->item_code.'.xiaose'.date('n',strtotime($date_start)),0)}}
									</span>
								</div>
								</div>
								<div class="col-md-3"></div>
								<div class="clearfix"></div>
							</div>
							
							<div class="progress-info row">
								<div class="col-md-3"> Time </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: {{$time_rate*100}}%;" class="progress-bar progress-bar-success green-sharp">
										{{$time_rate*100}}%
									</span>
								</div>
								</div>
								<div class="col-md-3" style="padding:0"> {{$time_rate*100}}% </div>
								<div class="clearfix"></div>
							</div>
							
							<div class="progress-info row">
								<div class="col-md-3"> Completed </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: {{($complete_sales>100)?100:$complete_sales}}%;" class="progress-bar progress-bar-success red-haze">
										{{array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VSRHJ',0)}}
									</span>
								</div>
								</div>
								<div class="col-md-3" style="padding:0"> {{$complete_sales}}% </div>
								<div class="clearfix"></div>
							</div>
							</td>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Session</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_flow" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-flow" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-flow" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.flow')}} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Conversion</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_conversion" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-conversion" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-conversion" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.conversion')}} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td  rowspan="6" width="6%" style="font-weight: bold;">Stock</td>
							<td  style="font-weight: bold;">FBA</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_fba_stock" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_stock" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_stock" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fba_stock')}} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">FBA Tran </td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_fba_transfer" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_transfer" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_transfer" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fba_transfer')}} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">FBM</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><a class="sku_fbm_stock" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fbm_stock" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fbm_stock" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fbm_stock')}} </a></td>
							<?php
							}
							?>
							<td rowspan="5">
							<div class="display">
								<div class="number">
									<h3 class="font-blue-sharp">
										<span>Profit Amount</span>
									</h3>
								</div>
							</div>
							<div class="progress-info row">
								<div class="col-md-3"> Target </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: 100%;" class="progress-bar progress-bar-success blue-sharp">
										{{array_get($oa_data,str_replace('.','',$data->site).'-'.$data->item_code.'.yewlr'.date('n',strtotime($date_start)),0)}}
									</span>
								</div>
								</div>
								<div class="col-md-3"></div>
								<div class="clearfix"></div>
							</div>
							
							<div class="progress-info row">
								<div class="col-md-3"> Time </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: {{$time_rate*100}}%;" class="progress-bar progress-bar-success green-sharp">
										{{$time_rate*100}}%
									</span>
								</div>
								</div>
								<div class="col-md-3" style="padding:0"> {{$time_rate*100}}% </div>
								<div class="clearfix"></div>
							</div>
							
							<div class="progress-info row">
								<div class="col-md-3"> Completed </div>
								<div class="col-md-6">
								<div class="progress">
									<span style="width: {{($complete_pro>100)?100:$complete_pro}}%;" class="progress-bar progress-bar-success red-haze">
										{{array_get($sap_data,str_replace('.','',$data->site).'-'.$data->item_code.'.VVVVV',0)}}
									</span>
								</div>
								</div>
								<div class="col-md-3" style="padding:0"> {{$complete_pro}}% </div>
								<div class="clearfix"></div>
							</div>
							</td>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">Total</td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-total_stock"> {!!intval(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fba_stock',0)+array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fbm_stock',0)+array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fba_transfer',0))!!} </span></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">FBA Keep </td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-fba_keep"> {!!(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.sales',0)!=0)?round(intval(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fba_stock',0))/(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.sales',0)),2):'∞'!!} </span></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">Total Keep </td>
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!}><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-total_keep"> {!!(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.sales',0)!=0)?round((intval(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fba_stock',0))+intval(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fbm_stock',0))+intval(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.fba_transfer',0)))/(array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.sales',0)),2):'∞'!!} </span></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Strategy</td>
							
							<?php 
							for($i=7;$i>=0;$i--){
								$style=((0==$i)?'style="background:#ddeef7;"':'');
								$week=date('Ymd',strtotime($date_start)+(-($i)*3600*24));
							?>
							<td {!!$style!!} class="strategy_s"><a class="sku_strategy" title="{{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.strategy')}}" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-strategy" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-strategy" data-type="text"> {{array_get($datas_details,str_replace('.','',$data->site).'-'.$data->asin.'-'.$week.'.strategy')}} </a></td>
							<?php
							}
							?>

						  </tr>

						    
						</table>
                        @endforeach
						
                               {{ $datas->appends(['date_start' => $date_start,'site' => $s_site,'user_id' => $s_user_id,'level' => $s_level,'bgbu' => $bgbu,'sku' => $sku])->links() }}   



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
		
		$('.sku_keywords,.sku_strategy,.sku_ranking,.sku_rating,.sku_review,.sku_price,.sku_flow,.sku_conversion').editable({
			emptytext:'N/A'
		});
		$('.sku_sales,.sku_fba_stock,.sku_fbm_stock,.sku_fba_transfer').editable({
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
    FormEditable.init();
	
	$("#vl_list_export").click(function(){
		location.href='/dreportexport?sku='+$("input[name='sku']").val()+'&date_start='+$("input[name='date_start']").val()+'&user_id='+(($("select[name='user_id[]']").val())?$("select[name='user_id[]']").val():'')+'&bgbu='+$("select[name='bgbu']").val()+'&site='+$('select[name="site"]').val()+'&level='+$('select[name="level"]').val();
	});
});
</script>


@endsection
