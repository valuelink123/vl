@extends('layouts.login')
@section('content')
    <!-- BEGIN LOGIN FORM -->
    <form class="login-form" method="POST" action="{{ route('login') }}">
        {{ csrf_field() }}
        <h3 class="form-title font-green">Sign In</h3>
        @if ($errors->has('email'))
            <div class="alert alert-danger ">
                <button class="close" data-close="alert"></button>
                {{ $errors->first('email') }}
            </div>
        @endif

        <div class="form-group">
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            <label class="control-label visible-ie8 visible-ie9">Email Address</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="email" id="email" autocomplete="off" placeholder="Email" name="email" value="{{ old('email') }}" required autofocus /> </div>
        @if ($errors->has('password'))
            <div class="alert alert-danger ">
                <button class="close" data-close="alert"></button>
                {{ $errors->first('password') }}
            </div>
        @endif
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">Password</label>
            <input class="form-control form-control-solid placeholder-no-fix" id="password" type="password" autocomplete="off" placeholder="Password" name="password" required /> </div>
        <div class="form-actions">
            <button type="submit" class="btn green uppercase">Login</button>
            <label class="rememberme check mt-checkbox mt-checkbox-outline">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />Remember
				<input type="hidden" name="redirect_url" value="{{array_get($_REQUEST,'redirect_url')}}" />
                <span></span>
            </label>
            <a href="{{ route('password.request') }}" id="forget-password" class="forget-password">Forgot Password?</a>
        </div>
        <!--
        <div class="login-options">
            <h4>Or login with</h4>
            <ul class="social-icons">
                <li>
                    <a class="social-icon-color facebook" data-original-title="facebook" href="javascript:;"></a>
                </li>
                <li>
                    <a class="social-icon-color twitter" data-original-title="Twitter" href="javascript:;"></a>
                </li>
                <li>
                    <a class="social-icon-color googleplus" data-original-title="Goole Plus" href="javascript:;"></a>
                </li>
                <li>
                    <a class="social-icon-color linkedin" data-original-title="Linkedin" href="javascript:;"></a>
                </li>
            </ul>
        </div>
        -->
        <div class="create-account">
            <p>
                <a href="{{ route('register') }}" id="register-btn" class="uppercase">Create an account</a>
            </p>
        </div>
    </form>
    <!-- END LOGIN FORM -->


@endsection

