@extends('layouts.layout')
@section('crumb')
    <a href="/marketingPlan/index">Marketing Plan</a>
@endsection
@section('content')
<style>
	.rsg_plan_box{
		padding: 30px 0;
		background: #fff;
		margin: 20px auto;
		width: 1000px;
	}
	
	.mask_table{
		border: 1px solid #999;
		margin: 0 auto;
		width: 900px;
	}
	.mask_h3{
		font-size: 22px;
		font-weight: 600;
	}
	.mask_table table tr td{
		text-align: center;
		border: 1px solid #666;
		padding: 5px 0;
		
	}
	.mask_from{
		margin: 20px 100px 0 0;
		text-align: right;
	}
	.save_submit{
		background: #1797f1;
		border: 1px solid #1797f1;
		color: #fff;
		padding: 10px 25px;
	}
	.mask_close{
		padding: 10px 25px;
		background: #fff;
		border: 1px solid #ccc;
	}
	.mask_close:hover{
		text-decoration: none;
	}
	.borderN{
		border: none;
		background: #fff;
	}
	.bw9{
		width: 97%;
		margin: auto;
		background: #fff;
		border: 1px solid #ccc;
	}
	.country_txt{
		float: left;
		width: 10%;
		background: #0000FF;
		color: #fff;
		padding: 2px 0px;
		margin: 1px 5px;
	}
	.table-striped>tbody>tr:nth-of-type(odd){
		background: #fff;
	}
	.mask_table table tr td:nth-child(3){
		min-width: 150px;
		padding: 0 30px;
	}
	.select2-container .select2-selection--single{
		line-height: 26px !important;
		border: 1px solid #ccc;
		height: 26px !important;
	}
	#select2-asin-select-results{
		overflow-y: auto;
		max-height: 300px;
	}
	.table-striped .files p{
		padding: 0;
		margin: 5px 10px;
	}
	.table-striped .files .name{
		line-height: 55px;
	}
	.table-striped .files .size{
		line-height: 24px;
	}
	.table-striped .files tr td:nth-child(2){
		line-height: 65px;
	}
	.table-striped .files tr td:nth-child(3){
		line-height: 70px;
	}
	.table-striped .files .progress{
		margin: 0 10px;
	}
	#fileupload .fileupload-buttonbar .col-lg-7{
		margin-top: 13px;
	}
	#select2-asin-select-container:after{
		display: inline-block;
		content: '';
		width: 0px;
		height: 0;
		border-width: 4px;
		border-style: solid;
		position: absolute;
		top: 12px;
		right: 7px;
		border-color: #000000 transparent transparent transparent;
	}
	.success_mask{
		width: 400px;
		height: 50px;
		border-radius: 10px !important;
		position: fixed;
		left: 50%;
		margin-left: -200px;
		top: 250px;
		margin-top: -70px;
		background: #f0f9eb;
		border: 1px solid #e1f3d8;
		display: none;
		z-index: 9999;
	}
	.mask_icon{
		float: left;
		margin: 11px 15px;
	}
	.mask_text{
		float: left;
		line-height: 45px;
		color: #67c23a;
	}
	
	.error_mask{
		width: 400px;
		height: 50px;
		border-radius: 10px !important;
		position: fixed;
		left: 50%;
		margin-left: -200px;
		top: 250px;
		margin-top: -70px;
		background: #fef0f0;
		border: 1px solid #fde2e2;
		display: none;
		z-index: 9999;
	}
	.error_mask .mask_text{
		color: #f56c6c !important;
	}
	.deleteItem{
		display: block;
		background: #e7505a;
		color: #fff;
		width: 82px;
		height: 34px;
		line-height: 34px;
		margin: 10px auto;
		cursor: pointer;
	}
	.fileupload-progress{
		height: 40px;
	}
	.btn.default:not(.btn-outline){
		height: 23px;
		padding: 0 6px;
	}
	td svg{
		position: absolute;
		right: 5px;
	}
	.progress-extended{
		margin-top: -22px;
	}
	.input-group .form-control:first-child{
		border: none;
		height: 23px;
		background: #fff;
	}
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
<body>
	<div style="overflow: auto;">
		<div class="rsg_plan_box">
				<div class="mask_table">
					<table class="table-bordered" width="100%" cellspacing="0" cellpadding="5">
						<tr>
							<td colspan="6">
								<h3 class="mask_h3">RSG</h3>
							</td>
						</tr>
						<tr>
							<td style="width: 110px;">RSG 需求目的</td>
							<td colspan="2">
								<select class="bw9 rsgGoal">
									<option value ="">请选择</option>
									<option value ="1">提升星级</option>
									<option value ="2">稳定星级</option>
									<option value="3">提升转化率</option>
									<option value ="4">提升留评率</option>
									<option value="5">提升星星数量</option>
									<option value ="6">其它</option>
								</select>
							</td>
							<td></td>
							<td style="width: 135px;">任务状态</td>
							<td>
								<select class="bw9 planStatus">
									<option value ="1">待审批</option>
									<option value ="2">进行中</option>
									<option value ="3">已完结</option>
									<option value ="4">已中止</option>
									<option value ="5">已拒绝</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>ASIN</td>
							<td>
								<select class="asin-select form-control" placeholder="请选择" id="asin-select" style="float: left;width: 97%;">
									<option value="-1">请选择</option>
								</select>
							</td>
							<td>SKU</td>
							<td><span class="sku"></span></td>
							<td>SKU 状态</td>
							<td><span class="skuStatus"></span></td>
						</tr>
						<tr>
							<td>当前售价</td>
							<td>
								<input type="number" value="" style="padding-left: 5px; width: 55%;border: 1px solid #ccc; background: #fff;" class="ratingVal">
								<select name="" class="rate rateSelect" style="width: 40%;padding: 1px 0;">
									<option value="-1">请选择</option>
								</select>
							</td>
							<td>星级</td>
							<td><span class="star"></span></td>
							<td>星星数量</td>
							<td><span class="reviews"></span></td>
						</tr>
						<tr>
							<td>FBA 可用库存</td>
							<td>
								<span class="inventory"></span>
							</td>
							<td>星级目标</td>
							<td><input type="number" class="targetRating bw9" style="padding-left: 5px;" value=""></td>
							<td title="数量目标为总目标，包括自然留评+CTG+RSG，非RSG目标" style="position: relative;">
								数量目标
								<svg t="1588835330500" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2629" width="13" height="13"><path d="M459.364486 360.47352h102.080997v-102.080997h-102.080997v102.080997z m0 408.323988h102.080997V462.554517h-102.080997v306.242991z m51.040498 255.202492c-280.722741 0-510.404984-229.682243-510.404984-510.404984S226.492212 3.190031 510.404984 3.190031s510.404984 229.682243 510.404985 510.404985-229.682243 510.404984-510.404985 510.404984z m0-918.728972C285.507788 105.271028 102.080997 288.697819 102.080997 513.595016S285.507788 921.919003 510.404984 921.919003s408.323988-183.426791 408.323988-408.323987C918.728972 288.697819 735.302181 105.271028 510.404984 105.271028z" p-id="2630" fill="#2c2c2c"></path></svg>
							</td>
							<td><input type="number" value="" style="padding-left: 5px;" class="bw9 targetUnitsSold"></td>
						</tr>
						<tr>
							<td>开始</td>
							<td style="">
								<div class="input-group date date-picker margin-bottom-5 bw9" id="fromDate">
									<input type="text" class="form-control form-filter input-sm fromDate" readonly name="date_from" placeholder="From" value="">
									<span class="input-group-btn">
										<button class="btn btn-sm default" type="button">
											<i class="fa fa-calendar"></i>
										</button>
									</span>
								</div>
							</td>
							<td style="">结束</td>
							<td>
								<div class="input-group date date-picker bw9" data-date-format="yyyy-mm-dd">
									<input type="text" class="form-control form-filter input-sm toDate" readonly name="date_to" placeholder="To" value="">
									<span class="input-group-btn">
										<button class="btn btn-sm default" type="button">
											<i class="fa fa-calendar"></i>
										</button>
									</span>
								</div>
							</td>
							<td title="我们承担的费用" style="position: relative;">
								RSG付款金额
								<svg t="1588835330500" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2629" width="13" height="13"><path d="M459.364486 360.47352h102.080997v-102.080997h-102.080997v102.080997z m0 408.323988h102.080997V462.554517h-102.080997v306.242991z m51.040498 255.202492c-280.722741 0-510.404984-229.682243-510.404984-510.404984S226.492212 3.190031 510.404984 3.190031s510.404984 229.682243 510.404985 510.404985-229.682243 510.404984-510.404985 510.404984z m0-918.728972C285.507788 105.271028 102.080997 288.697819 102.080997 513.595016S285.507788 921.919003 510.404984 921.919003s408.323988-183.426791 408.323988-408.323987C918.728972 288.697819 735.302181 105.271028 510.404984 105.271028z" p-id="2630" fill="#2c2c2c"></path></svg>
							</td>
							<td>
								<input type="number" value="" style="padding-left: 5px;width: 55%;" class="bw9 rsgPrice">
								<select name="" class="rate rateSelect" style="width: 40%;padding: 1px 0;">
									<option value="-1">请选择</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>每日目标</td>
							<td style=""><input type="number" value="" style="padding-left: 5px;" class="bw9 rsgD"></td>
							<td style="">RSG 数量</td>
							<td><span class="totalRsg"></span></td>
							<td>预计成本</td>
							<td><span class="estSpend"></span></td>
						</tr>
						<tr>
							<td colspan="6">
								<h4 class="mask_h4">投入效果预估(达成Review目标后1周)</h4>
							</td>
						</tr>
						<tr>
							<td>当前排名</td>
							<td><span class="currentRank1"></span></td>
							<td>预计排名</td>
							<td><input type="text" value="" style="padding-left: 5px;" class="bw9 estRank"></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>当前转化率</td>
							<td><span class="currentCr1"></span></td>
							<td>预计转化率</td>
							<td><input type="text" value="" style="padding-left: 5px;" class="bw9 estCr"></td>
							<td>转化率提升</td>
							<td><span class="crChange"></span></td>
						</tr>
						<tr>
							<td>当前日均</td>
							<td><span class="currentSold1">0</span></td>
							<td>预计日均</td>
							<td><input type="number" value="" style="padding-left: 5px;" class="bw9 estSold"></td>
							<td>日均增长</td>
							<td><span class="dailyChange"></span></td>
						</tr>
						<tr>
							<td>经济效益/个</td>
							<td><span class="eValue"></span></td>
							<td>预计经济效益/个</td>
							<td>￥<input type="number" value="" style="padding-left: 5px; width: 87%; border: 1px solid #ccc; background: #fff;" class="estDay"></td>
							<td>预计经济效益增长/日</td>
							<td><span class="estAdded"></span></td>
						</tr>
						<tr>
							<td>60天预计ROI</td>
							<td><span class="estRoi60"></span></td>
							<td>120天预计ROI</td>
							<td><span class="estRoi120"></span></td>
							<td>投资回报天数</td>
							<td><span class="investmentCycle1"></span></td>
						</tr>
						<tr>
							<td colspan="6">
								<h4 class="mask_h4">实际投入效果(达成Review目标后1周)</h4>
							</td>
						</tr>
						<tr>
							<td>当前排名</td>
							<td> <span class="currentRank2"></span></td>
							<td colspan="2"></td>
							<td>RSG实际成本</td>
							<td>￥<input type="number" class="actualSpend" style="width: 90%;"></td>
						</tr>
						<tr>
							<td>实际转化率</td>
							<td><span class="currentCr2"></span></td>
							<td colspan="2"></td>
							<td>转化率预测达成</td>
							<td><span class="conversionComplete"></span></td>
						</tr>
						<tr>
							<td>实际日均</td>
							<td><span class="currentSold2"></span></td>
							<td colspan="2"></td>
							<td>日均预测达成</td>
							<td><span class="dailyComplete"></span></td>
						</tr>
						<tr>
							<td>实际经济效益/个</td>
							<td><span class="eUnit"></span></td>
							<td colspan="2"></td>
							<td>经济效益增长/日达成</td>
							<td><span class="eComplete"></span></td>
						</tr>
						<tr>
							<td>60天ROI</td>
							<td><span class="estDay60"></span></td>
							<td colspan="2"></td>
							<td>投资回报天数</td>
							<td><span class="investmentCycle2"></span></td>
						</tr>
						<tr>
							<td>文档</td>
							<td colspan="5">
								
								<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
								<form id="fileupload" action="{{ url('send') }}" method="POST" enctype="multipart/form-data">
								    {{ csrf_field() }}
									<input type="hidden" name="warn" id="warn" value="0">
								    <input type="hidden" name="inbox_id" id="inbox_id" value="0">
								    <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
													
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
							</td>
						</tr>
						<tr>
							<td>备注</td>
							<td colspan="5">
								<input type="text" value="notes" style="padding-left: 5px;" class="bw9 remarks">
							</td>
						</tr>
					</table>
					
				</div>
				
				<div class="mask_from">
					<button class="save_submit">提交</button>
					<a class="mask_close" href="javascript:window.opener=null;window.open('','_self');window.close();">取消</a>
				</div>
		</div>
	</div>
	<div class="success_mask">
		<span class="mask_icon">
			<svg t="1586572594956" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="12690" width="24" height="24"><path d="M511.1296 0.2816C228.7616 0.2816 0 229.1456 0 511.4368c0 282.2656 228.864 511.1296 511.1296 511.1296 282.2912 0 511.1552-228.864 511.1552-511.1296C1022.2848 229.1712 793.4208 0.256 511.1296 0.256z m-47.104 804.8384l-244.5056-219.9808 72.448-73.2672 145.5872 112.9728c184.832-251.136 346.624-331.776 346.624-331.776l20.1984 30.464c-195.6864 152.192-340.48 481.5872-340.352 481.5872z" fill="#1DC50C" p-id="12691" data-spm-anchor-id="a313x.7781069.0.i18" class="selected"></path></svg>
		</span>
		<span class="mask_text success_mask_text"></span>
	</div>
	<div class="error_mask">
		<span class="mask_icon">
			<svg t="1586574167843" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="13580" width="24" height="24"><path d="M512 0A512 512 0 1 0 1024 512 512 512 0 0 0 512 0z m209.204301 669.673978a36.555699 36.555699 0 0 1-51.750538 51.640431L511.779785 563.64043 353.995699 719.662796a36.555699 36.555699 0 1 1-52.301075-51.089893 3.303226 3.303226 0 0 1 0.88086-0.88086L460.249462 511.779785l-157.013333-157.453763a36.665806 36.665806 0 1 1 48.777634-55.053764 37.876989 37.876989 0 0 1 2.972904 2.972903l157.233548 158.114409 157.784086-156.132473a36.555699 36.555699 0 0 1 51.420215 52.08086L563.750538 512.220215l157.013333 157.453763z" fill="#FF5252" p-id="13581"></path></svg>
		</span>
		<span class="mask_text error_mask_text"></span>
	</div>当前日均
