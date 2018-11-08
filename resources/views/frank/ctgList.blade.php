@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>['CTG']])
@endsection
@section('content')

    @include('frank.common')

    <h1 class="page-title font-red-intense"> CTG List
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <div class="table-toolbar">
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="btn-group " style="float:right;">

                            <button id="excel-export" class="btn sbold blue"> Export
                                <i class="fa fa-download"></i>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both;height:50px;"></div>
            <div class="table-container" style="">
                <table class="table table-striped table-bordered" id="thetable">
                    <thead>
                    <tr>
                        <th onclick="this===arguments[0].target && this.firstElementChild.click()">
                            <input type="checkbox" onchange="this.checked?dtApi.rows().select():dtApi.rows().deselect()"/>
                        </th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Customer Email</th>
                        <th>Phone Number</th>
                        <th>Expect Rating</th>
                        <th>Commented</th>
                        <th>Status</th>
                        <th>Processor</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>

        let $theTable = $(thetable)

        $theTable.dataTable({
            searching: false, // 不使用自带的搜索框
            search: {search: queryStringToObject().search},
            serverSide: true,
            pagingType: 'bootstrap_extended',
            processing: true,
            order: [[1, 'desc']],
            select: {
                style: 'os',
                info: true, // info N rows selected
                blurable: true, // unselect on blur
                selector: 'td:first-child', // 指定第一列可以点击选中
            },
            columns: [
                {
                    width: "1px",
                    orderable: false,
                    defaultContent: '',
                    className: 'select-checkbox', // 该类根据 tr:selected 改变自己的背景
                },
                {
                    width: "55px",
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    width: "20px",
                    data: 'name',
                    name: 'name'
                },
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {
                    width: "20px",
                    data: 'rating',
                    name: 'rating'
                },
                {
                    width: "20px",
                    data: 'commented',
                    name: 'commented',
                    render(data) {
                        return parseInt(data) > 0 ? 'Yes' : 'No'
                    }
                },
                {
                    width: "80px",
                    data: 'status',
                    name: 'status',
                    render(data) {
                        return data.toUpperCase()
                    }
                },
                {
                    width: "120px",
                    data: 'processor',
                    name: 'processor'
                },
                {
                    width: "20px",
                    data: 'order_id',
                    name: 'order_id',
                    orderable: false,
                    render(data) {
                        return `<a class="btn btn-danger btn-xs" href="/ctg/list/process?order_id=${data}" target="_blank">Process</a>`
                    }
                }
            ],
            ajax: {
                type: 'POST',
                url: location.href
            }
        })

        let dtApi = $theTable.api()

    </script>

@endsection