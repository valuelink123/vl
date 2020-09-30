@extends('layouts.layout')
@section('label', 'Update Edm Template')
@section('content')
	<style>
		table th{
			text-align:center;
		}
	</style>
	<form  action="/edm/template/update" id="form" novalidate method="POST" onsubmit="return validate_form()">
		{{ csrf_field() }}
		<input type="hidden" class="form-control" name="id" value="{{$customerData['id']}}">
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
							<span class="caption-subject bold font-green">Update Edm Template</span>
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
											<input type="text" class="form-control" name="name" id="name" value="{{$data['name']}}" required >
										</div>
									</div>
									<div class="form-group">
										<label>Abstract</label>
										<div class="input-group ">
                                            <span class="input-group-addon">
                                                <i class="fa fa-bookmark"></i>
                                            </span>
											<input type="text" class="form-control" name="abstract" id="abstract" value="{{$data['abstract']}}">
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
	<div id="tmp-content" style="display:none" data="{{$data['content']}}"></div>
	<script>
        //实例化富文本编辑器
        var ue = UE.getEditor('container');
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
            ue.setContent($('#tmp-content').attr('data'));
        });

        function validate_form(){
            var name = $('#name').val().trim();
            var abstract = $('#abstract').val().trim();
            if(name == ''){
                alert("name cannot be empty.");
                return false;
            }
            if(abstract == ''){
                alert("abstract cannot be empty.");
                return false;
            }
            var content = ue.getContent();
            if(content == ''){
                alert("content cannot be empty.");
                return false;
            }
        }
	</script>
@endsection