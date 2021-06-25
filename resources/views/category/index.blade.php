@extends('layouts.layout')
@section('label', 'Qa Category')
@section('content')
<link rel="stylesheet" href="css/zTreeStyle/zTreeStyle.css" type="text/css">
<script type="text/javascript" src="js/jquery.ztree.core.js"></script>
<style type="text/css">
	.dataTables_extended_wrapper .table.dataTable {
		margin: 0px !important;
	}

	table.dataTable thead th, table.dataTable thead td {
		padding: 10px 2px !important;}
	table.dataTable tbody th, table.dataTable tbody td {
		padding: 10px 2px;
	}
	th,td,td>span {
		font-size:12px !important;
		font-family:Arial, Helvetica, sans-serif;
	}
	.ztree * {font-size: 10pt;font-family:"Microsoft Yahei",Verdana,Simsun,"Segoe UI Web Light","Segoe UI Light","Segoe UI Web Regular","Segoe UI","Segoe UI Symbol","Helvetica Neue",Arial}
	.ztree{padding: 5px 0px 5px 0px;}
	.ztree li ul{ margin:0; padding:0}
	.ztree li {line-height:30px;}
	.ztree li a {width:100%;height:30px;padding-top: 0px; margin-bottom: 3px; border-bottom: 1px solid #f3f4f6;}
	.ztree li a:hover {text-decoration:none; background-color: #E7E7E7;}
	.ztree li a span.button.switch {visibility:hidden}
	.ztree.showIcon li a span.button.switch {visibility:visible}
	.ztree li a.curSelectedNode {background-color:#D4D4D4;border:0;height:30px;}
	.ztree li span {line-height:30px;}
	.ztree li span.button {margin-top: -7px;}
	.ztree li span.button.switch {width: 16px;height: 16px;}

	.ztree li a.level0 span {font-size: 150%;}
	.ztree li span.button {background-image:url("css/zTreeStyle/left_menuForOutLook.png"); *background-image:url("css/zTreeStyle/left_menuForOutLook.gif")}
	.ztree li span.button.switch.level0 {width: 20px; height:20px}
	.ztree li span.button.switch.level1 {width: 20px; height:20px}
	.ztree li span.button.noline_open {background-position: 0 0;}
	.ztree li span.button.noline_close {background-position: -18px 0;}
	.ztree li span.button.noline_open.level0 {background-position: 0 -18px;}
	.ztree li span.button.noline_close.level0 {background-position: -18px -18px;}
</style>
    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-dark">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject bold uppercase">Qa Category</span>
                    </div>
                </div>
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group">
                                <a data-target="#ajax" data-toggle="modal" href="{{ url('category/create?type=1')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add New
                                    <i class="fa fa-plus"></i>
                                </button>
                                </a>
                            </div>

							<div class="btn-group " style="float:right;">
								<form action="{{url('/category/import')}}" method="post" enctype="multipart/form-data">
									<div class="col-md-12">
										@permission('category-create')
										<div class="col-md-6">
											{{ csrf_field() }}
											<input type="file" name="importFile"  style="width: 90%;"/>
										</div>
										<div class="col-md-2">
											<button type="submit" class="btn blue" id="data_search">Import</button>
										</div>
										@endpermission
									</div>
								</form>
							</div>


                        </div>
                    </div>
                </div>
                <div class="portlet-body">

                    <div class="table-container" style=" border: 1px solid #f3f4f6;">
						<div style="background: #f3f4f6;height: 30px; line-height: 30px; font-weight: bold;">
							<div style="float: left; margin-left: 30px; line-height: 30px;">Category name</div>
							<div style="float: right; margin-right: 40px; line-height: 30px;">Actions</div>
						</div>
						<div style="clear: both;"></div>
						<div class="zTreeDemoBackground">
							<ul id="treeDemo" class="ztree"></ul>
						</div>
					</div>
                </div>

				<div class="table-toolbar" style="margin-top: 30px;">
					<div class="row">
						<div class="col-md-6">
							<div class="btn-group">
								<a data-target="#ajax" data-toggle="modal" href="{{ url('category/create?type=2')}}"><button id="sample_editable_1_2_new" class="btn sbold blue"> Add New
										<i class="fa fa-plus"></i>
									</button>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="portlet-body">

					<div class="table-container" style=" border: 1px solid #f3f4f6;">
						<div style="background: #f3f4f6;height: 30px; line-height: 30px; font-weight: bold;">
							<div style="float: left; margin-left: 30px; line-height: 30px;">Knowledge Category name</div>
							<div style="float: right; margin-right: 40px; line-height: 30px;">Actions</div>
						</div>
						<div style="clear: both;"></div>
						<div class="zTreeDemoBackground">
							<ul id="tree" class="ztree"></ul>
						</div>
					</div>
				</div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>


<div class="modal fade bs-modal-lg" id="ajax" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" >
			<div class="modal-body" >
				<img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading">
				<span>Loading... </span>
			</div>
		</div>
	</div>
</div>

<div class="modal fade bs-modal-sm" id="Delete" aria-hidden="true">
	<div class="modal-dialog modal-sm">
		<div class="modal-content" >
			<form action="{{ url('category/destroy') }}" method="POST" style="display: inline;">
				{{ method_field('DELETE') }}
				{{ csrf_field() }}
				<input type="hidden" class="cate_id" name="cate_id" value="">
			<div class="modal-header">
				<h4 class="modal-title">Delete！</h4>
			</div>
			<div class="modal-body" >
				Confirm whether to delete！
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-danger">Confirm</button>
			</div>
			</form>
		</div>
	</div>
</div>

<SCRIPT type="text/javascript">
	<!--
	var curMenu = null, zTree_Menu = null;
	var setting = {
		view: {
			showLine: false,
			showIcon: false,
			addDiyDom: addDiyDom,
		},
		data: {
			simpleData: {
				enable: true
			}
		}
	};

	var settings = {
		view: {
			showLine: false,
			showIcon: false,
			addDiyDom: addDiyDoms,
		},
		data: {
			simpleData: {
				enable: true
			}
		}
	};

	var zNodes =[

			<?php
			foreach($category_one as $key=>$val){
			?>
		{ id:"<?=$val['id']?>", pId:"<?=$val['category_pid']?>", name:"<?=$val['category_name']?>"},
			<?php
			}
			?>

	];

	var zNodesList =[

			<?php
			foreach($category_two as $key=>$val){
			?>
		{ id:"<?=$val['id']?>", pId:"<?=$val['category_pid']?>", name:"<?=$val['category_name']?>"},
		<?php
		}
		?>

	];

	function addDiyDom(treeId, treeNode) {
		var spaceWidth = 5;
		var switchObj = $("#" + treeNode.tId + "_switch"),
				icoObj = $("#" + treeNode.tId + "_ico");
		switchObj.remove();
		icoObj.before(switchObj);
		var spaceStr = "<span style='display: inline-block;width:" + (spaceWidth * treeNode.level)+ "px'></span> <span style='position:absolute;right:0px; margin-right: 40px;'><a data-target='#ajax' data-toggle='modal' href='category/"+treeNode.id+"/edit?type=1' style='text-align: center; width: 50px;background-color: #36c6d3;color: #ffffff; margin-right: 10px;' id='diyBtn2_" +treeNode.id+ "'>Edit</a>" +
				"<a data-target='#Delete' data-toggle='modal' style='text-align: center;width: 50px;background-color: #ed6b75;color: #ffffff;margin-right: 10px;' id='diyBtn1_" +treeNode.id+ "' onclick='del("+treeNode.id+")'>Delete</a> </span>";

		switchObj.before(spaceStr);

	}

	function addDiyDoms(treeId, treeNode) {
		var spaceWidth = 5;
		var switchObj = $("#" + treeNode.tId + "_switch"),
				icoObj = $("#" + treeNode.tId + "_ico");
		switchObj.remove();
		icoObj.before(switchObj);
		var spaceStr = "<span style='display: inline-block;width:" + (spaceWidth * treeNode.level)+ "px'></span> <span style='position:absolute;right:0px; margin-right: 40px;'><a data-target='#ajax' data-toggle='modal' href='category/"+treeNode.id+"/edit?type=2' style='text-align: center; width: 50px;background-color: #36c6d3;color: #ffffff; margin-right: 10px;' id='diyBtn2_" +treeNode.id+ "'>Edit</a>" +
				"<a data-target='#Delete' data-toggle='modal' style='text-align: center;width: 50px;background-color: #ed6b75;color: #ffffff;margin-right: 10px;' id='diyBtn1_" +treeNode.id+ "' onclick='del("+treeNode.id+")'>Delete</a> </span>";

		switchObj.before(spaceStr);

	}

	function del(id){
		$('.cate_id').val(id);
	}

	$(document).ready(function(){
		var treeObj = $("#treeDemo");
		$.fn.zTree.init(treeObj, setting, zNodes);
		zTree_Menu = $.fn.zTree.getZTreeObj("treeDemo");
		curMenu = zTree_Menu.getNodes();
		zTree_Menu.selectNode(curMenu);

		treeObj.hover(function () {
			if (!treeObj.hasClass("showIcon")) {
				treeObj.addClass("showIcon");
			}
		}, function() {
			treeObj.removeClass("showIcon");
		});
	});

	$(document).ready(function(){
		var treeObj = $("#tree");
		$.fn.zTree.init(treeObj, settings, zNodesList);
		zTree_Menu = $.fn.zTree.getZTreeObj("tree");
		curMenu = zTree_Menu.getNodes();
		zTree_Menu.selectNode(curMenu);

		treeObj.hover(function () {
			if (!treeObj.hasClass("showIcon")) {
				treeObj.addClass("showIcon");
			}
		}, function() {
			treeObj.removeClass("showIcon");
		});
	});

	$("#ajax").on("hidden.bs.modal",function(){
		$(this).find('.modal-content').html('<div class="modal-body"><img src="../assets/global/img/loading-spinner-grey.gif" alt="" class="loading"><span>Loading... </span></div>');
	});

	//-->
</SCRIPT>

@endsection
