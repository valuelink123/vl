@extends('layouts.layout')
@section('label', 'Config Options')
@section('content')

<h1 class="page-title font-red-intense">
    Config Option List
</h1>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption font-dark">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject bold uppercase">Config Option List</span>
                </div>
            </div>
            <div class="table-toolbar">
                <div class="row">
                    <div class="col-md-6">
                        @permission('config-option-create')
                        <div class="btn-group">
                            <a href="{{ url('config_option/create')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add New
                                    <i class="fa fa-plus"></i>
                                </button>
                            </a>
                        </div>
                        @endpermission
                    </div>
                    <div class="col-md-6">
                        @permission('config-option-update')
                        <div class="btn-group ">
                            <form role="form" action="" method="GET">
                                {{ csrf_field() }}
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">Parent Name</span>
                                        <select class="form-control" name="" id="co_pid">
                                            <option value="">Select...</option>
                                            @foreach ($id_name_pairs as $k => $v)
                                                @if($id_pid_pairs[$k] == '0')
                                                    <option value="{{$k}}">{{$v}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">Status</span>
                                        <select class="form-control" name="" id="co_status">
                                            <option value="">Select...</option>
                                            @foreach (getConfigOptionStatus() as $key => $value)
                                                <option value="{{$key}}" >
                                                    {{$value}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </form>
                        </div>
                        @endpermission
                    </div>
                </div>
            </div>
            <div class="portlet-body">

                 <table class="table table-striped table-bordered table-hover table-checkable order-column" id="the_table">
                    <thead>
                    <tr>
                        <th > ID </th>
                        <th> Parent Name </th>
                        <th> Name </th>
                        <th> Order </th>
                        <th> Status </th>
                        <th> Created At </th>
                        <th> Updated At </th>
                        <th> Action </th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                 </table>
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>




<script>

    $dataTablesSettings = {
        // Internationalisation. For more info refer to http://datatables.net/manual/i18n
        "language": {
            "aria": {
                "sortAscending": ": activate to sort column ascending",
                "sortDescending": ": activate to sort column descending"
            },
            "emptyTable": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ records",
            "infoEmpty": "No records found",
            "infoFiltered": "(filtered1 from _MAX_ total records)",
            "lengthMenu": "Show _MENU_",
            "search": "Search:",
            "zeroRecords": "No matching records found",
            "paginate": {
                "previous":"Prev",
                "next": "Next",
                "last": "Last",
                "first": "First"
            }
        },

        "bStateSave": false, // if true, save datatable state(pagination, sort, etc) in cookie.

        "lengthMenu": [
            [5, 15, 20, -1],
            [5, 15, 20, "All"] // change per page values here
        ],
        // set the initial value
        "pageLength": 20,
        "pagingType": "bootstrap_full_number",
        "columnDefs": [
            {
                "className": "dt-right",
                //"targets": [2]
            }
        ],

        columns: [
            {data: 'id', name: 'id'},
            {data: 'co_pid', name: 'co_pid'},
            {data: 'co_name', name: 'co_name'},
            {data: 'co_order', name: 'co_order'},
            {data: 'co_status', name: 'co_status'},
            {data: 'created_at', name: 'created_at'},
            {data: 'updated_at', name: 'updated_at'},
            {data: 'action', name: 'action'}
        ],

        //默认以某列排序，列的序号从0开始。
        "order": [[3, "asc"]],
        //指定某列不能排序。列的序号从0开始。
        "aoColumnDefs": [ { "bSortable": false, "aTargets": [7] }],

        "fnServerParams": function (aoData) {
        },

        ajax: {
            type: 'POST',
            url: "/config_option/get"
        }

    };

    var data_table = $('#the_table').dataTable($dataTablesSettings);

    $('select').on('change', function() {
        var params = [
                        {"name": "co_pid", "value": $('#co_pid').val()},
                        {"name": "co_status", "value": $('#co_status').val()}
                      ];
        redraw_data_table(data_table, $dataTablesSettings, params);

        //这里重新设置参数
        $dataTablesSettings.fnServerParams = function (aoData) {
            aoData.push(
                {"name": "co_pid", "value": $('#co_pid').val()},
                {"name": "co_status", "value": $('#co_status').val()}
            );

        }

        //搜索就是设置参数，然后销毁datatable重新再建一个
        data_table.fnDestroy(false);
        data_table = $("#the_table").dataTable($dataTablesSettings);
        //搜索后跳转到第一页
        data_table.fnPageChange(0);
    });

    function change_status(id){

        $dataTablesSettings.fnServerParams = function (aoData) {
            aoData.push(
                {"name": "co_pid", "value": $('#co_pid').val()},
                {"name": "co_status", "value": $('#co_status').val()},
                {"name": "id", "value": id}
            );
        }

        data_table.fnDestroy(false);
        data_table = $("#the_table").dataTable($dataTablesSettings);
        data_table.fnPageChange(0);

    }

    $(function() {
        var TableDatatablesManaged = function () {

            var initTable = function () {
                data_table;
            }

            return {
                //main function to initiate the module
                init: function () {
                    if (!jQuery().dataTable) {
                        return;
                    }
                    initTable();
                }
            };

        }();

        TableDatatablesManaged.init();
    });


</script>


@endsection
