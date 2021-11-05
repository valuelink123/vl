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
		   title = title.replace(rename, "{{$email['from_name']}}");
		   title = title.replace(remail, "{{$email['from_address']}}");
		   title = title.replace(retitle, "{{$email['subject']}}");
		   desc = desc.replace(rename, "{{$email['from_name']}}");
		   desc = desc.replace(remail, "{{$email['from_address']}}");
		   desc = desc.replace(retitle, "{{$email['subject']}}");
		   $( "#subject" ).val(title);
           var ue = UE.getEditor('valuelink_amzmessage_container');
		   ue.ready(function() {
				ue.setContent(desc);
		   });

		}
      }
    });
	$("#rebindorder").click(function(){
	  $.post("/saporder/get",
	  {
	  	"_token":"{{csrf_token()}}",
		"inboxid":$("#rebindorderinboxid").val(),
		"sellerid":$("#rebindordersellerid").val(),
		"orderid":$("#rebindorderid").val()
	  },
	  function(data,status){
	  	if(status=='success'){
	  		var redata = JSON.parse(data);
			if(redata.result==1){
				toastr.success(redata.message);
				setTimeout(function(){location.reload();},3000);
			}else{
				toastr.error(redata.message);
			}
		}

	  });
	});
	//解绑操作，解绑此封邮件绑定的订单号
      $("#unbindorder").click(function() {
          $.ajax({
              type: 'post',
              url: '/inbox/unbindInboxOrder',
              data: {inboxid: $("#rebindorderinboxid").val()},
              dataType: 'json',
              success: function (res) {
                  var msg = res.msg;
                  if(res.status==1){
                      toastr.success(msg);
                      setTimeout(function(){location.reload();},3000);
                  }else{
                      toastr.error(msg);
                  }
              }
          });
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

      $("#search_user").keyup(function(){
          // $('#user_id optgroup').attr('data-show',0);
          $('#user_id optgroup').hide();
          var search_value = $(this).val().toUpperCase();
          console.log(111);
          console.log(search_value);
          $('#user_id option').each(function (index,element){
              var content = $(element).text().toUpperCase();
              if(content.indexOf(search_value) >= 0 ) {
                  //包含搜索的内容
                  $(this).show();
                  $(this).parent().show();
                  // $(this).parent().attr('data-show',1);
              }else{
                  $(this).hide();
              }
          });
      });

  });
  </script>

  <?php if(count($unread_history)>0){?>
<div class="col-md-12">
                                <!-- BEGIN Portlet PORTLET-->
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-share font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase"> Warning</span>
                                            <span class="caption-helper">There are no reply messages</span>
                                        </div>
                                        <div class="tools">
                                            <a href="" class="collapse" data-original-title="" title=""> </a>
                                            <a href="" class="remove" data-original-title="" title=""> </a>
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible="1" data-rail-color="red" data-handle-color="green">
                                            <ul class="chats">
												 <?php foreach($unread_history as $unread_email){ ?>
                                                <li class="in">
                                                    <img class="avatar" alt="" src="/assets/layouts/layout/img/avatar.png">
                                                    <div class="message">
                                                        <span class="arrow"> </span>
                                                        <a href="javascript:;" class="name"> {{(isset($emailToEncryptedEmail[$unread_email->from_name])?$emailToEncryptedEmail[$unread_email->from_name]:$unread_email->from_name)}}  < @if(isset($emailToEncryptedEmail[$unread_email->from_address])) {{$emailToEncryptedEmail[$unread_email->from_address]}}  @else {{$unread_email->from_address}}  @endif >  </a>
                                                        <span class="datetime"> at {{$unread_email->date}} </span>
                                                        <span class="body" style="font-size:14px;"><a href="{{url('inbox/'.$unread_email->id)}}"> {{$unread_email->subject}} </a> </span>
                                                    </div>
                                                </li>
                                               <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Portlet PORTLET-->
                            </div>
							
							<?php } ?>
							
							
							
							<?php if(count($email_change_log)>0){?>
<div class="col-md-12">
                                <!-- BEGIN Portlet PORTLET-->
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-share font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase"> Email Change Logs</span>
                                        </div>
                                        <div class="tools">
                                            <a href="" class="collapse" data-original-title="" title=""> </a>
                                            <a href="" class="remove" data-original-title="" title=""> </a>
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible="1" data-rail-color="red" data-handle-color="green">
                                            <ul class="chats">
												 <?php foreach($email_change_log as $email_log){ ?>
                                                <li class="in">
                                                    <img class="avatar" alt="" src="/assets/layouts/layout/img/avatar.png">
                                                    <div class="message">
                                                        <span class="arrow"> </span>
                                                        <a href="javascript:;" class="name"> {{array_get($users,$email_log->user_id)}} Change User To {{array_get($users,$email_log->to_user_id)}}   </a>
                                                        <span class="datetime"> at {{$email_log->date}} </span>
                                                        <span class="body" style="font-size:14px;"> {{$email_log->text}}  </span>
                                                    </div>
                                                </li>
                                               <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Portlet PORTLET-->
                            </div>
							
							<?php } ?>
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
                    <a href="#tab_4" data-toggle="tab" aria-expanded="false"> Other Operations </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <div class="col-xs-9">
                        <form id="fileupload" action="{{ url('send') }}" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <input type="hidden" name="warn" id="warn" value="0">
                            <input type="hidden" name="account_type" id="account_type" value="{{$account_type}}">
                            <input type="hidden" name="from_address" id="from_address" value="{{$email['to_address']}}">
                            <input type="hidden" name="to_address" id="to_address" value="{{$email['from_address']}}">
                            <input type="hidden" name="inbox_id" id="inbox_id" value="{{$email['id']}}">
                            <input type="hidden" name="sendbox_id" id="sendbox_id" value="{{array_get($email,'draftId',0)}}">
                            <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
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
                                    <input type="text" class="form-control" name="subject" id="subject" value="{{array_get($email,'draftSubject','Re:'.$email['subject'])}}" >
                                </div>
                            </div>
                            <div class="form-group">
                            <label class="control-label">Specify time to send ( UTC Time )</label>
                            <div class="input-group">
                                <div class="input-group date form_datetime">
                                    <input type="text" size="16" name="plan_date" readonly class="form-control">
                                    <span class="input-group-btn">
                                        <button class="btn default date-clear" type="button">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <button class="btn default date-set" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>

                        </div>
                            <div class="form-group" >
                                    @include('UEditor::head')

                                    <!-- 加载编辑器的容器 -->
                                    <script id="valuelink_amzmessage_container" name="content" type="text/plain">					<?php echo array_get($email,'draftHtml',''); ?></script>
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
                                        if(array_get($email,'draftAttachs')) {
                                            $attachs = unserialize($email['draftAttachs']);
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
                                    <strong class="error label label-danger"></strong>
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
                                <script id="template-download" type="text/x-tmpl"> {% for (var i=0, file; file=o.files[i]; i++) { %}
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
                        <div class="caption" style="margin:50px 5%;">
                            <i class="icon-settings font-red-mint"></i>
                            <span class="caption-subject font-red-mint bold uppercase">Email History</span>
                        </div>
                        <div style="text-align: center;">
                            <?php
                            if($email['mark']) echo '<span class="btn btn-circle btn-danger">'.$email['mark'].'</span> ';
                            if($email['sku']) echo '<span class="btn btn-circle btn-primary">'.$email['sku'].'</span> ';
                            if($email['asin']) echo '<span class="btn btn-circle btn-primary">'.$email['asin'].'</span> ';
                            if($email['etype']) echo '<span class="btn btn-circle btn-danger">'.$email['etype'].'</span> ';
                            if($email['remark']) echo '<span class="btn btn-circle btn-info">'.$email['remark'].'</span> ';
                            if($email['reply']==0) echo '<span class="btn btn-circle red">Need reply</span>';
                            if($email['reply']==1) echo '<span class="btn btn-circle yellow">Do not need to reply</span>';
                            if($email['reply']==2) echo '<span class="btn btn-circle green">Replied</span>';
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
                                                    <span class="font-red-madison" >From : {{(isset($emailToEncryptedEmail[$s_email['from_name']])?$emailToEncryptedEmail[$s_email['from_name']]:$s_email['from_name'])}}  < @if(isset($emailToEncryptedEmail[$s_email['from_address']])) {{$emailToEncryptedEmail[$s_email['from_address']]}}  @else {{$s_email['from_address']}}  @endif></span>{!! $s_email['fromAddressRsgStatusHtml'] !!}
                                                </div>
                                                <div class="mt-author-name" style="text-align:right">
                                                    <span class="font-blue-madison" >To : @if(isset($accounts[strtolower($s_email['to_address'])])) {{$accounts[strtolower($s_email['to_address'])]}} @endif < {{$s_email['to_address']}} ></span>{!! $s_email['toAddressRsgStatusHtml'] !!}
                                                </div>
                                                <div class="mt-author-notes font-grey-mint" style="text-align:right">{{$s_email['date']}} <span class="label label-sm label-danger">{{array_get($users,$s_email['user_id'])}}</span></div>
                                            </div>
                                            <div class="mt-content border-grey-salt">

                                                <?php
                                                if($s_email['text_html']){
                                                    $s_email['text_html'] = preg_replace( "/<script[\s\S]*?<\/script>/i", "", $s_email['text_html'] );
                                                    $s_email['text_html'] = preg_replace( "/<iframe[\s\S]*?<\/iframe>/i", "", $s_email['text_html'] );
                                                    $s_email['text_html'] = preg_replace( "/<style[\s\S]*?<\/style>/i", "", $s_email['text_html'] );
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
                                                <div class="mt-author-name" style="text-align:left; float:left">
                                                    <span class="font-red-madison" >From : @if(isset($accounts[strtolower($s_email['from_address'])])) {{$accounts[strtolower($s_email['from_address'])]}} @endif < {{$s_email['from_address']}} ></span>{!! $s_email['fromAddressRsgStatusHtml'] !!}
                                                </div>
                                                <div style="clear: both"></div>
                                                <div class="mt-author-name" style="text-align:left; float:left">
                                                    <span href="javascript:;" class="font-blue-madison" >To : @if(isset($emailToEncryptedEmail[$s_email['to_address']])) {{$emailToEncryptedEmail[$s_email['to_address']]}}  @else {{$s_email['to_address']}}  @endif</span>{!! $s_email['toAddressRsgStatusHtml'] !!}
                                                </div>
                                                <div style="clear: both"></div>
                                                <div class="mt-author-notes font-grey-mint" style="text-align:left">{{$s_email['date']}} <span class="label label-sm label-danger">{{array_get($users,$s_email['user_id'])}}</span></div>
                                            </div>

                                            <div class="mt-content border-grey-steel">
                                        <span class="btn btn-circle <?php echo ($s_email['send_date'])?'green':'red';?>">
                                             <?php
                                            echo $s_email['status'].' ';

                                            if($s_email['send_date']) echo ' at '.$s_email['send_date'];

                                            if($s_email['error']){
                                                echo ' Error :'.$s_email['error'];
                                            }elseif($s_email['plan_date']){
                                                echo ' Plan at :'.date('Y-m-d H:i:s',$s_email['plan_date']);
                                            }
                                            ?>
                                        </span>
                                                <?php if($s_email['status']=='Waiting'){ ?>
                                                <a class="btn green-sharp btn-circle" data-toggle="confirmation" data-popout="true" href="{{url('sendcs/'.$s_email['id'])}}">Withdraw</a>
                                                <?php } ?>
                                                <BR>
                                                <?php
                                                $config = array(
                                                    'output-xhtml'=>true,
                                                    'drop-empty-paras'=>FALSE,
                                                    'join-classes'=>TRUE,
                                                    'show-body-only'=>TRUE,
                                                );


                                                $str = tidy_repair_string($s_email['text_html'], $config, 'UTF8');

                                                $str = tidy_parse_string($str,$config, 'UTF8');

                                                echo $str;

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

                    <div class="col-xs-3">
                        @include('inbox.rightBar')
                    </div>
                </div>
                <div style="clear:both;"></div>

                <div class="tab-pane" id="tab_2">

                        <div class="row" style="margin-bottom:50px;">
	
						<div class="col-md-2">
						
													<select id="rebindordersellerid" class="form-control" name="rebindordersellerid">
													<option value="">Auto Match</option>
													@foreach ($sellerids as $id=>$name)
														<option value="{{$id}}">{{$name}}</option>
													@endforeach
													</select> 		
													
						</div>

                        <div class="col-md-4">
                            <div class="input-group">
                                <input id="rebindorderinboxid" class="form-control" type="hidden" name="rebindorderinboxid" value="{{$email['id']}}">
                                <input id="rebindorderid" class="form-control" type="text" name="rebindorderid" placeholder="Amazon Order ID" style="width:300px;">
                                <span class="input-group-btn" style="padding-left: 10px;">
                                    <button id="rebindorder" class="btn btn-success" type="button">Rebind Order</button>
                                </span>
                                <span class="input-group-btn" style="padding-left: 20px;">
                                    <button id="unbindorder" class="btn btn-success" type="button">Unbind Order</button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            @if(isset($order->AmazonOrderId))<span>此封邮件订单号:{!! $order->AmazonOrderId !!}</span>@endif
                        </div>
                    </div>
                    </form>
                    @include('nonctg.orderInfo')
                </div>

                <div class="tab-pane" id="tab_4">
                    <form role="form" action="{{ url('inbox/change') }}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="inbox_id" id="inbox_id" value ="{{$email['id']}}">
                        <div class="col-xs-6">
                        <div class="form-group">
                            <label>Change Mail Status</label>
                            <div class="input-group ">
                                    <span class="input-group-addon">
                                        <i class="fa fa-bookmark"></i>
                                    </span>
                                <select class="form-control" name="reply" id="reply">
                                    <option value="2" <?php if(2==$email['reply']) echo 'selected';?>>Replied</option>
                                    <option value="1" <?php if(1==$email['reply']) echo 'selected';?>>Do not need to reply</option>
                                    <option value="0" <?php if(0==$email['reply']) echo 'selected';?>>Need reply</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Set Mark</label>
                            <div class="input-group">
                                <select class="form-control form-filter input-sm" name="mark">
                                    <option value="">Select...</option>
                                    @foreach (getMarks() as $mark)
                                        <option value="{{$mark}}" <?php if($mark==$email['mark']) echo 'selected';?>>{{$mark}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Question Type</label>
                            <div class="form-inline">
                                <select id="linkage1" name="linkage1" class="form-control city-select" data-selected="{{$email['linkage1']}}" data-parent_id="28"></select>
                                <select id="linkage2" name="linkage2" class="form-control city-select" data-selected="{{$email['linkage2']}}" data-parent_id="{{$email['linkage1']}}"></select>
                                <select id="linkage3" name="linkage3" class="form-control city-select" data-selected="{{$email['linkage3']}}" data-parent_id="{{$email['linkage2']}}"></select>
                                <select id="linkage4" name="linkage4" class="form-control city-select" data-selected="{{$email['linkage4']}}" data-parent_id="{{$email['linkage3']}}"></select>
                                <select id="linkage5" name="linkage5" class="form-control city-select" data-selected="{{$email['linkage5']}}" data-parent_id="{{$email['linkage4']}}"></select>
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
                                <input type="text" class="form-control" name="remark" id="remark" value="{{$email['remark']}}" >
                            </div>
                        </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div class="col-xs-6">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Assign to</label>
                                    <div class="input-group ">
                                    <span class="input-group-addon">
                                        <i class="fa fa-user"></i>
                                    </span>
                                        <select class="form-control" name="user_id" id="user_id">
                                            @foreach ($groups as $group_id=>$group)
                                                <optgroup label="{{array_get($group,'group_name')}}" data-show="0">
                                                    @foreach (array_get($group,'user_ids') as $user_id)
                                                        <option value="{{$group_id.'_'.$user_id}}" <?php if($group_id.'_'.$user_id==$email['group_id'].'_'.$email['user_id']) echo 'selected';?>>{{array_get($users,$user_id)}}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
										<input type="text" class="form-control" name="text" id="text" value="" placeholder="Remark for Assign">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" style="height:107px;">
                                <label></label>
                                <input id="search_user" type="text" class="form-control" value="" placeholder="search user" >
                            </div>

                                <div class="form-group">
                                    <label>ASIN</label>
                                    <div class="input-group ">
                                    <span class="input-group-addon">
                                        <i class="fa fa-bookmark"></i>
                                    </span>
                                        <input type="text" class="form-control" name="asin" id="asin" value="{{$email['asin']}}" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>SKU</label>
                                    <div class="input-group ">
                                    <span class="input-group-addon">
                                        <i class="fa fa-bookmark"></i>
                                    </span>
                                        <input type="text" class="form-control" name="sku" id="sku" value="{{$email['sku']}}" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Item NO.</label>
                                    <div class="input-group ">
                                    <span class="input-group-addon">
                                        <i class="fa fa-bookmark"></i>
                                    </span>
                                        <input type="text" class="form-control" onchange="rItemGroup();" name="item_no" id="item_no" value="{{$email['item_no']}}" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Item Group</label>
                                    <div class="input-group ">
                                    <span class="input-group-addon">
                                        <i class="fa fa-bookmark"></i>
                                    </span>
                                        <input type="text" class="form-control" name="item_group" id="item_group" value="{{$email['item_group']}}" >
                                    </div>
                                </div>




                                {{--<div class="form-group">--}}
                                    {{--<label>Problem Point</label>--}}
                                    {{--<div class="epoint_selectList form-inline">--}}
                                        {{--<select class="epoint_group form-control" name="epoint_group">--}}
                                            {{--<option value="{{$email['epoint_group']}}">{{$email['epoint_group']}}</option>--}}
                                        {{--</select>--}}
                                        {{--<select class="epoint_product form-control" name="epoint_product">--}}
                                            {{--<option value="{{$email['epoint_product']}}">{{$email['epoint_product']}}</option>--}}

                                        {{--</select>--}}
                                        {{--<select class="epoint form-control" name="epoint">--}}
                                            {{--<option value="{{$email['epoint']}}">{{$email['epoint']}}</option>--}}
                                        {{--</select>--}}
                                    {{--</div>--}}
                                    {{--<script type="text/javascript">--}}
                                        {{--$(function(){--}}
                                            {{--$(".epoint_selectList").each(function(){--}}
                                                {{--var url = "/epoint.json";--}}
                                                {{--var epointJson;--}}
                                                {{--var temp_html;--}}
                                                {{--var oepoint_group = $(this).find(".epoint_group");--}}
                                                {{--var oepoint_product = $(this).find(".epoint_product");--}}
                                                {{--var oepoint = $(this).find(".epoint");--}}

                                                {{--var epoint_group = function(){--}}
                                                    {{--temp_html = '<option value="">Group</option>';--}}
                                                    {{--$.each(epointJson,function(i,epoint_group){--}}
                                                        {{--temp_html+='<option value="'+i+'" '+((oepoint_group.val() == i) ?"selected":"")+'>'+i+'</option>';--}}
                                                    {{--});--}}
                                                    {{--oepoint_group.html(temp_html);--}}
                                                    {{--epoint_product();--}}
                                                {{--};--}}

                                                {{--var epoint_product = function(){--}}
                                                    {{--temp_html = '<option value="">Product</option>';--}}

                                                    {{--var n = oepoint_group.val();--}}
                                                    {{--if(typeof(epointJson[n]) == "undefined"){--}}
                                                        {{--oepoint_product.css("display","none");--}}
                                                        {{--oepoint.css("display","none");--}}
                                                    {{--}else{--}}
                                                        {{--oepoint_product.css("display","inline");--}}
                                                        {{--$.each(epointJson[n],function(i,epoint_product){--}}
                                                            {{--temp_html+='<option value="'+i+'" '+((oepoint_product.val() == i) ?"selected":"")+'>'+i+'</option>';--}}
                                                        {{--});--}}
                                                        {{--oepoint_product.html(temp_html);--}}
                                                        {{--epoint();--}}
                                                    {{--}--}}

                                                {{--};--}}

                                                {{--var epoint = function(){--}}
                                                    {{--temp_html = '<option value="">Problem</option>';--}}
                                                    {{--var m = oepoint_group.val();--}}
                                                    {{--var n = oepoint_product.val();--}}

                                                    {{--if(typeof(epointJson[m][n]) == "undefined"){--}}
                                                        {{--oepoint.css("display","none");--}}
                                                    {{--}else{--}}
                                                        {{--oepoint.css("display","inline");--}}
                                                        {{--$.each(epointJson[m][n],function(i,epoint){--}}
                                                            {{--temp_html+='<option value="'+epoint+'" '+((oepoint.val() == epoint) ?"selected":"")+'>'+epoint+'</option>';--}}
                                                        {{--});--}}
                                                        {{--oepoint.html(temp_html);--}}
                                                    {{--};--}}

                                                {{--};--}}
                                                {{--oepoint_group.change(function(){--}}
                                                    {{--epoint_product();--}}
                                                {{--});--}}
                                                {{--oepoint_product.change(function(){--}}
                                                    {{--epoint();--}}
                                                {{--});--}}
                                                {{--$.getJSON(url,function(data){--}}
                                                    {{--epointJson = data;--}}
                                                    {{--epoint_group();--}}
                                                {{--});--}}
                                            {{--});--}}
                                        {{--});--}}
                                    {{--</script>--}}
                                {{--</div>--}}
                            <div style="clear:both;"></div>
                        </div>

                        <div class="form-actions col-xs-12">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center;">
                                    <button type="submit" id="other_submit" class="btn blue">Submit</button>
                                    <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div style="clear:both;"></div>
                </div>

            </div>
        </div>


    </div>
</div>
        </div>
		 <div style="clear:both;"></div></div>
<?php
function procHtml($tree,$level = 0,$category_name)
{
    $html = '';
    foreach($tree as $key=>$val)
    {
        if($val['category_pid'] == '') {
            $html .= '<option value="'.$val['id'].'">'.$val['category_name'].' </option>';
        }else{
            $flg = str_repeat('|----',$level);
            $selected = ($val['category_name']==$category_name) ? 'selected' : '';
            $html .= '<option value="'.$val['category_name'].'" '.$selected.'>'.$flg.$val['category_name'];
            $html .= procHtml($val['category_pid'],$level+1,$category_name);
            $html = $html."</option>";
        }
    }
    return $html;
}
?>
<script src="/assets/global/plugins/bootstrap-confirmation/bootstrap-confirmation.min.js" type="text/javascript"></script>
    <script>
        $('#other_submit').click(function(){
            var linkage1 = $('#linkage1').val();
            var linkage2 = $('#linkage2').val();
            var linkage3 = $('#linkage3').val();
            console.log(linkage1+'__'+linkage2+'__'+linkage3);
            if(linkage1!='999999999'){
                //当Question Type选的是质量问题的时候，问题类型的2级和3级都要选才可以提交
                if(linkage1==14){
                    if(linkage2==999999999){
                        alert('Please select a secondary category');
                        return false;
                    }
                }
            }else{
                alert('Please select question type');
                return false;
            }
        })
    </script>
@endsection