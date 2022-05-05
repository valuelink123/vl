@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['Barcode']])
@endsection
<style type="text/css">
    .search-btn {
        background-color: #63C5D1;
        color: #ffffff;
        font-size: 14px;
        text-align: center;
        width: 70px;
        height: 30px;
        border-radius: 5px 5px 5px 5px !important;
    }

    .barcode-btn {
        background-color: #63C5D1;
        color: #ffffff;
        font-size: 14px;
        text-align: center;
        width: 100px;
        height: 30px;
        border-radius: 5px 5px 5px 5px !important;
    }

    th {
        text-align: center;
    }
</style>

@section('content')
    @include('frank.common')
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div style="height: 15px;"></div>

                <div class="portlet-title">
                    <form id="search-form">
                        {{--                    {{ csrf_field() }}--}}
                        <div class="table-toolbar" id="thetabletoolbar">
                            <div style="height: 15px;"></div>


                            <div class="pull-right">
                                @if($canChangeOperator)
                                <button id="changeOperator" class="barcode-btn">变更采购员
                                </button>
                                @else
                                @endif
                                <button id="addNewVendor" class="barcode-btn">新增供应商
                                </button>
{{--                                <button id="makeTokenBarcode" class="barcode-btn">条码演示--}}
{{--                                </button>--}}
                                <button id="generateBarcode" class="barcode-btn">生成条码
                                </button>
                                <button id="printBarcode" class="barcode-btn">打印条码
                                </button>
                                <input type="text" name="vendorText" id="vendorText"
                                       style="width: 240px; height: 29px; border: 1px solid #dddddd;"
                                       placeholder="输入供应商名称或代码(VOP,SAP均可)"/>
                                <button id="search" type="button" class="search-btn">搜索
                                </button>
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
                                    <th>供应商序号</th>
                                    <th>供应商代码(VOP)</th>
                                    <th>供应商代码(SAP)</th>
                                    <th>供应商名称</th>
                                    <th>采购人员</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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
                //processing: true,
                scrollX: false,
                ordering: true,
                aoColumnDefs: [
                    {"bSortable": false, "aTargets": []},
                    { "bVisible": false, "aTargets": [] }
                    ],
                order: [],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'vendor_code', name: 'vendor_code'},
                    {data: 'vendor_code_from_sap', name: 'vendor_code_from_sap'},
                    {data: 'vendor_name', name: 'vendor_name'},
                    {data: 'operator', name: 'operator'},
                    {data: 'enter', name: 'enter'},
                ],
                ajax: {
                    type: 'POST',
                    url: "{{ url('barcode/getVendorList')}}",
                    data: {
                        search: decodeURIComponent($("#search-form").serialize().replace(/\+/g, " "), true),
                        '_token': '{{csrf_token()}}'
                    },
                }
            });
        }

        initTable();
        let dtApi = $theTable.api();

        //点击提交按钮重新绘制表格，并将输入框中的值赋予检索框
        $('#search').click(function () {

            dtApi.settings()[0].ajax.data = {
                search: decodeURIComponent($("#search-form").serialize().replace(/\+/g, " "), true),
                '_token': '{{csrf_token()}}'
            };
            dtApi.ajax.reload();
            return false;
        });


        $('#changeOperator').click(function () {
            window.open('/barcode/changeOperator');
        });
        $('#addNewVendor').click(function () {
            window.open('/barcode/addNewVendor');
        });
        $('#makeTokenBarcode').click(function () {
            window.open('/barcode/makeTokenBarcode');
        });
        $('#generateBarcode').click(function () {
            window.open('/barcode/generateBarcode');
        });
        $('#printBarcode').click(function () {
            window.open('/barcode/printBarcode');
        });

        $(function () {
            $("#ajax").on("hidden.bs.modal", function () {
                $(this).find('.modal-content').html('<div class="modal-body"><img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"><span>Loading... </span></div>');
            });
        });
    </script>
@endsection