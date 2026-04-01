@extends('company_admin.auth.layout.main')
@section('title', 'Login')

@section('content')
<div class="login-header box-shadow">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="brand-logo">
            <a href="login.html">
                <img src={{ asset("vendors/images/admin/desktop-icon.svg") }} alt="">
            </a>
        </div>
    </div>
</div>
<div class="login-wrap d-flex align-items-center flex-wrap justify-content-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-lg-7">
                <img src={{ asset("vendors/images/admin/login-page-img.png") }} alt="">
            </div>
            <div class="col-md-6 col-lg-5">
                <div class="login-box bg-white box-shadow border-radius-10">
                    <div class="login-title">
                        <h2 class="text-center common-color ">Login To Dashboard</h2>
                    </div>
                    <form method="POST" action="{{ route('company_admin.login.post') }}">
                        @if (session()->has('success'))
                        <div class="input-group custom d-block">
                            <small class="text-success text-2xl">{{ __(session()->get('success')) }}</small>
                        </div>
                        @endif
                        @csrf
                        <div class="input-group custom d-block">
                            <input type="text" name="email"
                                class="form-control form-control-lg @error('email') border-danger @enderror"
                                placeholder="Email" value="{{ old('email') }}">
                            <div class="input-group-append custom" @error('email') style="height: 70%" @enderror>
                                <span class="input-group-text"><i class="icon-copy dw dw-user1"></i></span>
                            </div>
                            @error('email')
                            <small class="text-danger text-2xl">{{ __($message) }}</small>
                            @enderror
                        </div>
                        <div class="input-group custom d-block">
                            <input type="password" name="password"
                                class="form-control form-control-lg @error('password') border-danger @enderror"
                                placeholder="**********">
                            <div class="input-group-append custom" @error('password') style="height: 70%" @enderror>
                                <span style="cursor: pointer" class="input-group-text" id="password-field-icon">
                                    <i class="fa fa-eye-slash"></i>
                                </span>
                            </div>
                            @error('password')
                            <small class="text-danger text-2xl">{{ __($message) }}</small>
                            @enderror
                        </div>
                        <div class="row pb-30">
                            <div class="col-12">
                                <div class="forgot-password text-center"><a href="{{ route('company_admin.forgot.password') }}">Forgot Password</a></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="input-group mb-0">
                                    <button id="sign-in-button" class="btn btn-lg btn-block common-button-pink" href="{{ route('company_admin.dashboard') }}">Sign In</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $('#password-field-icon').on('click', function() {
        const parent = $(this).parent().siblings('input');
        const children = $(this).children('i');
        if (parent.attr('type') == 'password') {
            parent.attr('type', 'text');
            children.removeClass('fa-eye-slash');
            children.addClass('fa-eye');
        } else {
            parent.attr('type', 'password');
            children.removeClass('fa-eye');
            children.addClass('fa-eye-slash');
        }
    });
    $('form').on('submit', function() {
        const btn = $('#sign-in-button');
        btn.prop('disabled', true);
        btn.addClass('btn-disabled');
        btn.text('Signing in...');
    });
</script>
@endsection
