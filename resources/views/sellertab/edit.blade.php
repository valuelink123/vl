@extends('layouts.layout')
@section('label', 'Setting Seller Tab')
@section('content')
    <h1 class="page-title font-red-intense"> Seller Tab
        <small>Setting Seller Tab.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Tab Form</span>
                </div>
            </div>
            <div class="portlet-body form">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <form role="form" action="{{ url('sellertab/'.$rules['id']) }}" method="POST">
                    {{ csrf_field() }}
					{{ method_field('PUT') }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Tab Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" name="tab" id="tab" value="{{$rules['tab']}}" required />
                            </div>
                        </div>
						<div style="clear:both;height:30px;"></div>
                        <div class="form-group mt-repeater">
							<h3 class="mt-repeater-title">Tab Data filter Rules</h3>
							<div data-repeater-list="tab-rules">
							
								<?php 
								$rd = unserialize($rules['tab_rules']);
								$trds = array_get($rd,'tabrules',array());
								$srds = array_get($rd,'showrules',array());
								$order = array_get($rd,'order',array());
								$by = array_get($rd,'by',array());
								foreach($trds as $trd) { ?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
										<div class="col-md-3">
											<label class="control-label">Fields</label>
											<select class="form-control" name="fields" required>
												@foreach (getComparisonfield() as $val)
													<option value="{{$val}}" <?php if($val == array_get($trd,'fields')) echo "selected"?>>{{$val}}</option>
												@endforeach
											</select>
								
								 </div>
										<div class="col-md-3">
											<label class="control-label">Symbol</label>
											 <select class="form-control" name="symbols" required>
												@foreach (getComparisonSymbol() as $val)
													<option value="{{$val}}" <?php if($val == array_get($trd,'symbols')) echo "selected"?>>{{$val}}</option>
												@endforeach
											</select>
                                        </div>
											
										<div class="col-md-3">
											<label class="control-label">Value</label>
											<div class="input-group">
                                        <input type="text" class="form-control"  name="value" value="{{array_get($trd,'value')}}" required>
            
                                    </div></div>
				
										<div class="col-md-2">
											<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
												<i class="fa fa-close"></i>
											</a>
										</div>
									</div>
								</div>
								<?php } ?>
							</div>
							<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
								<i class="fa fa-plus"></i> Add Rule</a>
						</div>
						
						
						<div style="clear:both;height:30px;"></div>
						
						
						
						<div class="form-group mt-repeater">
							<h3 class="mt-repeater-title">Tab Data Show Rules</h3>
							<div class="row">
								<div class="col-md-3">
											<label class="control-label">Order by Field</label>
											<select class="form-control" name="orderfield" required>
												@foreach (getComparisonfield() as $val)
													<option value="{{$val}}" <?php if($val == $order) echo "selected"?>>{{$val}}</option>
												@endforeach
											</select>
								
								 </div>
								 <div class="col-md-3">
											<label class="control-label">Order by</label>
											<select class="form-control" name="order" required>
													<option value="asc" <?php if($by == 'asc') echo "selected"?>>Asc</option> 
													<option value="desc" <?php if($by == 'desc') echo "selected"?>>Desc</option>
											</select>
								
								 </div>
								 </div>
								 <div style="clear:both;height:30px;"></div>
							<div data-repeater-list="show-rules">
								<?php foreach($srds as $srd) { ?>
								<div data-repeater-item class="mt-repeater-item">
									<div class="row mt-repeater-row">
									
									<div class="col-md-3">
											<label class="control-label">Group Color</label>
											<div class="input-group">
                                        <input type="text" class="colorspick" style="height: 34px;
    padding: 6px 12px 6px 40px; width: 100%;" name="color" data-control="hue" value="{{array_get($srd,'color','#ff0000')}}"  required>
            
                                    </div></div>
									
									
									<div class="col-md-8">
											<label class="control-label">Recommended action</label>
											<div class="input-group col-md-10">
                                        <input type="text" class="form-control"  name="action" value="{{array_get($srd,'action')}}" >
            
                                    </div></div>
									<div class="col-md-12">
											<label class="control-label">Recommended explain</label>
											<div class="input-group col-md-10">
                                        <input type="text" class="form-control"  name="explain" value="{{array_get($srd,'explain')}}" >
            
                                    </div></div>
										<div class="col-md-3">
											<label class="control-label">Fields</label>
											<select class="form-control" name="fields" required>
												@foreach (getComparisonfield() as $val)
													<option value="{{$val}}" <?php if($val == array_get($srd,'fields')) echo "selected"?>>{{$val}}</option>
												@endforeach
											</select>
								
								 </div>
										<div class="col-md-3">
											<label class="control-label">Symbol</label>
											 <select class="form-control" name="symbols" required>
												@foreach (getComparisonSymbol() as $val)
													<option value="{{$val}}" <?php if($val == array_get($srd,'symbols')) echo "selected"?>>{{$val}}</option>
												@endforeach
											</select>
                                        </div>
											
										<div class="col-md-3">
											<label class="control-label">Value</label>
											<div class="input-group">
                                        <input type="text" class="form-control"  name="value" value="{{array_get($srd,'value')}}" required>
            
                                    </div></div>
									
									
				
										<div class="col-md-2">
											<a href="javascript:;" data-repeater-delete class="btn btn-danger mt-repeater-delete">
												<i class="fa fa-close"></i>
											</a>
										</div>
									</div>
								</div>
								<?php } ?>
							</div>
							<a href="javascript:;" data-repeater-create class="btn btn-info mt-repeater-add">
								<i class="fa fa-plus"></i> Add Rule</a>
						</div>

                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="submit" class="btn blue">Submit</button>
                                <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="portlet light bordered" id="blockui_sample_1_portlet_body">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-bubble font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp sbold">How to use it?</span>
                </div>
            </div>
            <div class="portlet-body">
                TAB DATA FILTER RULES
				<p>
Specify which data is displayed under the page<p><p>

TAB DATA SHOW RULES<p>
Grouping of the same color will be treated as a merged rule<p>
Data that matches the rule will be displayed in the specified color<p>
            </div>
        </div>

    </div>

    </div>
 <script>
        $(function() {
            	$('.mt-repeater-add').on('click',function(){
					$('.colorspick').minicolors({
						control: $(this).attr('data-control') || 'hue',
						defaultValue: $(this).attr('data-defaultValue') || '#ff0000',
						inline: $(this).attr('data-inline') === 'true',
						letterCase: $(this).attr('data-letterCase') || 'lowercase',
						opacity: $(this).attr('data-opacity'),
						position: $(this).attr('data-position') || 'bottom left',
						change: function(hex, opacity) {
							if (!hex) return;
							if (opacity) hex += ', ' + opacity;
							if (typeof console === 'object') {
								console.log(hex);
							}
						},
						theme: 'bootstrap'
					});
				});
				$('.colorspick').minicolors({
					control: $(this).attr('data-control') || 'hue',
					defaultValue: $(this).attr('data-defaultValue') || '#ff0000',
					inline: $(this).attr('data-inline') === 'true',
					letterCase: $(this).attr('data-letterCase') || 'lowercase',
					opacity: $(this).attr('data-opacity'),
					position: $(this).attr('data-position') || 'bottom left',
					change: function(hex, opacity) {
						if (!hex) return;
						if (opacity) hex += ', ' + opacity;
						if (typeof console === 'object') {
							console.log(hex);
						}
					},
					theme: 'bootstrap'
				});
	
		

        });
    </script>

@endsection
