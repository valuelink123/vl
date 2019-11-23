@extends('layouts.layout')
@section('label', 'Asin Sales Data')
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
	.portlet.light .dataTables_wrapper .dt-buttons{margin-top:0px;}
    </style>
    <h1 class="page-title font-red-intense"> Asin Sales Data
        <small></small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('home/asins')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						<div class="col-md-3">
						<div class="col-md-6">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="Date" value="{{$date_from}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="Compare Date" value="{{$date_to}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
						</div>
                       
						@if(Auth::user()->seller_rules)
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="bgbu">
                                        <option value="">BG && BU</option>
										<?php 
										$bg='';
										foreach($teams as $team){ 
											$selected = '';
											if($bgbu==($team->bg.'_')) $selected = 'selected';
											
											if($bg!=$team->bg) echo '<option value="'.$team->bg.'_" '.$selected.'>'.$team->bg.'</option>';	
											$bg=$team->bg;
											$selected = '';
											if($bgbu==($team->bg.'_'.$team->bu)) $selected = 'selected';
											if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'" '.$selected.'>'.$team->bg.' - '.$team->bu.'</option>';
										} ?>
                                    </select>
						</div>	
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default form-control form-filter input-sm " data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id" id="user_id">
								<option value="">All Sellers</option>
								@foreach ($users as $user_id=>$user_name)
									<option value="{{$user_id}}" <?php if($user_id==$s_user_id) echo 'selected'; ?>>{{$user_name}}</option>
								@endforeach
							</select>
						</div>
						
						@endif
						 <div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="site[]" id="site[]">

                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}">{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="keywords" placeholder="ASIN OR SKU" value ="{{array_get($_REQUEST,'keywords')}}">
                                       
						</div>	
						<div class="col-md-1"><button type="button" class="btn blue" id="data_search">Search</button>
						</div>
						</div>
					
                    </form>
					<div style="clear:both;"></div>
                </div>
			
                <div class="table-container">

                    <table class="table table-striped table-bordered table-hover" id="datatable_ajax_asin">
                        <thead>
							
                            <tr role="row" class="heading">
								<th>ASIN</th>
								<th>SITE</th>
								<th>SKU</th>
								<th>BGBU</th>
								<th>Seller</th>
								<th>Level</th>
								<th>SALES</th>
								<th>UNITS</th>
								<th>SALES/D</th>
								<th>FBM</th>
								<th>AVG.PRICE</th>
								<th>FBA</th>
								<th>OUTSTOCK</th>
								<th>RATING</th>
								<th>RevCount</th>
								<th>SESS</th>
								<th>CR</th>
								<th>KW RANK</th>
								<th>BSR</th>
								<th>SKU E.VALUE</th>
								<th>SKU BONUS</th>
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
			grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id']").val());
			grid.setAjaxParam("bgbu", $("select[name='bgbu']").val());
			grid.setAjaxParam("keywords", $("input[name='keywords']").val());
			grid.setAjaxParam("site", $("select[name='site[]']").val());
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
                   
                    "lengthMenu": [
                        [20, 50, 100, -1],
                        [20, 50, 100, 'All'] // change per page values here
                    ],
                    "pageLength": 20, // default record count per page
					buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'asins' }
                    ],
					"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0,1,2,3,4 ] }],	
					 "order": [
                        [5, "asc"]
                    ],
                    // scroller extension: http://datatables.net/extensions/scroller/
                    scrollY:        500,
                    scrollX:        true,
					
                    "ajax": {
                        "url": "{{ url('home/getasins')}}", // ajax source
                    },
					fixedColumns:   {
						leftColumns:5,
						rightColumns: 0
					},
					"dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
                }
            });


            

            //grid.setAjaxParam("customActionType", "group_action");
            //grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            //grid.setAjaxParam("date_to", $("input[name='date_to']").val());
			//grid.setAjaxParam("star_from", $("input[name='star_from']").val());
            //grid.setAjaxParam("star_to", $("input[name='star_to']").val());
            //grid.setAjaxParam("user_id", $("select[name='user_id[]']").val());
			//grid.setAjaxParam("asin_status", $("select[name='asin_status']").val());

			//grid.setAjaxParam("keywords", $("input[name='keywords']").val());
            //grid.getDataTable().ajax.reload(null,false);
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
	    dttable.fnClearTable(false); //清空一下table
	    dttable.fnDestroy(); //还原初始化了的datatable
		TableDatatablesAjax.init();
	});
	
});


</script>

<div class="modal bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" >
			<div class="modal-body" style="height:500px">
				<img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
				<span>Loading... </span>
			</div>
		</div>
	</div>
</div>

@endsection

