@extends('company_admin.layout.main')

@section('title', 'Edit Sub-Admin')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Edit Sub-Admin',
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
                <form action="{{ route('company_admin.sub-admins.update', $sub_admin->id) }}"
                    onsubmit="submitForm(event,this,'/company-admin/sub-admins/list',true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Name*</label>
                            <input type="text" name="name" value="{{ old('name', $sub_admin->first_name) }}"
                                class="form-control" placeholder="Enter Name">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        {{-- <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Email*</label>
                            <input type="email" name="email" value="{{ old('email', $sub_admin->email) }}"
                                class="form-control" placeholder="Enter Email">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div> --}}
                        {{-- <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Password</label>
                            <input type="password" name="password" value="{{ old('password') }}" class="form-control"
                                placeholder="Leave blank to keep current password">
                            <div class="form-control-feedback mt-2 d-none"></div>
                            <small class="form-text text-muted">Leave blank if you don't want to change the password</small>
                        </div> --}}
                    </div>

                    <!-- Roles & Permissions Section -->
                    @include('company_admin.sub-admins.role-permissions-edit', [
                        'roles' => $roles,
                        'permissions' => $permissions,
                        'admin' => $sub_admin,
                        'userRole' => $userRole,
                        'userDirectPermissions' => $userDirectPermissions,
                    ])

                    <div class="row">
                        <div class="col-md-12 col-sm-12 mt-4 text-lg-right text-md-right text-sm-center">
                            <a href="{{ route('company_admin.sub-admins.list') }}" class="btn btn-secondary mr-2">Cancel</a>
                            <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
