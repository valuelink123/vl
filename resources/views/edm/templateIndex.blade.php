@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>['edmTemplate']])
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
							<span class="input-group-addon">Name</span>
							<input class="form-control" value="" id="name" placeholder="Name" name="name"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<div class="btn-group pull-right" >
								<button id="search_table" class="btn sbold blue">Search</button>
							</div>
						</div>
					</div>
				</form>
			</div>
			@permission('edm-customers-add')
			<div class="btn-group " style="float:right;margin-top:20px;">
				<div class="col-md-12">
					<div class="col-md-2"  >
						<a  data-toggle="modal" href="/edm/template/add" target="_blank">
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
						<th>Name</th>
						<th>Abstract</th>
						<th>Date Added</th>
						<th>Last Changed</th>
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
                {data: 'name',name:'name'},
                {data: 'abstract',name:'abstract'},
                {data: 'created_at',name:'created_at'},
                {data: 'updated_at',name:'updated_at'},
                {data: 'action',name:'action'},
            ],
            ajax: {
                type: 'POST',
                url: '/edm/templateList',
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

        $(function(){
            $("#search_table").trigger("click");
        })
	</script>
@endsection