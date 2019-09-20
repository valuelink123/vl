<div class="row"><div class="col-md-12">
        <div class="portlet light bordered">
			<h1 class="page-title font-red-intense">Update Task
			</h1>
            <div class="portlet-body form">
                <form id="update_task_form"  name="update_task_form" >
                    {{ csrf_field() }}
					{{ method_field('PUT') }}
					<?php
					$response_user=$request_user=$other_user='';
					//$response_user = ($task['response_user_id']!=Auth::user()->id)?'Disabled':'';
					//$request_user = ($task['request_user_id']!=Auth::user()->id)?'Disabled':'';
					//$other_user = (!$request_user || !$response_user)?'':'Disabled';
					?>
                    <div class="form-body">
						<div class="form-group col-md-12">
                            <label>Assigned To</label>
                             <select class="form-control " name="user_id" id="user_id" {{$request_user}}>
								@foreach ($users as $user_id=>$user_name)
									<option value="{{$user_id}}" {{(($user_id==$task['response_user_id'])?'selected':'')}}>{{$user_name}}</option>
								@endforeach
							</select>
                        </div>
						
						<div class="form-group col-md-4">
                            <label>Task Type</label>
                           <select class="form-control " name="type" id="type" required {{$request_user}}>
							<?php 
							foreach(getTaskTypeArr() as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==$task['type'])?'selected':'').'>'.$v.'</option>';
							}?>
							
							</select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Priority</label>
                            
                             
                                <input type="text" class="form-control" name="priority" id="priority" placeholder="1 - 100" value='{{$task['priority']}}' required {{$request_user}}>
                            
                        </div>
						
						<div class="form-group col-md-4">
                            <label>Due To</label>
							<div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" readonly name="complete_date" value="{{$task['complete_date']}}" {{$request_user}}>
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>	                          
                        </div>
						
						<div class="form-group col-md-12">
                            <label>Task Details</label>
                            <textarea class="form-control" name="request" rows="5" required {{$request_user}}>{{$task['request']}}</textarea>
                        </div>
						<div class="form-group col-md-4">
                            <label>Sku</label>
                                <input type="text" class="form-control" name="sku" id="sku" placeholder="Optional"  value="{{$task['sku']}}" {{$other_user}}>
                           
                        </div>
						<div class="form-group col-md-4">
                            <label>Asin</label>
                                <input list="skuasins" type="text" class="form-control" name="asin" id="asin" placeholder="Optional" autoComplete="off"  value="{{$task['asin']}}" {{$other_user}}>
                           		<datalist id="skuasins">
							</datalist>
                        </div>
						
						
						
						<div class="form-group col-md-4">
                            <label>Customer Email</label>
                                <input type="text" class="form-control" name="customer_email" id="customer_email"  placeholder="Optional" value="{{$task['customer_email']}}" {{$other_user}}>
                           
                        </div>
						
						
						<div class="form-group col-md-12">
                            <label>Task Response</label>
                            <textarea class="form-control" name="response" rows="5" {{$response_user}}>{{$task['response']}}</textarea>
                        </div>
						
						<div class="form-group col-md-4">
                            <label>Stage</label>
                            <select class="form-control " name="stage" id="stage" {{$response_user}}>
							<?php 
							foreach(getTaskStageArr() as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==$task['stage'])?'selected':'').'>'.$v.'</option>';
							}?>
							</select>
                           
                        </div>
						
						<div class="form-group col-md-4">
                            <label>Score</label>
                            <input type="text" class="form-control" name="score" id="score" value="{{$task['score']}}" {{$request_user}}>
                           
                        </div>
						
						
						<div class="clearfix"></div>
						

                        
						
                    </div>
					
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-12">
								@if(!$request_user)
								<button type="button" name="delete" id="delete" class="btn red pull-left">Delete</button>
								@endif
								<button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
								@if(!$other_user)
                                <input type="submit" name="update" value="Update" class="btn blue pull-right" {{$other_user}}>
								@endif
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
	$('#delete').click(function() {
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('task/'.$task['id']) }}",
			data: "_method=DELETE&_token={{ csrf_token() }}",
			success: function (data) {
				if(data==1){
					alert('Delete Task Success');
					$('#ajax').modal('hide');//隐藏modal
					$('.modal-backdrop').remove();
					var dttable = $('#datatable_ajax').dataTable();
					dttable.fnClearTable(false); //清空一下table
					dttable.fnDestroy(); //还原初始化了的datatable
					TableDatatablesAjax.init();
				}else{
					alert(data);
				}
			},
			error: function(data) {
				alert("error:"+data.responseText);
			}
		});
		
		return false;
	});
	
	$('#update_task_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('task/'.$task['id']) }}",
			data: $('#update_task_form').serialize(),
			success: function (data) {
				if(data==1){
					alert('Update Task Success');
					$('#ajax').modal('hide');//隐藏modal
					$('.modal-backdrop').remove();
					var dttable = $('#datatable_ajax').dataTable();
					dttable.fnClearTable(false); //清空一下table
					dttable.fnDestroy(); //还原初始化了的datatable
					TableDatatablesAjax.init();
				}else{
					alert(data);
				}
			},
			error: function(data) {
				alert("error:"+data.responseText);
			}
		});
		return false;
	});
	
	$('#sku').change(function() {
		var sku = $(this).val();
		if(sku.length>=6){
			$('#skuasins').empty();
			$.ajaxSetup({
				headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
			});
			$.ajax({
				type: "POST",
				dataType: "json",
				url: "{{ url('getasinbysku') }}",
				data: "sku="+sku,
				success: function (data) {
					$.each(data, function (k, v) {
						$('#skuasins').append('<option value="' + v.asin + '">');
					});
				},
				error: function(data) {
					alert("error:"+data.responseText);
				}
			});
		}
	});
	
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		autoclose: true
	});
});
</script>
