@extends('admin.auth.layout.main')

@section('title', 'Reset Password')

@section('content')
<div class="login-wrap d-flex align-items-center flex-wrap justify-content-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-12 col-lg-12">
                <div class="login-box bg-white box-shadow border-radius-10">
                    <div class="login-title">
                        <h2 class="text-center common-color">Enter your new password</h2>
                    </div>
                    @error('token')
                    <div class="alert alert-danger d-flex justify-content-center">
                        {{ $message }}
                    </div>
                    @enderror
                    <form method="POST" action="{{ route('admin.password.reset.post') }}" onsubmit="disableSubmitButton(this)">
                        @csrf
                        <input type="hidden" name="token" value="{{ request()->token }}">
                        <input type="hidden" name="email" value="{{ request()->email }}">
                        <div class="input-group custom d-block">
                            <input type="password" name="password" class="form-control form-control-lg @error('password') border-danger @enderror" placeholder="Password" value={{ old('password') }}>
                            <div class="input-group-append custom" @error('password') style="height: 70%" @enderror>
                                <span style="cursor: pointer" class="input-group-text password-field-icon">
                                    <i class="fa fa-eye-slash"></i>
                                </span>
                            </div>
                            @error('password')
                            <small class="text-danger text-2xl">{{ __($message) }}</small>
                            @enderror
                            @error('error_message')
                            <small class="text-danger text-2xl">{{ __($message) }}</small>
                            @enderror
                        </div>
                        <div class="input-group custom d-block">
                            <input type="password" name="password_confirmation" class="form-control form-control-lg @error('password_confirmation') border-danger @enderror" placeholder="Confirm Password" value={{ old('confirmation_password') }}>
                            <div class="input-group-append custom" @error('password') style="height: 70%" @enderror>
                                <span style="cursor: pointer" class="input-group-text password-field-icon">
                                    <i class="fa fa-eye-slash"></i>
                                </span>
                            </div>
                            @error('password_confirmation')
                            <small class="text-danger text-2xl">{{ __($message) }}</small>
                            @enderror
                        </div>
                        <div class="row align-items-center">
                            <div class="col-5">
                                <div class="input-group mb-0">
                                    <button id="submit-button" class="btn common-button-pink btn-lg btn-block">Submit</button>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="font-16 weight-600 text-center" data-color="#707373">OR</div>
                            </div>
                            <div class="col-5">
                                <div class="input-group mb-0">
                                    <a class="btn btn-outline-info btn-lg btn-block" style="font-size: 12px" href="{{ route('admin.forgot.password') }}">Forgot Password</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- <div class="login-header box-shadow">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="brand-logo">
                <a href="login.html">
                    <img src="{{ asset('vendors/images/deskapp-logo.svg') }}" alt="">
</a>
</div>
{{-- <div class="login-menu"> --}}
{{-- <ul> --}}
{{-- <li><a href="register.html">Register</a></li> --}}
{{-- </ul> --}}
{{-- </div> --}}
</div>
{{-- </div> --}}
{{-- <div class="login-wrap d-flex align-items-center flex-wrap justify-content-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="{{ asset('vendors/images/forgot-password.png') }}" alt="">
</div>
<div class="col-md-6">
    <div class="login-box bg-white box-shadow border-radius-10">
        <div class="login-title">
            <h2 class="text-center text-primary">Reset Password</h2>
        </div>
        <h6 class="mb-20">Enter Password and Confirmation Password.</h6>
        <form method="POST" action="{{ route('admin.reset.password.post') }}" onsubmit="disableSubmitButton(this)">
            @csrf
            <input type="hidden" name="token" value="{{ request()->token }}">
            <div class="input-group custom d-block">
                <input type="password" name="password" class="form-control form-control-lg @error('password') border-danger @enderror" placeholder="**********" value={{ old('password') }}>
                <div class="input-group-append custom" style="height: 70%">
                    <span class="input-group-text">
                        <i class="dw dw-padlock1"></i>
                    </span>
                </div>
                @error('password')
                <small class="text-danger text-2xl">{{ __($message) }}</small>
                @enderror
                @error('error_message')
                <small class="text-danger text-2xl">{{ __($message) }}</small>
                @enderror
            </div>
            <div class="input-group custom d-block">
                <input type="password" name="password_confirmation" class="form-control form-control-lg @error('password_confirmation') border-danger @enderror" placeholder="**********" value={{ old('confirmation_password') }}>
                <div class="input-group-append custom" style="height: 70%">
                    <span class="input-group-text">
                        <i class="dw dw-padlock1"></i>
                    </span>
                </div>
                @error('password_confirmation')
                <small class="text-danger text-2xl">{{ __($message) }}</small>
                @enderror
            </div>
            <div class="row align-items-center">
                <div class="col-5">
                    <div class="input-group mb-0">
                        <!--
                                            use code for form submit
                                            <input class="btn btn-primary btn-lg btn-block" type="submit" value="Submit">
                                        -->
                        <button id="submit-button" class="btn btn-primary btn-lg btn-block">Submit</button>
                    </div>
                </div>
                <div class="col-2">
                    <div class="font-16 weight-600 text-center" data-color="#707373">OR</div>
                </div>
                <div class="col-5">
                    <div class="input-group mb-0">
                        <a class="btn btn-outline-primary btn-lg btn-block" style="font-size: 12px" href="{{ route('admin.forgot.password') }}">Forgot Password</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
</div> --}}
@endsection
@section('script')
<script>
     $('.password-field-icon').on('click', function () {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    function disableSubmitButton(form) {
        const submitButton = form.querySelector('#submit-button');
        submitButton.disabled = true;
        submitButton.innerText = 'Submitting...';
    }
</script>
@overwrite