@extends('layouts.layout')
@section('label', 'Daily Sales Report')
@section('content')
<style>
        .form-control {
            height: 29px;
        }
		.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}
.input-small {
    width: 80%!important;
	height: 20px;
}

th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}
    </style>
    <h1 class="page-title font-red-intense"> Daily Sales Report
        
    </h1>
	
	
	<div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-body">
							
							
					<div class="table-toolbar">
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
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover" id="datatable_ajax_sp">
                            <thead>
                            <tr role="row"  >
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
                                                <tr role="row" class="heading">
                                                    <th> BG </th>
                                                    <th> BU </th>
                                                    <th> Seller </th>
                                                    <th> Sku </th>
                                                    <th> Description </th>
                                                    <th> Site </th>
													<th> Status </th>
                                                    <th> Link </th>
                                                    <th width="50px"> Keywords </th>
                                                    
                                                    <th width="50px"> Mon </th>
                                                    <th width="50px"> Tues </th>
													<th width="50px"> Wed </th>
                                                    <th width="50px"> Thur </th>
                                                    <th width="50px"> Fri </th>
                                                    <th width="50px"> Sat </th>
                                                     <th width="50px"> Sun </th>
                                                    <th width="50px"> Mon </th>
                                                    <th width="50px"> Tues </th>
													<th width="50px"> Wed </th>
                                                    <th width="50px"> Thur </th>
                                                    <th width="50px"> Fri </th>
                                                    <th width="50px"> Sat </th>
                                                    <th width="50px"> Sun </th>
                                                    <th width="50px"> Mon </th>
                                                    <th width="50px"> Tues </th>
													<th width="50px"> Wed </th>
                                                    <th width="50px"> Thur </th>
                                                    <th width="50px"> Fri </th>
                                                    <th width="50px"> Sat </th>
													 <th width="50px"> Sun </th>
                                                    <th width="50px"> Mon </th>
                                                    <th width="50px"> Tues </th>
													<th width="50px"> Wed </th>
                                                    <th width="50px"> Thur </th>
                                                    <th width="50px"> Fri </th>
                                                    <th width="50px"> Sat </th>
                                                     <th width="50px"> Sun </th>
                                                    <th width="50px"> Mon </th>
                                                    <th width="50px"> Tues </th>
													<th width="50px"> Wed </th>
                                                    <th width="50px"> Thur </th>
                                                    <th width="50px"> Fri </th>
                                                    <th width="50px"> Sat </th>
                                                     <th width="50px"> Sun </th>
                                                    <th width="50px"> Mon </th>
                                                    <th width="50px"> Tues </th>
													<th width="50px"> Wed </th>
                                                    <th width="50px"> Thur </th>
                                                    <th width="50px"> Fri </th>
                                                    <th width="50px"> Sat </th>
                                                     <th width="50px"> Sun </th>
                                                    <th width="50px"> Mon </th>
                                                    <th width="50px"> Tues </th>
													<th width="50px"> Wed </th>
                                                    <th width="50px"> Thur </th>
                                                    <th width="50px"> Fri </th>
                                                    <th width="50px"> Sat </th>
													<th width="50px"> Sun </th>
													<th width="50px"> FBA InStock </th>
                                                    <th width="50px"> FBA Transfer </th>
													<th width="50px"> FBM InStock </th>
													<th width="50px"> Strategy </th>
                                                    <th width="50px"> Total </th>
                                                    <th width="50px"> FBA Keep </th>
													<th width="50px"> Total Keep </th>
													
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
											$d_number = (date('w',strtotime($date_start))==0)?6:(date('w',strtotime($date_start))-1);
											?>
                                                <tr>
                                                    <td> {{$data->bg}} </td>
                                                    <td> {{$data->bu}} </td>
                                                    <td> {{array_get($users,$data->sap_seller_id,$data->sap_seller_id)}} </td>
                                                    <td class="center">{{$data->item_code}} </td>
                                                    <td>
                                                        {{$data->item_name}}
                                                    </td>
                                                    <td>
                                                       {{$data->site}}
                                                    </td>
													<td> {!!($data->status)?'<span class="btn btn-success btn-xs">Reserved</span>':'<span class="btn btn-danger btn-xs">Eliminate</span>'!!} </td>
                                                    <td><a href="https://{{$data->site}}/dp/{{strip_tags(str_replace('&nbsp;','',$data->asin))}}" target="_blank">{{strip_tags(str_replace('&nbsp;','',$data->asin))}}</a></td>
                                                    <td> {{$data->keywords}}</td>
													
													<?php 
													for($i=0;$i<7;$i++){
														$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
														echo '<td '.$style.'>'.array_get($ranking,$i).'</td>';
													}
													?>
													
                                                    <?php 
													for($i=0;$i<7;$i++){
														$style=(($d_number==$i)?'style="background:#fff2cc;"':'');
														echo '<td '.$style.'>'.array_get($rating,$i).'</td>';
													}
													?>
                                                    
													 
                                                   <?php 
													for($i=0;$i<7;$i++){
														$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
														echo '<td '.$style.'>'.array_get($review,$i).'</td>';
													}
													?>
													
                                                    <?php 
													for($i=0;$i<7;$i++){
														$style=(($d_number==$i)?'style="background:#fff2cc;"':'');
														echo '<td '.$style.'>'.array_get($sales,$i).'</td>';
													}
													?>
													 
													 <?php 
													for($i=0;$i<7;$i++){
														$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
														echo '<td '.$style.'>'.array_get($price,$i).'</td>';
													}
													?>
													
                                                    <?php 
													for($i=0;$i<7;$i++){
														$style=(($d_number==$i)?'style="background:#fff2cc;"':'');
														echo '<td '.$style.'>'.array_get($flow,$i).'</td>';
													}
													?>
													 
													 <?php 
													for($i=0;$i<7;$i++){
														$style=(($d_number==$i)?'style="background:#ddeef7;"':'');
														echo '<td '.$style.'>'.array_get($conversion,$i).'</td>';
													}
													?>  
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
                    </div>
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

            //jqTds[3].innerHTML = aData[3]+'<a class="edit badge badge-info" href="">Save</a> <a class="cancel badge badge-danger" href="">Cancel</a>';
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
			//oTable.fnUpdate(aData[3], nRow, 3, false);
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

        var table = $('#datatable_ajax_sp');

        var oTable = table.dataTable({

            // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
            // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js). 
            // So when dropdowns used the scrollable div should be removed. 
            //"dom": "<'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",

            "lengthMenu": [
                        [10, 50, 100, -1],
                        [10, 50, 100, 'All'] // change per page values here
                    ],
            "pageLength": 10, // default record count per page
			bFilter: false,
            "language": {
                "lengthMenu": " _MENU_ records"
            },
           
            

					 "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(4).attr('style', 'max-width: 80px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(4).attr('title', $(row).children('td').eq(4).text());
						$(row).children('td').eq(7).attr('style', 'max-width: 80px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(7).attr('title', $(row).children('td').eq(7).text());
						$(row).children('td').eq(8).attr('style', 'max-width: 50px;overflow:hidden;white-space:nowrap;text-align: left; ');
						$(row).children('td').eq(8).attr('title', $(row).children('td').eq(8).text());
						
                    },
					
					scrollY:        450,
                    scrollX:        true,
					buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'skus' }
                    ],

					fixedColumns:   {
						leftColumns:8,
						rightColumns: 0
					},
					"dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
					
        });

        var nEditing = null;
        var nNew = false;
		
		table.on('keypress', 'input', function (e) {
             var nRow = $(this).parents('tr')[0];	 
			 if (nRow && e.keyCode == "13") {
			 	e.preventDefault();
				saveRow(oTable, nRow);
				nEditing = null;
			 }
           
        });
		
        table.on('dblclick', 'td', function (e) {
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
