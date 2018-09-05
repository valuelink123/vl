@extends('layouts.login')

@section('content')
<!-- BEGIN REGISTRATION FORM -->
<form class="register-form" method="POST" action="{{ route('register') }}" style="display: block;">
    {{ csrf_field() }}
    <h3 class="font-green">Sign Up</h3>
    <p class="hint"> Enter your personal details below: </p>
    <div class="form-group">
        @if ($errors->has('name'))
            <div class="alert alert-danger ">
                <button class="close" data-close="alert"></button>
                {{ $errors->first('name') }}
            </div>
        @endif
        <label class="control-label visible-ie8 visible-ie9">Full Name</label>
        <input class="form-control placeholder-no-fix" type="text" placeholder="Full Name" id="name" name="name" value="{{ old('name') }}" required autofocus />
    </div>

    <div class="form-group">
        @if ($errors->has('email'))
            <div class="alert alert-danger ">
                <button class="close" data-close="alert"></button>
                {{ $errors->first('email') }}
            </div>
        @endif
        <label class="control-label visible-ie8 visible-ie9">Email</label>
        <input class="form-control placeholder-no-fix" placeholder="Email" id="email" type="email"  name="email" value="{{ old('email') }}" required /> </div>

    <p class="hint"> Enter your account details below: </p>
    <div class="form-group">
        @if ($errors->has('password'))
            <div class="alert alert-danger ">
                <button class="close" data-close="alert"></button>
                {{ $errors->first('password') }}
            </div>
        @endif
        <label class="control-label visible-ie8 visible-ie9">Password</label>
        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="password" placeholder="Password" name="password" required /> </div>
    <div class="form-group">
        @if ($errors->has('password_confirmation'))
            <div class="alert alert-danger ">
                <button class="close" data-close="alert"></button>
                {{ $errors->first('password_confirmation') }}
            </div>
        @endif
        <label class="control-label visible-ie8 visible-ie9">Re-type Your Password</label>
        <input class="form-control placeholder-no-fix" type="password" id="password_confirmation" autocomplete="off" placeholder="Re-type Your Password" name="password_confirmation" required /> </div>
    <div class="form-group margin-top-20 margin-bottom-20">
        <label class="mt-checkbox mt-checkbox-outline">
            <input type="checkbox" name="tnc" /> I agree to the
            <a href="javascript:;">Terms of Service </a> &
            <a href="javascript:;">Privacy Policy </a>
            <span></span>
        </label>
        <div id="register_tnc_error"> </div>
    </div>
    <div class="form-actions">
        <a href="{{ route('login') }}"><button type="button" id="register-back-btn" class="btn green btn-outline">Back</button></a>
        <button type="submit" id="register-submit-btn" class="btn btn-success uppercase pull-right">Submit</button>
    </div>
</form>
<!-- END REGISTRATION FORM -->
@endsection
