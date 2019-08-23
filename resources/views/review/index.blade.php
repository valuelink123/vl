



@extends('layouts.layout')
@section('label', 'Review List')
@section('content')
<style type="text/css">
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 10px 2px;
}
th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}

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
						@permission('review-batch-update')
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default " multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]">
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}">{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						@endpermission
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="asin_status[]" id="asin_status[]" >
                                        @foreach ($asin_status as $key=>$v)
                                            <option value="{{$key}}" >{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="follow_status[]" id="follow_status[]">

                                        @foreach ($follow_status as $key=>$v)
                                            <option value="{{$key}}">{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						 <div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="site[]" id="site[]">

                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}">{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						
						
						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						
						
						<div class="col-md-2">
						<select class="form-control form-filter input-sm" name="bgbu">
                                        <option value="">Select BG && BU</option>
										<?php 
										$bg='';
										foreach($teams as $team){ 
											if($bg!=$team->bg) echo '<option value="'.$team->bg.'_">'.$team->bg.'</option>';	
											$bg=$team->bg;
											if($team->bg && $team->bu) echo '<option value="'.$team->bg.'_'.$team->bu.'">'.$team->bg.' - '.$team->bu.'</option>';
										} ?>
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
						
						
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="vp">
							<option value="">VP</option>
							<option value="1">No</option>
							<option value="2">Yes</option>
						</select>
						</div>
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="del">
							<option value="">Del</option>
							<option value="1">No</option>
							<option value="2">Yes</option>
						</select>
						</div>
						
						
						<div class="col-md-2">
						<select class="form-control form-filter input-sm" name="rc">
							<option value="">Rating Change</option>
							<option value="1">No</option>
							<option value="2">Yes</option>
						</select>
						</div>
						
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="np">
							<option value="">All</option>
							<option value="1" selected="selected">Negative</option>
							<option value="2">Positive</option>
						</select>
						</div>
						
						<div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" name="nextdate" placeholder="Next FollowUp Date" value="{{array_get($_REQUEST,'nextdate')}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
						
						<div class="col-md-1">
						<input type="text" class="form-control form-filter input-sm" name="keywords" placeholder="Keywords" value ="{{array_get($_REQUEST,'keywords')}}">
                                       
						</div>	
						<div class="col-md-1">
							
										<button type="button" class="btn blue" id="data_search">Search</button>
									
                        </div>
					</div>

                    </form>
					
					
					@permission('review-import')
					<div class="row" style="margin-top:30px">
                        <div class="col-md-4">
                            
                        </div>
						<div class="col-md-2">
						</div>
						<form action="{{url('review/upload')}}" method="post" enctype="multipart/form-data">
						<div class="col-md-2" style="text-align:right;" >

							<a href="{{ url('/uploads/reviewUpload/review_customers.csv')}}" >Import Template
                                </a>	
						</div>
						<div class="col-md-2">
							{{ csrf_field() }}
								 <input type="file" name="importFile"  />
						</div>
						<div class="col-md-2">
							<button type="submit" class="btn blue" id="data_search">Upload Email</button>

						</div>
						
						</form>
						
					</div>
					@endpermission
					
					
					
                </div>
				
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Review List</span>
                    </div>
					
					<div class="btn-group " style="float:right;">
                        <div class="table-actions-wrapper" id="table-actions-wrapper">
							@permission('review-batch-update')
							<select  class="table-group-action-input form-control input-inline input-small input-sm" id="giveReviewUser">
                                
                                @foreach ($users as $user_id=>$user_name)
                                    <option value="{{$user_id}}">{{$user_name}}</option>
                                @endforeach
                            </select>
                            <button class="btn  green table-group-action-submit">
                                <i class="fa fa-check"></i> Change</button>

                            <select id="customstatus" class="table-group-action-input form-control input-inline input-small input-sm">
                                <option value="">Select Status</option>
                                <?php
                                foreach($follow_status as $k=>$v){
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }?>
                            </select>
                            <button class="btn  green table-status-action-submit">
                                <i class="fa fa-check"></i> Update</button>
                        	@endpermission	
							@permission('review-export')
                                <button id="vl_list_export" class="btn sbold blue"> Export
                                    <i class="fa fa-download"></i>
                                </button>
								@endpermission	
                        </div>
                    </div>
                </div>
				
                <div class="portlet-body">

                    <div class="table-container">
                        
                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_asin">
                        <thead>
                            <tr role="row" class="heading">
								<th style="max-width:20px;">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax_asin .checkboxes" />
                                        <span></span>
                                    </label>
                                </th>
								<th style="max-width: 30px;">Imp.</th>
								<th style="max-width:70px;">Asin</th>
								<th style="max-width:55px;">Date</th>
								<th style="max-width:40px;">Rating</th>
								<th style="max-width:30px;">Rev</th>
								<th style="max-width:40px;">RevC</th>
								<th style="max-width:40px;">Name</th>
								<th style="max-width:30px;">VP</th>
								<th style="max-width:40px;">Status</th>
								<th style="max-width:80px;">Email</th>
								<th style="max-width:40px;">CusFB</th>
								<th style="max-width:55px;">NextDate</th>
								<th style="max-width:40px;">SKU</th>
								<th style="max-width:80px;">Follow</th>
                                <th style="max-width:50px;">User</th>
                                <th style="max-width:50px;">Last Update</th>
                                <th style="max-width:90px;">Action</th>
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
                        [10, 50, 100, -1],
                        [10, 50, 100, 'All'] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page


					"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0,13,14,16,17] }],
					 "order": [
                        [1, "desc"]
                    ],
                    // scroller extension: http://datatables.net/extensions/scroller/

					

					fixedColumns:   {
						leftColumns:0,
						rightColumns: 1
					},
                    "ajax": {
                        "url": "{{ url('review/get')}}", // ajax source
                    },

                    "createdRow": function( row, data, dataIndex ) {
						$(row).children('td').eq(0).attr('style', 'wdith: 30px;');
                        $(row).children('td').eq(10).attr('style', 'max-width: 80px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(10).attr('title', $(row).children('td').eq(10).text());
						$(row).children('td').eq(14).attr('style', 'max-width: 80px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(14).attr('title', $(row).children('td').eq(14).text());
                    },
					"dom": "<'row' <'col-md-12'>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
                }
            });

            //批量更改状态操作
            $('#table-actions-wrapper .table-status-action-submit').click(function(e){
                e.preventDefault();
                var giveReviewUser = $("#customstatus", $("#table-actions-wrapper"));

                if (giveReviewUser.val() != "" && grid.getSelectedRowsCount() > 0) {
                    grid.setAjaxParam("customActionType", "status_action");
                    grid.setAjaxParam("giveReviewStatus", giveReviewUser.val());
                    grid.setAjaxParam("id", grid.getSelectedRows());
                    grid.getDataTable().draw(false);
                    //grid.clearAjaxParams();
                } else if ( giveReviewUser.val() == "" ) {
                    App.alert({
                        type: 'danger',
                        icon: 'warning',
                        message: 'Please select an action',
                        container: $("#table-actions-wrapper"),
                        place: 'prepend'
                    });
                } else if (grid.getSelectedRowsCount() === 0) {
                    App.alert({
                        type: 'danger',
                        icon: 'warning',
                        message: 'No record selected',
                        container: $("#table-actions-wrapper"),
                        place: 'prepend'
                    });
                }
            });

            //设置负责人操作
			$("#table-actions-wrapper").unbind("click").on('click', '.table-group-action-submit', function (e) {
                e.preventDefault();
				var giveReviewUser = $("#giveReviewUser", $("#table-actions-wrapper"));
				
                if (giveReviewUser.val() != "" && grid.getSelectedRowsCount() > 0) {
                    grid.setAjaxParam("customActionType", "group_action");
					grid.setAjaxParam("giveReviewUser", giveReviewUser.val());
                    grid.setAjaxParam("id", grid.getSelectedRows());
                    grid.getDataTable().draw(false);
                    //grid.clearAjaxParams();
                } else if ( giveReviewUser.val() == "" ) {
                    App.alert({
                        type: 'danger',
                        icon: 'warning',
                        message: 'Please select an action',
                        container: $("#table-actions-wrapper"),
                        place: 'prepend'
                    });
                } else if (grid.getSelectedRowsCount() === 0) {
                    App.alert({
                        type: 'danger',
                        icon: 'warning',
                        message: 'No record selected',
                        container: $("#table-actions-wrapper"),
                        place: 'prepend'
                    });
                }
            });
            

            //grid.setAjaxParam("customActionType", "group_action");
			//alert($("select[name='user_id[]']").val());

            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
			grid.setAjaxParam("nextdate", $("input[name='nextdate']").val());
			grid.setAjaxParam("vp", $("select[name='vp']").val());
			grid.setAjaxParam("del", $("select[name='del']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id[]']").val());
			grid.setAjaxParam("rating", $("select[name='rating']").val());
			grid.setAjaxParam("asin_status", $("select[name='asin_status[]']").val());
			grid.setAjaxParam("follow_status", $("select[name='follow_status[]']").val());
			grid.setAjaxParam("site", $("select[name='site[]']").val());
			grid.setAjaxParam("keywords", $("input[name='keywords']").val());
			grid.setAjaxParam("bgbu", $("select[name='bgbu']").val());
			grid.setAjaxParam("rc", $("select[name='rc']").val());
			grid.setAjaxParam("np", $("select[name='np']").val());
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
		dttable.fnDestroy(); //还原初始化了的datatable
	    dttable.fnClearTable(); //清空一下table
		dttable.fnDestroy();
		TableDatatablesAjax.init();
	});
	$("#vl_list_export").click(function(){
		location.href='/reviewexport?asin_status='+(($("select[name='asin_status[]']").val())?$("select[name='asin_status[]']").val():'')+'&keywords='+$("input[name='keywords']").val()+'&date_from='+$("input[name='date_from']").val()+'&date_to='+$("input[name='date_to']").val()+'&nextdate='+$("input[name='nextdate']").val()+'&follow_status='+(($("select[name='follow_status[]']").val())?$("select[name='follow_status[]']").val():'')+'&user_id='+(($("select[name='user_id[]']").val())?$("select[name='user_id[]']").val():'')+'&site='+(($("select[name='site[]']").val())?$("select[name='site[]']").val():'')+'&rating='+$("select[name='rating']").val()+'&bgbu='+$("select[name='bgbu']").val()+'&vp='+$('select[name="vp"]').val()+'&rc='+$('select[name="rc"]').val()+'&np='+$('select[name="np"]').val()+'&del='+$('select[name="del"]').val();
	});
});


</script>


@endsection

