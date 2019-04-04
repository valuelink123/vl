@extends('layouts.layout')
@section('label', 'Non-CTG List')
@section('content')
    <h1 class="page-title font-red-intense"> Non-CTG List
        <small>Non-CTG.</small>
    </h1>
    <div class="row">
        <div class="portlet light bordered">
            <div class="portlet-body">
                <div class="table-container">
                    <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                        <thead>
                        <tr role="row" class="heading">
                            <th width="8%"> Date </th>
                            <th width="8%"> Email </th>
                            <th width="8%"> Name </th>
                            <th width="18%">Order ID </th>
                            <th width="10%">Asin</th>
                            <th width="8%">seller Name</th>
                            <th width="8%">From</th>
                            <th width="5%"> Action </th>
                        </tr>

                        <tr role="row" class="filter">
                            <td>
                                <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="From">
                                    <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                </div>
                                <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="To">
                                    <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-filter input-sm" name="email">
                            </td>
                            <td>
                            </td>
                            <td>
                                <input type="text" class="form-control form-filter input-sm" name="amazon_order_id">
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                                <input type="text" class="form-control form-filter input-sm" name="from">
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
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                    src: $("#datatable_ajax"),
                    onSuccess: function (grid, response) {
                    },
                    onError: function (grid) {

                    },
                    onDataLoad: function(grid) {

                    },
                    loadingMessage: 'Loading...',
                    dataTable: {
                        "dom": "<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'>>",

                        "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                        "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 1,2,3,4,5,6 ] }],
                        "lengthMenu": [
                            [10, 20, 50],
                            [10, 20, 50] // change per page values here
                        ],
                        "pageLength": 10, // default record count per page
                        "ajax": {
                            "url": "{{ url('nonctg/get')}}", // ajax source
                        },
                        "order": [
                            [0, "desc"]
                        ],// set first column as a default sort by asc

                    }
                });

                grid.setAjaxParam("date_from", $("input[name='date_from']").val());
                grid.setAjaxParam("date_to", $("input[name='date_to']").val());
                grid.setAjaxParam("date", $("input[name='date']").val());
                grid.setAjaxParam("email", $("input[name='email']").val());
                grid.setAjaxParam("name", $("input[name='name']").val());
                grid.setAjaxParam("order_id", $("input[name='order_id']").val());
                grid.setAjaxParam("Asin", $("select[name='Asin']").val());
                grid.setAjaxParam("seller_name", $("input[name='seller_name']").val());
                grid.setAjaxParam("from", $("input[name='from']").val());
                grid.setAjaxParam("action", $("input[name='from']").val());
                grid.getDataTable().ajax.reload(null,false);
                //grid.clearAjaxParams();
            }

            return {

                //main function to initiate the module
                init: function () {
                    initTable();
                    initPickers();
                }

            };

        }();

        $(function() {
            TableDatatablesAjax.init();
        });


    </script>

@endsection
