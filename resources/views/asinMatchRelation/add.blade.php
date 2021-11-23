@extends('layouts.layout')
@section('label', 'Create A New Asin Match Relation')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<form  action="/asinMatchRelation/add" id="form" novalidate method="POST" onsubmit="return validate_form()">
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
							<span class="caption-subject bold font-green">Create A New Asin Match Relation</span>
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
											<select  style="width:100%;height:35px;"  id="site" onchange="getAccountBySite()" name="marketplace_id">
												@foreach($site as $value)
													<option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="form-group" id="account-div">
										<label>Account</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select style="width:100%;height:35px;" class="btn btn-default" id="account" name="seller_id">
											</select>
										</div>
									</div>
									<input type="hidden" name="seller_name" id="seller_name" value="">

									<div class="form-group">
										<label>Asin</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="asin" id="asin" value="" required >
										</div>
									</div>
									<div class="form-group">
										<label>Seller Sku</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="seller_sku" id="seller_sku" value=""  >
										</div>
									</div>
									<div class="form-group">
										<label>Item No</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="item_no" id="item_no" value=""  >
										</div>
									</div>
									<div class="form-group">
										<label>Seller</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select  style="width:100%;height:35px;"  id="sap_seller_id"  name="sap_seller_id">
												@foreach($seller_user as $key=> $value)
													<option value="{{ $key }}">{{ $value }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="form-group">
										<label>Source</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<select  style="width:100%;height:35px;"  id="source" name="source">
												@foreach($source as $value)
													<option value="{{ $value }}">{{ $value }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="form-group">
										<label>Warehouse</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="warehouse" id="warehouse" value=""  >
										</div>
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
		</div>
	</form>
	@include('frank.common')
	<script>
        function validate_form(){
            var asin = $('#asin').val().trim();
            var seller_sku = $('#seller_sku').val().trim();
			var item_no = $('#item_no').val().trim();
            if(asin == ''){
                alert("Asin cannot be empty.");
                return false;
            }
            if(seller_sku == ''){
                alert("Seller Sku cannot be empty.");
                return false;
            }
			if(item_no == ''){
				alert("Item No cannot be empty.");
				return false;
			}
			$('#seller_name').val($('#account').find("option:selected").text());
        }
		function getAccountBySite(){
			var marketplaceid = $('#site option:selected').val();
			$.ajax({
				type: 'post',
				url: '/showAccountBySite',
				data: {marketplaceid:marketplaceid,field:'mws_seller_id'},
				dataType:'json',
				success: function(res) {
					if(res.status==1){
						var html = '';
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
		$(function(){
			getAccountBySite()//触发当前选的站点得到该站点所有的账号
		})
	</script>
@endsection