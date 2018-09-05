@extends('layouts.layout')
@section('label', 'Seller ASIN Details')
@section('content')
<script>
function loadorder(site,code,rating){
$(".chats").html('<div style="width:100%;text-align:center;"><img src="/assets/global/img/loading.gif"></div>');
$("#show_rating>li").eq(rating-1).addClass('active').siblings().removeClass('active');
$("#show_rating_content>div").eq(rating-1).addClass('active').siblings().removeClass('active');

$.post("/ratingdetails",
  {
	"_token":"{{csrf_token()}}",
	"site":site,
	"asin":code
  },
  function(data,status){
	if(status=='success'){
		var redata = JSON.parse(data);
		if(redata){
			//$("div[data-repeater-list='group-products']").html('');
			$(".chats").html('');
			
			var sts=[];
<?php foreach(getReviewStatus() as $key=>$val){ ?>
sts[{{$key}}]='{{$val}}';
<?php } ?>
			var star_1 = redata[1];
			for( var star in star_1 )
			{
			$("#star_1>.chats").append('<li class="in"><img class="avatar" alt="" src="/assets/layouts/layout/img/avatar.png"><div class="message"><span class="arrow"> </span> '+star_1[star].reviewer_name+'  &lt; <i class="fa fa-star"></i> &gt;  <span class="datetime"> at '+star_1[star].date+' </span> <span class="body" style="font-size:14px;"><a href="https://'+star_1[star].site+'/gp/customer-reviews/'+star_1[star].review+'" target="_blank"> '+star_1[star].review_content+' </a></span></div></li>');
			}
			var star_1 = redata[2];
			for( var star in star_1 )
			{
			$("#star_2>.chats").append('<li class="in"><img class="avatar" alt="" src="/assets/layouts/layout/img/avatar.png"><div class="message"><span class="arrow"> </span> '+star_1[star].reviewer_name+'  &lt; <i class="fa fa-star"></i><i class="fa fa-star"></i> &gt;  <span class="datetime"> at '+star_1[star].date+' </span> <span class="body" style="font-size:14px;"><a href="https://'+star_1[star].site+'/gp/customer-reviews/'+star_1[star].review+'" target="_blank"> '+star_1[star].review_content+' </a></span></div></li>');
			}
			var star_1 = redata[3];
			for( var star in star_1 )
			{
			$("#star_3>.chats").append('<li class="in"><img class="avatar" alt="" src="/assets/layouts/layout/img/avatar.png"><div class="message"><span class="arrow"> </span> '+star_1[star].reviewer_name+'  &lt; <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i> &gt;  <span class="datetime"> at '+star_1[star].date+' </span> <span class="body" style="font-size:14px;"><a href="https://'+star_1[star].site+'/gp/customer-reviews/'+star_1[star].review+'" target="_blank"> '+star_1[star].review_content+' </a></span></div></li>');
			}
		}
	}

  });
  $('#long').modal('show');
}
</script>
    <h1 class="page-title font-red-intense"> Seller ASIN Details
        <small>Seller ASIN Details</small>
    </h1>
    <div class="row">
        <div class="col-md-12">
