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
                        <div class="form-group col-md-6">
                            <label>平台 *</label>
                            <select class="form-control " name="platform" id="platform">
							<?php 
							foreach(\App\Models\PlatformOrder::PLATFORM as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'platform'))?'selected':'').'>'.$v.'</option>';
							}?>
							</select>
                        </div>


                        <div class="form-group col-md-6">
                            <label>国家代码 *</label>
                            <input type="text" class="form-control" name="country_code" id="country_code" value="{{array_get($form,'country_code')}}" >  
                        </div>
						
						<div class="form-group col-md-12">
                            <label>平台SKU *</label>
                            <input type="text" class="form-control" name="platform_sku" id="platform_sku" value="{{array_get($form,'platform_sku')}}" >  
                        </div>

                        <div class="form-group col-md-12">
                            <label>谷仓SKU *</label>
                            <select class="mt-multiselect form-control " name="product_sku" id="product_sku" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
							<?php 
							foreach(\App\Models\GuCangSku::pluck('product_title_cn','product_sku') as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==array_get($form,'product_sku'))?'selected':'').'>'.$k.' - '.$v.'</option>';
							}?>
							</select>
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
			url: "{{ url('platformsku') }}",
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
});
var MultiselectInit=function(){return{init:function(){$("#update_form .mt-multiselect").each(function(){var t,a=$(this).attr("class"),i=$(this).data("clickable-groups")?$(this).data("clickable-groups"):!1,l=$(this).data("collapse-groups")?$(this).data("collapse-groups"):!1,o=$(this).data("drop-right")?$(this).data("drop-right"):!1,e=($(this).data("drop-up")?$(this).data("drop-up"):!1,$(this).data("select-all")?$(this).data("select-all"):!1),s=$(this).data("width")?$(this).data("width"):"",n=$(this).data("height")?$(this).data("height"):"",d=$(this).data("filter")?$(this).data("filter"):!1,h=function(t,a,i){},r=function(t){alert("Dropdown shown.")},c=function(t){alert("Dropdown Hidden.")},p=1==$(this).data("action-onchange")?h:"",u=1==$(this).data("action-dropdownshow")?r:"",b=1==$(this).data("action-dropdownhide")?c:"";t=$(this).attr("multiple")?'<li class="mt-checkbox-list"><a href="javascript:void(0);"><label class="mt-checkbox"> <span></span></label></a></li>':'<li><a href="javascript:void(0);"><label></label></a></li>',$(this).multiselect({enableClickableOptGroups:i,enableCollapsibleOptGroups:l,disableIfEmpty:!0,enableFiltering:d,includeSelectAllOption:e,dropRight:o,buttonWidth:s,maxHeight:n,onChange:p,onDropdownShow:u,onDropdownHide:b,buttonClass:a})})}}}();jQuery(document).ready(function(){MultiselectInit.init()});
</script>
