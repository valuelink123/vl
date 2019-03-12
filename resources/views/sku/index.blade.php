@extends('layouts.layout')
@section('label', 'Daily Sales Report')
@section('content')
<style>
.table-checkable tr>td:first-child, .table-checkable tr>th:first-child{max-width:auto !important;}
table.dataTable thead>tr>th.sorting_asc, table.dataTable thead>tr>th.sorting_desc, table.dataTable thead>tr>th.sorting, table.dataTable thead>tr>td.sorting_asc, table.dataTable thead>tr>td.sorting_desc, table.dataTable thead>tr>td.sorting {
    padding-right: 15px !important;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 10px 2px !important;;
}
th,td {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
</style>
    <h1 class="page-title font-red-intense"> Daily Sales Report
        
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Daily Sales Report</span>
                    </div>
                </div>
                <div class="table-toolbar">
                    <div class="row">
                        	<div class="row widget-row">
						<div style="margin: 20px;">
							
<form role="form" action="{{url('skus')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">

                        <div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_start" placeholder="Date" value="{{$date_start}}">
                                <span class="input-group-btn">
									<button class="btn btn-sm default" type="button">
										<i class="fa fa-calendar"></i>
									</button>
								</span>
                            </div>
                        </div>
                       
						<div class="col-md-2">
						<select class="form-control form-filter input-sm"  name="user_id" id="user_id">
										<option value="">Select User</option>
                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}" <?php if($user_id==$s_user_id) echo 'selected'; ?>>{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-2">
						<select class="form-control form-filter input-sm" name="bgbu">
                                        <option value="">Select BG && BU</option>
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
						<select class="form-control form-filter input-sm" name="site" id="site">
									<option value="">Select Site</option>
                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}" <?php if($v==$s_site) echo 'selected'; ?>>{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="sku" placeholder="SKU OR ASIN" value ="{{array_get($_REQUEST,'sku')}}">
                                       
						</div>
						<div class="col-md-1">
							
										<button type="submit" class="btn blue" id="data_search">Search</button>
									
                        </div>
						</div>	
						
						
						 <div class="row" style="margin-top:20px;">
						
						
						
						
						
						
						
							
						
					</div>

                    </form>
						</div>
					
						
					</div>
                     </div>
                </div>
                        
                   
                <div class="portlet-body">
					<form id='update_sku'>
                    <table class="table table-striped table-hover table-bordered" id="skus_editable">
                        <thead>
							<tr role="row" >
								<td colspan="9" style="background:#fff2cc;text-align:left">Sku Base Info</td>
								<td colspan="7" style="background:#ddeef7;text-align:left">Ranking</td>
								<td colspan="7" style="background:#fff2cc;text-align:left">Rating</td>
								<td colspan="7" style="background:#ddeef7;text-align:left">Review</td>
								<td colspan="7" style="background:#fff2cc;text-align:left">Sales</td>
								<td colspan="7" style="background:#ddeef7;text-align:left">Price</td>
								<td colspan="7" style="background:#fff2cc;text-align:left">Flow</td>
								<td colspan="7" style="background:#ddeef7;text-align:left">Conversion rate</td>
								<td colspan="7" style="background:#fff2cc;text-align:left">Stock Info</td>
							</tr>
                                                <tr>
                                                    <th> BG </th>
                                                    <th> BU </th>
                                                    <th> Seller </th>
                                                    <th> Sku </th>
                                                    <th> Description </th>
                                                    <th> Site </th>
													<th> Status </th>
                                                    <th> Link </th>
                                                    <th> Keywords </th>
                                                    
                                                    <th> Mon </th>
                                                    <th> Tues </th>
													<th> Wed </th>
                                                    <th> Thur </th>
                                                    <th> Fri </th>
                                                    <th> Sat </th>
                                                     <th> Sun </th>
                                                    <th> Mon </th>
                                                    <th> Tues </th>
													<th> Wed </th>
                                                    <th> Thur </th>
                                                    <th> Fri </th>
                                                    <th> Sat </th>
                                                    <th> Sun </th>
                                                    <th> Mon </th>
                                                    <th> Tues </th>
													<th> Wed </th>
                                                    <th> Thur </th>
                                                    <th> Fri </th>
                                                    <th> Sat </th>
													 <th> Sun </th>
                                                    <th> Mon </th>
                                                    <th> Tues </th>
													<th> Wed </th>
                                                    <th> Thur </th>
                                                    <th> Fri </th>
                                                    <th> Sat </th>
                                                     <th> Sun </th>
                                                    <th> Mon </th>
                                                    <th> Tues </th>
													<th> Wed </th>
                                                    <th> Thur </th>
                                                    <th> Fri </th>
                                                    <th> Sat </th>
                                                     <th> Sun </th>
                                                    <th> Mon </th>
                                                    <th> Tues </th>
													<th> Wed </th>
                                                    <th> Thur </th>
                                                    <th> Fri </th>
                                                    <th> Sat </th>
                                                     <th> Sun </th>
                                                    <th> Mon </th>
                                                    <th> Tues </th>
													<th> Wed </th>
                                                    <th> Thur </th>
                                                    <th> Fri </th>
                                                    <th> Sat </th>
													<th> Sun </th>
													<th> FBA InStock </th>
                                                    <th> FBA Transfer </th>
													<th> FBM InStock </th>
													<th> Strategy </th>
                                                    <th> Total </th>
                                                    <th> FBA Keep </th>
													<th> Total Keep </th>
													
                                                </tr>
                                            </thead>
                                            <tbody>
											@foreach ($datas as $data)
											
											<?php $ranking = explode(";",$data->ranking);
											$rating = explode(";",$data->rating);
											$review = explode(";",$data->review);
											$sales = explode(";",$data->sales);
											$price = explode(";",$data->price);
											$flow = explode(";",$data->flow);
											$conversion = explode(";",$data->conversion);
											?>
                                                <tr>
                                                    <td> {{$data->bg}} </td>
                                                    <td> {{$data->bu}} </td>
                                                    <td> {{array_get($users,$data->sap_seller_id,$data->sap_seller_id)}} </td>
                                                    <td class="center"><a class="edit" href="javascript:;"> {{$data->item_code}} </a> </td>
                                                    <td>
                                                        {{$data->item_name}}
                                                    </td>
                                                    <td>
                                                       {{$data->site}}
                                                    </td>
													<td> {!!($data->status)?'<span class="btn btn-success btn-xs">Reserved</span>':'<span class="btn btn-danger btn-xs">Eliminate</span>'!!} </td>
                                                    <td><a href="https://{{$data->site}}/dp/{{strip_tags(str_replace('&nbsp;','',$data->asin))}}" target="_blank">{{strip_tags(str_replace('&nbsp;','',$data->asin))}}</a></td>
                                                    <td> {{$data->keywords}} </td>
                                                    <td >{{array_get($ranking,0)}}</td>
                                                    <td >{{array_get($ranking,1)}}</td>
                                                   <td >{{array_get($ranking,2)}}</td>
                                                    <td >{{array_get($ranking,3)}}</td>
                                                     <td >{{array_get($ranking,4)}}</td>
													 <td >{{array_get($ranking,5)}}</td>
													 <td >{{array_get($ranking,6)}}</td>  
													 
                                                    <td >{{array_get($rating,0)}}</td>
                                                    <td >{{array_get($rating,1)}}</td>
                                                   <td >{{array_get($rating,2)}}</td>
                                                    <td >{{array_get($rating,3)}}</td>
                                                     <td >{{array_get($rating,4)}}</td>
													 <td >{{array_get($rating,5)}}</td>
													 <td >{{array_get($rating,6)}}</td>  
													 
													 <td >{{array_get($review,0)}}</td>
                                                    <td >{{array_get($review,1)}}</td>
                                                   <td >{{array_get($review,2)}}</td>
                                                    <td >{{array_get($review,3)}}</td>
                                                     <td >{{array_get($review,4)}}</td>
													 <td >{{array_get($review,5)}}</td>
													 <td >{{array_get($review,6)}}</td>  
													 
													 <td >{{array_get($sales,0)}}</td>
                                                    <td >{{array_get($sales,1)}}</td>
                                                   <td >{{array_get($sales,2)}}</td>
                                                    <td >{{array_get($sales,3)}}</td>
                                                     <td >{{array_get($sales,4)}}</td>
													 <td >{{array_get($sales,5)}}</td>
													 <td >{{array_get($sales,6)}}</td>  
													 
													 <td >{{array_get($price,0)}}</td>
                                                    <td >{{array_get($price,1)}}</td>
                                                   <td >{{array_get($price,2)}}</td>
                                                    <td >{{array_get($price,3)}}</td>
                                                     <td >{{array_get($price,4)}}</td>
													 <td >{{array_get($price,5)}}</td>
													 <td >{{array_get($price,6)}}</td>  
													 
													 <td >{{array_get($flow,0)}}</td>
                                                    <td >{{array_get($flow,1)}}</td>
                                                   <td >{{array_get($flow,2)}}</td>
                                                    <td >{{array_get($flow,3)}}</td>
                                                     <td >{{array_get($flow,4)}}</td>
													 <td >{{array_get($flow,5)}}</td>
													 <td >{{array_get($flow,6)}}</td>  
													 
													 <td >{{array_get($conversion,0)}}</td>
                                                    <td >{{array_get($conversion,1)}}</td>
                                                   <td >{{array_get($conversion,2)}}</td>
                                                    <td >{{array_get($conversion,3)}}</td>
                                                     <td >{{array_get($conversion,4)}}</td>
													 <td >{{array_get($conversion,5)}}</td>
													 <td >{{array_get($conversion,6)}}</td>  
                                                    <td>
                                                        {{$data->fba_stock}}
                                                    </td>
                                                    <td>
                                                         {{$data->fba_transfer}}
                                                    </td>
													<td>
                                                        {{$data->fbm_stock}}
                                                    </td>
													<td>
                                                         {{$data->strategy}}
                                                    </td>
													<td>
                                                        {{$data->total_stock}}
                                                    </td>
													<td>
                                                       {{$data->fba_keep}}
                                                    </td>
													<td>
                                                       {{$data->total_keep}}
                                                    </td>
                                                </tr>
                                              @endforeach  
                                            </tbody>
                    </table>
					</form>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>


