@extends('layouts.layout')
@section('label', 'Management')
@section('content')
<style>
	.content{
		padding: 30px 20px 40px 20px;
		border-radius: 4px !important;
		background-color: rgba(255, 255, 255, 1);
	}
	.filter_box{
		overflow: hidden;
		padding: 20px;
		background: #fff;
		margin: 20px 0;
		border-radius: 8px !important;
	}
	.filter_box select{
		border-radius: 4px !important;
		width: 180px;
		height: 36px;
		color: #666;
		border: 1px solid rgba(220, 223, 230, 1);
	}
	.filter_option{
		float: left;
		margin-right: 20px;
	}
	.filter_option > label{
		display: block;
		color: rgba(48, 49, 51, 1);
		font-size: 14px;
		text-align: left;
		font-family: PingFangSC-Semibold;
	}
	.nav_list{
		overflow: hidden;
		height: 45px;
		line-height: 45px;
		border-bottom: 2px solid #fff;
		padding: 0;
		margin: 0;
	}
	.nav_list li{
		float: left;
		line-height: 36px;
		padding: 5px 10px 0 10px;
		margin: 0 10px 0 0;
		list-style: none;
		color: #666;
		cursor: pointer;
	}
	.nav_active{
		border-bottom: 2px solid #4B8DF8;
		color: #4B8DF8 !important;
	}
	.tab-item{
		display: none;
		background: #fff;
		padding: 20px;
		margin-top: 5px;
	}
	.tab-item:first-child{
		display: block;
	}
	.tab-item-header{
		text-align: right;
		border-bottom: 1px solid #ccc;
		padding-bottom: 10px;
	}
	.tab-item-header button{
		margin-left: 5px;
	}
	.tab-item-body ul{
		padding: 20px;
		margin: 0;
	}
	.tab-item-body ul li{
		padding: 0;
		margin: 0;
		list-style: none;
		overflow: hidden;
		line-height: 38px;
	}
	.tab-item-body ul li label{
		display: block;
		float: left;
		width: 80px;
		font-weight: bold;
	}
	.tab-item-body ul li span{
		margin-left: 80px;
		display: block;
	}
	.table>thead:first-child>tr:first-child>th{
		text-align: center;
	}
	.mask_box,.add_staff_mask,.add_Jobs_mask{
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		background: rgb(0,0,0,.3);
		z-index: 999;
		display: none;
	}
	.mask-dialog,.add_staff_dialog,.add_Jobs_dialog{
		width: 540px;
		height: 500px;
		background: #fff;
		position: absolute;
		left: 50%;
		top: 50%;
		padding: 20px 60px;
		margin-top: -250px;
		margin-left: -270px;
	}
	.close_class{
		position: absolute;
		top: 20px;
		right: 20px;
		cursor: pointer;
		width: 30px;
		padding: 8px;
		height: 30px;
	}
	.department_title,.staff_title,.Jobs_title{
		text-align: center;
		font-size: 16px;
		font-weight: bold;
	}
	.department_list{
		padding: 0;
		margin: 0 25px;
		list-style: none;
	}
	.department_list li{
		padding: 5px 0 15px 0;
		margin: 0;
		position: relative;
	}
	.department_list li label{
		display: inline-block;
		width: 80px;
	}
	.department_list li > input,.department_list li select{
		width: 280px;
		height: 28px;
		border: 1px solid #666;
		padding-left: 5px;
	}
	.mask_btn{
		text-align: center;
		margin: 20px 0px;
		margin-left: 30px;
	}
	.mask_btn button{
		width: 65px;
		height: 30px;
		margin:  0 5px;
	}
	.wrap_one_single:before{
		content: '*';
		color: red;
		position: absolute;
		left: -10px;
		top: 12px;
	}
	.errCode{
		position: absolute;
		color: red;
		left: 82px;
		font-size: 8px;
		bottom: -2px;
		display: none;
	}
	/* 添加人员 */
	.add_staff_dialog{
		height: 770px;
		margin-top: -385px;
	}
	.dropdown > .dropdown-menu, .dropdown-toggle > .dropdown-menu, .btn-group > .dropdown-menu{
		margin-top: 0;
	}
	.dropdown-menu{
		box-shadow: none;
	}
	.dropdown-menu li {
		padding: 0;
	}
	.input-group-addon{
		padding: 6px 2px;
	}
	.mask-dialog .multiselect-container{
		max-height: 235px;
	}
	.btn:not(.btn-sm):not(.btn-lg){
		padding: 3px;
		width: 280px !important;
		border: 1px solid #666;
	}
	.input-group-btn:last-child>.btn, .input-group-btn:last-child>.btn-group{
		width: 25px !important;
		padding: 6px 4px;
		border: 1px solid #c2cad8;
	}
	.filter_box .select2-container{
		border-radius: 4px !important;
		width: 180px !important;
		height: 36px !important;
		color: #666 !important;
		border: 1px solid rgba(220, 223, 230, 1) !important;
	}
	.filter_box .select2-container .select2-selection--single .select2-selection__rendered{
		padding: 7px 5px 0 5px !important;
	}
	.select2-container{
		/* height: 28px !important;
		width: 280px !important; */
		border: 1px solid #666 !important;
	}
	.select2-container .select2-selection--single .select2-selection__rendered{
		padding: 4px 5px 0 5px !important;
	}
	.mask_box .permissions_li2 .select2-container,.add_Jobs_mask .permissions_li2 .select2-container,.add_staff_mask .permissions_li2 .select2-container{
		width: 170px !important;
		margin-top: -3px;
	}
	.select2-results__option{
		font-size: 13px;
	}
	/* 新建岗位 */
	.add_Jobs_dialog{
		height: 440px;
		margin-top: -220px;
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
	/* switch开关 */
	.switch{
		cursor: pointer;
		width:40px;
		height:20px;
		border-radius:30px !important;
		overflow: hidden;
		vertical-align:middle;
		position:relative;
		display: inline-block;
		background:#ccc;
		box-shadow: 0 0 1px #61c737;
	}
	.switch input{
	  visibility: hidden;
	}
	.switch span{
	  position:absolute;
	  top:0;
	  left:0;
	  border-radius: 50%;
	  width:50%;
	  height:100%;
	  transition:all linear 0.2s;
	}
	.switch span::before{
	  position: absolute;
	  top:0;
	  left:-100%;
	  content:'';
	  width:200%;
	  height:100%;
	  border-radius: 30px;
	  background:#61c737;
	}
	.switch span::after{
	  content:'';
	  position:absolute;
	  left:0;
	  top:0;
	  width:100%;
	  height:100%;
	  border-radius: 50%;
	  background:#fff;
	}
	.switch input:checked +span{
	  transform:translateX(100%);
	}
	#select2-superior_department-results{
		max-height: 290px;
		overflow: auto;
	}
	.select2-results__options{
		max-height: 190px;
		overflow: auto;
	}
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js"></script>
<div>
	<div class="filter_box">
		<div class="filter_option">
			<label for="department1">一级部门</label>
			<select id="department1"></select>
		</div>
		<div class="filter_option">
			<label for="department2">二级部门</label>
			<select id="department2">
				<option value ="">请选择</option>
			</select>
		</div>
		<div class="filter_option">
			<label for="department3">三级部门</label>
			<select id="department3">
				<option value ="">请选择</option>
			</select>
		</div>
	</div>
	<div>
		<ul class="nav_list">
			<li class="nav_active">部门信息</li>
			<li>岗位</li>
			<li>人力资源</li>
		</ul>
	    <div class="tab_content">
		    <div class="tab-item">
				<div class="tab-item-header">
					<button class="btn btn-sm red-sunglo" id="handleAddPermissions">新建部门</button>
					<button class="btn btn-sm yellow-crusta" id="handleEditPermissions">编辑部门</button>
					<button class="btn btn-sm green-meadow handleAddStaff">添加人员</button>
				</div>
				<div class="tab-item-body">
					<ul>
						<li><label for="">部门</label> <span class="dept_text"></span></li>
						<li><label for="">上级部门</label> <span class="sup_dept"></span></li>
						<li><label for="">一级部门</label> <span class="rank_one_dept"></span></li>
						<li><label for="">部门长</label> <span class="leader"></span></li>
						<li><label for="">权限</label> <span class="permission"></span></li>
					</ul>
				</div>
			</div>
		    <div class="tab-item">
				<div class="tab-item-header">
					<button class="btn btn-sm red-sunglo handleAddJobs">新建岗位</button>
					<button class="btn btn-sm green-meadow handleAddStaff">添加人员</button>
				</div>
				<div>
					<table class="table table-striped table-bordered" id="jobsTable" style="width:100%">
					    <thead>
							<tr>
								<th>岗位</th>
								<th>职称</th>
								<th>部门</th>
								<th>权限</th>
								<th>操作</th>
							</tr>
					    </thead>
					    <tbody></tbody>
					</table>
				</div>
			</div>
		    <div class="tab-item">
				<div class="tab-item-header">
					<button class="btn btn-sm green-meadow handleAddStaff">添加人员</button>
				</div>
				<div>
					<table class="table table-striped table-bordered" id="staffTable" style="width:100%">
					    <thead>
							<tr>
								<th>ID</th>
								<th>姓名</th>
								<th>英文名</th>
								<th>岗位</th>
								<th>职称</th>
								<th>钉钉ID</th>
								<th>状态</th>
								<th>操作</th>
							</tr>
					    </thead>
					    <tbody></tbody>
					</table>
				</div>
			</div>
	    </div>
	</div>
