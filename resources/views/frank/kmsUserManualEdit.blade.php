@extends('layouts.layout')
@section('crumb')
	@include('layouts.crumb', ['crumbs'=>[['KMS', '/kms/productguide'], ['Product Manuals', '/kms/usermanual'], 'Data Update']])
@endsection
@section('content')

	@include('frank.common')

	<div class="container-top-msg">
		<div class="row">
			<div class="col-xs-12">
				@if($msg = $errors->dataImport->first('success'))
					<div class="alert alert-success"><strong>Success !</strong> {!! $msg !!}</div>
				@elseif($msg = $errors->dataImport->first('error'))
					<div class="alert alert-danger"><strong>Error !</strong> {!! $msg !!}</div>
				@endif
			</div>
		</div>
	</div>

	<h1 class="page-title font-red-intense"> Single Edit
		<small></small>
	</h1>

	<div class="portlet light bordered">
		<div class="portlet-body">

			<form method="post" class="row" enctype="multipart/form-data" action="/kms/usermanual/update">
				<div class="col-lg-4">
					<input type="hidden" name="id" id="id" value="{!! $data['id'] !!}"/>
					<div class="form-group">
						<label>
							Item Group
							<input required autocomplete="off" class="xform-autotrim form-control" placeholder="Item Group" name="item_group" id="item_group" value="{!! $data['item_group'] !!}"/>
						</label>
					</div>

					<div class="form-group">
						<label>
							Brand
							<input required autocomplete="off" class="xform-autotrim form-control" placeholder="Brand" name="brand" id="brand" value="{!! $data['brand'] !!}"/>
						</label>
					</div>

					<div class="form-group">
						<label>
							Item Model
							<input required autocomplete="off" class="xform-autotrim form-control" placeholder="Item Model" name="item_model" id="item_model" value="{!! $data['item_model'] !!}"/>
						</label>
					</div>

					<br/>{!! csrf_field() !!}

					<button type="submit" class="btn btn-primary">Submit</button>

				</div>

				<div class="col-lg-4">

					<div class="form-group">
						<label>
							Manual Link
							<input pattern=".*\S+.*" autocomplete="off" class="xform-autotrim form-control" placeholder="Link" name="link" title="This field is required." value="{!! $data['link'] !!}"/>
							<input  type="file" style="margin-top: 5px;" name="uploadfile"/>
						</label>
					</div>

					<div class="form-group">
						<label>
							Note
							<input autocomplete="off" class="xform-autotrim form-control" placeholder="Note" name="note" value="{!! $data['note'] !!}"/>
						</label>
					</div>

				</div>

			</form>
		</div>
	</div>

	<script>

        new LinkageInput([item_group, brand, item_model], @json($itemGroupBrandModels))

	</script>

@endsection
