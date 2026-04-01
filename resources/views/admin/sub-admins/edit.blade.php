@extends('admin.layout.main')

@section('title', 'Edit Sub-Admin')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('admin.components.page-title', [
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
                <form action="{{ route('admin.sub-admins.update', $sub_admin->id) }}"
                    onsubmit="submitForm(event,this,'/admin/sub-admins/list',true)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Name*</label>
                            <input type="text" name="name" value="{{ old('name', $sub_admin->name) }}"
                                class="form-control" placeholder="Enter Name">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                    </div>

                    <!-- Roles & Permissions Section -->
                    @include('admin.sub-admins.role-permissions-edit', [
                        'roles' => $roles,
                        'permissions' => $permissions,
                        'admin' => $sub_admin,
                        'userRole' => $userRole,
                        'userDirectPermissions' => $userDirectPermissions,
                    ])

                    <div class="row">
                        <div class="col-md-12 col-sm-12 mt-4 text-lg-right text-md-right text-sm-center">
                            <a href="{{ route('admin.sub-admins.list') }}" class="btn btn-secondary mr-2">Cancel</a>
                            <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
