@extends('company_admin.layout.main')

@section('title', 'Change Password')

@section('content')
<div class="pd-ltr-20 xs-pd-20-10">
    <div class="vh-100">
        <div class="page-header">
            <div class="row">
                @include('company_admin.components.page-title', [
                'page_title' => 'Change Password',
                ])
            </div>
        </div>
        <div class="pd-20 card-box mb-30">
            <form action="{{ route('company_admin.change.update') }}" onsubmit="submitForm(event,this,'/company-admin/dashboard',true)"
                method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">New Password*</label>
                        <input type="password" name="password" id="password" value="{{ old('password') }}"
                            class="form-control" placeholder="**********">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <label class="form-control-label">Confirm Password*</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            value="{{ old('password_confirmation') }}" class="form-control" placeholder="**********">
                        <div class="form-control-feedback mt-2 d-none"></div>
                    </div>
                    <div class="col-md-12 col-sm-12 mt-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="showPassword" onchange="togglePasswordVisibility()">
                            <label class="form-check-label" for="showPassword">
                                Show Passwords
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                        <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');
    const showPasswordCheckbox = document.getElementById('showPassword');

    if (showPasswordCheckbox.checked) {
        passwordField.type = 'text';
        confirmPasswordField.type = 'text';
    } else {
        passwordField.type = 'password';
        confirmPasswordField.type = 'password';
    }
}
</script>
@endsection
