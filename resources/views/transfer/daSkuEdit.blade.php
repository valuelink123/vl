
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>Da Sku与SAP Sku匹配关系</h1></div>
            <div class="portlet-body">

                <form id="update_form"  name="update_form" >
                    {{ csrf_field() }}
                    <div class="form-body">

                        <div class="row">


                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Sku:</label>
                                    <input type="text" class="form-control"  name="sku" id="sku" value="{{array_get($form,'sku')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>DaSku:</label>
                                    <input type="text" class="form-control"  name="da_sku" id="da_sku" value="{{array_get($form,'da_sku')}}" >
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
			url: "{{ url('/daSkuMatch/update') }}",
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
