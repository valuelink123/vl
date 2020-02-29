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
                <form role="form" action="{{ url('user') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control" value="{{old('email')}}" required />
                            </div>
                        </div>

                        

                        <div class="form-group">
                            <label>User Name</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" required>
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
										 
										<option value="{{$k}}">{{$v}}</option>
                                   				
                                    @endforeach
                                </select>
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Sap Seller Id</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" name="sap_seller_id" id="sap_seller_id" value="{{old('sap_seller_id')}}" autocomplete="off">
                            </div>
                        </div>
						<div class="form-group">
                            <label>BG</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" name="bg" id="bg" value="{{old('bg')}}" autocomplete="off" >
                            </div>
                        </div>
						<div class="form-group">
                            <label>BU</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" name="bu" id="bu" value="{{old('bu')}}" autocomplete="off" 
readonly 
onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly',true);">
                            </div>
                        </div>
						
                        <div class="form-group">
                            <label>New Password</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="password" class="form-control" name="password" id="password" value="{{old('password')}}" autocomplete="off" 
readonly 
onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly',true);" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Re-type New Password</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" value="{{old('password_confirmation')}}" required>
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
