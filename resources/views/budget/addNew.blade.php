<div class="row">
	<div class="col-md-12">
		<div class="portlet light bordered">
			<div class="portlet-body form">
				<form role="form" action="{{ url('/budgets/create') }}" method="POST">
					{{ csrf_field() }}

					<div class="form-body">
						<div class="clearfix margin-bottom-20"></div>
						<div class="form-group col-md-6">
							<label>站点</label>
							<select class="form-control" name="site" id="sitesel" required>
								<option class="active" value="">Please select</option>
								@foreach(getAsinSites() as $key=>$site)
									<option value="{!! $site !!}" >{!! $site !!}</option>
								@endforeach
							</select>
						</div>

						<div class="form-group col-md-6">
							<label>物料组</label>
							<select class="form-control" name="item_group" id="item_group" required>
								<option class="active" value="">Please select</option>
								@foreach($itemGroup as $key=>$val)
									<option value="{!! $key !!}" >{!! $val !!}</option>
								@endforeach
							</select>
						</div>

						<div class="form-group col-md-6">
							<label>产品名称</label>
							<input type="text" class="form-control" name="description" id="description" required>

						</div>

						<div class="form-group col-md-6">
							<label>退货率</label>
							<input type="text" class="form-control" name="exception" id="exception" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}[%]$" required>

						</div>

						<div class="form-group col-md-6">
							<label>关税税率</label>
							<input type="text" class="form-control" name="tax" id="tax" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}[%]$" required>

						</div>

						<div class="form-group col-md-6">
							<label>佣金比例</label>
							<input type="text" class="form-control" name="common_fee" id="common_fee" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}" required>
						</div>

						<div class="form-group col-md-6">
							<label>拣配费金额（CNY）</label>
							<input type="text" class="form-control" name="pick_fee" id="pick_fee" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}" required>
						</div>

						<div class="form-group col-md-6">
							<label>产品体积cm３</label>
							<input type="text" class="form-control" name="volume" id="volume" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}" required>
						</div>
						<div class="form-group col-md-6">
							<label>不含税采购单价</label>
							<input type="text" class="form-control" name="cost" id="cost" pattern="^[0-9]*[.]{0,1}[0-9]{0,2}" required>
						</div>
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