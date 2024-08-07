@extends('layouts.layout')
@section('label', 'Call Details')
@section('content')
<style>
  .ui-autocomplete {
    max-height: 300px;
	z-index:9999;
    overflow-y: auto;
    /* 防止水平滚动条 */
    overflow-x: hidden;
  }
  .styleclass{
      margin-top:-34px;
      margin-left:100px;
  }
</style>
<script>
  $(function() {
    
	$("#rebindorder").click(function(){
	  $.post("/saporder/get",
	  {
	  	"_token":"{{csrf_token()}}",
		"inboxid":0,
		"sellerid":$("#rebindordersellerid").val(),
		"orderid":$("#rebindorderid").val()
	  },
	  function(data,status){
	  	if(status=='success'){
	  		var redata = JSON.parse(data);
			if(redata.result==1){
				toastr.success(redata.message);
				if(redata.sellerid) $("select[name='rebindordersellerid']").val(redata.sellerid);
				if(redata.buyeremail) {
                    $("input[name='buyer_email']").val(redata.buyeremail);
                    $('.createEmail').attr('href','/send/create?to_address='+redata.buyeremail);
                }
				if(redata.orderhtml) $("#tab_2").html(redata.orderhtml);

                if(redata.productBasicInfo){
                    $("#tab_3 input[name='sku']").val(redata.productBasicInfo.SellerSKU);
                    $("#tab_3 input[name='asin']").val(redata.productBasicInfo.asin);
                    $("#tab_3 input[name='item_no']").val(redata.productBasicInfo.item_no);
                }
			}else{
				toastr.error(redata.message);
			}	
		}

	  });
	});
      $("#rebindorder").trigger("click");
	
	
  });
  </script>
  
  
    <div class="row">
        <div class="col-md-12">
