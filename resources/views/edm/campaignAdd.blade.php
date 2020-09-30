@extends('layouts.layout')
@section('label', 'Create A New Edm Campaign')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<form  action="/edm/campaign/add" id="form" novalidate method="POST" onsubmit="return validate_form()">
		{{ csrf_field() }}
		<div class="col-lg-9">
			<div class="col-md-12">
				<div class="portlet light portlet-fit bordered ">
					@if($errors->any())
						<div class="alert alert-danger">
							@foreach($errors->all() as $error)
								<div>{{ $error }}</div>
							@endforeach
						</div>
					@endif
					<div class="portlet-title">
						<div class="caption">
							<i class="icon-microphone font-green"></i>
							<span class="caption-subject bold font-green">Create A New Edm Campaign</span>
						</div>
					</div>
					<div class="portlet-body">
						<div class="tabbable-line">
							<div class="">
								<div class="col-lg-8">
									<div class="form-group">
										<label>Name</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="name" id="name" value="" required >
										</div>
									</div>
									<div class="form-group">
										<label>Template</label>
										<div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
											<select class="btn btn-default" id="template_id" data-width="100%" name="template_id" style="width:100%">
												<option value="" >Select</option>
												@foreach ($tmpData as $key => $value)
													<option value="{{$key}}" >{{$value}}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="form-group">
										<label>Site</label>
										<div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
											<select class="btn btn-default" id="marketplaceid" data-width="100%" name="marketplaceid" style="width:100%">
												@foreach ($site as $key => $value)
													<option value="{{ $value->marketplaceid }}">{{ $value->domain }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="form-group">
										<label>Asin</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="asin" id="asin" value=""  >
										</div>
									</div>
									<div class="form-group">
										<label>Subject</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="subject" id="subject" value=""  >
										</div>
									</div>
									<div class="form-group">
										<label>Tag</label>
										<div class="input-group ">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
											<select class="mt-multiselect btn btn-default" id="tag_id" multiple="multiple" data-width="100%" data-action-onchange="true" name="tag_id[]" id="tag_id[]" >
												@foreach ($tagData as $key => $value)
													<option value="{{$key}}" >{{$value}}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="form-group">
										<div class="col-md-4" style="margin-left:-16px;margin-bottom:10px;">
											<label>Send Time</label>
											<div class="input-group ">
													<span class="input-group-addon">
														<i class="fa fa-bookmark"></i>
													</span>
												<select class="btn btn-default" id="set_sendtime" data-width="100%" data-action-onchange="true" name="set_sendtime" >
													<option value="0">immediately</option>
													<option value="1">date time</option>
												</select>
											</div>
										</div>
										<div class="col-md-4 senddate" style="margin-top:22px;display:none;">
											<input class="form-control" value="{{$date}}" id="senddate" name="senddate"/>
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<label>Content</label>
									<div class="form-group" >
									@include('UEditor::head')
									<!-- 加载编辑器的容器 -->
										<script id="container" name="content" type="text/plain"></script>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-4 col-md-8">
								<button type="submit" class="btn blue">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</div>
	</form>
	@include('frank.common')
	<script>
        $('#senddate').datepicker({
            format:'yyyy-mm-dd',
            startDate:new Date(),
        })
        //实例化富文本编辑器
        var ue = UE.getEditor('container');
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
        });
		//验证表单
        function validate_form(){
            var name = $('#name').val().trim();
            var asin = $('#asin').val().trim();
            var subject = $('#subject').val().trim();
            var tag = $('.multiselect-selected-text').text();
            var template_id = $('#template_id').val().trim();//template_id
            if(name == ''){
                alert("name cannot be empty.");
                return false;
            }
            if(asin == ''){
                alert("asin cannot be empty.");
                return false;
            }
            if(subject == ''){
                alert("subject cannot be empty.");
                return false;
            }
            if(tag == 'None Selected'){
                alert("tag cannot be empty.");
                return false;
            }
            if(template_id == ''){
                alert("template cannot be empty.");
                return false;
            }
            var content = ue.getContent();
            if(content == ''){
                alert("content cannot be empty.");
                return false;
            }
        }

        //当选择template和asin后，赋值content内容
        $("#asin").change(function(){
            var template_id = $('#template_id').val().trim();//template_id
            var asin = $('#asin').val().trim();//asin
            var marketplaceid = $('#marketplaceid').val().trim();//marketplaceid
            $.ajax({
                type: 'post',
                url: '/edm/getContentByTmpAsin',
                data: {template_id:template_id,marketplaceid:marketplaceid,asin:asin},
                dataType:'json',
                success: function(res) {
                    if(res.status==1){
                        ue.setContent(res.content);
                    }else{
                        alert(res.msg);
                    }
                }
            });
        });
		//设置的发送日期在immediately与date time之间进行切换的时候,显示隐藏时间控件
        $('#set_sendtime').change(function(){
            var set_sendtime = $('#set_sendtime').val();
            if(set_sendtime==1){
                $('.senddate').show();
			}else{
                $('.senddate').hide();
			}

		})
	</script>
@endsection