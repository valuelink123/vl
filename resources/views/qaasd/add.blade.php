@extends('layouts.layout')
@section('label', 'Add Asin')
@section('content')
    <h1 class="page-title font-red-intense"> Add Asin
        <small>Configure your Asin.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Asin Form</span>
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
                <form role="form" action="{{ url('asin') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Asin</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="asin" name="asin" id="asin" value="{{old('asin')}}" required />
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Seller Sku</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Seller Sku" name="sellersku" id="sellersku" value="{{old('sellersku')}}" required />
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Site</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control form-filter input-sm" name="site">
									@foreach (getAsinSites() as $site)
										<option value="{{$site}}" <?php if($site==old('site')) echo 'selected';?>>{{$site}}</option>
									@endforeach
								</select>
                            </div>
                        </div>
						
						
						<div class="form-group">
                            <label>Status</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control form-filter input-sm" name="status">
									<option value="1" <?php if(1==old('status')) echo 'selected';?>>Normal</option>
									<option value="2" <?php if(2==old('status')) echo 'selected';?>>Plan to Eliminated</option>
									<option value="3" <?php if(3==old('status')) echo 'selected';?>>Eliminated</option>
									<option value="4" <?php if(4==old('status')) echo 'selected';?>>Removed</option>
									
								</select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Item NO.</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="item_no" id="item_no" value="{{old('item_no')}}" required>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Brand</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="brand" id="brand" value="{{old('brand')}}" required>
                            </div>
                        </div>
						
                        <div class="form-group">
                            <label>Brand Line</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="brand_line" id="brand_line" value="{{old('brand_line')}}" required>
                            </div>
                        </div>
						
						
						<div class="form-group">
                            <label>Seller</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="seller" id="seller" value="{{old('seller')}}" required>
                            </div>
                        </div>
						
                        <div class="form-group">
                            <label>User</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control" name="user_id" id="user_id" required>

                                    @foreach ($users as $user_id=>$user_name)
                                        <option value="{{$user_id}}" <?php if($user_id==old('user_id')) echo 'selected';?>>{{$user_name}}</option>
                                    @endforeach
                                </select>
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


    </div>


@endsection