<script>
var TableDatatablesEditable = function () {

    var handleTable = function () {

        function restoreRow(oTable, nRow) {
            var aData = oTable.fnGetData(nRow);
            var jqTds = $('>td', nRow);

            for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
                oTable.fnUpdate(aData[i], nRow, i, false);
            }

            oTable.fnDraw();
        }

        function editRow(oTable, nRow) {
            var aData = oTable.fnGetData(nRow);
            var jqTds = $('>td', nRow);
			for (var i = 8; i < 62; i++) {
                  jqTds[i].innerHTML = '<input type="text" name="sku_data[]" class="form-control input-small" value="' + aData[i] + '">';
            }

            jqTds[3].innerHTML = aData[3]+'<a class="edit badge badge-info" href="">Save</a> <a class="cancel badge badge-danger" href="">Cancel</a>';
        }

        function saveRow(oTable, nRow) {
            var jqInputs = $('input', nRow);
			var aData = oTable.fnGetData(nRow);
			var numArr = [];
			numArr.push(aData[7]);
			numArr.push(aData[5]);
			numArr.push('{{$date_start}}');
			
			for (var i = 8; i < 62; i++) {
				numArr.push(jqInputs[i-8].value);
                oTable.fnUpdate(jqInputs[i-8].value, nRow, i, false);
            }
			oTable.fnUpdate(aData[3], nRow, 3, false);
			$.ajax({
				url:'/skus',
				data:{data:JSON.stringify(numArr)},
				dataType: "json",
				type:'post',
				success:function(redata){
					console.log(redata);
					if(redata.result==1){
						if(redata.total_stock) oTable.fnUpdate(redata.total_stock, nRow, 62, false);
						if(redata.fba_keep) oTable.fnUpdate(redata.fba_keep, nRow, 63, false);
						if(redata.total_keep) oTable.fnUpdate(redata.total_keep, nRow, 64, false);
					}
				}
			})
            
            oTable.fnDraw();
        }

        function cancelEditRow(oTable, nRow) {
            var jqInputs = $('input', nRow);
			var aData = oTable.fnGetData(nRow);
            for (var i = 8; i < 62; i++) {
                 oTable.fnUpdate(jqInputs[i-8].value, nRow, i, false);
            }
            oTable.fnUpdate(aData[3], nRow, 3, false);
            oTable.fnDraw();
        }

        var table = $('#skus_editable');

        var oTable = table.dataTable({

            // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
            // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js). 
            // So when dropdowns used the scrollable div should be removed. 
            //"dom": "<'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",

            "lengthMenu": [
                [5, 15, 20, -1],
                [5, 15, 20, "All"] // change per page values here
            ],

            // Or you can use remote translation file
            //"language": {
            //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
            //},

            // set the initial value
            "pageLength": 20,
			bFilter: false,
            "language": {
                "lengthMenu": " _MENU_ records"
            },
            "columnDefs": [{ // set default column settings
                'orderable': true,
                'targets': [0]
            }, {
                "searchable": true,
                "targets": [0]
            }],
            "order": [
                [0, "asc"]
            ],

					 "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(4).attr('style', 'max-width: 80px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(4).attr('title', $(row).children('td').eq(4).text());
						$(row).children('td').eq(7).attr('style', 'max-width: 80px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(7).attr('title', $(row).children('td').eq(7).text());
						$(row).children('td').eq(8).attr('style', 'max-width: 80px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(8).attr('title', $(row).children('td').eq(8).text());
						
                    },
					
        });

        var nEditing = null;
        var nNew = false;

        table.on('click', '.cancel', function (e) {
            e.preventDefault();
            if (nNew) {
                oTable.fnDeleteRow(nEditing);
                nEditing = null;
                nNew = false;
            } else {
                restoreRow(oTable, nEditing);
                nEditing = null;
            }
        });

        table.on('click', '.edit', function (e) {
            e.preventDefault();
            nNew = false;
            
            /* Get the row as a parent of the link that was clicked on */
            var nRow = $(this).parents('tr')[0];

            if (nEditing !== null && nEditing != nRow) {
                /* Currently editing - but not this row - restore the old before continuing to edit mode */
                restoreRow(oTable, nEditing);
                editRow(oTable, nRow);
                nEditing = nRow;
            } else if (nEditing == nRow && this.innerHTML == "Save") {
                /* Editing this row and want to save it */
                saveRow(oTable, nEditing);
                nEditing = null;
                
            } else {
                /* No edit in progress - let's start one */
                editRow(oTable, nRow);
                nEditing = nRow;
            }
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            handleTable();
        }

    };

}();

jQuery(document).ready(function() {
    TableDatatablesEditable.init();
	$('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
});
</script>


@endsection