</div>

<!-- 新建/编辑部门 -->
<div class="mask_box">
	<div class="mask-dialog">
		<svg t="1588919283810" class="icon close_class" onclick="$('.mask_box').hide()" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4128" width="15" height="15"><path d="M1001.952 22.144c21.44 21.44 22.048 55.488 1.44 76.096L98.272 1003.36c-20.608 20.576-54.592 20-76.096-1.504-21.536-21.44-22.048-55.488-1.504-76.096L925.824 20.672c20.608-20.64 54.624-20 76.128 1.472" p-id="4129" fill="#707070"></path><path d="M22.176 22.112C43.616 0.672 77.6 0.064 98.24 20.672L1003.392 925.76c20.576 20.608 20 54.592-1.504 76.064-21.44 21.568-55.488 22.08-76.128 1.536L20.672 98.272C0 77.6 0.672 43.584 22.176 22.112" p-id="4130" fill="#707070"></path></svg>
		<p class="department_title"></p>
		<ul class="department_list">
			<li class="wrap_one_single">
				<label for="department_name">部门</label>
				<input type="text" id="department_name">
				<span class="errCode">部门不能为空!</span>
			</li>
			<li class="wrap_one_single">
				<label for="superior_department">上级部门</label>
				<select class="superior_department" placeholder="请选择" id="superior_department" style="float: left;">
					<option value="">请选择</option>
					<option value="-1">没有上级部门</option>
					<option value="1">一级部门1</option>
					<option value="2">一级部门2</option>
				</select>
				<span class="errCode">上级部门不能为空!</span>
			</li>
			<!-- <li class="wrap_one_single">
				<label for="first_department">一级部门</label>
				<input type="text" id="first_department">
				<span class="errCode">一级部门不能为空!</span>
			</li> -->
			<!-- <li class="wrap_one_single">
				<label for="executive_director">部门长</label>
				<select class="executive_director" placeholder="请选择" id="executive_director" style="float: left;">
					<option value="">请选择</option>
					<option value="张三">张三</option>
				</select>
				<span class="errCode">部门长不能为空!</span>
			</li> -->
			<li>
				<label for="">权限设置</label>
				<select name="" class="permissions_sele">
					<option value="默认权限">默认权限</option>
					<option value="复制权限">复制权限</option>
					<option value="手动设置">手动设置</option>			
				</select>
				<div class="permissions_li1" style="margin: 22px 0 0 84px; display: none;">
					<select class="mt-multiselect btn btn-default manual_settings" multiple="multiple" data-clickable-groups="true" data-collapse-groups="true" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
						<optgroup label="123">
							<option value="33">33</option>	
							<option value="22">22</option>
						</optgroup>
						<optgroup label="456">
							<option value="44">44</option>	
							<option value="55">55</option>
						</optgroup>
					</select>
				</div>
				<div class="permissions_li2" style="margin: 22px 0 0 84px; display: none;">
					<select name="" class="copy_object" style="width: 105px;">
						<option value="">复制类型</option>
						<option value="部门">部门</option>
						<option value="人员">人员</option>
					</select>
					<select name="" class="copy_permission"></select>
				</div>
			</li>
		</ul>
		<div class="mask_btn">
			<button onclick="$('.mask_box').hide()">取消</button>
			<button class="save_department">确认</button>
			<input type="hidden" class="permissions_id">
		</div>
	</div>
