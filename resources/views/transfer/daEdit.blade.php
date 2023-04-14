<?php
$disabledForm = ' disabled ';
if(in_array(array_get($form,'tstatus'),[0,1,2,3,8])) $disabledForm="";
?>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>Transfer Plan</h1></div>
            <div class="portlet-body">
            <?php
            $str = '';
            $daSkuSelect = [];
            foreach($items as $item){
                $str .= '<div class="row" style="margin-bottom:5px;">
                <div class="col-md-2"><image src="https://images-na.ssl-images-amazon.com/images/I/'.$item['image'].'" width=100%></div>
                <div class="col-md-10" style="text-align:left;font-size:14px;">
                    <div class="col-md-6">DA SKU : '.array_get($daSkus, $item['sku'], $item['sku']).'</div>
                    <div class="col-md-6">FNSKU : '.$item['fnsku'].'</div>
                    <div class="col-md-12">Quantity : '.intval(array_get($item,'quantity')).'</div>
                    <div class="col-md-6">Broads : '.$item['broads'].'</div>
                    <div class="col-md-6">Packages : '.$item['packages'].'</div>
                    <div class="col-md-6">RMS : '.array_get(\App\Models\TransferPlan::TF,$item['rms']).'</div>
                    <div class="col-md-6">Remove Card : '.array_get(\App\Models\TransferPlan::TF,$item['rcard']).'</div>
                </div></div>';
                $daSkuSelect[] = array_get($daSkus, $item['sku'], $item['sku']);
            }
            $str .= '<div class="col-md-12" style="textc -align:left;font-size:14px; margin-bottom:10px;"><span class="label label-sm label-primary">'.$form['reson'].'</span> <span class="label label-sm label-danger">'.$form['remark'].'</span></div>';
            echo $str;
            ?>

                    <form id="update_form"  name="update_form" >
                        {{ csrf_field() }}
                        <div class="form-body">
                        <input type="hidden" name="id" value="{{array_get($form,'id',0)}}">
						<div class="row">

						<div class="col-md-3">
						<div class="form-group">
							<label>Ship Status:</label>
							<select class="form-control" name="tstatus" id="tstatus" {{$disabledForm}} >
							@foreach (\App\Models\TransferPlan::DASHIPMENTSTATUS as $k=>$v)
							<option value="{{$k}}" {{($k==array_get($form,'tstatus'))?'selected':''}} >{{$v}}</option>
							@endforeach 
							</select>
						</div>
						</div>

						<div class="col-md-3">

						<div class="form-group">
							<label>Actual Ship Date:</label>
                            <input type="text" class="form-control date-picker" name="ship_date" id="ship_date" {{$disabledForm}} value="{{array_get($form,'ship_date')}}" required>
						</div>
						</div>

                        <div class="col-md-3">

						<div class="form-group">
							<label>Reservation Date:</label>
                            <input type="text" class="form-control date-picker" name="reservation_date" {{$disabledForm}} id="reservation_date" value="{{array_get($form,'reservation_date')}}" required>
						</div>
						</div>

					

						</div>				

						<div class="form-group mt-repeater">
							<div data-repeater-list="ships">
                                @if(empty($ships))
                                <div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
                                    <div class="col-md-2">
                                            <label class="control-label">Sku</label>
                                            <select class="form-control" name="sku" id="sku" {{$disabledForm}} required>
                                            @foreach ($daSkuSelect as $k)
                                            <option value="{{$k}}">{{$k}}</option>
                                            @endforeach 
                                            </select>
                                        </div>
										
                                        <div class="col-md-2">
                                            <label class="control-label">Location</label>
											<input type="text" class="form-control" name="location" {{$disabledForm}} required>
                                        </div>

										<div class="col-md-2">
                                            <label class="control-label">Quantity</label>
											<input type="text" class="form-control" name="quantity" {{$disabledForm}}  required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Actual Broads</label>
											<input type="text" class="form-control" name="broads" {{$disabledForm}} required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Actual Packages</label>
											<input type="text" class="form-control" name="packages" {{$disabledForm}}  required>
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
											<select class="form-control" name="sku" id="sku" {{$disabledForm}} required>
                                            @foreach ($daSkuSelect as $k)
                                            <option value="{{$k}}" {{($k==array_get($value,'sku'))?'selected':''}} >{{$k}}</option>
                                            @endforeach 
                                            </select>
                                        </div>
										
                                        <div class="col-md-2">
                                            <label class="control-label">Location</label>
											<input type="text" class="form-control" name="location" value="{{$value['location']}}" {{$disabledForm}} required>
                                        </div>

										<div class="col-md-2">
                                            <label class="control-label">Quantity</label>
											<input type="text" class="form-control" name="quantity" value="{{$value['quantity']}}" {{$disabledForm}} required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Actual Broads</label>
											<input type="text" class="form-control" name="broads" value="{{$value['broads']}}" {{$disabledForm}} required>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="control-label">Actual Packages</label>
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
							<i class="fa fa-plus"></i> Add SKU</a>
                            @endif
							<div style="clear:both;"></div>
						</div>
                        </div>
						
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

	$('.mt-repeater').repeater({
		isFirstItemUndeletable: true
	});
	
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
