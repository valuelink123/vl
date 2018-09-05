@extends('layouts.layout')
@section('label', 'Email Details')
@section('content')
<style>
  .ui-autocomplete {
    max-height: 300px;
	z-index:9999;
    overflow-y: auto;
    /* 防止水平滚动条 */
    overflow-x: hidden;
  }
</style>
<script>
  $(function() {
    $( "#tags" ).autocomplete({
      source: "/template/ajax/get",
      minLength: 1,
      select: function( event, ui ) {
	  	if(ui.item){
		
		   var title = ui.item.title;
		   var desc = ui.item.desc;
		   rename = new RegExp("{CUSTOMER_NAME}","g"); 
		   remail = new RegExp("{CUSTOMER_EMAIL}","g"); 
		   retitle = new RegExp("{EMAIL_TITLE}","g"); 
		   title = title.replace(rename, "{{array_get($email,'from_name')}}");
		   title = title.replace(remail, "{{array_get($email,'to_address')}}");
		   title = title.replace(retitle, "{{array_get($email,'subject')}}");
		   desc = desc.replace(rename, "{{array_get($email,'from_name')}}");
		   desc = desc.replace(remail, "{{array_get($email,'to_address')}}");
		   desc = desc.replace(retitle, "{{array_get($email,'subject')}}");
		   $( "#subject" ).val(title);
           var ue = UE.getEditor('valuelink_amzmessage_container');
		   ue.ready(function() {
				ue.setContent(desc);
		   });
		   
		}	
      }
    });
	$("#fileupload").submit(function(e){
	  if($('#account_type').val()!='Amazon') return true;
	  
	  var ue = UE.getEditor('valuelink_amzmessage_container');
	  var str = ue.getContent();
	  var forbidwords = {!!getForbidWords()!!};
	  var haveforbidwords = '';
	  for(var j = 0,len = forbidwords.length; j < len; j++){
		 var reg = eval("/"+forbidwords[j]+"/ig");
   		 if(reg.test(str)){
			haveforbidwords = haveforbidwords + forbidwords[j] + ' ; ' ;
		 }
	  }
	  
	  if(haveforbidwords){
	  	
	  	 bootbox.dialog({
			message: "Your submission contains sensitive words : ( "+haveforbidwords+" ) , Please resubmit it after revision",
			title: "Error",
				buttons: {
					main: {
						label: "Return To Edit",
						className: "blue"
					}
				}
		});
		return false;
	  }
	  
	  if($('#warn').val()!=1){
	  	var havewarnwords = '';	
		  var warnwords = {!!getWarnWords()!!};
		  for(var j = 0,len = warnwords.length; j < len; j++){
			 var reg = eval("/"+warnwords[j]+"/ig");
			 if(reg.test(str)){
				havewarnwords = havewarnwords + warnwords[j] + ' ; ' ;
			 }
		  }
		  if(havewarnwords){
		   bootbox.dialog({
				message: "Your submission contains sensitive words : ( "+havewarnwords+" ) , Please resubmit it after revision",
				title: "Warning",
				buttons: {
					danger: {
						label: "Continue Submit",
						className: "red",
						callback: function() {
							$('#warn').val(1);
							$('#fileupload').submit();
						}
					},
					main: {
						label: "Return To Edit",
						className: "blue"
					}
				}
			});
			return false;
		}
	  }
	  return true;		  
	});
  });
  </script>
    <div class="row">
        <div class="col-md-12">
