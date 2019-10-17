@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>['rsgUser']])
@endsection
@section('content')
<style>
	.table-container table th{
		text-align: center;
		vertical-align: middle;
	}
</style>

	<link rel="stylesheet" href="/js/chosen/chosen.min.css"/>
	<script src="/js/chosen/chosen.jquery.min.js"></script>

	@include('frank.common')

	<div class="portlet light bordered">
		<div class="portlet-body">
			<form id="search-form">
				<div class="table-toolbar" id="thetabletoolbar">
					<div class="row">
						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">From</span>
								<input  class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! $date_from !!}" id="date_from" name="date_from"
										autocomplete="off"/>
							</div>
						</div>

						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">To</span>
								<input  class="form-control" data-options="format:'yyyy-mm-dd'" value="{!! $date_to !!}" id="date_to" name="date_to"
										autocomplete="off"/>
							</div>
						</div>

						<div class="col-md-2">
							<div class="input-group">
								<span class="input-group-addon">Status</span>
								<select class="form-control" name="status">
									<option value="-1">Select</option>
									<option value="0">Inactive</option>
									<option value="1">Activated</option>
								</select>
							</div>
							<br>
						</div>

						<div class="btn-group">
							<button id="search" class="btn sbold blue">Search</button>
						</div>

					</div>
				</div>

			</form>

		</div>
		<div class="table-container" style="">
			<table class="table table-striped table-bordered" id="thetable">
				<thead>
				<tr>
					{{--<th onclick="this===arguments[0].target && this.firstElementChild.click()">--}}
						{{--<input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()" id="selectAll"/>--}}
					{{--</th>--}}
					<th>ID</th>
					<th>Email</th>
					<th>Date</th>
					<th>Status</th>
				</tr>
				</thead>
				<tbody></tbody>
			</table>

		</div>
	</div>
	</div>

	<script>

        XFormHelper.initByQuery('[data-init-by-query]')

        $("#thetabletoolbar [id^='date_']").each(function () {

            let defaults = {
                autoclose: true
            }

            let options = eval(`({${$(this).data('options')}})`)

            $(this).datepicker(Object.assign(defaults, options))
        })

        let $theTable = $(thetable)

        var initTable = function () {
            $theTable.dataTable({
                searching: false,//关闭搜索
                // search: {search: $("#search-form").serialize()},
                serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                // scrollX: 2000,
				pagingType: 'bootstrap_extended',
                processing: true,
                // ordering: false,
                order: [[0, 'desc']],
                aoColumnDefs: [ { "bSortable": false, "aTargets": [ ] }],
                select: {
                    style: 'os',
                    info: true, // info N rows selected
                    // blurable: true, // unselect on blur
                    selector: 'td:first-child', // 指定第一列可以点击选中
                },
                // "aoColumnDefs": [ { "bSortable": true, "aTargets": [] }],

                columns: [
                    // {
                    //     width: "1px",
                    //     defaultContent: '',
                    //     className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                    // },
                    {data: 'id', name: 'id'},
                    {data: 'email', name: 'email'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'status', name: 'status'},
                ],
                ajax: {
                    type: 'POST',
                    url: location.href,
                    data:  {search: $("#search-form").serialize()}
                }
            })
        }

        initTable();
        let dtApi = $theTable.api();

        //点击提交按钮重新绘制表格，并将输入框中的值赋予检索框
        $('#search').click(function () {
            dtApi.settings()[0].ajax.data = {search: $("#search-form").serialize()};
            dtApi.ajax.reload();
            return false;
        });
	</script>

@endsection