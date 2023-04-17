<?php
$disabledForm = ' disabled ';
if(in_array(array_get($form,'tstatus'),[0,1,2,3,4,5,8])) $disabledForm="";
?>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>调拨计划</h1></div>
            <div class="portlet-body">
            <?php
            $str = '';
            $daSkuSelect = [];

            if(is_array($items)){
                foreach($items as $item){
                    $str .= '<div class="row" style="margin-bottom:5px;"><div class="col-md-2"><image src="https://images-na.ssl-images-amazon.com/images/I/'.$item['image'].'" width=100% height=100% ></div>
                    <div class="col-md-10" style="text-align:left;">
                    <div class="col-md-6">SKU : '.$item['sku'].'</div>
                    <div class="col-md-6">FNSKU : '.$item['fnsku'].'</div>
                    <div class="col-md-6">Asin : '.$item['asin'].'</div>
                    <div class="col-md-6">SellerSku : '.$item['sellersku'].'</div>
                    <div class="col-md-6">数量 : '.intval(array_get($item,'quantity')).'</div>
                    <div class="col-md-6">单箱数量 : '.intval(array_get($item,'per_package_qty')).'</div>
                    <div class="col-md-6">预计卡板数 : '.$item['broads'].'</div>
                    <div class="col-md-6">预计箱数 : '.$item['packages'].'</div>
                    <div class="col-md-6">RMS : '.array_get(\App\Models\TransferPlan::TF,$item['rms']).'</div>
                    <div class="col-md-6">抽卡 : '.array_get(\App\Models\TransferPlan::TF,$item['rcard']).'</div>
                    <div class="col-md-12">预计运费:'.$item['ship_fee'].'</div>
                    </div></div>';
                }
            }
            $str .= '<div class="col-md-12" style="textc -align:left;"><span class="label label-sm label-primary">'.array_get($form,'reson').'</span> <span class="label label-sm label-danger">'.array_get($form,'remark').'</span></div>';
            $items = $ships;
            if(is_array($items)){
                $str .= '<div style="padding: 10px;
                clear: both;
                border-bottom: 1px solid #eee;"><h2>DA实际发货</h2></div>';
                foreach($items as $item){
                    if(!isset($daSkuShips[$item['sku']])){     
                        $daSkuShips[$item['sku']] = 
                        [
                            'quantity'=>0,
                            'broads'=>0,
                            'packages'=>0,
                            'locations'=>[]
                        ];
                    }
                    $daSkuShips[$item['sku']]['quantity'] += intval($item['quantity']);
                    $daSkuShips[$item['sku']]['broads'] += intval($item['broads']);
                    $daSkuShips[$item['sku']]['packages'] += intval($item['packages']);
                    $daSkuShips[$item['sku']]['locations'][]= $item['location'];
                }
                foreach($daSkuShips as $key=>$item){
                    $str .= '<div class="row" style="margin:10px;">
                    <div class="col-md-12" style="text-align:left;">
                    <div class="col-md-6">DASKU : '.$key.'</div>
                    <div class="col-md-6">数量 : '.intval($item['quantity']).'</div>
                    <div class="col-md-6">实际卡板数 : '.intval($item['broads']).'</div>
                    <div class="col-md-6">实际箱数 : '.intval($item['packages']).'</div>
                    <div class="col-md-12">Locations : '.implode(', ',$item['locations']).'</div>
                    </div></div>';
                }
            }

            $str .= '<div style="margin: 10px 0; clear: both; border-bottom: 1px solid #eee;"></div>';
            echo $str;
            
            ?>

                    <form id="update_form"  name="update_form" >
                        {{ csrf_field() }}
                        <div class="form-body">
                        <input type="hidden" name="id" value="{{array_get($form,'id',0)}}">
						<div class="row">

						<div class="col-md-6">
						<div class="form-group">
							<label>Ship Status:</label>
							<select class="form-control" name="tstatus" id="tstatus" {{$disabledForm}}>
							@foreach (\App\Models\TransferPlan::SHIPSHIPMENTSTATUS as $k=>$v)
							<option value="{{$k}}" {{($k==array_get($form,'tstatus'))?'selected':''}} >{{$v}}</option>
							@endforeach 
							</select>
						</div>
						</div>

						<div class="col-md-6">

						<div class="form-group">
							<label>Ship Fee:</label>
                            <input type="text" class="form-control" name="ship_fee" id="ship_fee" {{$disabledForm}} value="{{array_get($form,'ship_fee')}}" required>
						</div>
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
			url: "{{ url('/shipPlan/update') }}",
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