<div class="portlet light portlet-fit bordered">
	

    <div class="portlet-body">
		<h1 class="uppercase">{{$data->asin}}</h1>
		<?php
		
		$ads_fee = $in_come = $in_profit=0;
		$showdata = json_decode(json_encode($data),TRUE);

		$stars=[];
		$stars_urls = array_get($showdata,'stars_details',array());
		$stars[str_ireplace('www.','',array_get($showdata,'site'))]=[];
		foreach($stars_urls as $stars_url){
			$stars[str_ireplace('www.','',array_get($stars_url,'domain'))]=$stars_url;
		}

															  
		$profits = array_get($showdata,'in_profits',array());
		$ads = array_get($showdata,'ads',array());
		$fbms = array_get($showdata,'fbm_stock_data',array());
		$fbas = array_get($showdata,'fba_stock_data',array());
		
		foreach($profits as $profit){

			$in_come += round(array_get($profit,'income',0),2);
			$in_profit += round(array_get($profit,'sales_profits',0),2);
		}	

		foreach($ads as $ad){
			$ads_fee=$ads_fee+round(array_get($ad,'cost',0),2);
		}	
		?>
		<table class="table table-striped table-hover table-bordered" >
		
		<tr style="background:#EEF1F5;">
			<th>Associated Skus</th>
			<th colspan="8">Sku Description</th>
		</tr>
		<?php foreach($fbms as $fbm){ ?>
		<tr >
			<td>{{array_get($fbm,'item_code')}}</td>
			<td colspan="8">{{array_get($fbm,'item_name')}}</td>

		</tr>
		<?php } ?>
		<tr style="background:#EEF1F5;">
			<th>Cost</th>
			<th>FBA Stock</th>
			<th>FBA Transfer</th>
			<th>FBA Stock Value</th>
			<th>FBA Stock Days</th>
			<th>FBM Stock</th>
			<th>FBM Stock Value</th>
			<th>Total Stock Value</th>
			<th>Total Stock Days</th>
		</tr>
		<tr>
			<td>{{round($data->cost,2)}} <i class="fa fa-rmb"></i></td>
			<td>{{round($data->stock,2)}}</td>
			<td>{{round($data->transfer,2)}}</td>
			<td>{{round($data->cost,2)*(round($data->stock,2)+round($data->transfer,2))}} <i class="fa fa-rmb"></i></td>
			<td>{{(round($data->total_sales,2)>0)?round((round($data->stock,2)+round($data->transfer,2))/round($data->total_sales,2),2):(round($data->stock,2)+round($data->transfer,2))*100}}</td>
			<td>{{round($data->fbm_stock,2)}}</td>
			<td>{{round($data->fbm_amount,2)}} <i class="fa fa-rmb"></i></td>
			<td>{{round($data->stock_amount,2)}} <i class="fa fa-rmb"></i></td>
			<td>{{(round($data->total_sales,2)>0)?round((round($data->stock,2)+round($data->transfer,2)+round($data->fbm_stock,2))/round($data->total_sales,2),2):(round($data->stock,2)+round($data->transfer,2)+round($data->fbm_stock,2))*100}} </td>
		</tr>
		<tr style="background:#EEF1F5;">
			
			<th>Avg Sales/D</th>
			<th>Total Avg Sales/D</th>
			<th>Sales 14 days</th>
			<th>Review</th>
			<th>Review Count</th>
			<th>Total Ads Fee</th>
			<th>Total Income</th>
			<th>Total Profit</th>
			<th>Profit margin</th>
		</tr>
		<tr>
			<td>{{round($data->sales,2)}}</td>
			<td>{{round($data->total_sales,2)}}</td>
			<td>{{ceil(round($data->total_sales,2)*14)}}</td>
			<td>{{round($data->avg_star,2)}}</td>
			<td>{{intval($data->total_star)}}</td>
			<td>{{round($ads_fee,2)}} <i class="fa fa-rmb"></i></td>
			<td>{{round($in_come,2)}} <i class="fa fa-rmb"></i></td>
			<td>{{round($in_profit,2)}} <i class="fa fa-rmb"></i></td>
			<td>{{round($data->profits,2)}} %</td>
		</tr>
		</table>
		
		
		<div class="tabbable-line">
                                            <ul class="nav nav-tabs">
                                                <li class="active">
                                                    <a href="#overview_1" data-toggle="tab" aria-expanded="true"> Review Detail
 </a>
                                                </li>
                                                <li class="">
                                                    <a href="#overview_2" data-toggle="tab" aria-expanded="false"> Profit analysis detail data</a>
                                                </li>
                                                <li >
                                                    <a href="#overview_3" data-toggle="tab" aria-expanded="false"> Promotion cost detail data </a>
                                                </li>
												
												<li >
                                                    <a href="#overview_4" data-toggle="tab" aria-expanded="false"> FBM Stock</a>
                                                </li>
												
												
												<li >
                                                    <a href="#overview_5" data-toggle="tab" aria-expanded="false"> FBA Stock</a>
                                                </li>
                                                
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="overview_1">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-hover table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th> Site </th>
                                                                    <th> Asin </th>
                                                                    <th> Sellersku </th>
                                                                    <th> <i class="fa fa-star"></i> </th>
																	<th> <i class="fa fa-star"></i><i class="fa fa-star"></i> </th>
																	<th> <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></th>
																	<th> <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></th>
																	<th> <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></th>
																	<th> Count </th>
																	<th> Rating </th>
																	<th> LastUpdateDate </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
															<?php foreach($stars as $key=>$star){ 
															
															if(array_get($star,'asin')){
															?>
															<tr>
															<td><a href="https://{{$key}}/dp/{{$data->asin}}" target="_blank">
															https://{{$key}}/dp/{{$data->asin}}</a></td>
															<td>{{array_get($star,'asin')}}</td>
															<td>{{array_get($star,'sellersku')}}</td>
															<td><a href="javascript:loadorder('www.{{$key}}','{{$data->asin}}',1);">{{array_get($star,'one_star_number')}}</a></td>
															<td><a href="javascript:loadorder('www.{{$key}}','{{$data->asin}}',2);">{{array_get($star,'two_star_number')}}</a></td>
															<td><a href="javascript:loadorder('www.{{$key}}','{{$data->asin}}',3);">{{array_get($star,'three_star_number')}}</a></td>
															<td>{{array_get($star,'four_star_number')}}</td>
															<td>{{array_get($star,'five_star_number')}}</td>
															<td>{{array_get($star,'total_star_number')}}</td>
															<td>{{array_get($star,'average_score')}}</td>
															<td>{{array_get($star,'updated_at')}}</td>
                                                              </tr>
															
															<?php
															}else{
															?>
															<tr>
															<td><a href="https://{{$key}}/dp/{{$data->asin}}" target="_blank">
															https://{{$key}}/dp/{{$data->asin}}</a></td>
															<td>{{$data->asin}}</td>
															<td colspan="9">No data collected</td>
                                                              </tr>
															  <?php 
															  }
															  } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="overview_2">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-hover table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th> Item Code </th>
                                                                    <th> InCome </th>
                                                                    <th> Sale Profit </th>
																	<th> Seller </th>
                                                                    <th> Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                               <?php 
															   $in_come_t=$sales_profits_t=0;
															   foreach($profits as $profit){
															   $in_come_t+=round(array_get($profit,'income',0),2);
															   $sales_profits_t+=round(array_get($profit,'sales_profits',0),2);
 ?>
															<tr>
															<td>{{array_get($profit,'item_code',0)}}</td>
															<td>{{round(array_get($profit,'income',0),2)}} <i class="fa fa-rmb"></i></td>
															<td>{{round(array_get($profit,'sales_profits',0),2)}} <i class="fa fa-rmb"></i></td>
															<td>{{array_get($profit,'seller_name')}}</td>
															<td>{{array_get($profit,'date')}}</td>
					
                                                              </tr>
															  <?php } ?>
															   <thead>
															  <tr>
															<th>Total :</th>
															<th>{{$in_come_t}} <i class="fa fa-rmb"></i></th>
															<th>{{$sales_profits_t}} <i class="fa fa-rmb"></i></th>
															<th></th>
															<th></th>
					
                                                              </tr>
                                                                </th>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="tab-pane " id="overview_3">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-hover table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th> Item Code</th>
                                                                    <th> Ads Fee </th>
                                                                    <th> Date </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php 
																$cost_t=0;
																foreach($ads as $ad){
																$cost_t+=round(array_get($ad,'cost',0),2);
 ?>
															<tr>
															<td>{{array_get($ad,'item_code',0)}}</td>
															<td>{{round(array_get($ad,'cost',0),2)}} <i class="fa fa-rmb"></i></td>
															<td>{{array_get($ad,'date')}}</td>
					
                                                              </tr>
															  <?php } ?>
                                                                 
                                                            </tbody>
															 <thead>
															<tr>
                                                                    <th>Total :</th>
                                                                    <th> {{$cost_t}} <i class="fa fa-rmb"></i></th>
                                                                    <th> </th>
                                                                </tr>
																</thead>
                                                        </table>
                                                    </div>
                                                </div>
												
												
												
												<div class="tab-pane " id="overview_4">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-hover table-bordered">
                                                            <thead>
                                                                <tr>
																	<th> Asin</th>
                                                                    <th> Item Code</th>
																	<th> Item Description</th>
                                                                    <th> FBM InStock </th>
																	<th> FBM Value </th>
                                                                    <th> Date </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
																$cost_fba=$fbm_stock_total =$fbm_stock_amount= 0;
																foreach($fbms as $fbm){
																$cost_fba=round(array_get($fbm,'cost',0),2);
																$fbm_stock_total+=round(array_get($fbm,'fbm_stock',0),2);
																$fbm_stock_amount+=round(array_get($fbm,'fbm_amount',0),2);
 																?>
															<tr>
															<td>{{array_get($fbm,'asin')}}</td>
															<td>{{array_get($fbm,'item_code')}}</td>
															<td>{{array_get($fbm,'item_name')}}</td>
															<td>{{round(array_get($fbm,'fbm_stock',0),2)}}</td>
															<td>{{round(array_get($fbm,'fbm_amount',0),2)}} <i class="fa fa-rmb"></i></td>
															<td>{{array_get($fbm,'date')}}</td>
					
                                                              </tr>
															  <?php } ?>
                                                                 
                                                            </tbody>
															 <thead>
															<tr>
                                                                    <th>Total :</th>
																	<th> </th>
																	<th> </th>
																	<th> {{$fbm_stock_total}} </th>
                                                                    <th> {{$fbm_stock_amount}} <i class="fa fa-rmb"></i></th>
                                                                    <th> </th>
                                                                </tr>
																</thead>
                                                        </table>
                                                    </div>
                                                </div>
												
												
												
												<div class="tab-pane " id="overview_5">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-hover table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th> Seller ID</th>
																	<th> Asin</th>
                                                                    <th> Seller Sku </th>
																	 <th>FBA InStock </th>
																	 <th>FBA Transfer </th>
																	 <th>FBA Total </th>
																	 <th>FBA Value </th>
                                                                    <th> Date </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php 
																$fba_stock_total =$fba_total_total =$fba_transfer_total=$fba_stock_amount= 0;
																foreach($fbas as $fba){
																$fba_stock_total+=round(array_get($fba,'instock',0),2);
																$fba_transfer_total+=round(array_get($fba,'transfer',0),2);
																$fba_total_total+=round(array_get($fba,'total',0),2);
 ?>
															<tr>
															<td>{{array_get($fba,'sellerid')}}</td>
															<td>{{array_get($fba,'asin')}}</td>
															<td>{{array_get($fba,'sellersku')}}</td>
															<td>{{round(array_get($fba,'instock',0),2)}}</td>
															<td>{{round(array_get($fba,'transfer',0),2)}}</td>
															<td>{{round(array_get($fba,'total',0),2)}}</td>
															<td>{{round(array_get($fba,'total',0)*$cost_fba,2)}} <i class="fa fa-rmb"></i></td>
															<td>{{array_get($fba,'date')}}</td>
					
                                                              </tr>
															  <?php } ?>
                                                                 
                                                            </tbody>
															 <thead>
															<tr>
                                                                    <th>Total :</th>
																	<th> </th>
																	<th> </th>
                                                                    <th> {{$fba_stock_total}} </th>
																	<th> {{$fba_transfer_total}} </th>
																	<th> {{$fba_total_total}} </th>
																	<th> {{$fba_total_total*$cost_fba}} <i class="fa fa-rmb"></i></th>
                                                                    <th> </th>
                                                                </tr>
																</thead>
                                                        </table>
                                                    </div>
                                                </div>
												
												
                                                
                                            </div>
                                        </div>
	</div>
