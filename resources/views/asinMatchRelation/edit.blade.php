@extends('layouts.layout')
@section('label', 'Create A New Asin Match Relation')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<form  action="/asinMatchRelation/update" id="form" novalidate method="POST" onsubmit="return validate_form()">
		{{ csrf_field() }}
		<input type="hidden" class="form-control" name="id" value="{{$data['id']}}">
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
													<option value="{{ $value->marketplaceid }}" @if($data['marketplace_id']==$value->marketplaceid) selected @endif>{{ $value->domain }}</option>
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
									<input type="hidden" id="select_seller_id" value="{{$data['seller_id']}}">

									<div class="form-group">
										<label>Asin</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="asin" id="asin" value="{{$data['asin']}}" required >
										</div>
									</div>
									<div class="form-group">
										<label>Seller Sku</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="seller_sku" id="seller_sku" value="{{$data['seller_sku']}}"  >
										</div>
									</div>
									<div class="form-group">
										<label>SKU</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="sku" id="sku" value="{{$data['sku']}}"  >
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
													<option value="{{ $key }}" @if($data['sap_seller_id']==$key) selected @endif>{{ $value }}</option>
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
													<option value="{{ $value }}" @if($data['source']==$value) selected @endif>{{ $value }}</option>
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
											<input type="text" class="form-control" name="sap_warehouse_code" id="sap_warehouse_code" value="{{$data['sap_warehouse_code']}}"  >
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
			var sku = $('#sku').val().trim();
			if(asin == ''){
				alert("Asin cannot be empty.");
				return false;
			}
			if(seller_sku == ''){
				alert("Seller Sku cannot be empty.");
				return false;
			}
			if(sku == ''){
				alert("SKU cannot be empty.");
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
						var select_seller_id = $('#select_seller_id').val();
						var html = '';
						$.each(res.data,function(i,item) {
							if(item.id==select_seller_id){
								html += '<option value="'+item.id+'" selected>'+item.label+'</option>';
							}else{
								html += '<option value="'+item.id+'">'+item.label+'</option>';
							}
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