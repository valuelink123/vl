
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>设置重发单替代SKU</h1></div>
            <div class="portlet-body">

                <form id="update_form"  name="update_form" >
                    {{ csrf_field() }}
					<input type="hidden" name="id" id="id" value="{{array_get($form,'id',0)}}" >
                    <div class="form-body">

                        <div class="row">


                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>Sku:</label>
                                    <input type="text" class="form-control"  name="skua" id="skua" value="{{array_get($form,'skua')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>替代SKU:</label>
                                    <input type="text" class="form-control"  name="skub" id="skub" value="{{array_get($form,'skub')}}" >
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
				@if(array_get($form,'id')>0)
                <form action="{{ url('replaceSku/'.array_get($form,'id')) }}" method="POST" style="display: inline;">
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
			url: "{{ url('/replaceSku/update') }}",
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
