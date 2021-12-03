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

                        @if(array_get($form,'id'))
						<div class="form-group col-md-6">
                            <label>物料号 *</label>
                            <input type="text" class="form-control" name="sku" id="sku" value="{{array_get($form,'sku')}}" disabled>  
                        </div>
                        @else
                        <div class="form-group col-md-6">
                            <label>物料组 *</label>
                            <select class="form-control " name="item_group" id="item_group">
							@foreach($itemGroup as $key=>$val)
                                <option value="{!! $key !!}">{!! $val !!}</option>
                            @endforeach
							</select>
                        </div>
                        @endif

                        <div class="form-group col-md-6">
                            <label>站点 *</label>
                            <select class="form-control " name="site" id="site" {{array_get($form,'id')?'disabled':''}}>
							@foreach(getAsinSites() as $key=>$site)
								<option value="{!! $site !!}" {{($key==array_get($form,'site'))?'selected':''}}>{!! $site !!}</option>
							@endforeach
							</select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Sku状态 *</label>
                            <select class="form-control " name="status" id="status">
							@foreach(getSkuStatuses() as $k=>$v)
								<option value="{!! $k !!}" {{($k==array_get($form,'status'))?'selected':''}}>{!! $v !!}</option>
							@endforeach
							</select>
                        </div>


                        <div class="form-group col-md-6">
                            <label>Sku等级 *</label>
                            <select class="form-control " name="level" id="level">
							@foreach(getAsinStatus() as $k=>$v)
								<option value="{!! $k !!}" {{($k==array_get($form,'level'))?'selected':''}}>{!! $k !!}</option>
							@endforeach
							</select>
                        </div>
						
                        <div class="form-group col-md-12">
                            <label>产品名称 *</label>
                            <input type="text" class="form-control" name="description" id="description" value="{{array_get($form,'description')}}" required>  
                        </div>

                        <div class="form-group col-md-6">
                            <label>期初库存 *</label>
                            <input type="text" class="form-control" name="stock" id="stock" value="{{array_get($form,'stock')}}" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label>体积标准 *</label>
                            <select class="form-control " name="size" id="size">
							@foreach(getSkuSize() as $k=>$v)
								<option value="{!! $k !!}" {{($k==array_get($form,'size'))?'selected':''}}>{!! $v !!}</option>
							@endforeach
							</select>  
                        </div>

                        <div class="form-group col-md-6">
                            <label>退货率</label>
							<input type="text" class="form-control" value="{{array_get($form,'exception')}}" name="exception" id="exception" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}[%]$" placeholder="1.23%" required>
                        </div>

                        <div class="form-group col-md-6">
							<label>关税税率</label>
							<input type="text" class="form-control" value="{{array_get($form,'tax')}}" name="tax" id="tax" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}[%]$" placeholder="1.23%" required>
						</div>

						<div class="form-group col-md-6">
							<label>佣金比例</label>
							<input type="text" class="form-control" value="{{array_get($form,'common_fee')}}"  name="common_fee" id="common_fee" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}[%]$" placeholder="1.23%" required>
						</div>

						<div class="form-group col-md-6">
							<label>拣配费金额（外币）</label>
							<input type="text" class="form-control" value="{{round(array_get($form,'pick_fee'),2)}}" name="pick_fee" id="pick_fee" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}" placeholder="1.23" required>
						</div>

						<div class="form-group col-md-6">
							<label>产品体积cm３</label>
							<input type="text" class="form-control" value="{{round(array_get($form,'volume'),4)}}" name="volume" id="volume" pattern="^[0-9]*[.]{0,1}[0-9]{0,4}" placeholder="1.23" required>
						</div>
						<div class="form-group col-md-6">
							<label>不含税采购单价</label>
							<input type="text" class="form-control" value="{{round(array_get($form,'cost'),2)}}" name="cost" id="cost" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}" placeholder="1.23" required>
						</div>

						<div class="form-group col-md-6">
							<label>销售员</label>
							<select class="mt-multiselect btn btn-default form-control" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="sap_seller_id" id="sap_seller_id">
								@foreach (getUsers('sap_seller') as $sap_seller_id=>$user_name)
									<option value="{{$sap_seller_id}}" {{($sap_seller_id==array_get($form,'sap_seller_id'))?'selected':''}}>{{$user_name}}</option>
								@endforeach
							</select>
						</div>

						<div class="form-group col-md-6">
							<label>计划员</label>
							<select class="mt-multiselect btn btn-default form-control" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="planer_id" id="planer_id">
								@foreach (getUsers() as $sap_seller_id=>$user_name)
									<option value="{{$sap_seller_id}}" {{($sap_seller_id==array_get($form,'planer'))?'selected':''}}>{{$user_name}}</option>
								@endforeach
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
			url: "{{ url('budgetSku') }}",
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
