<?php
$disabledForm = ' disabled ';
if(in_array(array_get($form,'tstatus'),[5,6,7,8])) $disabledForm="";
?>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
        <div style="padding: 10px;
                clear: both;
                border-bottom: 1px solid #eee;"><h2>调拨计划</h2></div>
            <div class="portlet-body">

            <form id="update_form"  name="update_form" >
            {{ csrf_field() }}
            <div class="form-body">
            <input type="hidden" name="id" value="{{array_get($form,'id',0)}}">
            <input type="hidden" name="api_msg">
            <div class="row">

            <div class="col-md-6">
            <div class="form-group">
                <label>调拨状态:</label>
                <select class="form-control" name="tstatus" id="tstatus" {{in_array(array_get($form,'tstatus'),[5,6,7,8])?'':'disabled'}}>
                @foreach (\App\Models\TransferPlan::SHIPMENTSTATUS as $k=>$v)
                <option value="{{$k}}" {{($k==array_get($form,'tstatus'))?'selected':''}} {{in_array($k,[5,6,7,8])?'':'disabled'}}>{{$v}}</option>
                @endforeach 
                </select>
            </div>
            </div>

            <div class="col-md-6">

            <div class="form-group">
                <label>运费:</label>
                <input type="text" class="form-control" name="ship_fee" id="ship_fee" {{$disabledForm}} value="{{array_get($form,'ship_fee')}}" required>
            </div>
            </div>

		<div class="col-md-6">

            <div class="form-group">
                <label>材积重:</label>
                <input type="text" class="form-control" name="weight" id="weight" {{$disabledForm}} value="{{array_get($form,'weight')}}" required>
            </div>
            </div>

		<div class="col-md-6">

            <div class="form-group">
                <label>体积重:</label>
                <input type="text" class="form-control" name="volume" id="volume" {{$disabledForm}} value="{{array_get($form,'volume')}}" required>
            </div>
            </div>
            </div>
            <div class="col-md-12" style="text-align:left;"><span class="label label-sm label-primary">{{array_get($form,'reson')}}</span> <span class="label label-sm label-danger">{{array_get($form,'remark')}}</span></div>
            
            <div style="clear:both;"></div>
            <?php
            foreach($items as $key=>$item){
                $ships=array_get($item,'ships',[]);
            ?>
            
            <div class="row" style="margin:10px;">
            <div class="col-md-2"><image src="https://images-na.ssl-images-amazon.com/images/I/{{$item['image']}}" width=100%></div>
            <div class="col-md-10" style="text-align:left;font-size:14px;">
                <div class="col-md-6">SKU : {{$item['sku']}}</div>
                <div class="col-md-6">FNSKU : {{$item['fnsku']}}</div>
                <div class="col-md-6">Asin : {{$item['asin']}}</div>
                <div class="col-md-6">SellerSku : {{$item['sellersku']}}</div>
                <div class="col-md-6">仓库代码 : {{array_get($item,'warehouse_code')}}</div>
                <div class="col-md-6">数量 : {{intval(array_get($item,'quantity'))}}</div>
                <div class="col-md-6">预计卡板数 : {{$item['broads']}}</div>
                <div class="col-md-6">预计箱数 : {{$item['packages']}}</div>
                <div class="col-md-6">RMS : {{array_get(\App\Models\TransferPlan::TF,$item['rms'])}}</div>
                <div class="col-md-6">抽卡 : {{array_get(\App\Models\TransferPlan::TF,$item['rcard'])}}</div>
                <div class="col-md-12">地址 : {{array_get($warehouses,array_get($item,'warehouse_code').'.address')}} {{array_get($warehouses,array_get($item,'warehouse_code').'.state')}} {{array_get($warehouses,array_get($item,'warehouse_code').'.city')}}  {{array_get($warehouses,array_get($item,'warehouse_code').'.zip')}}</div>
            </div>
			<div style="clear:both;"></div>
	@if(array_get($form,'tstatus')==7)
	    <div class="row">
	<div class="col-md-4">
	<div class="form-group">
		<label>ST0:</label>
		<input type="text" class="form-control" name="items[{{array_get($item,'id')}}][sap_st0]" {{$disabledForm}} value="{{array_get($item,'sap_st0')}}" required>
	</div>
	</div>
	<div class="col-md-4">
	<div class="form-group">
		<label>DN:</label>
		<input type="text" class="form-control" name="items[{{array_get($item,'id')}}][sap_dn]" {{$disabledForm}} value="{{array_get($item,'sap_dn')}}" required>
	</div>
	</div>
	<div class="col-md-4">
	<div class="form-group">
		<label>TM:</label>
		<input type="text" class="form-control" name="items[{{array_get($item,'id')}}][sap_tm]" {{$disabledForm}} value="{{array_get($item,'sap_tm')}}" required>
	</div>
	</div>
</div>
	@endif
	    </div>
            <div class="row" style="margin:10px;">
            @if(!empty($ships))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>DA 发货详情</th>
                        <th> Sku </th>
                        <th> location </th>
                        <th> 数量 </th>
                        <th> 实际卡板数 </th>
                        <th> 实际箱数 </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i= $quantity = $broads = $packages =0;
                foreach($ships as $ship){
                    $i++;
                    $quantity+=intval($ship['quantity']);
                    $broads+=intval($ship['broads']);
                    $packages+=intval($ship['packages']);
                ?>
                    <tr>
                        <td># {{$i}}</td>
                        <td>{{$ship['sku']}} </td>
                        <td> {{$ship['location']}} </td>
                        <td> {{intval($ship['quantity'])}} </td>
                        <td> {{intval($ship['broads'])}} </td>
                        <td>
                        {{intval($ship['packages'])}}
                        </td>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td>合计</td>
                    <td> {{$quantity}} </td>
                    <td> {{$broads}} </td>
                    <td>{{$packages}}</td>
                </tr>
                </tbody>
            </table>
            @endif
            </div>
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