</div>
<!-- 添加人员 -->
<div class="add_staff_mask">
	<div class="add_staff_dialog">
		<svg t="1588919283810" class="icon close_class" onclick="$('.add_staff_mask').hide()" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4128" width="15" height="15"><path d="M1001.952 22.144c21.44 21.44 22.048 55.488 1.44 76.096L98.272 1003.36c-20.608 20.576-54.592 20-76.096-1.504-21.536-21.44-22.048-55.488-1.504-76.096L925.824 20.672c20.608-20.64 54.624-20 76.128 1.472" p-id="4129" fill="#707070"></path><path d="M22.176 22.112C43.616 0.672 77.6 0.064 98.24 20.672L1003.392 925.76c20.576 20.608 20 54.592-1.504 76.064-21.44 21.568-55.488 22.08-76.128 1.536L20.672 98.272C0 77.6 0.672 43.584 22.176 22.112" p-id="4130" fill="#707070"></path></svg>
		<p class="staff_title"></p>
		<ul class="department_list">
			<li class="wrap_one_single">
				<label for="email_sele">邮箱</label>
				<input type="text" id="email_sele">
				<span class="errCode">邮箱不能为空!</span>
			</li>
			<li class="wrap_one_single">
				<label for="name_input">姓名</label>
				<input type="text" id="name_input">
				<span class="errCode">姓名不能为空!</span>
			</li>
			<li>
				<label for="english_name">英文名</label>
				<input type="text" id="english_name">
			</li>
			<li>
				<label for="dingding_id">钉钉ID</label>
				<input type="text" id="dingding_id">
			</li>
			<li class="wrap_one_single">
				<label for="post_sele">岗位</label>
				<input type="text" id="post_sele">
				<span class="errCode">岗位不能为空!</span>
			</li>
			<li class="wrap_one_single">
				<label for="title_input">职称</label>
				<select name="" id="title_input">
					<option value="">请选择</option>
					<option value="1">专员</option>
					<option value="2">主管</option>
					<option value="3">经理</option>
					<option value="4">总监</option>
					<option value="5">副总裁</option>
					<option value="6">总裁</option>
					<option value="7">CEO</option>
				</select>
				<span class="errCode">职称不能为空!</span>
			</li>
			<li class="wrap_one_single">
				<label for="department_sele">部门</label>
				<select name="" class="department_sele" id="department_sele">
					<option value="">请选择</option>
					<option value="1">11</option>
					<option value="2">22</option>
					<option value="3">33</option>
				</select>
				<span class="errCode">部门不能为空!</span>
			</li>
			
			<li>
				<label for="password_input">新密码</label>
				<input type="password" id="password_input">
			</li>
			<li>
				<label for="confirm_password_input">确认新密码</label>
				<input type="password" id="confirm_password_input">
			</li>
			<li>
				<label for="status_sele">状态</label>
				<select name="" id="status_sele">
					<option value="1">有效</option>
					<option value="0">冻结</option>		
				</select>
			</li>
			<li>
				<label for="">权限设置</label>
				<select name="" class="permissions_sele">
					<option value="默认权限">默认权限</option>
					<option value="复制权限">复制权限</option>
					<option value="手动设置">手动设置</option>
				</select>
				<div class="permissions_li1" style="margin: 22px 0 0 84px; display: none;">
					<select class="mt-multiselect btn btn-default manual_settings" multiple="multiple" data-clickable-groups="true" data-collapse-groups="true" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
						<optgroup label="aaa">
							<option value="aa">aa</option>
							<option value="bb">bb</option>
							<option value="cc">cc</option>
							<option value="dd">dd</option>
						</optgroup>
						<optgroup label="bbb">
							<option value="aa">aa</option>
							<option value="bb">bb</option>
							<option value="cc">cc</option>
							<option value="dd">dd</option>
						</optgroup>
					</select>
				</div>
				<div class="permissions_li2" style="margin: 22px 0 0 84px; display: none;">
					<select name="" class="copy_object" style="width: 85px;">
						<option value="">复制类型</option>
						<option value="部门">部门</option>
						<option value="人员">人员</option>
					</select>
					<select name="" class="staff_copy_permission">
						<option value="">请选择</option>
						<option value="1">113</option>
					</select>
				</div>
			</li>
		</ul>
		<div class="mask_btn">
			<button onclick="$('.add_staff_mask').hide()">取消</button>
			<button class="save_staff">确认</button>
			<input type="hidden" class="staff_id">
		</div>
	</div>
