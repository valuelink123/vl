@extends('layouts.layout')
@section('label', 'CPFR协同补货')
@section('content')
<style>
	.content{
		background: #fff;
		border-radius: 10px !important;
		margin: 20px 0;
		padding: 20px;
	}
	.title{
		padding: 0;
		margin: 0;
		font-size: 18px;
		color: #cc6600;
		font-weight: bold;
	}
	.sub_title{
		margin: 0;
	}
	.table_box{
		background: #eff5fa;
		padding: 15px;
		border-radius: 10px !important;
		margin: 20px 0;
	}
	.table>thead:first-child>tr:first-child>th{
		background: #cddef3;
	}
	.table-striped>tbody>tr td{
		background: #fff;
	}
	.table-striped>tbody>tr:nth-child(2) td{
		background: #eee;
	}
	.print_box{
		text-align: center;
	}
	.print_btn{
		background:linear-gradient(to bottom, #ffe9b5,#d6a52c);
		border: 1px solid #6a83b4;
		border-radius: 5px !important;
		color: #1b1ba6;
		font-weight: bold;
		padding: 2px 10px;
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
		z-index:9999;
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
		z-index:9999;
	}
	.error_mask .mask_text{
		color: #f56c6c !important;
	}
</style>
<div class="content">
	<p class="title">Print Labels for Individual Products</p>
	<p class="sub_title">Specify the number of labels to print for each SKU and click the "Print Item Labels" button.</p>
	<div class="table_box">
		<table class="table table-striped table-bordered" id="thetable">
		    <thead>
		    <tr>
		        <th>Merchant SKU</th>
		        <th>Title</th>
		        <th style="text-align: right;">Number of labels to print</th>
		    </tr>
		    </thead>
			<tbody>
				<tr>
		            <td class="barCode_Num"></td>
		            <td class="barcode_title"></td>
		            <td style="text-align: right;"><input type="text" class="barcode_input" value="" onkeyup="value=value.replace(/^(0+)|[^\d]+/g,'')"></td>
		        </tr>
				<tr>
					<td>Total</td>
					<td colspan="2" style="text-align: right;"><span class="total_num"></span></td>
				</tr>
		    </tbody>
		</table>
		<div class="print_box">
			<label for="" style="font-weight: bold;">Paper/Sticker Type:</label>
			<select>
				<option value="">21-up labels 63.5 * 38.1 mm on A4</option>
				<option value="">24-up labels 63.5 * 33.9 mm on A4</option>
				<option value="">24-up labels 64.6 * 33.8 mm on A4</option>
				<option value="">24-up labels 66 * 33.9 mm on A4</option>
				<option value="">24-up labels 66 * 35 mm on A4</option>
				<option value="">24-up labels 70 * 36 mm on A4</option>
				<option value="">24-up labels 70 * 37 mm on A4</option>
				<option value="">27-up labels 63.5 * 29.6 mm on A4</option>
				<option value="">30-up labels 1"*2-5/8" on US Letter</option>
				<option value="">40-up labels 52.5 * 29.7 mm on A4</option>
				<option value="">44-up labels 48.5 * 25.4 mm on A4</option>
			</select>
			<button class="print_btn">
				<i class="glyphicon glyphicon-print"></i>
				Print Item Labels
			</button>
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
</div>
<script>
	$(document).ready(function(){
		let url = window.location.href
		let ids = url.substr(url.lastIndexOf('=') + 1);
		
		function getBarcodeInfo(obj,url) {
			 $.ajax({
			  	type:"post",
			  	url:"http://10.10.42.14/vl/public/shipment/getBarcodepub",
			  	data:{
					"shipment_requests_id": ids
			  	},
			  	success:function(res){
					$('.barCode_Num').text(res.fnsku)
					$('.barcode_title').text(res.title);
					$('.barcode_input').val(res.quantity);
					$('.total_num').text(res.quantity);
			  		/* if(res.status == 1){
			  			$('.barCode_Num').text(res.fnsku)
			  			$('.barcode_title').text(res.title);
			  		}else{
			  			$('.error_mask_text').text(res.msg)
			  			$('.error_mask').fadeIn(1000);
			  			setTimeout(function(){
			  				$('.error_mask').fadeOut(1000);
			  			},2000)
			  		} 	 */
			  	},
			  	error:function(err){
			  		console.log(err)
			  	},
			  });
		}
		getBarcodeInfo();
		//Total赋值
		$('.barcode_input').on('input',function(){
			$('.total_num').text($(this).val())
		})
		//点击下载PDF
		$('.print_btn').on('click',function(){
			let title = $('.barcode_title').text();
			let code = $('.barCode_Num').text();
			let total =$('.total_num').text();
			console.log(title,code,total)
		})
	})
</script>
@endsection