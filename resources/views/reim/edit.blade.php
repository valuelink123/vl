<div class="row"><div class="col-md-12">
        <div class="portlet light bordered">
			<h1 class="page-title font-red-intense">索赔更新
			</h1>
            <div class="portlet-body form">
                <form id="update_form"  name="update_form" >
					
                    <div class="form-body">
                        <div class="form-group col-md-12">
                            <label>Pod</label>
                            <input type="file" class="form-control" name="file" id="file">  
                        </div>
						
						<div class="form-group col-md-12">
                            <label>ISA</label>
                            <input type="text" class="form-control" name="isa" id="isa">  
                        </div>


                        <div class="form-group col-md-12">
                            <label>CaseID</label>
                            <input type="text" class="form-control" name="case_id" id="case_id">  
                        </div>

                        <div class="form-group col-md-12">
                            <label>进度</label>
                            <select class="form-control " name="step" id="step">
							<?php 
							foreach(\App\Models\AmazonShipmentItem::STATUS as $k=>$v){ 	
								echo '<option value="'.$k.'">'.$v.'</option>';
							}?>
							</select>
                        </div>
						
						<div class="form-group col-md-12">
                            <label>备注</label>
                            <input type="text" class="form-control" name="remark" id="remark" >
							<input type="hidden" name="ids" id="ids" value="{{$id}}">    
                        </div>
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
		var formFile = new FormData();
		formFile.append("file", $("#file")[0].files[0]);
		formFile.append("case_id", $("#case_id").val());
		formFile.append("step", $("#step").val());
		formFile.append("remark", $("#remark").val());
		formFile.append("ids", $("#ids").val());
		formFile.append("isa", $("#isa").val());
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('reims/batchUpdate') }}",
			data: formFile,
			cache: false,
			processData: false,
			contentType: false,
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
</script>
