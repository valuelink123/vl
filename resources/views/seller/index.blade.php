@extends('layouts.layout')
@section('label', 'Seller Tab')
@section('content')
<style>
.table-checkable tr>td:first-child, .table-checkable tr>th:first-child{max-width:auto !important;}
table.dataTable thead>tr>th.sorting_asc, table.dataTable thead>tr>th.sorting_desc, table.dataTable thead>tr>th.sorting, table.dataTable thead>tr>td.sorting_asc, table.dataTable thead>tr>td.sorting_desc, table.dataTable thead>tr>td.sorting {
    padding-right: 15px !important;
}
table.dataTable thead th, table.dataTable thead td {
    padding: 10px 2px !important;}
th,td {
    font-size:14px !important;}


</style>
    <h1 class="page-title font-red-intense"> Seller Tab
        <small>Seller Tab</small>
    </h1>
    <div class="row">
        <div class="col-md-12">
<div class="portlet light portlet-fit bordered">
	

    <div class="portlet-body">

        <div class="tabbable-line">
            <ul class="nav nav-tabs ">
				<li class="active">
                    <a href="#tab_negative_value" data-toggle="tab" aria-expanded="true" > Negative Remove Task</a>
                </li>
				
				
				<?php
				$site_key=array(
					'A2EUQ1WTGCTBG2'=>'CA',
					'A1PA6795UKMFR9'=>'DE',
					'A1RKKUPIHCS9HS'=>'ES',
					'A13V1IB3VIYZZH'=>'FR',
					'A21TJRUUN4KGV'=>'IN',
					'APJ6JRA9NG5V4'=>'IT',
					'A1VC38T7YXB528'=>'JP',
					'A1F83G8C2ARO7P'=>'GB',
					'A1AM78C64UM0Y8'=>'MX',
					'ATVPDKIKX0DER'=>'US'
				);
				$order_fields = getFieldtoSort();
				$config_fields = getFieldtoField();
				$xxx=0;
				foreach($tabs as $key=>$tab){ 
				$xxx++;
				?>
                <li <?php if($xxx==0) echo 'class="active"'; ?>>
                    <a href="#tab_<?php echo $key ?>" data-toggle="tab" aria-expanded="<?php echo ($xxx==1)?'false':'false'; ?>"> {{array_get($tab,'tab')}}</a>
                </li>
                
				<?php } ?>
				
				
				<li >
                    <a href="#tab_positive_value" data-toggle="tab" aria-expanded="false"> Positive Upgrade Task</a>
                </li>
            </ul>
            <div class="tab-content">
			
			
				<div class="tab-pane active" id="tab_negative_value">
					<table class="table table-striped table-bordered table-hover order-column" id="table_tab_negative_value" style="display:none;" >
                        <thead>
                        <tr>
                            <th>Asin </th>
							<th>Site </th>
							<th>Rating </th>
                            <th>Date</th>
							<th>Reviewer Name</th>
							<th>Review Content</th>
							<th>Important Value</th>
							<th>Seller</th>
							<th>Link</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($negatives as $data)
                            <tr class="odd gradeX">
                                <td>
                                    <a href="/seller/{{array_get($data,'asin')}}/{{array_get(siteToMarketplaceid(),array_get($data,'site'))}}" target="_blank">
									{{array_get($data,'asin')}} 
									</a> 
                                </td>
                                <td>
                                    {{array_get($data,'site')}} 
                                </td>
								<td>
                                    {{round(array_get($data,'rating'),1)}}
                                </td>
                                <td>
                                   {{array_get($data,'date')}} 
                                </td>
                                <td>
                                    {{array_get($data,'reviewer_name')}} 
                                </td>
								<td>
                                    <a href="https://{{array_get($data,'site')}}/gp/customer-reviews/{{array_get($data,'review')}}" target="_blank">{{strip_tags(array_get($data,'review_content'))?strip_tags(array_get($data,'review_content')):'Click here to link to review'}} </a>
                                </td>
								<td>
                                    {{array_get($data,'negative_value')}} 
                                </td>
								<td>
                                    {{array_get($data,'seller')}}
                                </td>
								<td>
                                    <a href="review/{{array_get($data,'id')}}/edit" target="_blank">
                                        <button type="submit" class="btn btn-success btn-xs">View</button>
                                    </a>
                                </td>
                            </tr>
                        @endforeach



                        </tbody>
                    </table>
					<div style="clear:both;"></div>	
                </div>
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

						"bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
	
						"lengthMenu": [
							[10, 50, 100, -1],
							[10, 50, 100, "All"] // change per page values here
						],
						// set the initial value
						"pageLength": 10,
						"createdRow": function( row, data, dataIndex ) {
							$(row).children('td').eq(5).attr('style', 'max-width: 200px;overflow:hidden;white-space:nowrap;text-align: left; ');
							$(row).children('td').eq(5).attr('title', $(row).children('td').eq(5).text());
						},
						"order": [
							[6 , "desc"]
						] // set first column as a default sort by asc
					});
					$('#table_tab_negative_value').show();
				});
				
		
		</script>
		
				<?php
				$xxx=0;
				foreach($tabs as $key=>$tab){ 
				$datas  = array_get($tab,'data');
				$rules  = array_get($tab,'rules');
	
				$xxx++;
				?>
                <div class="tab-pane <?php if($xxx==0) echo 'active'; ?>" id="tab_<?php echo $key ?>">
					<?php
					foreach($rules as $rule =>$rule_val){
						echo '<font style=\'color:'.$rule.';\'> <i  class="fa fa-info-circle popovers"></i>'.array_get($rule_val,'rec_str').'</font><BR>';
					}
					?>
					<table class="table table-striped table-bordered table-hover order-column" id="table_tab_<?php echo $key ?>" style="display:none;" >
                        <thead>
                        <tr>
                            <th >Asin </th>
							<th>Sku </th>
							<th>Sku Description </th>
                            <th >Sales/D</th>
							<th >Review</th>
							<th >RevCount</th>
							<th >Action</th>
							<th >Profit</th>
							<th>FbaStock</th>
							<th  >FbaDays</th>
                            <th >TotalStock</th>
							<th  >StockDays</th>
							<th >StockValue</th>
							<th  >Seller</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($datas as $data)
                            <tr class="odd gradeX">
                                <td>
                                    <a href="/seller/{{array_get($data,'asin')}}/{{array_get($data,'marketplaceid')}}" target="_blank">
									{{array_get($data,'asin')}} 
									</a><span class="label label-sm label-default"><a href="https://{{array_get($data,'site')}}/dp/{{array_get($data,'asin')}}" target="_blank">{{array_get($site_key,array_get($data,'marketplaceid'))}}</a></span> 
                                </td>
                                <td>
                                    {{implode(',',unserialize(array_get($data,'item_code')))}}
                                </td>
								<td>
                                    {{implode(',',unserialize(array_get($data,'item_name')))}}
                                </td>
                                <td>
                                    {{round(array_get($data,'sales'),2)}}
                                </td>
                                <td>
                                    {{round(array_get($data,'avg_star'),2)}}
                                </td>
								<td>
                                    {{intval(array_get($data,'total_star'))}}
                                </td>
								
								<td>
                                    <?php
									
									//$s_f=$s_l='';
									$helplink=array_get(array_get($tab,'rules'),array_get($data,'color').'.rec_exp');
									if(!$helplink) $helplink='javascript:void(0);';
									//if(array_get(array_get($tab,'rules'),array_get($data,'color').'.rec_exp')){
										$s_f= '<a href="'.$helplink.'" target="_blank" style="color:'.array_get($data,'color').';">';
										$s_l='</a>';
									//}
									//if(array_get(array_get($tab,'rules'),array_get($data,'color').'.rec_str')) echo $s_f.'<i  class="fa fa-info-circle popovers" data-container="body" onclick=" " data-trigger="hover" data-placement="left" data-html="true" data-content="'.str_replace('*','<p>*',array_get(array_get($tab,'rules'),array_get($data,'color').'.rec_exp')).'" data-original-title="<strong>'.$s_f.array_get(array_get($tab,'rules'),array_get($data,'color').'.rec_str').$s_l.'</strong>"></i>'.$s_l;
									if(array_get(array_get($tab,'rules'),array_get($data,'color').'.rec_str')) echo $s_f.array_get(array_get($tab,'rules'),array_get($data,'color').'.rec_str').$s_l;
									
									?>
									
                                </td>
								
                                <td>
                                    {{round(array_get($data,'profits'),2)}} %
                                </td>
								 <td>
                                    {{round(array_get($data,'stock'),2)+round(array_get($data,'transfer'),2)}}
                                </td>
								
								<td>
                                    {{round(array_get($data,'fba_stock_keep'),2)}}
                                </td>
                                <td>
                                    {{round(array_get($data,'stock'),2)+round(array_get($data,'transfer'),2)+round(array_get($data,'fbm_stock'),2)}}
                                </td>
								<td>
                                    {{round(array_get($data,'stock_keep'),2)}}
                                </td>
								<td>
                                    {{round(array_get($data,'stock_amount'),2)}} <i class="fa fa-rmb"></i>
                                </td>
								
								
								<td>
                                    {{array_get($data,'seller')}}
                                </td>

                            </tr>
                        @endforeach



                        </tbody>
                    </table>
					<div style="clear:both;"></div>	
                </div>
				<script>
				$(function() {
						// begin first table
					$('#table_tab_{{$key}}').dataTable({
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

						"bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
	
						"lengthMenu": [
							[20, 50, 100, -1],
							[20, 50, 100, "All"] // change per page values here
						],
						// set the initial value
						"pageLength": 20,
						"createdRow": function( row, data, dataIndex ) {
							$(row).children('td').eq(0).attr('style', 'text-align: left; ');
							$(row).children('td').eq(1).attr('style', 'max-width: 50px;overflow:hidden;white-space:nowrap;text-align: left; ');
							$(row).children('td').eq(2).attr('style', 'max-width: 90px;overflow:hidden;white-space:nowrap;text-align: left; ');
							$(row).children('td').eq(1).attr('title', $(row).children('td').eq(1).text());
							$(row).children('td').eq(2).attr('title', $(row).children('td').eq(2).text());
						},
						"order": [
							[{{array_get($order_fields,array_get($tab,'order'))}} , "{{array_get($tab,'by')}}"]
						] // set first column as a default sort by asc
					});
					$('#table_tab_{{$key}}').show();
				});
				
		
		</script>
				<?php } ?>
               




			
		
		
		
		
		
		
		<div class="tab-pane" id="tab_positive_value">
					<table class="table table-striped table-bordered table-hover order-column" id="table_tab_positive_value" style="display:none;" >
                        <thead>
                        <tr>
                            <th>Asin </th>
							<th>Site </th>
							<th>Review </th>
                            <th>Review Count</th>
							<th>Total Stock Day</th>
							<th>Total Stock Value</th>
							<th>Important Value</th>
							<th>Seller</th>
							<th>Link</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($positives as $data)
                            <tr class="odd gradeX">
                                <td>
                                    <a href="/seller/{{array_get($data,'asin')}}/{{array_get($data,'marketplaceid')}}" target="_blank">
									{{array_get($data,'asin')}} 
									</a> 
                                </td>
                                <td>
                                    {{array_get($data,'site')}} 
                                </td>
								<td>
                                    {{round(array_get($data,'avg_star'),1)}}
                                </td>
                                <td>
                                   {{intval(array_get($data,'total_star'))}} 
                                </td>
                                <td>
                                   {{round(array_get($data,'stock_keep'),2)}}
                                </td>
								<td>
                                    {{round(array_get($data,'stock_amount'),2)}}
                                </td>
								<td>
                                    {{array_get($data,'positive_value')}} 
                                </td>
								<td>
                                    {{array_get($data,'seller')}}
                                </td>
								<td>
                                    <a href="https://{{array_get($data,'site')}}/dp/{{array_get($data,'asin')}}" target="_blank">
                                        <button type="submit" class="btn btn-success btn-xs">View</button>
                                    </a>
                                </td>
                            </tr>
                        @endforeach



                        </tbody>
                    </table>
					<div style="clear:both;"></div>	
                </div>
				<script>
				$(function() {
						// begin first table
					$('#table_tab_positive_value').dataTable({
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

						"bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
	
						"lengthMenu": [
							[10, 50, 100, -1],
							[10, 50, 100, "All"] // change per page values here
						],
						// set the initial value
						"pageLength": 10,
						"order": [
							[6 , "desc"]
						] // set first column as a default sort by asc
					});
					$('#table_tab_positive_value').show();
				});
				
		
		</script>
		
		
		
            </div>
        </div>


    </div>
</div>
        </div>
		 <div style="clear:both;"></div></div>


@endsection