<div class="portlet light portlet-fit bordered">
	
    <div class="portlet-title">
        <div class="caption">
            <i class="icon-microphone font-green"></i>
            <span class="caption-subject bold font-green"> Call Details</span>
            <span class="caption-helper">The Call history of your received.</span>
        </div>

    </div>
    <div class="portlet-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
		<form id="phone_form" action="{{ url('phone/'.$phone['id']) }}" method="POST" >
		{{ csrf_field() }}
                    {{ method_field('PUT') }}
        <div class="tabbable-line">
            <ul class="nav nav-tabs ">
                <li class="active">
                    <a href="#tab_1" data-toggle="tab" aria-expanded="true"> Call Details</a>
                </li>
                <li class="">
                    <a href="#tab_2" data-toggle="tab" aria-expanded="false"> Amazon Order Info </a>
                </li>
   
                <li class="">
                    <a href="#tab_3" data-toggle="tab" aria-expanded="false"> Other Operations </a>
                </li>
				
				<li class="">
                    <a href="#tab_4" data-toggle="tab" aria-expanded="false"> Email History </a>
                </li>
				

				
		
            </ul>
            <div class="tab-content">
			 
                <div class="tab-pane active" id="tab_1">
				
				
				<div class="col-xs-10">

                            <div class="form-group">
                            <label>Call Notes ( caller name, issue, resolution given, etc. )</label>
                            <div class="input-group col-md-6 ">
                                <textarea  name="content" id="content" rows="5" cols="100%" class="form-control "><?php echo $phone['content'];?></textarea>
                            </div>
                        </div>
						<div class="form-group">
                                <label>Call Number</label>
                                <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                    <input type="text" class="form-control" name="phone" id="phone" value="{{$phone['phone']}}" >
                                </div>
                            </div>
							
							
							<div class="form-group">
                                <label>Amazon Order ID</label>
                                
                                <div class="row" style="margin-bottom:50px;">
	
						<div class="col-md-2">
						
													<select id="rebindordersellerid" class="form-control" name="rebindordersellerid">
													<option value="">Auto Match SellerID</option>
													@foreach ($sellerids as $id=>$name)
														<option value="{{$id}}" <?php if($phone['seller_id']==$id) echo 'selected';?>>{{$name}}</option>
													@endforeach
													</select> 		
													
						</div>

                        <div class="col-md-4">
						<div class="input-group">
                                                            
													
															
                                                                <input id="rebindorderid" class="form-control" type="text" name="rebindorderid" placeholder="Amazon Order ID" value="{{$phone['amazon_order_id']}}"> 
                                                            <span class="input-group-btn">
                                                                <button id="rebindorder" class="btn btn-success" type="button">
                                                                    Get Order</button>
                                                            </span>
                                                        </div>
                            
                        </div>
                        
                        
                  
                                </div>
                            </div>
							
							
							
							<div class="form-group">
                                <label>Buyer Email</label>
                                <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-email"></i>
                                </span>
                                    <input type="text" class="form-control" name="buyer_email" id="buyer_email" value ="{{$phone['buyer_email']}}" />
                                </div>
                            </div>
				
							<div style="clear:both;"></div>
						
                </div>
				<div style="clear:both;"></div>
				</div>

                <div class="tab-pane" id="tab_2">
                    <?php
                    if(isset($order->AmazonOrderId)){?>
                    <div class="invoice-content-2 bordered">
                        <div class="row invoice-head">
                            <div class="col-md-7 col-xs-6">
                                <div class="invoice-logo">
                                    <h1 class="uppercase">{{$order->AmazonOrderId}}  ( {{array_get($sellerids,$order->SellerId)}} )</h1>
                                    Buyer Email : {{$order->BuyerEmail}}<BR>
                                    Buyer Name : {{$order->BuyerName}}<BR>
                                    PurchaseDate : {{$order->PurchaseDate}}
                                </div>
                            </div>
                            <div class="col-md-5 col-xs-6">
                                <div class="company-address">
                                    <span class="bold ">{{$order->Name}}</span>
                                    <br> {{$order->AddressLine1}}
                                    <br> {{$order->AddressLine2}}
                                    <br> {{$order->AddressLine3}}
                                    <br> {{$order->City}} {{$order->StateOrRegion}} {{$order->CountryCode}}
                                    <br> {{$order->PostalCode}}
                                </div>
                            </div>
                        </div>
                            <BR><BR>
                        <div class="row invoice-cust-add">
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Seller ID</h4>
                                <p class="invoice-desc">{{$order->SellerId}}   </p>
                            </div>
                            <div class="col-xs-3">
                                <h4 class="invoice-title ">Site</h4>
                                <p class="invoice-desc">{{$order->SalesChannel}}</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Fulfillment Channel</h4>
                                <p class="invoice-desc">{{$order->FulfillmentChannel}}</p>
                            </div>
                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Ship Service Level</h4>
                                <p class="invoice-desc">{{$order->ShipServiceLevel}}</p>
                            </div>

                            <div class="col-xs-2">
                                <h4 class="invoice-title ">Status</h4>
                                <p class="invoice-desc">{{$order->OrderStatus}}</p>
                            </div>


                        </div>
                        <BR><BR>
                        <div class="row invoice-body">
                            <div class="col-xs-12 table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="invoice-title uppercase">Description</th>
                                        <th class="invoice-title uppercase text-center">Qty</th>
                                        <th class="invoice-title uppercase text-center">Price</th>
                                        <th class="invoice-title uppercase text-center">Shipping</th>
                                        <th class="invoice-title uppercase text-center">Promotion</th>
										<th class="invoice-title uppercase text-center">Tax</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach($order->item as $item){ ?>
                                    <tr>
                                        <td>
                                            <h4>{{$item->ASIN}} ( {{$item->SellerSKU}} )</h4>
                                            <p> {{$item->Title}} </p>
                                        </td>
                                        <td class="text-center sbold">{{$item->QuantityOrdered}}</td>
                                        <td class="text-center sbold">{{round($item->ItemPriceAmount/$item->QuantityOrdered,2)}}</td>
                                        <td class="text-center sbold">{{round($item->ShippingPriceAmount,2)}} {{($item->ShippingDiscountAmount)?'( -'.round($item->ShippingDiscountAmount,2).' )':''}}</td>
                                        <td class="text-center sbold">{{($item->PromotionDiscountAmount)?'( -'.round($item->PromotionDiscountAmount,2).' )':''}}</td>
										<td class="text-center sbold">{{round($item->ItemTaxAmount,2)}}</td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row invoice-subtotal">
                            <div class="col-xs-6">
                                <h4 class="invoice-title uppercase">Total</h4>
                                <p class="invoice-desc grand-total">{{round($order->Amount,2)}} {{$order->CurrencyCode}}</p>
                            </div>
                        </div>

                    </div>
                       <?php }else{
                            echo "Can not match or find order";
                        } ?>
                </div>



                <div class="tab-pane" id="tab_3">
                    <div class="col-xs-6">
                    <div class="form-group">
                        <label>SKU</label>
                        <div class="input-group ">
                        <span class="input-group-addon">
                            <i class="fa fa-bookmark"></i>
                        </span>
                            <input type="text" class="form-control" name="sku" id="sku" value="{{$phone['sku']}}" >
                        </div>
                    </div>
					
					<div class="form-group">
                        <label>ASIN</label>
                        <div class="input-group ">
                        <span class="input-group-addon">
                            <i class="fa fa-bookmark"></i>
                        </span>
                            <input type="text" class="form-control" name="asin" id="asin" value="{{$phone['asin']}}" >
                        </div>
                    </div>
					
					<div class="form-group">
                        <label>Item NO.</label>
                        <div class="input-group ">
                        <span class="input-group-addon">
                            <i class="fa fa-bookmark"></i>
                        </span>
                            {{--<input type="text" class="form-control" onchange="rItemGroup();" name="item_no" id="item_no" value="{{$phone['item_no']}}" >--}}
                            <input type="text" class="form-control" name="item_no" id="item_no" value="{{$phone['item_no']}}" >
                        </div>
                    </div>
                    {{-- item_group is hidden  --}}
                    <input type="hidden" class="form-control" name="item_group" id="item_group" value="">

                        {{--<div class="form-group">--}}
                        {{--<label>Question Type</label>--}}
                        {{--<div class="input-group ">--}}
                        {{--<span class="input-group-addon">--}}
                            {{--<i class="fa fa-bookmark"></i>--}}
                        {{--</span>--}}
                            {{--<select class="form-control" name="etype" id="etype">--}}
                                {{--<option value="">None</option>--}}
                                {{--@foreach (getEType() as $etype)--}}
                                    {{--<option value="{{$etype}}" @if($etype==$phone['etype']) selected @endif>{{$etype}}</option>--}}
                                {{--@endforeach--}}
                            {{--</select>--}}
                        {{--</div>--}}
                    {{--</div>--}}

                    {{--<div class="form-group">--}}
                        {{--<label>Problem Point</label>--}}
                        {{--<div class="input-group ">--}}
                                {{--<span class="input-group-addon">--}}
                                    {{--<i class="fa fa-bookmark"></i>--}}
                                {{--</span>--}}
                            {{--<input type="text" class="form-control" name="epoint" id="epoint" value="{{$phone['epoint']}}" >--}}
                        {{--</div>--}}
                    {{--</div>--}}

                    <div class="form-group">
                        <label>Question Type</label>
                        <div class="form-inline">
                            <select id="linkage1" name="linkage1" class="form-control city-select" data-selected="{{$phone['linkage1']}}" data-parent_id="28"></select>
                            <select id="linkage2" name="linkage2" class="form-control city-select" data-selected="{{$phone['linkage2']}}" data-parent_id="{{$phone['linkage1']}}"></select>
                            <select id="linkage3" name="linkage3" class="form-control city-select" data-selected="{{$phone['linkage3']}}" data-parent_id="{{$phone['linkage2']}}"></select>
                            <select id="linkage4" name="linkage4" class="form-control city-select" data-selected="{{$phone['linkage4']}}" data-parent_id="{{$phone['linkage3']}}"></select>
                            <select id="linkage5" name="linkage5" class="form-control city-select" data-selected="{{$phone['linkage5']}}" data-parent_id="{{$phone['linkage4']}}"></select>
                        </div>
                    </div>
                    <script>
                        var city=[],cityName=[];
                        $.fn.city = function (opt) {
                            var $id = $(this),
                                options = $.extend({
                                    url:"{{ url('inbox/getCategoryJson?parent_id=') }}",
                                    /*当前ID，设置选中状态*/
                                    selected: null,
                                    /*上级栏目ID*/
                                    parent_id: null,
                                    /*主键ID名称*/
                                    valueName: "id",
                                    /*名称*/
                                    textName: "category_name",
                                    /*默认名称*/
                                    defaultName: "None",
                                    /*下级对象ID*/
                                    nextID: null}, opt),selected,_tmp;
                            if(options.parent_id==null){
                                _tmp=$id.data('parent_id');
                                if(_tmp!==undefined){
                                    options.parent_id=_tmp;
                                }
                            }
                            //初始化层
                            this.init = function () {
                                if($.inArray($id.attr('id'),cityName)==-1){
                                    cityName.push($id.attr('id'));
                                }
                                if(!options.selected){
                                    options.selected=$id.data('selected');
                                }
                                $id.append(format(get(options.parent_id)));
                            };
                            function get(id) {
                                if (id !== null && !city[id]) {
                                    getData(id);
                                    return city[id];
                                }else if (id !== null && city[id]) {
                                    return city[id];
                                }
                                return [];
                            }

                            function getData(id) {
                                $.ajax({
                                    url: options.url+ id,
                                    type: 'GET',
                                    async: false,
                                    dataType:'json',
                                    success: function (d) {
                                        if (d.status == 'y') {
                                            city[id] = d.data;
                                        }
                                    }
                                });
                            }

                            function format(d) {
                                var _arr = [], r, selected = '';
                                if (options.defaultName !== null) _arr.push('<option value="999999999">' + options.defaultName + '</option>');
                                if ($.isArray(d)) for (var v in d) {
                                    r = null;
                                    r = d[v];
                                    selected = '';
                                    if (options.selected && options.selected == (r[options.valueName])) {
                                        selected = 'selected';
                                    }
                                    _arr.push('<option value="' + r[options.valueName] + '" ' + selected + '>' + r[options.textName] + '</option>');
                                }
                                return _arr.join('');
                            }

                            this.each(function () {
                                options.nextID && $id.on('change', function () {
                                    var $this = $('#' + options.nextID),id=$(this).attr('id'),i=$.inArray(id,cityName);
                                    $this.html(format(get($(this).val())));
                                    if ($.isArray(cityName)) for (var v in cityName) {
                                        if(v>(i+1)){
                                            $('#'+cityName[v]).html(format());
                                        }
                                    }
                                });
                            });
                            this.init();
                        };
                        $(function() {

                            $('#linkage1').city({nextID:'linkage2'});
                            $('#linkage2').city({nextID:'linkage3'});
                            $('#linkage3').city({nextID:'linkage4'});
                            $('#linkage4').city({nextID:'linkage5'});
                            $('#linkage5').city();

                            var sku = $('#sku').val();

                            $.ajax({
                                url: "{{ url('inbox/getItem') }}",
                                method: 'POST',
                                cache: false,
                                dataType:'json',
                                data: {sku: sku},
                                success: function (data) {
                                    if(data.code == 200){
                                        $('#item_no').val(data.data[0].item_no);
                                        $('#item_group').val(data.data[0].item_group);
                                    }
                                }
                            });
                        });

                        function rItemGroup(){
                            var item_no = $('#item_no').val();

                            $.ajax({
                                url: "{{ url('inbox/getItemGroup') }}",
                                method: 'POST',
                                cache: false,
                                dataType:'json',
                                data: {item_no: item_no},
                                success: function (data) {
                                    if(data.code == 200){
                                        $('#item_group').val(data.data[0].item_group);
                                    }else{
                                        $('#item_group').val('');
                                    }
                                }
                            });
                        }
                    </script>
					
                    <div class="form-group">
                        <label>Add Remark</label>
                        <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                            <input type="text" class="form-control" name="remark" id="remark" value="{{$phone['remark']}}" >
                        </div>
                    </div>
					
					
			
																	
                    

                        

                        <div style="clear:both;"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
				<div class="tab-pane" id="tab_4">
						<div class="table-container">
                        
                        <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_all">
                            <thead>
                            <tr role="row" class="heading">
                                <th width="2%">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input type="checkbox" class="group-checkable" data-set="#datatable_ajax_all .checkboxes" />
                                        <span></span>
                                    </label>
                                </th>
                                <th width="30%"> From </th>
                                <th width="20%"> To </th>
                                <th width="30%"> Subject </th>
                                <th width="15%"> Date </th>

                                <th width="5%"> Action </th>
                            </tr>
                            <tr role="row" class="filter">
                                <td> </td>
                                <td>
								<div class="input-group margin-bottom-5">
                                    <input type="text" class="form-control form-filter input-sm" name="from_address" value="{{$phone['buyer_email']}}">
									</div>
									<div class="input-group ">
									<select name="reply" class="form-control form-filter input-sm">
                                        <option value="">Select Status</option>
                                        <option value="2">Replied</option>
                                        <option value="1">Do not need to reply</option>
                                        <option value="0">Need reply</option>
                                    </select>
									</div>
                                </td>
                                <td>
								<div class="input-group margin-bottom-5">
                                    <input type="text" class="form-control form-filter input-sm" name="to_address">
									
									</div>
									<div class="input-group col-md-6 pull-left">
									<select class="form-control form-filter input-sm  " name="group_id">
                                        <option value="">Group</option>
										@foreach ($groups as $group_id=>$group)
										
											<option value="{{$group_id}}">{{array_get($group,'group_name')}}</option>
											
										@endforeach
                                    </select>
									</div>
									<div class="input-group col-md-6 pull-left">
									<select class="form-control form-filter input-sm " name="user_id">
                                        <option value="">User</option>
										@foreach ($users as $user_id=>$user)
										
											<option value="{{$user_id}}">{{$user}}</option>
											
										@endforeach
                                    </select>
									</div>
                                </td>
                                <td>
								<div class="input-group margin-bottom-5">
                                    <input type="text" class="form-control form-filter input-sm" name="subject">
									</div>
									<div class="input-group ">
									<select class="form-control form-filter input-sm" name="mark">
                                        <option value="">Select...</option>
                                        @foreach (getMarks() as $mark)
                                            <option value="{{$mark}}">{{$mark}}</option>
                                        @endforeach
                                    </select>
									</div>
                                </td>
                                <td>
                                    <div class="input-group date date-picker margin-bottom-5" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="date_from" placeholder="From">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                    <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                        <input type="text" class="form-control form-filter input-sm" readonly name="date_to" placeholder="To">
                                        <span class="input-group-btn">
                                                                    <button class="btn btn-sm default" type="button">
                                                                        <i class="fa fa-calendar"></i>
                                                                    </button>
                                                                </span>
                                    </div>
                                </td>

                                <td>
                                    <div class="margin-bottom-5">
                                        <button class="btn btn-sm green btn-outline filter-submit margin-bottom">
                                            <i class="fa fa-search"></i> Search</button>
                                    </div>
                                    <button class="btn btn-sm red btn-outline filter-cancel">
                                        <i class="fa fa-times"></i> Reset</button>
                                </td>
                            </tr>
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>
				</div>
            </div>
        </div>
				<div class="form-actions" style="margin-top:50px;">
                            <div class="row">
                                <div class="col-md-offset-4 col-md-8">
                                    <button type="submit" class="btn blue btn1">Submit</button>
                                   
                                </div>
                            </div>
                        </div>
		</form>

        <div class="row">
            <div class="col-md-offset-4 col-md-8">
                <div >
                    <a class="createRR" target="_blank" href=""><button class="btn blue btn2 hide" >Create refund and replacement</button></a>
                </div>
                <div class="commonclass styleclass">
                    <a class="createEmail" target="_blank" href="/send/create?to_address={{$phone['buyer_email']}}" ><button class="btn blue btnEmail" >Compose</button></a>
                </div>
            </div>
        </div>

    </div>
</div>
        </div>
		 <div style="clear:both;"></div></div>
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
                src: $("#datatable_ajax_all"),
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
                    "dom": "<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-6 col-sm-12'pli><'col-md-6 col-sm-12'>>",

                    "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                    "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 , 5 ] }],
                    "lengthMenu": [
                        [10, 20, 50],
                        [10, 20, 50] // change per page values here
                    ],
                    "pageLength": 10, // default record count per page
                    "ajax": {
                        "url": "{{ url('inbox/get')}}", // ajax source
                    },
                    "order": [
                        [4, "desc"]
                    ],// set first column as a default sort by asc
                    "createdRow": function( row, data, dataIndex ) {
                        $(row).children('td').eq(1).attr('style', 'text-align: left;word-break: break-all; ')
						$(row).children('td').eq(2).attr('style', 'text-align: left;')
						$(row).children('td').eq(3).attr('style', 'text-align: left;')
						$(row).children('td').eq(4).attr('style', 'text-align: left;')
                    },
                }
            });

            // handle group actionsubmit button click
           

            //grid.setAjaxParam("customActionType", "group_action");

            grid.setAjaxParam("from_address", $("input[name='from_address']").val());
            grid.setAjaxParam("to_address", $("input[name='to_address']").val());
            grid.setAjaxParam("date_from", $("input[name='date_from']").val());
            grid.setAjaxParam("date_to", $("input[name='date_to']").val());
            grid.setAjaxParam("subject", $("input[name='subject']").val());
            grid.setAjaxParam("reply", $("select[name='reply']").val());
			grid.setAjaxParam("remark", $("select[name='remark']").val());
            grid.setAjaxParam("user_id", $("select[name='user_id']").val());
			grid.setAjaxParam("group_id", $("select[name='group_id']").val());
			grid.setAjaxParam("show_all", 'show_all');
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

    $('.nav-tabs li').click(function(){
        var href = $(this).find('a').attr('href');
        $('.form-actions .btn1').removeClass('hide');
        $('.btn2').addClass('hide');
        $('.btnEmail').addClass('hide');
        $('.commonclass').addClass('styleclass');
        if(href=='#tab_2'){
            $('.form-actions .btn1').addClass('hide');
            $('.btn2').removeClass('hide');
            var request_orderid = $('#rebindorderid').val();
            $('.commonclass').removeClass('styleclass');
            $('.createRR').attr('href','/exception/create?request_orderid='+request_orderid);
        }else{
            if(href=='#tab_1'){
                var buyerEmail = $('#buyer_email').val();
                $('.btnEmail').removeClass('hide');
                $('.createEmail').attr('href','/send/create?to_address='+buyerEmail);
            }

        }
    })
});


</script>
@endsection