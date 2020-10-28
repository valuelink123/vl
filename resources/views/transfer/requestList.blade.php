@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>['Transfer Request']])
@endsection
@section('content')
	<link href="/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css" />
	<style>
		.table thead tr th,.table thead tr td,.table td, .table th{
			font-size:11px;
			white-space: nowrap;
			text-align:left;
		}
		table.dataTable thead th, table.dataTable thead td {
			padding: 8px 10px;
		}
		input[type=checkbox], input[type=radio]{
			margin:0px;
		}
		.DTFC_Cloned{
			margin-top:1px !important;

		#thetable .tractive{

		}
		}
	</style>

	<link rel="stylesheet" href="/js/chosen/chosen.min.css"/>
	<script src="/js/chosen/chosen.jquery.min.js"></script>

	<div class="portlet light bordered">
		<div class="portlet-body">
			<form id="search-form">
				<div class="table-toolbar" id="thetabletoolbar">
					<div class="row">
						<div class="col-md-2">
							<div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
								<span class="input-group-addon">Date From</span>
								<input  class="form-control" value="{{$date_from}}" data-options="format:'yyyy-mm-dd'" id="date_from" name="date_from"/>
							</div>
							<br>
							<div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
								<span class="input-group-addon">Date To</span>
								<input  class="form-control" value="{{$date_to}}" data-options="format:'yyyy-mm-dd'" id="date_to" name="date_to"/>
							</div>
						</div>

						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">Site</span>
								<select class="form-control"  id="site" name="site" onchange="getAccountBySite()">
									<option value="">Select</option>
									@foreach(getSiteCode() as $key=>$val)
										<option value="{!! $val !!}">{!! $key !!}</option>
									@endforeach
								</select>
							</div>
							<br>
							<div class="input-group" id="account-div">
								<span class="input-group-addon">Account</span>
								<select class="form-control"  id="account" name="account">
									<option value="">Select</option>
								</select>
							</div>
						</div>

						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">BG</span>
								<select class="form-control" id="bg" name="bg">
									<option value="">Select</option>
									@foreach($bgs as $bg)
										<option value="{!! $bg !!}">{!! $bg !!}</option>
									@endforeach
								</select>
							</div>
							<br>
							<div class="input-group">
								<span class="input-group-addon">BU</span>
								<select  class="form-control"  id="bu" name="bu">
									<option value="">Select</option>
									@foreach($bus as $bu)
										<option value="{!! $bu !!}">{!! $bu !!}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">销售员</span>
								<select  class="form-control"  id="user_id" name="user_id">
									<option value="">Select</option>
									@foreach(getUsers('seller_user') as $key=>$val)
										<option value="{!! $key !!}">{!! $val !!}</option>
									@endforeach
								</select>
							</div>
							<br><div class="input-group">
								<span class="input-group-addon">状态</span>
								<select  class="form-control"  name="status">
									<option value="">Select</option>
									@foreach(transferRequestStatus() as $key=>$val)
										<option value="{!! $key !!}">{!! $val !!}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">Asin</span>
								<input class="form-control" value="" id="asin" name="asin" autocomplete="off"/>
							</div>
							<br>
							<div class="input-group">
								<span class="input-group-addon">ItemNo</span>
								<input class="form-control" value="" id="item_no" name="item_no" autocomplete="off"/>
							</div>
						</div>
						<div class="col-md-2">
							<div class="input-group">
								<div class="btn-group pull-right" >
									<button id="search" class="btn sbold blue">Search</button>
								</div>
							</div>
							<br/>
						</div>
					</div>
				</div>
			</form>
			@permission('transfer-request-add')
				<div class="input-group" style="float:right;margin-top:10px;">
					<a  href="/transfer/request/add" target="_blank">
						<button class="btn sbold blue"> Add New
							<i class="fa fa-plus"></i>
						</button>
					</a>
				</div>
			@endpermission
		</div>
		@permission('transfer-request-update')
		@foreach ($operaStatus as $key=>$val)
			<div class="col-md-1" style="width:100px !important;margin-left:-15px;">
				<div class="input-group">
					<button type="button" class="btn btn-sm {{$val[1]}}" data-newstatus="{!! $key !!}" onclick="updateStatusAjax($(this))">{{$val[0]}}</button>
				</div>
			</div>
		@endforeach
		@endpermission

		@permission('transfer-plan-add')
		<div class="col-md-1" style="width:100px !important;margin-left:-15px;">
{{--			<a  href="/transfer/request/createPlan" onclick="checkAddPlan()" target="_blank">--}}
{{--				<button class="btn sbold blue"> Create Plan--}}
{{--					<i class="fa fa-plus"></i>--}}
{{--				</button>--}}
{{--			</a>--}}
			<div class="input-group">
				<button type="button" class="btn sbold blue blue-madison" onclick="checkAddPlan()">生成计划<i class="fa fa-plus"></i></button>
			</div>
		</div>
		@endpermission

		<div class="table-container" style="">
			<table class="table table-striped table-bordered dataTable " id="thetable">
				<thead>
				<tr>
					<th><input type="checkbox" id="selectAll" name="selectAll" /></th>
					<th>Asin</th>
					<th>Site</th>
					<th>Account</th>
					<th>ItemNo</th>
					<th>Quantity</th>
					<th>Delivery Date</th>
					<th>Seller</th>
					<th>BG</th>
					<th>BU</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody></tbody>
			</table>

		</div>
	</div>
	<div id="upload-attach" style="display:none;">
		<form id="upload-form" action="/transfer/request/uploadAttach" method="post" enctype="multipart/form-data"  tyle="width:500px;" >
			<input type="hidden" id="upload-id" name="id" value="">
			<div class="pull-left">
				{{ csrf_field() }}
				<input type="file" name="uploadFile[]" id="uploadFile" multiple="multiple"/>
			</div>
			<div class=" pull-left">
				<button type="submit" class="upload-btn btn blue btn-sm" id="data_search">上传</button>
			</div>
		</form>
	</div>

	<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/jquery.mockjax.js" type="text/javascript"></script>
	<script src="/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js" type="text/javascript"></script>
	<script src="/assets/pages/scripts/form-editable.min.js" type="text/javascript"></script>
	<script>

        let $theTable = $(thetable)

        var initTable = function () {
            $theTable.dataTable({
                searching: false,
                serverSide: true,
                "autoWidth":true,
                "lengthMenu": [
                    [20, 50, 100, -1],
                    [20, 50, 100, 'All']
                ],
                "pageLength": 20,
                pagingType: 'bootstrap_extended',
                processing: true,
                ordering:  false,
                //aoColumnDefs: [ { "bSortable": false, "aTargets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,21] }],
                order: [],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'asin', name: 'asin'},
                    {data: 'site', name: 'site'},
                    {data: 'account', name: 'account'},
                    {data: 'sku', name: 'sku'},
                    {data: 'quantity', name: 'quantity'},
                    {data: 'delivery_date', name: 'delivery_date'},
                    {data: 'seller_name', name: 'seller_name'},
                    {data: 'bg', name: 'bg'},
                    {data: 'bu', name: 'bu'},
                    {data: 'status_name', name: 'status_name'},
                    {data: 'action', name: 'action'},
                ],
                ajax: {
                    type: 'POST',
                    url: location.href,
                    data:  {search: $("#search-form").serialize()},

                },
                // scrollY:        false,
                // scrollX:        true,
                // fixedColumns:   {
                //     leftColumns:9,
                //     rightColumns: 0,
                //     "drawCallback": function(){
                //         $(".DTFC_Cloned input[id='selectAll']").on('change',function(e) {
                //             $(".DTFC_Cloned input[name='checkedInput']").prop("checked", this.checked);
                //         });
                //     }
				//
                // },
            })
        }

        initTable();
        let dtApi = $theTable.api();
        jQuery(document).ready(function() {
            $('#search').click(function () {
                dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
                dtApi.ajax.reload();
                return false;
            });

            $('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
        });
        //全选
        $("body").on('change','#selectAll',function(e){
            $("input[name='checkedInput']").prop("checked", this.checked);
            checkClass();
        });
        //单条选中
        $("body").on('change','.checkbox-item',function(e){
            var $subs = $("input[name='checkedInput']");
            var check = $subs.length == $subs.filter(":checked").length ? true :false;
            $("input[name='selectAll']").prop("checked", check);
            checkClass();
            e.cancelBubble=true;
        });
		//检测selected类名，是否选中显示
        function checkClass(){
            $("input[name='checkedInput']").parent().parent().removeClass('selected');
            $("#thetable input[name='checkedInput']:checked").each(function (index,value) {
                $(this).parent().parent().addClass('selected');
			});
		}

        //上传大货资料操作
        $("#thetable").on('click', '.up-attach',function(){
            var id = $(this).attr('data-id');
			$('#upload-id').val(id);
            art.dialog({
                id: 'art_upload-attach',
                title: 'upload attach',
				lock:true,
                content: document.getElementById('upload-attach'),
                ok: false,
                cancel: true,
                cancelVal:'Cancel'
            });
            return false;
        });
		//生成计划
		function checkAddPlan(){
            let ids_value = '';//id的拼接值
            var flag = 0;//flag判断选中的数据是否符合要求，例如状态值为0才能bu审核
            //newStatus==1和,3的时候，原状态为0;为2和4的时候，原状态为1;为5,6的时候，原状态为2，为7的时候，原状态随便
            var flag_msg = '';
            $("#thetable input[name='checkedInput']:checked").each(function (index,value) {
                var status = $(this).attr('data-status');
                var asin = $(this).attr('data-asin');
                if(status!=6){//计划确认状态才可以生成计划
                    flag = 1;
                    flag_msg = '此asin:' + asin + '状态不符合(计划确认状态才可以生成计划)';
                    return false;//跳出循环
                }
                if(ids_value != ''){
                    ids_value = ids_value + ',' + $(this).val()
                }else{
                    ids_value = ids_value + $(this).val()
                }
            });
            if(flag==1){//状态不匹配不能进行更改
                alert(flag_msg);
                return false;
            }
            if(ids_value == ""){
                alert('请先选择需要审核的数据!');
                return false;
            }
            console.log(ids_value);
            window.open('/transfer/plan/createPlan?id='+ids_value);
		}
        //审核更新状态
        function updateStatusAjax(obj){
            newStatus = obj.attr('data-newstatus');
            message = '确定' + obj.html() + '吗?';
            if(!confirm(message)){return false;};//点击取消，不更改
            let ids_value = '';
            var flag = 0;//flag判断选中的数据是否符合要求，例如状态值为0才能bu审核
			//newStatus==1和,3的时候，原状态为0;为2和4的时候，原状态为1;为5,6的时候，原状态为2，为7的时候，原状态随便
			var flag_msg = '';
            $("#thetable input[name='checkedInput']:checked").each(function (index,value) {
                var status = $(this).attr('data-status');
                var asin = $(this).attr('data-asin');
                if(((newStatus==1 || newStatus==3) && status!=0) || ((newStatus==2 || newStatus==4) && status!=1) || ((newStatus==5 || newStatus==6) && status!=2)){
                    flag = 1;
                    flag_msg = '此asin:' + asin + '状态不符合';
                    return false;//跳出循环
				}
                if(ids_value != ''){
                    ids_value = ids_value + ',' + $(this).val()
                }else{
                    ids_value = ids_value + $(this).val()
                }
            });
            if(flag==1){//状态不匹配不能进行更改
				alert(flag_msg);
				return false;
			}

            if(ids_value == ""){
                alert('请先选择需要审核的数据!')
            }else{
                $.ajax({
                    type: "POST",
                    url: "/transfer/request/updateStatus",
                    data: {ids: ids_value,status:newStatus},
                    success: function (res) {
                        if(res){
                            toastr.success("更新成功！");
                            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
                            dtApi.ajax.reload();
						}else{
                            toastr.error("更新异常！");
						}

                    },
                    error: function(err) {
                        toastr.error("err");
                    }
                });

            }
        }
        //通过选中的站点得到账号
        function getAccountBySite(){
            var marketplaceid = $('#site option:selected').val();
            $.ajax({
                type: 'post',
                url: '/showAccountBySite',
                data: {marketplaceid:marketplaceid},
                dataType:'json',
                success: function(res) {
                    if(res.status==1){
                        var html = '<option value="">Select</option>';
                        $.each(res.data,function(i,item) {
                            html += '<option value="'+item.id+'">'+item.label+'</option>';
                        })
                        $('#account').html(html);
                    }else{
                        alert('请先选择站点');
                    }
                }
            });

        }
	</script>

@endsection