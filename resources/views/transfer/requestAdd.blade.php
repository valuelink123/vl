@extends('layouts.layout')
@section('label', 'Create A New Transfer Request')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<form  action="/transfer/request/add" id="form" novalidate method="POST" onsubmit="return validate_form()">
		{{ csrf_field() }}
		<div class="col-lg-9">
			<div class="col-md-12">
				<div class="portlet light portlet-fit bordered ">
					@if($errors->any())
						<div class="alert alert-danger">
							@foreach($errors->all() as $error)
								<div>{{ $error }}</div>
							@endforeach
						</div>
					@endif
					<div class="portlet-title">
						<div class="caption">
							<i class="icon-microphone font-green"></i>
							<span class="caption-subject bold font-green">Create A New Transfer Request</span>
						</div>
					</div>
					<div class="portlet-body">
						<div class="tabbable-line">
							<div class="">
								<div class="col-lg-8">
									<div class="form-group">
										<label>Site</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select class="form-control"  id="site" name="site" onchange="getAccountBySite()">
												<option value="">Select</option>
												@foreach(getSiteCode() as $key=>$val)
													<option value="{!! $val !!}">{!! $key !!}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="form-group">
										<label>Account</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select class="form-control"  id="account" name="account">
												<option value="">Select</option>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label>Delivery Date</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<div class="input-group">
												<input  class="form-control" value="{!! $date !!}" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="date" name="date"/>
											</div>
										</div>
									</div>

									<div class="form-group">
										<label>Reason</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="request_reason" id="request_reason" value=""  >
										</div>
									</div>
								</div>

								<div class="col-sm-12">
									<div class="form-group mt-repeater frank">
										<div data-repeater-list="group-data" id="replacement-product-list">
											<div data-repeater-item class="mt-repeater-item">
												<div class="row mt-repeater-row">
													<div class="col-lg-2 col-md-2">
														<label class="control-label">Asin</label>
														<input type="text" class="form-control asin" name="asin" placeholder="Asin" value="" onchange="checkAsin($(this))"/>
													</div>
													<div class="col-lg-2 col-md-2">
														<label class="control-label">Quantity</label>
														<input type="text" class="form-control quantity" name="quantity" placeholder="quantity" value=""/>
													</div>
													<div class="col-lg-1 col-md-1">
														<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
															<i class="fa fa-close"></i>
														</a>
													</div>
												</div>
											</div>
										</div>
										<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add"><i class="fa fa-plus"></i> Add</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-4 col-md-8">
								<button type="submit" class="btn blue">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>
	</form>
	@include('frank.common')
	<script>
		//日期控件初始化
		$('#date').datepicker({
			rtl: App.isRTL(),
			autoclose: true
		});

        function validate_form(){
            var site = $('#site').val();
            var account = $('#account').val();
            var request_reason = $('#request_reason').val();
            if(site == ''){
                alert("site cannot be empty.");
                return false;
            }
            if(account == ''){
                alert("account cannot be empty.");
                return false;
            }
            if(request_reason == ''){
                alert("request_reason cannot be empty.");
                return false;
            }
            var flag = 0;
            $(".asin").each(function(ii,vv){ //ii 指第几个元素的序列号,vv 指遍历得到的元素
				var asin = $(this).val();
                if(asin == ''){
                    flag = 1;
                }
            });
            if(flag==1){
                alert("asin cannot be empty.");
                return false;
			}
            var flag_q = 0;
            $(".quantity").each(function(ii,vv){ //ii 指第几个元素的序列号,vv 指遍历得到的元素
                var quantity = $(this).val();
                if(quantity == ''){
                    flag_q = 1;
                }
            });
            if(flag_q==1){
                alert("quantity cannot be empty.");
                return false;
            }
        }
        let $replacementProductList = $('#replacement-product-list')
        let replacementItemRepeater = $replacementProductList.parent().repeater({repeaters: [{selector: '.inner-repeater'}],defaultValues:{qty:1}})
        function getAccountBySite(){
            var marketplaceid = $('#site option:selected').val();
            $.ajax({
                type: 'post',
                url: '/showAccountBySite',
                data: {marketplaceid:marketplaceid},
                dataType:'json',
                success: function(res) {
                    if(res.status==1){
                        var html = '<option value="">Select</option>';
                        $.each(res.data,function(i,item) {
                            html += '<option value="'+item.id+'">'+item.label+'</option>';
                        })
                        $('#account').html(html);
                    }else{
                        alert('请先选择站点');
                    }
                }
            });
        }
		//检测asin是否为有效asin，并且为自己所属的asin
        function checkAsin(obj){
            var asin = obj.val();
            var site = $('#site').val();
            $.ajax({
                type: 'post',
                url: '/checkAsin',
                dataType:'json',
                data: {asin:asin,site:site},
                success: function(res) {
                    if(res.status==0){
                        alert(res.msg);
                        obj.val('');
                    }
                }
            });

        }
	</script>
@endsection