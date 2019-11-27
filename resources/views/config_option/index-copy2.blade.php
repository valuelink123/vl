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
                                                @foreach (getConfigOptionStatus() as $value)
                                                    <option value="{{$value}}" >
                                                        @if($value =='0')
                                                            Visible
                                                        @else
                                                            Hidden
                                                        @endif
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
                        {{--<tbody>--}}
                        {{--@foreach ($config_options as $config_option)--}}
                            {{--<form id="{{'form_'.$config_option['id'] }}">--}}
                            {{--{{ csrf_field() }}--}}
                            {{--<tr class="odd gradeX">--}}
                                {{--<td>--}}
                                    {{--{{$config_option['id']}}--}}
                                {{--</td>--}}
                                {{--<td>--}}
                                    {{--@if($config_option['co_pid'] == '0')--}}
                                    {{-----}}
                                    {{--@else--}}
                                    {{--{{$id_name_pairs[$config_option['co_pid']]}}--}}
                                    {{--@endif--}}
                                {{--</td>--}}
                                {{--<td>--}}
                                    {{--{{$config_option['co_name']}}--}}
                                {{--</td>--}}
                                {{--<td>--}}
                                    {{--{{$config_option['co_order']}}--}}
                                {{--</td>--}}
                                {{--<td id="{{ 'td_status_'.$config_option['id'] }}">--}}
                                    {{--@if($config_option['co_status']=='0')--}}
                                    {{--Visible--}}
                                    {{--@else--}}
                                    {{--Hidden--}}
                                    {{--@endif--}}
                                {{--</td>--}}
                                {{--<td>--}}
                                    {{--{{$config_option['created_at']}}--}}
                                {{--</td>--}}
                                {{--<td>--}}
                                    {{--{{$config_option['updated_at']}}--}}
                                {{--</td>--}}

                                {{--<td>--}}
                                    {{--@if($config_option['co_pid'] == 0)--}}
                                    {{-----}}
                                    {{--@else--}}
                                    {{--<a href="{{ url('config_option/'.$config_option['id'].'/edit') }}">--}}
                                        {{--<button type="submit" class="btn btn-success btn-xs">Edit</button>--}}
                                    {{--</a>--}}
                                    {{--<button type="button" id="{{ 'btn_status_'.$config_option['id'] }}" id_num="{{ $config_option['id'] }}" class="status_btn btn {{$config_option['co_status']?'btn-danger':'btn-success'}} btn-xs" name="btn_status" value="{{$config_option['co_status']?0:1}}">{{$config_option['co_status']?'Enable':'Disable'}}</button>--}}


                                    {{--@endif--}}
                                {{--</td>--}}
                            {{--</tr>--}}
                            {{--</form>--}}
                        {{--@endforeach--}}

                        {{--</tbody>--}}
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




    <script>


//        var js_id_name_pairs = {$id_name_pairs};
//js读取PHP数组
var js_id_name_pairs=eval(<?php echo json_encode($id_name_pairs);?>)


//        console.log(js_id_name_pairs);

//        var js_id_name_pairs_obj = eval("("+js_id_name_pairs+")");

//        XFormHelper.initByQuery('[data-init-by-query]')
//
//        $("#thetabletoolbar [id^='date_']").each(function () {
//
//            let defaults = {
//                autoclose: true
//            }
//
//            let options = eval(`({${$(this).data('options')}})`)
//
//            $(this).datepicker(Object.assign(defaults, options))
//        })
//
//        $("#thetabletoolbar select[multiple]").chosen()
//
//        $(thetabletoolbar).change(e => {
//            dtApi.ajax.reload()
//        })
//
//        let $theTable = $(thetable)
//
//        $theTable.on('preXhr.dt', (e, settings, data) => {
//
//            Object.assign(data.search, {
//            daterange: {
//                from: date_from.value,
//                to: date_to.value
//            },
//            ands: {
//                email: email.value,
//                amazon_order_id: amazon_order_id.value,
//                facebook_group: facebook_group.value
//            },
//            ins: {
//                processor: $('#processor').val(),
//                bg: $('#bg').val(),
//                bu: $('#bu').val(),
//                from: $('#from').val(),
//                country: $('#country').val(),
//                brand: $('#brand').val(),
//            }
//        })
//
//        history.replaceState(null, null, '?' + objectToQueryString(data.search))
//
//        $('#search_str').val(objectToQueryString(data.search));
//        })
        var id_name_pairs = ""
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
                {
                    data: 'co_pid',
                    name: 'co_pid',
                    render(data){
                        {{--var pid = "{{$config_option['data']}}";--}}
                        if(data == '0'){
                            return '-';
                        }
                        else{
                            return js_id_name_pairs[data];
                        }


                    }
                },
                {data: 'co_name', name: 'co_name'},
                {data: 'co_order', name: 'co_order'},
                {
                    data: 'co_status',
                    name: 'co_status',
                    render(data){
                        if(data == "0"){
                            return "Visible";
                        }
                        else{
                            return "Hidden";
                        }


                    }


                },
                {data: 'created_at', name: 'created_at'},
                {data: 'updated_at', name: 'updated_at'},
                {data: 'action', name: 'action'},
            ],

            //默认以某列排序，列的序号从0开始。
            "order": [[3, "asc"]],
            //指定某列不能排序。列的序号从0开始。
            "aoColumnDefs": [ { "bSortable": false, "aTargets": [7] }],

            "fnServerParams": function (aoData) {
//                aoData._rand = Math.random();
            },

            ajax: {
                type: 'POST',
                url: "/config_option/get"
            }

        };

        var data_table = $('#the_table').dataTable($dataTablesSettings);

        $('select').on('change', function() {

            //这里重新设置参数
            $dataTablesSettings.fnServerParams = function (aoData) {
//                aoData._rand = Math.random();
                aoData.push(
                    {"name": "co_pid", "value": $('#co_pid').val()},
                    {"name": "co_status", "value": $('#co_status').val()}
                );

                console.log(aoData);
            }

//            console.log("aaaaaaa");
//
            //搜索就是设置参数，然后销毁datatable重新再建一个
            data_table.fnDestroy(false);
            data_table = $("#the_table").dataTable($dataTablesSettings);
            //搜索后跳转到第一页
            data_table.fnPageChange(0);
        });

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

//$("#thetabletoolbar [id^='date_']")

//        $(".status_btn").on('click', function() {
            console.log('1111111');
            return false;


            var id = $(this).attr('id_num');
            //
            var status = $(this).attr('value');

            $.ajax({
                type: 'post',
                url: '/config_option/' + id + '/update_status',
                //data:$("#form_" + id).serialize(),
                data:{'status': status},
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        var msg = res.msg;
                        var co_status = res.co_status;

                        if (msg == "successful") {

                            if(co_status == "0"){
                                $("#td_status_" + id).text('Visible');
                                //cannot use $(this)
                                $("#btn_status_" + id).attr('value', 1);
                                $("#btn_status_" + id).attr('class', 'btn btn-success btn-xs');
                                $("#btn_status_" + id).text('Disable');
                            }else{
                                $("#td_status_" + id).text('Hidden');
                                $("#btn_status_" + id).attr('value', 0);
                                $("#btn_status_" + id).attr('class', 'btn btn-danger btn-xs');
                                $("#btn_status_" + id).text('Enable');
                            }

                            return false;
                        }
                        else {
                            return false;
                        }
                    }
                    else {
                        //操作失败
                        alert('Failed');
                    }
                },
                error: function (res){
                }
            })

            return false;
        })


    </script>


@endsection