</div>
<!-- 新建/编辑岗位 -->
<div class="add_Jobs_mask">
	<div class="add_Jobs_dialog">
		<svg t="1588919283810" class="icon close_class" onclick="$('.add_Jobs_mask').hide()" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4128" width="15" height="15"><path d="M1001.952 22.144c21.44 21.44 22.048 55.488 1.44 76.096L98.272 1003.36c-20.608 20.576-54.592 20-76.096-1.504-21.536-21.44-22.048-55.488-1.504-76.096L925.824 20.672c20.608-20.64 54.624-20 76.128 1.472" p-id="4129" fill="#707070"></path><path d="M22.176 22.112C43.616 0.672 77.6 0.064 98.24 20.672L1003.392 925.76c20.576 20.608 20 54.592-1.504 76.064-21.44 21.568-55.488 22.08-76.128 1.536L20.672 98.272C0 77.6 0.672 43.584 22.176 22.112" p-id="4130" fill="#707070"></path></svg>
		<p class="Jobs_title"></p>
		<ul class="department_list">
			<li class="wrap_one_single">
				<label for="Jobs_input">岗位</label>
				<input type="text" id="Jobs_input">
				<span class="errCode">岗位不能为空!</span>
			</li>
			<li class="wrap_one_single">
				<label for="Jobs_title_sele">职称</label>
				<select class="Jobs_title_sele" placeholder="请选择" id="Jobs_title_sele">
					<option value="">请选择</option>
					<option value="1">专员</option>
					<option value="2">主管</option>
					<option value="3">经理</option>
					<option value="4">总监</option>
					<option value="5">副总裁</option>
					<option value="6">总裁</option>
					<option value="7">CEO</option>
				</select>
				<span class="errCode">职称不能为空!</span>
			</li>
			<li class="wrap_one_single">
				<label for="Jobs_department">部门</label>
				<select class="Jobs_department" placeholder="请选择" id="Jobs_department">
					<option value="">请选择</option>
					<option value="1">部门1</option>
					<option value="2">部门2</option>
					<option value="3">部门3</option>
					<option value="4">部门4</option>
				</select>
				<span class="errCode">部门不能为空!</span>
			</li>
			
			<li>
				<label for="">权限设置</label>
				<select name="" class="permissions_sele">
					<option value="默认权限">默认权限</option>
					<option value="复制权限">复制权限</option>
					<option value="手动设置">手动设置</option>
				</select>
				<div class="permissions_li1" style="margin: 22px 0 0 84px; display: none;">
					<select class="mt-multiselect btn btn-default manual_settings" multiple="multiple" data-clickable-groups="true" data-collapse-groups="true" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
						<optgroup label="aaa">
							<option value="aa">aa</option>
							<option value="bb">bb</option>
							<option value="cc">cc</option>
							<option value="dd">dd</option>
						</optgroup>
						<optgroup label="bbb">
							<option value="aa">aa</option>
							<option value="bb">bb</option>
							<option value="cc">cc</option>
							<option value="dd">dd</option>
						</optgroup>
					</select>
				</div>
				<div class="permissions_li2" style="margin: 22px 0 0 84px; display: none;">
					<select name="" class="copy_object" style="width: 85px;">
						<option value="">复制类型</option>
						<option value="部门">部门</option>
						<option value="人员">人员</option>
					</select>
					<select name="" class="Jobs_copy_permission" style="width: 120px;">
						<option value="">人员</option>
					</select>
				</div>
			</li>
		</ul>
		<div class="mask_btn">
			<button onclick="$('.add_Jobs_mask').hide()">取消</button>
			<button class="save_Jobs">确认</button>
			<input type="hidden" class="Jobs_id">
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
	</div>
