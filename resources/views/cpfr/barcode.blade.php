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
		            <td class="barCode_Num">MOHG6666</td>
		            <td class="barcode_title">Mooka Dehumidifier</td>
		            <td style="text-align: right;"><input type="text" class="barcode_input" value="14" onkeyup="value=value.replace(/^(0+)|[^\d]+/g,'')"></td>
		        </tr>
				<tr>
					<td>Total</td>
					<td colspan="2" style="text-align: right;"><span class="total_num">14</span></td>
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
</div>
<script>
	$(document).ready(function(){
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