@extends('layouts.layout')
@section('label', 'Edit Asin')
@section('content')
<h1 class="page-title font-red-intense"> Edit Asin
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
                <form role="form" action="{{ url('asin/'.$asin['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <input type="hidden" name="id" value="{{$asin['id']}}" />
                    <div class="form-body">
                        <div class="form-group">
                            <label>Asin</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="asin" name="asin" id="asin" value="{{$asin['asin']}}" required />
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Seller Sku</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Seller Sku" name="sellersku" id="sellersku" value="{{$asin['sellersku']}}" required />
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
										<option value="{{$site}}" <?php if($site==$asin['site']) echo 'selected';?>>{{$site}}</option>
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
									<option value="1" <?php if(1==$asin['status']) echo 'selected';?>>Normal</option>
									<option value="2" <?php if(2==$asin['status']) echo 'selected';?>>Plan to Eliminated</option>
									<option value="3" <?php if(3==$asin['status']) echo 'selected';?>>Eliminated</option>
									<option value="4" <?php if(4==$asin['status']) echo 'selected';?>>Removed</option>
									
								</select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Item NO.</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="item_no" id="item_no" value="{{$asin['item_no']}}" required>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Brand</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="brand" id="brand" value="{{$asin['brand']}}" required>
                            </div>
                        </div>
						
                        <div class="form-group">
                            <label>Brand Line</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="brand_line" id="brand_line" value="{{$asin['brand_line']}}" required>
                            </div>
                        </div>
						
						
						<div class="form-group">
                            <label>Seller</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="seller" id="seller" value="{{$asin['seller']}}" required>
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
                                        <option value="{{$user_id}}" <?php if($user_id==$asin['user_id']) echo 'selected';?>>{{$user_name}}</option>
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
    <div class="col-md-4">
        <div class="portlet light bordered" id="blockui_sample_1_portlet_body">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-bubble font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp sbold">How to use it?</span>
                </div>
            </div>
            <div class="portlet-body">
                The Email Address and Seller ID  Must  Use address registered in Seller Central!
                <p><p>In order to protect your account information, you can use forwarding or collection to forward the mail to another receiving mailbox to authorize us to receive mail.
            </div>
        </div>

    </div>

    </div>


@endsection
