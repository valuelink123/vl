@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>['asinMatchRelation']])
@endsection
@section('content')
	@include('frank.common')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<div class="row">
		<div class="top portlet light">
			<div class="search_table" style="margin-left: -15px;margin-bottom: 50px;">
				<form id="search-form">
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">Site</span>
							<select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="site" onchange="getAccountBySite()" name="site">
								@foreach($site as $value)
									<option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group" id="account-div">
							<span class="input-group-addon">Account</span>
							<select class="btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">

							</select>
						</div>
					</div>
					<div class="col-md-1">
						<div class="input-group">
							<span class="input-group-addon">Source</span>
							<select  style="width:100%;height:35px;" data-recent="" data-recent-date="" id="source" name="source">
								@foreach($source as $value)
									<option value="{{$value}}">{{$value}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">Asin</span>
							<input class="form-control" value="" id="asin" placeholder="Asin" name="asin"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">Seller Sku</span>
							<input class="form-control" value="" id="seller_sku" placeholder="Seller Sku" name="seller_sku"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">SKU</span>
							<input class="form-control" value="" id="sku" placeholder="SKU" name="sku"/>
						</div>
					</div>
					<div class="col-md-1">
						<div class="input-group">
							<div class="btn-group pull-right" >
								<button id="search_table" class="btn sbold blue">Search</button>
							</div>
						</div>
					</div>
				</form>
			</div>
			@permission('asin-match-relation-add')
			<div class="btn-group " style="float:right;margin-top:20px;">
				<div class="col-md-12">
					<div class="col-md-2"  >
						<a  data-toggle="modal" href="/asinMatchRelation/add" target="_blank">
							<button class="btn sbold blue"> Add New
								<i class="fa fa-plus"></i>
							</button>
						</a>
					</div>
				</div>
			</div>
			@endpermission

			<div>
				<table class="table table-striped table-bordered" id="datatable">
					<thead>
					<tr>
						<th>ID</th>
						<th>Site</th>
						<th>Seller Id</th>
						<th>Account Name</th>
						<th>Asin</th>
						<th>Seller Sku</th>
						<th>Item No</th>
						<th>Seller</th>
						<th>Source</th>
						<th>Warehouse</th>
						<th>Create Date</th>
						<th>Update Date</th>
						<th>Action</th>
					</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>

	<script>
        $('#datatable').dataTable({
            searching: false,//关闭搜索
            serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
            ordering:false,
            "pageLength": 10, // default record count per page
            "lengthMenu": [
                [10, 20,50,],
                [10, 20,50,] // change per page values here
            ],
            processing: true,
            columns: [
                {data: 'id',name:'id'},
                {data: 'site',name:'site'},
                {data: 'seller_id',name:'seller_id'},
                {data: 'seller_name',name:'seller_name'},
				{data: 'asin',name:'asin'},
				{data: 'seller_sku',name:'seller_sku'},
				{data: 'sku',name:'sku'},
				{data: 'user_name',name:'user_name'},
				{data: 'source',name:'source'},
				{data: 'sap_warehouse_code',name:'sap_warehouse_code'},
				{data: 'created_at',name:'created_at'},
                {data: 'updated_at',name:'updated_at'},
                {data: 'action',name:'action'},
            ],
            ajax: {
                type: 'POST',
                url: '/asinMatchRelationList',
                data:  {search: $("#search-form").serialize()}
            }
        })
        dtApi = $('#datatable').dataTable().api();
        //点击上面的搜索
        $('#search_table').click(function(){
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtApi.ajax.reload();
            return false;
        })

		$('table').on('click','.delete-action',function(){
			var id = $(this).attr('data-id');
			var r = confirm("真的确定删除吗？")
			if (r == true){
				$.ajax({
					type: 'post',
					url: '/asinMatchRelation/delete',
					data: {id:id},
					dataType:'json',
					success: function(res) {
						if(res.status>0){
							alert('Success');
							$("#search_table").trigger("click");
							// window.location.href="/asinMatchRelation";
						}else{
							alert(res.msg);
						}
					}
				});
			}
		})

		function getAccountBySite(){
			var marketplaceid = $('#site option:selected').val();
			$.ajax({
				type: 'post',
				url: '/showAccountBySite',
				data: {marketplaceid:marketplaceid,field:'mws_seller_id'},
				dataType:'json',
				success: function(res) {
					if(res.status==1){

						var html = '';
						$.each(res.data,function(i,item) {
							html += '<option value="'+item.id+'">'+item.label+'</option>';
						})
						var str = '<span class="input-group-addon">Account</span>\n' +
								'\t\t\t\t\t\t\t<select class="mt-multiselect btn btn-default" id="account" multiple="multiple" data-width="100%" data-action-onchange="true" name="account" id="account[]">\n' +
								'\n' +html+
								'\t\t\t\t\t\t\t</select>';
						$('#account-div').html(str);
						ComponentsBootstrapMultiselect.init();//处理account的多选显示样式
					}else{
						alert('请先选择站点');
					}
				}
			});

		}

        $(function(){
			getAccountBySite()//触发当前选的站点得到该站点所有的账号
            $("#search_table").trigger("click");
        })
	</script>
@endsection