</div>
        </div>
		 <div style="clear:both;"></div></div>

<div id="long" class="modal fade modal-scroll " tabindex="-1" data-replace="true">
	<div class="modal-dialog" style="width:60%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Negative Review Details</h4>
			</div>
			<div class="modal-body">
			
			<div class="tabbable-line">
				<ul class="nav nav-tabs" id="show_rating">
					<li class="active">
						<a href="#star_1" data-toggle="tab" aria-expanded="true"> <i class="fa fa-star"></i> </a>
					</li>
					<li class="">
						<a href="#star_2" data-toggle="tab" aria-expanded="false"> <i class="fa fa-star"></i><i class="fa fa-star"></i> </a>
					</li>
					<li class="">
						<a href="#star_3" data-toggle="tab" aria-expanded="false"> <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i> </a>
					</li>
					
				</ul>
											
					<div class="tab-content" id ="show_rating_content">
                         <div class="tab-pane active" id="star_1">
						 <ul class="chats">			
                           </ul> 
						 </div>
						 <div class="tab-pane " id="star_2">
						 <ul class="chats">
                           </ul> 
						 </div>
						 <div class="tab-pane " id="star_3">
						 <ul class="chats">
                           </ul> 
						 </div>
					</div>
			</div>
			
				</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn dark btn-outline">Close</button>
			</div>
		</div>
	</div>
</div>
@endsection
