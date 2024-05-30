
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>平台SKU库存</h1></div>
            <div class="portlet-body">

                <form id="update_form"  name="update_form" >
                    {{ csrf_field() }}
                    <div class="form-body">

                        <div class="row">


                            <div class="col-md-4">

                                <div class="form-group">
                                    <label>Sku:</label>
                                    <input type="text" class="form-control"  name="sku" id="sku" value="{{array_get($form,'sku')}}" <?php if(!Auth::user()->sap_seller_id || array_get($form,'id')>0) echo 'readonly'; ?>>
                                </div>
                            </div>

                            <div class="col-md-4">

                                <div class="form-group">
                                    <label>货好未提:</label>
                                    <input type="text" class="form-control"  name="unpicked" id="unpicked" value="{{array_get($form,'unpicked',0)}}" <?php if(Auth::user()->sap_seller_id) echo 'readonly'; ?> >
                                </div>
                            </div>
							
							<div class="col-md-4">

                                <div class="form-group">
                                    <label>已申请调拨:</label>
                                    <input type="text" class="form-control"  name="in_transit" id="in_transit" value="{{array_get($form,'in_transit',0)}}" <?php if(!Auth::user()->sap_seller_id) echo 'readonly'; ?> >
                                </div>
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
				@if(array_get($form,'id')>0 && Auth::user()->sap_seller_id )
				<form action="{{ url('otherSku/'.array_get($form,'id')) }}" method="POST" style="display: inline;">
					{{ method_field('DELETE') }}
					{{ csrf_field() }}
					<button type="submit" class="btn btn-danger">Delete</button>
				</form>
				@endif
            </div>
        </div>
    </div>
</div>

<script>

$(function() {

    $('#update_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('/otherSku/update') }}",
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
