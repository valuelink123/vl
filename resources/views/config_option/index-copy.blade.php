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
                        {{--<tbody></tbody>--}}
                        <tbody>
                        @foreach ($config_options as $config_option)
                            <form id="{{'form_'.$config_option['id'] }}">
                            {{ csrf_field() }}
                            <tr class="odd gradeX">
                                <td>
                                    {{$config_option['id']}}
                                </td>
                                <td>
                                    @if($config_option['co_pid'] == '0')
                                    -
                                    @else
                                    {{$id_name_pairs[$config_option['co_pid']]}}
                                    @endif
                                </td>
                                <td>
                                    {{$config_option['co_name']}}
                                </td>
                                <td>
                                    {{$config_option['co_order']}}
                                </td>
                                <td id="{{ 'td_status_'.$config_option['id'] }}">
                                    @if($config_option['co_status']=='0')
                                    Visible
                                    @else
                                    Hidden
                                    @endif
                                </td>
                                <td>
                                    {{$config_option['created_at']}}
                                </td>
                                <td>
                                    {{$config_option['updated_at']}}
                                </td>

                                <td>
                                    @if($config_option['co_pid'] == 0)
                                    -
                                    @else
                                    <a href="{{ url('config_option/'.$config_option['id'].'/edit') }}">
                                        <button type="submit" class="btn btn-success btn-xs">Edit</button>
                                    </a>
                                    <input type="hidden" name="{{ 'input_'.$config_option['id'] }}" value="111111" />
                                    <button type="button" id="{{ 'btn_status_'.$config_option['id'] }}" id_num="{{ $config_option['id'] }}" class="status_btn btn {{$config_option['co_status']?'btn-danger':'btn-success'}} btn-xs" name="btn_status" value="{{$config_option['co_status']?0:1}}">{{$config_option['co_status']?'Enable':'Disable'}}</button>


                                    @endif
                                </td>
                            </tr>
                            </form>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




    <script>

        $(".status_btn").on('click', function() {

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
            //默认以某列排序，列的序号从0开始。
            "order": [[3, "asc"]],
            //指定某列不能排序。列的序号从0开始。
            "aoColumnDefs": [ { "bSortable": false, "aTargets": [7] }],

        };

        var data_table = $('#the_table').dataTable($dataTablesSettings);

        $('select').on('change', function() {
            var columns_array = [data_table.api().columns(1), data_table.api().columns(4)];
            var co_pid_text = $.trim($('#co_pid option:selected').text());
            var co_pid_filter = co_pid_text == 'Select...' ? '' : co_pid_text;

            var co_status_text = $.trim($('#co_status option:selected').text());
            var co_status_filter = co_status_text == 'Select...' ? '' : co_status_text;
            var columns_filters = [co_pid_filter,co_status_filter];

            for(i=0;i<2;i++){
                var val = $.fn.dataTable.util.escapeRegex(
                    columns_filters[i]
                );

                var filter_regex = val? '^'+val+'$' : '';
                columns_array[i].search(filter_regex, true, false )
                    .draw();
            }

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

    </script>


@endsection
