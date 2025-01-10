<?php
$summaries = current(json_decode($form['summaries'],true));
$attributes = json_decode($form['attributes'],true);
$image = array_get($summaries,'mainImage.link');
$item_name = array_get($summaries,'itemName');
$purchasable_offers = array_get($attributes,'purchasable_offer');
$list_price = array_get($attributes,'list_price');
$item_name.= '<BR><span class="label label-sm label-primary">'.array_get($sellerAccounts,$form['seller_account_id']).'</span>
<span class="label label-sm label-success">'.array_get(array_flip(getSiteCode()),$form['marketplaceid']).'</span>
<span class="label label-sm label-warning">'.$form['asin'].'</span>
<span class="label label-sm label-danger">'.$form['seller_sku'].'</span>
</div>'
?>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title"><h1>Listing</h1></div>
                <form id="update_form"  name="update_form" >
                    <div class="portlet-body">         
                        {{ csrf_field() }}
                        <div class="form-body">
                            <input type="hidden" name="id" value="{{array_get($form,'id',0)}}">
                            <input type="hidden" name="api_msg">
                            <div class="form-group">
                            <div class="row">
                                <div class="col-md-2">
                                    <img src="{!!$image!!}" width=100px height=100px>
                                </div>
                                <div class="col-md-10">
                                    {!!$item_name!!}
                                </div>
                            </div>
                            </div>
                            @foreach($purchasable_offers as $offer)
                            @if(array_get($offer,'audience')=='ALL')
                            <div class="form-group">
                                <label><span class="label label-primary">Audience:ALL</span><span class="label label-danger">时间格式解释 年-月-日T时:分:秒.毫秒Z</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Start At:</label>
                                            <input type="text" class="form-control date-picker" name="start_at" value="{{array_get($offer,'start_at.value')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>End At:</label>
                                            <input type="text" class="form-control date-picker" name="end_at" value="{{array_get($offer,'end_at.value')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Our Price:</label>
                                            <input type="text" class="form-control" name="our_price" value="{{array_get($offer,'our_price.0.schedule.0.value_with_tax')}}">
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Discounted Start At:</label>
                                            <input type="text" class="form-control date-picker" name="discounted_start_at" value="{{array_get($offer,'discounted_price.0.schedule.0.start_at')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Discounted End At:</label>
                                            <input type="text" name="discounted_end_at" class="form-control date-picker" value="{{array_get($offer,'discounted_price.0.schedule.0.end_at')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Discounted Price:</label>
                                            <input type="text" name="discounted_price" class="form-control" value="{{array_get($offer,'discounted_price.0.schedule.0.value_with_tax')}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                    &nbsp;&nbsp;
                                    <input type="submit" name="update" value="Save" class="btn blue pull-right">
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row" style="margin:10px;">
                            @if(!empty($logs))
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>用户</th>
                                        <th>更新</th>
                                        <th>结果</th>
                                        <th>创建</th>
                                        <th>更新</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $logs = json_decode(json_encode($logs),true);
                                foreach($logs as $log){

                                    $input = json_decode($log['input'],true);
                                    
                                    $output = json_decode($log['output'],true);
                                    
                                    $offers = array_get($input,'patches.0.value');
                                    $priceStr= '';
                                    if(!empty($offers)){
                                        foreach($offers as $offer){
                                            $priceStr.= '<span class="label label-sm label-primary">'.array_get($offer,'audience').'</span>';

                                            if(empty(array_get($offer,'discounted_price'))){
                                                $priceStr.= '<span class="label label-sm label-danger">'.array_get($offer,'our_price.0.schedule.0.value_with_tax').'</span>';
                                            }else{
                                                $priceStr.= '<span class="label label-sm label-default" style="text-decoration: line-through;">'.array_get($offer,'our_price.0.schedule.0.value_with_tax').'</span><span class="label label-sm label-danger">'.array_get($offer,'discounted_price.0.schedule.0.value_with_tax').'</span>';
                                            }
                                            $priceStr.= '<span class="label label-sm label-success">'.array_get($offer,'currency').'</span><BR><BR>';
                                        }
                                    }
                                ?>
                                
                                    <tr>
                                        <td>{{array_get($sellers,$log['user_id'])}} </td>
                                        <td>{!!$priceStr!!}</td>
                                        <td>{{array_get($output,'status','PROCESSING')}}</td>
                                        <td>{{$log['created_at']}}</td>
                                        <td>{{$log['updated_at']}}</td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            @endif
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
    $('.date-picker').datepicker({
        rtl: App.isRTL(),
		format: 'yyyy-mm-dd\T00:00:00.000\Z',
        autoclose: true
    });

    $('#update_form').submit(function() {
        if (confirm("确定要添加此价格更新?")) {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
            });
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ url('/listing/update') }}",
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
        }else{
            return false;
        }
		
	});
});
</script>
