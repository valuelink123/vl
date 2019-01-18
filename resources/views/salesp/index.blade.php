@extends('layouts.layout')
@section('label', 'Sales Prediction')
@section('content')
<style>
        .form-control {
            height: 29px;
        }
		.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}


th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
    </style>
    <h1 class="page-title font-red-intense"> Sales Prediction
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">

                    <div class="table-container">

                        <table class="table table-striped table-bordered" id="datatable_ajax_sp">
                            <thead>
                            <tr role="row" class="heading">
								<th width="10%"> Group </th>
								<th width="10%"> Seller </th>
								<th width="10%"> SKU </th>
								<th width="10%"> Factory </th>
								<th width="10%"> Description </th>
								<th width="10%"> Sales 22-28 </th>
								<th width="10%"> Sales 21-15 </th>
								<th width="10%"> Sales 14-08 </th>
								<th width="10%"> Sales 07-01 </th>
								<?php foreach($addcolspans as $k=>$v){ ?>
									<th> {{$k}} </th>
								<?php } ?>
                                
                            </tr>
                            <tr role="row" class="filter">
								<td>
                                    <select name="bgbu" class="form-control form-filter input-sm">
										<option value="">BG && BU</option>
										<option value="-">[Empty]</option>
										<?php 
										$bg='';
										foreach($teams as $team){ 	
											$bg=$team->bg;
											if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'">'.$team->bg.' - '.$team->bu.'</option>';
										}?>
									</select>
                                </td>
                                <td>
                                    <select name="sap_seller_id" class="form-control form-filter input-sm">
										<option value="">Sellers</option>
										@foreach ($users as $sap_seller_id=>$user_name)
											<option value="{{$sap_seller_id}}">{{$user_name}}</option>
										@endforeach
									</select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" name="sku">
                                </td>
                                <td>
                                   <select name="sap_site_id" class="form-control form-filter input-sm">
										<option value="">All</option>
										@foreach (matchSapSiteCode() as $k=>$v)
											<option value="{{$v}}">{{$k}}</option>
										@endforeach
									</select>
                                </td>
                                <td>
                                    
                                        <button class="btn btn-sm green btn-outline filter-submit margin-bottom">
                                            <i class="fa fa-search"></i> Search</button>
                                   
                                    <button class="btn btn-sm red btn-outline filter-cancel">
                                        <i class="fa fa-times"></i> Reset</button>
                                </td>
								<td colspan="26"></td>
                            </tr>
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




<script>
    var TableDatatablesAjax = function () {

        var initTable = function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
            });
            var grid = new Datatable();

            grid.init({
                src: $("#datatable_ajax_sp"),
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
                    //"dom": "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>>",
					scrollX: 2000,
					fixedColumns:   {
						leftColumns:8,
						rightColumns: 0
					},

                    "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                    "aoColumnDefs": [ { "bSortable": false, "aTargets": [4,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30] }],
                    "lengthMenu": [
                        [20, 50, 100, -1],
                        [20, 50, 100, 'All'] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page
                    "ajax": {
                        "url": "{{ url('salesp/get')}}", // ajax source
                    },
                    "order": [
                        [1, "desc"]
                    ],// set first column as a default sort by asc
					
					buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'salesp' }
                    ],
					 "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(4).attr('style', 'max-width: 200px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(4).attr('title', $(row).children('td').eq(4).text());
                    },
					"dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
					
                }
            });
            grid.setAjaxParam("sku", $("input[name='sku']").val());
            grid.setAjaxParam("bgbu", $("select[name='bgbu']").val());
			grid.setAjaxParam("sap_seller_id", $("select[name='sap_seller_id']").val());
			grid.setAjaxParam("sap_site_id", $("select[name='sap_site_id']").val());
            grid.getDataTable().ajax.reload(null,false);
            //grid.clearAjaxParams();
        }


        return {

            //main function to initiate the module
            init: function () {
                initTable();
            }

        };

    }();

$(function() {
    TableDatatablesAjax.init();
});


</script>


@endsection
