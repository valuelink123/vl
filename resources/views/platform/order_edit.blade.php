<div class="row"><div class="col-md-12">
        <div class="portlet light bordered">
			<h1 class="page-title font-red-intense">编辑记录
			</h1>
            <div class="portlet-body form">
                <form id="update_form"  name="update_form" >
                    {{ csrf_field() }}
					
                    <div class="form-body">
                        
						
                        <div class="divider" style="clear: both;width: 100%;height: 2px;background: #ccc; margin-bottom: 20px;"></div>
                        <input type="hidden" name="id" value="{{array_get($form,'id')}}">
                        <div class="form-group col-md-3">
                            <label>平台 *</label>
                            <select class="form-control " name="platform" id="platform">
							<?php 
							foreach(\App\Models\PlatformOrder::PLATFORM as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'platform'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label>平台订单号 *</label>
                            <input type="text" class="form-control" name="reference_no" id="reference_no" value="{{array_get($form,'reference_no')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>谷仓订单号 </label>
                            <input type="text" class="form-control" name="order_code" id="order_code" value="{{array_get($form,'order_code')}}" readonly disabled>
                        </div>
						
						<div class="form-group col-md-3">
                            <label>平台发货方式 *</label>
                            <input type="text" class="form-control" name="shipping_method" id="shipping_method" value="{{array_get($form,'shipping_method')}}" >  
                        </div>

                        <div class="form-group col-md-12">
                            <label>订单备注 </label>
                            <input type="text" class="form-control" name="order_desc" id="order_desc" value="{{array_get($form,'order_desc')}}" >  
                        </div>
                        

                        <div class="divider" style="clear: both;width: 100%;height: 2px;background: #ccc; margin-bottom: 20px;"></div>

                        <div class="form-group mt-repeater col-md-12">
                        <h3 class="mt-repeater-title">订单明细 *</h3>
							<div data-repeater-list="items">
                                @if(empty(array_get($form,'items')))
                                <div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-2">
											<label class="control-label">SKU *</label>
											<input type="text" class="form-control" name="product_sku" required>
								
								        </div>
                                        <div class="col-md-2">
                                            <label class="control-label">Quantity *</label>
											<input type="text" class="form-control rankFormat" name="quantity" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="control-label">OrderItemID</label>
											<input type="text" class="form-control rankFormat" name="item_id" >
                                        </div>
                                        <div class="col-md-3">
                                            <label class="control-label">TransactionID</label>
											<input type="text" class="form-control rankFormat" name="transaction_id" >
                                        </div>
											
				
                                        <div class="col-md-2">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
								    </div>
								</div>
                                @else
								<?php 
								foreach(array_get($form,'items') as $k=>$val) { ?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-2">
											<label class="control-label">SKU *</label>
											<input type="text" class="form-control" value="{{$val['product_sku']}}" name="product_sku" required>
								        </div>
                                        <div class="col-md-2">
                                            <label class="control-label">Quantity *</label>
											<input type="text" class="form-control" value="{{$val['quantity']}}" name="quantity" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="control-label">OrderItemID</label>
											<input type="text" class="form-control" value="{{$val['item_id']}}" name="item_id" >
                                        </div>
                                        <div class="col-md-3">
                                            <label class="control-label">TransactionID</label>
											<input type="text" class="form-control" value="{{$val['transaction_id']}}" name="transaction_id" >
                                        </div>
                                        <div class="col-md-2">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
								    </div>
								</div>
								<?php } ?>
                                @endif
							</div>
							<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
								<i class="fa fa-plus"></i> Add Item</a>
						</div>

                        <div class="divider" style="clear: both;width: 100%;height: 2px;background: #ccc; margin-bottom: 20px;"></div>

                        <div class="form-group col-md-3">
                            <label>客户姓名 *</label>
                            <input type="text" class="form-control" name="name" id="name" value="{{array_get($form,'name')}}" >  
                        </div>
						
                        <div class="form-group col-md-3">
                            <label>国家代码 *</label>
                            <input type="text" class="form-control" name="country_code" id="country_code" value="{{array_get($form,'country_code')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>州/省 *</label>
                            <input type="text" class="form-control" name="province" id="province" value="{{array_get($form,'province')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>城市  *</label>
                            <input type="text" class="form-control" name="city" id="city" value="{{array_get($form,'city')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>地址1  *</label>
                            <input type="text" class="form-control" name="address1" id="address1" value="{{array_get($form,'address1')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>地址2 </label>
                            <input type="text" class="form-control" name="address2" id="address2" value="{{array_get($form,'address2')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>地址3 </label>
                            <input type="text" class="form-control" name="address3" id="address3" value="{{array_get($form,'address3')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>公司名称 </label>
                            <input type="text" class="form-control" name="company" id="company" value="{{array_get($form,'company')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>邮编  *</label>
                            <input type="text" class="form-control" name="zipcode" id="zipcode" value="{{array_get($form,'zipcode')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>门牌号 </label>
                            <input type="text" class="form-control" name="doorplate" id="doorplate" value="{{array_get($form,'doorplate')}}" >  
                        </div>


						<div class="form-group col-md-3">
                            <label>客户邮箱 </label>
                            <input type="text" class="form-control" name="email" id="email" value="{{array_get($form,'email')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>客户电话 *</label>
                            <input type="text" class="form-control" name="phone" id="phone" value="{{array_get($form,'phone')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>客户手机号 </label>
                            <input type="text" class="form-control" name="cell_phone" id="cell_phone" value="{{array_get($form,'cell_phone')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>FBA Shipment ID </label>
                            <input type="text" class="form-control" name="fba_shipment_id" id="fba_shipment_id" value="{{array_get($form,'fba_shipment_id')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>FBA Shipment ID创建时间 </label>
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" name="out_date" value="{{array_get($form,'fba_shipment_id_create_time')}}" >
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>	  
                        </div>

                        <div class="divider" style="clear: both;width: 100%;height: 2px;background: #ccc; margin-bottom: 20px;"></div>
                        

                        <div class="form-group col-md-3">
                            <label>是否直接审核</label>
                            <select class="form-control " name="verify" id="verify">
							<?php 
                            $status = ['0'=>'否','1'=>'是'];
							foreach($status as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'verify'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>  
                        </div>


                        <div class="form-group col-md-3">
                            <label>是否允许更改物流产品</label>
                            <select class="form-control " name="is_shipping_method_not_allow_update" id="is_shipping_method_not_allow_update">
							<?php 
                            $status = ['0'=>'否','1'=>'是'];
							foreach($status as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'is_shipping_method_not_allow_update'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>  
                        </div>

                        <div class="form-group col-md-3">
                            <label>签名服务</label>
                            <select class="form-control " name="is_signature" id="is_signature">
							<?php 
                            $status = ['0'=>'否','1'=>'是'];
							foreach($status as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'is_signature'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>  
                        </div>

                        <div class="form-group col-md-3">
                            <label>保险服务</label>
                            <select class="form-control " name="is_insurance" id="is_insurance">
							<?php 
                            $status = ['0'=>'否','1'=>'是'];
							foreach($status as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'is_insurance'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>  
                        </div>

                        <div class="form-group col-md-3">
                            <label>保额 </label>
                            <input type="text" class="form-control" name="insurance_value" id="insurance_value" onkeyup="value=value.replace(/[^\d]/g,'')" value="{{array_get($form,'insurance_value')}}" >  
                        </div>

                        <div class="form-group col-md-3">
                            <label>换标服务</label>
                            <select class="form-control " name="is_change_label" id="is_change_label">
							<?php 
                            $status = ['0'=>'否','1'=>'是'];
							foreach($status as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'is_change_label'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>  
                        </div>

                        <div class="form-group col-md-3">
                            <label>LiftGate服务</label>
                            <select class="form-control " name="LiftGate" id="LiftGate">
							<?php 
                            $status = ['0'=>'否','1'=>'是'];
							foreach($status as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'LiftGate'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>  
                        </div>

                        <div class="form-group col-md-3">
                            <label>年龄检测服务</label>
                            <select class="form-control " name="age_detection" id="age_detection">
							<?php 
                            $status = ['0'=>'否','16'=>'是16','18'=>'是18'];
							foreach($status as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'age_detection'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>  
                        </div>

                        <div class="divider" style="clear: both;width: 100%;height: 2px;background: #ccc; margin-bottom: 20px;"></div>
    
                        <div class="form-group col-md-12">
                            {{array_get($form,'sync_message')}}
                        </div>


                        <div style="clear:both;"></div>
                    </div>
					
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-12">

								<button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
								
                                <input type="submit" name="update" value="Update" class="btn blue pull-right" >
								
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>


<script type="text/javascript">

$(function() {
    
    $('#update_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('platformorder') }}",
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
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		autoclose: true
	});
    FormRepeater.init();
});

var MultiselectInit=function(){return{init:function(){$("#update_form .mt-multiselect").each(function(){var t,a=$(this).attr("class"),i=$(this).data("clickable-groups")?$(this).data("clickable-groups"):!1,l=$(this).data("collapse-groups")?$(this).data("collapse-groups"):!1,o=$(this).data("drop-right")?$(this).data("drop-right"):!1,e=($(this).data("drop-up")?$(this).data("drop-up"):!1,$(this).data("select-all")?$(this).data("select-all"):!1),s=$(this).data("width")?$(this).data("width"):"",n=$(this).data("height")?$(this).data("height"):"",d=$(this).data("filter")?$(this).data("filter"):!1,h=function(t,a,i){},r=function(t){alert("Dropdown shown.")},c=function(t){alert("Dropdown Hidden.")},p=1==$(this).data("action-onchange")?h:"",u=1==$(this).data("action-dropdownshow")?r:"",b=1==$(this).data("action-dropdownhide")?c:"";t=$(this).attr("multiple")?'<li class="mt-checkbox-list"><a href="javascript:void(0);"><label class="mt-checkbox"> <span></span></label></a></li>':'<li><a href="javascript:void(0);"><label></label></a></li>',$(this).multiselect({enableClickableOptGroups:i,enableCollapsibleOptGroups:l,disableIfEmpty:!0,enableFiltering:d,includeSelectAllOption:e,dropRight:o,buttonWidth:s,maxHeight:n,onChange:p,onDropdownShow:u,onDropdownHide:b,buttonClass:a})})}}}();jQuery(document).ready(function(){MultiselectInit.init()});
</script>
