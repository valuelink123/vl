@extends('layouts.layout')
@section('label', 'Setting Step Tab')
@section('content')
    <h1 class="page-title font-red-intense"> Step Tab
        <small>Setting Step Tab.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Step Form</span>
                </div>
            </div>
            <div class="portlet-body form">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <form role="form" action="{{ url('rs') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Title</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" name="title" id="title" value="" required />
                            </div>
                        </div>
						
					
						
						<div class="form-group">
                            <label>Content</label>
                            <div class="input-group ">
							@include('UEditor::head')
                                <script id="valuelink_content" name="content" type="text/plain">
									</script>
                                    <!-- 实例化编辑器 -->
                                    <script type="text/javascript">
                                        var ue1 = UE.getEditor('valuelink_content');
                                        ue1.ready(function() {
                                            ue1.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
											ue1.setHeight(300);
                                        });
                               		 </script>
                            </div>
                        </div>
						

                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="submit" class="btn blue">Submit</button>
                                <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="portlet light bordered" id="blockui_sample_1_portlet_body">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-bubble font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp sbold">How to use it?</span>
                </div>
            </div>
            <div class="portlet-body">

            </div>
        </div>

    </div>

    </div>

@endsection
