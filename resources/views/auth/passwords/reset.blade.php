@extends('layouts.login')

@section('content')
<form class="reset-form" method="POST" action="{{ route('password.request') }}" style="display: block;">
    {{ csrf_field() }}

    <input type="hidden" name="token" value="{{ $token }}">
    <h3 class="font-green">Reset Password</h3>
    <p class="hint"> Enter your account details below: </p>
    <div class="form-group">
        @if ($errors->has('email'))
            <div class="alert alert-danger ">
                <button class="close" data-close="alert"></button>
                {{ $errors->first('email') }}
            </div>
        @endif
        <label class="control-label visible-ie8 visible-ie9">Email</label>
        <input class="form-control placeholder-no-fix" placeholder="Email" id="email" type="email"  name="email" value="{{ $email or old('email') }}" required /> </div>
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
        <input class="form-control placeholder-no-fix" type="password" id="password-confirm" autocomplete="off" placeholder="Re-type Your Password" name="password_confirmation" required /> </div>

    <div class="form-actions">
        <a href="{{ route('login') }}"><button type="button" id="register-back-btn" class="btn green btn-outline">Back</button></a>
        <button type="submit" id="reset-submit-btn" class="btn btn-success uppercase pull-right">Submit</button>
    </div>
</form>
@endsection
