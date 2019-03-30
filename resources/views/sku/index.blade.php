@extends('layouts.layout')
@section('label', 'Daily Sales Report')
@section('content')
<link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
<style>
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 10px 2px;
}
th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
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
						

						 <div class="col-md-2">
						<select class="form-control form-filter input-sm" name="site" id="site">
									<option value="">Select Site</option>
                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}" <?php if($v==$s_site) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
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
					
                </div>
                    <div class="table-container">
					{{ $datas->appends(['date_start' => $date_start,'site' => $s_site,'user_id' => $s_user_id,'bgbu' => $bgbu,'sku' => $sku])->links() }}   
					@foreach ($datas as $data)
						<table id="user" class="table table-striped table-bordered table-hover">
 
						 
																	
						<?php 

						$d_number = (date('w',strtotime($date_start))==0)?6:(date('w',strtotime($date_start))-1);
						?>
							<tr >
							<td width="5%" style="font-weight: bold;">SKU</td>
							<td width="5%"  style="font-weight: bold;">Seller</td>
							<td width="5%" style="font-weight: bold;">BG</td>
							<td width="5%" style="font-weight: bold;">BU</td>
							<td colspan="2" width="14%" style="font-weight: bold;">Link</td>
							<td width="7%" style="font-weight: bold;">Site</td>
							<td colspan="3" width="21%" style="font-weight: bold;"> Main Keywords </td>
							<td width="7%" style="font-weight: bold;">Status</td>
							<td width="7%" style="font-weight: bold;">Level</td>
							<td colspan="2"width="24%" style="font-weight: bold;">Description</td>
						  </tr>
						  <tr>
							<td rowspan="16">{{$data->item_code}}</td>
							<td rowspan="16"> {{array_get($users,$data->sap_seller_id,$data->sap_seller_id)}} </td>
							<td rowspan="16">{{$data->bg}}</td>
							<td rowspan="16">{{$data->bu}}</td>
							<td colspan="2"><a href="https://{{$data->site}}/dp/{{strip_tags(str_replace('&nbsp;','',$data->asin))}}" target="_blank">{{strip_tags(str_replace('&nbsp;','',$data->asin))}}</a></td>
							<td>{{$data->site}}</td>
							<td colspan="3"><a class="sku_keywords" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-keywords" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-keywords" data-type="text"> {{$data->keywords}} </a></td>
							<td>{!!($data->status)?'<span class="btn btn-success btn-xs">Reserved</span>':'<span class="btn btn-danger btn-xs">Eliminate</span>'!!}</td>
							<td>{{$data->pro_status}}</td>
							<td colspan="2">{{$data->item_name}}</td>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Index </td>
							<td style="font-weight: bold;">Mon ({{date('m-d',strtotime($date_start)+(-($d_number-0)*3600*24))}})</td>
							<td width="7%"  style="font-weight: bold;">Tues ({{date('m-d',strtotime($date_start)+(-($d_number-1)*3600*24))}})</td>
							<td width="7%" style="font-weight: bold;" >Wed ({{date('m-d',strtotime($date_start)+(-($d_number-2)*3600*24))}})</td>
							<td width="7%" style="font-weight: bold;" >Thur ({{date('m-d',strtotime($date_start)+(-($d_number-3)*3600*24))}})</td>
							<td style="font-weight: bold;">Fri ({{date('m-d',strtotime($date_start)+(-($d_number-4)*3600*24))}})</td>
							<td style="font-weight: bold;">Sat ({{date('m-d',strtotime($date_start)+(-($d_number-5)*3600*24))}})</td>
							<td width="7%" style="font-weight: bold;">Sun ({{date('m-d',strtotime($date_start)+(-($d_number-6)*3600*24))}})</td>
							<td rowspan="5">
							<div class="progress-info">
								<div class="progress">
									<span style="width: 85%;" class="progress-bar progress-bar-success red-haze">
										<span class="sr-only">85% change</span>
									</span>
								</div>
							   
							</div>
							</td>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Ranking</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_ranking" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-ranking_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-ranking_{{$i}}" data-type="text"> {!!$data->{'ranking_'.$i}!!} </a></td>
							<?php
							}
							?>
							
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Rating</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_rating" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-rating_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-rating_{{$i}}" data-type="text"> {!!$data->{'rating_'.$i}!!} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Review</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_review" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-review_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-review_{{$i}}" data-type="text"> {!!$data->{'review_'.$i}!!} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Sales</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_sales" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-sales_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-sales_{{$i}}" data-type="text"> {!!$data->{'sales_'.$i}!!} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Price</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_price" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-price_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-price_{{$i}}" data-type="text"> {!!$data->{'price_'.$i}!!} </a></td>
							<?php
							}
							?>
							<td rowspan="5">
							<div class="progress-info">
								<div class="progress">
									<span style="width: 85%;" class="progress-bar progress-bar-success red-haze">
										<span class="sr-only">85% change</span>
									</span>
								</div>
							   
							</div>
							</td>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Session</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_flow" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-flow_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-flow_{{$i}}" data-type="text"> {!!$data->{'flow_'.$i}!!} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Conversion</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_conversion" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-conversion_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-conversion_{{$i}}" data-type="text"> {!!$data->{'conversion_'.$i}!!} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td  rowspan="6" width="7%" style="font-weight: bold;">Stock</td>
							<td  style="font-weight: bold;">FBA</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_fba_stock" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_stock_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_stock_{{$i}}" data-type="text"> {!!$data->{'fba_stock_'.$i}!!} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">FBA Tran </td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_fba_transfer" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_transfer_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fba_transfer_{{$i}}" data-type="text"> {!!$data->{'fba_transfer_'.$i}!!} </a></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">FBM</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><a class="sku_fbm_stock" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-fbm_stock_{{$i}}" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-fbm_stock_{{$i}}" data-type="text"> {!!$data->{'fbm_stock_'.$i}!!} </a></td>
							<?php
							}
							?>
							<td rowspan="5">
							<div class="progress-info">
								<div class="progress">
									<span style="width: 85%;" class="progress-bar progress-bar-success red-haze">
										<span class="sr-only">85% change</span>
									</span>
								</div>
							   
							</div>
							</td>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">Total</td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-total_stock_{{$i}}"> {!!intval($data->{'fba_stock_'.$i}+$data->{'fbm_stock_'.$i}+$data->{'fba_transfer_'.$i})!!} </span></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">FBA Keep </td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-fba_keep_{{$i}}"> {!!($data->{'sales_'.$i})?round(intval($data->{'fba_stock_'.$i})/($data->{'sales_'.$i}),2):'∞'!!} </span></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td style="font-weight: bold;">Total Keep </td>
							<?php 
							for($i=0;$i<7;$i++){
								$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
							?>
							<td {!!$style!!}><span id="{{str_replace('.','',$data->site).'-'.$data->asin.'-'.$week}}-total_keep_{{$i}}"> {!!($data->{'sales_'.$i})?round((intval($data->{'fba_stock_'.$i})+intval($data->{'fbm_stock_'.$i})+intval($data->{'fba_transfer_'.$i}))/($data->{'sales_'.$i}),2):'∞'!!} </span></td>
							<?php
							}
							?>
						  </tr>
						  <tr>
							<td colspan="2" style="font-weight: bold;">Strategy</td>
							<td colspan="7"><a class="sku_strategy" href="javascript:;" id="{{$data->site.'-'.$data->asin.'-'.$week}}-strategy" data-pk="{{$data->site.'-'.$data->asin.'-'.$week}}-strategy" data-type="text"> {{$data->strategy}} </a></td>
						  </tr>

						    
						</table>
                        @endforeach
						
                               {{ $datas->appends(['date_start' => $date_start,'site' => $s_site,'user_id' => $s_user_id,'bgbu' => $bgbu,'sku' => $sku])->links() }}   



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
});
</script>


@endsection
