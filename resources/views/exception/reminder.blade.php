@extends('layouts.layout')
@section('label', ' Refund Replacement Reminder Manage')
@section('content')
    <style type="text/css">
        th, td { white-space: nowrap;word-break:break-all; }
    </style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-3">
                                <select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="sales[]" id="sales[]">
                                    @foreach ($sales as $id=>$name)
                                        <option value="{{$id}}">{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-addon">开始时间</span>
                                    <input  class="form-control"  value="{!! $date_start !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="date_start" name="date_start" />
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-addon">结束时间</span>
                                    <input  class="form-control"  value="{!! $date_end !!}" data-change="0" data-date-format="yyyy-mm-dd" data-options="format:'yyyy-mm-dd'" id="date_end" name="date_end"/>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <button type="button" class="btn blue" id="data_search">Search</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="portlet-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                            <thead>
                            <tr role="row" class="heading">
                                <th>BGBU</th>
                                <th>销售</th>
                                <th>Confirmed</th>
                                <th>Auto MCF Failed</th>
                                <th>Auto SAP Failed</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>
    <script>
        $('#date_start').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

        $('#from_end').datepicker({
            rtl: App.isRTL(),
            autoclose: true
        });

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
                grid.setAjaxParam("sales", $("select[name='sales[]']").val());
                grid.setAjaxParam("date_start", $("input[name='date_start']").val());
                grid.setAjaxParam("date_end", $("input[name='date_end']").val());
                grid.init({
                    src: $("#datatable_ajax"),
                    onSuccess: function (grid, response) {
                        grid.setAjaxParam("customActionType", '');
                    },
                    onError: function (grid) {
                    },
                    onDataLoad: function(grid) {
                    },
                    loadingMessage: 'Loading...',
                    dataTable: {
                        "autoWidth":false,
                        "ordering": false,
                        "lengthMenu": [
                            [15, 30, 50, -1],
                            [15, 30, 50, 'All']
                        ],
                        "pageLength": 15,
                        "ajax": {
                            "url": "{{ url('/exceptionReminder/get')}}",
                        },
                    }
                });
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
            $('#data_search').on('click',function(){
                var dttable = $('#datatable_ajax').dataTable();
                dttable.fnClearTable(false);
                dttable.fnDestroy();
                TableDatatablesAjax.init();
            });
        });
    </script>
@endsection