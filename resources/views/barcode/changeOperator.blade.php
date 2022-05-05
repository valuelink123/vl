@extends('layouts.layout')
@section('crumb')
    Change Operator
@endsection
@section('content')
    <style>
        .search-btn {
            background-color: #63C5D1;
            color: #ffffff;
            font-size: 14px;
            text-align: center;
            width: 70px;
            height: 30px;
            border-radius: 5px 5px 5px 5px !important;
        }

        select {
            width: 258px;
            height: 26px;
        }

    </style>
    <div class="row">


        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div style="height: 15px;"></div>

                <div class="portlet-title">
                    {{--新添加的状态统计数据--}}
                    <form id="search-form" action="{{ url('/barcode/modifyOperator') }}" method="post">
                        {{ csrf_field() }}
                        <div class="table-toolbar" id="thetabletoolbar">
                            <div style="width:258px; float:left; margin-right:10px">
                                <div>当前采购人员</div>
                                <select name="operatorId" id="operatorId" data-width="205px" class="selectpicker"
                                        data-live-search="true">
                                    <option value="">请选择</option>
                                    @foreach ($operators as $operator_id=>$operator_name)
                                        <option value="{{$operator_id}}">{{$operator_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="width:258px; float:left; margin-right:10px">
                                <div>新的采购人员</div>
                                <select name="newOperatorId" id="newOperatorId" data-width="205px" class="selectpicker"
                                        data-live-search="true">
                                        <option value="">请选择</option>
                                        @foreach ($operators as $operator_id=>$operator_name)
                                            <option value="{{$operator_id}}">{{$operator_name}}</option>
                                        @endforeach
                                </select>
                            </div>
                            <div style="float:left; margin-right:10px">
                                <div>&nbsp;</div>
                                <input type="text" name="vendorText" id="vendorText"
                                       style="width: 250px; height: 34px; border: 1px solid #dddddd;"
                                       placeholder="输入供应商名称或代码"/>
                                <button id="search" type="button" class="search-btn">搜索
                                </button>
                            </div>
                            <div style="clear: both"></div>
                            <div style="height:10px"></div>
                            <div style="float:left; margin-right:10px;">
                                <button id="changeBtn" type="button" class="search-btn">更新</button>
                            </div>
                        </div>
                    </form>

                </div>
                <div style="height: 20px;"></div>
                <div class="portlet-body">
                    <div class="table-container">

                        <div style="overflow:auto;width: 100%;">
                            <table class="table table-striped table-bordered table-hover table-checkable" id="thetable">
                                <thead>
                                <tr role="row" class="heading">
{{--                                    <th onclick="this===arguments[0].target && this.firstElementChild.click()">--}}
{{--                                        <input type="checkbox"--}}
{{--                                               onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()"--}}
{{--                                               id="selectAll"/>--}}
{{--                                    </th>--}}

                                    <th><input type="checkbox" id="selectAll" /></th>
                                    <th>供应商序号</th>
                                    <th>供应商编码</th>
                                    <th>供应商名称</th>
                                    <th>采购人员</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
            {{--</div>--}}
        </div>


        <div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body">
                        <img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
                        <span>Loading... </span>
                    </div>
                </div>
            </div>
        </div>
        <script>


            let $theTable = $(thetable)

            var initTable = function () {
                $theTable.dataTable({
                    searching: false,//关闭搜索
                    serverSide: true,//启用服务端分页（这是使用Ajax服务端的必须配置）
                    "lengthMenu": [
                        [10, 50, 100, -1],
                        [10, 50, 100, 'All'] // change per page values here
                    ],
                    "pageLength": 50, // default record count per page
                    pagingType: 'bootstrap_extended',
                    processing: true,
                    scrollX: false,
                    ordering: true,
                    aoColumnDefs: [
                        {"bSortable": false, "aTargets": [0]},
                        {"bVisible": false, "aTargets": []} //隐藏列
                        ],
                    order: [],
                    select: {
                        style: 'os',
                        info: true, // info N rows selected
                        // blurable: true, // unselect on blur
                        selector: 'td:first-child', // 指定第一列可以点击选中
                    },
                    columns: [
                        // {
                        //     width: "1px",
                        //     defaultContent: '',
                        //     className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                        // },

                        {data: 'checkbox_id', name: 'checkbox_id'},
                        {data: 'id', name: 'id'},
                        {data: 'vendor_code', name: 'vendor_code'},
                        {data: 'vendor_name', name: 'vendor_name'},
                        {data: 'operator', name: 'operator'},
                    ],

                    ajax: {
                        type: 'POST',
                        url: "{{ url('barcode/getVendorTable')}}",
                        data: {search: decodeURIComponent($("#search-form").serialize().replace(/\+/g, " "), true)},
                    }
                });
            }
            $("input[id='selectAll']").on('change', function (e) {
                $("input[name='checkedInput']").prop("checked", this.checked);
            }),

            initTable();
            let dtApi = $theTable.api();

            var grid = new Datatable();

            //点击提交按钮重新绘制表格，并将输入框中的值赋予检索框
            $('#search').click(function () {
                dtApi.settings()[0].ajax.data = {search: decodeURIComponent($("#search-form").serialize().replace(/\+/g, " "), true)};
                dtApi.ajax.reload();
                return false;
            });


            $('#operatorId').change(function () {
                $("#search").trigger("click");
            });

            $('#changeBtn').click(function () {
                $operatorId = $('#operatorId').val();
                $newOperatorId = $('#newOperatorId').val();
                // alert($newOperatorId);  return false;
                if($operatorId == '' && $newOperatorId == ''){
                    alert('当前操作者和新操作者均未选择');
                    return false;
                }
                if($operatorId == $newOperatorId){
                    alert('当前操作者和新操作者是同一人');
                    return false;
                }

                // let selectedRows = dtApi.rows({selected: true})
                // //e.g. 51,66,92 到后台是一个数组
                // let vendorIdRows = selectedRows.data().toArray().map(obj => [obj.id])
                // if (!vendorIdRows.length) {
                //     alert('请选择至少一行');
                //     return false;
                // }
                let chk_value = '';
                $("input[name='checkedInput']:checked").each(function (index, value) {
                    if (chk_value != '') {
                        chk_value = chk_value + ',' + $(this).val()
                    } else {
                        chk_value = chk_value + $(this).val()
                    }
                });
                if (chk_value == "") {
                    alert('请先选择需要提交的数据!')
                    return false;
                }
                $.ajax({
                    type: 'post',
                    url: '/barcode/modifyOperator',
                    data: {
                        vendorIdRows: chk_value,
                        operatorId: $operatorId,
                        newOperatorId: $newOperatorId,
                        _token: '{{csrf_token()}}',
                    },
                    dataType: 'json',
                    success: function (res) {
                        console.log(res)
                        if (res) {
                            alert(res.msg);
                            $("#search").trigger("click");

                        } else {
                            //编辑失败

                        }
                    }
                });
            });

            $(function () {
                $("#ajax").on("hidden.bs.modal", function () {
                    $(this).find('.modal-content').html('<div class="modal-body"><img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"><span>Loading... </span></div>');
                });
            });

        </script>

    </div>
@endsection