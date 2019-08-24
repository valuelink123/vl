@extends('layouts.layout')
@section('label', 'Add RSG Products')
@section('content')
<script>
    window.UEDITOR_HOME_URL = "/laravel-u-editor/";//配置编辑器的文件路径
</script>
@include('UEditor::head')
    <div class="row"><div class="col-md-12">
        <div class="portlet light bordered">

            <div class="portlet-body form">
                <form role="form" action="{{ url('rsgproducts') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-body">
						<div class="form-group col-md-6">
                            <label>Start Date</label>
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
								<input type="text" class="form-control" readonly name="start_date" placeholder="From" value="{{date('Y-m-d')}}" required>
								<span class="input-group-btn">
									<button class="btn default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
							</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>End Date</label>
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
								<input type="text" class="form-control" readonly name="end_date" placeholder="To" value="{{date('Y-m-d')}}" required>
								<span class="input-group-btn">
									<button class="btn   default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
							</div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Account</label>
                            
                                
								<select name="seller_id" class="form-control" required>
								   <?php 
									foreach($accounts as $k=>$v){ 	
										echo '<option value="'.$k.'">'.$v.' ( '.$k.' ) </option>';
									}?>
								</select>
                            
                        </div>
                        <div class="form-group col-md-6">
                            <label>Site</label>
                           
                                
                                <select name="site" class="form-control " required>
								   <?php 
									foreach(getAsinSites() as $v){ 	
										echo '<option value="'.$v.'">'.$v.' </option>';
									}?>
								</select>
                            
                        </div>
                        <div class="form-group col-md-6">
                            <label>Asin</label>
                            
                             
                                <input type="text" class="form-control" name="asin" id="asin" value="" required>
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Daily Gift Quantity</label>


                                <input type="text" class="form-control" name="daily_stock" id="daily_stock" value="" required>

                        </div>
						<div class="form-group col-md-6">
                            <label>Product Name</label>
                            
                             
                                <input type="text" class="form-control" name="product_name" id="product_name" value="" required>
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Product Image</label>
                            
                      
                                <input type="text" class="form-control" name="product_img" id="product_img" value="" required>
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Price</label>
                            	<div class="clearfix"></div>
                             	<div class="form-inline">
                                <input type="text" class="form-control col-md-8" name="price" id="price" value="" required>
								
								<select class="form-control col-md-4" name="currency" id="currency" required>
								<?php 
									foreach(getCurrency() as $v){ 	
										echo '<option value="'.$v.'">'.$v.' </option>';
									}?>
								</select>
								</div>
                            
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Purchase Search Keywords</label>
                            
                      
                                <input type="text" class="form-control" name="keyword" id="keyword" value="">
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Purchase Search Page No.</label>
                            
                      
                                <input type="text" class="form-control" name="page" id="page" value="" >
                           
                        </div>
						
						
						<div class="form-group col-md-6">
                            <label>Purchase Search Position</label>
                            
                      
                                <input type="text" class="form-control" name="position" id="position" value="" >
                           
                        </div>
						
						<div class="form-group col-md-6">
                            <label>Reviews Needed</label>
                            
                      
                                <input type="text" class="form-control" name="positive_target" id="positive_target" value="">
                           
                        </div>
						
						
						<div class="form-group col-md-6">
                            <label>Reviews needed weekly</label>
                            
                      
                                <input type="text" class="form-control" name="positive_daily_limit" id="positive_daily_limit" value="">
                           
                        </div>

                        {{--<div class="form-group col-md-6">--}}
                            {{--<label>Review Rating</label>--}}


                            {{--<input type="text" class="form-control" name="review_rating" id="review_rating" value="">--}}

                        {{--</div>--}}

                        {{--<div class="form-group col-md-6">--}}
                            {{--<label>Number of reviews</label>--}}


                            {{--<input type="text" class="form-control" name="number_of_reviews" id="number_of_reviews" value="">--}}

                        {{--</div>--}}

                        <div class="form-group col-md-6">
                            <label>Sales' target reviews</label>
                            <input type="text" class="form-control" name="sales_target_reviews" id="sales_target_reviews" value="" pattern="[0-9]*" required>
                        </div>

                        <div class="form-group col-md-8">
                            <label>Product Summary</label>
                            <textarea class="form-control"  style="height:300px;" name="product_summary"></textarea>

                        </div>


                        <div class="col-sm-12">
                            <label>Product Content</label>
                            <script id="bdeditor" name="product_content" type="text/plain"></script>
                        </div>

						
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
								<button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                <button type="submit" class="btn blue pull-right">Submit</button>
                                
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>
<script>
$(function() {
$('.date-picker').datepicker({
	rtl: App.isRTL(),
	autoclose: true
});
    var ue = UE.getEditor('bdeditor');
    ue.ready(function() {
        ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
    });
});
</script>
@endsection
