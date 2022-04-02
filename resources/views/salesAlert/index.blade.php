@extends('layouts.layout')
@section('label', 'Sales Alert')
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
		font-family:Arial, Helvetica, sans-serif;
	}
</style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Sales Alert</span>
                    </div>
                </div>
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group">
                                <a href="{{ url('salesAlert/create')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add New
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="portlet-body">
					<table class="table table-striped table-bordered table-hover table-checkable order-column" id="manage_sales_alert">
						<thead>
						<tr>
							<th> ID </th>
							<th> 部门 </th>
							<th> 年 </th>
							<th> 月 </th>
							<th> 开始时间 </th>
							<th> 结束时间 </th>
							<th> 销售额 </th>
							<th> 营销费用 </th>
							<th> 占比 </th>
							<th> 添加时间 </th>
							<th> 添加人 </th>
							<th> Actions </th>
						</tr>
						</thead>
						<tbody>
						@foreach ($data as $value)
							<tr class="odd">
								<td>
									{{$value['id']}}
								</td>
								<td>
									{{$value['department']}}
								</td>
								<td>
									{{$value['year']}}
								</td>
								<td>
									{{$value['month']}}
								</td>
								<td>
									{{$value['start_time']}}
								</td>
								<td>
									{{$value['end_time']}}
								</td>
								<td>
									{{$value['sales']}}
								</td>
								<td>
									{{$value['marketing_expenses']}}
								</td>
								<td>
									@if($value['marketing_expenses'] != 0)
										{{round(($value['marketing_expenses']/$value['sales'])*100,2)}}%
										@else
										0%
									@endif
								</td>
								<td>
									{{$value['created_at']}}
								</td>
								<td>
									{{$value['creatrd_user']}}
								</td>
								<td>

									<a href="{{ url('salesAlert/'.$value['id'].'/edit') }}">
										<button type="submit" class="btn btn-success btn-xs">Edit</button>
									</a>
									<form action="{{ url('salesAlert/'.$value['id']) }}" method="POST" style="display: inline;">
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

				var table = $('#manage_sales_alert');

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

					"bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.

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
					"order": [
						[0, "desc"]
					] // set first column as a default sort by asc
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
	});


</script>

@endsection
