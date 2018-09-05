@extends('layouts.layout')
@section('label', 'Setting Amazon Seller Accounts')
@section('content')
    <h1 class="page-title font-red-intense"> User Profile
        <small>user account page.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">User Profile Form</span>
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
                <form role="form" action="{{ url('profile') }}" method="POST">
                    {{ csrf_field() }}

                    <div class="form-body">
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" value="{{$profile->email}}" readonly />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Full Name</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="name" id="name" value="{{$profile->name}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Current Password</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="password" class="form-control" name="current_password" id="current_password" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="password" class="form-control" name="password" id="password" value="">
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



                            <!--
                            <label>Amazon AWS Access KeyId</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Email Address (Important: Use address registered in Seller Central or emails won't send.)">
                            </div>

                            <label>Amazon Secret Key</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Email Address (Important: Use address registered in Seller Central or emails won't send.)">
                            </div>
                            -->

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

            <div class="col-md-4 pricing-content-1" style="padding: 0;">
                <div class="price-column-container border-active">
                    <div class="price-table-head bg-green">
                        <h2 class="no-margin">Account Plan</h2>
                    </div>
                    <div class="arrow-down border-top-green"></div>
                    <div class="price-table-pricing">
                        <h3>
                            <sup class="price-sign">$</sup>Free</h3>
                        <p>per month</p>

                    </div>
                    <div class="price-table-content">
                        <div class="row mobile-padding">
                            <div class="col-xs-3 text-right mobile-padding">
                                <i class="icon-user-follow"></i>
                            </div>
                            <div class="col-xs-9 text-left mobile-padding">1 Amazon Account</div>
                        </div>
                        <div class="row mobile-padding">
                            <div class="col-xs-3 text-right mobile-padding">
                                <i class="icon-drawer"></i>
                            </div>
                            <div class="col-xs-9 text-left mobile-padding">500 Send Emails</div>
                        </div>
                        <div class="row mobile-padding">
                            <div class="col-xs-3 text-right mobile-padding">
                                <i class="icon-cloud-download"></i>
                            </div>
                            <div class="col-xs-9 text-left mobile-padding"></div>
                        </div>
                        <div class="row mobile-padding">
                            <div class="col-xs-3 text-right mobile-padding">
                                <i class="icon-refresh"></i>
                            </div>
                            <div class="col-xs-9 text-left mobile-padding">Daily Backups</div>
                        </div>
                    </div>
                    <div class="arrow-down arrow-grey"></div>
                    <div class="price-table-footer">
                        <button type="button" class="btn green price-button sbold uppercase">Sign Up</button>
                    </div>
                </div>
            </div>


    </div>


@endsection
