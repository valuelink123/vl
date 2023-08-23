<?php
$disabledForm = ' disabled ';
if(in_array(array_get($form,'tstatus'),[0,1,2,3,4,8])) $disabledForm="";
?>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>Transfer Plan</h1></div>
            <div class="portlet-body">

                <form id="update_form"  name="update_form" >
                    {{ csrf_field() }}
                    <div class="form-body">
                    <input type="hidden" name="id" value="{{array_get($form,'id',0)}}">
                    <input type="hidden" name="api_msg">
                    <div class="row">

                        <div class="col-md-3">
                        <div class="form-group">
                            <label>Ship Status:</label>
                            <select class="form-control" name="tstatus" id="tstatus" {{in_array(array_get($form,'tstatus'),[1,2,3,4,8])?'':'disabled'}} >
                            @foreach (\App\Models\TransferPlan::SHIPMENTSTATUS as $k=>$v)
                            <option value="{{$k}}" {{($k==array_get($form,'tstatus'))?'selected':''}} {{in_array($k,[1,2,3,4,5,5,5,5,5,8])?'':'disabled'}} >{{$v}}</option>
                            @endforeach 
                            </select>
                        </div>
                        </div>

                        <div class="col-md-3">

                        <div class="form-group">
                            <label>Actual Ship Date:</label>
                            <input type="text" class="form-control date-picker" readonly name="ship_date" id="ship_date" {{$disabledForm}} value="{{array_get($form,'ship_date')}}" >
                        </div>
                        </div>

                        <div class="col-md-3">

                        <div class="form-group">
                            <label>Reservation Date:</label>
                            <input type="text" class="form-control date-picker" readonly name="reservation_date" {{$disabledForm}} id="reservation_date" value="{{array_get($form,'reservation_date')}}" >
                        </div>
                        </div>

					
                        <div class="col-md-12" style="text-align:left;font-size:14px; margin-bottom:10px;"><span class="label label-sm label-primary">{{$form['reson']}}</span> <span class="label label-sm label-danger">{{$form['remark']}}</span></div>

					</div>	
                        <?php
                        foreach($items as $key=>$item){
                            $ships=array_get($item,'ships',[]);
                        ?>
                        
                        <div class="row" style="margin-bottom:10px;">
                        <div class="col-md-2"><image src="https://images-na.ssl-images-amazon.com/images/I/{{$item['image']}}" width=100%></div>
                        <div class="col-md-10" style="text-align:left;font-size:14px;">
                            <div class="col-md-6">DA SKU : {{array_get($daSkus, $item['sku'], $item['sku'])}}</div>
                            <div class="col-md-6">FNSKU : {{$item['fnsku']}}</div>
                            <div class="col-md-6">Warehouse : {{array_get($item,'warehouse_code')}}</div>
                            <div class="col-md-6">Quantity : {{intval(array_get($item,'quantity'))}}</div>
                            <div class="col-md-6">Pallet Count : {{$item['broads']}}</div>
                            <div class="col-md-6">Boxes Count : {{$item['packages']}}</div>
                            <div class="col-md-6">RMS : {{array_get(\App\Models\TransferPlan::TF,$item['rms'])}}</div>
                            <div class="col-md-6">Remove Card : {{array_get(\App\Models\TransferPlan::TF,$item['rcard'])}}</div>
                            <div class="col-md-12">Address : {{array_get($warehouses,array_get($item,'warehouse_code').'.address')}} {{array_get($warehouses,array_get($item,'warehouse_code').'.state')}} {{array_get($warehouses,array_get($item,'warehouse_code').'.city')}}  {{array_get($warehouses,array_get($item,'warehouse_code').'.zip')}}</div>
                        </div></div>

                        

                        <div class="form-group mt-repeater mt-repeater-{{$item['id']}}">
							<div data-repeater-list="ships[{{$item['id']}}]">
                                @if(empty($ships))
                                <div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">

                                        <div class="col-md-2">
                                            <label class="control-label">Sku</label>
											<input type="text" class="form-control" name="sku" value="{{array_get($daSkus, $item['sku'], $item['sku'])}}" {{$disabledForm}} required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Location</label>
											<input type="text" class="form-control" name="location" {{$disabledForm}} required>
                                        </div>

										<div class="col-md-2">
                                            <label class="control-label">Quantity</label>
											<input type="text" class="form-control" name="quantity" value="1" {{$disabledForm}}  required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Actual Pallet</label>
											<input type="text" class="form-control" name="broads" value="0" {{$disabledForm}} required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Actual Boxes</label>
											<input type="text" class="form-control" name="packages" value="0" {{$disabledForm}}  required>
                                        </div>

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
								foreach($ships as $key=>$value) { ?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row ">
                                        <div class="col-md-2">
                                            <label class="control-label">Sku</label>
											<input type="text" class="form-control" name="sku" value="{{$value['sku']}}" {{$disabledForm}} required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Location</label>
                                            <input type="hidden" name="sku" id="sku" value="{{$value['sku']}}">
											<input type="text" class="form-control" name="location" value="{{$value['location']}}" {{$disabledForm}} required>
                                        </div>

										<div class="col-md-2">
                                            <label class="control-label">Quantity</label>
											<input type="text" class="form-control" name="quantity" value="{{$value['quantity']}}" {{$disabledForm}} required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Actual Pallet</label>
											<input type="text" class="form-control" name="broads" value="{{$value['broads']}}" {{$disabledForm}} required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Actual Boxes</label>
											<input type="text" class="form-control" name="packages" value="{{$value['packages']}}" {{$disabledForm}} required>
                                        </div>
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
							<i class="fa fa-plus"></i> Add Info</a>
                            @endif
						</div>
                        
                        <div style="clear:both;"></div>
                        <script>
                        $(function() {            
                            $('.mt-repeater-{{$item["id"]}}').repeater({
                                defaultValues: {
                                    'sku': '{{array_get($daSkus, $item["sku"], $item["sku"])}}',
                                    'quantity': '1',
                                    'broads': '0',
                                    'packages': '0',
                                },
                                show: function () {
                                    $(this).slideDown();
                                },
                                hide: function (deleteElement) {
                                    $(this).slideUp(deleteElement);
                                },
                            });
                        });
                        </script>
                        <?php
                        }
                        ?>
                        
                        
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                    &nbsp;&nbsp;
                                    <input type="submit" name="update" value="Save" class="btn blue pull-right" >
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
    /*
    FormRepeater.init();
    
	$('.mt-repeater').repeater({
		isFirstItemUndeletable: true
	});
	*/
    $('#update_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('/daPlan/update') }}",
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
