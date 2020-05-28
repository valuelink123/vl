@extends('layouts.layout')
@section('label', 'Marketing Plan')
@section('content')



<link rel="stylesheet" type="text/css" media="all" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>

<div>
	<div class="button_box">
			<form id="fileupload" action="/marketingPlan/importExecl" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="warn" id="warn" value="0">
				<input type="hidden" name="inbox_id" id="inbox_id" value="0">

				<div>
					<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
					<div class="fileupload-buttonbar">
						<div class="col-lg-12">
							<!-- The fileinput-button span is used to style the file input field as button -->
							<span class="btn green fileinput-button">
													<i class="fa fa-plus"></i>
													<span>添加文件</span>
													<input type="file" name="files[]" multiple="">
												</span>
							<button type="submit" class="btn blue start">
								<i class="fa fa-upload"></i>
								<span>开始上传</span>
							</button>
							<button type="reset" class="btn warning cancel">
								<i class="fa fa-ban-circle"></i>
								<span>取消上传 </span>
							</button>

							<button type="button" class="btn red delete">
								<i class="fa fa-trash"></i>
								<span>删除</span>
							</button>
							<!-- <input type="checkbox" class="toggle"> -->
							<!-- The global file processing state -->
							<span class="fileupload-process"> </span>
						</div>
						<!-- The global progress information -->
						<div class="col-lg-12 fileupload-progress fade">
							<!-- The global progress bar -->
							<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
								<div class="progress-bar progress-bar-success" style="width:0%;"> </div>
							</div>
							<!-- The extended global progress information -->
							<div class="progress-extended"> &nbsp; </div>
						</div>
					</div>
					<!-- The table listing the files available for upload/download -->
					<table role="presentation" class="table table-striped clearfix" id="table-striped" style="margin-bottom: 0;">
						<tbody class="files" id="filesTable"> </tbody>
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
								                <strong class="error text-danger label label-danger" style="padding: 0 6px;"></strong>
								            </td>
								            <!-- <td>
								                <p class="size">Processing...</p>
								                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
								                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
								                </div>
								            </td> -->
								            <td> {% if (!i && !o.options.autoUpload) { %}
								                <button class="btn blue start" disabled>
								                    <i class="fa fa-upload"></i>
								                    <span>开始</span>
								                </button> {% } %} {% if (!i) { %}
								                <button class="btn red cancel">
								                    <i class="fa fa-ban"></i>
								                    <span>取消</span>
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
								                        <input type="hidden" name="fileid[]" class="filesUrl" value="{%=file.url%}">
								                    {% } %}

								                    </p> {% if (file.error) { %}
								                <div>
								                    <span class="label label-danger">Error</span> {%=file.error%}</div> {% } %} </td>
								            <!-- <td>
								                <span class="size">{%=o.formatFileSize(file.size)%}</span>
								            </td> -->
								            <td> {% if (file.deleteUrl) { %}
								                <button class="btn red delete btn-sm" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}" {% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}' {% } %}>
								                    <i class="fa fa-trash-o"></i>
								                    <span>删除</span>
								                </button>
								                <!-- <input type="checkbox" name="delete" value="1" class="toggle"> --> {% } else { %}
								                <button class="btn yellow cancel btn-sm">
								                    <i class="fa fa-ban"></i>
								                    <span>取消</span>
								                </button> {% } %} </td>
								        </tr> {% } %} </script>
					<div style="clear:both;"></div>
				</div>
			</form>
	</div>

</div>

@endsection