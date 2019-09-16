@extends('layouts.layout')
@section('label', 'User Accounts Manage')
@section('content')
    <h1 class="page-title font-red-intense"> User Accounts
        <small>Manager users and user's permissions.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">User Account Form</span>
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
                <form role="form" action="{{ url('user/'.$user['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" value="{{$user['email']}}" readonly disabled />
                            </div>
                        </div>

                        

                        <div class="form-group">
                            <label>User Name</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="name" id="name" value="{{$user['name']}}" required>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Roles</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <select name="roles[]" id="roles[]" class="mt-multiselect btn btn-default" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
									
									
                                    @foreach ($roles as $k => $v)
										 
										<option value="{{$k}}" {{ in_array($k,$userRole)?'selected':''}}>{{$v}}</option>
                                   				
                                    @endforeach
                                </select>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Sap Seller Id</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="sap_seller_id" id="sap_seller_id" value="{{$user['sap_seller_id']}}">
                            </div>
                        </div>
						<div class="form-group">
                            <label>BG</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="bg" id="bg" value="{{$user['ubg']}}">
                            </div>
                        </div>
						<div class="form-group">
                            <label>BU</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="bu" id="bu" value="{{$user['ubu']}}">
                            </div>
                        </div>
						
						
                        <div class="form-group">
                            <label>New Password</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="password" class="form-control" name="password" id="password" value="" placeholder="Leave blank to indicate no change">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Re-type New Password</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" value="">
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
