@extends('layouts.login')

@section('content')
<!-- BEGIN FORGOT PASSWORD FORM -->
<form class="forget-form" method="POST" action="{{ route('password.email') }}" style="display: block;">
    {{ csrf_field() }}
    <h3 class="font-green">Forget Password ?</h3>
    <p> Enter your e-mail address below to reset your password. </p>
    @if (session('status'))
        <div class="alert alert-success ">
            <button class="close" data-close="alert"></button>
            {{ session('status') }}
        </div>
    @endif
    <div class="form-group">
        @if ($errors->has('email'))
            <div class="alert alert-danger ">
                <button class="close" data-close="alert"></button>
                {{ $errors->first('email') }}
            </div>
        @endif
        <input class="form-control placeholder-no-fix" id="email" type="email" autocomplete="off" placeholder="Email" name="email" value="{{ old('email') }}" required /> </div>
    <div class="form-actions">
        <a href="{{ route('login') }}"><button type="button" id="back-btn" class="btn green btn-outline" >Back</button></a>
        <button type="submit" class="btn btn-success uppercase pull-right">Send Password Reset Link</button>
    </div>
</form>
<!-- END FORGOT PASSWORD FORM -->
@endsection
