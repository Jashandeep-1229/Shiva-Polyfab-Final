@extends('auth.app')
@section('title')
    Lead Management Login 
@endsection
@section('css')
@endsection

@section('style')
<style>
    .login-card .login-main .theme-form .show-hide{
        top:70%;
    }
    .login-main {
        border-top: 5px solid #7366ff !important;
    }
</style>
@endsection

@section('content')

<div class="container-fluid p-0">
    <div class="row m-0">
        <div class="col-12 p-0">
            <div class="login-card">
                <div>
                    <div class="login-main">
                        <form class="theme-form" method="POST" action="{{ route('lead.login.submit') }}">
                            @csrf
                            <h4 class="text-center">Lead Management</h4>
                            <p class="text-center">Enter your lead credentials to login</p>
                            
                            <div class="form-group">
                                <label class="col-form-label">Email Address</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="lead@example.com">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label class="col-form-label">Password</label>
                                <div class="form-input position-relative">
                                    <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" required="" placeholder="*********">
                                    <div class="show-hide"><span class="show"></span></div>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-0">
                                <div class="checkbox p-0">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="text-muted" for="remember">Remember password</label>
                                </div>
                                <button class="btn btn-primary mt-2 btn-lg w-100" type="submit">Lead Login</button>
                            </div>

                            <div class="mt-4 text-center">
                                <a href="{{ url('/') }}" class="text-muted"><i class="fa fa-arrow-left me-2"></i>Back to Production System</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