</body>


<script>
	/* http://10.10.42.14/vl/public */
	
	let sap_seller_id = <?php echo $sap_seller_id;?>;
	let ratVal,fulfillment,commission,cost;
	let tableObj  , urlIndex , detailId , listObj,time1,time2,domin_url,saveId;
	
	let url = window.location.href
	let ids = url.substr(url.lastIndexOf('=') + 1);
	
	function deleteRow(obj,url) {
		
		 $.ajax({
		  	type:"post",
		  	url:"/marketingPlan/delfiles",
		  	data:{
		  		"files_url": url,
				"id": ids
		  	},
		  	success:function(res){
		  		if(res.status == 1){
		  			$('.success_mask_text').text(res.msg)
		  			$('.success_mask').fadeIn(1000);
		  			setTimeout(function(){
		  				$('.success_mask').fadeOut(1000);
		  			},2000)	
		  		}else{
		  			$('.error_mask_text').text(res.msg)
		  			$('.error_mask').fadeIn(1000);
		  			setTimeout(function(){
		  				$('.error_mask').fadeOut(1000);
		  			},2000)
		  		} 	
				let index = obj.parentNode.parentNode.rowIndex
				let table = document.getElementById("table-striped");
				table.deleteRow(index);
		  	},
		  	error:function(err){
		  		$('.error_mask_text').text(err)
		  		$('.error_mask').fadeIn(1000);
		  		setTimeout(function(){
		  			$('.error_mask').fadeOut(1000);
		  		},2000)
		  	},
		  });
	}
	$(document).ready(function(){
		
		//初始化获取asin和汇率的数据
		function getInitalData(){
			$.ajax({
				type:"post",
				url:"/marketingPlan/index1",
				async: true,
				data:{
					"sap_seller_id": sap_seller_id,
				},
				success:function(res){
					$.each(res[0], function (index, value) {
						$("#asin-select").append("<option id='"+value.marketplaceid+"' fulfillment='"+value.fulfillment+"' commission='"+value.commission+"' cost='"+value.cost+"' rating='"+value.rating+"' value='"+value.asin + "' sku='"+value.sku+"' sku_status='"+value.sku_status+"' reviews='"+value.reviews+"'>" + value.country+" — "+ value.asin + "</option>");
					})
					$.each(res[1], function (index, value) {
						$('.rateSelect').append("<option value='"+value.id + "' rate='"+value.rate+"'>" + value.currency + "</option>");
					})
					$('.rateSelect').val('1')
				},
				error:function(err){
					$('.error_mask_text').text(err)
					$('.error_mask').fadeIn(1000);
					setTimeout(function(){
						$('.error_mask').fadeOut(1000);
					},2000)
				},
			}); 
						
		}
		getInitalData();
		
		if(ids == "null"){
			clearInput();
			$('.planStatus').attr("disabled",true);
		}else{
			$.ajax({
				type:"post",
				url:"/marketingPlan/detailEdit",
				data:{
					"sap_seller_id": sap_seller_id,
					"id": ids,
				},
				success:function(res){
					if(res.role <= 1){
						if(res.marketing_plan.plan_status > 1){
							$('.planStatus').attr("disabled",true);
						}else{
							$('.planStatus').attr("disabled",false);
						}
					}else{
						$('.rsgGoal').attr("disabled",true);
						$('#asin-select').attr("disabled",true);
						$('.ratingVal').attr("disabled",true);
						$('.rateSelect').attr("disabled",true);
						$('.fromDate').attr("disabled",true);
						$('.btn-sm').attr("disabled",true);
						$('.rsgD').attr("disabled",true);
						$('.toDate').attr("disabled",true);
						$('.targetRating').attr("disabled",true);
						$('.targetUnitsSold').attr("disabled",true);
						$('.rsgPrice').attr("disabled",true);
						$('.estRank').attr("disabled",true);
						$('.estCr').attr("disabled",true);
						$('.estSold').attr("disabled",true);
						$('.estDay').attr("disabled",true);
						$('.planStatus').attr("disabled",false);
					}
					$('.rsgGoal').val(res.marketing_plan.goal);
					$('.rateSelect').val(res.marketing_plan.currency_rates_id);
					console.log(res.marketing_plan.currency_rates_id)
					$('.planStatus').val(res.marketing_plan.plan_status);
					$("#asin-select").val(res.marketing_plan.asin);
					$('.sku').text(res.marketing_plan.sku);
					
					$('.skuStatus').text(res.marketing_plan.sku_status);
					$('.ratingVal').val(res.marketing_plan.sku_price);
					$("#select2-asin-select-container").text(res.marketing_plan.country + '—'+res.marketing_plan.asin);
					$('.inventory').text(res.marketing_plan.fba_stock);
					$('.star').text(res.marketing_plan.rating);
					$('.reviews').text(res.marketing_plan.reviews);
					$('.targetRating').val(res.marketing_plan.target_rating);
					$('.targetUnitsSold').val(res.marketing_plan.target_reviews);
					$('.fromDate').val(res.marketing_plan.from_time);
					$('.toDate').val(res.marketing_plan.to_time);
					$('.rsgPrice').val(res.marketing_plan.rsg_price);
					$('.rsgD').val(res.marketing_plan.rsg_d_target);
					$('.totalRsg').text(res.marketing_plan.rsg_total);
					$('.estSpend').text('￥' + res.marketing_plan.est_spend);
					$('.estRank').val(res.marketing_plan.est_rank);
					$('.estCr').val(Number(res.marketing_plan.est_cr).toFixed(2) + '%');
					$('.estSold').val(Number(res.marketing_plan.est_units_day).toFixed(2));
					$('.estDay').val(Number(res.marketing_plan.est_val));
					$('.estRoi120').text(Number(res.marketing_plan.est_120d_romi).toFixed(2));
					$('.remarks').val(res.marketing_plan.notes);
					$('.currentRank1').text(res.marketing_plan.ranking);
					$('.currentCr1').text(Number(res.marketing_plan.conversion).toFixed(2) + '%');
					$('.currentSold1').text(Math.round(res.marketing_plan.current_units_day));
					$('.eValue').text('￥' + Number(res.marketing_plan.current_e_val).toFixed(2));
					$('.estRoi60').text(Number(res.marketing_plan.current_60romi).toFixed(2) + '%');
					$('.currentRank2').text(res.marketing_plan.actual_rankactual_rank);
					$('.currentCr2').text(Number(res.marketing_plan.actual_cr).toFixed(2) + '%');
					$('.currentSold2').text(Number(res.marketing_plan.actual_units_day).toFixed(2));
					$('.eUnit').text('￥' + Number(res.marketing_plan.actual_e_val).toFixed(2));
					$('.estDay60').text(Number(res.marketing_plan.actual_60romi).toFixed(2) + '%');
					$('.crChange').text(Number(res.marketing_plan.cr_increase).toFixed(2) + '%');
					$('.dailyChange').text(Number(res.marketing_plan.units_d_increase).toFixed(2) + '%');
					$('.estAdded').text('￥' + Number(res.marketing_plan.val_d_increase).toFixed(2));
					$('.actualSpend').val(Number(res.marketing_plan.actual_spend).toFixed(2));
					$('.investmentCycle1').text(parseInt(res.marketing_plan.investment_return_d));
					$('.conversionComplete').text(Number(res.marketing_plan.cr_complete).toFixed(2) + '%');
					$('.dailyComplete').text(Number(res.marketing_plan.units_d_complete).toFixed(2) + '%');
					$('.eComplete').text(Number(res.marketing_plan.e_val_complete).toFixed(2) + '%');
					$('.investmentCycle2').text(Number(res.marketing_plan.investment_return_c));

					let strHtml="";
					if(res.marketing_plan.files != null){
						
						let fileArray = res.marketing_plan.files.split(",");
						$(fileArray).each(function(index,item){
							item+=''
							strHtml+="<tr><td><span style='display: block;line-height: 60px;'><a href=\" " + item + " \">"+ item +"</a></span><input type='hidden' class='filesUrl' value=\" " + item + " \"/></td><td><span class='deleteItem' onclick='deleteRow(this,\" " + item + " \")'><i class='fa fa-trash' style='margin-right:5px'></i>Delete</span></td></tr>";
						})
						$("#filesTable").append(strHtml);
					}
					return saveId = res.marketing_plan.marketplaceid;
				},
				error:function(err){
					$('.error_mask_text').text(err)
					$('.error_mask').fadeIn(1000);
					setTimeout(function(){
						$('.error_mask').fadeOut(1000);
					},2000)
				},
			}); 	
		}
		
		
		
		
		$('#asin-select').select2({
			tags:false,
		});
		$('#asin-select').on("change",function(e){
			let asinId = $(this).val();
			let id = $(this).find("option:selected").attr("id");
			$.ajax({
				type:"post",
				url:"/marketingPlan/getAsinDailyReport",
				data:{
					"asin":asinId,
					"marketplace_id": id,
				},
				success:function(res){
					$('.country_txt').text(res.country);//国旗缩写
					$('.inventory').text(res.fba_stock);//FBA可用库存
					$('.currentRank1').text(res.ranking);//当前排名
					$('.currentCr1').text(res.conversion);//当前转化率
					$('.currentSold1').text(Math.round(res.avg_day_sales));//当前日均
					$('.star').text(res.rating);
					$('.skuStatus').text(res.sku_status);
					$('.sku').text(res.sku);
					$('.reviews').text(res.reviews);
					$('.eValue').text(Number(res.single_economic).toFixed(2));//当前经济效益/个
					//日均增长赋值
					$('.dailyChange').text(dailyChangeNum($('.estSold').val(),$('.currentSold1').text()) + '%');
					//预计经济效益增长/日赋值
					$('.estAdded').text('￥' + estAddedNum($('.estSold').val(),$('.estDay').val(),$('.currentSold1').text(),$('.eValue').text()));
					//转化率提升赋值
					$(".crChange").text(crChangeNum($('.estCr').val(),$('.currentCr1').text()) + '%');
					//投资回报天数赋值
					//$('.investmentCycle1').text(investmentCycle1Num($('.estSpend').text(),$('.estAdded').text()));
					fulfillment = res.fulfillment;
					commission = res.commission;
					cost = res.cost;
					estSpendNum()
					$('.estRoi60').text(estRoiNum($('.estAdded').text(),$('.estSpend').text(),60) + "%")
					$('.estRoi120').text(estRoiNum($('.estAdded').text(),$('.estSpend').text(),120) + "%");
				},
				error:function(err){
					$('.error_mask_text').text(err)
					$('.error_mask').fadeIn(1000);
					setTimeout(function(){
						$('.error_mask').fadeOut(1000);
					},2000)
				},
			});
		});
		$('.rateSelect').on("change",function(e){
			$(".rateSelect").val($(this).find("option:selected").attr("value"));
			//投资回报天数赋值
			$('.investmentCycle1').text(investmentCycle1Num($('.fromDate').val(),$('.toDate').val(),$('.estAdded').text(),$('.estSpend').text()));
			
			ratVal = $(this).find("option:selected").attr("rate");
			estSpendNum()
		})
		//时间选择器
		$('.date-picker').datepicker({
			format: 'yyyy-mm-dd',
		    autoclose: true,
			datesDisabled : new Date(),
			startDate: '0',
		});
		//开始日期跟结束日期赋值
		function dateVal(){
			let oDate = new Date();
			let year = oDate.getFullYear();
			let month = oDate.getMonth()+1; 
			let day = oDate.getDate() + 1;
			month < 10 ? month = '0'+ month : month = month
			day < 10 ? day = '0'+ day : day = day
			let date = year + '-' + month + '-' + day;
			return date
		}
		function toDateVal(){
			let oDate = new Date();
			let year = oDate.getFullYear();
			let month = oDate.getMonth()+1; 
			let day = oDate.getDate() + 7;
			month < 10 ? month = '0'+ month : month = month
			day < 10 ? day = '0'+ day : day = day
			let date = year + '-' + month + '-' + day;
			return date
		}
		$('.fromDate').val(dateVal());
		$('.toDate').val(toDateVal());
		
		//日期1
		$('.fromDate').on('change',function(){
			$('.totalRsg').text(rsgNum($('.toDate').val(),$(this).val(),$('.rsgD').val()));
			//投资回报天数赋值
			$('.investmentCycle1').text(investmentCycle1Num($('.fromDate').val(),$('.toDate').val(),$('.estAdded').text(),$('.estSpend').text()));
			estSpendNum()
			
		})
		//日期2
		$('.toDate').on('change',function(){
			$('.totalRsg').text(rsgNum($(this).val(),$('.fromDate').val(),$('.rsgD').val()));	
			//投资回报天数赋值
			$('.investmentCycle1').text(investmentCycle1Num($('.fromDate').val(),$('.toDate').val(),$('.estAdded').text(),$('.estSpend').text()));
			estSpendNum()
		})
		//关闭窗口
		$('.mask_close').click(function(){
			window.close();
		})
		//汇率
		$('.rate').change(function(){
			$('.rate').val($(this).val())
		})
		
		//每日目标
		$('.rsgD').on('input',function(){
			$('.totalRsg').text(rsgNum($('.toDate').val(),$('.fromDate').val(),$(this).val()));	
			//投资回报天数赋值
			$('.investmentCycle1').text(investmentCycle1Num($('.fromDate').val(),$('.toDate').val(),$('.estAdded').text(),$('.estSpend').text()));
			
			estSpendNum()
		})
		//预计转化率
		$('.estCr').on('input',function(){
			$(".crChange").text(crChangeNum($(this).val(),$('.currentCr1').text()) + '%')
			//日均增长赋值
			$('.dailyChange').text(dailyChangeNum($('.estSold').val(),$('.currentSold1').text()) + '%');
		})
		
		//预计经济效益/个estDay
		$('.estDay').on('input',function(){
			//日均增长赋值
			$('.dailyChange').text(dailyChangeNum($('.estSold').val(),$('.currentSold1').text()) + '%');
			$('.estAdded').text('￥' + estAddedNum($('.estSold').val(),$(this).val(),$('.currentSold1').text(),$('.eValue').text()))
			$('.estRoi60').text(estRoiNum($('.estAdded').text(),$('.estSpend').text(),60) + "%")
			$('.estRoi120').text(estRoiNum($('.estAdded').text(),$('.estSpend').text(),120) + "%");
			//投资回报天数赋值
			$('.investmentCycle1').text(investmentCycle1Num($('.fromDate').val(),$('.toDate').val(),$('.estAdded').text(),$('.estSpend').text()));
		})
		
		//RSG金额
		$('.rsgPrice').on('input',function(){
			//投资回报天数赋值
			$('.investmentCycle1').text(investmentCycle1Num($('.fromDate').val(),$('.toDate').val(),$('.estAdded').text(),$('.estSpend').text()));
			estSpendNum()
		})
		//预计成本  预计日均*预计经济效益/个 - 当前日均*当前经济效益/个
		$('.estSold').on('input',function(){
			//日均增长赋值
			$('.dailyChange').text(dailyChangeNum($('.estSold').val(),$('.currentSold1').text()) + '%');
			$('.estAdded').text('￥' + estAddedNum($(this).val(),$('.estDay').val(),$('.currentSold1').text(),$('.eValue').text()))
			$('.estRoi60').text(estRoiNum($('.estAdded').text(),$('.estSpend').text(),60) + "%")
			$('.estRoi120').text(estRoiNum($('.estAdded').text(),$('.estSpend').text(),120) + "%");
			//投资回报天数赋值
			$('.investmentCycle1').text(investmentCycle1Num($('.fromDate').val(),$('.toDate').val(),$('.estAdded').text(),$('.estSpend').text()));
		})
		//当前售价
		$('.ratingVal').on('input',function(){
			estSpendNum();
			//投资回报天数赋值
			$('.investmentCycle1').text(investmentCycle1Num($('.fromDate').val(),$('.toDate').val(),$('.estAdded').text(),$('.estSpend').text()));
		})
		//任务状态
		$('.planStatus').on("change",function(){
			
		})
		
		
		/* ***********************************************计算*********************************************** */
		//预计成本 = RSG预计成本 * RSG数量   (RSG预计成本 = 物料成本+当前售价*亚马逊佣金率*汇率+拣配费+RSG付款/(1+paypal佣金率）*汇率)
		//RSG预计成本=  [物料成本+当前售价*亚马逊佣金率*汇率+拣配费+（RSG付款/（1-paypal费率）-RSG付款）*汇率]*RSG数量
		function estSpendNum(){
			let rsgPriceNum = $('.rsgPrice').val();//RSG金额
			let ratingVal = $('.ratingVal').val();//当前售价
			let totalRsgNum = $('.totalRsg').text();//RSG数量
			rsgPriceNum == null || rsgPriceNum == "" ? rsgPriceNum=0:rsgPriceNum,
			ratingVal == null || ratingVal == "" ? ratingVal=0:ratingVal,
			totalRsgNum == null || totalRsgNum == "" ? totalRsgNum=0:totalRsgNum
			ratVal == null || ratVal == undefined ? ratVal = 7.0655 : ratVal; //汇率
			fulfillment == null ? fulfillment = 0 : fulfillment;//拣配费
			commission == null ? commission = 0 : commission;//佣金比率
			cost == null ? cost = 0 : cost; //物料成本
			//let num = (Number(cost) + Number(ratingVal) * Number(commission) * Number(ratVal) + Number(fulfillment) + Number(rsgPriceNum) / (1 + 0.026) * Number(ratVal)) * Number(totalRsgNum);
			let num = (Number(cost) + Number(ratingVal) * Number(commission) * Number(ratVal) + Number(fulfillment) + (Number(rsgPriceNum) / (1 - 0.026) - Number(rsgPriceNum)) * Number(ratVal)) * Number(totalRsgNum);
			
			num = num.toFixed(2);
			isNaN(num) || num == "Infinity" || num == "-Infinity" ?  num = 0 :  num
			$('.estSpend').text('￥' + num);
		}
		//RSG 数量 = 每日目标 * 数量
		function rsgNum(num1,num2,num3){
			let date1 = Math.round(new Date(num1) / 1000)
			let date2 = Math.round(new Date(num2) / 1000)
			let date = date1 - date2;
			var days = Math.floor(Math.abs(date) / 60 / 60 / 24) + 1
			let num = parseFloat(num3) * days;	
			return isNaN(num) ?  num = 0 :  num
		}
		$('.totalRsg').text(rsgNum($('.toDate').val(),$('.fromDate').val(),$('.rsgD').val()));
		
		//转化率提升 = （预计转化率 - 当前转化率）/ 当前转化率 * 100%
		function crChangeNum(num1,num2){
			let val1 = parseFloat(num1);
			let val2 = parseFloat(num2)
			let num = (val1 - val2) / val2 * 100;
			num = num.toFixed(2)
			return isNaN(num) || num == "Infinity" || num == "-Infinity" ?  num = 0 :  num
		}
		//日均增长 = （预计日均 - 当前日均 ）/ 当前日均 * 100
		function dailyChangeNum(num1,num2){
			let num =( Number(num1) - Number(num2) ) / Number(num2) * 100;
			num = num.toFixed(2)
			return isNaN(num) || num == "Infinity" || num == "-Infinity" ?  num = 0 :  num
		}
		
		
		
		//预计经济效益增长/日 = 预计日均*预计经济效益/个 - 当前日均*当前经济效益/个
		function estAddedNum(num1,num2,num3,num4){
			let val1 = parseFloat(num1);
			let val2 = parseFloat(num2);
			isNaN(val2) ?  val2 = 0 :  val2
			let val3 = parseFloat(num3);
			val3==""||val3==null? val3=0:val3
			let val4 = parseFloat(num4.substring(num4.indexOf('￥') + 1));
			val4==""||val4==null? val4=0:val4
			isNaN(val4) ?  val4 = 0 :  val4
			let num = Number((val1 * val2)-(val3 * val4)).toFixed(2);		
			return isNaN(num) || num == "Infinity" || num == "-Infinity" ?  num = 0 :  num
		}
		
		
		
		//投资回报天数 = 预计成本/ (预计经济效益增长/日 * 天数)
		function investmentCycle1Num(num1,num2,num3,num4){
			
			let date1 = Math.round(new Date(num1) / 1000)
			let date2 = Math.round(new Date(num2) / 1000)
			let date = date1 - date2;
			var days = Math.floor(Math.abs(date) / 60 / 60 / 24) + 1
			let num = Number(strMoney(num4)) / (strMoney(num3) * days);
			num = Math.ceil(num)
			return isNaN(num) || num == "Infinity" || num == "-Infinity" ?  num = 0 :  num
		}
		investmentCycle1Num();
		
		//时间戳转换
		function dateStr(str){
			str = str.replace(/-/g,'/'); // 将-替换成/，因为下面这个构造函数只支持/分隔的日期字符串
			return  Math.round(new Date(str).getTime()/1000); // 构造一个日期型数据，值为传入的字符串
		}
		
		//120天/60天预计ROI = （预计经济效益增长/日 * 天数 - RSG预计成本）/ RSG预计成本
		function estRoiNum(num1,num2,num3){
			let ind1 = num1.indexOf('￥') + 1;
			let val1 = parseFloat(num1.substring(ind1));
			let ind2 = num2.indexOf('￥') + 1;
			let val2 = parseFloat(num2.substring(ind2));
			let num;
			if(num3 == 120){
				num = (val1 * 120  - val2) / val2
			}else{
				num = (val1 * 60  - val2) / val2
			}
			num == "Infinity"? num=0:num
			return isNaN(num) ?  num = 0 :  num.toFixed(2)
		}
		$('.estRoi60').text(estRoiNum($('.estAdded').text(),$('.estSpend').text(),60) + "%")
		$('.estRoi120').text(estRoiNum($('.estAdded').text(),$('.estSpend').text(),120) + "%");
		$('.save_submit').on('click',function(){
			if($('.rsgGoal').val() == ""){
				$('.error_mask_text').text('RSG 需求目的不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('#select2-asin-select-container').text() == '-1'){
				$('.error_mask_text').text('ASIN不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.rateSelect').val() == '-1'){
				$('.error_mask_text').text('货币类型不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.ratingVal').val() == ''){
				$('.error_mask_text').text('当前售价不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.fromDate').val() == ''){
				$('.error_mask_text').text('开始日期不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.rsgD').val() == ''){
				$('.error_mask_text').text('每日目标不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.targetRating').val() == ''){
				$('.error_mask_text').text('星级目标不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.toDate').val() == ''){
				$('.error_mask_text').text('结束日期不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.targetUnitsSold').val() == ''){
				$('.error_mask_text').text('数量目标不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.rsgPrice').val() == ''){
				$('.error_mask_text').text('RSG付款金额不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.estRank').val() == ''){
				$('.error_mask_text').text('预计排名不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.estCr').val() == ''){
				$('.error_mask_text').text('预计转化率不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.estSold').val() == ''){
				$('.error_mask_text').text('预计日均不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else if($('.estDay').val() == ''){
				$('.error_mask_text').text('预计经济效益/个不能为空')
				$('.error_mask').fadeIn(1000);
				setTimeout(function(){
					$('.error_mask').fadeOut(1000);
				},2000)
			}else{
				let goal= $('.rsgGoal').val();
				let plan_status = $('.planStatus').val();
				let marketplaceidVal,asinVal,fileList = [];
				let str = $('.table-striped tbody tr td').find('.filesUrl');
				for(var i=0;i<str.length;i++){
					fileList.push(str[i].defaultValue)
				}
				ids == null ? ids = 0 : ids;	
				if(ids == 'null'){
					marketplaceidVal = $('#asin-select').find("option:selected").attr("id");
					asinVal = $('#asin-select').val();
				}else{
					asinVal = $('#select2-asin-select-container').text().substr($('#select2-asin-select-container').text().lastIndexOf('—') + 1);
					marketplaceidVal = saveId;	
					$.ajax({
						type:"post",
						url:"/marketingPlan/updatePlan",
						data:{
							"sap_seller_id": sap_seller_id,
							"id": ids,
							"plan_status": plan_status
						},
						success:function(res){
							if(res.status == 1){
								$('.success_mask_text').text(res.msg)
								$('.success_mask').fadeIn(1000);
								setTimeout(function(){
									$('.success_mask').fadeOut(1000);
								},2000)	
							}else{
								$('.error_mask_text').text(res.msg)
								$('.error_mask').fadeIn(1000);
								setTimeout(function(){
									$('.error_mask').fadeOut(1000);
								},2000)
							}
						},
						error:function(err){
							$('.error_mask_text').text(err)
							$('.error_mask').fadeIn(1000);
							setTimeout(function(){
								$('.error_mask').fadeOut(1000);
							},2000)
						},
					});
				}
				let asin = asinVal;
				let marketplaceid = marketplaceidVal;
				let sku = $('.sku').text();
				let sku_status = $('.skuStatus').text();
				let sku_price = $('.ratingVal').val();
				let currency_rates_id = $('.rateSelect').val();
				let fba_stock = $('.inventory').text();
				let rating = $('.star').text();
				let reviews = $('.reviews').text();
				let target_rating = $('.targetRating').val();
				let target_reviews = $('.targetUnitsSold').val();
				let fromDate = dateStr($('.fromDate').val());
				let toDate = dateStr($('.toDate').val());
				let rsg_price = $('.rsgPrice').val();
				let rsg_d_target = $('.rsgD').val();
				let rsg_total = $('.totalRsg').text(); 
				let est_spend = strMoney($('.estSpend').text());
				let est_rank = $('.estRank').val();
				let est_cr = parseFloat($('.estCr').val());
				let est_units_day = $('.estSold').val();
				let est_val = $('.estDay').val();
				let est_120d_romi = parseFloat($('.estRoi120').text());
				let notes = $('.remarks').val();
				let current_rank = $('.currentRank1').text();
				let current_cr = $('.currentCr1').text();
				let current_units_day = $('.currentSold1').text();
				let current_e_val = $('.eValue').text();
				let current_60romi = parseFloat($('.estRoi60').text());
				let actual_rank = $('.currentRank2').text();
				let actual_cr = parseFloat($('.currentCr2').text());
				let actual_units_day = $('.currentSold2').text();
				let actual_e_val = strMoney($('.eUnit').text());
				let actual_60romi = parseFloat($('.estDay60').text());
				let cr_increase = parseFloat($('.crChange').text());
				let units_d_increase = parseFloat($('.dailyChange').text());
				let val_d_increase = strMoney($('.estAdded').text());
				let actual_spend = $('.actualSpend').val();
				let investment_return_d = $('.investmentCycle1').text();
				let cr_complete = parseFloat($('.conversionComplete').val());
				let units_d_complete = parseFloat($('.dailyComplete').text());
				let e_val_complete = parseFloat($('.eComplete').text());
				let investment_return_c = $('.investmentCycle2').text();
				$.ajax({
					type:"post",
					url:"/marketingPlan/addMarketingPlan",
					data:{
						"id": ids,
						"sap_seller_id": sap_seller_id,
						"goal": goal,
						"plan_status": plan_status,
						"asin": asin,
						"marketplaceid": marketplaceid,
						"sku": sku,
						"sku_status":sku_status,
						"sku_price": sku_price,
						"currency_rates_id": currency_rates_id,
						"fba_stock": fba_stock,
						"rating": rating,
						"reviews": reviews,
						"target_rating": target_rating,
						"target_reviews": target_reviews,
						"from_time": fromDate,
						"to_time": toDate,
						"rsg_price": rsg_price,
						"rsg_d_target": rsg_d_target,
						"rsg_total": rsg_total,
						"est_spend": est_spend,
						"est_rank": est_rank,
						"est_cr": est_cr,
						"est_units_day": est_units_day,
						"est_val": est_val,
						"est_120d_romi": est_120d_romi,
						"notes": notes,
						"current_rank": current_rank,
						"current_cr": current_cr,
						"current_units_day": current_units_day,
						"current_e_val": current_e_val,
						"current_60romi": current_60romi,
						"actual_rank": actual_rank,
						"actual_cr": actual_cr,
						"actual_units_day": actual_units_day,
						"actual_e_val": actual_e_val,
						"actual_60romi": actual_60romi,
						"cr_increase": cr_increase,
						"units_d_increase": units_d_increase,
						"val_d_increase": val_d_increase,
						"actual_spend": actual_spend,
						"investment_return_d": investment_return_d,
						"cr_complete": cr_complete,
						"units_d_complete": units_d_complete,
						"e_val_complete": e_val_complete,
						"investment_return_c": investment_return_c,
						"files": fileList
					},
					success:function(res){
						if(res.status == 1){
							$('.success_mask_text').text(res.msg)
							$('.success_mask').fadeIn(1000);
							setTimeout(function(){
								$('.success_mask').fadeOut(1000);
							},2000);
							if(res.id != null){
								let urlId = res.id;
								window.location.href = "?id=" + urlId
							}
						
						}else{
							$('.error_mask_text').text(res.msg)
							$('.error_mask').fadeIn(1000);
							setTimeout(function(){
								$('.error_mask').fadeOut(1000);
							},2000)
						}
					},
					error:function(err){
						$('.error_mask_text').text(err.statusText)
						$('.error_mask').fadeIn(1000);
						setTimeout(function(){
							$('.error_mask').fadeOut(1000);
						},2000)
					},
				}); 
			}
			
		}) 
		
		function clearInput(){
			$('.rsgGoal').val("");
			$('.planStatus').val(1);
			$("#asin-select").val(-1);
			$('.sku').text("");
			$('.skuStatus').text("");
			$('.ratingVal').val("");
			$('.rateSelect').val(-1);
			$('.inventory').text("");
			$('.star').text("");
			$('.reviews').text("");
			$('.targetRating').val("");
			$('.targetUnitsSold').val("");
			$('.fromDate').val("");
			$('.toDate').val("");
			$('.rsgPrice').val("");
			$('.rsgD').val("");
			$('.totalRsg').text("");
			$('.estSpend').text("");
			$('.estRank').val("");
			$('.estCr').val("");
			$('.estSold').val("");
			$('.estDay').val("");
			$('.estRoi120').text("");
			$('.remarks').val("");
			$('.currentRank2').text("");
			$('.currentCr1').text("");
			$('.currentSold1').text("");
			$('.eValue').text("");
			$('.estRoi60').text("");
			$('.currentRank2').text("");
			$('.currentCr2').text("");
			$('.currentSold2').text("");
			$('.eUnit').text("");
			$('.estDay60').text("");
			$('.crChange').text("");
			$('.crChange').text("");
			$('.estAdded').text("");
			$('.actualSpend').val("");
			$('.investmentCycle1').text("");
			$('.conversionComplete').val("");
			$('.dailyComplete').text("");
			$('.eComplete').text("");
			$('.investmentCycle2').text("");
		}
		function strMoney(str){
			if(str != null){
				return str.substr(str.lastIndexOf('￥')+1);	
			}
		}
		
		
		
	})
</script>

@endsection