<div class="portlet light portlet-fit bordered">
    <div class="portlet-title">
        <div class="caption">
            <i class="icon-microphone font-green"></i>
            <span class="caption-subject bold font-green"> Email Details</span>
            <span class="caption-helper">The mail history of your received.</span>
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
        <div class="tabbable-line">
            <ul class="nav nav-tabs ">
                <li class="active">
                    <a href="#tab_1" data-toggle="tab" aria-expanded="true"> Email Details</a>
                </li>
                <li class="">
                    <a href="#tab_2" data-toggle="tab" aria-expanded="false"> Amazon Order Info </a>
                </li>
                <li class="">
                    <a href="#tab_3" data-toggle="tab" aria-expanded="false"> Compose </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
				<div style="text-align: center;">
                    <?php
					if(array_get($email,'mark')) echo '<span class="btn btn-circle btn-danger">'.$email['mark'].'</span> ';
                    if(array_get($email,'sku')) echo '<span class="btn btn-circle btn-primary">'.$email['sku'].'</span> ';
					if(array_get($email,'asin')) echo '<span class="btn btn-circle btn-primary">'.$email['asin'].'</span> ';
                    if(array_get($email,'etype')) echo '<span class="btn btn-circle btn-danger">'.$email['etype'].'</span> ';
                    if(array_get($email,'remark')) echo '<span class="btn btn-circle btn-info">'.$email['remark'].'</span> ';
                    if(array_get($email,'reply')==0) echo '<span class="btn btn-circle red">Need reply</span>';
                    if(array_get($email,'reply')==1) echo '<span class="btn btn-circle yellow">Do not need to reply</span>';
                    if(array_get($email,'reply')==2) echo '<span class="btn btn-circle green">Replied</span>';
                    ?>
                    </div>
                    <BR>
                    <div class="mt-timeline-2">

                        <ul class="mt-container">


                        <?php foreach($email_history as $s_email){ ?>


                        <?php if(isset($s_email['mail_id'])){ ?>
                        <!--接收-->
                            <li class="mt-item">
                                <div class="mt-timeline-icon bg-red bg-font-red border-grey-steel" style="left:5%;">
                                    <i class="icon-action-redo"></i>
                                </div>
                                <div class="mt-timeline-content" style="width:95%;">
                                    <div class="mt-content-container" style="margin-right: 0px;margin-left:12%;">
                                        <div class="mt-title" style="float:left;text-align:left;">
                                            <h3 class="mt-content-title">{{$s_email['subject']}}</h3>
                                        </div>
                                        <div class="mt-author" style="float:right;text-align:right">
                                            <div class="mt-author-name" style="text-align:right">
                                                <span class="font-red-madison" >From : {{$s_email['from_name']}}  < {{$s_email['from_address']}} ></span>
                                            </div>
                                            <div class="mt-author-name" style="text-align:right">
                                                <span class="font-blue-madison" >To : {{$accounts[strtolower($s_email['to_address'])]}} < {{$s_email['to_address']}} ></span>
                                            </div>
                                            <div class="mt-author-notes font-grey-mint" style="text-align:right">{{$s_email['date']}} <span class="label label-sm label-danger">{{array_get($users,$s_email['user_id'])}}</span></div>
                                        </div>
                                        <div class="mt-content border-grey-salt">
                                            <?php
                                            if($s_email['text_html']){
                                                $config = array('indent' => TRUE,
                                                    'output-xhtml' => TRUE,
                                                    'wrap' => 200);

                                                $tidy = tidy_parse_string($s_email['text_html'], $config, 'UTF8');

                                                $tidy->cleanRepair();
                                                echo $tidy;

                                            }else{
                                                echo '<pre>'.htmlspecialchars($s_email['text_plain']).'</pre>';
                                            }
                                            ?>

                                            <BR>
                                            <?php if($s_email['attachs']){
                                                $attachs = unserialize($s_email['attachs']);
                                                foreach($attachs as $attach){
                                                    $name = basename($attach);
                                                    echo '<a href="'.$attach.'" target="_blank" class="btn btn-circle btn-outline green-jungle">'.$name.'</a>';
                                                }

                                            }?>
                                        </div>
                                    </div>
                                </div>

                            </li>
                            <!--接收-->
                        <?php }else{ ?>
                        <!--发送-->
                            <li class="mt-item">
                                <div class="mt-timeline-icon bg-green-jungle bg-font-green-jungle border-grey-steel" style="left:95%;">
                                    <i class="icon-action-undo"></i>
                                </div>
                                <div class="mt-timeline-content" style="width:95%;left:5%;">
                                    <div class="mt-content-container " style="margin-right: 12%;margin-left:0;">
                                        <div class="mt-title" style="float:right;text-align:right;">
                                            <h3 class="mt-content-title">{{$s_email['subject']}}</h3>
                                        </div>
                                        <div class="mt-author" style="float:left;text-align:left">
                                            <div class="mt-author-name" style="text-align:left">
                                                <span class="font-red-madison" >From : {{$accounts[strtolower($s_email['from_address'])]}} < {{$s_email['from_address']}} ></span>
                                            </div>
                                            <div class="mt-author-name" style="text-align:left">
                                                <span href="javascript:;" class="font-blue-madison" >To : {{$s_email['to_address']}}</span>
                                            </div>
                                            <div class="mt-author-notes font-grey-mint" style="text-align:left">{{$s_email['date']}} <span class="label label-sm label-danger">{{array_get($users,$s_email['user_id'])}}</span></div>
                                        </div>

                                        <div class="mt-content border-grey-steel">
                                    <span class="btn btn-circle <?php echo ($s_email['send_date'])?'green':'red';?>">
                                        <?php
                                        echo $s_email['status'];
										if($s_email['send_date']) echo ' at '.$s_email['send_date'];
									
                                        if($s_email['error']) echo $s_email['error'];
                                        ?>
                                    </span>
                                            <BR>
                                            <?php
                                            $config = array('indent' => TRUE,
                                                'output-xhtml' => TRUE,
                                                'wrap' => 200);

                                            $tidy = tidy_parse_string($s_email['text_html'], $config, 'UTF8');

                                            $tidy->cleanRepair();
                                            echo $tidy;
                                            ?>
                                            <BR>
                                            <?php if($s_email['attachs']){
                                                $attachs = unserialize($s_email['attachs']);
                                                foreach($attachs as $attach){
                                                    $name = basename($attach);
                                                    echo '<a href="'.$attach.'" target="_blank" class="btn btn-circle btn-outline green-jungle">'.$name.'</a>';
                                                }

                                            }?>
                                        </div>


                                    </div>
                                </div>
                            </li>
                            <!--发送-->

                            <?php } ?>


                            <?php } ?>




                        </ul>
                    </div>
                </div>


                <div class="tab-pane" id="tab_2">
                    <?php
                    if(isset($order->AmazonOrderId)){?>
                    <div class="invoice-content-2 bordered">
                        <div class="row invoice-head">
                            <div class="col-md-7 col-xs-6">
                                <div class="invoice-logo">
                                    <h1 class="uppercase">{{$order->AmazonOrderId}}</h1>
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
                                <p class="invoice-desc">{{$order->SellerId}}</p>
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
                        <div class="row">
                            <div class="col-xs-12">
                                <a class="btn btn-lg green-haze hidden-print uppercase print-btn" onclick="javascript:window.print();">Print</a>
                            </div>
                        </div>
                    </div>
                       <?php }else{
                            echo "Can not match or find order";

                        } ?>
                </div>


                <div class="tab-pane" id="tab_3">
                    <div class="col-xs-10">
                        <form id="fileupload" action="{{ url('send') }}" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}
							<input type="hidden" name="warn" id="warn" value="0">
							<input type="hidden" name="account_type" id="account_type" value="{{$account_type}}">
                            <input type="hidden" name="from_address" id="from_address" value="{{$email['from_address']}}">
                            <input type="hidden" name="to_address" id="to_address" value="{{$email['to_address']}}">
                            <input type="hidden" name="inbox_id" id="inbox_id" value="{{$email['inbox_id']}}">
                            <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
							<input type="hidden" name="sendbox_id" id="sendbox_id" value="{{($email['status']=='Draft')?$email['id']:0}}">
                            <div class="form-group">
                            <label>Templates</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                <input type="text" class="form-control" id="tags" >
                            </div>
                        </div>
						<div class="form-group">
                                <label>Subject</label>
                                <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                    <input type="text" class="form-control" name="subject" id="subject" value="{{$email['subject']}}" >
                                </div>
                            </div>

                            <div class="form-group" >
                                    @include('UEditor::head')

                                    <!-- 加载编辑器的容器 -->
                                    <script id="valuelink_amzmessage_container" name="content" type="text/plain">
									<?php echo ($email['status']=='Draft')?$email['text_html']:''; ?>
									</script>
                                    <!-- 实例化编辑器 -->
                                    <script type="text/javascript">
                                        var ue = UE.getEditor('valuelink_amzmessage_container');
                                        ue.ready(function() {
                                            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                        });
                                </script>
                                        <div style="clear:both;"></div>
                            </div>
                            <div class="form-group">
                                    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
                                    <div class="row fileupload-buttonbar">
                                        <div class="col-lg-7">
                                            <!-- The fileinput-button span is used to style the file input field as button -->
                                            <span class="btn green fileinput-button">
                                                <i class="fa fa-plus"></i>
                                                <span> Add files... </span>
                                                <input type="file" name="files[]" multiple=""> </span>
                                            <button type="submit" class="btn blue start">
                                                <i class="fa fa-upload"></i>
                                                <span> Start upload </span>
                                            </button>
                                            <button type="reset" class="btn warning cancel">
                                                <i class="fa fa-ban-circle"></i>
                                                <span> Cancel upload </span>
                                            </button>

                                            <button type="button" class="btn red delete">
                                                <i class="fa fa-trash"></i>
                                                <span> Delete </span>
                                            </button>
                                            <input type="checkbox" class="toggle">
                                            <!-- The global file processing state -->
                                            <span class="fileupload-process"> </span>
                                        </div>
                                        <!-- The global progress information -->
                                        <div class="col-lg-5 fileupload-progress fade">
                                            <!-- The global progress bar -->
                                            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar progress-bar-success" style="width:0%;"> </div>
                                            </div>
                                            <!-- The extended global progress information -->
                                            <div class="progress-extended"> &nbsp; </div>
                                        </div>
                                    </div>
                                    <!-- The table listing the files available for upload/download -->
                                    <table role="presentation" class="table table-striped clearfix">
                                        <tbody class="files">
										<?php
										if($email['attachs'] && $email['status']=='Draft') {
											$attachs = unserialize($email['attachs']);
											foreach($attachs  as $attach){
											$name = basename($attach);
											if(file_exists(public_path().$attach)){
												$filesize = round(filesize(public_path().$attach)/1028,2).'KB';
											}else{
												$filesize = 0;
											}
											
											$url = $attach;
											$deleteUrl = url('send/deletefile/' . base64_encode($attach));
										?>
										
										<tr class="template-download fade in">
											<td>
												<p class="name">
													<a href="{{$url}}" title="{{$name}}" download="{{$name}}" >{{$name}}</a>
														<input type="hidden" name="fileid[]" value="{{$url}}">
			 </td>
											<td>
												<span class="size">{{$filesize}}</span>
											</td>
											<td>
												<button class="btn red delete btn-sm" data-type="get" data-url="{{$deleteUrl}}" >
													<i class="fa fa-trash-o"></i>
													<span>Delete</span>
												</button>
												<input type="checkbox" name="delete" value="1" class="toggle"></td>
										</tr>
										<?php }} ?>
										</tbody>
                                    </table>
                                <div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
                                    <div class="slides"> </div>
                                    <h3 class="title"></h3>
                                    <a class="prev"> ‹ </a>
                                    <a class="next"> › </a>
                                    <a class="close white"> </a>
                                    <a class="play-pause"> </a>
                                    <ol class="indicator"> </ol>
                                </div>
                                <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
                                <script id="template-upload" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
                            <tr class="template-upload fade">
                                <td>
                                    <p class="name">{%=file.name%}</p>
                                    <strong class="error text-danger label label-danger"></strong>
                                </td>
                                <td>
                                    <p class="size">Processing...</p>
                                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                        <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                                    </div>
                                </td>
                                <td> {% if (!i && !o.options.autoUpload) { %}
                                    <button class="btn blue start" disabled>
                                        <i class="fa fa-upload"></i>
                                        <span>Start</span>
                                    </button> {% } %} {% if (!i) { %}
                                    <button class="btn red cancel">
                                        <i class="fa fa-ban"></i>
                                        <span>Cancel</span>
                                    </button> {% } %} </td>
                            </tr> {% } %} </script>
                                <!-- The template to display files available for download -->
							
							
                                <script id="template-download" type="text/x-tmpl"> {% 
								for (var i=0, file; file=o.files[i]; i++) { %}
                            <tr class="template-download fade">
                                <td>
                                    <p class="name"> {% if (file.url) { %}
                                        <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl? 'data-gallery': ''%}>{%=file.name%}</a> {% } else { %}
                                        <span>{%=file.name%}</span> {% } %}
                                        {% if (file.name) { %}
                                            <input type="hidden" name="fileid[]" value="{%=file.url%}">
                                        {% } %}

                                        </p> {% if (file.error) { %}
                                    <div>
                                        <span class="label label-danger">Error</span> {%=file.error%}</div> {% } %} </td>
                                <td>
                                    <span class="size">{%=o.formatFileSize(file.size)%}</span>
                                </td>
                                <td> {% if (file.deleteUrl) { %}
                                    <button class="btn red delete btn-sm" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}" {% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}' {% } %}>
                                        <i class="fa fa-trash-o"></i>
                                        <span>Delete</span>
                                    </button>
                                    <input type="checkbox" name="delete" value="1" class="toggle"> {% } else { %}
                                    <button class="btn yellow cancel btn-sm">
                                        <i class="fa fa-ban"></i>
                                        <span>Cancel</span>
                                    </button> {% } %} </td>
                            </tr> {% } %} </script>
                                <div style="clear:both;"></div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-4 col-md-8">
                                        <button type="submit" class="btn blue">Submit</button>
                                        <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
										<button type="submit" class="btn yellow" name='asDraft' value="1">Save as Draft</button>
                                    </div>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                        </form>
                        <div style="clear:both;"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>


               

            </div>
        </div>


    </div>
</div>
        </div>
		 <div style="clear:both;"></div></div>

@endsection