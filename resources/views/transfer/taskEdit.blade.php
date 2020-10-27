<div class="row"><div class="col-md-12">
        <div class="portlet light bordered">
			<h1 class="page-title font-red-intense">调拨任务编辑
			</h1>
            <div class="portlet-body form">
                <form id="update_form"  name="update_form" >
                    {{ csrf_field() }}
					{{ method_field('PUT') }}
					
                    <div class="form-body">
                        <?php 
                        $trueOrFalse = array('0'=>'NO','1'=>'YES');
                        if(!empty($transferRequest)){ ?>
                        <div class="form-group col-md-6 bold">
                            <label>调拨申请号:</label> {{$transferRequest->transfer_request_key}}
                        </div>
                        <div class="form-group col-md-6">
                            <label>操作人:</label> {{array_get($users,$transferRequest->user_id)}}
                        </div>

                        <div class="form-group col-md-3">
                            <label>站点:</label>
                            {{array_get($siteCode,$transferRequest->marketplace_id)}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>账号:</label>
                            {{array_get($accountCode,$transferRequest->seller_id)}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>状态:</label>
                            {{array_get($requestStatus,$transferRequest->status)}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>销售员:</label>
                            {{$transferRequest->bg}}{{$transferRequest->bu}}-{{array_get($sellers,$transferRequest->sap_seller_id)}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>Asin:</label>
                            {{$transferRequest->asin}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>Sku:</label>
                            {{$transferRequest->sku}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>申请数量:</label>
                            {{$transferRequest->quantity}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>入库日期:</label>
                            {{$transferRequest->delivery_date}}
                        </div>


                        <div class="form-group col-md-6">
                            <label>调拨理由:</label>
                            {{$transferRequest->request_reason}}
                        </div>

                        <div class="form-group col-md-3">
                            <label>Shipment Id:</label>
                            {{$transferRequest->shipment_id}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>大货资料:</label>
                            {{$transferRequest->attach_data}}
                        </div>
                        <div class="form-group col-md-6">
                            <label>创建日期:</label>
                            {{$transferRequest->created_at}}
                        </div>
                        <div class="form-group col-md-6">
                            <label>最后更新:</label>
                            {{$transferRequest->updated_at}}
                        </div>

						<?php } ?>
						<?php if(!empty($transferPlan)){ ?>
                        <div class="divider" style="clear: both;width: 100%;height: 2px;background: #ccc; margin-bottom: 20px;"></div>
                        
                        <div class="form-group col-md-6 bold">
                            <label>调拨计划号:</label> {{$transferPlan->transfer_plan_key}}
                        </div>

                        <div class="form-group col-md-6">
                            <label>操作人:</label> {{array_get($users,$transferPlan->planer)}}
                        </div>

                        <div class="form-group col-md-3">
                            <label>调出工厂:</label>{{$transferPlan->out_factory}}
                    
                        </div>
						
						<div class="form-group col-md-3">
                            <label>调出日期:</label>{{$transferPlan->out_date}}
							                         
                        </div>

                        <div class="form-group col-md-3">
                            <label>调入工厂:</label>{{$transferPlan->in_factory}}
                        </div>
						
						<div class="form-group col-md-3">
                            <label>调入日期:</label>{{$transferPlan->in_date}}
								                          
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label>调出Sku:</label>{{$transferPlan->sku}}
                        </div>

                        <div class="form-group col-md-3">
                            <label>调出数量:</label>{{$transferPlan->quantity}}
                        </div>


                        <div class="form-group col-md-3">
                            <label>计划物流:</label>{{$transferPlan->carrier_code}} 
                        </div>

                        <div class="form-group col-md-3">
                            <label>发货方式:</label>{{$transferPlan->ship_method}}
                        </div>


                        <div class="form-group col-md-3">
                            <label>需大货资料:</label>{{array_get($trueOrFalse,$transferPlan->require_attach)}}
                        </div>

                        <div class="form-group col-md-3">
                            <label>需采购:</label>{{array_get($trueOrFalse,$transferPlan->require_purchase)}}
                        </div>

                        <div class="form-group col-md-3">
                            <label>需换标:</label>{{array_get($trueOrFalse,$transferPlan->require_rebrand)}}
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label>需RMS标贴:</label>{{array_get($trueOrFalse,$transferPlan->require_rms)}}
                        </div>

                        <div class="form-group col-md-3">
                            <label>RMS标贴:</label>{{$transferPlan->rms}}
                        </div>

                        <div class="form-group col-md-3">
                            <label>状态:</label>{{array_get($planStatus,$transferPlan->status)}}
                        </div>
                        <div style="clear:both;"></div>
                        <div class="form-group col-md-6">
                            <label>创建日期:</label>
                            {{$transferPlan->created_at}}
                        </div>
                        <div class="form-group col-md-6">
                            <label>最后更新:</label>
                            {{$transferPlan->updated_at}}
                        </div>
                        <?php } ?>    
                            
                        <div class="divider" style="clear: both;width: 100%;height: 2px;background: #ccc; margin-bottom: 20px;"></div>
                        
                        <div class="form-group col-md-6 bold">
                            <label>调拨任务号:</label> {{$transferTask->transfer_task_key}}
                        </div>
                        <div class="form-group col-md-6">
                            <label>操作人:</label> {{array_get($users,$transferTask->user_id)}}
                        </div>
                        <div class="form-group col-md-3">
                            <label>实际调出:</label>
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" name="out_date" value="{{$transferTask->out_date}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>	 
                        </div>
                        <div class="form-group col-md-3">
                            <label>实际调入:</label>
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control" name="in_date" value="{{$transferTask->in_date}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label>跟踪号:</label>
                            <input type="text" class="form-control" name="tracking_number" id="tracking_number" value='{{$transferTask->tracking_number}}'>
                        </div>
                        <div class="form-group col-md-3">
                            <label>状态:</label>
                            <select class="form-control " name="status" id="status">
							<?php 
							foreach($taskStatus as $k=>$v){ 	
								echo '<option value="'.$k.'" '.(($k==$transferTask->status)?'selected':'').'>'.$v.'</option>';
							}?>
							</select>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="form-group col-md-6">
                            <label>创建日期:</label>
                            {{$transferTask->created_at}}
                        </div>
                        <div class="form-group col-md-6">
                            <label>最后更新:</label>
                            {{$transferTask->updated_at}}
                        </div>
                        <div class="form-group col-md-12">
                            <label>操作日志:</label><BR>
                            {!!implode('</BR>',$logArr)!!}
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
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('transferTask/'.$transferTask->id) }}",
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
	$('.date-picker').datepicker({
		rtl: App.isRTL(),
		autoclose: true
	});
});
</script>
