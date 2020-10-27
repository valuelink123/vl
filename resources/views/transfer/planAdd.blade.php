@extends('layouts.layout')
@section('label', 'Create A New Transfer Plan')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<form  action="/transfer/plan/createPlan" id="form" novalidate method="POST" onsubmit="return validate_form()">
		{{ csrf_field() }}
		<div class="col-lg-9">
			<div class="col-md-12">
				<div class="portlet light portlet-fit bordered ">
					@if($errors->any())
						<div class="alert alert-danger">
							@foreach($errors->all() as $error)
								<div>{{ $error }}</div>
							@endforeach
						</div>
					@endif
					<input type="hidden" value="{!! $ids !!}" name="id">
					<div class="portlet-title">
						<div class="caption">
							<i class="icon-microphone font-green"></i>
							<span class="caption-subject bold font-green">Create A New Transfer Plan</span>
						</div>
					</div>
					<div class="portlet-body">
						<div class="tabbable-line">
							<div class="">
								<div class="col-lg-8">
									@foreach($data as $k=>$v)
										<div class="col-md-4">
											<div class="form-group">
												<label>Asin</label>
												<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
													<input type="text" class="form-control"  value="{!! $v['asin'] !!}" disabled="disabled">
												</div>
											</div>
										</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>ItemNo</label>
											<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
												<input type="text" class="form-control"  value="{!! $v['sku'] !!}" disabled="disabled">
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>Quantity</label>
											<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
												<input type="text" class="form-control"  value="{!! $v['quantity'] !!}" disabled="disabled">
											</div>
										</div>
									</div>
									@endforeach
									<div class="col-md-6">
									<div class="form-group">
										<label>ItemNo</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control"  value="{!! key($total) !!}" disabled="disabled">
										</div>
									</div>
									</div>
									<div class="col-md-6">
									<div class="form-group">
										<label>Quantity</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control"  value="{!! $total[key($total)] !!}" disabled="disabled">
										</div>
									</div>
									</div>
									<div class="col-md-6">
									<div class="form-group">
										<label>调出工厂</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" id="out_factory"  value="" name="out_factory">
										</div>
									</div>
									</div>
									<div class="col-md-6">
									<div class="form-group">
										<label>调入工厂</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" id="in_factory"  value="" name="in_factory">
										</div>
									</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>调拨数量</label>
											<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
												<input type="text" class="form-control" id="quantity"  value="" name="quantity">
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>物流商代码</label>
											<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
												<input type="text" class="form-control" id="carrier_code"  value="" name="carrier_code">
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>发货模式</label>
											<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
												<input type="text" class="form-control" id="ship_method"  value="" name="ship_method">
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>是否RMS标贴</label>
											<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
												<select class="form-control"  id="require_rms" name="require_rms">
													<option value="0">否</option>
													<option value="1">是</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>是否大货资料</label>
											<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
												<select class="form-control"  id="require_attach" name="require_attach">
													<option value="0">否</option>
													<option value="1">是</option>
												</select>
											</div>
										</div>
									</div>

									<div class="col-md-6">
										<div class="form-group">
											<label>是否换标</label>
											<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
												<select class="form-control"  id="require_rebrand" name="require_rebrand">
													<option value="0">否</option>
													<option value="1">是</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-6">
									<div class="form-group">
										<label>调出时间</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<div class="input-group">
												<input  class="form-control date" value="" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="out_date" name="out_date"/>
											</div>
										</div>
									</div>
									</div>
									<div class="col-md-6">
									<div class="form-group">
										<label>调入时间</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<div class="input-group">
												<input  class="form-control date" value="" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="in_date" name="in_date"/>
											</div>
										</div>
									</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-4 col-md-8">
								<button type="submit" class="btn blue">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>
	</form>
	@include('frank.common')
	<script>
        //日期控件初始化
        $('.date').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        function validate_form(){
            var array = {'out_factory':'调出工厂','in_factory':'调入工厂','quantity':'调拨数量','carrier_code':'物流商代码','ship_method':'发货模式','out_date':'调出时间','in_date':'调入时间'};
            var flag = 0;
            var msg = '';
            $.each(array,function(kk,vv){
                var value = $('#'+kk).val();
                if(value == ''){
                    flag = 1;
                    msg = vv + "不能为空.";
                    return false;
                }
            });
            if(flag==1){
                alert(msg);
                return false;
			}
            var out_date = $('#out_date').val();
            var in_date = $('#in_date').val();
            if(in_date < out_date){
                alert('调入时间不能小于调出时间');
                return false;
			}
        }

	</script>
@endsection