@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>['edmCustomers']])
@endsection
@section('content')
	@include('frank.common')
	<style>
		#status,#mailchimp_status,#tag_id{
			width:100%;
		}
	</style>
	<div class="row">
		<div class="top portlet light">

			<div class="search_table" style="margin-left: -15px;margin-bottom: 50px;">
				<form id="search-form">
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">Email</span>
							<input class="form-control" value="" id="email" placeholder="Email Address" name="email"/>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">Status</span>
							<select class="btn btn-default " id="status"  name="status">
								<option value="">Select</option>
								@foreach($status as $key=>$value)
									<option value="{{ $key }}">{{ $value }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">Mailchimp Status</span>
							<select class="btn btn-default " id="mailchimp_status"  name="mailchimp_status">
								<option value="">Select</option>
								@foreach($mailchimp_status as $key=> $value)
									<option value="{{ $key }}">{{ $value }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon">EDM Tag</span>
							<select class="btn btn-default " id="tag_id"  name="tag_id">
								<option value="">Select</option>
								@foreach($tag as $key=>$value)
									<option value="{{ $key }}">{{ $value }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-md-1">
						<div class="input-group">
							<div class="btn-group pull-right" >
								<button id="search_table" class="btn sbold blue">Search</button>
							</div>
						</div>
					</div>
					@permission('edm-customers-add')
					<div class="col-md-1"  style="margin-left: -60px;">
						<div class="input-group">
							<div class="btn-group pull-right" ><button id="pull-by-mailchimp" class="btn sbold blue">Pull</button></div>
						</div>
					</div>
					<div class="col-md-1"  style="margin-left: -60px;">
						<div id="loading" style="display:none;"><img style="width:50px;heoght:50px;" src='/image/loading.gif' /></div>
					</div>
					@endpermission
				</form>
			</div>
				@permission('edm-customers-add')
				<div class="btn-group " style="float:right;margin-top:20px;">
					<div class="col-md-12">
						<form action="/edm/customers/import" method="post" enctype="multipart/form-data">
						<div class="col-md-4"  >
							<a href="/edm/customers/download" >Import Template
							</a>
						</div>
						<div class="col-md-4">
							{{ csrf_field() }}
							<input type="file" name="importFile"  style="width: 90%;"/>
						</div>
						<div class="col-md-2">
							<button type="submit" class="btn blue" id="data_search">Import</button>
						</div>
						</form>
						<div class="col-md-2"  >
							<a  data-toggle="modal" href="/edm/customers/add" target="_blank">
								<button class="btn sbold blue"> Add New
									<i class="fa fa-plus"></i>
								</button>
							</a>
						</div>
					</div>
				</div>
				@endpermission
{{--			</div>--}}

			<div>
			<table class="table table-striped table-bordered" id="datatable">
				<thead>
				<tr>
{{--					<th onclick="this===arguments[0].target && this.firstElementChild.click()">--}}
{{--						<input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>--}}
{{--					</th>--}}
					<th>Email Address</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Address</th>
					<th>Phone Number</th>
					<th>Group Name</th>
					<th>Status</th>
					<th>Mailchimp Status</th>
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
            // pagingType: 'bootstrap_extended',
            processing: true,
            // select: {
            //     style: 'os',
            //     info: true, // info N rows selected
            //     // blurable: true, // unselect on blur
            //     selector: 'td:first-child', // 指定第一列可以点击选中
            // },
            columns: [
                // {
                //     width: "1px",
                //     orderable: false,
                //     defaultContent: '',
                //     className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                // },
                {data: 'email',name:'email'},
                {data: 'first_name',name:'first_name'},
                {data: 'last_name',name:'last_name'},
                {data: 'address',name:'address'},
                {data: 'phone',name:'phone'},
                {data: 'tag_name',name:'tag_name'},
                {data: 'status',name:'status'},
                {data: 'mailchimp_status',name:'mailchimp_status'},
                {data: 'created_at',name:'created_at'},
                {data: 'updated_at',name:'updated_at'},
                {data: 'action',name:'action'},
            ],
            ajax: {
                type: 'POST',
                url: '/edm/customersList',
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

        $('.table').on('click', ".status_btn", function () {
		    var type = $(this).attr('data-type');
            var id = $(this).attr('data-id');
            $.ajax({
                type: 'post',
                url: '/edm/customers/action',
                data: {type:type,id:id},
                dataType:'json',
                success: function(res) {
                    if(res.status==1){
                        alert('success');
                        $("#search_table").trigger("click");
                    }else{
                        alert(res.msg);
                    }
                }
            });
		})
		$('#pull-by-mailchimp').click(function(){
            $.ajax({
                type: 'post',
                url: '/edm/customers/pullByMailchimp',
                dataType:'json',
                beforeSend:function(){
                    $("#loading").css('display','block');
                },
                success: function(res) {
                    if(res.status==1){
                        $("#loading").css('display','none');
                        alert(res.msg);
                        $("#search_table").trigger("click");
                    }else{
                        alert(res.msg);
                    }
                }
            });
            return false;
		})

        $(function(){
            $("#search_table").trigger("click");
        })
	</script>
@endsection