@extends('admin.auth.layout.main')

@section('title', 'Forgot Password')

@section('content')
    <div class="login-wrap d-flex align-items-center flex-wrap justify-content-center">
        <div class="container">
            <div class="row align-items-center">
                {{-- <div class="col-md-6 col-lg-7">
                    <img src="{{ asset('../../login-left-img.png') }}" alt="">
                </div> --}}
                <div class="col-md-12 col-lg-12">
                    <div class="login-box bg-white box-shadow border-radius-10">
                        <div class="login-title">
                            <h2 class="text-center common-color">Enter your email address to reset your password</h2>
                        </div>
                        <form method="POST" action="{{ route('admin.forgot.password.post') }}" onsubmit="disableSubmitButton(this)">
                            @csrf
                            <div class="input-group custom d-block">
                                <input type="text" name="email" class="form-control form-control-lg @error('email') border-danger @enderror" placeholder="Email" value={{ old('email') }}>
                                <div class="input-group-append custom" style="height: 70%">
                                    <span class="input-group-text">
                                        <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                    </span>
                                </div>
                                @error('email')
                                    <small class="text-danger text-2xl">{{ __($message) }}</small>
                                @enderror
                                @if(session()->has('success'))
                                    <small class="text-success text-2xl">{{ __(session()->get('success')) }}</small>
                                @endif
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
                                        <a class="btn btn-outline-info btn-lg btn-block" style="font-size: 12px;" href="{{ route('admin.login') }}">Login</a>
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
        function disableSubmitButton(form) {
            const submitButton = form.querySelector('#submit-button');
            submitButton.disabled = true;
            submitButton.innerText = 'Submitting...';
        }
    </script>
@overwrite
