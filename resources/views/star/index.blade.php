@extends('layouts.layout')
@section('label', 'Asin Rating List')
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
    <h1 class="page-title font-red-intense"> Asin Rating List
        <small></small>
    </h1>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="table-toolbar">
                    <form role="form" action="{{url('star')}}" method="GET">
                        {{ csrf_field() }}
                        <div class="row">
						
						<div class="col-md-2">
                            <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="Date" value="{{$date_from}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="Compare Date" value="{{$date_to}}">
                                <span class="input-group-btn">
                                                                        <button class="btn btn-sm default" type="button">
                                                                            <i class="fa fa-calendar"></i>
                                                                        </button>
                                                                    </span>
                            </div>
                        </div>
                        <div class="col-md-1">
                            
                                <input type="text" class="form-control form-filter input-sm"  name="star_from" placeholder="Rating From" value="{{array_get($_REQUEST,'star_from')}}">

                           
                        </div>
                        <div class="col-md-1">
                            
                                <input type="text" class="form-control form-filter input-sm"  name="star_to" placeholder="Rating To" value="{{array_get($_REQUEST,'star_to')}}">

                        </div>
						@if(Auth::user()->seller_rules)
						<div class="col-md-2">
						<select class="mt-multiselect btn btn-default input-sm form-control form-filter" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="user_id[]" id="user_id[]">

                                        @foreach ($users as $user_id=>$user_name)
                                            <option value="{{$user_id}}">{{$user_name}}</option>
                                        @endforeach
                                    </select>
						</div>
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
						@endif
						<!--
						<div class="col-md-2">
						<select class="form-control form-filter input-sm" name="rating_status">
                                        <option value="">Rating Status</option>
                                        <option value="Above" <?php if("Above"==array_get($_REQUEST,'rating_status')) echo 'selected';?>>Above Warning Rating</option>
										<option value="Below" <?php if("Below"==array_get($_REQUEST,'rating_status')) echo 'selected';?>>Below Warning Rating</option>
                                    </select>
						</div>-->
						<div class="col-md-2">
						<select class="form-control form-filter input-sm" name="listing_status">
                                        <option value="">All Listing Status</option>
                                        <option value="1" <?php if(1==array_get($_REQUEST,'listing_status')) echo 'selected';?>>Listing Down</option>
										<option value="2" <?php if(2==array_get($_REQUEST,'listing_status')) echo 'selected';?>>Listing UnAvailable</option>
										<option value="3" <?php if(3==array_get($_REQUEST,'listing_status')) echo 'selected';?>>Listing Available</option>
                                    </select>
                                       
						</div>
						
						</div>
						<div class="row">
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="price_status">
                                        <option value="">Price Fluncuation</option>
                                        <option value=">" <?php if(1==array_get($_REQUEST,'price_status')) echo 'selected';?>>Price Increase</option>
										<option value="<" <?php if(2==array_get($_REQUEST,'price_status')) echo 'selected';?>>Price Decrease</option>
                                    </select>
                                       
						</div>
						{{--帖子状态，帖子类型的搜索选项--}}
						<div class="col-md-1">
							<select class="form-control form-filter input-sm" name="post_type">
								<option value="">Asin Type</option>
								@foreach(getPostType() as $key=>$val)
									<option value="{!! $key !!}" @if($key==array_get($_REQUEST,'post_type')) selected @endif>{!! $val['name'] !!}</option>
								@endforeach
							</select>
						</div>

						<div class="col-md-1">
							<select class="form-control form-filter input-sm" name="post_status">
								<option value="">Asin Status</option>
								@foreach(getPostStatus() as $key=>$val)
									<option value="{!! $key !!}" @if($key==array_get($_REQUEST,'post_status')) selected @endif>{!! $val['name'] !!}</option>
								@endforeach
							</select>
						</div>


						
						<div class="col-md-1">
						<select class="form-control form-filter input-sm" name="item_status">
                                        <option value="">Sku Level</option>
                                        <option value="1" <?php if(1==array_get($_REQUEST,'item_status')) echo 'selected';?>>Eliminate</option>
										<option value="2" <?php if(2==array_get($_REQUEST,'item_status')) echo 'selected';?>>Reserved</option>
                                    </select>
                                       
						</div>
						
						
						<div class="col-md-3 form-inline">
						<select class="form-filter input-sm form-group" name="coupon_than">
                                        <option value="">Coupon Compare</option>
                                        <option value=">" <?php if('S'==array_get($_REQUEST,'coupon_than')) echo 'selected';?>>></option>
										<option value=">=" <?php if('A'==array_get($_REQUEST,'coupon_than')) echo 'selected';?>>>=</option>
										<option value="=" <?php if('B'==array_get($_REQUEST,'coupon_than')) echo 'selected';?>>=</option>
										<option value="<=" <?php if('C'==array_get($_REQUEST,'coupon_than')) echo 'selected';?>><=</option>
										<option value="<" <?php if('D'==array_get($_REQUEST,'coupon_than')) echo 'selected';?>><</option>
                                    </select>
									
									<select class="form-filter input-sm form-group" name="coupon_type">
                                        <option value="">Coupon Type</option>
                                        <option value="coupon_p" <?php if('coupon_p'==array_get($_REQUEST,'coupon_type')) echo 'selected';?>>%</option>
										<option value="coupon_n" <?php if('coupon_n'==array_get($_REQUEST,'coupon_type')) echo 'selected';?>>$</option>
                                    </select>
                                    <input type="text" class=" form-filter input-sm form-group" name="coupon_value" placeholder="Coupon Value" value ="{{array_get($_REQUEST,'coupon_value')}}">
						</div>
						 <div class="col-md-2">
						<select class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="site[]" id="site[]">

                                        @foreach (getAsinSites() as $v)
                                            <option value="{{$v}}">{{$v}}</option>
                                        @endforeach
                                    </select>
						</div>
						
						<div class="col-md-2">
						<input type="text" class="form-control form-filter input-sm" name="keywords" placeholder="Keywords" value ="{{array_get($_REQUEST,'keywords')}}">
                                       
						</div>	
						
						<div class="col-md-1"><button type="button" class="btn blue" id="data_search">Search</button>
						</div>
						</div>
					
                    </form>
					<div style="clear:both;"></div>
                </div>

				<div>

				</div>
                <div class="table-container">

                    <table class="table table-striped table-bordered table-hover" id="datatable_ajax_asin">
                        <thead>
							
                            <tr role="row" class="heading">
								<th style="min-width:60px;"> Asin </th><!-- 0 -->
                                <th style="min-width:50px;"> Item No. </th><!-- 1 -->
								<th style="min-width:50px;"> Detail</th><!-- 2 -->
								<th style="min-width:50px;"> Status </th>	<!-- 3 -->
                                <th style="min-width:50px;"> Rating Status </th><!-- 4 -->
                                <th style="min-width:50px;"> Listing </th><!-- 5 -->
                                <th style="min-width:50px;"> Price </th><!-- 6 -->
                                <th style="min-width:50px;"> Coupon</th><!-- 7 -->
                                <th style="min-width:50px;">Ratings </th><!-- 8 -->

                                <th style="min-width:50px;"> Quantity Changes </th><!-- 9 -->
                                <th style="min-width:50px;"> Rating Changes </th><!-- 10 -->
								<th style="min-width:50px;"> Positive Changes </th><!-- 11 -->
								<th style="min-width:50px;"> Negative Changes </th><!-- 12 -->

                                <th style="min-width:50px;"> One Star</th><!-- 13 -->
                                <th style="min-width:50px;"> Two Star</th><!-- 14 -->
                                <th style="min-width:50px;"> Three Star</th><!-- 15 -->
                                <th style="min-width:50px;"> Four Star</th><!-- 16 -->
                                <th style="min-width:50px;"> Five Star</th><!-- 17 -->


								<th style="min-width:50px;"> Last Update</th><!-- 13 -->
                                <th style="min-width:50px;"> Seller </th><!-- 19 -->
								<th style="min-width:50px;"> Action </th><!-- 20 -->
                            </tr>
							
                            
                            </thead>
                            <tbody></tbody>
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>

