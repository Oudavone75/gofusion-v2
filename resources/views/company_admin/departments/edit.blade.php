@extends('company_admin.layout.main')

@section('title', 'Edit Department')

@section('content')
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="vh-100">
            <div class="page-header">
                <div class="row">
                    @include('company_admin.components.page-title', [
                        'page_title' => 'Edit Department',
                        'paths' => breadcrumbs(),
                    ])
                </div>
            </div>
            <div class="pd-20 card-box mb-30">
                <form action="{{ route('company_admin.departments.update', $company_department->id) }}"
                    onsubmit="submitForm(event,this,'/company-admin/departments',true)" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Name*</label>
                            <input type="text" name="name" value="{{ old('name', $company_department->name) }}"
                                class="form-control" placeholder="Enter Name">
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div>
                        {{-- <div class="col-md-6 col-sm-12">
                            <label class="form-control-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="" disabled
                                    {{ old('status', $company_department->status ?? '') == '' ? 'selected' : '' }}>
                                    Select Status
                                </option>
                                <option value="active"
                                    {{ old('status', $company_department->status ?? '') == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="pending"
                                    {{ old('status', $company_department->status ?? '') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="disabled"
                                    {{ old('status', $company_department->status ?? '') == 'disabled' ? 'selected' : '' }}>Disabled
                                </option>
                            </select>
                            <div class="form-control-feedback mt-2 d-none"></div>
                        </div> --}}
                        <div class="col-md-12 col-sm-12 mt-3 ml-0 text-lg-right text-md-right text-sm-center">
                            <button type="submit" id="submit-button" class="btn btn-primary">Update</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
