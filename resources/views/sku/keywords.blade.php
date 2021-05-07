<div class="row"><div class="col-md-12">
        <div class="portlet light bordered">
			<h1 class="page-title font-red-intense">Manage Keywords
			</h1>
            <div class="portlet-body form">
                <form id="update_form"  name="update_form" >
                    {{ csrf_field() }}
					
                    <div class="form-body">
                    <div class="form-group mt-repeater">
                    <h3 class="mt-repeater-title">{{$asin}}  {{array_get(getSiteUrl(),$marketplace_id)}}  {{$date}}</h3>
							<div data-repeater-list="keywords">
                                @if(empty($keywords))
                                <div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-5">
											<label class="control-label">Keyword</label>
											<input type="text" class="form-control" name="keyword" required>
								
								        </div>
                                        <div class="col-md-4">
                                            <label class="control-label">Rank</label>
											<input type="text" class="form-control rankFormat" name="rank" required>
                                        </div>
											
				
                                        <div class="col-md-3">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
								    </div>
								</div>
                                @else
								<?php 
								foreach($keywords as $keyword=>$rank) { ?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-5">
											<label class="control-label">Keyword</label>
											<input type="text" value="{{$keyword}}" class="form-control" name="keyword" required>
								
								        </div>
                                        <div class="col-md-4">
                                            <label class="control-label">Rank</label>
											<input type="text" value="{{$rank}}" class="form-control rankFormat" name="rank" required>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
								    </div>
								</div>
								<?php } ?>
                                @endif
							</div>
							<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
								<i class="fa fa-plus"></i> Add Keyword</a>
						</div>

                        
						
                    </div>
					
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" name="asin" value="{{$asin}}">
                                <input type="hidden" name="marketplace_id" value="{{$marketplace_id}}">
                                <input type="hidden" name="date" value="{{$date}}">
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
    $.validator.addMethod("rankFormat",function(value,element){
                var  objRegExp= /^P\d+\-\d+$/i;
                return objRegExp.test(value);
            },"Must be Number format");
            
    FormRepeater.init();
    $('#update_form').submit(function() {
        if(!($("#update_form").valid())) return false;
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('skus/keywords') }}",
			data: $('#update_form').serialize(),
			success: function (data) {
				if(data.customActionStatus=='OK'){  
					$('#ajax').modal('hide');
					$('.modal-backdrop').remove();
                    for(var item in data.ajaxReplace){
                        $('#'+item).html(data.ajaxReplace[item]);
                    }
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
