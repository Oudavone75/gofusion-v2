@extends('company_admin.layout.main')

@section('title', 'Add Sub-Admin')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Add Sub-Admin',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('company_admin.sub-admins.store') }}"
                    onsubmit="submitForm(event,this,'/company-admin/sub-admins/list',true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Name*</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control"
                                placeholder="Enter Name">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Email*</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                                placeholder="Enter Email">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Password*</label>
                            <input type="password" name="password" id="password" value="{{ old('password') }}"
                                class="form-control" placeholder="Enter Password">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        <div class="col-md-12 col-sm-12 mt-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="showPassword"
                                    onchange="togglePasswordVisibility()">
                                <label class="form-check-label" for="showPassword">
                                    Show Passwords
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Roles & Permissions Section -->
                    @include('company_admin.sub-admins.role-permissions', [
                        'roles' => $roles,
                        'permissions' => $permissions,
                    ])

                    <div class="row">
                        <div class="col-md-12 col-sm-12 mt-4 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const showPasswordCheckbox = document.getElementById('showPassword');

            if (showPasswordCheckbox.checked) {
                passwordField.type = 'text';
            } else {
                passwordField.type = 'password';
            }
        }
    </script>
@endpush