<script>
	$(document).ready(function(){
		
		/* 获取部门信息 */
		var id1,id2,id3;
		function getSubDepartments (item,id){
			$.ajax({
			    type: "POST",
				url: "http://www.vl.test/management/getSubDepartments",
				async:false,
				data: {
					parent_id:id
				},
				success: function (res) {
					if(item == "一级"){
						$("#department1").empty();
						$.each(res.data, function (index, value) {
							$("#department1").append("<option value='" + value.id + "'>" + value.display_name + "</option>");
						})
					}else if(item == "二级"){
						$("#department2").empty();
						$('#department2').append("<option value='-1'>请选择</option>");
						$.each(res.data, function (index, value) {
							$('#department2').append("<option value='" + value.id + "'>" + value.display_name + "</option>");
						})
					}else if(item == "三级"){
						$("#department3").empty();
						$('#department3').append("<option value='-1'>请选择</option>");
						$.each(res.data, function (index, value) {
							$('#department3').append("<option value='" + value.id + "'>" + value.display_name + "</option>");
						})
					}
					
				},
				error: function(err) {
					console.log(err)
				}
			});
			
		}
		getSubDepartments('一级',0)
		$("#department1").on('change',function(){
			getSubDepartments('二级',$(this).val());
			$("#department3").empty().append("<option value='-1'>请选择</option>");
			getDepartmentDataFun($(this).val());
			reloadData();
		})
		//实时改变table数据
		function reloadData(){
			var jobsPage = jobsTableObj.page();
			jobsTableObj.clear();//清理原数据
			jobsTableObj.rows.add(role_info); //添加新数据
			jobsTableObj.page(jobsPage).draw( false );
			var staffPage = staffTableObj.page();
			staffTableObj.clear();//清理原数据
			staffTableObj.rows.add(user_info); //添加新数据
			staffTableObj.page(staffPage).draw( false );
		}
		$("#department2").on('change',function(){
			getSubDepartments('三级',$(this).val());
			getDepartmentDataFun($(this).val());
			reloadData();
		})
		$("#department3").on('change',function(){
			getDepartmentDataFun($(this).val());
			reloadData();
		})
		//获取当前用户所属的部门
		$.ajax({
		    type: "POST",
			url: "http://www.vl.test/management/getUserDepartmentIds",
			async:false,
			data: {
				user_id: 1
			},
			success: function (res) {
				id1 = res.data.dept_1;
				id2 = res.data.dept_2;
				id3 = res.data.dept_3;
				getSubDepartments('二级',id1);
				getSubDepartments('三级',id2);
			},
			error: function(err) {
				console.log(err)
			},
		});
		let ids = null , role_info = [] , user_info = [];
		
		//获取选中部门的信息
		function getDepartmentDataFun(parent_id){
			let ids = null;
			if( parent_id == null){
				if(id3 != -1){
					ids = id3
				}else if(id2 != -1){
					ids = id2
				}else if(id1 != -1){
					ids = id1
				}
			}else{
				ids = parent_id
			}
			
			$.ajax({
			    type: "POST",
				url: "http://www.vl.test/management/getDepartmentData",
				async:false,
				data: {
					department_id: ids
				},
				success: function (res) {
					$('.dept_text').text(res.dept_info.dept);
					$('.sup_dept').text(res.dept_info.sup_dept);
					$('.rank_one_dept').text(res.dept_info.rank_one_dept);
					$('.leader').text(res.dept_info.leader);
					$('.permission').text(res.dept_info.permission);
					role_info = res.role_info;
					user_info = res.user_info;
				},
				error: function(err) {
					console.log(err)
				},
			});
			
		}
		getDepartmentDataFun(null);
		//部门初始化赋值
		$("#department1").val(id1).select2();
		$('#department2').val(id2);
		$('#department3').val(id3);
		
		$('#department1').select2({
			tags:false,
		});
		$('#superior_department').select2({
			tags:false,
		});
		/* $('#executive_director').select2({
			tags:false,
		}); */
		$('.copy_permission').select2({
			tags:false,
		});
		//部门
		$('.department_sele').select2({
			tags:false,
		});
		//人员复制权限
		$('.staff_copy_permission').select2({
			tags:false,
		});
		//岗位复制权限
		$('.Jobs_copy_permission').select2({
			tags:false,
		});
		//岗位部门
		$('#Jobs_department').select2({
			tags:false,
		});
		/* tab切换 */
		$(".nav_list li").click(function(){
			$(this).attr("class","nav_active").siblings().removeClass('nav_active');
			$('.tab_content .tab-item').eq($(this).index()).show().siblings().hide()
		})
		
		/* *******************************部门信息 ***********************************/
		//清空添加部门信息
		function clearPermissions(){
			$('#department_name').val("");
			$("#superior_department").select2("val", " ");
			/* $("#executive_director").select2("val", " "); */
			$('.permissions_sele').val("默认权限");
			$('.permissions_li1').hide();
			$('.permissions_li2').hide();
			$('.copy_object').val("");
			$(".copy_permission").select2("val", " "); 
			$('select[multiple="multiple"]').multiselect('clearSelection');
		}
		function getDepartmentInfo (){
			$.ajax({
			    type: "POST",
				url: "http://www.vl.test/management/createDepartmentInfo",
				async:false,
				success: function (res) {
					$("#superior_department").empty();
					$("#superior_department").append("<option value='-1'>请选择</option>");
					$.each(res.sup_dept, function (index, value) {
						$("#superior_department").append("<option value='" + value.id + "'>" + value.display_name + "</option>");
					})
				},
				error: function(err) {
					console.log(err)
				},
			});
		}
		//新建部门
		$('#handleAddPermissions').on('click',function(){
			clearPermissions();
			$('.mask_box').show();
			$('.department_title').text('新建部门');
			$('.permissions_id').val('');
			getDepartmentInfo();
			
		})
		//编辑部门
		$('#handleEditPermissions').on('click',function(){
			clearPermissions();
			$('.mask_box').show();
			$('.department_title').text('编辑部门');
			$('.permissions_id').val(1)
			getDepartmentInfo()
		})
		//权限设置
		$('.permissions_sele').on('change',function(){
			if($(this).val() == '复制权限'){
				$('.permissions_li2').show();
				$('.permissions_li1').hide();
			}else if($(this).val() == '手动设置'){
				$('.permissions_li1').show();
				$('.permissions_li2').hide();
			}else{
				$('.permissions_li1').hide();
				$('.permissions_li2').hide();
			}
		})
		//设置一级部门
		function settingSuperiorDepartmen(){
			if($("#superior_department").val() == -1 && $('#department_name').val()){
				//$('#first_department').val($('#department_name').val())
			}else{
				/* $.ajax({
				    type: "POST",
					url: "",
					data: {
					},
					success: function (res) {
						$('#first_department').val(res.data)
					},
					error: function(err) {
						console.log(err)
					}
				}); */	
			}
		}
		//部门
		$('#department_name').on('input',function(){
			settingSuperiorDepartmen()
			if($(this).val() == ""){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//上级部门
		$('#superior_department').on('change',function(){
			settingSuperiorDepartmen();
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//复制部门权限
		$('.copy_object').on('change',function(){
			$('.copy_permission').empty();
			if($(this).val() == "部门"){
				$.ajax({
				    type: "POST",
					url: "http://www.vl.test/management/getAllDepartments",
					success: function (res) {
						$(".copy_permission").append("<option value='-1'>请选择</option>");
						$.each(res.departments, function (index, value) {
							$(".copy_permission").append("<option value='" + value.id + "'>" + value.display_name + "</option>");
						})
					},
					error: function(err) {
						console.log(err)
					}
				});
			}else if($(this).val() == "人员"){
				$.ajax({
				    type: "POST",
					url: "http://www.vl.test/management/getAllUsers",
					success: function (res) {
						$(".copy_permission").append("<option value='-1'>请选择</option>");
						$.each(res.departments, function (index, value) {
							$(".copy_permission").append("<option value='" + value.id + "'>" + value.name + "</option>");
						})
					},
					error: function(err) {
						console.log(err)
					}
				});
			}
		})
		//一级部门
		/* $('#first_department').on('input',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		}) */
		//部门长
		/* $('#executive_director').on('change',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		}) */
		//权限
		$('.permissions_sele').on('input',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//新建部门保存
		$('.save_department').on('click',function(){
			if($('#department_name').val() == ''){
				$('#department_name').parent().find('.errCode').show();
				return
			}else{
				$('#department_name').parent().find('.errCode').hide();
			}
			if($("#superior_department").select2("val") == null){
				$('#superior_department').parent().find('.errCode').show();
				return
			}else{
				$('#superior_department').parent().find('.errCode').hide();
			}
			/* if($('#first_department').val() == ""){
				$('#first_department').parent().find('.errCode').show();
				return
			}else{
				$('#first_department').parent().find('.errCode').hide();
			} */
			/* if($("#executive_director").select2("val") == null){
				$('#executive_director').parent().find('.errCode').show();
				return
			}else{
				$('#executive_director').parent().find('.errCode').hide();
			} */
			
			if($('.permissions_sele').val() == ""){
				$('#permissions_sele').parent().find('.errCode').show();
				return
			}else{
				$('.permissions_sele').parent().find('.errCode').hide();
			}
			if($('.permissions_id').val() != ""){
				console.log('编辑')
			}else{
				console.log('新增')
			}
		})
		/* 添加人员 */
		//清空添加人员信息
		function clearStaffVal(){
			$('#email_sele').val("");
			$('#name_input').val("");
			$('#english_name').val("");
			$('#dingding_id').val("");
			$('#post_sele').val("");
			$('#title_input').val("");
			$("#department_sele").select2("val", " "); 
			$('#password_input').val("");
			$('#confirm_password_input').val("");
			$('#status_sele').val("1");
			$('.permissions_sele').val("默认权限");
			$('.permissions_li1').hide();
			$('.permissions_li2').hide();
			$('.copy_object').val("");
			$(".staff_copy_permission").select2("val", " "); 
			$('select[multiple="multiple"]').multiselect('clearSelection');
		}
		$('.handleAddStaff').on('click',function(){
			clearStaffVal()
			$('.add_staff_mask').show()
			$('.staff_title').text('新建人员');		
			$.ajax({
				type:"post",
				url:"",
				async: true,
				data:{
					//"sap_seller_id": sap_seller_id,
				},
				success:function(res){
					$.each(res[0], function (index, value) {
						$("#asin-select").append("<option id='"+value.marketplaceid+"' fulfillment='"+value.fulfillment+"' commission='"+value.commission+"' cost='"+value.cost+"' rating='"+value.rating+"' value='"+value.asin + "' sku='"+value.sku+"' sku_status='"+value.sku_status+"' reviews='"+value.reviews+"'>" + value.country+" — "+ value.asin + "</option>");
					})
					$('select[multiple="multiple"]').multiselect('refresh');
				},
				error:function(err){
					console.log(err)
				},
			}); 
			
		})
		//邮件
		$('#email_sele').on('input',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//姓名
		$('#name_input').on('input',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//岗位
		$('#post_sele').on('input',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//职称
		$('#title_input').on('input',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//部门
		$("#department_sele").on("select2:select",function(e){
		　　if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		
		//保存人员
		$('.save_staff').on('click',function(){
			if($('#email_sele').val() == ''){
				$('#email_sele').parent().find('.errCode').show();
				return
			}else{
				$('#email_sele').parent().find('.errCode').hide();
			}
			if($('#name_input').val() == ''){
				$('#name_input').parent().find('.errCode').show();
				return
			}else{
				$('#name_input').parent().find('.errCode').hide();
			}
			if($('#post_sele').val() == ''){
				$('#post_sele').parent().find('.errCode').show();
				return
			}else{
				$('#post_sele').parent().find('.errCode').hide();
			}
			if($('#title_input').val() == ''){
				$('#title_input').parent().find('.errCode').show();
				return
			}else{
				$('#title_input').parent().find('.errCode').hide();
			}
			if($("#department_sele").select2("val") == null){
				$('#department_sele').parent().find('.errCode').show();
				return
			}else{
				$('#department_sele').parent().find('.errCode').hide();
			}
			
		})
		/* 添加岗位 */
		//清空添加部门信息
		function clearJobsVal(){
			$('#Jobs_input').val("");
			$('#Jobs_title_sele').val("");
			$("#Jobs_department").select2("val", " ");
			$('.permissions_sele').val("默认权限");
			$('.permissions_li1').hide();
			$('.permissions_li2').hide();
			$('.copy_object').val("");
			$(".copy_permission").select2("val", " "); 
			$('select[multiple="multiple"]').multiselect('clearSelection');
		}
		$('.handleAddJobs').on('click',function(){
			clearJobsVal();
			$('.add_Jobs_mask').show();
			$('.Jobs_title').text('新建岗位');
		})
		//岗位
		$('#Jobs_input').on('input',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//职称
		$('#Jobs_title_sele').on('change',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//部门
		$('#Jobs_department').on('change',function(){
			if($(this).val() == ''){
				$(this).parent().find('.errCode').show();
			}else{
				$(this).parent().find('.errCode').hide();
			}
		})
		//保存岗位
		$('.save_Jobs').on('click',function(){
			if($('#Jobs_input').val() == ''){
				$('#Jobs_input').parent().find('.errCode').show();
				return
			}else{
				$('#Jobs_input').parent().find('.errCode').hide();
			}
			if($('#Jobs_title_sele').val() == ""){
				$('#Jobs_title_sele').parent().find('.errCode').show();
				return
			}else{
				$('#Jobs_title_sele').parent().find('.errCode').hide();
			}
			if($('#Jobs_department').val() == ""){
				$('#Jobs_department').parent().find('.errCode').show();
				return
			}else{
				$('#Jobs_department').parent().find('.errCode').hide();
			}
		})
		
		//禁止警告弹窗弹出
		$.fn.dataTable.ext.errMode = 'none';
		jobsTableObj = $("#jobsTable").DataTable({
			bLengthChange:false,
			ordering: true,
			dispalyLength: 20, // default record count per page
			paging: true,  // 是否显示分页
			info: false,// 是否表格左下角显示的文字
			serverSide: false,//是否所有的请求都请求服务器	
			searching : false,
			data:role_info,
			columns: [
				{data: 'display_name'},
				{data: 'title'},
				{data: 'dept'},
				{data: 'permission'},
				{
					data: null, 
					render: function(data, type, row, meta) {
					 	var content = '<button class="btn btn-sm green-meadow">编辑</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							clearJobsVal()
							$('.Jobs_id').val(cellData.user_id);
							$('.Jobs_title').text('编辑岗位');
							$('.add_Jobs_mask').show();
						});
					}
				},
			],
			columnDefs: [
				{ "bSortable": false, "aTargets": [4]},
			],
		})
		
		
		staffTableObj = $("#staffTable").DataTable({
			bLengthChange:false,
			ordering: true,
			dispalyLength: 20, // default record count per page
			paging: true,  // 是否显示分页
			info: false,// 是否表格左下角显示的文字
			serverSide: false,//是否所有的请求都请求服务器	
			searching : false,
			data: user_info,
			columns: [
				{data: 'user_id'},
				{data: 'name'},
				{data: 'english_name'},
				{data: 'role'},
				{data: 'title'},
				{data: 'dingtalk_id'},
				{
					data: 'status',
					render: function (data, type, row, meta) {
						//let title = row.reselling_switch == 0 ? "Hijacker monitoring turned off.":"Hijacker monitoring turned on."
						var html = '<label class="switch"> <input type="checkbox" class="switch_input" checked value=""><span>'+data+'</span></label>';
						return html;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						if(rowData.status == 0){
							$(cell).find('.switch_input').removeAttr("checked");
						}else {
							$(cell).find('.switch_input').attr("checked");
						}
						let element = $(cell).find('.switch_input')
						element.click(function(){
							let reselling_switch = rowData.status;
							reselling_switch == 0 ? reselling_switch = 1 : reselling_switch = 0
							$.ajax({
								type:"post",
								url:"",
								data:{
									"id": rowData.id,
									"reselling_switch": reselling_switch
								},
								success:function(res){
									/* if(res.status == 1){
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
									} */	
								},
								error:function(err){
									console.log(err)
								},
							});
						})
					},
				},
				{
					data: null, 
					render: function(data, type, row, meta) {
					 	var content = '<button class="btn btn-sm green-meadow">编辑</div>';
					 	return content;
					},
					createdCell: function (cell, cellData, rowData, rowIndex, colIndex) {
						$(cell).on( 'click', function () {
							clearStaffVal();
							$('.staff_id').val(cellData.user_id);
							$('.add_staff_mask').show();
							$('.staff_title').text('编辑人员')
						});
					}
				},
			],
			columnDefs: [
				{ "bSortable": false, "aTargets": [7]},
			],
		})
		
		
	})
</script>
@endsection