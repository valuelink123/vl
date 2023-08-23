
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>SKU尺寸重量信息</h1></div>
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
                                    <label>数量/箱:</label>
                                    <input type="text" class="form-control"  name="quantity" id="quantity" value="{{array_get($form,'quantity')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>长（CM）:</label>
                                    <input type="text" class="form-control"  name="length" id="length" value="{{array_get($form,'length')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>宽（CM）:</label>
                                    <input type="text" class="form-control"  name="width" id="width" value="{{array_get($form,'width')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>高（CM）:</label>
                                    <input type="text" class="form-control"  name="height" id="height" value="{{array_get($form,'height')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>材积重（kg）:</label>
                                    <input type="text" class="form-control"  name="weight" id="weight" value="{{array_get($form,'weight')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>体积重:</label>
                                    <input type="text" class="form-control"  name="volume" id="volume" value="{{array_get($form,'volume')}}" >
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
			url: "{{ url('/skuSize/update') }}",
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
