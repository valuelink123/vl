@extends('layouts.layout')
@section('label', 'Report List')
@section('content')
    <h1 class="page-title font-red-intense"> Report List
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Report List</span>
                    </div>
                </div>
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
							@permission('requestreport-create')
                            <div class="btn-group">
                                <a href="{{ url('rr/create')}}" target="_blank"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add Report
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a>
                            </div>
							@endpermission
                            <div class="btn-group">
                                <button id="batch_del" class="btn sbold red"> Delete Selected
                                    <i class="fa fa-del"></i>
                                </button>
                               
                            </div>
                        </div>
                        <!--
                        <div class="col-md-6">
                            <div class="btn-group pull-right">
                                <button class="btn blue  btn-outline dropdown-toggle" data-toggle="dropdown">Tools
                                    <i class="fa fa-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        <a href="javascript:;">
                                            <i class="fa fa-file-excel-o"></i> Export to Excel </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        -->
                    </div>
                </div>
				
                <div class="portlet-body">

                    <table class="table table-striped table-bordered table-hover table-checkable" id="manage_report">
                        <thead>
                        <tr>
							<th><label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input type="checkbox" class="group-checkable" data-set="#manage_report .checkboxes" />
                                        <span></span>
                                    </label>
									
							</th>
                            <th> Account / Status </th>
                            <th> Report Type </th>
							<th> Report Date </th>
                            <th> Request Date </th>
                            <th> Actions </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($datas as $data)
                            <tr class="odd gradeX">
								<td>
								<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
									<input name="id[]" type="checkbox" class="checkboxes" value="{{$data->id}}"/><span></span>
								</label>
								</td>
                                <td>
									<div class="btn-group" style="display:block;">
										<a class="btn dropdown-toggle" href="javascript:;" data-toggle="dropdown" aria-expanded="false"> {{count($data->reports)}} Accounts
											<i class="fa fa-angle-down"></i>
										</a>
										<ul class="dropdown-menu">
											
											<?php
											$reports = $data->reports;
											foreach($reports as $report){
												echo '<li><a href="javascript:;">'.array_get($accounts,$report->seller_account_id.'.name',$report->seller_account_id).' : ';
												if(isset($report->report->status)){
													echo $report->report->status;
												}else{
													if(!empty($report->error)){
														echo 'ERROR';
													} else{
														echo 'PENDING';
													}
												}
												echo '</a></li>';
											}
											?>
											
										</ul>
									</div>
                                    
                                </td>
                                <td>
                                    {{$data->reports[0]->report_type}}
                                </td>
								<td>
                                    {{$data->reports[0]->after_date}} -- {{$data->reports[0]->before_date}}
                                </td>
                                <td>
                                    {{$data->reports[0]->created_at}}
                                </td>
                                <td>
									@if($data->report_url)
                                    <a href="{{$data->report_url}}" target="_blank">
                                        <button type="submit" class="btn btn-success btn-xs">Download</button>
                                    </a>
									@endif
                                    <form action="{{ url('rr/'.$data->id) }}" method="POST" style="display: inline;">
                                        {{ method_field('DELETE') }}
                                        {{ csrf_field() }}
                                        <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach



                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>




    <script>
        $(function() {
        var TableDatatablesManaged = function () {

            var initTable = function () {

                var table = $('#manage_report');

                // begin first table
                table.dataTable({

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

                    //"bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.

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
                        },
						{
							"searchable": false,
							"targets": [0]
						}
                    ],
					"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0  ] }],
                });
				
				table.find('.group-checkable').change(function () {
					var set = jQuery(this).attr("data-set");
					var checked = jQuery(this).is(":checked");
					jQuery(set).each(function () {
						if (checked) {
							$(this).prop("checked", true);
							$(this).parents('tr').addClass("active");
						} else {
							$(this).prop("checked", false);
							$(this).parents('tr').removeClass("active");
						}
					});
				});
		
				table.on('change', 'tbody tr .checkboxes', function () {
					$(this).parents('tr').toggleClass("active");
				});

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
			
			$("#batch_del").click(function(){
				var id_array=new Array();  
				$('input[name="id[]"]:checked').each(function(){  
					id_array.push($(this).val());
				});  
				var idstr=id_array.join(',');
				location.href='/rr?batch_del='+idstr;
			});
        });


</script>


@endsection
