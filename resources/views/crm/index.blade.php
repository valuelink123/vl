@extends('layouts.layout')
@section('label', 'CRM')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-dark col-md-6">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">CRM</span>
                    </div>

                    <div class="col-md-12" style="padding: 0px;">

                        @permission('crm-add')
                        <a  data-toggle="modal" href="{{ url('crm/create')}}" target="_blank"><button id="sample_editable_1_2_new" class="btn sbold red"> Add New
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>
                        @endpermission
                        @permission('crm-export')
                        <button id="crm-export" class="btn sbold blue"> Export
                            <i class="fa fa-download"></i>
                        </button>
                        @endpermission

                        <div class="btn-group " style="float:right;">
                            <form action="{{url('/crm/import')}}" method="post" enctype="multipart/form-data">
                            <div class="col-md-12">
                                @permission('crm-import')
                                    <div class="col-md-4"  >
                                        <a href="{{ url('/crm/download')}}" >Import Template
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        {{ csrf_field() }}
                                        <input type="file" name="importFile"  style="width: 90%;"/>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn blue" id="data_search">Import</button>

                                    </div>
                                @endpermission
                            </div>
                            </form>
                        </div>

                    </div>
                </div>
                <div class="portlet-body">
                    <div class="table-container">
                            <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_rsg_requests">
                                <thead>
                                <tr role="row" class="heading">
                                    <th width="5%">ID</th>
                                    <th width="15%">Date</th>
                                    <th width="10%">Name</th>
                                    <th width="10%">Email</th>
                                    <th width="10%">Phone</th>
                                    <th width="10%">Country</th>
                                    <th width="10%">From</th>
                                    <th width="10%">Brand</th>
                                    <th width="10%">CTG</th>
                                    <th width="10%">RSG</th>
                                    <th width="10%">negative_review</th>
                                    <th width="10%">Review</th>
                                    <th width="10%">Order Number</th>
                                    <th width="5%">Action</th>
                                </tr>
                                <tr role="row" class="filter">
                                    <td>
                                        <input type="text" class="form-control form-filter input-sm" placeholder='id' name="id">
                                    </td>
                                    <td>
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="From" value="{{$date_from}}">
                                            <span class="input-group-btn">
                                                <button class="btn btn-sm default" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="To" value="{{$date_to}}">
                                            <span class="input-group-btn">
                                                <button class="btn btn-sm default" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                        </div>

                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-filter input-sm" placeholder='name' name="name">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-filter input-sm" placeholder='email' name="email">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-filter input-sm" placeholder='phone' name="phone">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-filter input-sm" placeholder='country' name="country">
                                    </td>

                                    <td>
                                        <input type="text" class="form-control form-filter input-sm"  name="from">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-filter input-sm" name="brand">
                                    </td>

                                    <td>
                                        <input type="text" class="form-control form-filter input-sm"  name="times_ctg">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-filter input-sm"  name="times_rsg">
                                    </td>


                                    <td>
                                        <input type="text" class="form-control form-filter input-sm"  name="times_negative_review">
                                    </td>
                                    <td colspan="2">
                                       <input type="text" class="form-control form-filter input-sm" placeholder='order_id'  name="order_id">

                                    </td>
                                    <td>
                                        <div class="margin-bottom-5">
                                            <button class="btn btn-sm green btn-outline filter-submit margin-bottom">
                                                <i class="fa fa-search"></i> Search</button>
                                        </div>
                                        <button class="btn btn-sm red btn-outline filter-cancel">
                                            <i class="fa fa-times"></i> Reset</button>
                                    </td>
                                </tr>
                                </thead>
                                <tbody> </tbody>
                            </table>
                            <script>
                            var TableDatatablesAjax = function () {

                                var initPickers = function () {
                                    //init date pickers
                                    $('.date-picker').datepicker({
                                        rtl: App.isRTL(),
                                        autoclose: true
                                    });
                                }

                                var initTable = function () {
                                    $.ajaxSetup({
                                        headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
                                    });
                                    var grid = new Datatable();

                                    grid.init({
                                        src: $("#datatable_ajax_rsg_requests"),
                                        onSuccess: function (grid, response) {
                                            // grid:        grid object
                                            // response:    json object of server side ajax response
                                            // execute some code after table records loaded
                                        },
                                        onError: function (grid) {
                                            // execute some code on network or other general error
                                        },
                                        onDataLoad: function(grid) {
                                            // execute some code on ajax data load
                                            //alert('123');
                                            //alert($("#subject").val());
                                            //grid.setAjaxParam("subject", $("#subject").val());
                                        },
                                        loadingMessage: 'Loading...',
                                        dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

                                            // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                                            // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js).
                                            // So when dropdowns used the scrollable div should be removed.
                                            "dom": "<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'>>",

                                            "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                                            "aoColumnDefs": [ { "bSortable": false, "aTargets": [2,3,4,5,6,7] }],
                                            "lengthMenu": [
                                                [10, 20, 50],
                                                ['All',10, 20, 50] // change per page values here
                                            ],
                                            "pageLength": 10, // default record count per page
                                            "ajax": {
                                                "url": "{{ url('crm/get')}}", // ajax source
                                            },
                                            "order": [
                                                [0, "desc"]
                                            ],// set first column as a default sort by asc
                                        }
                                    });
                                    grid.setAjaxParam("id", $("input[name='id']").val());
                                    grid.setAjaxParam("date_from", $("input[name='date_from']").val());
                                    grid.setAjaxParam("date_to", $("input[name='date_to']").val());
                                    grid.setAjaxParam("name", $("input[name='name']").val());
                                    grid.setAjaxParam("email", $("input[name='email']").val());
                                    grid.setAjaxParam("phone", $("input[name='phone']").val());
                                    grid.setAjaxParam("country", $("input[name='country']").val());
                                    grid.setAjaxParam("from", $("input[name='from']").val());
                                    grid.setAjaxParam("brand", $("input[name='brand']").val());
                                    grid.setAjaxParam("times_ctg", $("input[name='times_ctg']").val());
                                    grid.setAjaxParam("times_rsg", $("input[name='times_rsg']").val());
                                    grid.setAjaxParam("times_negative_review", $("input[name='times_negative_review']").val());
                                    grid.setAjaxParam("order_id", $("input[name='order_id']").val());
                                    grid.getDataTable().ajax.reload(null,false);
                                    //grid.clearAjaxParams();
                                }
                                return {
                                    init: function () {
                                        initPickers();
                                        initTable();
                                    }
                                };
                            }();

                        $(function() {
                            TableDatatablesAjax.init();
                            $("#crm-export").click(function(){
                                location.href='/crm/export?id='+$("input[name='id']").val()+'&date_from='+$("input[name='date_from']").val()+'&date_to='+$("input[name='date_to']").val()+'&name='+$("input[name='name']").val()+'&email='+$("input[name='email']").val()+'&phone='+$("input[name='phone']").val()+'&country='+$("input[name='country']").val()+'&from='+$("input[name='from']").val()+'&brand='+$("input[name='brand']").val()+'&times_ctg='+$("input[name='times_ctg']").val()+'&times_rsg='+$("input[name='times_rsg']").val()+'&times_negative_review='+$("input[name='times_negative_review']").val()+'&order_id='+$("input[name='order_id']").val();
                            });
                        });
                        </script>
                        </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>


<div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" >
            <div class="modal-body" >
                <img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
                <span>Loading... </span>
            </div>
        </div>
    </div>
</div>
@endsection
