<?php
$disabledForm = ' disabled ';
if(array_get($form,'sap_seller_id') == \Auth::user()->sap_seller_id && array_get($form,'status')<=1) $disabledForm="";
if(empty($form)) $disabledForm="";
?>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>调拨计划</h1></div>
            <div class="portlet-body">
                    <form id="update_form"  name="update_form" >
                        {{ csrf_field() }}
                        <div class="form-body">
                        <input type="hidden" name="id" value="{{array_get($form,'id',0)}}">
                        <input type="hidden" name="seller_id" id = "seller_id" value="{{array_get($form,'seller_id')}}">
						<input type="hidden" name="api_msg">
						<div class="row">
                        @if(getTransferForRole())
						<div class="col-md-3">
						<div class="form-group">
							<label>审核状态:</label>
							<select class="form-control" name="status" id="status" >
							@foreach (getTransferForRole() as $k=>$v)
							<option value="{{$k}}" {{($k==array_get($form,'status') || (empty(array_get($form,'status')) && $k==1))?'selected':''}} >{{$v}}</option>
							@endforeach 
							</select>
						</div>
						</div>
						@endif
						<div class="col-md-3">
						<div class="form-group">
							<label>Shipment ID:</label>
                            <input type="text" class="form-control" name="shipment_id" id="shipment_id" {{$disabledForm}} value="{{array_get($form,'shipment_id')}}" required>
						</div>
						</div>

						<div class="col-md-2">
						<div class="form-group">
							<label>站点:</label>
							<select class="form-control" name="marketplace_id" id="marketplace_id" {{$disabledForm}} required>
							@foreach (getSiteCode() as $k=>$v)
							<option value="{{$v}}" {{($v==array_get($form,'marketplace_id'))?'selected':''}} >{{$k}}</option>
							@endforeach 
							</select>
						</div>
						</div>
						<div class="col-md-2">
						<div class="form-group">
							<label>运输方式:</label>
                            <select class="form-control" name="ship_method" id="ship_method" {{$disabledForm}} required>
							@foreach (\App\Models\TransferPlan::SHIPMETHOD as $k=>$v)
							<option value="{{$k}}" {{($k==array_get($form,'ship_method'))?'selected':''}} >{{$v}}</option>
							@endforeach 
							</select>
						</div>
						</div>
						<div class="col-md-2">
						<div class="form-group">
							<label>预约号:</label>
                            <input type="text" class="form-control" name="reservation_id" id="reservation_id" {{$disabledForm}} value="{{array_get($form,'reservation_id')}}" required>
						</div>
						</div>
						</div>

						<div class="row">
						<div class="col-md-3">
						<div class="form-group">
							<label>仓库代码:</label>
                            <select class="form-control" name="warehouse_code" id="warehouse_code" {{$disabledForm}} required>
							@foreach ($warehouses as $k=>$v)
							<option value="{{$k}}" {{($k==array_get($form,'warehouse_code'))?'selected':''}} >{{$k.' - '.$v}}</option>
							@endforeach 
							</select>
						</div>
						</div>

						<div class="col-md-3">

						<div class="form-group">
							<label>预计到货时间:</label>
                            <input type="text" class="form-control date-picker" name="received_date" id="received_date" {{$disabledForm}} value="{{array_get($form,'received_date')}}" required>
						</div>
						</div>

						<div class="col-md-2">
						<div class="form-group">
							<label>调入工厂:</label>
                            <select class="form-control" name="in_factory_code" id="in_factory_code" {{$disabledForm}} required>
							@foreach ($factorys as $k=>$v)
							<option value="{{$v}}" {{($v==array_get($form,'in_factory_code') || (empty(array_get($form,'in_factory_code')) && $v=='US01'))?'selected':''}} >{{$v}}</option>
							@endforeach 
							</select>
						</div>
						</div>

						<div class="col-md-2">
						<div class="form-group">
							<label>调出工厂:</label>
							<select class="form-control" name="out_factory_code" id="out_factory_code" {{$disabledForm}} required>
							@foreach ($factorys as $k=>$v)
							<option value="{{$v}}" {{($v==array_get($form,'out_factory_code') || (empty(array_get($form,'out_factory_code')) && $v=='US04'))?'selected':''}} >{{$v}}</option>
							@endforeach 
							</select>
						</div>
						</div>

						</div>


						<div class="row">
					

						<div class="col-md-6">

						<div class="form-group">
							<label>调拨理由:</label>
                            <input type="text" class="form-control" name="reson" id="reson" {{$disabledForm}} value="{{array_get($form,'reson')}}" required>
						</div>
						</div>

						<div class="col-md-6">

						<div class="form-group">
							<label>备注:</label>
                            <input type="text" class="form-control" name="remark" id="remark" {{$disabledForm}} value="{{array_get($form,'remark')}}" required>
						</div>
						</div>
						</div>
						

						<div class="form-group mt-repeater">
							<div data-repeater-list="items">
                                @if(empty($items))
                                <div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-3">
											<label class="control-label">Asin</label>
											<input type="text" class="form-control asin_input" {{$disabledForm}} name="asin" required>
								
								        </div>
                                        <div class="col-md-3">
                                            <label class="control-label">Sku</label>
											<input type="text" class="form-control sku_input" {{$disabledForm}} name="sku" required>
                                        </div>
										
										<div class="col-md-2">
                                            <label class="control-label">申请数量</label>
											<input type="text" class="form-control" name="quantity" {{$disabledForm}} required>
                                        </div>

										<div class="col-md-2">
											<label class="control-label">是否贴rms标</label>
											<select class="form-control" name="rms" id="rms" {{$disabledForm}} required>
											@foreach (\App\Models\TransferPlan::TF as $k=>$v)
											<option value="{{$k}}" {{($k==0)?'selected':''}}>{{$v}}</option>
											@endforeach 
											</select>
								
								        </div>
                                        <div class="col-md-2">
                                            <label class="control-label">是否抽卡</label>
											<select class="form-control" name="rcard" id="rcard" {{$disabledForm}} required>
											@foreach (\App\Models\TransferPlan::TF as $k=>$v)
											<option value="{{$k}}" {{($k==0)?'selected':''}} >{{$v}}</option>
											@endforeach 
											</select>
                                        </div>

										<div class="col-md-3">
                                            <label class="control-label">Seller Sku</label>
											<select name="sellersku" id="sellersku" class="form-control sellersku_input" {{$disabledForm}} required></select>
                                        </div>

										<div class="col-md-3">
                                            <label class="control-label">条码FNSKU</label>
											<input type="text" class="form-control" name="fnsku" {{$disabledForm}} required>
                                        </div>

										<!--
										<div class="col-md-2">
                                            <label class="control-label">预计箱数</label>
											<input type="text" class="form-control" name="packages" {{$disabledForm}} required>
											
                                        </div>
										-->
										<input type="hidden" name="image">
										<input type="hidden" name="seller_id">
                                        
                                        @if(!$disabledForm)
                                        <div class="col-md-2">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
                                        @endif
								    </div>
									<div style="clear:both;"></div>
								</div>
                                @else
								<?php 
								foreach($items as $key=>$value) { ?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row ">
										<div class="col-md-3">
											<label class="control-label">Asin</label>
											<input type="text" class="form-control asin_input" name="asin" value="{{$value['asin']}}" {{$disabledForm}} required>
								        </div>
                                        <div class="col-md-3">
                                            <label class="control-label">Sku</label>
											<input type="text" class="form-control sku_input" name="sku" value="{{$value['sku']}}" {{$disabledForm}} required>
                                        </div>
										
										<div class="col-md-2">
                                            <label class="control-label">申请数量</label>
											<input type="text" class="form-control" name="quantity" value="{{$value['quantity']}}" {{$disabledForm}} required>
                                        </div>

										<div class="col-md-2">
											<label class="control-label">是否贴rms标</label>
											<select class="form-control" name="rms" id="rms" {{$disabledForm}} required>
											@foreach (\App\Models\TransferPlan::TF as $k=>$v)
											<option value="{{$k}}" {{($k==array_get($value,'rms'))?'selected':''}} >{{$v}}</option>
											@endforeach 
											</select>
								
								        </div>
                                        <div class="col-md-2">
                                            <label class="control-label">是否抽卡</label>
											<select class="form-control" name="rcard" id="rcard" {{$disabledForm}}  required>
											@foreach (\App\Models\TransferPlan::TF as $k=>$v)
											<option value="{{$k}}" {{($k==array_get($value,'rcard'))?'selected':''}} >{{$v}}</option>
											@endforeach 
											</select>
                                        </div>

										<div class="col-md-3">
                                            <label class="control-label">Seller Sku</label>
											<select name="sellersku" id="sellersku" class="form-control sellersku_input" {{$disabledForm}} required>
                                                <option value="{{$value['sellersku']}}">{{$value['sellersku']}}
                                            </select>
                                        </div>
										<div class="col-md-3">
                                            <label class="control-label">条码FNSKU</label>
											<input type="text" class="form-control" name="fnsku" value="{{$value['fnsku']}}" {{$disabledForm}} required>
                                        </div>
										<!--
										
										<div class="col-md-2">
                                            <label class="control-label">预计箱数</label>
											<input type="text" class="form-control" name="packages" value="{{$value['packages']}}" {{$disabledForm}} required>
											
                                        </div>

										-->
										<input type="hidden" name="image" value="{{$value['image']}}">
										<input type="hidden" name="seller_id" value="{{$value['seller_id']}}">
                                        @if(!$disabledForm)
                                        <div class="col-md-2">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
                                        @endif
								    </div>
									<div style="clear:both;"></div>
								</div>
								<?php } ?>
                                @endif
							</div>
                            @if(!$disabledForm)
							<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
							<i class="fa fa-plus"></i> 添加SKU</a>
                            @endif
							<div style="clear:both;"></div>
						</div>
                        </div>
						
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                 <button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">关闭</button>
								 &nbsp;&nbsp;
                                 <input type="submit" name="update" value="保存" class="btn blue pull-right" >
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
		format: 'yyyy-mm-dd',
        autoclose: true
    });


	//FormRepeater.init();

	$('.mt-repeater').repeater({
		isFirstItemUndeletable: true,
		initEmpty: false,
		ready: function () {
			$('.asin_input').on('change',function(){
				var str = $(this).attr('name').slice(0,-6);
				getAjaxData(str)
			})
		},
		show: function () {
			$(this).slideDown();
			$('.asin_input').on('change',function(){
				var str = $(this).attr('name').slice(0,-6);
				getAjaxData(str)
			})
		},
		hide: function () {
			$(this).slideUp();
		},
	});

	function getAjaxData(str){
		if($("input[name='"+str+"[asin]']").val().length != 10) {
            $("input[name='"+str+"[sku]']").val('');
            $("input[name='"+str+"[seller_id]']").val('');
            $("input[name='"+str+"[image]']").val('');
            $("select[name='"+str+"[sellersku]']").empty();
        }else{
            $.ajax({
                type: "POST",
                url: "/transferPlan/getSellerSku",
                data: {
                    marketplace_id: $('#marketplace_id').val(),
                    asin: $("input[name='"+str+"[asin]']").val()
                },
                success: function (res) {
                    $("#seller_id").val(res.seller_id);
                    $("input[name='"+str+"[sku]']").val(res.sku);
                    $("input[name='"+str+"[seller_id]']").val(res.seller_id);
                    $("input[name='"+str+"[image]']").val(res.image);
                    $("select[name='"+str+"[sellersku]']").empty();
                    $.each(res.seller_sku_list, function (index, value) {
                        $("select[name='"+str+"[sellersku]']").append("<option value='"+value.seller_sku+"'>"+value.seller_sku+" - "+value.seller_id+"</option>");
                    });
                },
                error: function(err) {
                    console.log(err)
                }
            });
        }
		
	}

	$('#marketplace_id').on('change',function(){
		$('.asin_input').val('');
		$('.sku_input').val('');
		$('.sellersku_input').empty();
	})
	
    $('#update_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('/transferPlan/update') }}",
			data: $('#update_form').serialize(),
			success: function (data) {
				if(data.customActionStatus=='OK'){
                    $('#ajax').modal('hide');
					$('.modal-backdrop').remove();
                    toastr.success(data.customActionMessage);
                    var dttable = $('#datatable_ajax').dataTable();
					dttable.api().ajax.reload(null, false);
				}else{
					toastr.error(data.customActionMessage);
				}
			},
			error: function(data) {
                toastr.error(data.responseText);
			}
		});
		return false;
	});
});
</script>
