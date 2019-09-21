
    <div class="row"><div class="col-md-12">
        <div class="portlet light bordered">
			<h1 class="page-title font-red-intense">Add Tasks
			</h1>
            <div class="portlet-body form">
                <form id="add_task_form"  name="add_task_form">
                    {{ csrf_field() }}

                    <div class="form-body">
						
				
				
				
						
						<div class="form-group col-md-12">
                            <label>Assigned To</label>
                             <select class="ajax-mt-multiselect btn btn-default form-control " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]">

								@foreach ($users as $user_id=>$user_name)
									<option value="{{$user_id}}">{{$user_name}}</option>
								@endforeach
							</select>
                        </div>
						
						<div class="form-group col-md-4">
                            <label>Task Type</label>
                           <select class="form-control " name="type" id="type" required>
							<?php 
							foreach(getTaskTypeArr() as $k=>$v){ 	
								echo '<option value="'.$k.'" >'.$v.'</option>';
							}?>
							
							</select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Priority</label>
                            
                             
                                <input type="text" class="form-control" name="priority" id="priority" placeholder="1 - 100"  required>
                            
                        </div>
						
						<div class="form-group col-md-4">
                            <label>Due To</label>
							<div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" readonly name="complete_date" value="{{date('Y-m-d')}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>	                          
                        </div>
						
						<div class="form-group col-md-12">
                            <label>Task Details</label>
                            <textarea class="form-control" name="request" rows="5" required></textarea>
                        </div>
						
						<div class="form-group col-md-4">
                            <label>Sku</label>
                                <input type="text" class="form-control" name="sku" id="sku" placeholder="Optional" >
                           
                        </div>
						<div class="form-group col-md-4">
                            <label>Asin</label>
							<input list="skuasins" type="text" class="form-control" name="asin" id="asin"  placeholder="Optional" autoComplete="off" >
							<datalist id="skuasins">
							</datalist>	
                        </div>
						
						
						
						<div class="form-group col-md-4">
                            <label>Customer Email</label>
                                <input type="text" class="form-control" name="customer_email" id="customer_email"  placeholder="Optional" >
                           
                        </div>
						<div class="clearfix"></div>
						

                        
						
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
								<button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                <button type="submit" class="btn blue pull-right">Submit</button>
                                
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

<script>
$('.ajax-mt-multiselect').each(function(){
	var btn_class = $(this).attr('class');
	var clickable_groups = ($(this).data('clickable-groups')) ? $(this).data('clickable-groups') : false ;
	var collapse_groups = ($(this).data('collapse-groups')) ? $(this).data('collapse-groups') : false ;
	var drop_right = ($(this).data('drop-right')) ? $(this).data('drop-right') : false ;
	var drop_up = ($(this).data('drop-up')) ? $(this).data('drop-up') : false ;
	var select_all = ($(this).data('select-all')) ? $(this).data('select-all') : false ;
	var width = ($(this).data('width')) ? $(this).data('width') : '' ;
	var height = ($(this).data('height')) ? $(this).data('height') : '' ;
	var filter = ($(this).data('filter')) ? $(this).data('filter') : false ;
	
	// advanced functions
	var onchange_function = function(option, checked, select) {
		//alert('Changed option ' + $(option).val() + '.');
	}
	var dropdownshow_function = function(event) {
		//alert('Dropdown shown.');
	}
	var dropdownhide_function = function(event) {
		//alert('Dropdown Hidden.');
	}
	
	// init advanced functions
	var onchange = ($(this).data('action-onchange') == true) ? onchange_function : '';
	var dropdownshow = ($(this).data('action-dropdownshow') == true) ? dropdownshow_function : '';
	var dropdownhide = ($(this).data('action-dropdownhide') == true) ? dropdownhide_function : '';
	
	// template functions
	// init variables
	var li_template;
	if ($(this).attr('multiple')){
		li_template = '<li class="mt-checkbox-list"><a href="javascript:void(0);"><label class="mt-checkbox"> <span></span></label></a></li>';
	} else {
		li_template = '<li><a href="javascript:void(0);"><label></label></a></li>';
	}
	
	// init multiselect
	$(this).multiselect({
		enableClickableOptGroups: clickable_groups,
		enableCollapsibleOptGroups: collapse_groups,
		disableIfEmpty: true,
		enableFiltering: filter,
		includeSelectAllOption: select_all,
		dropRight: drop_right,
		buttonWidth: width,
		maxHeight: height,
		onChange: onchange,
		onDropdownShow: dropdownshow,
		onDropdownHide: dropdownhide,
		buttonClass: btn_class,
		//optionClass: function(element) { return "mt-checkbox"; },
		//optionLabel: function(element) { console.log(element); return $(element).html() + '<span></span>'; },
		/*templates: {
			li: li_template,
		}*/
	});   
});

$(function() {
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		autoclose: true
	});
	$('#add_task_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('task') }}",
			data: $('#add_task_form').serialize(),
			success: function (data) {
				if(data==1){
					alert('Add Task Success');
					$('#ajax').modal('hide');//隐藏modal
					$('#global_task_ajax').modal('hide');
					$('.modal-backdrop').remove();
					if ( $('#datatable_ajax').length > 0 ){
						var dttable = $('#datatable_ajax').dataTable();
						dttable.fnClearTable(false); //清空一下table
						dttable.fnDestroy(); //还原初始化了的datatable
						TableDatatablesAjax.init();
					} 
					if ( location.pathname=='/home' ){
						location.reload();
					}
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
});
</script>
