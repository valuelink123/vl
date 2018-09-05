



@extends('layouts.layout')
@section('label', 'Review List')
@section('content')
<style type="text/css">
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

.table-checkable tr>td:first-child, .table-checkable tr>th:first-child {
	max-width:150px !important;
}

</style>
    <h1 class="page-title font-red-intense"> Review List
        <small></small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('review')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">

                        <div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="Review Date From" value="{{$date_from}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="Review Date To" value="{{$date_to}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
						<?php if(Auth::user()->admin){ ?>
						<div class="col-md-1">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]">
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}">{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						<?php } ?>
						<div class="col-md-1">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="asin_status[]" id="asin_status[]" >

                                        @foreach ($asin_status as $key=>$v)
                                            <option value="{{$key}}" >{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-1">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="follow_status[]" id="follow_status[]">

                                        @foreach ($follow_status as $key=>$v)
                                            <option value="{{$key}}">{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>	
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="rating">
                                        <option value="">Rating</option>
                                        <option value="1" <?php if(1==array_get($_REQUEST,'rating')) echo 'selected';?>>1</option>
										<option value="2" <?php if(2==array_get($_REQUEST,'rating')) echo 'selected';?>>2</option>
										<option value="3" <?php if(3==array_get($_REQUEST,'rating')) echo 'selected';?>>3</option>
                                    </select>
						</div>	
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="keywords" placeholder="Keywords" value ="{{array_get($_REQUEST,'keywords')}}">
                                       
						</div>	
						<div class="col-md-1">
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
					<div class="row" style="margin-top:30px">
                        <div class="col-md-4">
                            <div class="btn-group">
                                <a href="{{ url('review/create')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add New
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a>
                            </div>
                        </div>
						<div class="col-md-2">
						</div>
						<form action="{{url('review/upload')}}" method="post" enctype="multipart/form-data">
						<div class="col-md-2" style="text-align:right;" >

							<a href="{{ url('/uploads/reviewUpload/reviews.csv')}}" >Import Template
                                </a>	
						</div>
						<div class="col-md-2">
							{{ csrf_field() }}
								 <input type="file" name="importFile"  />
						</div>
						<div class="col-md-2">
							<button type="submit" class="btn blue" id="data_search">Batch upload</button>

						</div>
						
						</form>
						
					</div>
                </div>

                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Review List</span>
                    </div>
					<div class="btn-group " style="float:right;">
                                <button id="vl_list_export" class="btn sbold blue"> Export
                                    <i class="fa fa-download"></i>
                                </button>
                               
                            </div>
                </div>

                <div class="portlet-body">

                    <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_asin">
                        <thead>
                            <tr role="row" class="heading">
								<th style="min-width:50px;">Important</th>
								<th style="min-width:100px;">Asin</th>
                                <th style="min-width:60px;">Item No.</th>
								<th style="min-width:80px;">Date</th>
								<th style="min-width:50px;">Rating</th>
								<th style="min-width:50px;">Status</th>
								<th style="min-width:150px;">Account</th>
								<th style="min-width:120px;">OrderId</th>
								<th style="min-width:150px;">BuyerEmail</th>
								<th style="max-width:400px;">FollowUpProgress</th>
								<th style="min-width:100px;">ReviewerName</th>
								 <th style="min-width:100px;">FollowUpDate</th>
                                <th style="min-width:100px;">User</th>
                                <th style="min-width:100px;">Action</th>
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


					"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 9,13] }],	
					 "order": [
                        [3, "desc"]
                    ],
                    // scroller extension: http://datatables.net/extensions/scroller/
                    scrollY:        500,
                    scrollX:        true,
					

					fixedColumns:   {
						leftColumns:0,
						rightColumns: 1
					},
                    "ajax": {
                        "url": "{{ url('review/get')}}", // ajax source
                    },

                    "createdRow": function( row, data, dataIndex ) {
						$(row).children('td').eq(8).attr('style', 'max-width: 100px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(8).attr('title', $(row).children('td').eq(8).text());
                        $(row).children('td').eq(9).attr('style', 'max-width: 200px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(9).attr('title', $(row).children('td').eq(9).text());
                    },
					"dom": "<'row' <'col-md-12'>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
                }
            });


            

            //grid.setAjaxParam("customActionType", "group_action");
			//alert($("select[name='user_id[]']").val());

            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id[]']").val());
			grid.setAjaxParam("rating", $("select[name='rating']").val());
			grid.setAjaxParam("asin_status", $("select[name='asin_status[]']").val());
			grid.setAjaxParam("follow_status", $("select[name='follow_status[]']").val());
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
	$("#vl_list_export").click(function(){
		
		location.href='/reviewexport?asin_status='+(($("select[name='asin_status[]']").val())?$("select[name='asin_status[]']").val():'')+'&keywords='+$("input[name='keywords']").val()+'&date_from='+$("input[name='date_from']").val()+'&date_to='+$("input[name='date_to']").val()+'&follow_status='+(($("select[name='follow_status[]']").val())?$("select[name='follow_status[]']").val():'')+'&user_id='+(($("select[name='user_id[]']").val())?$("select[name='user_id[]']").val():'')+'&rating='+$("select[name='rating']").val();
	});
});


</script>


@endsection

