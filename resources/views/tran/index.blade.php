



@extends('layouts.layout')
@section('label', 'Distribution analysis')
@section('content')
<style type="text/css">
.dataTables_extended_wrapper .table.dataTable {
  margin: 0px !important;
}

table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 10px 0px;
}
th,td,td>span {
    font-size:12px !important;
	font-family:Arial, Helvetica, sans-serif;}

</style>
    <h1 class="page-title font-red-intense"> Distribution analysis
        <small></small>
    </h1>
    <div class="row" >
        <div class="col-md-12">
		 <div class="portlet light bordered">
			<table class="table table-striped table-bordered table-hover order-column" id="table_tab_negative_value" style="display:none;">
                       <thead>
                            <tr role="row" class="heading">
								<th style="max-width:30px;">Asin</th>
								<th style="max-width:30px;">Sku</th>
								<th style="max-width:30px;">Sales/D</th>
								<th style="max-width:30px;">Rev</th>
								<th style="max-width:30px;">RevScore</th>
								<th style="max-width:30px;">RevC</th>
								<th style="max-width:30px;">RevCScore</th>
								<th style="max-width:30px;">ProfitM</th>
								<th style="max-width:30px;">ProfitMScore</th>
								<!--<th style="max-width:30px;">RevChange</th>
								<th style="max-width:30px;">RevChangeScore</th>-->
								<th style="max-width:30px;">FbmDistD</th>
								<th style="max-width:30px;">FbaStock</th>
								<th style="max-width:30px;">FbmStock</th>
								<th style="max-width:30px;">FbmAdd</th>
								<th style="max-width:30px;">ShipAdd</th>
                                <th style="max-width:30px;">FbaSales</th>
                                <th style="max-width:30px;">FbmLoc</th>
								<th style="max-width:30px;">FbmLocScore</th>
								<th style="max-width:30px;">FbaDay</th>
								<th style="max-width:30px;">FbaDayScore</th>
								<th style="max-width:30px;">Profit</th>
								<th style="max-width:30px;">ProfitScore</th>
								<th style="max-width:30px;">Priority Dist</th>
                            </tr>
                            </thead>
                        <tbody>
                        @foreach ($datas as $data)
						
							<?php
							$avg_star = array_get($data,'avg_star',0);
							if($avg_star>=4.8){
								$avg_star_score=1.1;
							}elseif($avg_star>=4.5){
								$avg_star_score=1.05;
							}elseif($avg_star>=4.3){
								$avg_star_score=1;
							}elseif($avg_star>=4.2){
								$avg_star_score=0.9;
							}elseif($avg_star>=4.1){
								$avg_star_score=0.7;
							}elseif($avg_star>=4){
								$avg_star_score=0.5;
							}elseif($avg_star>=3.9){
								$avg_star_score=0.2;
							}elseif($avg_star>=3.8){
								$avg_star_score=0.1;
							}else{
								$avg_star_score=0;
							}
							
							$total_star = array_get($data,'total_star',0);
							if($total_star>=1000){
								$total_star_score=1.1;
							}elseif($total_star>=500){
								$total_star_score=1.05;
							}elseif($total_star>=100){
								$total_star_score=1;
							}elseif($total_star>=50){
								$total_star_score=0.95;
							}elseif($total_star>=10){
								$total_star_score=0.9;
							}elseif($total_star>=0){
								$total_star_score=0.8;
							}else{
								$total_star_score=0;
							}
							
							
							
							$profits = array_get($data,'profits',0);
							if($profits>=40){
								$profits_score=1.5;
							}elseif($profits>=30){
								$profits_score=1.4;
							}elseif($profits>=25){
								$profits_score=1.35;
							}elseif($profits>=20){
								$profits_score=1.3;
							}elseif($profits>=18){
								$profits_score=1.2;
							}elseif($profits>=16){
								$profits_score=1.15;
							}elseif($profits>=14){
								$profits_score=1.1;
							}elseif($profits>=12){
								$profits_score=1.05;
							}elseif($profits>=10){
								$profits_score=1;
							}elseif($profits>=8){
								$profits_score=0.95;
							}elseif($profits>=6){
								$profits_score=0.8;
							}elseif($profits>=4){
								$profits_score=0.6;
							}elseif($profits>=2){
								$profits_score=0.4;
							}elseif($profits>=0){
								$profits_score=0.2;
							}elseif($profits>=-5){
								$profits_score=0.1;
							}else{
								$profits_score=0;
							}
							$fba_sales = round(array_get($data,'sales'),2)*$avg_star_score*$profits_score*$total_star_score*28;
							
							if( array_get($data,'fbm_stock')>($fba_sales - array_get($data,'fba_stock')) ){
								$fbm_add = $fba_sales - array_get($data,'fba_stock');
								$ship_add = 'Warning';
							}else{
								$fbm_add = array_get($data,'fbm_stock');
								$ship_add =  $fba_sales - array_get($data,'fba_stock') - array_get($data,'fbm_stock');
							}
							
							
							$fba_stock_keep = array_get($data,'fba_stock_keep',0);
							if($fba_stock_keep>=30){
								$fba_stock_keep_score=0.8;
							}elseif($fba_stock_keep>=21){
								$fba_stock_keep_score=1;
							}elseif($fba_stock_keep>=14){
								$fba_stock_keep_score=1.5;
							}else{
								$fba_stock_keep_score=2;
							}
							
							
							
							$profits_value = array_get($data,'profits_value',0);
							if($profits_value>=200000){
								$profits_value_score=3;
							}elseif($profits_value>=100000){
								$profits_value_score=2;
							}elseif($profits_value>=50000){
								$profits_value_score=1.5;
							}elseif($profits_value>=30000){
								$profits_value_score=1.2;
							}elseif($profits_value>=10000){
								$profits_value_score=1.1;
							}elseif($profits_value>=5000){
								$profits_value_score=0.5;
							}elseif($profits_value>=0){
								$profits_value_score=0.2;
							}else{
								$profits_value_score=0.1;
							}
							
							
							$pd = $profits_value_score*$fba_stock_keep_score;
							?>
                            <tr class="odd gradeX">
                                <td>
								<a href="https://{{array_get($data,'site')}}/dp/{{array_get($data,'asin')}} " target="_blank">{{array_get($data,'asin')}}</a>
								</td>
                                <td>
								{{implode(",",unserialize(array_get($data,'item_code')))}}
								</td>
								<td>
								{{round(array_get($data,'sales'),2)}}
								</td>
                                <td>
								{{round($avg_star,1)}}
								</td>
								<td>
								{{$avg_star_score}}
								</td>
								<td>
								{{round($total_star,1)}}
								</td>
								<td>
								{{$total_star_score}}
								</td>
                                <td>
								{{round($profits,2)}}%
								</td>
								<td>
								{{$profits_score}}
								</td>
								<!--<td></td><td></td>-->
								<td>28</td>
								<td>
								{{round(array_get($data,'fba_stock'),2)}}
								</td>
								<td>
								{{round(array_get($data,'fbm_stock'),2)}}
								</td>
								<td>
								{{round($fbm_add,2)}}
								</td>
								<td>
								{{$ship_add}}
								</td>
								<td>
								{{round($fba_sales,2)}}
								</td>
								
								<td>A</td><td>1</td>
								<td>
								{{round($fba_stock_keep,2)}}
								</td>
								<td>
								{{$fba_stock_keep_score}}
								</td>
								<td>
								{{round($profits_value,2)}}
								</td>
								<td>
								{{$profits_value_score}}
								</td>
								<td>
								{{$pd}}
								</td>
                            </tr>
                        @endforeach



                        </tbody>
                    </table>
					<div style="clear:both;"></div>	
				<script>
				$(function() {
						// begin first table
					$('#table_tab_negative_value').dataTable({
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
						"autoWidth":false,
						"bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
	
						"lengthMenu": [
							[10, 50, 100, -1],
							[10, 50, 100, "All"] // change per page values here
						],
						// set the initial value
						"pageLength": 50,
						"createdRow": function( row, data, dataIndex ) {
							$(row).children('td').eq(0).attr('style', 'width: 90px;');
							$(row).children('td').eq(1).attr('style', 'max-width: 60px;overflow:hidden;white-space:nowrap;text-align: left; ');
							$(row).children('td').eq(1).attr('title', $(row).children('td').eq(1).text());
							
						},
						"order": [
							[2 , "desc"]
						] // set first column as a default sort by asc
					});
					$('#table_tab_negative_value').show();
				});
				
		
		</script>
		</div>
		
         </div></div>

@endsection

