
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>亚马逊仓库信息</h1></div>
            <div class="portlet-body">

                <form id="update_form"  name="update_form" >
                    {{ csrf_field() }}
                    <div class="form-body">

                        <div class="row">


                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>仓库代码:</label>
                                    <input type="text" class="form-control"  name="code" id="code" value="{{array_get($form,'code')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>地址:</label>
                                    <input type="text" class="form-control"  name="address" id="address" value="{{array_get($form,'address')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>州:</label>
                                    <input type="text" class="form-control"  name="state" id="state" value="{{array_get($form,'state')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>城市:</label>
                                    <input type="text" class="form-control"  name="city" id="city" value="{{array_get($form,'city')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>邮编:</label>
                                    <input type="text" class="form-control"  name="zip" id="zip" value="{{array_get($form,'zip')}}" >
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label>费用$/板:</label>
                                    <input type="text" class="form-control"  name="fee" id="fee" value="{{array_get($form,'fee')}}" >
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
			url: "{{ url('/amazonWarehouse/update') }}",
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