{{--编辑帖子状态和帖子类型--}}
<div id="edit-content" style="display:none;">
	<form id="edit-form">
		<input type="hidden" class="form-control" name="asin" id="post-asin" value="">
		<input type="hidden" class="form-control" name="domain" id="post-domain" value="">
		<div class="form-group">
			<label>Post Type</label>
			<div class="input-group ">
				<select id="post-type" class="table-group-action-input form-control input-inline input-small input-sm" name="post-type">
					@foreach(getPostType() as $key=>$val)
						<option value="{!! $key !!}">{!! $val['name'] !!}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="form-group">
			<label>Post Status</label>
			<div class="input-group ">
				<select id="post-status" class="table-group-action-input form-control input-inline input-small input-sm" name="post-status">
					@foreach(getPostStatus() as $key=>$val)
						<option value="{!! $key !!}">{!! $val['name'] !!}</option>
					@endforeach
				</select>
			</div>
		</div>
	</form>
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
			grid.setAjaxParam("star_from", $("input[name='star_from']").val());
            grid.setAjaxParam("star_to", $("input[name='star_to']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id[]']").val());
			//grid.setAjaxParam("rating_status", $("select[name='rating_status']").val());
			grid.setAjaxParam("keywords", $("input[name='keywords']").val());
			grid.setAjaxParam("listing_status", $("select[name='listing_status']").val());
			grid.setAjaxParam("price_status", $("select[name='price_status']").val());
			grid.setAjaxParam("asin_status", $("select[name='asin_status']").val());
			grid.setAjaxParam("coupon_than", $("select[name='coupon_than']").val());
			grid.setAjaxParam("coupon_type", $("select[name='coupon_type']").val());
			grid.setAjaxParam("item_status", $("select[name='item_status']").val());
			grid.setAjaxParam("bgbu", $("select[name='bgbu']").val());
			grid.setAjaxParam("site", $("select[name='site[]']").val());
			grid.setAjaxParam("coupon_value", $("input[name='coupon_value']").val());

            grid.setAjaxParam("post_status", $("select[name='post_status']").val());
            grid.setAjaxParam("post_type", $("select[name='post_type']").val());

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
					<?php if(Auth::user()->can(['asin-rating-export'])){ ?>
					buttons: [
                        { extend: 'csv', className: 'btn purple btn-outline ',filename:'stars' }
                    ],
					<?php }else{?>
											
					buttons: [],
					
					<?php } ?>
					"aoColumnDefs": [ { "bSortable": false, "aTargets": [0,1,2,4,13,14,15,16,17,18,19,20 ] }],
					 "order": [
                        [8, "asc"]
                    ],
                    // scroller extension: http://datatables.net/extensions/scroller/
                    // scrollY:        500,
                    // scrollX:        true,
					

					// fixedColumns:   {
					// 	leftColumns:6,
					// 	rightColumns: 1
					// },
                    "ajax": {
                        "url": "{{ url('star/get')}}", // ajax source
                    },
					//"dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
					"dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
                }


            });
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

	// $('.editAction').on('click',function(){
	$('#datatable_ajax_asin').on('click','.editAction',function(){
	    //获取值
	    var asin = $(this).attr('data-asin');
	    var domain = $(this).attr('data-domain');
        var postStatus = $(this).attr('data-postStatus');
        var postType = $(this).attr('data-postType')

		//赋值值
	    $('#post-asin').val(asin);
        $('#post-domain').val(domain);
        $('#post-status').val(postStatus);
        $('#post-type').val(postType);
        // var obj = $(this).parent();
        var obj = $(this).parent().parent();
        var index = $(".DTFC_RightBodyWrapper table tbody tr").index(obj);
        console.log(index);

        art.dialog({
            id: 'art_edit',
            title: 'edit_'+asin+'_'+domain,
            content: document.getElementById('edit-content'),
            okVal: 'Submit',
            lock:true,
            ok: function () {
                this.title('In the submission…');
                var data = $("#edit-form").serialize();
                $.ajax({
                    type: 'post',
                    url: '/star/updatePost',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        if(res){
							// $("#data_search").trigger("click");
							var statusText = $('#post-status option:selected').text();
                            var typeText = $('#post-type option:selected').text();
                            console.log($('.DTFC_LeftBodyWrapper table tbody tr').eq(index).find('td').html());
							$('.DTFC_LeftBodyWrapper table tbody tr').eq(index).find('td').eq(1).text(typeText);
                            $('.DTFC_LeftBodyWrapper table tbody tr').eq(index).find('td').eq(2).text(statusText);

                            //动态改变已修改的值，不用重新加载数据
                            obj.find('a').attr('data-postStatus',$('#post-status option:selected').val());
                            obj.find('a').attr('data-postType',$('#post-type option:selected').val());
							//
                           	// obj.prev().text($('#post-type option:selected').text());
                            // obj.prev().prev().text($('#post-status option:selected').text());
                            toastr.success('Saved !');
                        }else{
                            //编辑失败
                            alert('Failed');
                        }
                    }
                });
            },
            cancel: true,
            cancelVal:'Cancel'
        });
        return false;
	})

	
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

