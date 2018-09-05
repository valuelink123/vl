



@extends('layouts.layout')
@section('label', 'Asin Rating List')
@section('content')

<style type="text/css">
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

.table-checkable tr>td:first-child, .table-checkable tr>th:first-child {
max-width:150px !important;
}
</style>
    <h1 class="page-title font-red-intense"> Asin Rating List
        <small></small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('star')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						<div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="Date" value="{{$date_from}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="Compare Date" value="{{$date_to}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-1">
                            
                                <input type="text" class="form-control form-filter input-sm"  name="star_from" placeholder="Rating From" value="{{array_get($_REQUEST,'star_from')}}">

                           
                        </div>
                        <div class="col-md-1">
                            
                                <input type="text" class="form-control form-filter input-sm"  name="star_to" placeholder="Rating To" value="{{array_get($_REQUEST,'star_to')}}">

                        </div>
						<?php if(Auth::user()->admin){ ?>
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default input-sm form-control form-filter" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]">

                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}">{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						<?php } ?>
						<div class="col-md-2">
						<select class="form-control form-filter input-sm" name="asin_status">
                                        <option value="">Asin Status</option>
                                        <option value="Above" <?php if("Above"==array_get($_REQUEST,'asin_status')) echo 'selected';?>>Above Warning Rating</option>
										<option value="Below" <?php if("Below"==array_get($_REQUEST,'asin_status')) echo 'selected';?>>Below Warning Rating</option>
                                    </select>
						</div>
						
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="keywords" placeholder="Keywords" value ="{{array_get($_REQUEST,'keywords')}}">
                                       
						</div>	
						</div>
					<div class="row">
						<div class="col-md-10">&nbsp;</div>
                        <div class="col-md-2">
							<div class="form-actions">
								<div class="row">
									<div class="col-md-offset-4 col-md-8">
										<button type="button" class="btn blue" id="data_search">Search</button>
									</div>
								</div>
							</div>
                        </div>
                    </div>
                    </form>
					
                </div>

                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Asin Rating List</span>
                    </div>
                </div>

                <div class="portlet-body">

                    <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_asin">
                        <thead>
                            <tr role="row" class="heading">
							<th style="min-width:80px;"> Brand </th>
								<th style="min-width:80px;"> Asin </th>	
								<th style="min-width:80px;"> SellerSku </th>	
                                <th style="min-width:80px;"> Item No. </th>
								
                                <th style="min-width:80px;"> Seller </th>
                                <th style="min-width:80px;"> User </th>
								<th style="min-width:100px;"> Site </th>

								
                                <th style="min-width:80px;"> Quantity Changes </th>
                                <th style="min-width:80px;"> Rating Changes </th>
								<th style="min-width:80px;"> Positive Changes </th>
								<th style="min-width:80px;"> Negative Changes </th>
								<th style="min-width:80px;"> Rating Limit </th>
								<th style="min-width:80px;"> Rating Status </th>
								<th style="min-width:80px;"> Increase </th>
								<th style="min-width:80px;"> Decrease </th>
								<th style="min-width:80px;"> Last Update</th>
								<th style="min-width:80px;"> Quantity </th>
                                <th style="min-width:80px;"> Rating </th>
                                <th style="min-width:80px;"> 1 Star </th>
								<th style="min-width:80px;"> 2 Stars </th>
                                <th style="min-width:80px;"> 3 Stars </th>
								<th style="min-width:80px;"> 4 Stars </th>
                                <th style="min-width:80px;"> 5 Stars </th>
								<th style="min-width:80px;"> Pre Update</th>
								<th style="min-width:80px;"> Quantity </th>
                                <th style="min-width:80px;"> Rating </th>
                                <th style="min-width:80px;"> 1 Star </th>
								<th style="min-width:80px;"> 2 Stars </th>
                                <th style="min-width:80px;"> 3 Stars </th>
								<th style="min-width:80px;"> 4 Stars </th>
                                <th style="min-width:80px;"> 5 Stars </th>
                            </tr>
							
                            
                            </thead>
                            <tbody>
							
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
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
                src: $("#datatable_ajax_asin"),
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
                   "autoWidth":true,
                    "lengthMenu": [
                        [20, 50, 100, -1],
                        [20, 50, 100, 'All'] // change per page values here
                    ],
                    "pageLength": 20, // default record count per page
					buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'stars' }
                    ],
					"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 , 6,11,12,13,14 ] }],	
					 "order": [
                        [7, "asc"]
                    ],
                    // scroller extension: http://datatables.net/extensions/scroller/
                    scrollY:        500,
                    scrollX:        true,
					

					fixedColumns:   {
						leftColumns:2,
						rightColumns: 0
					},
                    "ajax": {
                        "url": "{{ url('star/get')}}", // ajax source
                    },

                    
					"dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
                }
            });


            

            //grid.setAjaxParam("customActionType", "group_action");
            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
			grid.setAjaxParam("star_from", $("input[name='star_from']").val());
            grid.setAjaxParam("star_to", $("input[name='star_to']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id[]']").val());
			grid.setAjaxParam("asin_status", $("select[name='asin_status']").val());

			grid.setAjaxParam("keywords", $("input[name='keywords']").val());
            grid.getDataTable().ajax.reload(null,false);
            //grid.clearAjaxParams();
        }


        return {

            //main function to initiate the module
            init: function () {
                initPickers();
                initTable();
            }

        };

    }();

$(function() {
    TableDatatablesAjax.init();
	$('#data_search').on('click',function(){
		var dttable = $('#datatable_ajax_asin').dataTable();
	    dttable.fnClearTable(); //清空一下table
	    dttable.fnDestroy(); //还原初始化了的datatable
		TableDatatablesAjax.init();
	});
	
});


</script>


@endsection

